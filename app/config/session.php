<?php
// Configuration sécurisée des sessions
if (session_status() === PHP_SESSION_NONE) {
    // Définir les paramètres de session avant de la démarrer
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    // Démarrer la session
    session_start();
} 