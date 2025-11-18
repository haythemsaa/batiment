# 🚀 Guide de Démarrage Rapide - BatiSaaS

Démarrez avec BatiSaaS en **5 minutes** chrono!

---

## ⚡ Installation Express (5 min)

```bash
# 1. Cloner
git clone https://github.com/votre-username/batisaas.git
cd batisaas

# 2. Base de données
mysql -u root -p -e "CREATE DATABASE batisaas CHARACTER SET utf8mb4;"
mysql -u root -p batisaas < database/schema.sql
php database/migrate.php up

# 3. Configuration
nano config/database.php  # Modifier DB_USER, DB_PASS

# 4. Dossiers
mkdir -p public/uploads/{photos,documents,carnet_bord,punch_lists,messages}
mkdir -p storage/{logs,backups}
chmod -R 775 public/uploads storage

# 5. Lancer
php -S localhost:8000 -t public
```

**Compte par défaut:** admin@batisaas.com / Admin123!

⚠️ **Changez le mot de passe immédiatement!**

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
0 2 * * * php /path/to/scripts/backup.php
0 3 * * * php /path/to/scripts/optimize.php
*/5 * * * * php /path/to/scripts/monitor.php
```

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
