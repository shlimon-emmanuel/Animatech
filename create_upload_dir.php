<?php
// Ce script crée le dossier d'uploads pour les photos de profil et vérifie/définit les permissions

echo "<h1>Configuration du dossier d'upload</h1>";

// Définir le chemin du dossier d'upload
$uploadDir = __DIR__ . '/assets/uploads/profiles';

echo "<p>Dossier d'upload: " . $uploadDir . "</p>";

// Supprimer le dossier s'il existe déjà (pour repartir de zéro)
if (file_exists($uploadDir)) {
    echo "<p>Le dossier existe déjà. Tentative de suppression pour recréation...</p>";
    try {
        rmdir($uploadDir);
        echo "<p>Dossier supprimé avec succès.</p>";
    } catch (Exception $e) {
        echo "<p>Erreur lors de la suppression du dossier: " . $e->getMessage() . "</p>";
    }
}

// Supprimer le dossier parent s'il existe
$parentDir = __DIR__ . '/assets/uploads';
if (file_exists($parentDir)) {
    echo "<p>Le dossier parent existe déjà. Tentative de suppression pour recréation...</p>";
    try {
        rmdir($parentDir);
        echo "<p>Dossier parent supprimé avec succès.</p>";
    } catch (Exception $e) {
        echo "<p>Erreur lors de la suppression du dossier parent: " . $e->getMessage() . "</p>";
    }
}

// Créer le dossier parent s'il n'existe pas
if (!file_exists($parentDir)) {
    echo "<p>Création du dossier parent...</p>";
    if (mkdir($parentDir, 0777, false)) {
        echo "<p>✅ Dossier parent créé avec succès.</p>";
    } else {
        echo "<p>❌ Échec de la création du dossier parent.</p>";
    }
}

// Créer le dossier d'upload s'il n'existe pas
if (!file_exists($uploadDir)) {
    echo "<p>Création du dossier d'upload...</p>";
    if (mkdir($uploadDir, 0777, false)) {
        echo "<p>✅ Dossier d'upload créé avec succès.</p>";
    } else {
        echo "<p>❌ Échec de la création du dossier d'upload.</p>";
    }
}

// Définir les permissions
if (file_exists($uploadDir)) {
    echo "<p>Définition des permissions du dossier d'upload...</p>";
    if (chmod($uploadDir, 0777)) {
        echo "<p>✅ Permissions définies avec succès.</p>";
    } else {
        echo "<p>❌ Échec de la définition des permissions.</p>";
    }
}

// Définir les permissions du dossier parent
if (file_exists($parentDir)) {
    echo "<p>Définition des permissions du dossier parent...</p>";
    if (chmod($parentDir, 0777)) {
        echo "<p>✅ Permissions du dossier parent définies avec succès.</p>";
    } else {
        echo "<p>❌ Échec de la définition des permissions du dossier parent.</p>";
    }
}

// Vérifier les permissions
if (file_exists($uploadDir)) {
    $perms = substr(sprintf('%o', fileperms($uploadDir)), -4);
    echo "<p>Permissions actuelles du dossier d'upload: " . $perms . "</p>";
}

if (file_exists($parentDir)) {
    $perms = substr(sprintf('%o', fileperms($parentDir)), -4);
    echo "<p>Permissions actuelles du dossier parent: " . $perms . "</p>";
}

// Définir également les permissions du dossier assets
$assetsDir = __DIR__ . '/assets';
if (file_exists($assetsDir)) {
    echo "<p>Définition des permissions du dossier assets...</p>";
    if (chmod($assetsDir, 0777)) {
        echo "<p>✅ Permissions du dossier assets définies avec succès.</p>";
    } else {
        echo "<p>❌ Échec de la définition des permissions du dossier assets.</p>";
    }
    
    $perms = substr(sprintf('%o', fileperms($assetsDir)), -4);
    echo "<p>Permissions actuelles du dossier assets: " . $perms . "</p>";
}

// Créer un fichier de test
if (file_exists($uploadDir)) {
    echo "<p>Création d'un fichier de test...</p>";
    $testFile = $uploadDir . '/test.txt';
    if (file_put_contents($testFile, 'Test de permissions')) {
        echo "<p>✅ Fichier de test créé avec succès: " . $testFile . "</p>";
    } else {
        echo "<p>❌ Échec de la création du fichier de test.</p>";
    }
}

echo "<h2>Instructions</h2>";
echo "<p>Si le script a correctement créé et défini les permissions des dossiers, vous devriez maintenant pouvoir uploader des photos de profil.</p>";
echo "<p>Retournez à votre <a href='index.php?action=profile'>page de profil</a> et essayez à nouveau d'uploader une photo.</p>";
?> 