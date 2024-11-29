<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootPath = dirname(dirname(__DIR__));
echo "Root Path: " . $rootPath . "\n<br>";

// Afficher la structure des dossiers
function listDirectory($dir, $indent = '') {
    echo $indent . basename($dir) . "/\n<br>";
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                listDirectory($path, $indent . '&nbsp;&nbsp;&nbsp;&nbsp;');
            } else {
                echo $indent . '&nbsp;&nbsp;&nbsp;&nbsp;' . $file . "\n<br>";
            }
        }
    }
}

echo "Structure du projet :\n<br>";
listDirectory($rootPath);

// Vérifier spécifiquement le fichier config.php
$configPath = $rootPath . '/app/config/config.php';
echo "\nVérification du fichier config :\n<br>";
echo "Chemin complet : " . $configPath . "\n<br>";
echo "Existe : " . (file_exists($configPath) ? 'Oui' : 'Non') . "\n<br>";
echo "Est lisible : " . (is_readable($configPath) ? 'Oui' : 'Non') . "\n<br>"; 