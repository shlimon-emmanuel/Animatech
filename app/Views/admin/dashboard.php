<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord SuperAdmin</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎬</text></svg>" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #0a0a1f 0%, #1a1a2e 100%);
            color: #f8f9fa;
            font-family: 'Rajdhani', sans-serif;
            padding-top: 80px;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--darker-bg) 0%, #1e1e2e 100%);
            box-shadow: 0 4px 20px rgba(0, 243, 255, 0.15);
            border-bottom: 1px solid var(--neon-blue);
        }
        
        .navbar-brand {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue) !important;
            font-weight: 700;
            text-shadow: 0 0 10px rgba(0, 243, 255, 0.5);
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: #fff !important;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 0 5px;
            padding: 8px 15px !important;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(0, 243, 255, 0.2);
            color: var(--neon-blue) !important;
            box-shadow: 0 0 10px rgba(0, 243, 255, 0.3);
        }
        
        .container {
            max-width: 1200px;
            padding: 0 20px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--darker-bg) 0%, #2a2a3e 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            border: 1px solid var(--neon-purple);
            box-shadow: 0 0 20px rgba(157, 78, 221, 0.2);
        }
        
        .admin-header h1 {
            font-family: 'Orbitron', sans-serif;
            color: var(--neon-blue);
            text-shadow: 0 0 15px rgba(0, 243, 255, 0.7);
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        
        .admin-header p {
            color: #aaa;
            font-size: 1.1rem;
            margin: 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .card {
            background: linear-gradient(135deg, var(--darker-bg) 0%, #2a2a3e 100%);
            border: 1px solid var(--neon-purple);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            margin-bottom: 25px;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 243, 255, 0.2);
            border-color: var(--neon-blue);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--neon-purple) 0%, var(--neon-blue) 100%);
            border: none;
            padding: 20px;
            color: white;
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .stat-card {
            text-align: center;
            padding: 30px 20px;
        }
        
        .stat-icon {
            font-size: 3rem;
            color: var(--neon-blue);
            margin-bottom: 15px;
            text-shadow: 0 0 20px rgba(0, 243, 255, 0.5);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--neon-purple);
            font-family: 'Orbitron', sans-serif;
            text-shadow: 0 0 10px rgba(157, 78, 221, 0.5);
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #ccc;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .admin-user-pic {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid var(--neon-blue);
            box-shadow: 0 0 10px rgba(0, 243, 255, 0.3);
        }
        
        .table {
            color: #f8f9fa;
            background-color: transparent;
        }
        
        .table th {
            border-color: var(--neon-purple);
            color: var(--neon-blue);
            font-family: 'Orbitron', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px;
        }
        
        .table td {
            border-color: rgba(157, 78, 221, 0.3);
            padding: 15px;
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 243, 255, 0.1);
        }
        
        .badge.bg-danger {
            background: linear-gradient(45deg, #dc3545, #e74c3c) !important;
            box-shadow: 0 2px 10px rgba(220, 53, 69, 0.3);
        }
        
        .badge.bg-warning {
            background: linear-gradient(45deg, #ffc107, #f39c12) !important;
            color: #212529;
            box-shadow: 0 2px 10px rgba(255, 193, 7, 0.3);
        }
        
        .badge.bg-info {
            background: linear-gradient(45deg, var(--neon-blue), #17a2b8) !important;
            box-shadow: 0 2px 10px rgba(0, 243, 255, 0.3);
        }
        
        .btn {
            border-radius: 8px;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, var(--neon-blue), var(--neon-purple));
            border: none;
            box-shadow: 0 4px 15px rgba(0, 243, 255, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 243, 255, 0.4);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 25px;
        }
        
        .alert-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .admin-header {
                padding: 20px;
                text-align: center;
            }
            
            .admin-header h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px 15px;
            }
            
            .table-responsive {
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">ANIMATECH</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php?action=admin">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=admin&subaction=users">Utilisateurs</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=logout">Déconnexion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error'] ?>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</div>

<div class="container admin-dashboard">
    <!-- Header Section -->
    <div class="admin-header">
        <h1><i class="fas fa-user-shield"></i> Tableau de bord SuperAdmin</h1>
        <p><i class="fas fa-info-circle"></i> Bienvenue dans l'interface SuperAdmin. Vous pouvez gérer tous les utilisateurs du site.</p>
    </div>
    
    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="card">
            <div class="card-body stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?= count($users) ?></div>
                <div class="stat-label">Utilisateurs inscrits</div>
                <a href="index.php?action=admin&subaction=users" class="btn btn-primary mt-3">
                    <i class="fas fa-user-cog"></i> Gérer les utilisateurs
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-number">
                    <?php 
                    $modCount = 0;
                    foreach($users as $user) {
                        if(isset($user['role']) && ($user['role'] === 'admin' || $user['role'] === 'moderator')) {
                            $modCount++;
                        }
                    }
                    echo $modCount;
                    ?>
                </div>
                <div class="stat-label">Modérateurs</div>
                <a href="index.php?action=admin&subaction=users" class="btn btn-primary mt-3">
                    <i class="fas fa-user-edit"></i> Modifier profils
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body stat-card">
                <div class="stat-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="stat-number">
                    <?php 
                    $activeUsers = 0;
                    foreach($users as $user) {
                        if(isset($user['created_at']) && strtotime($user['created_at']) > strtotime('-30 days')) {
                            $activeUsers++;
                        }
                    }
                    echo $activeUsers;
                    ?>
                </div>
                <div class="stat-label">Nouveaux (30j)</div>
                <a href="index.php?action=profile" class="btn btn-primary mt-3">
                    <i class="fas fa-cog"></i> Mon profil
                </a>
            </div>
        </div>
    </div>
    
    <!-- Recent Users Table -->
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-users"></i> Utilisateurs récents</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Photo</th>
                                    <th>Nom d'utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Date d'inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recentUsers = array_slice($users, 0, 5); 
                                foreach ($recentUsers as $user): 
                                ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                                            <img src="<?= $user['profile_picture'] ?>" alt="Photo de profil" class="admin-user-pic">
                                        <?php else: ?>
                                            <img src="assets/img/default-profile.png" alt="Photo par défaut" class="admin-user-pic">
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge <?= $user['role'] === 'superadmin' ? 'bg-danger' : ($user['role'] === 'admin' ? 'bg-warning' : 'bg-info') ?>">
                                            <?= ucfirst($user['role'] ?? 'user') ?>
                                        </span>
                                    </td>
                                    <td><?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'N/A' ?></td>
                                    <td>
                                        <a href="index.php?action=admin&subaction=editUser&id=<?= $user['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (($user['role'] ?? 'user') !== 'superadmin'): ?>
                                            <a href="index.php?action=admin&subaction=deleteUser&id=<?= $user['id'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="index.php?action=admin&subaction=users" class="btn btn-primary">
                            <i class="fas fa-list"></i> Voir tous les utilisateurs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-light py-3 mt-5">
    <div class="container">
        <p class="text-center">&copy; <?= date('Y') ?> ANIMATECH - Panel Admin</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 