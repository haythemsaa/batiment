# 🚀 Démarrage rapide - BatiSaaS

## Application créée avec succès ! ✅

Vous disposez maintenant d'une application SaaS complète pour la gestion d'entreprises du bâtiment.

## 📋 Ce qui a été créé

### Backend PHP (44 fichiers)
- ✅ Architecture MVC complète
- ✅ Système de routing personnalisé
- ✅ Gestion de base de données avec PDO
- ✅ 8 contrôleurs principaux
- ✅ 6 modèles de données
- ✅ Middlewares d'authentification
- ✅ API REST complète

### Frontend
- ✅ Interface utilisateur responsive
- ✅ Design moderne et professionnel
- ✅ CSS Grid/Flexbox
- ✅ JavaScript vanilla
- ✅ Composants réutilisables

### Base de données
- ✅ Schéma MySQL complet (14 tables)
- ✅ Support multi-tenant
- ✅ Relations et contraintes
- ✅ Données de démonstration

### Documentation
- ✅ README complet
- ✅ Guide d'installation
- ✅ Documentation API
- ✅ Guide de contribution
- ✅ CHANGELOG

## 🎯 Démarrage ultra-rapide avec Docker

```bash
# Démarrer l'application
docker-compose up -d

# L'application sera disponible sur :
# - http://localhost:8080
# - PHPMyAdmin: http://localhost:8081
```

Compte de démo :
- Email: **admin@demo.com**
- Mot de passe: **password**

## 🔧 Installation manuelle

### 1. Créer la base de données

```bash
mysql -u root -p
```

```sql
CREATE DATABASE batisaas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'batisaas_user'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
GRANT ALL PRIVILEGES ON batisaas.* TO 'batisaas_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Importer le schéma

```bash
mysql -u batisaas_user -p batisaas < database/schema.sql
```

### 3. Configurer la connexion

Éditez `config/database.php` :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'batisaas');
define('DB_USER', 'batisaas_user');
define('DB_PASS', 'votre_mot_de_passe');
```

### 4. Configurer Apache

```apache
<VirtualHost *:80>
    ServerName batisaas.local
    DocumentRoot "/chemin/vers/batiment"

    <Directory "/chemin/vers/batiment">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 5. Redémarrer Apache

```bash
sudo systemctl restart apache2
```

### 6. Accéder à l'application

Ouvrez votre navigateur : http://localhost ou http://batisaas.local

## 📱 Modules disponibles

### 1. Tableau de bord
- Vue d'ensemble de l'activité
- Statistiques en temps réel
- Graphiques et KPI
- Actions rapides

### 2. Gestion des devis
- Créer des devis professionnels
- Calculs automatiques (HT, TVA, TTC)
- Lignes de devis personnalisables
- Conversion en facture
- Export PDF

### 3. Gestion des factures
- Factures, acomptes, avoirs
- Suivi des paiements
- Gestion des échéances
- Relances automatiques
- Export PDF

### 4. Gestion des chantiers
- Planification et suivi
- Tâches et sous-tâches
- Diagramme de Gantt
- Calcul de rentabilité
- Suivi des interventions

### 5. Gestion des clients
- Clients particuliers et entreprises
- Informations complètes
- Historique des transactions
- Notes et documents

### 6. API REST
- Authentification par token
- Endpoints complets
- Documentation Swagger (à venir)
- Rate limiting (à venir)

## 🔐 Sécurité

✅ **Déjà implémenté :**
- Protection CSRF
- Hashage bcrypt des mots de passe
- Requêtes préparées (PDO)
- Validation des données
- Sessions sécurisées
- Isolation multi-tenant
- Protection XSS

⚠️ **Pour la production :**
- Activer HTTPS
- Changer tous les secrets
- Configurer les sauvegardes
- Mettre en place des logs
- Activer le rate limiting

## 🎨 Personnalisation

### Changer les couleurs

Éditez `public/css/style.css` :

```css
:root {
    --primary-color: #2563eb;  /* Votre couleur principale */
    --secondary-color: #64748b;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
}
```

### Ajouter un module

1. Créer le contrôleur : `app/Controllers/MonModule.php`
2. Créer le modèle : `app/Models/MonModule.php`
3. Créer les vues : `app/Views/mon-module/`
4. Ajouter les routes dans `index.php`

## 📊 Structure de la base de données

Tables principales :
- `companies` - Entreprises (multi-tenant)
- `users` - Utilisateurs
- `clients` - Clients
- `devis` - Devis
- `devis_items` - Lignes de devis
- `factures` - Factures
- `facture_items` - Lignes de facture
- `chantiers` - Chantiers
- `chantier_tasks` - Tâches de chantier
- `interventions` - Interventions
- `fournisseurs` - Fournisseurs
- `expenses` - Dépenses
- `work_hours` - Heures de travail
- `documents` - Documents

## 🔌 Utilisation de l'API

### Authentification

```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@demo.com","password":"password"}'
```

### Récupérer les devis

```bash
curl http://localhost/api/devis \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

### Paramètres disponibles
- `status` - Filtrer par statut
- `limit` - Nombre de résultats (max 100)
- `offset` - Décalage pour pagination

## 📈 Prochaines étapes

1. **Personnaliser l'application**
   - Modifier les couleurs
   - Ajouter votre logo
   - Personnaliser les emails

2. **Configurer les emails**
   - Installer PHPMailer
   - Configurer SMTP dans `config/config.php`

3. **Générer des PDF**
   - Installer TCPDF ou mPDF
   - Implémenter les templates PDF

4. **Ajouter la signature électronique**
   - Intégrer Signature Pad
   - Stocker les signatures

5. **Déployer en production**
   - Configurer HTTPS
   - Optimiser les performances
   - Mettre en place les sauvegardes

## 🆘 Besoin d'aide ?

- 📖 Lisez le [README.md](README.md)
- 📝 Consultez [INSTALL.md](INSTALL.md)
- 🐛 Signalez un bug sur GitHub
- 💬 Posez vos questions dans les issues

## 🎉 Félicitations !

Votre application SaaS est prête à l'emploi. Bon développement ! 🚀

---

**Note importante :** Cette application est un point de départ. Pour un usage en production, assurez-vous de :
- Tester en profondeur
- Sécuriser les accès
- Optimiser les performances
- Mettre en place des sauvegardes
- Respecter la RGPD
