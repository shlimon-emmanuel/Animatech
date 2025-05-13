<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Cache Redis - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cache-stats {
            background-color: rgba(10, 10, 31, 0.8);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px var(--neon-purple);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stats-card {
            background-color: rgba(5, 5, 16, 0.7);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 0 5px var(--neon-blue);
        }
        
        .stats-card h3 {
            color: var(--neon-blue);
            margin-top: 0;
        }
        
        .stats-value {
            font-size: 24px;
            color: white;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .cache-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        
        .cache-actions form {
            flex: 1;
            min-width: 250px;
        }
        
        .cache-section {
            background-color: rgba(10, 10, 31, 0.8);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px var(--neon-purple);
        }
        
        .cache-section h2 {
            color: var(--neon-purple);
            margin-top: 0;
            border-bottom: 1px solid rgba(157, 78, 221, 0.5);
            padding-bottom: 10px;
        }
        
        .nosql-info {
            background-color: rgba(5, 5, 16, 0.7);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            border-left: 3px solid var(--neon-blue);
        }
        
        .nosql-info h3 {
            color: var(--neon-blue);
            margin-top: 0;
        }
    </style>
</head>
<body>
    <?php require_once APP_PATH . '/Views/includes/header.php'; ?>
    
    <div class="container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <h1 style="text-align: center; color: var(--neon-purple); margin-bottom: 30px;">Gestion du Cache Redis (NoSQL)</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="cache-section">
            <h2><i class="fas fa-database"></i> Base de données NoSQL Redis</h2>
            
            <div class="nosql-info">
                <h3>À propos de Redis</h3>
                <p>Redis est une base de données NoSQL de type "key-value store" utilisée ici pour mettre en cache les résultats de l'API TMDB.</p>
                <p>Avantages :</p>
                <ul>
                    <li>Performance ultra-rapide (stockage en mémoire)</li>
                    <li>Réduction des appels API externes</li>
                    <li>Amélioration du temps de réponse de l'application</li>
                </ul>
            </div>
            
            <div class="stats-grid">
                <div class="stats-card">
                    <h3>État de la connexion</h3>
                    <div class="stats-value">
                        <?= $redisStatus ? '<span style="color: #4CAF50;">Connecté</span>' : '<span style="color: #F44336;">Déconnecté</span>' ?>
                    </div>
                </div>
                
                <div class="stats-card">
                    <h3>Entrées en cache</h3>
                    <div class="stats-value"><?= $cacheCount ?></div>
                </div>
                
                <div class="stats-card">
                    <h3>Utilisation mémoire</h3>
                    <div class="stats-value"><?= $memoryUsage ?></div>
                </div>
                
                <div class="stats-card">
                    <h3>Temps de fonctionnement</h3>
                    <div class="stats-value"><?= $uptime ?></div>
                </div>
            </div>
            
            <div class="cache-actions">
                <form action="index.php?action=admin&subaction=clearCache" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="neon-button" name="clear_all" value="1">
                        <i class="fas fa-trash"></i> Vider tout le cache
                    </button>
                </form>
                
                <form action="index.php?action=admin&subaction=clearCache" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="neon-button" name="clear_movies" value="1">
                        <i class="fas fa-film"></i> Vider cache des films
                    </button>
                </form>
            </div>
        </div>
        
        <div class="cache-section">
            <h2><i class="fas fa-chart-line"></i> Performance du cache</h2>
            
            <p>Le cache Redis NoSQL permet d'obtenir des gains de performance considérables :</p>
            
            <div class="stats-grid">
                <div class="stats-card">
                    <h3>Hits du cache</h3>
                    <div class="stats-value"><?= $cacheHits ?></div>
                    <div>Requêtes servies depuis le cache</div>
                </div>
                
                <div class="stats-card">
                    <h3>Taux de succès</h3>
                    <div class="stats-value"><?= $hitRate ?>%</div>
                    <div>Pourcentage d'utilisation du cache</div>
                </div>
                
                <div class="stats-card">
                    <h3>Temps économisé</h3>
                    <div class="stats-value"><?= $timeSaved ?> sec</div>
                    <div>Temps économisé grâce au cache</div>
                </div>
                
                <div class="stats-card">
                    <h3>Requêtes API évitées</h3>
                    <div class="stats-value"><?= $apiRequestsAvoided ?></div>
                    <div>Appels API TMDB économisés</div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once APP_PATH . '/Views/partials/footer.php'; ?>
</body>
</html> 