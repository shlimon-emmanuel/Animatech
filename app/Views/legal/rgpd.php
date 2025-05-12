<?php
// Définir le titre de la page
$pageTitle = "RGPD - ANIMATECH";

// Inclure le header
require_once APP_PATH . '/Views/includes/header.php';
?>

<div class="legal-container">
    <h1>Politique de Confidentialité et RGPD</h1>
    
    <div class="legal-section">
        <h2>1. Introduction</h2>
        <p>Chez ANIMATECH, nous sommes engagés à protéger votre vie privée. Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos données personnelles conformément au Règlement Général sur la Protection des Données (RGPD).</p>
        <p>Cette politique s'applique à tous les utilisateurs du site ANIMATECH et est effective à compter du <?= date('d/m/Y') ?>.</p>
    </div>
    
    <div class="legal-section">
        <h2>2. Responsable du traitement</h2>
        <p>Le responsable du traitement des données personnelles est :</p>
        <p>ANIMATECH<br>
        [Adresse]<br>
        [Code postal] [Ville]<br>
        Email : contact@animatech.fr</p>
    </div>
    
    <div class="legal-section">
        <h2>3. Données personnelles collectées</h2>
        <p>Nous collectons les types de données personnelles suivants :</p>
        <ul>
            <li><strong>Données d'identification</strong> : nom d'utilisateur, adresse email</li>
            <li><strong>Données d'authentification</strong> : mot de passe (crypté)</li>
            <li><strong>Données de profil</strong> : photo de profil, préférences</li>
            <li><strong>Données d'activité</strong> : films favoris, commentaires, évaluations</li>
            <li><strong>Données techniques</strong> : adresse IP, données de navigation, cookies</li>
        </ul>
    </div>
    
    <div class="legal-section">
        <h2>4. Finalités et base légale du traitement</h2>
        <p>Nous traitons vos données personnelles pour les finalités suivantes :</p>
        <table class="rgpd-table">
            <tr>
                <th>Finalité</th>
                <th>Base légale</th>
            </tr>
            <tr>
                <td>Création et gestion de votre compte utilisateur</td>
                <td>Exécution du contrat</td>
            </tr>
            <tr>
                <td>Personnalisation de votre expérience</td>
                <td>Votre consentement</td>
            </tr>
            <tr>
                <td>Gestion des favoris et commentaires</td>
                <td>Exécution du contrat</td>
            </tr>
            <tr>
                <td>Amélioration de nos services</td>
                <td>Intérêt légitime</td>
            </tr>
            <tr>
                <td>Communication avec vous</td>
                <td>Votre consentement / Exécution du contrat</td>
            </tr>
        </table>
    </div>
    
    <div class="legal-section">
        <h2>5. Durée de conservation des données</h2>
        <p>Nous conservons vos données personnelles selon les principes suivants :</p>
        <ul>
            <li>Données du compte : pendant toute la durée de votre inscription (jusqu'à suppression du compte)</li>
            <li>Activités sur le site : 3 ans après votre dernière activité</li>
            <li>Données de connexion : 1 an</li>
        </ul>
        <p>Vous pouvez demander la suppression de vos données à tout moment en nous contactant.</p>
    </div>
    
    <div class="legal-section">
        <h2>6. Destinataires des données</h2>
        <p>Vos données personnelles sont accessibles :</p>
        <ul>
            <li>À notre équipe interne en charge de la gestion du site</li>
            <li>À nos sous-traitants techniques (hébergeur, fournisseurs de services)</li>
        </ul>
        <p>Nous ne vendons jamais vos données personnelles à des tiers.</p>
    </div>
    
    <div class="legal-section">
        <h2>7. Transfert de données hors UE</h2>
        <p>Nous pouvons être amenés à transférer certaines de vos données vers des serveurs situés hors de l'Union Européenne (notamment pour l'hébergement). Dans ce cas, nous nous assurons que ces transferts respectent les exigences du RGPD.</p>
    </div>
    
    <div class="legal-section">
        <h2>8. Vos droits</h2>
        <p>Conformément au RGPD, vous disposez des droits suivants concernant vos données personnelles :</p>
        <ul>
            <li><strong>Droit d'accès</strong> : obtenir une copie de vos données</li>
            <li><strong>Droit de rectification</strong> : corriger des informations inexactes</li>
            <li><strong>Droit à l'effacement</strong> ("droit à l'oubli") : faire supprimer vos données</li>
            <li><strong>Droit à la limitation du traitement</strong> : restreindre l'utilisation de vos données</li>
            <li><strong>Droit à la portabilité</strong> : recevoir vos données dans un format structuré</li>
            <li><strong>Droit d'opposition</strong> : vous opposer au traitement de vos données</li>
            <li><strong>Droit de retirer votre consentement</strong> à tout moment</li>
        </ul>
        <p>Pour exercer ces droits, contactez-nous à <a href="mailto:rgpd@animatech.fr">rgpd@animatech.fr</a>.</p>
    </div>
    
    <div class="legal-section">
        <h2>9. Sécurité des données</h2>
        <p>Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données personnelles, notamment :</p>
        <ul>
            <li>Chiffrement des mots de passe</li>
            <li>Protocole HTTPS pour la transmission sécurisée</li>
            <li>Accès limité aux données personnelles</li>
            <li>Formation de notre personnel aux exigences de la protection des données</li>
        </ul>
    </div>
    
    <div class="legal-section">
        <h2>10. Cookies</h2>
        <p>Notre site utilise des cookies pour améliorer votre expérience de navigation. Vous pouvez configurer votre navigateur pour refuser les cookies ou être alerté lorsque des cookies sont envoyés.</p>
        <p>Types de cookies utilisés :</p>
        <ul>
            <li>Cookies essentiels au fonctionnement du site</li>
            <li>Cookies de préférences</li>
            <li>Cookies d'analyse et de performance</li>
        </ul>
    </div>
    
    <div class="legal-section">
        <h2>11. Modifications de la politique de confidentialité</h2>
        <p>Nous nous réservons le droit de modifier cette politique de confidentialité à tout moment. Les modifications seront publiées sur cette page avec la date de mise à jour.</p>
    </div>
    
    <div class="legal-section">
        <h2>12. Contact et réclamations</h2>
        <p>Pour toute question concernant cette politique ou pour exercer vos droits, vous pouvez nous contacter à <a href="mailto:rgpd@animatech.fr">rgpd@animatech.fr</a>.</p>
        <p>Vous avez également le droit d'introduire une réclamation auprès de la CNIL (Commission Nationale de l'Informatique et des Libertés) si vous estimez que le traitement de vos données n'est pas conforme à la réglementation.</p>
    </div>
    
    <p><a href="index.php" class="neon-link">Retour à l'accueil</a></p>
</div>

<style>
    .legal-container {
        max-width: 800px;
        margin: 80px auto 50px;
        padding: 30px;
        background-color: rgba(15, 15, 25, 0.7);
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(157, 78, 221, 0.3);
    }
    
    .legal-section {
        margin-bottom: 40px;
    }
    
    h1 {
        font-family: 'Orbitron', sans-serif;
        color: var(--neon-purple);
        margin-bottom: 30px;
        text-transform: uppercase;
        text-shadow: 0 0 10px rgba(157, 78, 221, 0.5);
    }
    
    h2 {
        color: var(--neon-blue);
        margin-top: 20px;
        margin-bottom: 15px;
        border-bottom: 1px solid var(--neon-blue);
        padding-bottom: 5px;
        text-shadow: 0 0 5px rgba(5, 217, 232, 0.5);
    }
    
    p {
        margin-bottom: 15px;
        line-height: 1.6;
    }
    
    ul {
        margin-bottom: 15px;
        padding-left: 20px;
    }
    
    ul li {
        margin-bottom: 8px;
        line-height: 1.5;
    }
    
    .neon-link {
        color: var(--neon-purple);
        text-decoration: none;
        font-weight: bold;
        transition: text-shadow 0.3s;
        display: inline-block;
        margin-top: 20px;
    }
    
    .neon-link:hover {
        text-shadow: 0 0 8px var(--neon-purple);
    }
    
    .rgpd-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    
    .rgpd-table th, .rgpd-table td {
        padding: 10px;
        border: 1px solid rgba(157, 78, 221, 0.3);
    }
    
    .rgpd-table th {
        background-color: rgba(157, 78, 221, 0.2);
        color: var(--neon-blue);
        text-align: left;
    }
    
    .rgpd-table tr:nth-child(even) {
        background-color: rgba(15, 15, 25, 0.4);
    }
    
    .rgpd-table tr:hover {
        background-color: rgba(5, 217, 232, 0.1);
    }
    
    @media (max-width: 768px) {
        .legal-container {
            padding: 20px;
            margin: 70px auto 30px;
        }
        
        h1 {
            font-size: 1.5rem;
        }
        
        h2 {
            font-size: 1.2rem;
        }
        
        .rgpd-table {
            font-size: 0.9rem;
        }
    }
</style>

<?php
// Inclure le footer
require_once APP_PATH . '/Views/includes/footer.php';
?> 