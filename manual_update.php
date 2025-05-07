<?php
/**
 * Script pour mettre à jour manuellement un profil utilisateur
 */

// Inclure les fichiers nécessaires
require_once 'Core/config.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Paramètres de base pour l'affichage
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Mise à jour manuelle du profil</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 4px; margin: 10px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 4px; margin: 10px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 4px; margin: 10px 0; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type='text'], input[type='email'] { width: 300px; padding: 8px; }
    button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
</style></head><body>";

echo "<h1>Mise à jour manuelle du profil utilisateur</h1>";

// Récupérer l'ID de l'utilisateur connecté
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null);

if (!$userId) {
    echo "<div class='error'>Aucun utilisateur connecté! Veuillez vous connecter avant d'utiliser cet outil.</div>";
    echo "<a href='index.php?action=login' style='display: inline-block; margin-top: 20px; padding: 10px 15px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>Se connecter</a>";
    exit;
}

// Connexion à la base de données
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<div class='info'>Connexion à la base de données réussie!</div>";
    
    // Récupérer les informations utilisateur actuelles
    $stmt = $db->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<div class='error'>Utilisateur introuvable dans la base de données (ID: $userId).</div>";
        exit;
    }
    
    echo "<div class='info'>Utilisateur trouvé dans la base de données:</div>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";
    
    // Traiter le formulaire de mise à jour
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        
        // Construire la requête SQL
        $sql = "UPDATE users SET username = ?, email = ?";
        $params = [$username, $email];
        
        // Gérer l'upload de fichier si présent
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/uploads/profiles/';
            
            // Créer le dossier s'il n'existe pas
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Générer un nom de fichier unique
            $fileExtension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $fileName = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                echo "<div class='success'>Fichier uploadé avec succès: $targetFile</div>";
                
                // Ajouter le chemin de l'image à la requête SQL
                $sql .= ", profile_picture = ?";
                $params[] = $targetFile;
                
                // Supprimer l'ancienne image si elle existe
                if (!empty($user['profile_picture']) && 
                    $user['profile_picture'] !== 'assets/img/default-profile.png' && 
                    file_exists($user['profile_picture'])) {
                    unlink($user['profile_picture']);
                    echo "<div class='info'>Ancienne image supprimée: " . $user['profile_picture'] . "</div>";
                }
            } else {
                echo "<div class='error'>Échec de l'upload du fichier.</div>";
            }
        }
        
        // Finaliser la requête SQL
        $sql .= " WHERE id = ?";
        $params[] = $userId;
        
        // Exécuter la requête
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            echo "<div class='success'>Profil mis à jour avec succès!</div>";
            
            // Mettre à jour la session
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['email'] = $email;
                
                if (isset($targetFile)) {
                    $_SESSION['user']['profile_picture'] = $targetFile;
                }
            } else {
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
                if (isset($targetFile)) {
                    $_SESSION['profile_picture'] = $targetFile;
                }
            }
            
            // Récupérer les informations mises à jour
            $stmt = $db->prepare("SELECT username, email, profile_picture FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<div class='info'>Nouvelles informations utilisateur:</div>";
            echo "<pre>";
            print_r($updatedUser);
            echo "</pre>";
            
            echo "<div class='info'>Variables de session mises à jour:</div>";
            echo "<pre>";
            print_r($_SESSION);
            echo "</pre>";
        } else {
            echo "<div class='error'>Erreur lors de la mise à jour du profil. Informations de débogage:</div>";
            echo "<pre>";
            print_r($stmt->errorInfo());
            echo "</pre>";
        }
    }
    
    // Formulaire de mise à jour
    echo "<h2>Formulaire de mise à jour manuelle</h2>";
    echo "<form action='' method='POST' enctype='multipart/form-data'>";
    
    echo "<div class='form-group'>";
    echo "<label for='username'>Nom d'utilisateur:</label>";
    echo "<input type='text' id='username' name='username' value='" . htmlspecialchars($user['username']) . "' required>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='email'>Email:</label>";
    echo "<input type='email' id='email' name='email' value='" . htmlspecialchars($user['email']) . "' required>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='profile_picture'>Photo de profil:</label>";
    echo "<input type='file' id='profile_picture' name='profile_picture'>";
    echo "</div>";
    
    echo "<input type='hidden' name='update_profile' value='1'>";
    echo "<button type='submit'>Mettre à jour manuellement</button>";
    echo "</form>";
    
} catch (PDOException $e) {
    echo "<div class='error'>Erreur de connexion à la base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Liens de navigation
echo "<p style='margin-top:20px;'>";
echo "<a href='index.php?action=profile' style='display: inline-block; margin-right: 10px; padding: 10px 15px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>Retour à la page de profil</a>";
echo "<a href='php_error_log.php' style='display: inline-block; padding: 10px 15px; background: #FF5722; color: white; text-decoration: none; border-radius: 4px;'>Voir les erreurs PHP</a>";
echo "</p>";

echo "</body></html>";
?> 