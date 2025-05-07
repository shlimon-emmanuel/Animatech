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
    <style>
        body {
            background-color: #121212;
            color: #f8f9fa;
            font-family: 'Rajdhani', sans-serif;
            padding-top: 60px;
        }
        
        .navbar {
            background-color: #1e1e1e;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .navbar-brand {
            font-family: 'Orbitron', sans-serif;
            color: #7b2cbf;
            font-weight: 700;
        }
        
        .card {
            background-color: #1e1e1e;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .admin-dashboard .card {
            transition: all 0.3s ease;
        }
        
        .admin-dashboard .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .admin-user-pic {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .admin-dashboard .fa-3x {
            color: #007bff;
        }
        
        .table {
            color: #f8f9fa;
        }
        
        .badge.bg-danger {
            background-color: #dc3545 !important;
        }
        
        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #212529;
        }
        
        .badge.bg-info {
            background-color: #17a2b8 !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">MANGA API</a>
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
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="my-4">Tableau de bord SuperAdmin</h1>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Bienvenue dans l'interface SuperAdmin. Vous pouvez gérer tous les utilisateurs du site.
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card admin-card">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x mb-3"></i>
                    <h3><?= count($users) ?></h3>
                    <h5>Utilisateurs inscrits</h5>
                    <a href="index.php?action=admin&subaction=users" class="btn btn-primary mt-3">
                        <i class="fas fa-user-cog"></i> Gérer les utilisateurs
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card admin-card">
                <div class="card-body text-center">
                    <i class="fas fa-user-shield fa-3x mb-3"></i>
                    <h3>Modération</h3>
                    <h5>Gérer les profils</h5>
                    <a href="index.php?action=admin&subaction=users" class="btn btn-primary mt-3">
                        <i class="fas fa-user-edit"></i> Modifier profils
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card admin-card">
                <div class="card-body text-center">
                    <i class="fas fa-cogs fa-3x mb-3"></i>
                    <h3>Paramètres</h3>
                    <h5>Configuration</h5>
                    <a href="index.php?action=profile" class="btn btn-primary mt-3">
                        <i class="fas fa-cog"></i> Mon profil
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-users"></i> Utilisateurs récents</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
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
        <p class="text-center">&copy; <?= date('Y') ?> Manga API - Panel Admin</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 