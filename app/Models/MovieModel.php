<?php
// app/Models/MovieModel.php

namespace App\Models;

use PDO;
use PDOException;

class MovieModel {
    private $apiKey;
    private $baseUrl;
    private $db;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->baseUrl = OMDB_API_URL;
        
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

    public function getPopularMovies($page = 1) {
        $url = $this->baseUrl . "discover/movie?api_key=" . $this->apiKey 
             . "&language=fr-FR"
             . "&page=" . $page 
             . "&sort_by=popularity.desc"
             . "&with_genres=16"  // Genre Animation uniquement
             . "&include_adult=false"
             . "&vote_count.gte=100"; // Pour avoir des films avec un minimum de votes
        
        $response = $this->makeApiCall($url);
        
        if (!$response || !isset($response->results)) {
            return null;
        }
        
        return $response;
    }

    public function getMovieById($id) {
        $url = $this->baseUrl . "movie/" . $id . "?api_key=" . $this->apiKey . "&language=fr-FR";
        return $this->makeApiCall($url);
    }

    private function makeApiCall($url) {
        $response = file_get_contents($url);
        if ($response === false) {
            error_log("Erreur lors de l'appel API: " . $url);
            return null;
        }
        return json_decode($response);
    }

    public function addToFavorites($userId, $movieId) {
        try {
            $stmt = $this->db->prepare("INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)");
            return $stmt->execute([$userId, $movieId]);
        } catch (PDOException $e) {
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
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND movie_id = ?");
            $stmt->execute([$userId, $movieId]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getUserFavorites($userId) {
        try {
            $stmt = $this->db->prepare("SELECT movie_id FROM favorites WHERE user_id = ?");
            $stmt->execute([$userId]);
            $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $movies = [];
            foreach ($favorites as $movieId) {
                $movies[] = $this->getMovieById($movieId);
            }
            return $movies;
        } catch (PDOException $e) {
            return [];
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
                SELECT c.*, u.username 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.movie_id = ? 
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$movieId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getMovieVideos($movieId) {
        try {
            // Appel direct à l'API TMDB pour les vidéos
            $url = "https://api.themoviedb.org/3/movie/{$movieId}/videos?api_key={$this->apiKey}";
            $response = file_get_contents($url);
            $videos = json_decode($response, true);

            if (!isset($videos['results']) || empty($videos['results'])) {
                return null;
            }

            // Parcourir tous les résultats pour trouver une bande-annonce
            foreach ($videos['results'] as $video) {
                // Vérifier si c'est une bande-annonce YouTube
                if ($video['site'] === 'YouTube' && 
                    ($video['type'] === 'Trailer' || $video['type'] === 'Teaser')) {
                    return [
                        'key' => $video['key'],
                        'name' => $video['name'],
                        'type' => $video['type']
                    ];
                }
            }

            // Si aucune bande-annonce n'est trouvée, retourner la première vidéo YouTube disponible
            foreach ($videos['results'] as $video) {
                if ($video['site'] === 'YouTube') {
                    return [
                        'key' => $video['key'],
                        'name' => $video['name'],
                        'type' => $video['type']
                    ];
                }
            }

            return null;
        } catch (Exception $e) {
            // Log l'erreur pour le débogage
            error_log("Erreur lors de la récupération des vidéos : " . $e->getMessage());
            return null;
        }
    }

    public function searchMovies($query, $page = 1) {
        $url = $this->baseUrl . "search/movie?api_key=" . $this->apiKey 
             . "&language=fr-FR"
             . "&page=" . $page 
             . "&query=" . urlencode($query)
             . "&with_genres=16"; // Genre Animation
        
        $response = $this->makeApiCall($url);
        
        if (!$response || !isset($response->results)) {
            return null;
        }
        
        return $response;
    }

    private function isAnime($movieDetails) {
        // Vérifie si le film est une animation japonaise
        $isAnimation = false;
        $isJapanese = false;
        
        // Vérifie les genres
        if (isset($movieDetails['genres'])) {
            foreach ($movieDetails['genres'] as $genre) {
                if ($genre['id'] === 16) { // 16 est l'ID du genre Animation
                    $isAnimation = true;
                    break;
                }
            }
        }
        
        // Vérifie la langue originale
        if (isset($movieDetails['original_language']) && $movieDetails['original_language'] === 'ja') {
            $isJapanese = true;
        }
        
        return $isAnimation && $isJapanese;
    }

    public function getMovieDetails($movieId) {
        $url = $this->baseUrl . "movie/" . $movieId . "?api_key=" . $this->apiKey . "&language=fr-FR";
        $movieDetails = $this->makeApiCall($url);
        
        if ($movieDetails && $this->isAnime($movieDetails)) {
            return $movieDetails;
        }
        return null;
    }
}
?>
