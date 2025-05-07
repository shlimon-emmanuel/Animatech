<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Cinetech</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎬</text></svg>" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&family=Rajdhani:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Correctifs spécifiques au formulaire de connexion */
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
        
        /* Espace pour les liens */
        .auth-links {
            margin-top: 30px;
        }
        
        .auth-links p {
            margin-bottom: 15px;
        }
        
        /* Debug info */
        .debug-info {
            margin-top: 30px;
            padding: 15px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 6px;
            font-size: 14px;
        }
        
        .debug-info h3 {
            margin-top: 0;
            color: #00ccff;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Connexion</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?action=login" method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <i class="fas fa-envelope form-icon"></i>
                <input type="email" name="email" id="email" required value="admin@example.com">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <i class="fas fa-lock form-icon"></i>
                <input type="password" name="password" id="password" required value="superadmin123">
            </div>
            
            <button type="submit" class="neon-button">Se connecter</button>
        </form>

        <div class="auth-links">
            <p>Pas encore de compte ? <a href="index.php?action=register" class="neon-link">S'inscrire</a></p>
            <a href="index.php" class="neon-link">Retour à l'accueil</a>
        </div>
        
        <!-- Information de debug -->
        <div class="debug-info">
            <h3>Information de connexion superadmin</h3>
            <p><strong>Email:</strong> admin@example.com</p>
            <p><strong>Mot de passe:</strong> superadmin123</p>
        </div>
    </div>
</body>
</html>