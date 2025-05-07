<?php
// Configuration de base
define('DB_HOST', 'localhost');
define('DB_NAME', 'userauth');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Connect to the database
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h2>Users Table Structure</h2>";
    
    // Get table structure
    $stmt = $db->query("DESCRIBE users");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>Users in the database</h2>";
    
    // Get all users
    $stmt = $db->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table border='1'>";
        // Print headers based on first user's columns
        echo "<tr>";
        foreach (array_keys($users[0]) as $column) {
            if ($column !== 'password') { // Skip showing passwords
                echo "<th>$column</th>";
            }
        }
        echo "</tr>";
        
        // Print user data
        foreach ($users as $user) {
            echo "<tr>";
            foreach ($user as $column => $value) {
                if ($column !== 'password') { // Skip showing passwords
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in the database.</p>";
    }

} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?> 