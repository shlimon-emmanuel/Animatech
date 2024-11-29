<?php
// Configuration de base
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(dirname(__DIR__)));
if (!defined('APP_PATH')) define('APP_PATH', dirname(__DIR__));
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', APP_PATH . '/config');
if (!defined('ASSETS_PATH')) define('ASSETS_PATH', ROOT_PATH . '/assets');

// Configuration API TMDB
define('OMDB_API_KEY', 'e592f1f6d22e8a0437cd5fe1db8915c0');
define('OMDB_API_URL', 'https://api.themoviedb.org/3/');

// Configuration base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'userauth');
define('DB_USER', 'root');
define('DB_PASS', '');
?>
