<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir les chemins
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', dirname(__DIR__));

// Inclure la configuration
require_once APP_PATH . '/config/config.php';

// Inclure les modèles et contrôleurs nécessaires
require_once APP_PATH . '/Models/MovieModel.php';
require_once APP_PATH . '/Models/UserModel.php';
require_once APP_PATH . '/Controllers/MovieController.php';
require_once APP_PATH . '/Controllers/AuthController.php';

try {
    // Créer les instances des contrôleurs
    $movieController = new App\Controllers\MovieController();
    $authController = new App\Controllers\AuthController();

    // Gérer les routes
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'login':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $authController->login();
                } else {
                    $authController->showLoginForm();
                }
                break;
            case 'register':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $authController->register();
                } else {
                    $authController->showRegisterForm();
                }
                break;
            case 'logout':
                $authController->logout();
                break;
            case 'view':
                if (isset($_GET['id'])) {
                    $movieController->showMovieDetail($_GET['id']);
                }
                break;
            case 'profile':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $authController->updateProfile();
                } else {
                    $authController->showProfile();
                }
                break;
            case 'loadMore':
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $movieController->loadMoreMovies($page);
                break;
            case 'favorites':
                $movieController->showFavorites();
                break;
            case 'addFavorite':
                $movieController->addFavorite();
                break;
            case 'removeFavorite':
                $movieController->removeFavorite();
                break;
            case 'addComment':
                $movieController->addComment();
                break;
            case 'search':
                $movieController->search();
                break;
            default:
                $movieController->listMovies();
        }
    } else {
        $movieController->listMovies();
    }
} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage();
}
