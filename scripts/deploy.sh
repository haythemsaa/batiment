#!/bin/bash

# ============================================================================
# BatiSaaS - Script de déploiement automatique
# ============================================================================
# Ce script automatise le déploiement de l'application en production
# ============================================================================

set -e  # Arrêter en cas d'erreur

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="BatiSaaS"
APP_DIR="/var/www/batisaas"
BACKUP_DIR="/var/backups/batisaas"
GIT_REPO="https://github.com/votre-utilisateur/batisaas.git"
GIT_BRANCH="main"

# Fonctions utilitaires
print_header() {
    echo -e "${BLUE}============================================================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}============================================================================${NC}"
}

print_success() {
    echo -e "${GREEN} $1${NC}"
}

print_error() {
    echo -e "${RED} $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}  $1${NC}"
}

print_info() {
    echo -e "${BLUE}9 $1${NC}"
}

# Vérification des prérequis
check_prerequisites() {
    print_header "Vérification des prérequis"

    # Vérifier si on est en root
    if [ "$EUID" -ne 0 ]; then
        print_warning "Ce script doit être exécuté en tant que root (sudo)"
    fi

    # Vérifier Git
    if ! command -v git &> /dev/null; then
        print_error "Git n'est pas installé"
        exit 1
    fi
    print_success "Git installé"

    # Vérifier PHP
    if ! command -v php &> /dev/null; then
        print_error "PHP n'est pas installé"
        exit 1
    fi
    PHP_VERSION=$(php -v | head -n 1 | cut -d ' ' -f 2)
    print_success "PHP $PHP_VERSION installé"

    # Vérifier MySQL
    if ! command -v mysql &> /dev/null; then
        print_error "MySQL n'est pas installé"
        exit 1
    fi
    print_success "MySQL installé"

    # Vérifier Docker (optionnel)
    if command -v docker &> /dev/null; then
        DOCKER_VERSION=$(docker --version | cut -d ' ' -f 3 | tr -d ',')
        print_success "Docker $DOCKER_VERSION installé"
    else
        print_warning "Docker n'est pas installé (optionnel)"
    fi

    echo ""
}

# Sauvegarde avant déploiement
create_backup() {
    print_header "Création de la sauvegarde"

    BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="$BACKUP_DIR/$BACKUP_DATE"

    mkdir -p "$BACKUP_PATH"

    # Sauvegarde de la base de données
    if [ -f "$APP_DIR/.env" ]; then
        source "$APP_DIR/.env"
        print_info "Sauvegarde de la base de données..."
        mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_PATH/database.sql.gz"
        print_success "Base de données sauvegardée"
    fi

    # Sauvegarde des fichiers uploadés
    if [ -d "$APP_DIR/public/uploads" ]; then
        print_info "Sauvegarde des fichiers uploadés..."
        tar -czf "$BACKUP_PATH/uploads.tar.gz" -C "$APP_DIR/public" uploads
        print_success "Fichiers uploadés sauvegardés"
    fi

    # Sauvegarde du fichier .env
    if [ -f "$APP_DIR/.env" ]; then
        cp "$APP_DIR/.env" "$BACKUP_PATH/.env"
        print_success "Configuration sauvegardée"
    fi

    print_success "Sauvegarde complète créée dans $BACKUP_PATH"
    echo ""
}

# Récupération du code
pull_code() {
    print_header "Récupération du code source"

    if [ -d "$APP_DIR/.git" ]; then
        print_info "Mise à jour du dépôt Git..."
        cd "$APP_DIR"
        git fetch origin
        git checkout "$GIT_BRANCH"
        git pull origin "$GIT_BRANCH"
        print_success "Code source mis à jour"
    else
        print_info "Clonage du dépôt Git..."
        git clone -b "$GIT_BRANCH" "$GIT_REPO" "$APP_DIR"
        print_success "Code source cloné"
    fi

    echo ""
}

# Installation des dépendances
install_dependencies() {
    print_header "Installation des dépendances"

    cd "$APP_DIR"

    # Composer (si présent)
    if [ -f "composer.json" ]; then
        if command -v composer &> /dev/null; then
            print_info "Installation des dépendances PHP..."
            composer install --no-dev --optimize-autoloader
            print_success "Dépendances PHP installées"
        else
            print_warning "Composer n'est pas installé"
        fi
    fi

    # NPM (si présent)
    if [ -f "package.json" ]; then
        if command -v npm &> /dev/null; then
            print_info "Installation des dépendances NPM..."
            npm install --production
            npm run build
            print_success "Dépendances NPM installées"
        else
            print_warning "NPM n'est pas installé"
        fi
    fi

    echo ""
}

# Configuration
setup_configuration() {
    print_header "Configuration de l'application"

    cd "$APP_DIR"

    # Copier .env.example si .env n'existe pas
    if [ ! -f ".env" ]; then
        if [ -f ".env.example" ]; then
            cp .env.example .env
            print_success "Fichier .env créé"
            print_warning "IMPORTANT: Configurez le fichier .env avant de continuer"
            read -p "Appuyez sur Entrée après avoir configuré .env..."
        fi
    fi

    # Créer les dossiers nécessaires
    print_info "Création des dossiers..."
    mkdir -p public/uploads/{photos,documents,logos,thumbnails}
    mkdir -p storage/{logs,cache,sessions}
    mkdir -p backups

    # Permissions
    print_info "Configuration des permissions..."
    chown -R www-data:www-data "$APP_DIR"
    chmod -R 755 "$APP_DIR"
    chmod -R 775 public/uploads storage backups config
    chmod 600 .env

    print_success "Configuration terminée"
    echo ""
}

# Migration de la base de données
migrate_database() {
    print_header "Migration de la base de données"

    cd "$APP_DIR"

    if [ -f ".env" ]; then
        source .env

        print_info "Exécution des migrations..."

        # Si le fichier database/schema.sql existe
        if [ -f "database/schema.sql" ]; then
            mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/schema.sql
            print_success "Migrations exécutées"
        fi

        # Si le fichier database/seeder.sql existe (optionnel)
        if [ -f "database/seeder.sql" ]; then
            read -p "Voulez-vous charger les données de démonstration ? (o/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Oo]$ ]]; then
                mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/seeder.sql
                print_success "Données de démonstration chargées"
            fi
        fi
    fi

    echo ""
}

# Optimisation
optimize() {
    print_header "Optimisation de l'application"

    cd "$APP_DIR"

    # Nettoyer le cache
    print_info "Nettoyage du cache..."
    rm -rf storage/cache/*
    print_success "Cache nettoyé"

    # Optimiser les tables de la base de données
    if [ -f ".env" ]; then
        source .env
        print_info "Optimisation de la base de données..."
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "OPTIMIZE TABLE companies, users, clients, chantiers, devis, factures;"
        print_success "Base de données optimisée"
    fi

    # Optimisation PHP (OPcache)
    print_info "Rechargement OPcache..."
    if command -v php &> /dev/null; then
        php -r "if (function_exists('opcache_reset')) opcache_reset();"
        print_success "OPcache rechargé"
    fi

    echo ""
}

# Redémarrage des services
restart_services() {
    print_header "Redémarrage des services"

    # Apache
    if systemctl is-active --quiet apache2; then
        print_info "Redémarrage d'Apache..."
        systemctl restart apache2
        print_success "Apache redémarré"
    fi

    # Nginx
    if systemctl is-active --quiet nginx; then
        print_info "Redémarrage de Nginx..."
        systemctl restart nginx
        print_success "Nginx redémarré"
    fi

    # PHP-FPM
    if systemctl is-active --quiet php8.2-fpm; then
        print_info "Redémarrage de PHP-FPM..."
        systemctl restart php8.2-fpm
        print_success "PHP-FPM redémarré"
    fi

    # MySQL
    if systemctl is-active --quiet mysql; then
        print_info "Redémarrage de MySQL..."
        systemctl restart mysql
        print_success "MySQL redémarré"
    fi

    echo ""
}

# Health check
health_check() {
    print_header "Vérification de la santé de l'application"

    sleep 3

    # Test HTTP
    if command -v curl &> /dev/null; then
        RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health)
        if [ "$RESPONSE" = "200" ]; then
            print_success "Application en ligne (HTTP $RESPONSE)"
        else
            print_error "Problème détecté (HTTP $RESPONSE)"
        fi
    fi

    echo ""
}

# Fonction principale
main() {
    print_header "Déploiement de $APP_NAME"
    echo ""

    check_prerequisites
    create_backup
    pull_code
    install_dependencies
    setup_configuration
    migrate_database
    optimize
    restart_services
    health_check

    print_header "Déploiement terminé avec succès !"
    print_success "L'application $APP_NAME est maintenant déployée"
    print_info "Consultez les logs en cas de problème: /var/log/apache2/ ou /var/log/nginx/"
    echo ""
}

# Exécution
main "$@"
