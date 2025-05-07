<?php
// Configuration de base
define('DB_HOST', 'localhost');
define('DB_NAME', 'userauth');
define('DB_USER', 'root');
define('DB_PASS', '');

// Required for UserModel
define('APP_PATH', __DIR__ . '/app');

// Manually include the UserModel
require_once 'app/Models/UserModel.php';

try {
    // Create an instance of UserModel
    $userModel = new App\Models\UserModel();
    
    // Get all users
    $users = $userModel->getAllUsers();
    
    echo "<h2>Test of getAllUsers Method</h2>";
    
    if (is_array($users) && count($users) > 0) {
        echo "<p>Success! Found " . count($users) . " users.</p>";
        echo "<pre>";
        var_dump($users);
        echo "</pre>";
    } else {
        echo "<p>Error: No users returned or not an array. Value: </p>";
        echo "<pre>";
        var_dump($users);
        echo "</pre>";
    }
    
    // Check dashboard.php for potential issues
    echo "<h2>Dashboard.php Analysis</h2>";
    
    if (file_exists('app/Views/admin/dashboard.php')) {
        echo "<p>Dashboard file exists.</p>";
        
        // Check if dashboard is properly passing $users
        $dashboardContent = file_get_contents('app/Views/admin/dashboard.php');
        
        // Look for common issues
        $issuesFound = [];
        
        if (strpos($dashboardContent, '$recentUsers = array_slice($users, 0, 5);') !== false) {
            echo "<p>Found array_slice for recent users.</p>";
            
            // Check if it's correctly handling the array
            if (strpos($dashboardContent, 'foreach ($recentUsers as $user):') !== false) {
                echo "<p>Found foreach loop for recent users.</p>";
            } else {
                $issuesFound[] = "Missing foreach loop for recent users";
            }
        } else {
            $issuesFound[] = "Missing array_slice for recent users";
        }
        
        if (!empty($issuesFound)) {
            echo "<h3>Issues found:</h3>";
            echo "<ul>";
            foreach ($issuesFound as $issue) {
                echo "<li>" . htmlspecialchars($issue) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No obvious issues found in dashboard.php</p>";
        }
    } else {
        echo "<p>Error: Dashboard file not found at app/Views/admin/dashboard.php</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 