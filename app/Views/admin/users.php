<?php
$pageTitle = "Gestion des Utilisateurs";
require_once APP_PATH . '/Views/partials/header.php';
?>

<div class="container admin-users">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="my-4">Gestion des Utilisateurs</h1>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="index.php?action=admin" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour au tableau de bord
                </a>
                <form class="d-flex" action="index.php" method="GET">
                    <input type="hidden" name="action" value="admin">
                    <input type="hidden" name="subaction" value="users">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Rechercher un utilisateur..." 
                               name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-users"></i> Liste des utilisateurs</h5>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucun utilisateur trouvé.
                </div>
            <?php else: ?>
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
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'assets/img/default-profile.png'): ?>
                                        <img src="<?= $user['profile_picture'] ?>" alt="Photo de profil" class="admin-user-pic"
                                             onerror="this.onerror=null; this.src='assets/img/default-profile.png';">
                                    <?php else: ?>
                                        <img src="assets/img/default-profile.png" alt="Photo par défaut" class="admin-user-pic">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge <?= $user['role'] === 'superadmin' ? 'bg-danger' : ($user['role'] === 'admin' ? 'bg-warning' : 'bg-info') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'N/A' ?></td>
                                <td class="text-nowrap">
                                    <div class="btn-group">
                                        <a href="index.php?action=admin&subaction=editUser&id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?action=view&user_id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-info" title="Voir le profil" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($user['role'] !== 'superadmin'): ?>
                                            <a href="index.php?action=admin&subaction=deleteUser&id=<?= $user['id'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')"
                                               title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <span>Total : <?= count($users) ?> utilisateur(s)</span>
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <a href="index.php?action=admin&subaction=users" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i> Réinitialiser la recherche
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.admin-user-pic {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
    background-color: #1a1a2e;
    border: 2px solid #323232;
    box-shadow: 0 0 5px rgba(110, 84, 255, 0.3);
}

.admin-users .btn-group .btn {
    margin-right: 2px;
}

/* Fix hover colors - target each cell specifically */
.table-hover > tbody > tr:hover td {
    background-color: #272b30 !important; /* Dark background */
    color: #f8f9fa !important; /* Very light gray, almost white */
}

/* Ensure link colors stay visible on hover */
.table-hover > tbody > tr:hover a:not(.btn) {
    color: #7b2cbf !important; /* Purple for links */
}

/* Keep badge styles intact */
.table-hover > tbody > tr:hover .badge {
    opacity: 1 !important;
}
</style>

<?php
require_once APP_PATH . '/Views/partials/footer.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des erreurs de chargement d'images de profil
        const profileImages = document.querySelectorAll('.admin-user-pic');
        profileImages.forEach(img => {
            img.addEventListener('error', function() {
                console.log('Erreur de chargement de l\'image de profil:', this.src);
                this.src = 'assets/img/default-profile.png';
            });
        });
    });
</script> 