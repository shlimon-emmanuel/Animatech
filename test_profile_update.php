<?php
// Script pour tester la fonctionnalité de mise à jour du profil utilisateur
// Placez ce fichier à la racine du projet et exécutez-le

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
session_start();

// Définir des styles pour une meilleure lisibilité
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h1, h2, h3 { color: #333; }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .info { color: blue; background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .warning { color: #ff9800; background: #fff3e0; padding: 10px; border-radius: 5px; margin: 5px 0; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
    form { background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; }
    button { padding: 10px 15px; background: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer; }
    button:hover { background: #0b7dda; }
    input[type="text"], input[type="email"] { padding: 10px; margin: 5px 0; width: 100%; border: 1px solid #ddd; border-radius: 4px; }
    label { display: block; margin-top: 10px; font-weight: bold; }
</style>';

echo '<h1>Test de mise à jour du profil utilisateur</h1>';

// Charger la configuration de la base de données
if (file_exists('app/config/config.php')) {
    require_once 'app/config/config.php';
    echo "<div class='success'>Fichier de configuration chargé avec succès.</div>";
} else {
    die("<div class='error'>Le fichier de configuration est manquant (app/config/config.php).</div>");
}

// Vérifier l'état de la session
echo "<h2>État de la session</h2>";
echo "<div class='info'>ID de session: " . session_id() . "</div>";

if (empty($_SESSION)) {
    echo "<div class='warning'>La session est vide. Aucun utilisateur n'est connecté.</div>";
    echo "<p>Veuillez vous <a href='index.php?action=login'>connecter</a> d'abord.</p>";
} else {
    echo "<div class='success'>Session active.</div>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
}

// Déterminer l'ID de l'utilisateur en fonction du format de session
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
        (isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null);

if (!$userId) {
    echo "<div class='error'>Impossible d'identifier l'utilisateur dans la session.</div>";
    echo "<p>Veuillez vous <a href='index.php?action=login'>connecter</a> d'abord.</p>";
    exit;
}

// Tester la connexion à la base de données
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<div class='success'>Connexion à la base de données réussie!</div>";
    
    // Récupérer les informations utilisateur actuelles
    $stmt = $db->prepare("SELECT id, username, email, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<div class='error'>Utilisateur avec ID $userId introuvable dans la base de données.</div>";
        exit;
    }
    
    echo "<h2>Informations utilisateur actuelles</h2>";
    echo "<div class='info'>ID: {$user['id']}</div>";
    echo "<div class='info'>Nom d'utilisateur: {$user['username']}</div>";
    echo "<div class='info'>Email: {$user['email']}</div>";
    echo "<div class='info'>Photo de profil: {$user['profile_picture']}</div>";
    
    if (file_exists($user['profile_picture'])) {
        echo "<div class='success'>Le fichier image existe: {$user['profile_picture']}</div>";
        echo "<img src='{$user['profile_picture']}' style='max-width: 150px; border-radius: 50%; margin: 10px 0;'>";
    } else if ($user['profile_picture'] === 'assets/img/default-profile.png' && file_exists($user['profile_picture'])) {
        echo "<div class='info'>Image par défaut utilisée.</div>";
        echo "<img src='{$user['profile_picture']}' style='max-width: 150px; border-radius: 50%; margin: 10px 0;'>";
    } else {
        echo "<div class='warning'>Le fichier image n'existe pas: {$user['profile_picture']}</div>";
    }
    
    // Afficher un formulaire de test pour mettre à jour le profil
    echo "<h2>Tester la mise à jour du profil</h2>";
    
    // Formulaire de test - méthode directe (sans passer par le contrôleur)
    echo "<h3>Méthode 1: Mise à jour directe en base de données</h3>";
    echo "<form id='direct-update' method='POST' action='' enctype='multipart/form-data'>";
    echo "<label for='username1'>Nom d'utilisateur:</label>";
    echo "<input type='text' id='username1' name='username1' value='{$user['username']}' required>";
    echo "<label for='email1'>Email:</label>";
    echo "<input type='email' id='email1' name='email1' value='{$user['email']}' required>";
    echo "<label for='profile_picture1'>Photo de profil:</label>";
    echo "<input type='file' id='profile_picture1' name='profile_picture1'>";
    echo "<input type='hidden' name='direct_update' value='1'>";
    echo "<button type='submit'>Mettre à jour directement en base de données</button>";
    echo "</form>";
    
    // Formulaire de test - utilisant le contrôleur AuthController via la route update-profile
    echo "<h3>Méthode 2: Utiliser la route update-profile</h3>";
    echo "<form id='controller-update' method='POST' action='index.php?action=update-profile' enctype='multipart/form-data'>";
echo "<label for='username'>Nom d'utilisateur:</label>";
    echo "<input type='text' id='username' name='username' value='{$user['username']}' required>";
echo "<label for='email'>Email:</label>";
    echo "<input type='email' id='email' name='email' value='{$user['email']}' required>";
echo "<label for='profile_picture'>Photo de profil:</label>";
echo "<input type='file' id='profile_picture' name='profile_picture'>";
    echo "<button type='submit'>Mettre à jour via le contrôleur</button>";
echo "</form>";

    // Traiter le formulaire de mise à jour directe si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['direct_update'])) {
        echo "<h3>Résultat de la mise à jour directe</h3>";
        
        $username = $_POST['username1'];
        $email = $_POST['email1'];
        
        echo "<div class='info'>Tentative de mise à jour pour l'utilisateur $userId avec:</div>";
        echo "<div class='info'>Nouveau nom d'utilisateur: $username</div>";
        echo "<div class='info'>Nouvel email: $email</div>";
        
        // Construire la requête SQL
        $sql = "UPDATE users SET username = ?, email = ?";
        $params = [$username, $email];
        
        // Gérer l'upload de fichier si présent
        if (isset($_FILES['profile_picture1']) && $_FILES['profile_picture1']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/uploads/profiles/';
            
            // Créer le dossier s'il n'existe pas
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Générer un nom de fichier unique
            $fileExtension = pathinfo($_FILES['profile_picture1']['name'], PATHINFO_EXTENSION);
            $fileName = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['profile_picture1']['tmp_name'], $targetFile)) {
                echo "<div class='success'>Fichier uploadé avec succès: $targetFile</div>";
                
                // Ajouter le chemin de l'image à la requête SQL
                $sql .= ", profile_picture = ?";
                $params[] = $targetFile;
                
                // Supprimer l'ancienne image si elle existe et n'est pas l'image par défaut
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
        
        // Compléter la requête SQL avec la clause WHERE
        $sql .= " WHERE id = ?";
        $params[] = $userId;
        
        // Exécuter la requête
        try {
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                echo "<div class='success'>Profil mis à jour avec succès!</div>";
                
                // Mettre à jour les données de session
                if (isset($_SESSION['user'])) {
                    $_SESSION['user']['username'] = $username;
                    $_SESSION['user']['email'] = $email;
                    
                    if (isset($targetFile)) {
                        $_SESSION['user']['profile_picture'] = $targetFile;
                    }
                } else {
                    // Format alternatif de session
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    
                    if (isset($targetFile)) {
                        $_SESSION['profile_picture'] = $targetFile;
                    }
                }
                
                echo "<div class='info'>Session mise à jour.</div>";
            } else {
                echo "<div class='error'>Erreur lors de la mise à jour du profil.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>Exception lors de la mise à jour: " . $e->getMessage() . "</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>Erreur de connexion à la base de données: " . $e->getMessage() . "</div>";
}

// Liens utiles
echo "<h2>Liens utiles</h2>";
echo "<p><a href='index.php?action=profile'>Voir mon profil</a></p>";
echo "<p><a href='index.php?action=edit-profile'>Modifier mon profil via la page edit-profile</a></p>";
echo "<p><a href='test_uploads_dir.php'>Vérifier les dossiers d'upload</a></p>";
echo "<p><a href='index.php'>Retour à l'accueil</a></p>";
?> 