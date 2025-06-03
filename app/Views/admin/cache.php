<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Cache NoSQL - Administration</title>
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Styles CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .cache-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: rgba(30, 30, 50, 0.7);
            border: 1px solid var(--neon-purple);
            border-radius: 10px;
            padding: 20px;
            flex: 1;
            min-width: 200px;
            text-align: center;
            box-shadow: 0 0 15px rgba(149, 0, 255, 0.2);
        }
        .stat-card h3 {
            color: var(--neon-blue);
            margin-top: 0;
            font-size: 18px;
        }
        .stat-card .value {
            font-size: 24px;
            color: var(--neon-pink);
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-card p {
            color: #ccc;
            font-size: 14px;
            margin-bottom: 0;
        }
        .collection-list {
            margin-top: 20px;
            background: rgba(30, 30, 50, 0.5);
            border-radius: 10px;
            padding: 15px;
        }
        .collection-list h4 {
            color: var(--neon-blue);
            margin-top: 0;
        }
        .collection-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .collection-item:last-child {
            border-bottom: none;
        }
        .collection-name {
            color: #fff;
        }
        .collection-count {
            color: var(--neon-green);
            font-weight: bold;
        }
        .cache-actions {
            margin-top: 30px;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .status-on {
            background-color: #4CAF50;
        }
        .status-off {
            background-color: #F44336;
        }
    </style>
</head>
<body>
    <?php include_once ROOT_PATH . '/app/Views/admin/includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include_once ROOT_PATH . '/app/Views/admin/includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="content-header">
                <h1>Gestion du Cache</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Cache NoSQL</li>
                    </ol>
                </nav>
            </div>
            
            <?php include_once ROOT_PATH . '/app/Views/admin/includes/alerts.php'; ?>
            
            <div class="content-body">
                <h1 style="text-align: center; color: var(--neon-purple); margin-bottom: 30px;">Gestion du Cache NoSQL (JSON)</h1>
                
                <div class="admin-card">
                    <div class="card-header">
                        <h2><i class="fas fa-tachometer-alt"></i> État et statistiques</h2>
                    </div>
                    <div class="card-body">
                        <p class="card-description">
                            Ce tableau de bord vous permet de gérer le cache NoSQL et de consulter ses statistiques.
                        </p>
                        
                        <div class="section-divider"></div>
                        
                        <h2><i class="fas fa-database"></i> Base de données NoSQL JSON</h2>
                        
                        <div class="info-box">
                            <h3>À propos du cache JSON</h3>
                            <p>Notre système de cache NoSQL basé sur JSON stocke les données dans des fichiers organisés en collections, similaire à une base de données NoSQL. Il est utilisé pour mettre en cache les résultats de l'API TMDB.</p>
                            <p>Avantages :</p>
                            <ul>
                                <li>Aucune installation de logiciel supplémentaire requise</li>
                                <li>Performances optimisées pour les requêtes fréquentes</li>
                                <li>Organisation des données en collections</li>
                                <li>Gestion automatique de l'expiration des données</li>
                            </ul>
                        </div>
                        
                        <div class="section-divider"></div>
                        
                        <h3><i class="fas fa-power-off"></i> État du système de cache</h3>
                        <p>
                            <span class="status-indicator <?= $cacheStatus ? 'status-on' : 'status-off' ?>"></span>
                            <strong>Statut :</strong> 
                            <?= $cacheStatus ? '<span style="color: #4CAF50;">Disponible</span>' : '<span style="color: #F44336;">Non disponible</span>' ?>
                        </p>
                        
                        <?php if ($cacheStatus): ?>
                            <div class="cache-stats">
                                <div class="stat-card">
                                    <h3>Taille totale</h3>
                                    <div class="value"><?= $info['total_size_formatted'] ?? '0 B' ?></div>
                                    <p>Espace disque utilisé</p>
                                </div>
                                
                                <div class="stat-card">
                                    <h3>Documents</h3>
                                    <div class="value"><?= $info['document_count'] ?? 0 ?></div>
                                    <p>Nombre total d'entrées</p>
                                </div>
                                
                                <div class="stat-card">
                                    <h3>Collections</h3>
                                    <div class="value"><?= count($info['collections'] ?? []) ?></div>
                                    <p>Groupes de données</p>
                                </div>
                                
                                <div class="stat-card">
                                    <h3>Uptime</h3>
                                    <div class="value"><?= $info['uptime_formatted'] ?? '-' ?></div>
                                    <p>Temps de fonctionnement</p>
                                </div>
                            </div>
                            
                            <?php if (!empty($info['collections'])): ?>
                                <div class="collection-list">
                                    <h4>Collections de données</h4>
                                    <?php foreach ($info['collections'] as $name => $count): ?>
                                        <div class="collection-item">
                                            <span class="collection-name"><?= htmlspecialchars($name) ?></span>
                                            <span class="collection-count"><?= $count ?> documents</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="section-divider"></div>
                            
                            <h3><i class="fas fa-chart-line"></i> Métriques de performance</h3>
                            <p>Le cache NoSQL permet d'obtenir des gains de performance considérables :</p>
                            
                            <div class="cache-stats">
                                <div class="stat-card">
                                    <h3>Cache Hits</h3>
                                    <div class="value"><?= $stats['hits'] ?? 0 ?></div>
                                    <p>Requêtes servies depuis le cache</p>
                                </div>
                                
                                <div class="stat-card">
                                    <h3>Cache Misses</h3>
                                    <div class="value"><?= $stats['misses'] ?? 0 ?></div>
                                    <p>Requêtes non trouvées dans le cache</p>
                                </div>
                                
                                <div class="stat-card">
                                    <h3>Temps économisé</h3>
                                    <div class="value"><?= number_format(($stats['time_saved'] ?? 0), 1) ?> sec</div>
                                    <p>Temps gagné grâce au cache</p>
                                </div>
                                
                                <div class="stat-card">
                                    <h3>API économisées</h3>
                                    <div class="value"><?= $stats['requests_avoided'] ?? 0 ?></div>
                                    <p>Appels API évités</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Le système de cache NoSQL n'est pas disponible. Vérifiez les permissions du dossier <code>storage/nosql</code>.</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="section-divider"></div>
                        
                        <div class="cache-actions">
                            <h3><i class="fas fa-broom"></i> Actions de maintenance</h3>
                            
                            <form action="<?= BASE_URL ?>/admin/cache/clear" method="post" class="admin-form">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                
                                <div class="form-group">
                                    <label for="cache_type">Type de vidage :</label>
                                    <select name="cache_type" id="cache_type" class="form-control">
                                        <option value="all">Tout le cache</option>
                                        <option value="movies">Films populaires</option>
                                        <option value="search">Résultats de recherche</option>
                                        <option value="details">Détails des films</option>
                                    </select>
                                </div>
                                
                                <div class="button-group">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash-alt"></i> Vider le cache
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="<?= BASE_URL ?>/assets/js/admin.js"></script>
</body>
</html> 