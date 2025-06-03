<?php
// Définir les chemins si ce n'est pas déjà fait
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(dirname(__DIR__)));
if (!defined('APP_PATH')) define('APP_PATH', dirname(__DIR__));
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', APP_PATH . '/config');
if (!defined('VIEW_PATH')) define('VIEW_PATH', APP_PATH . '/Views');
if (!defined('ASSET_PATH')) define('ASSET_PATH', ROOT_PATH . '/assets');

// Inclure la configuration des sessions
require_once APP_PATH . '/config/session.php';

// Activer l'affichage des erreurs en développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si l'URL de base est définie
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . '://' . $host);
}

// Inclure la configuration
if (file_exists(APP_PATH . '/config/config.php')) {
    require_once APP_PATH . '/config/config.php';
} else {
    die("Le fichier de configuration est manquant.");
}

// Vérifier la configuration de la base de données
try {
    $testConnection = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Vérifier si la base de données existe
    $dbResult = $testConnection->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($dbResult->rowCount() == 0) {
        // La base de données n'existe pas, la créer
        $testConnection->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8 COLLATE utf8_general_ci");
        echo "<div style='background-color: #dff0d8; color: #3c763d; padding: 15px; margin: 15px 0; border-radius: 4px;'>
                Base de données '" . DB_NAME . "' créée avec succès.
              </div>";
        
        // Sélectionner la base de données
        $testConnection->exec("USE `" . DB_NAME . "`");
        
        // Créer les tables nécessaires
        $tables = [
            "users" => "CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL,
                `email` varchar(100) NOT NULL,
                `password` varchar(255) NOT NULL,
                `profile_picture` varchar(255) DEFAULT 'assets/img/default-profile.png',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `email` (`email`),
                UNIQUE KEY `username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
            
            "movies" => "CREATE TABLE IF NOT EXISTS `movies` (
                `id` int(11) NOT NULL,
                `title` varchar(255) NOT NULL,
                `overview` text,
                `poster_path` varchar(255) DEFAULT NULL,
                `backdrop_path` varchar(255) DEFAULT NULL,
                `release_date` date DEFAULT NULL,
                `popularity` float DEFAULT NULL,
                `vote_average` float DEFAULT NULL,
                `vote_count` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
            
            "favorites" => "CREATE TABLE IF NOT EXISTS `favorites` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `movie_id` int(11) NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `user_movie` (`user_id`,`movie_id`),
                KEY `movie_id` (`movie_id`),
                CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
            
            "comments" => "CREATE TABLE IF NOT EXISTS `comments` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `movie_id` int(11) NOT NULL,
                `content` text NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `movie_id` (`movie_id`),
                CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
            
            "comment_replies" => "CREATE TABLE IF NOT EXISTS `comment_replies` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `comment_id` int(11) NOT NULL,
                `user_id` int(11) NOT NULL,
                `content` text NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `comment_id` (`comment_id`),
                KEY `user_id` (`user_id`),
                CONSTRAINT `comment_replies_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
                CONSTRAINT `comment_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        ];
        
        // Créer chaque table
        foreach ($tables as $tableName => $tableSQL) {
            $testConnection->exec($tableSQL);
            echo "<div style='background-color: #dff0d8; color: #3c763d; padding: 5px; margin: 5px 0; border-radius: 4px;'>
                    Table '$tableName' créée avec succès.
                  </div>";
        }
    }
    // Fermer la connexion de test
    $testConnection = null;
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Inclure les modèles et contrôleurs nécessaires
if (file_exists(APP_PATH . '/Models/JsonDbModel.php')) {
    require_once APP_PATH . '/Models/JsonDbModel.php';
} else {
    die("Le modèle JsonDbModel est manquant.");
}

if (file_exists(APP_PATH . '/Models/MovieModel.php')) {
    require_once APP_PATH . '/Models/MovieModel.php';
} else {
    die("Le modèle MovieModel est manquant.");
}

if (file_exists(APP_PATH . '/Models/UserModel.php')) {
    require_once APP_PATH . '/Models/UserModel.php';
} else {
    die("Le modèle UserModel est manquant.");
}

if (file_exists(APP_PATH . '/Controllers/MovieController.php')) {
    require_once APP_PATH . '/Controllers/MovieController.php';
} else {
    die("Le contrôleur MovieController est manquant.");
}

if (file_exists(APP_PATH . '/Controllers/AuthController.php')) {
    require_once APP_PATH . '/Controllers/AuthController.php';
} else {
    die("Le contrôleur AuthController est manquant.");
}

if (file_exists(APP_PATH . '/Controllers/FavoriteController.php')) {
    require_once APP_PATH . '/Controllers/FavoriteController.php';
} else {
    die("Le contrôleur FavoriteController est manquant.");
}

// Inclure le contrôleur d'administration
if (file_exists(APP_PATH . '/Controllers/AdminController.php')) {
    require_once APP_PATH . '/Controllers/AdminController.php';
} else {
    die("Le contrôleur AdminController est manquant.");
}

try {
    // Créer les instances des contrôleurs
    $movieController = new App\Controllers\MovieController();
    $authController = new App\Controllers\AuthController();
    $favoriteController = new App\Controllers\FavoriteController();
    $adminController = new App\Controllers\AdminController();

    // Gérer les routes
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'home':
                $movieController->listMovies();
                break;
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
            case 'edit-profile':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $authController->updateProfile();
                } else {
                    require_once APP_PATH . '/Views/auth/edit-profile.php';
                }
                break;
            case 'update-profile':
                $authController->updateProfile();
                break;
            case 'loadMore':
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $movieController->loadMoreMovies($page);
                break;
            case 'favorites':
                $favoriteController->showFavorites();
                break;
            case 'addFavorite':
                $favoriteController->addFavorite();
                break;
            case 'removeFavorite':
                $favoriteController->removeFavorite();
                break;
            case 'addComment':
                $movieController->addComment();
                break;
            case 'addReply':
                $movieController->addReply();
                break;
            case 'deleteComment':
                $movieController->deleteComment();
                break;
            case 'deleteCommentReply':
                $movieController->deleteCommentReply();
                break;
            case 'search':
                $movieController->search();
                break;
            case 'getUpcomingPopular':
                $movieController->getUpcomingPopular();
                break;
            
            // Routes d'administration
            case 'admin':
                $subaction = $_GET['subaction'] ?? 'dashboard';
                switch ($subaction) {
                    case 'dashboard':
                        $adminController->dashboard();
                        break;
                    case 'users':
                        $adminController->manageUsers();
                        break;
                    case 'editUser':
                        $adminController->editUser();
                        break;
                    case 'updateUser':
                        $adminController->updateUser();
                        break;
                    case 'deleteUser':
                        $adminController->deleteUser();
                        break;
                    default:
                        $adminController->dashboard();
                }
                break;
            
            // Route pour les mentions légales
            case 'mentions-legales':
                require_once APP_PATH . '/Views/legal/mentions-legales.php';
                break;
            
            // Route pour la page RGPD
            case 'rgpd':
                require_once APP_PATH . '/Views/legal/rgpd.php';
                break;
            
            default:
                $movieController->listMovies();
        }
    } else {
        // Page d'accueil par défaut
        $movieController->listMovies();
    }
} catch (Exception $e) {
    // Gérer les erreurs
    echo '<div style="color: red; padding: 20px; background-color: #ffe6e6; margin: 20px; border-radius: 5px;">';
    echo '<h2>Erreur</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
    if (ini_get('display_errors')) {
        echo '<p>Dans ' . $e->getFile() . ' à la ligne ' . $e->getLine() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }
    echo '</div>';
}
