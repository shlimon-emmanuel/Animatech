<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir les répertoires à vérifier
$directories = [
    'assets/uploads/',
    'assets/uploads/profiles/',
    'assets/images/profiles/' // Au cas où ce répertoire est également utilisé
];

echo "<h1>Vérification et correction des permissions des répertoires</h1>";

// Vérifier et corriger chaque répertoire
foreach ($directories as $dir) {
    echo "<h2>Répertoire: " . htmlspecialchars($dir) . "</h2>";
    
    // Vérifier si le répertoire existe
    if (file_exists($dir)) {
        echo "<p>✅ Le répertoire existe.</p>";
        
        // Vérifier les permissions
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "<p>Permissions actuelles: " . $perms . "</p>";
        
        $isWritable = is_writable($dir);
        if ($isWritable) {
            echo "<p>✅ Le répertoire est accessible en écriture.</p>";
        } else {
            echo "<p>❌ Le répertoire n'est pas accessible en écriture.</p>";
            
            // Tenter de corriger les permissions
            echo "<p>Tentative de correction des permissions...</p>";
            if (chmod($dir, 0777)) {
                echo "<p>✅ Permissions corrigées à 0777.</p>";
            } else {
                echo "<p>❌ Impossible de modifier les permissions.</p>";
                echo "<p>Message d'erreur: " . htmlspecialchars(error_get_last()['message'] ?? 'Inconnu') . "</p>";
            }
        }
        
        // Tester l'écriture d'un fichier
        $testFile = $dir . 'test_' . time() . '.txt';
        echo "<p>Test d'écriture d'un fichier: " . htmlspecialchars($testFile) . "</p>";
        if (file_put_contents($testFile, "Test file created at " . date('Y-m-d H:i:s'))) {
            echo "<p>✅ Écriture réussie.</p>";
            // Supprimer le fichier de test
            unlink($testFile);
        } else {
            echo "<p>❌ Échec de l'écriture.</p>";
            echo "<p>Message d'erreur: " . htmlspecialchars(error_get_last()['message'] ?? 'Inconnu') . "</p>";
        }
    } else {
        echo "<p>❌ Le répertoire n'existe pas.</p>";
        
        // Tenter de créer le répertoire
        echo "<p>Tentative de création du répertoire...</p>";
        if (mkdir($dir, 0777, true)) {
            echo "<p>✅ Répertoire créé avec succès (permissions 0777).</p>";
            
            // Tester l'écriture d'un fichier
            $testFile = $dir . 'test_' . time() . '.txt';
            echo "<p>Test d'écriture d'un fichier: " . htmlspecialchars($testFile) . "</p>";
            if (file_put_contents($testFile, "Test file created at " . date('Y-m-d H:i:s'))) {
                echo "<p>✅ Écriture réussie.</p>";
                // Supprimer le fichier de test
                unlink($testFile);
            } else {
                echo "<p>❌ Échec de l'écriture.</p>";
                echo "<p>Message d'erreur: " . htmlspecialchars(error_get_last()['message'] ?? 'Inconnu') . "</p>";
            }
        } else {
            echo "<p>❌ Échec de la création du répertoire.</p>";
            echo "<p>Message d'erreur: " . htmlspecialchars(error_get_last()['message'] ?? 'Inconnu') . "</p>";
        }
    }
    
    echo "<hr>";
}

// Afficher des informations sur l'environnement
echo "<h2>Informations sur l'environnement</h2>";
echo "<p><strong>Utilisateur PHP:</strong> " . get_current_user() . "</p>";
echo "<p><strong>Répertoire courant:</strong> " . getcwd() . "</p>";
echo "<p><strong>Propriétaire du script:</strong> " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'Fonction non disponible sous Windows') . "</p>";
echo "<p><strong>Version PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>Système d'exploitation:</strong> " . php_uname() . "</p>";

echo "<p><a href='index.php?action=profile'>Retour au profil</a> | <a href='profile_diagnostic.php'>Diagnostic complet du profil</a></p>";
?> 