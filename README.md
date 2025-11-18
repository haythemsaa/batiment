# BatiSaaS - Plateforme SaaS de gestion pour entreprises du bâtiment

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## 🏗️ À propos

BatiSaaS est une application SaaS complète développée en PHP pour la gestion d'entreprises du bâtiment, inspirée de [Costructor.co](https://costructor.co/). Elle offre une solution tout-en-un pour gérer devis, factures, chantiers, clients et bien plus encore.

## ✨ Fonctionnalités principales

### 📋 Gestion commerciale
- ✅ Création de devis personnalisables
- ✅ Conversion automatique devis → factures
- ✅ Génération de factures (facture, acompte, avoir)
- ✅ Signature électronique
- ✅ Export PDF

### 🔨 Suivi opérationnel
- ✅ Planification et gestion de chantiers
- ✅ Diagramme de Gantt pour les tâches
- ✅ Gestion des interventions
- ✅ Suivi en temps réel de l'avancement
- ✅ Centralisation de documents et photos

### 📊 Pilotage financier
- ✅ Analyse de rentabilité par chantier
- ✅ Gestion des factures d'achat et fournisseurs
- ✅ Suivi des heures de travail
- ✅ Tableaux de bord et analytics
- ✅ Relances automatiques

### 👥 Gestion multi-tenant
- ✅ Système SaaS avec isolation des données
- ✅ Gestion des entreprises et abonnements
- ✅ Rôles et permissions (admin, manager, user)
- ✅ API REST pour applications mobiles

## 🚀 Installation

### Prérequis

- PHP 8.0 ou supérieur
- MySQL 5.7+ ou MariaDB 10.3+
- Apache avec mod_rewrite activé
- Extensions PHP : PDO, mysqli, mbstring, json

### Étapes d'installation

1. **Cloner le dépôt**
```bash
git clone https://github.com/votre-username/batisaas.git
cd batisaas
```

2. **Créer la base de données**
```bash
mysql -u root -p
CREATE DATABASE batisaas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. **Importer le schéma**
```bash
mysql -u root -p batisaas < database/schema.sql
```

4. **Configurer la base de données**
Éditez `config/database.php` avec vos paramètres :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'batisaas');
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_password');
```

5. **Configurer Apache**
Assurez-vous que le DocumentRoot pointe vers le dossier du projet et que mod_rewrite est activé.

```apache
<VirtualHost *:80>
    ServerName batisaas.local
    DocumentRoot "/chemin/vers/batisaas"

    <Directory "/chemin/vers/batisaas">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

6. **Créer les dossiers nécessaires**
```bash
mkdir -p storage/uploads
mkdir -p storage/logs
chmod -R 775 storage
```

7. **Accéder à l'application**
```
http://localhost/batisaas
ou
http://batisaas.local
```

## 🔐 Compte de démonstration

- **Email :** admin@demo.com
- **Mot de passe :** password

## 📁 Structure du projet

```
batisaas/
├── app/
│   ├── Controllers/        # Contrôleurs MVC
│   │   ├── Api/           # Contrôleurs API REST
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── DevisController.php
│   │   ├── FactureController.php
│   │   ├── ChantierController.php
│   │   └── ClientController.php
│   ├── Models/            # Modèles de données
│   │   ├── User.php
│   │   ├── Company.php
│   │   ├── Client.php
│   │   ├── Devis.php
│   │   ├── Facture.php
│   │   └── Chantier.php
│   ├── Views/             # Vues (templates)
│   │   ├── layouts/
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── devis/
│   │   ├── factures/
│   │   └── chantiers/
│   ├── Core/              # Classes du framework
│   │   ├── Router.php
│   │   ├── Database.php
│   │   ├── Controller.php
│   │   └── Model.php
│   └── Middleware/        # Middlewares
│       ├── AuthMiddleware.php
│       └── ApiMiddleware.php
├── config/                # Configuration
│   ├── config.php
│   └── database.php
├── database/              # Schéma SQL
│   └── schema.sql
├── public/                # Assets publics
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── storage/               # Fichiers stockés
│   ├── uploads/
│   └── logs/
├── .htaccess             # Configuration Apache
├── index.php             # Point d'entrée
└── README.md
```

## 🔌 API REST

L'application expose une API REST pour les applications mobiles.

### Authentification

**POST** `/api/auth/login`
```json
{
  "email": "admin@demo.com",
  "password": "password"
}
```

Réponse :
```json
{
  "success": true,
  "token": "votre-token-api",
  "user": {
    "id": 1,
    "email": "admin@demo.com",
    "first_name": "Admin",
    "last_name": "Demo"
  }
}
```

### Utilisation

Ajoutez le header `Authorization` avec le token :
```
Authorization: Bearer votre-token-api
```

### Endpoints disponibles

- `GET /api/devis` - Liste des devis
- `GET /api/devis/{id}` - Détails d'un devis
- `GET /api/factures` - Liste des factures
- `GET /api/chantiers` - Liste des chantiers

Paramètres de requête :
- `status` - Filtrer par statut
- `limit` - Nombre de résultats (max 100, défaut 20)
- `offset` - Décalage pour la pagination

## 🎨 Personnalisation

### Modifier les couleurs

Éditez `public/css/style.css` :
```css
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --success-color: #10b981;
    --danger-color: #ef4444;
}
```

### Ajouter des modules

1. Créer le contrôleur dans `app/Controllers/`
2. Créer le modèle dans `app/Models/`
3. Créer les vues dans `app/Views/`
4. Ajouter les routes dans `index.php`

## 🔒 Sécurité

- ✅ Protection CSRF
- ✅ Hashage des mots de passe (bcrypt)
- ✅ Validation des données
- ✅ Requêtes préparées (PDO)
- ✅ Protection XSS
- ✅ Sessions sécurisées
- ✅ Isolation multi-tenant

## 🚧 Fonctionnalités à venir

- [ ] Génération de PDF avancée avec TCPDF/mPDF
- [ ] Envoi d'emails avec PHPMailer
- [ ] Signature électronique avec signature pad
- [ ] Intégration de paiement en ligne
- [ ] Facturation électronique (Peppol, Factur-X)
- [ ] Application mobile (React Native)
- [ ] Diagramme de Gantt interactif
- [ ] Notifications push
- [ ] Export comptable
- [ ] Module de messagerie

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Auteur

Développé avec ❤️ pour les entreprises du bâtiment

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📞 Support

Pour toute question ou assistance :
- Email : support@batisaas.com
- Documentation : [docs.batisaas.com](https://docs.batisaas.com)

## 🙏 Remerciements

Inspiré par [Costructor.co](https://costructor.co/) pour la conception et les fonctionnalités.

---

**Note :** Cette application est un projet de démonstration. Pour un usage en production, assurez-vous de :
- Changer tous les secrets et tokens
- Configurer HTTPS
- Mettre en place des sauvegardes régulières
- Implémenter un système de logs robuste
- Optimiser les performances
- Effectuer des tests de sécurité
