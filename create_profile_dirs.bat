@echo off
echo Création des répertoires pour les uploads de photos de profil...

:: Créer les répertoires s'ils n'existent pas
mkdir assets\uploads 2>nul
mkdir assets\uploads\profiles 2>nul
mkdir assets\images\profiles 2>nul

echo Répertoires créés avec succès.

:: Définir les permissions (équivalent à 0777 sous Linux)
echo Définition des permissions...
icacls "assets\uploads" /grant Everyone:(OI)(CI)F
icacls "assets\uploads\profiles" /grant Everyone:(OI)(CI)F
icacls "assets\images\profiles" /grant Everyone:(OI)(CI)F

echo Permissions définies avec succès.

:: Créer un fichier de test dans chaque répertoire
echo Création de fichiers de test...
echo Test file > assets\uploads\test.txt
echo Test file > assets\uploads\profiles\test.txt
echo Test file > assets\images\profiles\test.txt

echo Fichiers de test créés avec succès.

echo.
echo Procédure terminée. Tous les répertoires nécessaires ont été créés et configurés.
echo.
echo Vous pouvez maintenant utiliser le système de mise à jour de profil.
pause 