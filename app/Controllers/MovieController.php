<?php
// app/Controllers/MovieController.php

namespace App\Controllers;

use App\Models\MovieModel;
use Exception;

class MovieController {
    private $movieModel;

    public function __construct() {
        $this->movieModel = new MovieModel(TMDB_API_KEY);
    }

    public function listMovies() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $sortBy = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'popularity.desc';
            
            // Options de filtrage
            $filterOptions = [];
            if (isset($_GET['with_original_language'])) {
                $filterOptions['with_original_language'] = trim($_GET['with_original_language']);
            }
            if (isset($_GET['primary_release_date_gte'])) {
                $filterOptions['primary_release_date.gte'] = trim($_GET['primary_release_date_gte']);
            }
            if (isset($_GET['primary_release_date_lte'])) {
                $filterOptions['primary_release_date.lte'] = trim($_GET['primary_release_date_lte']);
            }
            if (isset($_GET['vote_average_gte'])) {
                $filterOptions['vote_average.gte'] = (float)$_GET['vote_average_gte'];
            }
            if (isset($_GET['year'])) {
                $filterOptions['year'] = (int)$_GET['year'];
            }
            
            // Si on a une recherche, on utilise searchMovies
            if (!empty($search)) {
                $results = $this->movieModel->searchMovies($search, $page);
                if (is_array($results)) {
                    $results = (object)$results;
                }
                if (is_array($results->results)) {
                    $results->results = array_map(function($movie) {
                        return is_array($movie) ? (object)$movie : $movie;
                    }, $results->results);
                }
            } 
            // Si on a des filtres, on utilise getFilteredMovies
            else if (!empty($filterOptions)) {
                $filterOptions['sort_by'] = $sortBy;
                $results = $this->movieModel->getFilteredMovies($page, $filterOptions);
            } 
            // Sinon, on récupère les films populaires avec le tri demandé
            else {
                $results = $this->movieModel->getPopularMovies($page, $sortBy);
            }
            
            // Ajouter un message de débogage
            if (!$results || empty($results->results)) {
                error_log("Aucun résultat trouvé pour la page " . $page);
            }
            
            // Récupération des options de tri pour l'interface
            $sortOptions = [
                'popularity.desc' => 'Popularité (décroissante)',
                'popularity.asc' => 'Popularité (croissante)',
                'release_date.desc' => 'Date de sortie (récent → ancien)',
                'release_date.asc' => 'Date de sortie (ancien → récent)',
                'vote_average.desc' => 'Note (décroissante)',
                'vote_average.asc' => 'Note (croissante)',
                'revenue.desc' => 'Recettes (décroissantes)',
                'original_title.asc' => 'Titre (A-Z)'
            ];
            
            // Variables pour l'interface
            $currentSort = $sortBy;
            $currentFilters = $filterOptions;
            
            // Récupération des films d'animation à venir
            $today = date('Y-m-d');
            $nextYear = date('Y-m-d', strtotime('+1 year'));
            
            // Paramètres pour récupérer des films d'animation à venir
            $upcomingParams = [
                'sort_by' => 'popularity.desc',
                'primary_release_date.gte' => $today,
                'primary_release_date.lte' => $nextYear,
                'with_genres' => 16,
                'vote_count.gte' => 0 // Inclure même les films avec peu de votes
            ];
            
            // Tenter de récupérer les films à venir
            $upcomingMovies = $this->movieModel->getFilteredMovies(1, $upcomingParams);
            
            // Plan B: si pas assez de films à venir, prendre les films d'animation populaires
            if (!$upcomingMovies || !isset($upcomingMovies->results) || count($upcomingMovies->results) < 3) {
                error_log("Pas assez de films à venir, on utilise les films populaires");
                
                // Récupérer une liste de films populaires
                $popularMovies = $this->movieModel->getPopularMovies(1, 'popularity.desc', 0);
                
                if (isset($popularMovies->results) && !empty($popularMovies->results)) {
                    if (!$upcomingMovies || !isset($upcomingMovies->results)) {
                        $upcomingMovies = $popularMovies;
                    } else {
                        // Fusionner les résultats
                        $upcomingMovies->results = array_merge(
                            $upcomingMovies->results, 
                            array_slice($popularMovies->results, 0, 3 - count($upcomingMovies->results))
                        );
                    }
                }
            }
            
            // Ensure upcoming movies are properly formatted
            if ($upcomingMovies && isset($upcomingMovies->results)) {
                if (is_array($upcomingMovies)) {
                    $upcomingMovies = (object)$upcomingMovies;
                }
                if (is_array($upcomingMovies->results)) {
                    $upcomingMovies->results = array_map(function($movie) {
                        return is_array($movie) ? (object)$movie : $movie;
                    }, $upcomingMovies->results);
                }
            }
            
            // Variables SEO
            $pageTitle = 'ANIMATECH - Application web de gestion et découverte de films d\'animation';
            $pageDescription = 'Application web développée en PHP MVC avec intégration API TMDB. Fonctionnalités : authentification, catalogue interactif, système de favoris, commentaires et notation. Base de données MySQL pour la gestion utilisateurs.';
            
            require_once APP_PATH . '/Views/movies/list.php';
        } catch (Exception $e) {
            error_log("Erreur dans listMovies: " . $e->getMessage());
            throw new Exception("Erreur lors du chargement des films : " . $e->getMessage());
        }
    }

    public function showMovieDetail($id) {
        try {
            $movie = $this->movieModel->getMovieById($id);
            
            // Vérifier si on a bien récupéré un film
            if (!$movie) {
                error_log("Film non trouvé - ID: $id");
                throw new Exception("Film non trouvé");
            }
            
            // Convertir l'objet en tableau associatif pour la vue
            $movie = json_decode(json_encode($movie), true);
            
            // Récupérer la bande-annonce
            $videos = $this->movieModel->getMovieVideos($id);
            $trailer = null;
            
            // Chercher une bande-annonce parmi les vidéos
            if ($videos && !empty($videos['results'])) {
                foreach ($videos['results'] as $video) {
                    if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
                        $trailer = $video;
                        break;
                    }
                }
            }
            
            $isFavorite = false;
            // Récupérer les commentaires
            $comments = $this->movieModel->getMovieComments($id);
            
            // Vérifier si l'utilisateur est connecté pour les favoris
            if (isset($_SESSION['user']) || isset($_SESSION['user_id'])) {
                // Déterminer l'ID utilisateur
                $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['user']['id'];
                
                // Utiliser le nouveau modèle de favoris
                require_once APP_PATH . '/Models/FavoriteModel.php';
                $favoriteModel = new \App\Models\FavoriteModel();
                
                // Vérifier si le film est en favoris et journaliser le résultat
                $isFavorite = $favoriteModel->isFavorite($userId, $id);
                error_log("showMovieDetail: Vérification favori pour userId=$userId, movieId=$id, résultat=" . ($isFavorite ? 'true' : 'false'));
            }
            
            // Variables SEO pour la page de détail
            $pageTitle = htmlspecialchars($movie['title']) . ' - Film d\'Animation | ANIMATECH';
            $pageDescription = 'Regardez ' . htmlspecialchars($movie['title']) . ' en streaming. ' . (isset($movie['overview']) ? htmlspecialchars(substr($movie['overview'], 0, 150)) . '...' : 'Film d\'animation disponible sur ANIMATECH.');
            
            // Inclure la vue de détail
            require_once APP_PATH . '/Views/movies/detail.php';
        } catch (Exception $e) {
            error_log("Erreur dans showMovieDetail: " . $e->getMessage());
            throw new Exception("Erreur lors du chargement du film : " . $e->getMessage());
        }
    }

    public function loadMoreMovies($page) {
        header('Content-Type: application/json');
        
        try {
            $sortBy = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'popularity.desc';
            $withGenres = isset($_GET['with_genres']) ? trim($_GET['with_genres']) : '16';
            
            // Options de filtrage
            $filterOptions = [
                'with_genres' => $withGenres,
                'sort_by' => $sortBy
            ];
            
            // Ajouter les autres filtres s'ils sont présents
            if (isset($_GET['with_original_language']) && !empty($_GET['with_original_language'])) {
                $filterOptions['with_original_language'] = trim($_GET['with_original_language']);
            }
            if (isset($_GET['primary_release_date_gte']) && !empty($_GET['primary_release_date_gte'])) {
                $filterOptions['primary_release_date.gte'] = trim($_GET['primary_release_date_gte']);
            }
            if (isset($_GET['primary_release_date_lte']) && !empty($_GET['primary_release_date_lte'])) {
                $filterOptions['primary_release_date.lte'] = trim($_GET['primary_release_date_lte']);
            }
            if (isset($_GET['vote_average_gte']) && !empty($_GET['vote_average_gte'])) {
                $filterOptions['vote_average.gte'] = (float)$_GET['vote_average_gte'];
            }
            if (isset($_GET['year']) && !empty($_GET['year'])) {
                $filterOptions['year'] = (int)$_GET['year'];
            }
            
            $search = isset($_GET['query']) ? trim($_GET['query']) : '';
            
            // Si on a une recherche, on utilise searchMovies
            if (!empty($search)) {
                $results = $this->movieModel->searchMovies($search, $page);
            } 
            // Sinon, on utilise getFilteredMovies
            else {
                $results = $this->movieModel->getFilteredMovies($page, $filterOptions);
            }
            
            // Vérification basique des résultats
            if (!$results || !isset($results->results)) {
                error_log("Aucun résultat retourné par l'API");
            }
            
            // Si aucun résultat n'est trouvé, retourner un tableau vide mais valide
            if (!$results || !isset($results->results)) {
                echo json_encode([
                    'movies' => [],
                    'total_pages' => 0,
                    'page' => $page
                ]);
                exit;
            }
            
            // Les résultats sont déjà filtrés par l'API avec with_genres=16
            // Pas besoin de re-filtrer, juste convertir le format
            $formattedResults = [];
            foreach ($results->results as $movie) {
                $formattedResults[] = $movie;
            }
            
            // Utiliser directement le nombre de pages de l'API
            $totalPages = isset($results->total_pages) ? $results->total_pages : 1;
            
            echo json_encode([
                'movies' => $formattedResults,
                'total_pages' => $totalPages,
                'page' => $page
            ]);
            
        } catch (Exception $e) {
            error_log("Erreur dans loadMoreMovies: " . $e->getMessage());
            echo json_encode([
                'error' => $e->getMessage(),
                'movies' => [],
                'total_pages' => 0,
                'page' => $page
            ]);
        }
        exit;
    }

    public function addFavorite() {
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'])) {
            $movieId = $_POST['movie_id'];
            // Utiliser user_id s'il existe, sinon utiliser user['id']
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['user']['id'];
            
            try {
                // Vérifier d'abord si ce film existe dans la base de données
                $movie = $this->movieModel->getMovieById($movieId);
                
                // Si le film n'existe pas dans notre base, nous devons l'ajouter d'abord
                if (empty($movie)) {
                    error_log("Film non trouvé dans la base de données, récupération depuis l'API - movieId: $movieId");
                    $movie = $this->movieModel->getMovieDetails($movieId);
                    if (!$movie) {
                        error_log("Erreur: Le film avec l'ID $movieId n'a pas pu être trouvé via l'API");
                        $_SESSION['error'] = "Erreur: Impossible de trouver ce film";
                        header('Location: index.php?action=view&id=' . $movieId);
                        exit;
                    }
                    error_log("Film récupéré depuis l'API avec succès - movieId: $movieId, titre: " . $movie->title);
                }
                
                // Vérifier si le film est déjà dans les favoris
                if ($this->movieModel->isFavorite($userId, $movieId)) {
                    error_log("Le film est déjà dans les favoris - userId: $userId, movieId: $movieId");
                    $_SESSION['success'] = "Ce film est déjà dans vos favoris";
                    header('Location: index.php?action=view&id=' . $movieId);
                    exit;
                }
                
                // Tenter d'ajouter aux favoris
                $result = $this->movieModel->addToFavorites($userId, $movieId);
                if ($result) {
                    error_log("Film ajouté aux favoris avec succès - userId: $userId, movieId: $movieId");
                    $_SESSION['success'] = "Film ajouté aux favoris";
                } else {
                    error_log("Échec de l'ajout aux favoris - userId: $userId, movieId: $movieId");
                    $_SESSION['error'] = "Erreur lors de l'ajout aux favoris";
                }
            } catch (Exception $e) {
                error_log("Exception dans addFavorite: " . $e->getMessage());
                $_SESSION['error'] = "Une erreur est survenue: " . $e->getMessage();
            }
            
            header('Location: index.php?action=view&id=' . $movieId);
            exit;
        }
    }

    public function removeFavorite() {
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'])) {
            $movieId = $_POST['movie_id'];
            // Utiliser user_id s'il existe, sinon utiliser user['id']
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['user']['id'];
            
            if ($this->movieModel->removeFromFavorites($userId, $movieId)) {
                $_SESSION['success'] = "Film retiré des favoris";
            } else {
                $_SESSION['error'] = "Erreur lors du retrait des favoris";
            }
            
            header('Location: index.php?action=view&id=' . $movieId);
            exit;
        }
    }

    public function showFavorites() {
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }

        try {
            // Utiliser user_id s'il existe, sinon utiliser user['id']
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['user']['id'];
            
            $favorites = $this->movieModel->getUserFavorites($userId);
            
            // Convertir les objets en tableaux associatifs pour la vue
            $formattedFavorites = [];
            foreach ($favorites as $movie) {
                if ($movie) {
                    // Convertir l'objet en tableau associatif
                    $formattedFavorites[] = json_decode(json_encode($movie), true);
                }
            }
            
            // Passer les favoris formatés à la vue
            $favorites = $formattedFavorites;
            
            require_once APP_PATH . '/Views/movies/favorites.php';
        } catch (Exception $e) {
            error_log("Erreur dans showFavorites: " . $e->getMessage());
            throw new Exception("Erreur lors du chargement des favoris : " . $e->getMessage());
        }
    }

    public function addComment() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $movieId = $_POST['movie_id'];
            // Ne pas utiliser htmlspecialchars pour éviter l'échappement des apostrophes
            $content = trim($_POST['content']);
            $rating = min(5, max(1, (int)$_POST['rating']));
            $userId = $_SESSION['user']['id'];

            if ($this->movieModel->addComment($userId, $movieId, $content, $rating)) {
                $_SESSION['success'] = "Commentaire ajouté avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout du commentaire";
            }

            header('Location: index.php?action=view&id=' . $movieId);
            exit;
        }
    }

    /**
     * Ajouter une réponse à un commentaire
     */
    public function addReply() {
        if (!isset($_SESSION['user'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour répondre']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
            $content = isset($_POST['content']) ? trim($_POST['content']) : '';
            $movieId = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;
            $userId = $_SESSION['user']['id'];
            
            // Log pour debug
            error_log("Tentative d'ajout de réponse - commentId: $commentId, movieId: $movieId, userId: $userId, content: " . substr($content, 0, 50));
            
            // Validation
            if (!$commentId || empty($content) || !$movieId) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Données manquantes']);
                exit;
            }
            
            try {
                // Ajouter la réponse
                $success = $this->movieModel->addCommentReply($commentId, $userId, $content);
                
                // Si c'est une requête AJAX
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    
                    if ($success) {
                        // Construire manuellement les détails de la réponse
                        $reply = [
                            'comment_id' => $commentId,
                            'user_id' => $userId,
                            'content' => $content,
                            'created_at' => date('Y-m-d H:i:s'),
                            'username' => $_SESSION['user']['username'],
                            'profile_picture' => isset($_SESSION['user']['profile_picture']) ? $_SESSION['user']['profile_picture'] : 'assets/img/default-profile.png'
                        ];
                        
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Réponse ajoutée avec succès', 
                            'reply' => $reply
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de la réponse']);
                    }
                    exit;
                }
                
                // Si c'est une requête normale
                if ($success) {
                    $_SESSION['success'] = "Réponse ajoutée avec succès";
                } else {
                    $_SESSION['error'] = "Erreur lors de l'ajout de la réponse";
                }
                
                header('Location: index.php?action=view&id=' . $movieId);
                exit;
            } catch (Exception $e) {
                error_log("Exception dans addReply: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur interne du serveur']);
                exit;
            }
        }
    }

    public function search() {
        try {
            $query = isset($_GET['query']) ? trim($_GET['query']) : '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            
            $results = $this->movieModel->searchMovies($query, $page);
            
            // Pour la recherche, on garde le filtrage car searchMovies ne filtre pas automatiquement par genre
            $filteredResults = [];
            
            if ($results && isset($results->results)) {
                foreach ($results->results as $movie) {
                    // Vérifier que le film a le genre animation dans ses genres
                    $isAnimation = false;
                    
                    // Vérifier dans les genres si disponibles
                    if (isset($movie->genre_ids) && is_array($movie->genre_ids)) {
                        if (in_array(16, $movie->genre_ids)) { // 16 = Animation
                            $isAnimation = true;
                        }
                    }
                    
                    // Si c'est un film d'animation, l'ajouter aux résultats filtrés
                    if ($isAnimation) {
                        $filteredResults[] = $movie;
                    }
                }
            }
            
            // Pour la recherche, utiliser une estimation basée sur les résultats trouvés
            $totalPages = 1;
            if ($results && isset($results->total_pages) && count($filteredResults) > 0) {
                // Si on a trouvé des films d'animation, estimer qu'il y en a autant sur les autres pages
                $totalPages = $results->total_pages;
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'movies' => $filteredResults,
                'total_pages' => $totalPages
            ]);
            exit;
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Récupérer les films d'animation populaires à venir
     */
    public function getUpcomingPopular() {
        try {
            // Obtenir la date du jour au format YYYY-MM-DD
            $today = date('Y-m-d');
            
            // Configurer les paramètres pour l'API TMDB
            // Utiliser directement la méthode getFilteredMovies qui fonctionne déjà
            $params = [
                'sort_by' => 'popularity.desc',
                'primary_release_date.gte' => $today,
                'with_genres' => 16  // 16 = Animation
            ];
            
            $results = $this->movieModel->getFilteredMovies(1, $params);
            
            if (!$results) {
                throw new Exception('Erreur lors de la récupération des données');
            }
            
            // Envoyer une réponse JSON
            header('Content-Type: application/json');
            echo json_encode([
                'movies' => $results->results,
                'total_results' => $results->total_results ?? 0,
                'total_pages' => $results->total_pages ?? 0
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Supprimer un commentaire (réservé aux admins et superadmins)
     */
    public function deleteComment() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Vérifier si l'utilisateur est admin ou superadmin
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : 
               (isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : '');
        
        if ($role !== 'admin' && $role !== 'superadmin') {
            $_SESSION['error'] = "Vous n'avez pas les droits pour effectuer cette action";
            header('Location: index.php');
            exit;
        }
        
        // Vérifier si l'ID du commentaire est fourni
        if (!isset($_GET['comment_id']) || !isset($_GET['movie_id'])) {
            $_SESSION['error'] = "Paramètres manquants";
            header('Location: index.php');
            exit;
        }
        
        $commentId = (int)$_GET['comment_id'];
        $movieId = (int)$_GET['movie_id'];
        
        // Supprimer le commentaire
        if ($this->movieModel->deleteComment($commentId)) {
            $_SESSION['success'] = "Commentaire supprimé avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du commentaire";
        }
        
        // Rediriger vers la page du film
        header('Location: index.php?action=view&id=' . $movieId);
        exit;
    }
    
    /**
     * Supprimer une réponse à un commentaire (réservé aux admins et superadmins)
     */
    public function deleteCommentReply() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Vérifier si l'utilisateur est admin ou superadmin
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : 
               (isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : '');
        
        if ($role !== 'admin' && $role !== 'superadmin') {
            $_SESSION['error'] = "Vous n'avez pas les droits pour effectuer cette action";
            header('Location: index.php');
            exit;
        }
        
        // Vérifier si l'ID de la réponse est fourni
        if (!isset($_GET['reply_id']) || !isset($_GET['movie_id'])) {
            $_SESSION['error'] = "Paramètres manquants";
            header('Location: index.php');
            exit;
        }
        
        $replyId = (int)$_GET['reply_id'];
        $movieId = (int)$_GET['movie_id'];
        
        // Supprimer la réponse
        if ($this->movieModel->deleteCommentReply($replyId)) {
            $_SESSION['success'] = "Réponse supprimée avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de la réponse";
        }
        
        // Rediriger vers la page du film
        header('Location: index.php?action=view&id=' . $movieId);
        exit;
    }
}
?>
