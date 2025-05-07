<?php
/**
 * Script pour afficher les erreurs PHP récentes
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Journal d'erreurs PHP</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    .error-section { background: #ffebee; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    .error-date { color: #888; font-size: 0.9em; margin-bottom: 5px; }
    .error-message { color: #d32f2f; font-weight: bold; }
    .no-errors { background: #e8f5e9; padding: 15px; border-radius: 4px; color: #388e3c; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
    .button { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; }
</style></head><body>";

echo "<h1>Journal d'erreurs PHP</h1>";

// Déterminer le chemin du fichier d'erreur PHP
$error_log_path = ini_get('error_log');
if (empty($error_log_path) || !file_exists($error_log_path)) {
    $error_log_path = dirname(__FILE__) . '/php_errors.log';
    
    // Essayer d'autres emplacements courants
    $possible_locations = [
        'C:/laragon/logs/php_error.log',
        'C:/xampp/php/logs/php_errors.log',
        '/var/log/apache2/error.log',
        '/var/log/httpd/error_log',
        '/var/log/php_errors.log',
        dirname(__FILE__) . '/error_log'
    ];
    
    foreach ($possible_locations as $location) {
        if (file_exists($location)) {
            $error_log_path = $location;
            break;
        }
    }
}

echo "<p><strong>Chemin du fichier d'erreur :</strong> " . htmlspecialchars($error_log_path) . "</p>";

// Vérifier si le fichier existe
if (file_exists($error_log_path)) {
    $errors = file_get_contents($error_log_path);
    $lines = file($error_log_path);
    
    // Prendre les 50 dernières lignes (ou moins s'il y en a moins)
    $last_lines = array_slice($lines, -50);
    
    if (!empty($last_lines)) {
        echo "<h2>Les 50 dernières entrées :</h2>";
        
        foreach ($last_lines as $line) {
            echo "<div class='error-section'>";
            
            // Essayez de parser la date et le message
            if (preg_match('/^\[(.*?)\](.*)$/', $line, $matches)) {
                echo "<div class='error-date'>" . htmlspecialchars($matches[1]) . "</div>";
                echo "<div class='error-message'>" . htmlspecialchars($matches[2]) . "</div>";
            } else {
                echo "<div class='error-message'>" . htmlspecialchars($line) . "</div>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<div class='no-errors'>Aucune erreur récente trouvée dans le fichier journal.</div>";
    }
} else {
    echo "<p>Le fichier journal d'erreurs n'a pas été trouvé à l'emplacement spécifié.</p>";
    
    // Essayons de créer un nouveau fichier journal
    echo "<h2>Création d'un nouveau fichier journal</h2>";
    
    // Test pour générer une erreur
    echo "<p>Tentative de génération d'une erreur de test...</p>";
    
    try {
        // Une erreur simple
        $undefined_var++;
    } catch (Throwable $e) {
        error_log("Erreur de test générée à " . date('Y-m-d H:i:s') . ": " . $e->getMessage());
        echo "<p>Erreur de test générée et journalisée.</p>";
    }
    
    // Vérifier à nouveau
    if (file_exists($error_log_path)) {
        echo "<p>Nouveau fichier journal créé à : " . htmlspecialchars($error_log_path) . "</p>";
        echo "<p>Contenu :</p>";
        echo "<pre>" . htmlspecialchars(file_get_contents($error_log_path)) . "</pre>";
    } else {
        echo "<p>Impossible de créer ou de localiser le fichier journal.</p>";
        
        // Afficher les paramètres PHP pertinents
        echo "<h2>Paramètres PHP pertinents</h2>";
        echo "<pre>";
        echo "error_reporting: " . ini_get('error_reporting') . "\n";
        echo "display_errors: " . ini_get('display_errors') . "\n";
        echo "log_errors: " . ini_get('log_errors') . "\n";
        echo "error_log: " . ini_get('error_log') . "\n";
        echo "</pre>";
    }
}

// Lien pour retourner à la page de profil
echo "<a href='index.php?action=profile' class='button'>Retour à la page de profil</a>";

echo "</body></html>";
?> 