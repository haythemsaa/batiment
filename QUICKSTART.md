# 🚀 Guide de Démarrage Rapide - BatiSaaS

Démarrez avec BatiSaaS en **2 minutes** avec Docker ou **5 minutes** avec installation classique!

---

## 🐳 MÉTHODE 1: Docker (RECOMMANDÉ - 2 min)

**La méthode la plus rapide et la plus simple!**

```bash
# 1. Cloner le projet
git clone https://github.com/votre-username/batisaas.git
cd batisaas

# 2. Démarrer avec Docker Compose (tout est automatique !)
docker-compose up -d

# 3. Charger les données de démonstration (optionnel)
docker-compose exec web mysql -h db -u batisaas_user -pbatisaas_password batisaas < database/seeder.sql
```

**C'est tout ! L'application est prête !** 🎉

- **Application:** http://localhost:8080
- **PHPMyAdmin:** http://localhost:8081

**Comptes de démonstration:**
- Admin: `admin@batipro.fr` / `password`
- Chef: `chef@batipro.fr` / `password`
- Ouvrier: `ouvrier1@batipro.fr` / `password`

---

## 🛠️ MÉTHODE 2: Makefile (3 min)

**Utilise les commandes rapides du Makefile:**

```bash
# 1. Cloner
git clone https://github.com/votre-username/batisaas.git
cd batisaas

# 2. Installation complète en une commande
make install

# 3. Configurer le fichier .env
nano .env  # Modifier DB_USER, DB_PASS

# 4. Initialiser la base de données
make db-init

# 5. Charger les données de démo (optionnel)
make db-seed

# 6. Démarrer l'application
make dev-start
```

**Application sur:** http://localhost:8000

---

## ⚡ MÉTHODE 3: Installation Express (5 min)

```bash
# 1. Cloner
git clone https://github.com/votre-username/batisaas.git
cd batisaas

# 2. Base de données
mysql -u root -p -e "CREATE DATABASE batisaas CHARACTER SET utf8mb4;"
mysql -u root -p batisaas < database/schema.sql
mysql -u root -p batisaas < database/seeder.sql  # Données de démo

# 3. Configuration
cp .env.example .env
nano .env  # Modifier DB_USER, DB_PASS

# 4. Dossiers
mkdir -p public/uploads/{photos,documents,logos,thumbnails}
mkdir -p storage/{logs,cache,sessions}
chmod -R 775 public/uploads storage config

# 5. Lancer
php -S localhost:8000 -t public
```

**Application sur:** http://localhost:8000

---

## 🌐 MÉTHODE 4: Installation Web (Interface graphique)

**Pour une installation sans ligne de commande:**

1. Téléchargez BatiSaaS sur votre serveur web
2. Accédez à: `http://votre-domaine.com/install.php`
3. Suivez le wizard d'installation en 4 étapes:
   - ✅ Vérification des prérequis
   - ⚙️ Configuration base de données
   - 👤 Création compte administrateur
   - 🎉 Installation terminée !

**Interface élégante avec barre de progression !**

⚠️ **Supprimez `install.php` après installation pour sécurité**

---

## 🎯 Premier Démarrage (10 min)

1. **Configurer entreprise** (Paramètres → Entreprise)
2. **Créer utilisateurs** (Paramètres → Utilisateurs)
3. **Ajouter client** (Clients → Nouveau)
4. **Créer devis** (Devis → Nouveau)
5. **Convertir en chantier** (Devis → Convertir)

**🎉 C'est prêt!**

---

## 📱 Utilisation Quotidienne

### Matin
```
Timesheets → Pointage → Pointer arrivée ⏰
```

### Journée
```
Photos → Upload photos GPS 📸
Stocks → Enregistrer sorties 📦
Carnet de Bord → Entrée du jour ✏️
```

### Soir
```
Timesheets → Pointer sortie ⏰
Carnet de Bord → Compléter 📝
```

---

## 🎨 Fonctionnalités Principales

| Module | URL | Description |
|--------|-----|-------------|
| 📸 Photos | `/photos` | Upload GPS automatique |
| 📦 Stocks | `/stocks` | Gestion inventaire + alertes |
| ⏱️ Pointage | `/timesheets/clock` | Clock in/out GPS |
| 📋 Réserves | `/punch-lists` | Défauts avec workflow |
| 📔 Carnet | `/carnet-bord/{id}` | Journal quotidien |
| 📄 Documents | `/documents` | Versioning automatique |
| 📅 Planning | `/planning/gantt` | Gantt interactif |

---

## 📱 PWA - Mode Hors Ligne

**Installation:**
- Mobile: Menu → "Ajouter à l'écran d'accueil"
- Desktop: Cliquer icône "Installer"

**Hors ligne:**
✅ Pointage ✅ Photos ✅ Carnet ✅ Messages

**Sync automatique** au retour en ligne!

---

## 🛠️ Maintenance

### Avec Makefile (recommandé)

```bash
# Backup de la base de données
make backup

# Optimiser l'application
make optimize

# Monitoring système
make monitor

# Nettoyer les fichiers temporaires
make clean

# Health check
make health
```

### Sans Makefile

```bash
# Backup quotidien
php scripts/backup.php

# Optimisation
php scripts/optimize.php

# Monitoring
php scripts/monitor.php
```

**Cron automatique:**
```cron
0 2 * * * cd /var/www/batisaas && make backup
0 3 * * * cd /var/www/batisaas && make optimize
*/5 * * * * cd /var/www/batisaas && make monitor
```

---

## 📋 Commandes Makefile Disponibles

```bash
make help              # Afficher toutes les commandes
make install           # Installation complète
make start             # Démarrer avec Docker
make stop              # Arrêter Docker
make restart           # Redémarrer Docker
make logs              # Voir les logs
make shell             # Ouvrir un shell dans le container
make db-init           # Initialiser la base de données
make db-seed           # Charger données de démo
make db-reset          # Réinitialiser la BDD
make backup            # Créer une sauvegarde
make optimize          # Optimiser l'application
make clean             # Nettoyer les fichiers temporaires
make deploy            # Déployer en production
make health            # Vérifier l'état de l'app
make version           # Afficher la version
```

**Tapez `make help` pour voir toutes les commandes !**

---

## 🆘 Problèmes Courants

**Erreur connexion BDD:**
```bash
nano config/database.php
mysql -u USER -p -h HOST DATABASE
```

**Erreur permissions:**
```bash
chmod -R 775 public/uploads storage
chown -R www-data:www-data public/uploads storage
```

**GPS ne fonctionne pas:**
- Activer HTTPS (requis)
- Autoriser géolocalisation navigateur
- Vérifier extension `php-exif`

---

## 📚 Documentation Complète

- 📘 [README.md](README.md) - Documentation complète
- 📗 [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - API REST
- 📙 [ANALYSE_CONCURRENTS.md](ANALYSE_CONCURRENTS.md) - Analyse marché
- 📕 [FEATURES.md](FEATURES.md) - 140+ fonctionnalités
- 📔 [SUMMARY.md](SUMMARY.md) - Résumé exécutif

---

**Version:** 2.0.0 | **Licence:** MIT

**Fait avec ❤️ pour les pros du BTP**

🏗️ *BatiSaaS - Solution #1 en Europe*
