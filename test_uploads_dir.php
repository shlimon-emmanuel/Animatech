<?php
// Script pour vérifier les dossiers d'upload et leur permission
// Placez ce fichier à la racine du projet et exécutez-le

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir des styles pour une meilleure lisibilité
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h1, h2 { color: #333; }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 5px 0; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
</style>';

echo '<h1>Test des dossiers d\'upload</h1>';

// Dossiers à vérifier
$directories = [
    'assets/uploads',
    'assets/uploads/profiles',
    'assets/img'
];

// Vérifier chaque dossier
foreach ($directories as $dir) {
    echo "<h2>Vérification du dossier : $dir</h2>";
    
    if (!file_exists($dir)) {
        echo "<div class='error'>Le dossier n'existe pas. Tentative de création...</div>";
        
        if (mkdir($dir, 0777, true)) {
            echo "<div class='success'>Dossier créé avec succès!</div>";
        } else {
            echo "<div class='error'>Impossible de créer le dossier. Erreur : " . error_get_last()['message'] . "</div>";
            echo "<div class='info'>Essayez de le créer manuellement avec la commande : <code>mkdir -p $dir</code></div>";
        }
    } else {
        echo "<div class='success'>Le dossier existe.</div>";
        
        // Vérifier les permissions
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "<div class='info'>Permissions du dossier : $perms</div>";
        
        if (!is_writable($dir)) {
            echo "<div class='error'>Le dossier n'est pas accessible en écriture!</div>";
            echo "<div class='info'>Essayez de changer les permissions avec : <code>chmod 777 $dir</code></div>";
        } else {
            echo "<div class='success'>Le dossier est accessible en écriture.</div>";
        }
    }
    
    echo "<hr>";
}

// Vérifier si le fichier par défaut existe
$defaultProfilePic = 'assets/img/default-profile.png';
echo "<h2>Vérification de l'image de profil par défaut</h2>";

if (!file_exists($defaultProfilePic)) {
    echo "<div class='error'>L'image de profil par défaut n'existe pas : $defaultProfilePic</div>";
    echo "<div class='info'>Assurez-vous que ce fichier existe, car il est utilisé comme image par défaut.</div>";
} else {
    echo "<div class='success'>L'image de profil par défaut existe.</div>";
}

// Vérifier la taille maximale d'upload
echo "<h2>Configuration PHP pour les uploads</h2>";
echo "<div class='info'>Taille maximale d'upload : " . ini_get('upload_max_filesize') . "</div>";
echo "<div class='info'>Taille maximale des données POST : " . ini_get('post_max_size') . "</div>";
echo "<div class='info'>Dossier temporaire d'upload : " . ini_get('upload_tmp_dir') . "</div>";

// Vérifier la configuration de session
echo "<h2>Configuration de la session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Vérifier la configuration des extensions PHP
echo "<h2>Extensions PHP requises</h2>";
$requiredExtensions = ['gd', 'fileinfo', 'pdo', 'pdo_mysql'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='success'>L'extension $ext est chargée.</div>";
    } else {
        echo "<div class='error'>L'extension $ext n'est pas chargée!</div>";
    }
}

// Vérifier la connexion à la base de données
echo "<h2>Test de connexion à la base de données</h2>";
echo "<div class='info'>Vérification de la connexion à la base de données...</div>";

try {
    require_once 'app/config/config.php';
    
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<div class='success'>Connexion à la base de données réussie!</div>";
    
    // Vérifier la table users
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>La table 'users' existe.</div>";
        
        // Vérifier la structure de la table
        $stmt = $db->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<div class='info'>Colonnes de la table users : " . implode(', ', $columns) . "</div>";
        
        // Vérifier si la colonne profile_picture existe
        if (in_array('profile_picture', $columns)) {
            echo "<div class='success'>La colonne 'profile_picture' existe dans la table 'users'.</div>";
        } else {
            echo "<div class='error'>La colonne 'profile_picture' n'existe pas dans la table 'users'!</div>";
            echo "<div class='info'>Ajoutez-la avec: <code>ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT 'assets/img/default-profile.png';</code></div>";
        }
    } else {
        echo "<div class='error'>La table 'users' n'existe pas!</div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>Erreur de connexion à la base de données: " . $e->getMessage() . "</div>";
}

echo "<h2>Liens utiles</h2>";
echo "<p><a href='index.php' style='color: blue;'>Retour à l'accueil</a></p>";
echo "<p><a href='index.php?action=profile' style='color: blue;'>Aller au profil</a></p>";
echo "<p><a href='index.php?action=edit-profile' style='color: blue;'>Modifier le profil</a></p>";
?> 