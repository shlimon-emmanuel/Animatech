<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction pour vérifier et créer un répertoire avec des permissions correctes
function setupDirectory($dir) {
    echo "<h3>Configuration du répertoire: $dir</h3>";
    
    // Vérifier si le répertoire existe
    if (file_exists($dir)) {
        echo "<p>✅ Le répertoire existe déjà.</p>";
    } else {
        echo "<p>⚠️ Le répertoire n'existe pas. Tentative de création...</p>";
        
        if (mkdir($dir, 0777, true)) {
            echo "<p>✅ Répertoire créé avec succès.</p>";
        } else {
            echo "<p>❌ Échec de la création du répertoire.</p>";
            echo "<p>Erreur: " . error_get_last()['message'] . "</p>";
            return false;
        }
    }
    
    // Vérifier les permissions
    $perms = substr(sprintf('%o', fileperms($dir)), -4);
    echo "<p>Permissions actuelles: $perms</p>";
    
    if (is_writable($dir)) {
        echo "<p>✅ Le répertoire est accessible en écriture.</p>";
    } else {
        echo "<p>⚠️ Le répertoire n'est pas accessible en écriture. Tentative de modification des permissions...</p>";
        
        if (chmod($dir, 0777)) {
            echo "<p>✅ Permissions modifiées avec succès (0777).</p>";
        } else {
            echo "<p>❌ Échec de la modification des permissions.</p>";
            echo "<p>Erreur: " . error_get_last()['message'] . "</p>";
            return false;
        }
    }
    
    // Test d'écriture
    $testFile = $dir . '/test_' . time() . '.txt';
    $testContent = 'Test file created on ' . date('Y-m-d H:i:s');
    
    echo "<p>Test d'écriture dans le répertoire...</p>";
    
    if (file_put_contents($testFile, $testContent)) {
        echo "<p>✅ Fichier de test créé avec succès ($testFile).</p>";
        
        // Supprimer le fichier de test
        unlink($testFile);
        echo "<p>✅ Fichier de test supprimé.</p>";
    } else {
        echo "<p>❌ Échec de la création du fichier de test.</p>";
        echo "<p>Erreur: " . error_get_last()['message'] . "</p>";
        return false;
    }
    
    return true;
}

// Fonction pour tester la connexion à la base de données
function testDatabase() {
    echo "<h3>Test de la connexion à la base de données</h3>";
    
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<p>✅ Connexion à la base de données réussie.</p>";
        
        // Vérifier si la table users existe
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('users', $tables)) {
            echo "<p>✅ La table 'users' existe.</p>";
            
            // Vérifier si le champ profile_picture existe
            $columns = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
            
            $profileField = null;
            
            if (in_array('profile_picture', $columns)) {
                echo "<p>✅ Le champ 'profile_picture' existe dans la table users.</p>";
                $profileField = 'profile_picture';
            } else {
                echo "<p>⚠️ Le champ 'profile_picture' n'existe pas dans la table users.</p>";
                
                // Rechercher un champ alternatif pour la photo de profil
                foreach ($columns as $column) {
                    if (strpos(strtolower($column), 'photo') !== false || 
                        strpos(strtolower($column), 'image') !== false || 
                        strpos(strtolower($column), 'picture') !== false || 
                        strpos(strtolower($column), 'avatar') !== false) {
                        $profileField = $column;
                        echo "<p>✅ Champ alternatif trouvé pour la photo de profil: '$profileField'.</p>";
                        break;
                    }
                }
                
                if (!$profileField) {
                    echo "<p>❌ Aucun champ pour la photo de profil n'a été trouvé dans la table users.</p>";
                    echo "<p>Champs disponibles: " . implode(', ', $columns) . "</p>";
                    return false;
                }
            }
            
            return $profileField;
        } else {
            echo "<p>❌ La table 'users' n'existe pas.</p>";
            echo "<p>Tables disponibles: " . implode(', ', $tables) . "</p>";
            return false;
        }
    } catch (PDOException $e) {
        echo "<p>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Page HTML
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration du système de profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .section {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #27ae60;
        }
        .warning {
            color: #f39c12;
        }
        .error {
            color: #e74c3c;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Configuration du système de profil utilisateur</h1>
        
        <div class="section">';

// Vérifier si le fichier de configuration existe
if (file_exists('app/config/config.php')) {
    echo "<p>✅ Fichier de configuration trouvé.</p>";
    require_once 'app/config/config.php';
} else {
    echo "<p>❌ Fichier de configuration introuvable (app/config/config.php).</p>";
    echo "</div></div></body></html>";
    exit;
}

echo '</div>
        
        <div class="section">
            <h2>Configuration des répertoires</h2>';

// Liste des répertoires à configurer
$directories = [
    'assets/uploads/',
    'assets/uploads/profiles/',
    'assets/images/profiles/'
];

$directoriesOk = true;

// Configurer chaque répertoire
foreach ($directories as $dir) {
    $result = setupDirectory($dir);
    $directoriesOk = $directoriesOk && $result;
}

echo '</div>
        
        <div class="section">
            <h2>Configuration de la base de données</h2>';

// Tester la base de données
$profileField = testDatabase();

echo '</div>';

// Résumé et étapes suivantes
echo '<div class="section">
        <h2>Résumé</h2>';

if ($directoriesOk && $profileField) {
    echo '<p class="success">✅ Configuration réussie ! Le système de mise à jour de profil devrait fonctionner correctement.</p>';
    
    // Proposer de corriger le code s'il y a un champ personnalisé pour la photo de profil
    if ($profileField !== 'profile_picture') {
        echo '<p class="warning">⚠️ Votre base de données utilise un champ personnalisé pour la photo de profil: <strong>' . $profileField . '</strong></p>';
        echo '<p>Assurez-vous que votre code utilise ce nom de champ ou modifie-le dans la fonction <code>updateProfile</code> du contrôleur.</p>';
    }
    
    echo '<p>Vous pouvez maintenant tester la mise à jour de profil :</p>';
    echo '<p><a href="index.php?action=profile" class="button">Aller à la page de profil</a></p>';
    echo '<p><a href="fix_user_profile.php" class="button">Outil de diagnostic avancé</a></p>';
} else {
    echo '<p class="error">❌ Des problèmes ont été détectés lors de la configuration. Veuillez les résoudre avant d\'utiliser le système de mise à jour de profil.</p>';
    
    if (!$directoriesOk) {
        echo '<p>Problèmes avec les répertoires:</p>';
        echo '<ul>';
        echo '<li>Vérifiez que l\'utilisateur du serveur web (www-data, apache, etc.) a les permissions d\'écriture sur les répertoires.</li>';
        echo '<li>Sur Windows, vous pouvez exécuter le site en tant qu\'administrateur pour éviter les problèmes de permissions.</li>';
        echo '<li>Créez manuellement les répertoires et définissez les permissions à 0777.</li>';
        echo '</ul>';
    }
    
    if (!$profileField) {
        echo '<p>Problèmes avec la base de données:</p>';
        echo '<ul>';
        echo '<li>Vérifiez que la table "users" existe et qu\'elle contient un champ pour la photo de profil.</li>';
        echo '<li>Si vous utilisez un nom personnalisé pour le champ de photo de profil, modifiez le code pour l\'utiliser.</li>';
        echo '</ul>';
    }
}

echo '</div>
    </div>
</body>
</html>'; 