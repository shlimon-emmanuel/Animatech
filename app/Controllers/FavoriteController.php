<?php
// app/Controllers/FavoriteController.php

namespace App\Controllers;

use Exception;

class FavoriteController {
    private $favoriteModel;
    private $movieModel;

    public function __construct() {
        // Utiliser require_once au lieu d'autoloading
        require_once APP_PATH . '/Models/FavoriteModel.php';
        require_once APP_PATH . '/Models/MovieModel.php';
        
        $this->favoriteModel = new \App\Models\FavoriteModel();
        $this->movieModel = new \App\Models\MovieModel(TMDB_API_KEY);
    }

    /**
     * Ajoute un film aux favoris
     */
    public function addFavorite() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour ajouter un film aux favoris.";
            header('Location: index.php?action=login');
            exit;
        }

        // Vérifier si le formulaire a été soumis avec un ID de film
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'])) {
            $movieId = (int)$_POST['movie_id'];
            
            // Récupérer l'ID de l'utilisateur en fonction du format de session
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['user']['id'];
            
            error_log("FavoriteController::addFavorite - Tentative d'ajout du film $movieId pour l'utilisateur $userId");
            
            try {
                // Vérifier si le film existe déjà dans la base de données
                $movie = $this->movieModel->getMovieById($movieId);
                
                // Si le film n'est pas trouvé, afficher un message d'erreur
                if (!$movie) {
                    $_SESSION['error'] = "Le film demandé n'existe pas.";
                    header('Location: index.php');
                    exit;
                }
                
                // Ajouter le film aux favoris
                $success = $this->favoriteModel->addToFavorites($userId, $movieId);
                
                if ($success) {
                    $_SESSION['success'] = "Le film a été ajouté à vos favoris.";
                } else {
                    $_SESSION['error'] = "Une erreur est survenue lors de l'ajout du film aux favoris.";
                }
                
                // Rediriger vers la page du film
                header('Location: index.php?action=view&id=' . $movieId);
                exit;
            } catch (Exception $e) {
                error_log("FavoriteController::addFavorite - Exception: " . $e->getMessage());
                $_SESSION['error'] = "Une erreur est survenue: " . $e->getMessage();
                header('Location: index.php?action=view&id=' . $movieId);
                exit;
            }
        } else {
            // Si aucun ID de film n'est fourni, rediriger vers la page d'accueil
            $_SESSION['error'] = "Aucun film spécifié.";
            header('Location: index.php');
            exit;
        }
    }

    /**
     * Supprime un film des favoris
     */
    public function removeFavorite() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour retirer un film des favoris.";
            header('Location: index.php?action=login');
            exit;
        }

        // Vérifier si le formulaire a été soumis avec un ID de film
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'])) {
            $movieId = (int)$_POST['movie_id'];
            
            // Récupérer l'ID de l'utilisateur en fonction du format de session
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['user']['id'];
            
            error_log("FavoriteController::removeFavorite - Tentative de retrait du film $movieId pour l'utilisateur $userId");
            
            try {
                // Supprimer le film des favoris
                $success = $this->favoriteModel->removeFromFavorites($userId, $movieId);
                
                if ($success) {
                    $_SESSION['success'] = "Le film a été retiré de vos favoris.";
                } else {
                    $_SESSION['error'] = "Une erreur est survenue lors du retrait du film des favoris.";
                }
                
                // Rediriger vers la page du film
                header('Location: index.php?action=view&id=' . $movieId);
                exit;
            } catch (Exception $e) {
                error_log("FavoriteController::removeFavorite - Exception: " . $e->getMessage());
                $_SESSION['error'] = "Une erreur est survenue: " . $e->getMessage();
                header('Location: index.php?action=view&id=' . $movieId);
                exit;
            }
        } else {
            // Si aucun ID de film n'est fourni, rediriger vers la page d'accueil
            $_SESSION['error'] = "Aucun film spécifié.";
            header('Location: index.php');
            exit;
        }
    }

    /**
     * Affiche les films favoris de l'utilisateur
     */
    public function showFavorites() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Vous devez être connecté pour voir vos favoris.";
            header('Location: index.php?action=login');
            exit;
        }
        
        try {
            // Récupérer l'ID de l'utilisateur en fonction du format de session
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SESSION['user']['id'];
            
            // Récupérer les IDs des films favoris
            $favoriteMovieIds = $this->favoriteModel->getUserFavorites($userId);
            
            // Récupérer les détails de chaque film
            $favorites = [];
            foreach ($favoriteMovieIds as $movieId) {
                $movie = $this->movieModel->getMovieById($movieId);
                if ($movie) {
                    // Convertir l'objet en tableau associatif
                    $favorites[] = json_decode(json_encode($movie), true);
                }
            }
            
            // Afficher la vue des favoris
            require_once APP_PATH . '/Views/movies/favorites.php';
        } catch (Exception $e) {
            error_log("FavoriteController::showFavorites - Exception: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue lors du chargement de vos favoris.";
            header('Location: index.php');
            exit;
        }
    }
    
    /**
     * Vérifie si un film est dans les favoris de l'utilisateur
     */
    public function isFavorite($userId, $movieId) {
        return $this->favoriteModel->isFavorite($userId, $movieId);
    }
}
?> 