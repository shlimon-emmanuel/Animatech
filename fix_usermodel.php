<?php
// Configuration de base
define('DB_HOST', 'localhost');
define('DB_NAME', 'userauth');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Connect directly to the database
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h2>Testing UserModel's getAllUsers Query</h2>";
    
    // Test the exact query from the getAllUsers method
    $query = "SELECT id, username, email, profile_picture, role, created_at, updated_at FROM users ORDER BY id DESC";
    
    echo "<p>Query being tested: <code>" . htmlspecialchars($query) . "</code></p>";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (is_array($users) && count($users) > 0) {
        echo "<p>Success! Found " . count($users) . " users with direct query.</p>";
        echo "<pre>";
        print_r($users);
        echo "</pre>";
    } else {
        echo "<p>Error: No users returned from direct query.</p>";
    }
    
    // Check if there are any errors with PDO
    echo "<p>PDO Error Info: " . json_encode($stmt->errorInfo()) . "</p>";
    
    // Check if there's a column issue (updated_at might be missing)
    echo "<h2>Checking table structure</h2>";
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Table columns:</p>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Modified query that should work if updated_at is missing
    $modifiedQuery = "SELECT id, username, email, profile_picture, role, created_at FROM users ORDER BY id DESC";
    
    echo "<h2>Testing with modified query (without updated_at)</h2>";
    echo "<p>Modified query: <code>" . htmlspecialchars($modifiedQuery) . "</code></p>";
    
    $stmt = $db->prepare($modifiedQuery);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (is_array($users) && count($users) > 0) {
        echo "<p>Success! Found " . count($users) . " users with modified query.</p>";
        echo "<pre>";
        print_r($users[0]); // Just show the first user to keep output manageable
        echo "</pre>";
        
        echo "<p>Total users found: " . count($users) . "</p>";
    } else {
        echo "<p>Error: No users returned from modified query.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 