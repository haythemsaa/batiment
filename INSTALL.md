# Guide d'installation de BatiSaaS

## Installation rapide avec Docker

### Prérequis
- Docker Desktop installé
- Docker Compose installé

### Étapes

1. **Cloner le projet**
```bash
git clone https://github.com/votre-username/batisaas.git
cd batisaas
```

2. **Lancer les conteneurs**
```bash
docker-compose up -d
```

3. **Accéder à l'application**
- Application : http://localhost:8080
- PHPMyAdmin : http://localhost:8081

4. **Compte de démonstration**
- Email : admin@demo.com
- Mot de passe : password

## Installation manuelle

### Prérequis système

- **Serveur Web** : Apache 2.4+ avec mod_rewrite
- **PHP** : Version 8.0 ou supérieure
- **Base de données** : MySQL 5.7+ ou MariaDB 10.3+

### Extensions PHP requises

```bash
# Sur Ubuntu/Debian
sudo apt-get install php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl

# Sur CentOS/RHEL
sudo yum install php82 php82-mysqlnd php82-mbstring php82-xml php82-curl
```

### Configuration Apache

1. **Activer mod_rewrite**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

2. **Créer un Virtual Host**

Créez `/etc/apache2/sites-available/batisaas.conf` :

```apache
<VirtualHost *:80>
    ServerName batisaas.local
    ServerAdmin admin@batisaas.local
    DocumentRoot /var/www/batisaas

    <Directory /var/www/batisaas>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/batisaas-error.log
    CustomLog ${APACHE_LOG_DIR}/batisaas-access.log combined
</VirtualHost>
```

3. **Activer le site**
```bash
sudo a2ensite batisaas.conf
sudo systemctl reload apache2
```

4. **Ajouter au fichier hosts**
```bash
sudo nano /etc/hosts
# Ajouter :
127.0.0.1   batisaas.local
```

### Installation de la base de données

1. **Se connecter à MySQL**
```bash
mysql -u root -p
```

2. **Créer la base de données et l'utilisateur**
```sql
CREATE DATABASE batisaas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'batisaas_user'@'localhost' IDENTIFIED BY 'votre_mot_de_passe';
GRANT ALL PRIVILEGES ON batisaas.* TO 'batisaas_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

3. **Importer le schéma**
```bash
mysql -u batisaas_user -p batisaas < database/schema.sql
```

### Configuration de l'application

1. **Copier les fichiers**
```bash
sudo cp -r batisaas /var/www/
sudo chown -R www-data:www-data /var/www/batisaas
```

2. **Configurer la base de données**

Éditez `config/database.php` :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'batisaas');
define('DB_USER', 'batisaas_user');
define('DB_PASS', 'votre_mot_de_passe');
```

3. **Créer les dossiers de stockage**
```bash
mkdir -p storage/{uploads,logs,cache,sessions}
sudo chmod -R 775 storage
sudo chown -R www-data:www-data storage
```

4. **Configurer les permissions**
```bash
sudo chmod 644 .htaccess
sudo chmod 644 index.php
sudo chmod -R 755 app
sudo chmod -R 755 config
sudo chmod -R 755 public
```

## Configuration HTTPS (Production)

### Avec Let's Encrypt (Certbot)

1. **Installer Certbot**
```bash
sudo apt-get install certbot python3-certbot-apache
```

2. **Obtenir un certificat**
```bash
sudo certbot --apache -d votredomaine.com
```

3. **Renouvellement automatique**
```bash
sudo certbot renew --dry-run
```

## Configuration de l'email

Éditez `config/config.php` :
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'votre-email@gmail.com');
define('SMTP_PASSWORD', 'votre-mot-de-passe');
```

Pour Gmail, vous devez utiliser un "App Password" :
1. Accédez à votre compte Google
2. Sécurité > Validation en deux étapes
3. Mots de passe des applications

## Optimisation des performances

### Activer le cache PHP OPcache

Éditez `/etc/php/8.2/apache2/php.ini` :
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Configurer la compression Apache

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

## Sauvegardes

### Script de sauvegarde automatique

Créez `/usr/local/bin/backup-batisaas.sh` :
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/batisaas"
DATE=$(date +%Y%m%d_%H%M%S)

# Créer le dossier de sauvegarde
mkdir -p $BACKUP_DIR

# Sauvegarder la base de données
mysqldump -u batisaas_user -p'votre_mot_de_passe' batisaas | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Sauvegarder les fichiers
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/batisaas/storage/uploads

# Supprimer les sauvegardes de plus de 30 jours
find $BACKUP_DIR -type f -mtime +30 -delete
```

Ajoutez au crontab :
```bash
sudo crontab -e
# Ajouter :
0 2 * * * /usr/local/bin/backup-batisaas.sh
```

## Dépannage

### Erreur 500

1. Vérifiez les logs Apache :
```bash
tail -f /var/log/apache2/batisaas-error.log
```

2. Activez le mode debug dans `config/config.php` :
```php
define('DEBUG_MODE', true);
```

### Problème de connexion à la base de données

1. Testez la connexion :
```bash
mysql -u batisaas_user -p -h localhost batisaas
```

2. Vérifiez les paramètres dans `config/database.php`

### Les routes ne fonctionnent pas (404)

1. Vérifiez que mod_rewrite est activé :
```bash
apache2ctl -M | grep rewrite
```

2. Vérifiez que AllowOverride est sur "All" dans votre VirtualHost

### Problème de permissions

```bash
sudo chown -R www-data:www-data /var/www/batisaas
sudo chmod -R 755 /var/www/batisaas
sudo chmod -R 775 /var/www/batisaas/storage
```

## Support

Pour toute aide supplémentaire :
- Documentation : https://docs.batisaas.com
- Support : support@batisaas.com
- Issues GitHub : https://github.com/votre-username/batisaas/issues
