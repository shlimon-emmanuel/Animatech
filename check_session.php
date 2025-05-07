<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
session_start();

// Style simple
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de Session</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        img {
            max-width: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Vérification de la Session Utilisateur</h1>';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo '<p class="error">❌ Aucun utilisateur connecté en session.</p>';
    echo '<pre>' . print_r($_SESSION, true) . '</pre>';
    echo '</div></body></html>';
    exit;
}

// Afficher les informations de l'utilisateur
$userId = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'] ?? 'Non défini';
$email = $_SESSION['user']['email'] ?? 'Non défini';
$profilePicture = $_SESSION['user']['profile_picture'] ?? 'Non défini';

echo '<h2>Informations de l\'utilisateur en session</h2>';
echo '<p><strong>ID:</strong> ' . htmlspecialchars($userId) . '</p>';
echo '<p><strong>Nom d\'utilisateur:</strong> ' . htmlspecialchars($username) . '</p>';
echo '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>';
echo '<p><strong>Photo de profil:</strong> ' . htmlspecialchars($profilePicture) . '</p>';

// Vérifier si l'image existe
if (!empty($profilePicture)) {
    if (file_exists($profilePicture)) {
        echo '<p class="success">✅ L\'image existe sur le serveur</p>';
        echo '<img src="' . htmlspecialchars($profilePicture) . '" alt="Photo de profil">';
    } else {
        echo '<p class="error">❌ L\'image n\'existe pas à l\'emplacement spécifié: ' . htmlspecialchars($profilePicture) . '</p>';
    }
} else {
    echo '<p class="error">❌ Aucun chemin d\'image défini</p>';
}

// Afficher le contenu complet de la session
echo '<h2>Contenu complet de la session</h2>';
echo '<pre>' . print_r($_SESSION, true) . '</pre>';

// Lien vers les scripts de diagnostic
echo '<p><a href="fix_user_profile.php">Exécuter l\'outil de réparation de profil</a></p>';
echo '<p><a href="index.php?action=profile">Retour au profil</a></p>';

echo '</div></body></html>';
?> 