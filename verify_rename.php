<?php
// Script pour vérifier si tous les noms ont bien été changés
echo "<h1>Vérification des changements de nom</h1>";

$files_to_check = [
    'app/Views/partials/header.php' => ['MANGA API', 'Manga API'],
    'app/Views/partials/footer.php' => ['Manga API'],
    'app/Views/admin/dashboard.php' => ['MANGA API', 'Manga API'],
    'app/config/config.php' => ['Manga API'],
    'app/Views/includes/header.php' => ['Cinetech']
];

$all_good = true;

foreach ($files_to_check as $file => $strings) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        $found_string = false;
        foreach ($strings as $string) {
            if (strpos($content, $string) !== false) {
                $found_string = true;
                echo "<p style='color:red'>✕ Le fichier <b>$file</b> contient encore la chaîne \"$string\"</p>";
                $all_good = false;
            }
        }
        
        if (!$found_string) {
            echo "<p style='color:green'>✓ Le fichier <b>$file</b> a été correctement modifié</p>";
        }
    } else {
        echo "<p style='color:orange'>⚠ Le fichier <b>$file</b> n'existe pas</p>";
    }
}

if ($all_good) {
    echo "<h2 style='color:green'>Tous les fichiers ont été correctement modifiés!</h2>";
    echo "<p>Vous pouvez maintenant suivre ces étapes pour finaliser le changement de nom :</p>";
    echo "<ol>
        <li>Arrêtez le serveur PHP en cours d'exécution (Ctrl+C dans le terminal)</li>
        <li>Naviguez vers le dossier parent : <code>cd ..</code></li>
        <li>Renommez le dossier : <code>rename Manga-API-main Animatech</code> (sur Windows) ou <code>mv Manga-API-main Animatech</code> (sur Linux/Mac)</li>
        <li>Naviguez dans le nouveau dossier : <code>cd Animatech</code></li>
        <li>Redémarrez le serveur : <code>php -S localhost:8000</code></li>
    </ol>";
} else {
    echo "<h2 style='color:red'>Certains fichiers n'ont pas été correctement modifiés!</h2>";
    echo "<p>Veuillez corriger les problèmes listés ci-dessus.</p>";
} 