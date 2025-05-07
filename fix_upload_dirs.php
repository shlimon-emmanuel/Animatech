<?php
/**
 * Script pour créer et réparer les permissions des répertoires d'upload
 */

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Correction des répertoires d'upload</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    .success { color: green; background: #e8f5e9; padding: 10px; margin: 5px 0; border-radius: 4px; }
    .error { color: red; background: #ffebee; padding: 10px; margin: 5px 0; border-radius: 4px; }
    .info { color: blue; background: #e3f2fd; padding: 10px; margin: 5px 0; border-radius: 4px; }
    .dir-info { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; margin-top: 20px; }
</style></head><body>";

echo "<h1>Correction des répertoires d'upload</h1>";

// Répertoires à vérifier/créer
$directories = [
    'assets',
    'assets/uploads',
    'assets/uploads/profiles',
    'assets/images',
    'assets/images/profiles',
    'assets/img'
];

// Traiter l'action de correction
if (isset($_POST['fix_all'])) {
    echo "<h2>Résultats de la correction</h2>";
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            if (mkdir($dir, 0777, true)) {
                echo "<div class='success'>Répertoire '$dir' créé avec succès avec les permissions 0777.</div>";
            } else {
                echo "<div class='error'>Impossible de créer le répertoire '$dir'. Erreur: " . error_get_last()['message'] . "</div>";
            }
        } else {
            if (chmod($dir, 0777)) {
                echo "<div class='success'>Permissions du répertoire '$dir' changées à 0777.</div>";
            } else {
                echo "<div class='error'>Impossible de modifier les permissions du répertoire '$dir'. Erreur: " . error_get_last()['message'] . "</div>";
            }
        }
    }
    
    // Vérifier le fichier par défaut
    $defaultImage = 'assets/img/default-profile.png';
    if (!file_exists($defaultImage)) {
        echo "<div class='info'>Création de l'image par défaut...</div>";
        
        if (function_exists('imagecreate')) {
            $image = imagecreate(200, 200);
            $background = imagecolorallocate($image, 30, 30, 50);
            $foreground = imagecolorallocate($image, 157, 78, 221);
            
            // Dessiner une silhouette simple
            imagefilledellipse($image, 100, 80, 80, 80, $foreground);
            imagefilledrectangle($image, 60, 120, 140, 200, $foreground);
            
            if (imagepng($image, $defaultImage)) {
                echo "<div class='success'>Image par défaut créée: $defaultImage</div>";
            } else {
                echo "<div class='error'>Impossible de créer l'image par défaut.</div>";
            }
            
            imagedestroy($image);
        } else {
            echo "<div class='error'>La bibliothèque GD n'est pas disponible pour créer l'image par défaut.</div>";
        }
    }
}

// Afficher l'état actuel des répertoires
echo "<h2>État actuel des répertoires</h2>";

foreach ($directories as $dir) {
    echo "<div class='dir-info'>";
    echo "<strong>Répertoire: $dir</strong><br>";
    
    if (file_exists($dir)) {
        echo "Existe: <span style='color:green'>Oui</span><br>";
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "Permissions: $perms<br>";
        echo "Accessible en écriture: " . (is_writable($dir) ? "<span style='color:green'>Oui</span>" : "<span style='color:red'>Non</span>") . "<br>";
    } else {
        echo "Existe: <span style='color:red'>Non</span><br>";
    }
    
    echo "</div>";
}

// Formulaire pour corriger les répertoires
echo "<form action='' method='post'>";
echo "<input type='hidden' name='fix_all' value='1'>";
echo "<button type='submit'>Créer/Corriger tous les répertoires</button>";
echo "</form>";

echo "<p><a href='index.php?action=profile' style='display: inline-block; margin-top: 20px; padding: 10px 15px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>Retour à la page de profil</a></p>";

echo "</body></html>";
?> 