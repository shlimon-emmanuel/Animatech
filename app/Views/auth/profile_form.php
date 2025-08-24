<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Style cohérent avec les formulaires de connexion/inscription */
        .profile-container {
            max-width: 480px;
            margin: 60px auto;
            padding: 40px;
            background-color: var(--darker-bg);
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 243, 255, 0.3);
            box-sizing: border-box;
        }
        
        .auth-title {
            font-family: 'Orbitron', sans-serif;
            text-align: center;
            color: var(--neon-blue);
            text-shadow: var(--text-glow);
            margin-bottom: 35px;
            font-size: 35px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .auth-form input[type="text"], 
        .auth-form input[type="email"], 
        .auth-form input[type="password"] {
            width: 100%;
            box-sizing: border-box;
            padding: 14px 14px 14px 40px;
            background-color: rgba(157, 78, 221, 0.15);
            border: 2px solid var(--neon-purple);
            border-radius: 6px;
            color: white;
            font-family: 'Rajdhani', sans-serif;
            font-size: 17px;
            transition: all 0.3s ease;
        }
        
        .auth-form input:focus {
            border-color: var(--neon-blue);
            box-shadow: 0 0 15px var(--neon-blue);
            background-color: rgba(0, 243, 255, 0.1);
        }
        
        .auth-form label {
            display: block;
            margin-bottom: 10px;
            color: var(--neon-purple);
            font-size: 17px;
            font-weight: 500;
        }
        
        .neon-button {
            margin-top: 30px;
            padding: 14px;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 2px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .form-icon {
            position: absolute;
            left: 14px;
            top: 42px; /* Position après le label */
            color: var(--neon-purple);
            font-size: 18px;
        }
        
        /* Style spécifique pour l'input de type file */
        .file-input {
            width: 100%;
            padding: 14px;
            background-color: rgba(157, 78, 221, 0.15);
            border: 2px solid var(--neon-purple);
            border-radius: 6px;
            color: white;
            font-family: 'Rajdhani', sans-serif;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .file-input:focus, 
        .file-input:hover {
            border-color: var(--neon-blue);
            box-shadow: 0 0 15px var(--neon-blue);
            background-color: rgba(0, 243, 255, 0.1);
        }
        
        .file-input-info {
            margin-top: 8px;
            font-size: 0.85rem;
            color: var(--neon-purple);
            padding-left: 5px;
        }
        
        /* Style pour l'avatar */
        .profile-picture-container {
            text-align: center;
            padding: 10px 0 30px;
            margin-bottom: 10px;
        }
        
        .profile-picture, 
        .default-profile-icon {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 15px;
            border: 3px solid var(--neon-purple);
            box-shadow: 0 0 15px var(--neon-blue);
            transition: all 0.3s ease;
        }
        
        .profile-picture:hover, 
        .default-profile-icon:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px var(--neon-blue);
        }
        
        .home-button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <h1 class="auth-title">Mon Profil</h1>
        
        <div class="profile-picture-container">
            <?php if (!empty($_SESSION['user']['profile_picture']) && $_SESSION['user']['profile_picture'] !== 'assets/img/default-profile.png'): ?>
                <img src="<?= $_SESSION['user']['profile_picture'] ?>" 
                    alt="Photo de profil" id="profile-preview" class="profile-picture">
            <?php else: ?>
                <div class="default-profile-icon" id="profile-preview">
                    <i class="fa-solid fa-user"></i>
                </div>
            <?php endif; ?>
            <p class="profile-username"><?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?></p>
        </div>
        
        <form action="index.php?action=profile" method="POST" class="auth-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profile_picture">Photo de profil</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="file-input">
                <div class="file-input-info">Formats acceptés: JPG, PNG, GIF, WEBP</div>
            </div>
            
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <i class="fas fa-user form-icon"></i>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <i class="fas fa-envelope form-icon"></i>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" required autocomplete="off">
            </div>

            <button type="submit" class="neon-button">Mettre à jour le profil</button>
        </form>

        <div class="auth-links text-center">
            <a href="index.php" class="neon-button home-button">
                <i class="fa-solid fa-home"></i> Accueil
            </a>
        </div>
    </div>
    
    <script>
        // Prévisualisation de l'image sélectionnée
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('profile_picture');
            const preview = document.getElementById('profile-preview');
            
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        // Remplacer l'icône par défaut ou l'image actuelle par la nouvelle
                        if (preview.tagName === 'IMG') {
                            preview.src = e.target.result;
                        } else {
                            // Créer une nouvelle image et remplacer l'icône par défaut
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.id = 'profile-preview';
                            img.className = 'profile-picture';
                            img.alt = 'Aperçu de la photo de profil';
                            
                            preview.parentNode.replaceChild(img, preview);
                        }
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    </script>
</body>
</html> 