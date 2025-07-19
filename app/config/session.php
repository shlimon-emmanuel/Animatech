<?php
// Configuration sécurisée des sessions
if (session_status() === PHP_SESSION_NONE) {
    // Valeurs par défaut si les constantes ne sont pas définies
    $sessionHttpOnly = defined('SESSION_HTTPONLY') ? SESSION_HTTPONLY : true;
    $sessionSecure = defined('SESSION_SECURE') ? SESSION_SECURE : false;
    $sessionSameSite = defined('SESSION_SAMESITE') ? SESSION_SAMESITE : 'Lax';
    $sessionLifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 120;
    $storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(dirname(__DIR__)) . '/storage';
    
    // Définir les paramètres de session avant de la démarrer
    ini_set('session.cookie_httponly', $sessionHttpOnly);
    ini_set('session.cookie_secure', $sessionSecure);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', $sessionSameSite);
    ini_set('session.gc_maxlifetime', $sessionLifetime * 60);
    ini_set('session.cookie_lifetime', $sessionLifetime * 60);
    
    // Configuration du gestionnaire de session
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', $storagePath . '/sessions');
    
    // Créer le dossier des sessions s'il n'existe pas
    if (!file_exists($storagePath . '/sessions')) {
        mkdir($storagePath . '/sessions', 0755, true);
    }
    
    // Démarrer la session
    session_start();
    
    // Régénérer l'ID de session périodiquement
    if (!isset($_SESSION['last_regeneration']) || 
        time() - $_SESSION['last_regeneration'] > ($sessionLifetime * 30)) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
} 