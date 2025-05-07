<?php
// Configuration de base - vérifier si les constantes sont déjà définies
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(dirname(__DIR__)));
if (!defined('APP_PATH')) define('APP_PATH', dirname(__DIR__));
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', APP_PATH . '/config');
if (!defined('ASSETS_PATH')) define('ASSETS_PATH', ROOT_PATH . '/assets');

// Configuration API TMDB
if (!defined('OMDB_API_KEY')) define('OMDB_API_KEY', 'e592f1f6d22e8a0437cd5fe1db8915c0');
if (!defined('OMDB_API_URL')) define('OMDB_API_URL', 'https://api.themoviedb.org/3/');

// Configuration base de données
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'userauth');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

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

// Clé API TMDB (si nécessaire)
if (!defined('TMDB_API_KEY')) define('TMDB_API_KEY', ''); // À remplir si nécessaire
?>
