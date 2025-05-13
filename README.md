# 🎬 Animatech - Plateforme de Films d'Animation

## 📝 Description
Animatech est une application web permettant de découvrir et suivre les films d'animation du monde entier. Elle utilise l'API TMDB pour fournir une base de données riche en contenu d'animation.

## 🚀 Fonctionnalités
- Recherche de films d'animation
- Chargement infini des résultats
- Système d'authentification
- Gestion des favoris
- Commentaires et notes
- Visualisation des bandes-annonces
- Interface responsive et moderne
- Panel d'administration sécurisé
- Cache Redis (NoSQL) pour optimiser les performances

## 📋 Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Redis (recommandé, mais optionnel pour le cache NoSQL)
- Composer
- Serveur web (Apache/Nginx)
- Clé API TMDB

## ⚙️ Installation

### 1. Cloner le projet
```bash
git clone https://github.com/shlimon-emmanuel/Animatech.git
cd Animatech
```

### 2. Configuration
```bash
# Copier le fichier d'exemple de configuration
cp .env-example .env

# Éditer le fichier .env avec vos propres paramètres
nano .env
```

### 3. Base de données
- Créez une base de données MySQL pour l'application
- Les tables seront créées automatiquement au premier lancement

### 4. Redis (NoSQL)
- Installez Redis sur votre serveur (optionnel mais recommandé)
- Par défaut, l'application cherchera Redis sur 127.0.0.1:6379
- Si Redis n'est pas disponible, l'application fonctionnera quand même, mais sans le bénéfice du cache

### 5. Permissions
```bash
# Donner les droits d'écriture aux dossiers d'upload et de logs
chmod 755 assets/uploads/profiles
chmod 755 logs
```

### 6. Accès
- Ouvrez votre navigateur et accédez à l'application
- Créez un compte utilisateur (le premier utilisateur peut être promu administrateur)

## 🔒 Sécurité

L'application implémente plusieurs mesures de sécurité :

- Protection CSRF avec tokens uniques par formulaire
- Validation rigoureuse des entrées utilisateur
- Hachage sécurisé des mots de passe
- En-têtes de sécurité HTTP (CSP, X-XSS-Protection, etc.)
- Contrôle des types de fichiers uploadés
- Protection contre les injections SQL via PDO
- Séparation des configurations dev/prod

## 🗄️ Architecture hybride SQL/NoSQL

Animatech utilise une architecture hybride de base de données :

- **MySQL (SQL)** : Stockage principal pour les données structurées (utilisateurs, favoris, commentaires)
- **Redis (NoSQL)** : Cache performant pour les résultats de l'API TMDB

Cette approche offre le meilleur des deux mondes : la fiabilité et l'intégrité de SQL pour les données critiques, et la rapidité de NoSQL pour améliorer les performances.

## 🌐 Configuration pour la production

Pour un déploiement en production, veillez à :

1. Définir `APP_ENV=production` dans votre fichier `.env`
2. Utiliser des identifiants de base de données sécurisés
3. Configurer un certificat SSL (HTTPS)
4. Utiliser des variables d'environnement pour les informations sensibles
5. Vérifier que les logs d'erreurs sont correctement configurés

## 🤝 Crédits
- API de films : [The Movie Database (TMDB)](https://www.themoviedb.org/)
- Icônes : [Font Awesome](https://fontawesome.com/)
- Polices : [Google Fonts](https://fonts.google.com/) (Orbitron, Rajdhani)