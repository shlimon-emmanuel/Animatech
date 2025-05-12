<?php
// Configuration de base - vérifier si les constantes sont déjà définies
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(dirname(__DIR__)));
if (!defined('APP_PATH')) define('APP_PATH', dirname(__DIR__));
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', APP_PATH . '/config');
if (!defined('ASSETS_PATH')) define('ASSETS_PATH', ROOT_PATH . '/assets');

// Détection de l'environnement (development ou production)
if (!defined('APP_ENV')) {
    // Définir 'production' lors du déploiement
    define('APP_ENV', 'development');
}

// Configuration API TMDB - Utiliser des variables d'environnement pour la production
if (!defined('OMDB_API_KEY')) {
    // En production, cette clé devrait être stockée de façon sécurisée
    // et non directement dans le code source
    if (APP_ENV === 'production') {
        define('OMDB_API_KEY', getenv('TMDB_API_KEY') ?: 'e592f1f6d22e8a0437cd5fe1db8915c0');
    } else {
        define('OMDB_API_KEY', 'e592f1f6d22e8a0437cd5fe1db8915c0');
    }
}
if (!defined('OMDB_API_URL')) define('OMDB_API_URL', 'https://api.themoviedb.org/3/');

// Configuration base de données
if (!defined('DB_HOST')) {
    if (APP_ENV === 'production') {
        define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    } else {
        define('DB_HOST', 'localhost');
    }
}
if (!defined('DB_NAME')) {
    if (APP_ENV === 'production') {
        define('DB_NAME', getenv('DB_NAME') ?: 'userauth');
    } else {
        define('DB_NAME', 'userauth');
    }
}
if (!defined('DB_USER')) {
    if (APP_ENV === 'production') {
        define('DB_USER', getenv('DB_USER') ?: 'root');
    } else {
        define('DB_USER', 'root');
    }
}
if (!defined('DB_PASS')) {
    if (APP_ENV === 'production') {
        define('DB_PASS', getenv('DB_PASS') ?: '');
    } else {
        define('DB_PASS', '');
    }
}

// Chemins de l'application
if (!defined('VIEW_PATH')) define('VIEW_PATH', APP_PATH . '/Views');

// Configuration de l'URL de base
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . '://' . $host);
}

// Configuration générale
if (!defined('SITE_NAME')) define('SITE_NAME', 'ANIMATECH');
if (!defined('DEFAULT_CONTROLLER')) define('DEFAULT_CONTROLLER', 'MovieController');
if (!defined('DEFAULT_ACTION')) define('DEFAULT_ACTION', 'listMovies');

// Configuration des erreurs
if (APP_ENV === 'production') {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Création du dossier de logs s'il n'existe pas
if (!file_exists(ROOT_PATH . '/logs')) {
    mkdir(ROOT_PATH . '/logs', 0755, true);
}
?>
