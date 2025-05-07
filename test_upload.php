<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session
session_start();

echo '<h1>Test de téléchargement de fichiers</h1>';

echo '<h2>Informations système</h2>';
echo '<pre>';
echo 'PHP version: ' . phpversion() . "\n";
echo 'max_file_uploads: ' . ini_get('max_file_uploads') . "\n";
echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . "\n";
echo 'post_max_size: ' . ini_get('post_max_size') . "\n";
echo 'max_execution_time: ' . ini_get('max_execution_time') . "\n";
echo 'memory_limit: ' . ini_get('memory_limit') . "\n";
echo '</pre>';

echo '<h2>Vérification des dossiers</h2>';
echo '<pre>';
$uploadDirs = [
    'assets/uploads/',
    'assets/uploads/profiles/',
    'assets/images/',
    'assets/images/profiles/'
];

foreach ($uploadDirs as $dir) {
    echo "Dossier: $dir - ";
    if (file_exists($dir)) {
        echo "Existe";
        if (is_writable($dir)) {
            echo ", Accessible en écriture";
        } else {
            echo ", NON accessible en écriture";
        }
        echo ", Permissions: " . substr(sprintf('%o', fileperms($dir)), -4);
    } else {
        echo "N'existe PAS";
        // Essayer de le créer
        echo " - Tentative de création: ";
        if (mkdir($dir, 0777, true)) {
            echo "Succès";
        } else {
            echo "Échec";
        }
    }
    echo "\n";
}
echo '</pre>';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<h2>Données soumises</h2>';
    echo '<pre>';
    echo "POST data: \n";
    print_r($_POST);
    
    echo "\nFILES data: \n";
    print_r($_FILES);
    echo '</pre>';
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'assets/uploads/profiles/';
        
        // Vérifier que le dossier existe
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                echo "<p style='color:red'>Impossible de créer le dossier $uploadDir</p>";
            }
        }
        
        // Générer un nom unique
        $fileName = 'test_' . time() . '_' . $_FILES['profile_picture']['name'];
        $targetFile = $uploadDir . $fileName;
        
        echo "<h3>Tentative de téléchargement</h3>";
        echo "<pre>";
        echo "Fichier source: " . $_FILES['profile_picture']['tmp_name'] . "\n";
        echo "Cible: " . $targetFile . "\n";
        
        // Essayer de déplacer le fichier
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
            echo "<p style='color:green'>Fichier téléchargé avec succès à: $targetFile</p>";
            
            // Vérifier si le fichier est accessible
            if (file_exists($targetFile)) {
                echo "Le fichier existe sur le serveur\n";
                echo "Taille: " . filesize($targetFile) . " octets\n";
                echo "Permissions: " . substr(sprintf('%o', fileperms($targetFile)), -4) . "\n";
            } else {
                echo "<p style='color:red'>Le fichier n'existe pas après téléchargement!</p>";
            }
        } else {
            echo "<p style='color:red'>Échec du téléchargement</p>";
            
            // Afficher les erreurs
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => "La taille du fichier dépasse la limite définie dans le php.ini",
                UPLOAD_ERR_FORM_SIZE => "La taille du fichier dépasse la limite définie dans le formulaire HTML",
                UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé",
                UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été téléchargé",
                UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant",
                UPLOAD_ERR_CANT_WRITE => "Échec d'écriture du fichier sur le disque",
                UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté le téléchargement"
            ];
            
            if (isset($_FILES['profile_picture']['error'])) {
                $error = $_FILES['profile_picture']['error'];
                echo "Code d'erreur: " . $error . "\n";
                echo "Message: " . ($uploadErrors[$error] ?? "Erreur inconnue") . "\n";
            }
        }
        echo "</pre>";
    } elseif (isset($_FILES['profile_picture'])) {
        echo "<h3>Erreur de téléchargement</h3>";
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => "La taille du fichier dépasse la limite définie dans le php.ini",
            UPLOAD_ERR_FORM_SIZE => "La taille du fichier dépasse la limite définie dans le formulaire HTML",
            UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé",
            UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été téléchargé",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant",
            UPLOAD_ERR_CANT_WRITE => "Échec d'écriture du fichier sur le disque",
            UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté le téléchargement"
        ];
        
        $error = $_FILES['profile_picture']['error'];
        echo "<p style='color:red'>Code d'erreur: " . $error . "</p>";
        echo "<p style='color:red'>Message: " . ($uploadErrors[$error] ?? "Erreur inconnue") . "</p>";
    }
}
?>

<h2>Formulaire de test</h2>
<form action="" method="POST" enctype="multipart/form-data">
    <div>
        <label for="username">Nom d'utilisateur:</label>
        <input type="text" id="username" name="username" value="TestUser">
    </div>
    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="test@example.com">
    </div>
    <div>
        <label for="profile_picture">Photo de profil:</label>
        <input type="file" id="profile_picture" name="profile_picture">
    </div>
    <div>
        <button type="submit">Tester l'upload</button>
    </div>
</form>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2, h3 { color: #333; }
    pre { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; }
    form div { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; }
    input[type="text"], input[type="email"] { padding: 5px; width: 300px; }
    button { padding: 8px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
</style> 