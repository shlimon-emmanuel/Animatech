# Configuration d'Animatech

## Configuration des variables d'environnement

### 1. Fichier .env

Ce projet utilise des variables d'environnement pour sécuriser les informations sensibles. 

**⚠️ IMPORTANT : Ne jamais commiter le fichier `.env` dans Git !**

### 2. Configuration de l'API TMDB

Pour utiliser l'application, vous devez :

1. **Créer un compte sur TMDB** : https://www.themoviedb.org/
2. **Obtenir une clé API** :
   - Aller dans Paramètres → API
   - Demander une clé API
   - Choisir "Developer" si c'est pour un usage personnel
3. **Configurer votre `.env`** :
   ```
   TMDB_API_KEY=votre_vraie_cle_api_ici
   ```

### 3. Structure du fichier .env

```env
# Configuration de l'environnement
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Configuration de la base de données
DB_HOST=localhost
DB_NAME=userauth
DB_USERNAME=root
DB_PASSWORD=

# Configuration de l'API TMDB (OBLIGATOIRE)
TMDB_API_KEY=votre_vraie_cle_api_ici
TMDB_API_URL=https://api.themoviedb.org/3/

# Configuration du cache
CACHE_DRIVER=file
CACHE_PREFIX=animatech_
CACHE_LIFETIME=3600

# Configuration des sessions
SESSION_LIFETIME=120
SESSION_SECURE=false
SESSION_HTTPONLY=true
SESSION_SAMESITE=Lax
```

### 4. Sécurité

✅ **Bonnes pratiques :**
- Le fichier `.env` est dans `.gitignore`
- Les clés API sont chargées depuis les variables d'environnement
- Pas de secrets codés en dur dans le code

❌ **À éviter :**
- Partager le fichier `.env`
- Commiter des clés API dans Git
- Mettre des secrets dans le code source

### 5. Environnements

- **Development** : `APP_ENV=development` + `APP_DEBUG=true`
- **Production** : `APP_ENV=production` + `APP_DEBUG=false`

### 6. Dépannage

Si l'application ne fonctionne pas :

1. **Vérifier que le fichier `.env` existe**
2. **Vérifier que `TMDB_API_KEY` est configurée**
3. **Vérifier les logs** dans `/logs/`
4. **Tester la clé API** sur https://api.themoviedb.org/

### 7. Installation rapide

```bash
# 1. Copier le fichier d'exemple
cp .env.example .env

# 2. Éditer le fichier .env
# Remplacer TMDB_API_KEY=your_api_key_here par votre vraie clé

# 3. Installer les dépendances
composer install

# 4. Lancer l'application
php -S localhost:8000
``` 