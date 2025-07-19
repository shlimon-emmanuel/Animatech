<?php
// app/Models/MovieModel.php

namespace App\Models;

use PDO;
use PDOException;

class MovieModel {
    private $apiKey;
    private $baseUrl;
    private $db;
    private $cache; // Système de cache NoSQL basé sur JSON

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->baseUrl = TMDB_API_URL;
        
        // Vérifier que la clé API est configurée
        if ($apiKey === 'your_api_key_here' || empty($apiKey)) {
            error_log("ERREUR: Clé API TMDB non configurée! Veuillez obtenir une clé sur https://www.themoviedb.org/");
        }
        
        // Initialisation du cache NoSQL JSON
        $this->cache = new JsonDbModel();
        
        // Initialisation de la connexion à la base de données
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch(PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    private function buildApiUrl($endpoint, $params = []) {
        $isJWT = (strpos($this->apiKey, 'eyJ') === 0);
        $url = $this->baseUrl . $endpoint;
        
        if (!$isJWT) {
            $params['api_key'] = $this->apiKey;
        }
        
        if (!empty($params)) {
            $url .= "?" . http_build_query($params);
        }
        
        return $url;
    }

    public function getPopularMovies($page = 1, $sortBy = 'popularity.desc', $minVoteCount = 100) {
        // Créer une clé de cache unique pour cette requête
        $cacheKey = "popular_movies:$page:$sortBy:$minVoteCount";
        
        // Essayer de récupérer depuis le cache NoSQL
        $cachedData = $this->cache->get('movies', $cacheKey);
        if ($cachedData) {
            error_log("Récupération des films populaires depuis le cache JSON");
            return (object)$cachedData;
        }
        
        // Si pas en cache, faire l'appel API normal
        $url = $this->buildApiUrl("discover/movie", [
            'language' => 'fr-FR',
            'page' => $page,
            'sort_by' => $sortBy,
            'with_genres' => 16,  // Genre Animation uniquement
            'include_adult' => false,
            'vote_count.gte' => $minVoteCount
        ]);
        
        $response = $this->makeApiCall($url);
        
        if (!$response || !isset($response->results)) {
            return null;
        }
        
        // Stocker dans le cache NoSQL pour les requêtes futures (2 heures)
        $this->cache->store('movies', $cacheKey, $response, 7200);
        
        return $response;
    }

    public function getFilteredMovies($page = 1, $params = []) {
        // Paramètres par défaut
        $defaultParams = [
            'sort_by' => 'popularity.desc',
            'with_genres' => 16, // Animation
            'vote_count.gte' => 50,
            'include_adult' => false,
            'with_original_language' => '', // Langue originale (ex: 'ja' pour japonais)
            'primary_release_date.gte' => '', // Date de sortie min (format: YYYY-MM-DD)
            'primary_release_date.lte' => '', // Date de sortie max (format: YYYY-MM-DD)
            'vote_average.gte' => 0, // Note minimale (0-10)
            'year' => '' // Année spécifique
        ];
        
        // Fusionne les paramètres par défaut avec ceux fournis
        $params = array_merge($defaultParams, $params);
        
        // Créer une clé de cache unique basée sur les paramètres de filtrage
        $cacheKey = "filtered_movies:$page:" . md5(serialize($params));
        
        // Essayer de récupérer depuis le cache NoSQL
        $cachedData = $this->cache->get('search', $cacheKey);
        if ($cachedData) {
            error_log("Récupération des films filtrés depuis le cache JSON");
            return (object)$cachedData;
        }
        
        $apiParams = [
            'language' => 'fr-FR',
            'page' => $page,
            'sort_by' => $params['sort_by'],
            'with_genres' => $params['with_genres'],
            'vote_count.gte' => $params['vote_count.gte'],
            'include_adult' => $params['include_adult'] ? 'true' : 'false'
        ];
        
        // Ajout des paramètres optionnels si définis
        if (!empty($params['with_original_language'])) {
            $apiParams['with_original_language'] = $params['with_original_language'];
        }
        
        if (!empty($params['primary_release_date.gte'])) {
            $apiParams['primary_release_date.gte'] = $params['primary_release_date.gte'];
        }
        
        if (!empty($params['primary_release_date.lte'])) {
            $apiParams['primary_release_date.lte'] = $params['primary_release_date.lte'];
        }
        
        if (!empty($params['vote_average.gte'])) {
            $apiParams['vote_average.gte'] = $params['vote_average.gte'];
        }
        
        if (!empty($params['year'])) {
            $apiParams['year'] = $params['year'];
        }
        
        $url = $this->buildApiUrl("discover/movie", $apiParams);
        
        // Log the URL for debugging
        error_log("TMDB API request URL: " . $url);
        
        $response = $this->makeApiCall($url);
        
        if (!$response || !isset($response->results)) {
            error_log("API call failed or returned no results");
            return null;
        }
        
        error_log("API returned " . count($response->results) . " results");
        
        // Stocker dans le cache NoSQL pour les requêtes futures (2 heures)
        $this->cache->store('search', $cacheKey, $response, 7200);
        
        return $response;
    }

    public function getMovieById($id) {
        // Créer une clé de cache unique pour ce film
        $cacheKey = "movie:$id";
        
        // Essayer de récupérer depuis le cache NoSQL
        $cachedData = $this->cache->get('details', $cacheKey);
        if ($cachedData) {
            error_log("Récupération du film #$id depuis le cache JSON");
            return (object)$cachedData;
        }
        
        // Si pas en cache, faire l'appel API normal
        $url = $this->buildApiUrl("movie/$id", ['language' => 'fr-FR']);
        $movie = $this->makeApiCall($url);
        
        // Stocker dans le cache NoSQL pour les requêtes futures (24 heures - les films changent rarement)
        if ($movie) {
            $this->cache->store('details', $cacheKey, $movie, 86400);
        }
        
        return $movie;
    }

    private function makeApiCall($url) {
        try {
            // Validation d'URL pour prévenir l'injection d'URL malveillantes
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \Exception("URL invalide dans makeApiCall: " . $url);
            }
            
            // Déterminer le type d'authentification
            $isJWT = (strpos($this->apiKey, 'eyJ') === 0); // JWT commence par 'eyJ'
            
            $headers = [
                'Accept: application/json',
                'Connection: close'
            ];
            
            if ($isJWT) {
                $headers[] = 'Authorization: Bearer ' . $this->apiKey;
            }
            
            // Configuration sécurisée du contexte
            $context = stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'ignore_errors' => true,
                    'user_agent' => 'ANIMATECH/1.0',
                    'header' => $headers
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false
                ]
            ]);
            
            // Tentative de récupération avec gestion des timeouts
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                $error = error_get_last();
                throw new \Exception("Erreur lors de l'appel API: " . ($error ? $error['message'] : 'Erreur inconnue'));
            }
            
            // Vérification des headers HTTP
            $statusLine = $http_response_header[0] ?? '';
            preg_match('{HTTP\/\S*\s(\d{3})}', $statusLine, $match);
            $status = $match[1] ?? 500;
            
            if ($status >= 400) {
                throw new \Exception("Erreur HTTP $status lors de l'appel API");
            }
            
            // Protection contre les injections JSON
            $response = preg_replace('/[[:cntrl:]]/', '', $response);
            
            // Décodage du JSON avec vérification
            $decoded = json_decode($response);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Erreur de décodage JSON: " . json_last_error_msg());
            }
            
            return $decoded;
            
        } catch (\Exception $e) {
            error_log("Erreur makeApiCall: " . $e->getMessage() . " pour URL: " . $url);
            return null;
        }
    }

    public function addToFavorites($userId, $movieId) {
        try {
            // Vérifier les paramètres
            if (!$userId || !$movieId) {
                error_log("addToFavorites: Paramètres invalides - userId: $userId, movieId: $movieId");
                return false;
            }
            
            // Vérifier et créer les tables nécessaires avec la structure simple
            try {
                // Table favorites simplifiée sans contraintes de clé étrangère
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS favorites (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        movie_id INT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_favorite (user_id, movie_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
                ");
                
                // Table movies simplifiée
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS movies (
                        id INT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        overview TEXT,
                        poster_path VARCHAR(255),
                        backdrop_path VARCHAR(255),
                        release_date DATE,
                        popularity FLOAT,
                        vote_average FLOAT,
                        vote_count INT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
                ");
                
                error_log("Tables vérifiées/créées avec succès");
            } catch (PDOException $e) {
                error_log("Erreur lors de la création des tables: " . $e->getMessage());
                // Continuer malgré l'erreur
            }
            
            // Vérifier si le film est déjà dans les favoris
            try {
                $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND movie_id = ?");
                $checkStmt->execute([$userId, $movieId]);
                
                if ($checkStmt->fetchColumn() > 0) {
                    error_log("Film déjà en favoris - userId: $userId, movieId: $movieId");
                    return true;
                }
            } catch (PDOException $e) {
                error_log("Erreur lors de la vérification des favoris: " . $e->getMessage());
                // Continuer malgré l'erreur
            }
            
            // Vérifier si le film existe dans la base de données
            $movieExists = false;
            try {
                $checkMovieStmt = $this->db->prepare("SELECT COUNT(*) FROM movies WHERE id = ?");
                $checkMovieStmt->execute([$movieId]);
                $movieExists = ($checkMovieStmt->fetchColumn() > 0);
                
                if (!$movieExists) {
                    // Le film n'existe pas, récupérer ses détails
                    $movieDetails = $this->getMovieDetails($movieId);
                    
                    if ($movieDetails) {
                        // Insérer le film dans la base de données
                        $insertMovieStmt = $this->db->prepare("
                            INSERT INTO movies (id, title, overview, poster_path, backdrop_path, release_date, popularity, vote_average, vote_count) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        
                        try {
                            $insertMovieStmt->execute([
                                $movieDetails->id,
                                $movieDetails->title,
                                $movieDetails->overview ?? '',
                                $movieDetails->poster_path ?? '',
                                $movieDetails->backdrop_path ?? '',
                                $movieDetails->release_date ?? null,
                                $movieDetails->popularity ?? 0,
                                $movieDetails->vote_average ?? 0,
                                $movieDetails->vote_count ?? 0
                            ]);
                            
                            error_log("Film inséré avec succès dans la base de données - ID: " . $movieDetails->id);
                            $movieExists = true;
                        } catch (PDOException $insertError) {
                            error_log("Erreur lors de l'insertion du film: " . $insertError->getMessage());
                            // Continuer même en cas d'erreur pour essayer d'ajouter le favori
                        }
                    } else {
                        error_log("Impossible de récupérer les détails du film - movieId: $movieId");
                    }
                }
            } catch (PDOException $e) {
                error_log("Erreur lors de la vérification du film: " . $e->getMessage());
                // Continuer malgré l'erreur
            }
            
            // Ajouter le film aux favoris (même s'il n'est pas dans la table movies)
            try {
                // Utiliser INSERT IGNORE pour éviter les erreurs de clé dupliquée
                $stmt = $this->db->prepare("INSERT IGNORE INTO favorites (user_id, movie_id) VALUES (?, ?)");
                $stmt->execute([$userId, $movieId]);
                
                if ($stmt->rowCount() > 0) {
                    error_log("Film ajouté aux favoris avec succès - userId: $userId, movieId: $movieId");
                    return true;
                } else {
                    error_log("Film déjà ajouté ou erreur lors de l'ajout aux favoris - userId: $userId, movieId: $movieId");
                    // Considérer comme un succès si le film est déjà en favoris
                    return $this->isFavorite($userId, $movieId);
                }
            } catch (PDOException $e) {
                error_log("Exception lors de l'ajout aux favoris: " . $e->getMessage());
                return false;
            }
        } catch (PDOException $e) {
            error_log("Exception principale dans addToFavorites: " . $e->getMessage());
            return false;
        }
    }

    public function removeFromFavorites($userId, $movieId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?");
            return $stmt->execute([$userId, $movieId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function isFavorite($userId, $movieId) {
        if (!$userId || !$movieId) {
            error_log("isFavorite: Paramètres invalides - userId: $userId, movieId: $movieId");
            return false;
        }
        
        try {
            // Vérifier que la table existe
            try {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS favorites (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        movie_id INT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_favorite (user_id, movie_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
                ");
            } catch (PDOException $e) {
                error_log("isFavorite: Erreur lors de la création de la table: " . $e->getMessage());
                // Continuer malgré l'erreur
            }
            
            // Vérifier si le film est dans les favoris
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND movie_id = ?");
            $stmt->execute([$userId, $movieId]);
            $count = $stmt->fetchColumn();
            
            error_log("isFavorite: Film " . ($count > 0 ? "trouvé" : "non trouvé") . " dans les favoris - userId: $userId, movieId: $movieId");
            
            return $count > 0;
        } catch (PDOException $e) {
            error_log("isFavorite: Exception - " . $e->getMessage());
            return false;
        }
    }

    public function getUserFavorites($userId) {
        if (!$userId) {
            error_log("getUserFavorites: ID utilisateur invalide");
            return [];
        }
        
        try {
            // Vérifier que la table existe
            try {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS favorites (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        movie_id INT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_favorite (user_id, movie_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
                ");
                
                // S'assurer que la table movies existe aussi
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS movies (
                        id INT PRIMARY KEY,
                        title VARCHAR(255) NOT NULL,
                        overview TEXT,
                        poster_path VARCHAR(255),
                        backdrop_path VARCHAR(255),
                        release_date DATE,
                        popularity FLOAT,
                        vote_average FLOAT,
                        vote_count INT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
                ");
            } catch (PDOException $e) {
                error_log("getUserFavorites: Erreur lors de la création des tables: " . $e->getMessage());
                // Continuer malgré l'erreur
            }
            
            // Récupérer les IDs des films favoris
            $stmt = $this->db->prepare("SELECT movie_id FROM favorites WHERE user_id = ?");
            $stmt->execute([$userId]);
            $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            error_log("getUserFavorites: " . count($favorites) . " films favoris trouvés pour l'utilisateur $userId");
            
            if (empty($favorites)) {
                return [];
            }
            
            // Récupérer les détails des films
            $movies = [];
            foreach ($favorites as $movieId) {
                // D'abord, vérifier si le film existe dans notre base de données locale
                $stmt = $this->db->prepare("SELECT * FROM movies WHERE id = ?");
                $stmt->execute([$movieId]);
                $localMovie = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($localMovie) {
                    // Si le film existe localement mais avec des données minimales, essayer de l'enrichir
                    if (empty($localMovie['poster_path']) || $localMovie['title'] == "Film #$movieId") {
                        $apiMovie = $this->getMovieById($movieId);
                        if ($apiMovie) {
                            // Mettre à jour le film local avec les données de l'API
                            $this->updateMovieDetails($movieId, $apiMovie);
                            $movies[] = $apiMovie;
                        } else {
                            // Sinon utiliser les données locales, même si elles sont minimales
                            $movies[] = (object)$localMovie;
                        }
                    } else {
                        // Les données locales sont complètes, les utiliser
                        $movies[] = (object)$localMovie;
                    }
                } else {
                    // Le film n'existe pas localement, essayer de le récupérer via l'API
                    $apiMovie = $this->getMovieById($movieId);
                    if ($apiMovie) {
                        // Stocker le film dans la base de données locale pour les futures requêtes
                        $this->storeMovieDetails($movieId, $apiMovie);
                        $movies[] = $apiMovie;
                    } else {
                        // Si l'API échoue, créer une entrée minimale
                        $minimalMovie = [
                            'id' => $movieId,
                            'title' => 'Film #' . $movieId,
                            'poster_path' => '',
                            'release_date' => date('Y-m-d'),
                            'vote_average' => 0
                        ];
                        $this->storeMinimalMovie($movieId, $minimalMovie);
                        $movies[] = (object)$minimalMovie;
                    }
                }
            }
            
            error_log("getUserFavorites: " . count($movies) . " films récupérés avec succès");
            return $movies;
        } catch (PDOException $e) {
            error_log("getUserFavorites: Exception - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Stocker ou mettre à jour les détails d'un film dans la base de données locale
     */
    private function storeMovieDetails($movieId, $movieDetails) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO movies (
                    id, title, overview, poster_path, backdrop_path, 
                    release_date, popularity, vote_average, vote_count
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    overview = VALUES(overview),
                    poster_path = VALUES(poster_path),
                    backdrop_path = VALUES(backdrop_path),
                    release_date = VALUES(release_date),
                    popularity = VALUES(popularity),
                    vote_average = VALUES(vote_average),
                    vote_count = VALUES(vote_count)
            ");
            
            $stmt->execute([
                $movieId,
                $movieDetails->title ?? '',
                $movieDetails->overview ?? '',
                $movieDetails->poster_path ?? '',
                $movieDetails->backdrop_path ?? '',
                $movieDetails->release_date ?? null,
                $movieDetails->popularity ?? 0,
                $movieDetails->vote_average ?? 0,
                $movieDetails->vote_count ?? 0
            ]);
            
            error_log("storeMovieDetails: Film ID $movieId stocké avec succès");
            return true;
        } catch (PDOException $e) {
            error_log("storeMovieDetails: Exception - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mettre à jour les détails d'un film existant
     */
    private function updateMovieDetails($movieId, $movieDetails) {
        return $this->storeMovieDetails($movieId, $movieDetails);
    }
    
    /**
     * Stocker un film avec des informations minimales
     */
    private function storeMinimalMovie($movieId, $minimalData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO movies (id, title, poster_path, release_date, vote_average)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    poster_path = VALUES(poster_path),
                    release_date = VALUES(release_date),
                    vote_average = VALUES(vote_average)
            ");
            
            $stmt->execute([
                $movieId,
                $minimalData['title'],
                $minimalData['poster_path'],
                $minimalData['release_date'],
                $minimalData['vote_average']
            ]);
            
            error_log("storeMinimalMovie: Données minimales pour le film ID $movieId stockées avec succès");
            return true;
        } catch (PDOException $e) {
            error_log("storeMinimalMovie: Exception - " . $e->getMessage());
            return false;
        }
    }

    public function addComment($userId, $movieId, $content, $rating) {
        try {
            $stmt = $this->db->prepare("INSERT INTO comments (user_id, movie_id, content, rating) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$userId, $movieId, $content, $rating]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getMovieComments($movieId) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.username, u.profile_picture 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.movie_id = ? 
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$movieId]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Récupérer les réponses pour chaque commentaire
            foreach ($comments as &$comment) {
                $comment['replies'] = $this->getCommentReplies($comment['id']);
            }
            
            return $comments;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des commentaires: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère les réponses à un commentaire spécifique
     */
    public function getCommentReplies($commentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.username, u.profile_picture 
                FROM comment_replies r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.comment_id = ? 
                ORDER BY r.created_at ASC
            ");
            $stmt->execute([$commentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des réponses: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ajoute une réponse à un commentaire
     */
    public function addCommentReply($commentId, $userId, $content) {
        try {
            // Vérifier d'abord si le commentaire existe
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM comments WHERE id = ?");
            $checkStmt->execute([$commentId]);
            if ($checkStmt->fetchColumn() == 0) {
                error_log("Tentative de répondre à un commentaire inexistant (id: $commentId)");
                return false;
            }
            
            // Vérifier si l'utilisateur existe
            $checkUserStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id = ?");
            $checkUserStmt->execute([$userId]);
            if ($checkUserStmt->fetchColumn() == 0) {
                error_log("Tentative de répondre avec un utilisateur inexistant (id: $userId)");
                return false;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO comment_replies (comment_id, user_id, content) 
                VALUES (?, ?, ?)
            ");
            $result = $stmt->execute([$commentId, $userId, $content]);
            
            if (!$result) {
                error_log("Échec de l'insertion d'une réponse - commentId: $commentId, userId: $userId");
                return false;
            }
            
            error_log("Réponse ajoutée avec succès - commentId: $commentId, userId: $userId");
            return true;
        } catch (PDOException $e) {
            error_log("Exception dans addCommentReply: " . $e->getMessage());
            return false;
        }
    }

    public function getMovieVideos($movieId) {
        try {
            // Appel à l'API TMDB pour les vidéos via notre méthode makeApiCall
            $url = $this->buildApiUrl("movie/$movieId/videos", ['language' => 'fr-FR']);
            $response = $this->makeApiCall($url);
            
            if (!$response) {
                error_log("Aucune vidéo trouvée ou erreur pour le film ID: " . $movieId);
                return null;
            }
            
            return json_decode(json_encode($response), true); // Convert to array
        } catch (Exception $e) {
            // Log l'erreur pour le débogage
            error_log("Exception dans getMovieVideos: " . $e->getMessage());
            return null;
        }
    }

    public function searchMovies($query, $page = 1) {
        $url = $this->buildApiUrl("search/movie", [
            'language' => 'fr-FR',
            'page' => $page,
            'query' => $query,
            'include_adult' => false
        ]);
        
        error_log("URL de recherche: " . $url); // Log pour debug
        
        $response = $this->makeApiCall($url);
        
        if (!$response || !isset($response->results)) {
            return null;
        }
        
        // Vérification supplémentaire côté serveur pour les films d'animation
        $filteredResults = [];
        foreach ($response->results as $movie) {
            if (isset($movie->genre_ids) && in_array(16, $movie->genre_ids)) {
                $filteredResults[] = $movie;
            }
        }
        
        // Remplacer les résultats originaux par les résultats filtrés
        $response->results = $filteredResults;
        $response->total_results = count($filteredResults);
        
        return $response;
    }

    private function isAnime($movieDetails) {
        // Vérifie si le film est une animation (pas seulement japonaise)
        $isAnimation = false;
        
        // Vérifie les genres
        if (isset($movieDetails->genres)) {
            foreach ($movieDetails->genres as $genre) {
                if ($genre->id === 16) { // 16 est l'ID du genre Animation
                    $isAnimation = true;
                    break;
                }
            }
        }
        
        // Pour cette application, considérer toutes les animations, pas uniquement japonaises
        return $isAnimation;
    }

    public function getMovieDetails($movieId) {
        $url = $this->buildApiUrl("movie/$movieId", ['language' => 'fr-FR']);
        $movieDetails = $this->makeApiCall($url);
        
        if ($movieDetails) {
            // Si les genres ne sont pas récupérés, considérer que c'est valide pour continuer
            if (!isset($movieDetails->genres) || $this->isAnime($movieDetails)) {
            return $movieDetails;
            }
            
            // Log pour debug
            error_log("Film non considéré comme animation: ID=$movieId, Genres=" . 
                     (isset($movieDetails->genres) ? json_encode($movieDetails->genres) : "non définis"));
        }
        
        return $movieDetails; // Retourner quand même les détails pour tester
    }

    /**
     * Supprime un commentaire et ses réponses
     */
    public function deleteComment($commentId) {
        try {
            // Commencer une transaction pour assurer l'intégrité
            $this->db->beginTransaction();
            
            // Supprimer d'abord les réponses associées à ce commentaire
            $stmtReplies = $this->db->prepare("DELETE FROM comment_replies WHERE comment_id = ?");
            $stmtReplies->execute([$commentId]);
            
            // Ensuite supprimer le commentaire lui-même
            $stmtComment = $this->db->prepare("DELETE FROM comments WHERE id = ?");
            $result = $stmtComment->execute([$commentId]);
            
            // Valider la transaction si tout s'est bien passé
            $this->db->commit();
            
            error_log("Commentaire ID $commentId supprimé avec succès");
            return $result;
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            error_log("Erreur lors de la suppression du commentaire ID $commentId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprime une réponse à un commentaire
     */
    public function deleteCommentReply($replyId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM comment_replies WHERE id = ?");
            $result = $stmt->execute([$replyId]);
            
            error_log("Réponse ID $replyId supprimée avec succès");
            return $result;
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de la réponse ID $replyId: " . $e->getMessage());
            return false;
        }
    }
}
?>
