<?php
$pageTitle = "Modifier l'utilisateur";
require_once APP_PATH . '/Views/partials/header.php';
?>

<div class="container admin-edit-user">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="my-4">Modifier l'utilisateur</h1>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="index.php?action=admin&subaction=users" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Profil actuel</h5>
                </div>
                <div class="card-body text-center">
                    <div class="user-avatar-container mb-3">
                        <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                            <img src="<?= $user['profile_picture'] ?>" alt="Photo de profil" class="img-fluid user-avatar">
                        <?php else: ?>
                            <img src="assets/img/default-profile.png" alt="Photo par défaut" class="img-fluid user-avatar">
                        <?php endif; ?>
                    </div>
                    <h5><?= htmlspecialchars($user['username']) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                    <p>
                        <span class="badge <?= $user['role'] === 'superadmin' ? 'bg-danger' : ($user['role'] === 'admin' ? 'bg-warning' : 'bg-info') ?>">
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </p>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="index.php?action=view&user_id=<?= $user['id'] ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye"></i> Voir le profil
                        </a>
                        <?php if ($user['role'] !== 'superadmin'): ?>
                            <a href="index.php?action=admin&subaction=deleteUser&id=<?= $user['id'] ?>" 
                               class="btn btn-outline-danger btn-sm" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Modifier les informations</h5>
                </div>
                <div class="card-body">
                    <form action="index.php?action=admin&subaction=updateUser" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <select class="form-select" id="role" name="role">
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="superadmin" <?= $user['role'] === 'superadmin' ? 'selected' : '' ?>>SuperAdmin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                            <div class="form-text">Laissez vide pour conserver l'image actuelle.</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="index.php?action=admin&subaction=users" class="btn btn-outline-secondary me-md-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-avatar {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 50%;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.user-avatar-container {
    position: relative;
    display: inline-block;
}
</style>

<?php
require_once APP_PATH . '/Views/partials/footer.php';
?> 