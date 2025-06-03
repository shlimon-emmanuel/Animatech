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
                    <a href="<?= BASE_URL ?>/?action=admin&subaction=users" class="btn btn-primary mt-3">
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
                    <a href="<?= BASE_URL ?>/?action=admin&subaction=users" class="btn btn-primary mt-3">
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
                    <a href="<?= BASE_URL ?>/?action=profile" class="btn btn-primary mt-3">
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
                        <table class="table table-striped table-hover">
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
                                            <img src="<?= BASE_URL ?>/assets/img/default-profile.png" alt="Photo par défaut" class="admin-user-pic">
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
                                        <a href="<?= BASE_URL ?>/?action=admin&subaction=editUser&id=<?= $user['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (($user['role'] ?? 'user') !== 'superadmin'): ?>
                                            <a href="<?= BASE_URL ?>/?action=admin&subaction=deleteUser&id=<?= $user['id'] ?>" 
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
                        <a href="<?= BASE_URL ?>/?action=admin&subaction=users" class="btn btn-primary">
                            <i class="fas fa-list"></i> Voir tous les utilisateurs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 