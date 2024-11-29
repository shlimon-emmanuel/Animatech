<?php
// app/Controllers/MovieController.php

namespace App\Controllers;

use App\Models\MovieModel;
use Exception;

class MovieController {
    private $movieModel;

    public function __construct() {
        $this->movieModel = new MovieModel(OMDB_API_KEY);
    }

    public function listMovies() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            if (!empty($search)) {
                $results = $this->movieModel->searchMovies($search, $page);
            } else {
                $results = $this->movieModel->getPopularMovies($page);
            }
            
            // Ajouter un message de débogage
            if (!$results || empty($results->results)) {
                error_log("Aucun résultat trouvé pour la page " . $page);
            }
            
            require_once APP_PATH . '/Views/movies/list.php';
        } catch (Exception $e) {
            error_log("Erreur dans listMovies: " . $e->getMessage());
            throw new Exception("Erreur lors du chargement des films : " . $e->getMessage());
        }
    }

    public function showMovieDetail($id) {
        try {
            $movie = $this->movieModel->getMovieById($id);
            $trailer = $this->movieModel->getMovieVideos($id);
            $isFavorite = false;
            $comments = [];
            
            if (isset($_SESSION['user'])) {
                $isFavorite = $this->movieModel->isFavorite($_SESSION['user']['id'], $id);
                $comments = $this->movieModel->getMovieComments($id);
            }
            
            require_once APP_PATH . '/app//Views/movies/detail.php';
        } catch (Exception $e) {
            throw new Exception("Erreur lors du chargement du film : " . $e->getMessage());
        }
    }

    public function loadMoreMovies($page) {
        try {
            $movies = $this->movieModel->getPopularMovies($page);
            header('Content-Type: application/json');
            echo json_encode(['movies' => $movies]);
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function addFavorite() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'])) {
            $movieId = $_POST['movie_id'];
            $userId = $_SESSION['user']['id'];
            
            if ($this->movieModel->addToFavorites($userId, $movieId)) {
                $_SESSION['success'] = "Film ajouté aux favoris";
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout aux favoris";
            }
            
            header('Location: index.php?action=view&id=' . $movieId);
            exit;
        }
    }

    public function removeFavorite() {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'])) {
            $movieId = $_POST['movie_id'];
            $userId = $_SESSION['user']['id'];
            
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
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }

        try {
            $favorites = $this->movieModel->getUserFavorites($_SESSION['user']['id']);
            require_once APP_PATH . '/Views/movies/favorites.php';
        } catch (Exception $e) {
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
            $content = htmlspecialchars($_POST['content']);
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

    public function search() {
        try {
            $query = isset($_GET['query']) ? trim($_GET['query']) : '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            
            $results = $this->movieModel->searchMovies($query, $page);
            
            header('Content-Type: application/json');
            echo json_encode([
                'movies' => $results ? $results->results : [],
                'total_pages' => $results ? $results->total_pages : 0
            ]);
            exit;
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}
?>
