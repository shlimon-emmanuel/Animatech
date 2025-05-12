<?php
/**
 * Script de vérification post-renommage
 * Ce script analyse le fonctionnement de l'application après le renommage du dossier principal
 */

// Démarrer une session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour vérifier l'accessibilité d'une URL
function checkUrl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $responseCode;
}

// Vérifier les fichiers essentiels
$essentialFiles = [
    'index.php',
    'app/config/config.php',
    'app/public/index.php',
    '.htaccess',
    'app/Views/template/header.php',
    'app/Views/template/footer.php',
    'app/Controllers/MovieController.php',
    'app/Controllers/AuthController.php',
    'app/Models/UserModel.php',
    'assets/css/style.css'
];

$missingFiles = [];
foreach ($essentialFiles as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        $missingFiles[] = $file;
    }
}

// Vérifier la connexion à la base de données
$dbConnectionOk = false;
$dbError = "";
if (file_exists(__DIR__ . '/app/config/config.php')) {
    require_once __DIR__ . '/app/config/config.php';
    
    try {
        $dbh = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", 
            DB_USER, 
            DB_PASS, 
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $dbConnectionOk = true;
    } catch (PDOException $e) {
        $dbError = $e->getMessage();
    }
}

// Vérifier l'accessibilité des assets
$assetsPaths = [
    'css' => 'assets/css/style.css',
    'js' => 'assets/js/main.js',
    'img' => 'assets/img/default-profile.png'
];

$assetsStatus = [];
$baseUrl = isset($_SERVER['HTTP_HOST']) ? 
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] : '';

foreach ($assetsPaths as $key => $path) {
    if (file_exists(__DIR__ . '/' . $path)) {
        $testUrl = $baseUrl . '/' . basename(__DIR__) . '/' . $path;
        $status = checkUrl($testUrl);
        $assetsStatus[$key] = [
            'path' => $path,
            'status' => $status >= 200 && $status < 400 ? 'OK' : 'Erreur (' . $status . ')',
            'code' => $status
        ];
    } else {
        $assetsStatus[$key] = [
            'path' => $path,
            'status' => 'Fichier manquant',
            'code' => 0
        ];
    }
}

// Compiler les résultats
$allOk = empty($missingFiles) && $dbConnectionOk && 
         !in_array(false, array_map(function($item) { 
             return $item['code'] >= 200 && $item['code'] < 400;
         }, $assetsStatus));

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification post-renommage</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #0a0a1a;
            color: #ddd;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #141428;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(100, 100, 255, 0.1);
            border: 1px solid #7e57c2;
        }
        h1, h2 {
            color: #bb86fc;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .info-box {
            background-color: #191930;
            border-left: 4px solid #bb86fc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success-box {
            background-color: #0c2a1c;
            border-left: 4px solid #03dac6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .warning-box {
            background-color: #2a1c0c;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error-box {
            background-color: #2a0c0c;
            border-left: 4px solid #cf6679;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .status-ok {
            background-color: #03dac6;
        }
        .status-warning {
            background-color: #ff9800;
        }
        .status-error {
            background-color: #cf6679;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #191930;
            border-radius: 4px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #2a2a40;
        }
        th {
            background-color: #222240;
            color: #bb86fc;
            font-weight: bold;
        }
        tr:hover {
            background-color: #1e1e38;
        }
        .btn {
            display: inline-block;
            background-color: #bb86fc;
            color: #000;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #9d65fa;
        }
        .btn-secondary {
            background-color: #303050;
            color: #fff;
        }
        .btn-secondary:hover {
            background-color: #404060;
        }
        .summary {
            font-size: 1.2em;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border-radius: 4px;
        }
        .summary.success {
            background-color: #0c2a1c;
            color: #03dac6;
        }
        .summary.error {
            background-color: #2a0c0c;
            color: #cf6679;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Vérification post-renommage</h1>
        
        <div class="summary <?php echo $allOk ? 'success' : 'error'; ?>">
            <?php if ($allOk): ?>
                ✅ Tout fonctionne correctement !
            <?php else: ?>
                ⚠️ Des problèmes ont été détectés.
            <?php endif; ?>
        </div>
        
        <!-- Vérification des fichiers -->
        <div class="<?php echo empty($missingFiles) ? 'success-box' : 'error-box'; ?>">
            <h2>Fichiers essentiels</h2>
            <?php if (empty($missingFiles)): ?>
                <p>✅ Tous les fichiers essentiels sont présents.</p>
            <?php else: ?>
                <p>❌ Certains fichiers essentiels sont manquants:</p>
                <ul>
                    <?php foreach ($missingFiles as $file): ?>
                    <li><?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <!-- Vérification de la base de données -->
        <div class="<?php echo $dbConnectionOk ? 'success-box' : 'error-box'; ?>">
            <h2>Connexion à la base de données</h2>
            <?php if ($dbConnectionOk): ?>
                <p>✅ La connexion à la base de données fonctionne correctement.</p>
            <?php else: ?>
                <p>❌ Erreur de connexion à la base de données:</p>
                <p><?php echo htmlspecialchars($dbError); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Vérification des assets -->
        <div class="info-box">
            <h2>Accessibilité des assets</h2>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Chemin</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assetsStatus as $type => $data): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(ucfirst($type)); ?></td>
                        <td><?php echo htmlspecialchars($data['path']); ?></td>
                        <td>
                            <?php 
                            $statusClass = 'status-error';
                            if ($data['code'] >= 200 && $data['code'] < 400) {
                                $statusClass = 'status-ok';
                            } elseif ($data['code'] > 0) {
                                $statusClass = 'status-warning';
                            }
                            ?>
                            <span class="status-indicator <?php echo $statusClass; ?>"></span>
                            <?php echo htmlspecialchars($data['status']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Suggestions pour résoudre les problèmes -->
        <?php if (!$allOk): ?>
        <div class="warning-box">
            <h2>Suggestions pour résoudre les problèmes</h2>
            
            <?php if (!empty($missingFiles)): ?>
            <p><strong>Fichiers manquants:</strong></p>
            <ul>
                <li>Vérifiez si les fichiers ont été correctement copiés lors du renommage.</li>
                <li>Restaurez ces fichiers depuis votre sauvegarde.</li>
            </ul>
            <?php endif; ?>
            
            <?php if (!$dbConnectionOk): ?>
            <p><strong>Problèmes de base de données:</strong></p>
            <ul>
                <li>Vérifiez que les paramètres de connexion dans app/config/config.php sont corrects.</li>
                <li>Assurez-vous que le serveur MySQL est en cours d'exécution.</li>
                <li>Vérifiez que la base de données existe et que l'utilisateur a les droits appropriés.</li>
            </ul>
            <?php endif; ?>
            
            <?php if (count(array_filter($assetsStatus, function($item) { return $item['code'] < 200 || $item['code'] >= 400; })) > 0): ?>
            <p><strong>Problèmes d'assets:</strong></p>
            <ul>
                <li>Vérifiez que le chemin relatif des assets est correct dans votre configuration.</li>
                <li>Assurez-vous que le serveur web a les droits d'accès aux dossiers d'assets.</li>
                <li>Vérifiez que la configuration .htaccess permet l'accès aux assets.</li>
            </ul>
            <?php endif; ?>
            
            <p>Si les problèmes persistent, vous pouvez revenir à la configuration précédente en utilisant votre sauvegarde.</p>
        </div>
        <?php endif; ?>
        
        <!-- Actions possibles -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn">Aller à la page d'accueil</a>
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-secondary">Rafraîchir la vérification</a>
        </div>
    </div>
</body>
</html> 