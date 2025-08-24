<?php
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit;
}

// Déterminer l'ID de l'utilisateur en fonction du format de session
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
        (isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null);

if (!$userId) {
    $_SESSION['error'] = "Erreur: Impossible d'identifier l'utilisateur";
    header('Location: index.php');
    exit;
}

// Récupérer les données de l'utilisateur, soit depuis la session, soit depuis userInfo
$username = '';
$email = '';
$profilePicture = '';

if (isset($_SESSION['user'])) {
    $username = $_SESSION['user']['username'] ?? '';
    $email = $_SESSION['user']['email'] ?? '';
    $profilePicture = $_SESSION['user']['profile_picture'] ?? '';
} elseif (isset($userInfo)) {
    $username = $userInfo['username'] ?? '';
    $email = $userInfo['email'] ?? '';
    $profilePicture = $userInfo['profile_picture'] ?? '';
}

// Définir le titre de la page
$pageTitle = "Modification du profil";

// Afficher les erreurs s'il y en a
$errorMessage = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$successMessage = isset($_SESSION['success']) ? $_SESSION['success'] : '';

// Vider les messages flash après les avoir récupérés
if (isset($_SESSION['error'])) unset($_SESSION['error']);
if (isset($_SESSION['success'])) unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --neon-blue: #05d9e8;
            --neon-purple: #9d4edd;
            --neon-pink: #ff2a6d;
            --neon-green: #39ff14;
            --neon-red: #ff2a6d;
            --darker-bg: #0f0f1a;
            --dark-bg: #1a1a2e;
            --text-color: #e4e4e4;
            --text-glow: 0 0 5px rgba(5, 217, 232, 0.7);
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Rajdhani', sans-serif;
            margin: 0;
            padding: 0;
        }

        .edit-profile-container {
            max-width: 600px;
            margin: 80px auto 40px;
            padding: 40px;
            background-color: var(--darker-bg);
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(110, 84, 255, 0.3);
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }

        .edit-profile-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--neon-purple), var(--neon-blue));
            box-shadow: 0 0 10px var(--neon-blue);
            z-index: 1;
        }

        .edit-profile-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .edit-profile-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            color: var(--neon-blue);
            text-shadow: 0 0 5px var(--neon-blue);
            margin: 0 0 0.5rem 0;
        }

        .edit-profile-header p {
            color: var(--text-color);
            opacity: 0.8;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            animation: fadeIn 0.5s ease-out;
        }

        .alert-success {
            background-color: rgba(0, 128, 0, 0.2);
            border: 1px solid var(--neon-green);
            color: var(--neon-green);
        }

        .alert-error, .alert-danger {
            background-color: rgba(255, 0, 0, 0.2);
            border: 1px solid var(--neon-red);
            color: var(--neon-red);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            color: var(--neon-purple);
            border-bottom: 1px solid var(--neon-purple);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 0 0 5px rgba(157, 78, 221, 0.5);
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            color: var(--neon-purple);
            font-size: 17px;
            font-weight: 500;
        }

        .form-help {
            margin-top: -10px;
            margin-bottom: 20px;
            font-size: 14px;
            color: rgba(228, 228, 228, 0.7);
        }

        .form-control {
            width: 100%;
            padding: 14px 14px 14px 40px;
            background-color: rgba(157, 78, 221, 0.15);
            border: 2px solid var(--neon-purple);
            border-radius: 6px;
            color: var(--text-color);
            transition: all 0.3s ease;
            font-family: 'Rajdhani', sans-serif;
            font-size: 17px;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--neon-blue);
            box-shadow: 0 0 15px var(--neon-blue);
            background-color: rgba(0, 243, 255, 0.1);
        }

        .form-icon {
            position: absolute;
            left: 14px;
            top: 43px;
            color: var(--neon-purple);
            font-size: 18px;
        }

        .profile-picture-container {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--neon-purple);
            box-shadow: 0 0 10px var(--neon-blue);
        }

        .default-profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--darker-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--neon-purple);
            border: 2px solid var(--neon-purple);
            box-shadow: 0 0 10px var(--neon-blue);
        }

        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .upload-btn {
            background-color: transparent;
            color: var(--neon-purple);
            padding: 12px 20px;
            border: 1px solid var(--neon-purple);
            border-radius: 5px;
            font-family: 'Orbitron', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 5px rgba(157, 78, 221, 0.5);
            font-size: 14px;
            text-transform: uppercase;
        }

        .upload-btn:hover {
            background-color: var(--neon-purple);
            color: #fff;
            box-shadow: 0 0 10px var(--neon-purple);
        }

        .upload-btn-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            gap: 15px;
        }

        .btn {
            padding: 14px;
            font-family: 'Orbitron', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            flex: 1;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: var(--neon-blue);
            color: var(--darker-bg);
            box-shadow: 0 0 10px rgba(5, 217, 232, 0.5);
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: var(--neon-purple);
            box-shadow: 0 0 15px var(--neon-purple);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--text-color);
            border: 1px solid var(--text-color);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .current-profile-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .current-profile-section p {
            margin-top: 0.5rem;
            opacity: 0.8;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <header>
        <?php require_once APP_PATH . '/Views/includes/header.php'; ?>
    </header>

    <div class="edit-profile-container">
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <div class="edit-profile-header">
            <h1>Modifier votre profil</h1>
            <p>Mettez à jour vos informations personnelles</p>
        </div>
        
        <form action="index.php?action=update-profile" method="POST" enctype="multipart/form-data">
            <div class="form-section">
                <h2 class="form-section-title">Informations de base</h2>
                
                <div class="profile-picture-container">
                    <?php if (isset($profilePicture) && !empty($profilePicture) && $profilePicture !== 'assets/img/default-profile.png'): ?>
                        <img src="<?= htmlspecialchars($profilePicture) ?>" alt="Photo de profil" class="profile-picture">
                    <?php else: ?>
                        <div class="default-profile-picture">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="upload-btn-wrapper">
                        <button class="upload-btn" type="button">Changer d'image</button>
                        <input type="file" name="profile_picture" accept="image/jpeg, image/png, image/gif, image/webp">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <i class="fas fa-user form-icon"></i>
                    <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Adresse e-mail</label>
                    <i class="fas fa-envelope form-icon"></i>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required autocomplete="off">
                </div>
            </div>
            
            <div class="form-section">
                <h2 class="form-section-title">Changer le mot de passe</h2>
                <p class="form-help">Laissez ces champs vides si vous ne souhaitez pas changer votre mot de passe.</p>
                
                <div class="form-group">
                    <label for="current_password" class="form-label">Mot de passe actuel</label>
                    <i class="fas fa-lock form-icon"></i>
                    <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password">
                </div>
                
                <div class="form-group">
                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                    <i class="fas fa-key form-icon"></i>
                    <input type="password" id="new_password" name="new_password" class="form-control" autocomplete="new-password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                    <i class="fas fa-check-circle form-icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password">
                </div>
            </div>
            
            <div class="form-actions">
                <a href="index.php?action=profile" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Sauvegarder les modifications</button>
            </div>
        </form>
    </div>

    <script>
        // Afficher le nom du fichier sélectionné
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.querySelector('input[type=file]');
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    const fileName = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné';
                    // Crée ou met à jour un élément pour afficher le nom du fichier
                    let fileNameDisplay = document.getElementById('selected-file-name');
                    if (!fileNameDisplay) {
                        fileNameDisplay = document.createElement('span');
                        fileNameDisplay.id = 'selected-file-name';
                        fileNameDisplay.style.marginLeft = '10px';
                        this.parentNode.appendChild(fileNameDisplay);
                    }
                    fileNameDisplay.textContent = fileName;
                });
            }
        });
    </script>

    <?php if (file_exists(APP_PATH . '/Views/includes/footer.php')): ?>
        <?php require_once APP_PATH . '/Views/includes/footer.php'; ?>
    <?php endif; ?>
</body>
</html> 