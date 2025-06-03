<?php

// Configuration de la base de données MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'animatech');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration de l'application
define('BASE_URL', 'http://localhost/Animatech/');
define('DEFAULT_CONTROLLER', 'HomeController');
define('DEFAULT_ACTION', 'index');

// Configurations diverses
define('UPLOAD_DIR', 'uploads/');
define('IMG_DIR', 'assets/img/');

// Constantes de sécurité
define('SECRET_KEY', 'votre_clé_secrète_ici');
define('SESSION_LIFETIME', 3600); // 1 heure 