<?php
// Définir les constantes globales
if (!defined('ROOT_PATH')) define('ROOT_PATH', __DIR__);
if (!defined('APP_PATH')) define('APP_PATH', __DIR__ . '/app');
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', APP_PATH . '/config');
if (!defined('VIEW_PATH')) define('VIEW_PATH', APP_PATH . '/Views');
if (!defined('ASSET_PATH')) define('ASSET_PATH', ROOT_PATH . '/assets');

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Activer l'affichage des erreurs en développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration de l'URL de base
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . '://' . $host);
}

// Rediriger vers l'application principale
require_once __DIR__ . '/app/public/index.php';
?>