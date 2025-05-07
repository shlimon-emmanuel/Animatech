<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Cinetech</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎬</text></svg>" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Correctifs spécifiques au formulaire d'inscription */
        .auth-container {
            max-width: 480px; /* Augmenté pour plus de largeur */
            box-sizing: border-box;
            padding: 40px; /* Plus d'espace à l'intérieur */
        }
        
        /* Assurer que TOUS les champs ont le fond violet */
        .auth-form input[type="text"],
        .auth-form input[type="email"],
        .auth-form input[type="password"] {
            width: 100%;
            box-sizing: border-box;
            padding-left: 40px; /* Plus d'espace pour l'icône */
            background-color: rgba(157, 78, 221, 0.15); /* Fond violet transparent */
            border: 2px solid var(--neon-purple);
            padding-top: 14px; /* Augmenter la hauteur */
            padding-bottom: 14px;
            font-size: 17px; /* Texte légèrement plus grand */
            color: white;
            font-family: 'Rajdhani', sans-serif;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px; /* Plus d'espace entre les champs */
        }
        
        .form-icon {
            position: absolute;
            left: 14px;
            top: 42px; /* Position après le label */
            color: var(--neon-purple);
            font-size: 18px; /* Icône légèrement plus grande */
        }
        
        /* Animation cohérente pour le focus */
        .auth-form input:focus {
            border-color: var(--neon-blue);
            box-shadow: 0 0 15px var(--neon-blue);
            background-color: rgba(0, 243, 255, 0.1); /* Fond bleu en focus */
            outline: none;
        }
        
        /* Corriger le fond blanc de l'autocomplétion */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px rgba(157, 78, 221, 0.15) inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
            background-clip: content-box !important;
        }
        
        /* Firefox autocomplétion fix */
        input:autofill {
            background-color: rgba(157, 78, 221, 0.15) !important;
            color: white !important;
        }
        
        /* Styles pour le message de force du mot de passe */
        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
            color: var(--neon-purple);
            transition: all 0.3s;
            padding-left: 5px;
        }
        
        .password-strength.weak {
            color: #ff5050;
        }
        
        .password-strength.medium {
            color: #ffaa00;
        }
        
        .password-strength.strong {
            color: #32ff32;
        }
        
        /* Style du bouton */
        .neon-button {
            margin-top: 30px; /* Plus d'espace avant le bouton */
            padding: 14px; /* Bouton plus grand */
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 2px;
            width: 100%;
        }
        
        /* Labels plus stylés */
        .auth-form label {
            color: var(--neon-purple);
            font-size: 17px;
            margin-bottom: 10px;
            display: block;
            font-weight: 500;
        }
        
        /* Améliorer le titre */
        .auth-title {
            font-size: 35px;
            margin-bottom: 35px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Inscription</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=register" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <i class="fas fa-user form-icon"></i>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <i class="fas fa-envelope form-icon"></i>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <i class="fas fa-lock form-icon"></i>
                <input type="password" id="password" name="password" minlength="6" required>
                <div id="password-strength" class="password-strength">Le mot de passe doit contenir au moins 6 caractères</div>
            </div>

            <button type="submit" class="neon-button">S'inscrire</button>
        </form>

        <div class="auth-links">
            <p>Déjà un compte ? <a href="index.php?action=login" class="neon-link">Se connecter</a></p>
            <a href="index.php" class="neon-link">Retour à l'accueil</a>
        </div>
    </div>
    
    <script>
        // Script pour indiquer la force du mot de passe
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const strengthIndicator = document.getElementById('password-strength');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 'faible';
                let strengthClass = 'weak';
                let message = 'Mot de passe faible';
                
                if (password.length < 6) {
                    message = 'Le mot de passe doit contenir au moins 6 caractères';
                    strengthClass = 'weak';
                } else if (password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                    strength = 'fort';
                    message = 'Mot de passe fort';
                    strengthClass = 'strong';
                } else if (password.length >= 6) {
                    strength = 'moyen';
                    message = 'Mot de passe moyen';
                    strengthClass = 'medium';
                }
                
                strengthIndicator.textContent = message;
                strengthIndicator.className = 'password-strength ' + strengthClass;
            });
        });
    </script>
</body>
</html>