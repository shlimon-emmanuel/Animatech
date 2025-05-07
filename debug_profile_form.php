<?php
/**
 * Script de débogage pour le formulaire de profil
 * Placez ce script en tant que cible du formulaire pour voir les données soumises
 */

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Paramètres de base pour l'affichage
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<html><head><title>Débogage du formulaire de profil</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    .section { background: #f5f5f5; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    pre { background: #e0e0e0; padding: 10px; border-radius: 4px; overflow: auto; }
    table { border-collapse: collapse; width: 100%; }
    table, th, td { border: 1px solid #ddd; }
    th, td { padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .button { display: inline-block; margin: 10px 0; padding: 10px 15px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; }
    .button.green { background: #4CAF50; }
</style></head><body>";

echo "<h1>Débogage du formulaire de profil</h1>";

// Vérifier l'utilisateur connecté
echo "<div class='section'>";
echo "<h2>Utilisateur connecté</h2>";
if (isset($_SESSION['user_id']) || isset($_SESSION['user']['id'])) {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null);
    echo "<p>ID Utilisateur: <strong>" . $userId . "</strong></p>";
} else {
    echo "<p style='color:red;'>Aucun utilisateur connecté!</p>";
}
echo "</div>";

// Vérifier la méthode de la requête
echo "<div class='section'>";
echo "<h2>Méthode de requête</h2>";
echo "<p>Méthode utilisée: <strong>" . $_SERVER['REQUEST_METHOD'] . "</strong></p>";
echo "</div>";

// Afficher les données POST
echo "<div class='section'>";
echo "<h2>Données POST</h2>";
if (!empty($_POST)) {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} else {
    echo "<p>Aucune donnée POST reçue.</p>";
}
echo "</div>";

// Afficher les données FILES
echo "<div class='section'>";
echo "<h2>Fichiers uploadés (FILES)</h2>";
if (!empty($_FILES)) {
    echo "<table>";
    echo "<tr><th>Nom</th><th>Type</th><th>Taille</th><th>Erreur</th><th>Chemin temporaire</th></tr>";
    
    foreach ($_FILES as $key => $file) {
        echo "<tr>";
        echo "<td>{$key}</td>";
        echo "<td>{$file['type']}</td>";
        echo "<td>{$file['size']} octets</td>";
        echo "<td>" . getUploadErrorMessage($file['error']) . "</td>";
        echo "<td>{$file['tmp_name']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
} else {
    echo "<p>Aucun fichier uploadé.</p>";
}
echo "</div>";

// Afficher les variables de session
echo "<div class='section'>";
echo "<h2>Variables de session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "</div>";

// Ajouter un formulaire pour transmettre les données au contrôleur
echo "<div class='section'>";
echo "<h2>Transmettre les données au contrôleur</h2>";

// Si nous avons des données POST, on crée un formulaire caché pour les transmettre
if (!empty($_POST)) {
    echo "<form action='index.php?action=updateProfile' method='POST' enctype='multipart/form-data' id='forward-form'>";
    
    // Recréer tous les champs POST
    foreach ($_POST as $key => $value) {
        echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
    }
    
    // Si on a un fichier uploadé, on ne peut pas le transmettre directement
    // On affiche juste un message
    if (!empty($_FILES) && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        echo "<p style='color: orange;'>Note: Le fichier uploadé ne peut pas être transmis automatiquement. Vous devrez le sélectionner à nouveau.</p>";
        echo "<div style='margin: 15px 0;'>";
        echo "<label for='re_upload' style='display:block; margin-bottom:5px;'>Sélectionnez le fichier à nouveau:</label>";
        echo "<input type='file' name='profile_picture' id='re_upload'>";
        echo "</div>";
    }
    
    echo "<button type='submit' class='button green'>Transmettre au contrôleur</button>";
    echo "</form>";
} else {
    echo "<p>Aucune donnée à transmettre.</p>";
}

echo "</div>";

// Fonction pour obtenir un message d'erreur d'upload lisible
function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_OK:
            return "Aucune erreur";
        case UPLOAD_ERR_INI_SIZE:
            return "Le fichier dépasse la taille maximale définie dans php.ini";
        case UPLOAD_ERR_FORM_SIZE:
            return "Le fichier dépasse la taille maximale définie dans le formulaire HTML";
        case UPLOAD_ERR_PARTIAL:
            return "Le fichier n'a été que partiellement uploadé";
        case UPLOAD_ERR_NO_FILE:
            return "Aucun fichier n'a été uploadé";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Le dossier temporaire est manquant";
        case UPLOAD_ERR_CANT_WRITE:
            return "Impossible d'écrire le fichier sur le disque";
        case UPLOAD_ERR_EXTENSION:
            return "L'upload a été arrêté par une extension PHP";
        default:
            return "Erreur inconnue ($errorCode)";
    }
}

// Bouton pour retourner à la page de profil
echo "<a href='index.php?action=profile' class='button'>Retour à la page de profil</a>";
echo " <a href='php_error_log.php' class='button'>Voir les erreurs PHP</a>";

echo "</body></html>";
?> 