<?php
// Définir les chemins et les constantes
define('ROOT_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');
define('CONFIG_PATH', APP_PATH . '/config');

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration
require_once CONFIG_PATH . '/config.php';

// Fonction pour vérifier un mot de passe
function verifyPassword($storedHash, $password) {
    return password_verify($password, $storedHash);
}

// Connexion à la base de données
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h2>Test de connexion</h2>";
    
    // Récupérer les données de l'utilisateur superadmin
    $email = 'admin@example.com';
    $password = 'superadmin123';
    
    echo "<p>Tentative de connexion avec : </p>";
    echo "<ul>";
    echo "<li>Email: $email</li>";
    echo "<li>Mot de passe: $password</li>";
    echo "</ul>";
    
    // 1. Vérifier si l'utilisateur existe
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p style='color: red;'>Erreur: Aucun utilisateur trouvé avec cet email.</p>";
    } else {
        echo "<p style='color: green;'>Utilisateur trouvé :</p>";
        echo "<ul>";
        echo "<li>ID: {$user['id']}</li>";
        echo "<li>Username: {$user['username']}</li>";
        echo "<li>Email: {$user['email']}</li>";
        echo "<li>Rôle: {$user['role']}</li>";
        echo "</ul>";
        
        // 2. Vérifier le mot de passe
        $passwordMatch = verifyPassword($user['password'], $password);
        
        if ($passwordMatch) {
            echo "<p style='color: green;'>✓ Le mot de passe est correct!</p>";
            
            echo "<h3>Hash du mot de passe stocké:</h3>";
            echo "<pre>{$user['password']}</pre>";
            
            // Créer une vraie session pour simuler la connexion
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            echo "<p>Session créée avec succès.</p>";
            echo "<p><a href='index.php?action=admin' style='color: blue;'>Accéder au panneau d'administration</a></p>";
            
        } else {
            echo "<p style='color: red;'>✗ Le mot de passe est incorrect!</p>";
            
            echo "<h3>Hash du mot de passe stocké:</h3>";
            echo "<pre>{$user['password']}</pre>";
            
            echo "<h3>Test de tous les algorithmes de hachage:</h3>";
            echo "<p>Parfois, le problème peut venir de l'algorithme de hachage utilisé.</p>";
            
            $algorithms = [
                'PASSWORD_DEFAULT' => PASSWORD_DEFAULT,
                'PASSWORD_BCRYPT' => PASSWORD_BCRYPT
            ];
            
            foreach ($algorithms as $name => $algo) {
                $testHash = password_hash($password, $algo);
                $testVerify = password_verify($password, $testHash);
                echo "<p>$name: " . ($testVerify ? "✓ Fonctionne" : "✗ Ne fonctionne pas") . "</p>";
            }
            
            // Mettre à jour le mot de passe avec le hash actuel
            echo "<h3>Mise à jour du mot de passe:</h3>";
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateResult = $updateStmt->execute([$newHash, $user['id']]);
            
            if ($updateResult) {
                echo "<p style='color: green;'>Le mot de passe a été mis à jour avec un nouveau hash.</p>";
                echo "<p>Nouveau hash: <pre>$newHash</pre></p>";
                echo "<p>Veuillez <a href='test_login.php' style='color: blue;'>recharger cette page</a> pour vérifier que la connexion fonctionne maintenant.</p>";
            } else {
                echo "<p style='color: red;'>Erreur lors de la mise à jour du mot de passe.</p>";
            }
        }
    }
    
} catch (PDOException $e) {
    die("<p>Erreur de base de données: " . $e->getMessage() . "</p>");
}
?> 