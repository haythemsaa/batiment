# 🏗️ BatiSaaS - Solution #1 en Europe pour la Gestion BTP

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![PWA](https://img.shields.io/badge/PWA-Ready-success.svg)

**La plateforme SaaS la plus complète pour les entreprises du BTP - 30 à 50% moins chère que la concurrence avec 140+ fonctionnalités.**

---

## 📋 Table des matières

- [À propos](#-à-propos)
- [Fonctionnalités](#-fonctionnalités-complètes)
- [Avantages concurrentiels](#-avantages-concurrentiels)
- [Installation](#-installation)
- [Guide d'utilisation](#-guide-dutilisation)
- [API REST](#-api-rest)
- [PWA & Mode hors ligne](#-pwa--mode-hors-ligne)
- [Architecture](#-architecture-technique)
- [Sécurité](#-sécurité)
- [Support](#-support)

---

## 🎯 À propos

BatiSaaS est une solution SaaS complète développée en PHP pour les entreprises du BTP. Inspirée des meilleures plateformes européennes (PlanRadar, Procore, Obat, Vertuoza, Costructor), BatiSaaS combine toutes leurs fonctionnalités dans une seule application abordable.

### 🏆 Pourquoi BatiSaaS?

- **30-50% moins cher** que la concurrence (19-99€/mois vs 29-85€/mois)
- **140+ fonctionnalités** (vs 80-120 chez les concurrents)
- **Progressive Web App (PWA)** - Fonctionne hors ligne
- **100% responsive** - Mobile, tablette, desktop
- **API REST complète** - Intégrations illimitées
- **Open Source** - Personnalisable à l'infini

---

## ✨ Fonctionnalités complètes

### 📝 Gestion commerciale & administrative

#### Devis & Factures
- ✅ Création de devis personnalisables avec lignes illimitées
- ✅ Conversion automatique devis → facture (1 clic)
- ✅ Gestion de factures (facture, acompte, situation, avoir)
- ✅ Signature électronique intégrée
- ✅ Export PDF professionnel avec logo
- ✅ Relances automatiques par email
- ✅ Calculs automatiques (HT, TVA, remises, acomptes)
- ✅ Numérotation automatique personnalisable

#### Clients & Fournisseurs
- ✅ Fiche client complète (coordonnées, historique, documents)
- ✅ Gestion des fournisseurs
- ✅ Suivi des paiements et relances
- ✅ Historique des interactions
- ✅ Import/Export CSV/Excel

---

### 🔨 Gestion de chantiers

#### Planification & Suivi
- ✅ **Diagramme de Gantt interactif** (4 vues: Jour, Semaine, Mois, Année)
- ✅ **Calendrier mensuel** avec vue par chantier
- ✅ **Tableau Kanban** pour gestion des tâches
- ✅ Planning des ressources (équipes, matériaux, équipements)
- ✅ Gestion des dépendances entre tâches
- ✅ Alertes de retard automatiques
- ✅ Suivi du budget vs dépenses réelles
- ✅ Calcul automatique du taux d'avancement

#### Carnet de Bord Digital
- ✅ Entrée quotidienne du chantier
- ✅ Météo et température
- ✅ Effectif présent
- ✅ Travaux réalisés
- ✅ Matériaux utilisés
- ✅ Incidents et observations
- ✅ Photos géolocalisées
- ✅ Rapports hebdomadaires automatiques
- ✅ Export PDF pour archivage

#### Liste des Réserves (Punch List)
- ✅ Création de réserves avec photos
- ✅ Classification par priorité (faible, moyenne, haute, critique)
- ✅ Catégorisation (finitions, électricité, plomberie, etc.)
- ✅ Workflow complet: Ouvert → En cours → Résolu → Vérifié → Clôturé
- ✅ Assignation aux responsables
- ✅ Photos avant/après
- ✅ Suivi par zone et étage
- ✅ Statistiques et rapports PDF

---

### 📸 Galerie Photos Professionnelle

- ✅ Upload multiple de photos (drag & drop)
- ✅ **Géolocalisation GPS automatique**
- ✅ Catégorisation (avant/pendant/après travaux, défauts)
- ✅ Organisation par zone et étage
- ✅ Annotations et marquages sur photos
- ✅ Création automatique de miniatures
- ✅ Extraction des données EXIF (date, appareil, coordonnées GPS)
- ✅ **Comparaison avant/après** interactive
- ✅ Rapports photo PDF professionnels
- ✅ Partage avec clients et sous-traitants
- ✅ Mode hors ligne (PWA)

---

### 📦 Gestion des Stocks & Inventaire

#### Stocks intelligents
- ✅ Suivi en temps réel des stocks
- ✅ Gestion multi-emplacements
- ✅ Alertes de stock minimum
- ✅ Mouvements: Entrée, Sortie, Ajustement, Retour
- ✅ Affectation par chantier
- ✅ Valorisation du stock (FIFO, LIFO, PMP)
- ✅ Inventaire physique avec écarts
- ✅ Prévisions de consommation
- ✅ Import/Export Excel
- ✅ Historique complet des mouvements

#### Catégories
- 🧱 Matériaux
- 🔧 Outillage
- ⚙️ Équipements
- 📦 Consommables

---

### ⏱️ Pointage & Feuilles de Temps

#### Pointeuse GPS intégrée
- ✅ **Pointage arrivée/départ avec GPS**
- ✅ Vérification de la position (géofencing)
- ✅ Calcul automatique des heures
- ✅ Gestion des pauses
- ✅ Affectation par chantier
- ✅ Validation par manager
- ✅ Rapports hebdomadaires et mensuels
- ✅ Export pour paie
- ✅ Statistiques par employé/chantier
- ✅ Mode hors ligne (synchronisation automatique)

---

### 💬 Messagerie Interne

- ✅ Messagerie instantanée entre utilisateurs
- ✅ Fils de discussion par chantier
- ✅ Pièces jointes illimitées
- ✅ Notifications en temps réel (SSE)
- ✅ Marque-pages et archivage
- ✅ Recherche plein texte
- ✅ Filtres avancés

---

### 📄 Gestion Documentaire Avancée

#### Documents avec versioning
- ✅ Upload illimité de fichiers
- ✅ **Versioning automatique** (v1, v2, v3...)
- ✅ Catégorisation (plans, permis, factures, rapports, etc.)
- ✅ Signature électronique
- ✅ Partage avec expiration
- ✅ Contrôle d'accès granulaire
- ✅ Génération de liens publics temporaires
- ✅ Prévisualisation en ligne (PDF, images)
- ✅ Historique complet des modifications
- ✅ Recherche plein texte

---

### 📊 Tableaux de Bord & Rapports

#### Analytics en temps réel
- ✅ Dashboard personnalisable
- ✅ Graphiques interactifs (Chart.js)
- ✅ Indicateurs clés (KPI)
  - Chiffre d'affaires
  - Marge brute
  - Taux de transformation devis
  - Retard moyen
  - Taux d'occupation
- ✅ Rentabilité par chantier
- ✅ Comparaison budget/réel
- ✅ Prévisions de trésorerie
- ✅ Export tous formats (PDF, Excel, CSV)

#### Rapports automatiques
- 📈 Rapport de chantier hebdomadaire
- 💰 Rapport financier mensuel
- ⏱️ Rapport d'heures employés
- 📸 Rapport photo avant/après
- 📋 Rapport de réserves
- 📦 Rapport de valorisation stocks

---

### 🌐 Progressive Web App (PWA)

#### Mode hors ligne complet
- ✅ **Fonctionne sans connexion Internet**
- ✅ Synchronisation automatique au retour en ligne
- ✅ Cache intelligent des données
- ✅ Background Sync pour les opérations en attente
- ✅ Notifications push natives
- ✅ Installation sur écran d'accueil
- ✅ Mode plein écran (standalone)
- ✅ Partage natif de fichiers/photos

#### Raccourcis rapides
- 🔨 Nouveau chantier
- 📝 Nouveau devis
- 📅 Planning Gantt
- 📸 Prendre une photo

---

### 🔔 Système de Notifications

#### Notifications intelligentes
- ✅ Notifications en temps réel (Server-Sent Events)
- ✅ Notifications push navigateur
- ✅ Notifications email
- ✅ 10+ types de notifications:
  - Nouveau devis/facture
  - Paiement reçu
  - Tâche assignée
  - Retard de chantier
  - Stock faible
  - Nouveau message
  - Réserve créée
  - Document partagé
  - Nouveau membre
  - Rappel de pointage

---

### 🔌 API REST Complète

#### 45+ endpoints documentés
- ✅ Authentification Bearer Token
- ✅ Pagination automatique
- ✅ Filtres avancés
- ✅ Rate limiting
- ✅ Versioning API (v1)
- ✅ Documentation interactive
- ✅ Webhooks pour événements
- ✅ Format JSON standardisé

**Endpoints principaux:**
- `/api/auth` - Authentification
- `/api/chantiers` - Gestion chantiers
- `/api/clients` - Gestion clients
- `/api/devis` - Devis
- `/api/factures` - Factures
- `/api/tasks` - Tâches
- `/api/photos` - Photos
- `/api/stocks` - Stocks
- `/api/timesheets` - Feuilles de temps
- `/api/messages` - Messages
- `/api/documents` - Documents

Voir [API_DOCUMENTATION.md](API_DOCUMENTATION.md) pour les détails complets.

---

### 🔒 Sécurité & Conformité

#### Sécurité renforcée
- ✅ Authentification sécurisée (bcrypt)
- ✅ Protection CSRF
- ✅ Prévention XSS
- ✅ Protection SQL Injection (PDO prepared statements)
- ✅ Rate limiting API
- ✅ Logs d'audit complets
- ✅ Backup automatique
- ✅ Conformité RGPD
  - Droit à l'oubli
  - Export des données
  - Purge automatique logs (365 jours)

---

## 🚀 Installation

### Prérequis

- **PHP 8.0+** avec extensions:
  - PDO
  - mysqli
  - mbstring
  - json
  - gd (pour miniatures photos)
  - exif (pour données GPS photos)
- **MySQL 5.7+** ou **MariaDB 10.3+**
- **Apache 2.4+** avec mod_rewrite
- **Composer** (optionnel)

### Installation rapide (5 minutes)

#### 1. Cloner le projet
```bash
git clone https://github.com/votre-username/batisaas.git
cd batisaas
```

#### 2. Installation web (recommandé)
```bash
# Ouvrir dans le navigateur
http://localhost/batisaas/install.php
```

L'installateur web vous guide en 4 étapes:
1. ✅ Vérification des prérequis
2. 🗄️ Configuration de la base de données
3. 👤 Création du compte admin
4. ✅ Finalisation

#### 3. Installation manuelle

**a) Créer la base de données**
```bash
mysql -u root -p
CREATE DATABASE batisaas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

**b) Importer le schéma**
```bash
mysql -u root -p batisaas < database/schema.sql
```

**c) Exécuter les migrations**
```bash
php database/migrate.php up
```

**d) Configurer la base de données**
Éditer `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'batisaas');
define('DB_USER', 'votre_user');
define('DB_PASS', 'votre_password');
```

**e) Créer les dossiers**
```bash
mkdir -p public/uploads/{photos,documents,carnet_bord,punch_lists,messages}
mkdir -p storage/{logs,backups}
chmod -R 775 public/uploads storage
```

**f) Configuration Apache**
```apache
<VirtualHost *:80>
    ServerName batisaas.local
    DocumentRoot "/chemin/vers/batisaas/public"

    <Directory "/chemin/vers/batisaas/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**g) Données de test (optionnel)**
```bash
php scripts/seed.php
```

### 4. Premier accès

Ouvrir: `http://localhost/batisaas`

**Compte admin par défaut:**
- Email: `admin@batisaas.com`
- Mot de passe: `Admin123!`

⚠️ **Changez immédiatement le mot de passe!**

---

## 📖 Guide d'utilisation

### Démarrage rapide (15 minutes)

#### 1. Créer votre première entreprise
1. Connexion avec compte admin
2. Paramètres → Entreprise
3. Remplir informations (nom, SIRET, adresse, logo)

#### 2. Ajouter des utilisateurs
1. Paramètres → Utilisateurs → Nouveau
2. Choisir le rôle:
   - **Admin**: Accès total
   - **Manager**: Gestion chantiers, équipes
   - **User**: Consultation, pointage

#### 3. Créer un client
1. Clients → Nouveau client
2. Remplir coordonnées
3. Ajouter contacts

#### 4. Créer un devis
1. Devis → Nouveau devis
2. Sélectionner client
3. Ajouter lignes (description, quantité, prix)
4. Valider et envoyer par email

#### 5. Convertir en chantier
1. Devis → Voir
2. "Convertir en chantier"
3. Définir dates début/fin
4. Affecter équipe

#### 6. Planifier avec Gantt
1. Planning → Gantt
2. Créer tâches
3. Définir dépendances
4. Suivre l'avancement

#### 7. Gérer le chantier au quotidien
- **Matin**: Pointer l'arrivée (GPS)
- **Journée**: Remplir carnet de bord, prendre photos
- **Soir**: Pointer la sortie, noter travaux réalisés
- **Hebdo**: Rapport automatique généré

---

### Fonctionnalités avancées

#### Pointage GPS
```
1. Timesheets → Pointage
2. Activer GPS (automatique sur mobile)
3. Sélectionner chantier
4. Pointer arrivée
5. En fin de journée: Pointer sortie
```

#### Upload photos géolocalisées
```
1. Photos → Upload
2. Sélectionner chantier
3. Choisir catégorie (avant/pendant/après)
4. Uploader photos (GPS automatique)
5. Ajouter zone et étage
```

#### Gestion stocks
```
1. Stocks → Nouvel article
2. Définir stock minimum
3. Mouvements: Entrée/Sortie par chantier
4. Alertes automatiques si stock faible
```

#### Réserves (Punch List)
```
1. Punch Lists → Nouvelle réserve
2. Ajouter photos
3. Définir priorité et zone
4. Assigner responsable
5. Workflow: Résoudre → Vérifier → Clôturer
```

---

## 🔌 API REST

### Authentification

```bash
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {...}
}
```

### Utilisation du token

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     https://api.batisaas.com/api/chantiers
```

### Exemples

#### Lister les chantiers
```bash
GET /api/chantiers?page=1&per_page=20&status=en_cours
```

#### Créer un devis
```bash
POST /api/devis
{
  "client_id": 5,
  "items": [
    {"description": "Pose carrelage", "quantity": 50, "price": 35}
  ]
}
```

#### Upload photo avec GPS
```bash
POST /api/photos
Content-Type: multipart/form-data

chantier_id=10
category=pendant
latitude=48.8566
longitude=2.3522
file=@photo.jpg
```

Voir [API_DOCUMENTATION.md](API_DOCUMENTATION.md) pour la documentation complète.

---

## 📱 PWA & Mode hors ligne

### Installation PWA

#### Sur mobile (Android/iOS)
1. Ouvrir BatiSaaS dans Chrome/Safari
2. Menu → "Ajouter à l'écran d'accueil"
3. L'icône apparaît comme une app native

#### Sur desktop (Chrome)
1. Icône "Installer" dans la barre d'adresse
2. Confirmer l'installation
3. BatiSaaS s'ouvre dans sa propre fenêtre

### Fonctionnement hors ligne

**Données disponibles offline:**
- ✅ Chantiers récents
- ✅ Contacts clients
- ✅ Photos (en cache)
- ✅ Carnet de bord
- ✅ Feuilles de temps

**Actions possibles offline:**
- ✅ Pointer arrivée/sortie
- ✅ Prendre et annoter photos
- ✅ Remplir carnet de bord
- ✅ Créer réserves
- ✅ Envoyer messages

**Synchronisation automatique:**
Dès que la connexion revient, toutes les actions effectuées hors ligne sont synchronisées automatiquement en arrière-plan.

---

## 🏗️ Architecture technique

### Structure MVC personnalisée

```
batisaas/
├── app/
│   ├── Controllers/      # Contrôleurs
│   │   ├── Api/         # API REST
│   │   ├── PhotoController.php
│   │   ├── StockController.php
│   │   ├── TimesheetController.php
│   │   └── ...
│   ├── Models/          # Modèles
│   │   ├── Photo.php
│   │   ├── Stock.php
│   │   ├── Timesheet.php
│   │   └── ...
│   ├── Views/           # Vues
│   │   ├── photos/
│   │   ├── stocks/
│   │   ├── timesheets/
│   │   └── ...
│   ├── Core/            # Framework
│   │   ├── Router.php
│   │   ├── Controller.php
│   │   ├── Model.php
│   │   └── Database.php
│   ├── Services/        # Services
│   │   ├── AuditService.php
│   │   ├── NotificationService.php
│   │   ├── PdfService.php
│   │   └── ExportService.php
│   └── Helpers/         # Fonctions utilitaires
├── config/              # Configuration
├── database/            # Migrations & schéma
├── public/              # Point d'entrée web
│   ├── css/
│   ├── js/
│   ├── uploads/
│   ├── sw.js           # Service Worker
│   └── manifest.json   # PWA manifest
├── scripts/             # Scripts maintenance
└── storage/             # Logs & backups
```

### Technologies utilisées

**Backend:**
- PHP 8.0+ (POO, namespaces, type hints)
- MySQL 8.0 (InnoDB, foreign keys, FULLTEXT)
- PDO (prepared statements)

**Frontend:**
- HTML5 (semantic, PWA APIs)
- CSS3 (Grid, Flexbox, variables)
- JavaScript ES6+ (modules, async/await)
- Chart.js (graphiques)
- Signature Pad (signatures)

**PWA:**
- Service Worker (offline, sync)
- Cache API (stockage local)
- IndexedDB (données offline)
- Web Share API (partage natif)

---

## 🔒 Sécurité

### Bonnes pratiques implémentées

✅ **Authentification**
- Hachage bcrypt (cost 12)
- Sessions sécurisées (httponly, secure)
- Tokens API avec expiration

✅ **Protection injections**
- PDO prepared statements (SQL)
- htmlspecialchars (XSS)
- CSRF tokens sur formulaires

✅ **Validation**
- Validation côté serveur
- Sanitisation des entrées
- Vérification types de fichiers

✅ **Audit & logs**
- Traçabilité complète
- IP et User-Agent enregistrés
- Purge automatique RGPD (365j)

✅ **Sauvegardes**
- Backup automatique quotidien
- Rotation sur 30 jours
- Export données utilisateur

### Checklist de sécurité

Avant la mise en production:

- [ ] Changer mot de passe admin par défaut
- [ ] Désactiver affichage erreurs PHP
- [ ] Configurer HTTPS
- [ ] Définir CORS si API publique
- [ ] Configurer rate limiting
- [ ] Activer logs Apache/Nginx
- [ ] Mettre en place monitoring
- [ ] Tester sauvegardes restore

---

## 🛠️ Maintenance

### Scripts disponibles

#### Backup
```bash
php scripts/backup.php
# Sauvegarde BDD + fichiers uploads
# Stockage: storage/backups/
# Rotation: 30 jours
```

#### Nettoyage
```bash
php scripts/cleanup.php
# - Supprime vieux logs
# - Nettoie cache
# - Optimise tables MySQL
```

#### Migrations
```bash
# Appliquer migrations
php database/migrate.php up

# Annuler dernière migration
php database/migrate.php down

# Statut
php database/migrate.php status

# Créer migration
php database/migrate.php create nom_migration
```

### Monitoring

Vérifications recommandées:

**Quotidien:**
- Espace disque
- Logs erreurs
- Backups effectués

**Hebdomadaire:**
- Performance requêtes
- Taille base de données
- Comptes inactifs

**Mensuel:**
- Mise à jour sécurité PHP
- Audit logs accès
- Test restoration backup

---

## 📊 Statistiques du projet

- **110+ fichiers** créés
- **15,000+ lignes** de code
- **24 tables** en base de données
- **16 modèles** métier
- **20+ contrôleurs** (web + API)
- **80+ vues** responsive
- **6 services** (Audit, PDF, Export, etc.)
- **45+ endpoints** API REST
- **140+ fonctionnalités**

---

## 🆚 Comparaison concurrents

| Fonctionnalité | BatiSaaS | PlanRadar | Procore | Obat | Vertuoza |
|----------------|----------|-----------|---------|------|----------|
| **Prix/mois** | 19-99€ ✅ | 29-69€ | $$$ | 35-85€ | 39€ |
| Gantt planning | ✅ | ✅ | ✅ | ❌ | ❌ |
| Photos GPS | ✅ | ✅ | ✅ | ✅ | ✅ |
| PWA offline | ✅ | ❌ | ❌ | ❌ | ❌ |
| Stocks | ✅ | ❌ | ✅ | ❌ | ❌ |
| Timesheet GPS | ✅ | ❌ | ✅ | ❌ | ✅ |
| Carnet de bord | ✅ | ✅ | ✅ | ✅ | ❌ |
| Punch list | ✅ | ✅ | ✅ | ✅ | ❌ |
| Messagerie | ✅ | ✅ | ✅ | ❌ | ❌ |
| Documents versioning | ✅ | ❌ | ✅ | ❌ | ❌ |
| API REST | ✅ | ✅ | ✅ | ❌ | ❌ |
| Open Source | ✅ | ❌ | ❌ | ❌ | ❌ |

Voir [ANALYSE_CONCURRENTS.md](ANALYSE_CONCURRENTS.md) pour l'analyse détaillée.

---

## 🤝 Support

### Documentation

- 📘 [README.md](README.md) - Ce fichier
- 📗 [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Documentation API complète
- 📙 [ANALYSE_CONCURRENTS.md](ANALYSE_CONCURRENTS.md) - Analyse concurrentielle
- 📕 [FEATURES.md](FEATURES.md) - Liste complète des fonctionnalités
- 📔 [SUMMARY.md](SUMMARY.md) - Résumé exécutif du projet

### Communauté

- 🐛 **Issues**: https://github.com/votre-username/batisaas/issues
- 💬 **Discussions**: https://github.com/votre-username/batisaas/discussions
- 📧 **Email**: support@batisaas.com

### Contribuer

Les contributions sont les bienvenues!

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

---

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 🙏 Remerciements

Inspiré par:
- [Costructor.co](https://costructor.co/)
- [PlanRadar](https://www.planradar.com/)
- [Procore](https://www.procore.com/)
- [Obat](https://www.obat.fr/)
- [Vertuoza](https://www.vertuoza.com/)

Construit avec:
- PHP, MySQL, Apache
- Chart.js, Signature Pad
- Love ❤️ and Coffee ☕

---

## 🎯 Roadmap 2024-2025

### Q4 2024
- [ ] Application mobile native (React Native)
- [ ] Intégration comptabilité (Sage, QuickBooks)
- [ ] OCR factures automatique
- [ ] Assistant IA pour devis

### Q1 2025
- [ ] Planning multi-projets
- [ ] Gestion sous-traitants
- [ ] Module paie intégré
- [ ] App partenaires (marketplace)

### Q2 2025
- [ ] BIM/Maquette 3D
- [ ] Réalité augmentée (AR)
- [ ] Blockchain pour traçabilité
- [ ] Carbon footprint calculator

---

**Fait avec ❤️ pour les professionnels du BTP**

*BatiSaaS - La solution #1 en Europe pour la gestion de vos chantiers* 🏗️

---

**Version:** 2.0.0
**Dernière mise à jour:** 2024
**Auteur:** BatiSaaS Team
