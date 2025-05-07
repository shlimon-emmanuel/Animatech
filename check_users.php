<?php
// Définir les chemins et les constantes
define('ROOT_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');
define('CONFIG_PATH', APP_PATH . '/config');

// Inclure la configuration
require_once CONFIG_PATH . '/config.php';

// Connexion à la base de données
try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h2>Vérification de la table users</h2>";
    
    // 1. Vérifier si la colonne role existe
    $checkRoleColumn = $db->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($checkRoleColumn->rowCount() === 0) {
        echo "<p>La colonne 'role' n'existe pas. Ajout de la colonne...</p>";
        $db->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin', 'superadmin') NOT NULL DEFAULT 'user'");
        echo "<p>Colonne 'role' ajoutée avec succès.</p>";
    } else {
        echo "<p>La colonne 'role' existe déjà.</p>";
    }
    
    // 2. Vérifier si le superadmin existe
    $checkSuperadmin = $db->query("SELECT * FROM users WHERE role = 'superadmin'");
    if ($checkSuperadmin->rowCount() === 0) {
        echo "<p>Aucun superadmin trouvé. Création d'un compte superadmin...</p>";
        
        // Hash du mot de passe
        $hashedPassword = password_hash('superadmin123', PASSWORD_DEFAULT);
        
        // Vérifier si l'email existe déjà
        $checkEmail = $db->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->execute(['admin@example.com']);
        
        if ($checkEmail->rowCount() > 0) {
            $user = $checkEmail->fetch(PDO::FETCH_ASSOC);
            $userId = $user['id'];
            
            // Mettre à jour l'utilisateur existant
            $updateUser = $db->prepare("UPDATE users SET role = 'superadmin', username = 'superadmin', password = ? WHERE id = ?");
            $updateUser->execute([$hashedPassword, $userId]);
            echo "<p>Utilisateur existant (ID: {$userId}) mis à jour comme superadmin.</p>";
        } else {
            // Créer un nouveau superadmin
            $createUser = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $result = $createUser->execute(['superadmin', 'admin@example.com', $hashedPassword, 'superadmin']);
            
            if ($result) {
                $userId = $db->lastInsertId();
                echo "<p>Superadmin créé avec succès (ID: {$userId}).</p>";
            } else {
                echo "<p>Erreur lors de la création du superadmin.</p>";
            }
        }
    } else {
        $superadmin = $checkSuperadmin->fetch(PDO::FETCH_ASSOC);
        echo "<p>Superadmin trouvé (ID: {$superadmin['id']}, Username: {$superadmin['username']}, Email: {$superadmin['email']}).</p>";
    }
    
    // 3. Afficher tous les utilisateurs
    echo "<h2>Liste des utilisateurs</h2>";
    $users = $db->query("SELECT id, username, email, role FROM users ORDER BY id ASC");
    
    if ($users->rowCount() === 0) {
        echo "<p>Aucun utilisateur trouvé.</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // 4. Mettre à jour le mot de passe du superadmin pour être sûr
    echo "<h2>Mise à jour du mot de passe du superadmin</h2>";
    $updatePassword = $db->prepare("UPDATE users SET password = ? WHERE role = 'superadmin'");
    $newPassword = password_hash('superadmin123', PASSWORD_DEFAULT);
    $result = $updatePassword->execute([$newPassword]);
    
    if ($result) {
        echo "<p>Mot de passe du superadmin mis à jour avec succès.</p>";
        echo "<p><strong>Email:</strong> admin@example.com</p>";
        echo "<p><strong>Mot de passe:</strong> superadmin123</p>";
    } else {
        echo "<p>Erreur lors de la mise à jour du mot de passe.</p>";
    }
    
} catch (PDOException $e) {
    die("<p>Erreur de base de données: " . $e->getMessage() . "</p>");
}
?> 