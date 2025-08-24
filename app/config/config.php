<?php
// Charger les variables d'environnement depuis .env si le fichier existe
if (file_exists(ROOT_PATH . '/.env')) {
    $envFile = file_get_contents(ROOT_PATH . '/.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// Configuration de base
if (!defined('APP_ENV')) define('APP_ENV', 'production');
if (!defined('APP_DEBUG')) define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// Configuration API TMDB
if (!defined('TMDB_API_KEY')) define('TMDB_API_KEY', getenv('TMDB_API_KEY') ?: '');
if (!defined('TMDB_API_URL')) define('TMDB_API_URL', getenv('TMDB_API_URL') ?: 'https://api.themoviedb.org/3/');

// Configuration base de données
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_DATABASE') ?: 'userauth');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USERNAME') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASSWORD') ?: '');

// Configuration des chemins
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(dirname(__DIR__)));
if (!defined('APP_PATH')) define('APP_PATH', dirname(__DIR__));
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', APP_PATH . '/config');
if (!defined('VIEW_PATH')) define('VIEW_PATH', APP_PATH . '/Views');
if (!defined('ASSETS_PATH')) define('ASSETS_PATH', ROOT_PATH . '/assets');
if (!defined('STORAGE_PATH')) define('STORAGE_PATH', ROOT_PATH . '/storage');
if (!defined('CACHE_PATH')) define('CACHE_PATH', STORAGE_PATH . '/cache');

// Configuration de l'URL de base
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = getenv('APP_URL') ?: ($protocol . '://' . $host);
    define('BASE_URL', rtrim($baseUrl, '/'));
}

// Configuration du site
if (!defined('SITE_NAME')) define('SITE_NAME', 'ANIMATECH');
if (!defined('DEFAULT_CONTROLLER')) define('DEFAULT_CONTROLLER', 'MovieController');
if (!defined('DEFAULT_ACTION')) define('DEFAULT_ACTION', 'listMovies');

// Configuration du cache
if (!defined('CACHE_DRIVER')) define('CACHE_DRIVER', getenv('CACHE_DRIVER') ?: 'file');
if (!defined('CACHE_PREFIX')) define('CACHE_PREFIX', getenv('CACHE_PREFIX') ?: 'animatech_');
if (!defined('CACHE_LIFETIME')) define('CACHE_LIFETIME', getenv('CACHE_LIFETIME') ?: 3600);

// Configuration des sessions
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 120);
if (!defined('SESSION_SECURE')) define('SESSION_SECURE', getenv('SESSION_SECURE') === 'true');
if (!defined('SESSION_HTTPONLY')) define('SESSION_HTTPONLY', getenv('SESSION_HTTPONLY') === 'true');
if (!defined('SESSION_SAMESITE')) define('SESSION_SAMESITE', getenv('SESSION_SAMESITE') ?: 'Lax');

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

// Création des dossiers nécessaires
$directories = [
    ROOT_PATH . '/logs',
    STORAGE_PATH,
    CACHE_PATH
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Configuration CORS pour les requêtes AJAX
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $allowedOrigins = [
        BASE_URL
    ];
    
    if (in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
    }
    exit(0);
}
?>
