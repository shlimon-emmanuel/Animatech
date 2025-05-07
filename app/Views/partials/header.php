<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Administration Manga API</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎬</text></svg>" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
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
        
        .return-to-site {
            display: inline-block;
            padding: 0.375rem 0.75rem !important;
            margin-right: 10px;
            color: #fff !important;
            background-color: transparent;
            border: 1px solid #fff;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .return-to-site:hover {
            background-color: #fff;
            color: #121212 !important;
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
                    <a class="nav-link" href="index.php?action=admin">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?action=admin&subaction=users">Utilisateurs</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-light btn-sm me-2 return-to-site" href="index.php">
                        <i class="fas fa-arrow-left"></i> Retour au site
                    </a>
                </li>
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

<main class="container mt-3">
</main>
</body>
</html> 