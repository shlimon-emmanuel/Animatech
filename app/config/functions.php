<?php

/**
 * Fonction d'échappement HTML sécurisé
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Fonction de nettoyage des entrées
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $data;
}

/**
 * Validation sécurisée des URLs
 */
function is_safe_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) &&
           (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0);
}

/**
 * Génération d'un token CSRF
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validation d'un token CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Fonction de journalisation sécurisée
 */
function secure_log($message, $level = 'info') {
    $log_file = ROOT_PATH . '/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = sprintf("[%s] [%s] %s\n", 
        $timestamp,
        strtoupper($level),
        clean_input($message)
    );
    error_log($log_message, 3, $log_file);
} 