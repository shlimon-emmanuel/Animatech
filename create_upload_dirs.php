<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Style de base pour la page
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création des répertoires d\'upload</title>
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
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .success {
            color: #27ae60;
            background-color: #e8f8f5;
            padding: 10px;
            border-left: 4px solid #27ae60;
            margin: 10px 0;
        }
        .error {
            color: #c0392b;
            background-color: #fadbd8;
            padding: 10px;
            border-left: 4px solid #c0392b;
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Création des répertoires d\'upload</h1>';

// Définir les répertoires à créer
$directories = [
    'assets/uploads/' => 'Répertoire principal pour les uploads',
    'assets/uploads/profiles/' => 'Répertoire pour les photos de profil',
    'assets/images/profiles/' => 'Ancien répertoire pour les photos de profil (à des fins de compatibilité)'
];

// Compter les réussites et les échecs
$success = 0;
$failed = 0;

// Créer chaque répertoire
echo '<div class="card">';
echo '<h2>Création des répertoires</h2>';

foreach ($directories as $dir => $description) {
    echo '<h3>' . htmlspecialchars($dir) . '</h3>';
    echo '<p>' . htmlspecialchars($description) . '</p>';
    
    if (file_exists($dir)) {
        echo '<div class="success">✓ Le répertoire existe déjà.</div>';
        
        // Vérifier les permissions
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $isWritable = is_writable($dir);
        
        if ($isWritable) {
            echo '<div class="success">✓ Le répertoire est accessible en écriture. (Permissions: ' . $perms . ')</div>';
        } else {
            echo '<div class="error">✗ Le répertoire n\'est pas accessible en écriture. (Permissions: ' . $perms . ')</div>';
            
            // Tenter de corriger les permissions
            if (chmod($dir, 0777)) {
                echo '<div class="success">✓ Permissions corrigées à 0777.</div>';
                $success++;
            } else {
                echo '<div class="error">✗ Impossible de modifier les permissions.</div>';
                echo '<div class="error">Message d\'erreur: ' . htmlspecialchars(error_get_last()['message'] ?? 'Inconnu') . '</div>';
                $failed++;
            }
        }
    } else {
        // Créer le répertoire
        if (mkdir($dir, 0777, true)) {
            echo '<div class="success">✓ Répertoire créé avec succès.</div>';
            $success++;
        } else {
            echo '<div class="error">✗ Impossible de créer le répertoire.</div>';
            echo '<div class="error">Message d\'erreur: ' . htmlspecialchars(error_get_last()['message'] ?? 'Inconnu') . '</div>';
            $failed++;
        }
    }
}

echo '</div>';

// Tester l'upload de fichier
echo '<div class="card">';
echo '<h2>Test d\'écriture</h2>';

$testFiles = [];
foreach ($directories as $dir => $description) {
    $testFile = $dir . 'test.txt';
    $testFiles[] = $testFile;
    
    echo '<h3>Test d\'écriture dans ' . htmlspecialchars($dir) . '</h3>';
    
    if (file_exists($dir)) {
        // Tenter d'écrire un fichier test
        $content = 'Test file created on ' . date('Y-m-d H:i:s');
        
        if (file_put_contents($testFile, $content)) {
            echo '<div class="success">✓ Fichier test créé avec succès: ' . htmlspecialchars($testFile) . '</div>';
        } else {
            echo '<div class="error">✗ Impossible de créer le fichier test.</div>';
            echo '<div class="error">Message d\'erreur: ' . htmlspecialchars(error_get_last()['message'] ?? 'Inconnu') . '</div>';
        }
    } else {
        echo '<div class="error">✗ Le répertoire n\'existe pas, test impossible.</div>';
    }
}

echo '</div>';

// Résumé
echo '<div class="card">';
echo '<h2>Résumé</h2>';

if ($failed === 0) {
    echo '<div class="success">✓ Tous les répertoires ont été créés ou vérifiés avec succès.</div>';
} else {
    echo '<div class="error">✗ Il y a eu ' . $failed . ' erreur(s) lors de la création ou vérification des répertoires.</div>';
    echo '<p>Vérifiez les permissions sur le serveur. Vous pourriez avoir besoin de créer ces répertoires manuellement ou de modifier les permissions.</p>';
}

echo '</div>';

// Informations sur le système
echo '<div class="card">';
echo '<h2>Informations sur le système</h2>';

echo '<p><strong>Répertoire courant:</strong> ' . htmlspecialchars(getcwd()) . '</p>';
echo '<p><strong>Utilisateur PHP:</strong> ' . htmlspecialchars(get_current_user()) . '</p>';
echo '<p><strong>Version PHP:</strong> ' . phpversion() . '</p>';
echo '<p><strong>Système d\'exploitation:</strong> ' . php_uname() . '</p>';

// Vérifier les limites d'upload
echo '<h3>Limites d\'upload</h3>';
echo '<p><strong>upload_max_filesize:</strong> ' . ini_get('upload_max_filesize') . '</p>';
echo '<p><strong>post_max_size:</strong> ' . ini_get('post_max_size') . '</p>';
echo '<p><strong>memory_limit:</strong> ' . ini_get('memory_limit') . '</p>';

echo '</div>';

// Liens
echo '<div class="card">';
echo '<h2>Actions</h2>';
echo '<p><a href="index.php" class="button">Retour à l\'accueil</a></p>';
echo '<p><a href="profile_diagnostic.php" class="button">Diagnostic du profil</a></p>';
echo '<p><a href="index.php?action=profile" class="button">Voir mon profil</a></p>';
echo '</div>';

echo '</div></body></html>'; 