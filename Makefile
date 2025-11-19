# ============================================================================
# BatiSaaS - Makefile
# ============================================================================
# Commandes rapides pour faciliter le développement et le déploiement
# ============================================================================

.PHONY: help install start stop restart logs clean test backup optimize deploy

# Couleurs pour l'affichage
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
RESET  := $(shell tput -Txterm sgr0)

# Par défaut : afficher l'aide
.DEFAULT_GOAL := help

## help: Affiche cette aide
help:
	@echo "$(GREEN)BatiSaaS - Commandes disponibles$(RESET)"
	@echo ""
	@grep -E '^## [a-zA-Z_-]+:' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":"}; {printf "  $(YELLOW)%-20s$(RESET) %s\n", $$2, $$3}' | \
		sed 's/##//'

## install: Installation complète de l'application
install:
	@echo "$(GREEN)Installation de BatiSaaS...$(RESET)"
	@if [ ! -f .env ]; then \
		echo "Création du fichier .env..."; \
		cp .env.example .env; \
	fi
	@echo "Création des dossiers nécessaires..."
	@mkdir -p public/uploads/{photos,documents,logos,thumbnails}
	@mkdir -p storage/{logs,cache,sessions}
	@mkdir -p backups
	@chmod -R 775 public/uploads storage backups config
	@echo "$(GREEN)Installation terminée !$(RESET)"
	@echo "Modifiez le fichier .env puis exécutez: make db-init"

## db-init: Initialise la base de données
db-init:
	@echo "$(GREEN)Initialisation de la base de données...$(RESET)"
	@php -r "require 'config/database.php'; \$$pdo = new PDO('mysql:host='.DB_HOST, DB_USER, DB_PASS); \$$pdo->exec('CREATE DATABASE IF NOT EXISTS '.DB_NAME);"
	@mysql -h localhost -u $(shell grep DB_USER .env | cut -d '=' -f2) -p$(shell grep DB_PASS .env | cut -d '=' -f2) $(shell grep DB_NAME .env | cut -d '=' -f2) < database/schema.sql
	@echo "$(GREEN)Base de données initialisée !$(RESET)"

## db-seed: Remplit la base avec des données de démonstration
db-seed:
	@echo "$(GREEN)Insertion des données de démonstration...$(RESET)"
	@mysql -h localhost -u $(shell grep DB_USER .env | cut -d '=' -f2) -p$(shell grep DB_PASS .env | cut -d '=' -f2) $(shell grep DB_NAME .env | cut -d '=' -f2) < database/seeder.sql
	@echo "$(GREEN)Données de démo insérées !$(RESET)"

## db-reset: Réinitialise complètement la base de données
db-reset:
	@echo "$(YELLOW)ATTENTION: Cette action va SUPPRIMER toutes les données !$(RESET)"
	@read -p "Êtes-vous sûr ? (oui/non) " confirm; \
	if [ "$$confirm" = "oui" ]; then \
		make db-init && make db-seed; \
		echo "$(GREEN)Base de données réinitialisée !$(RESET)"; \
	else \
		echo "Opération annulée."; \
	fi

## start: Démarre l'application avec Docker
start:
	@echo "$(GREEN)Démarrage de BatiSaaS...$(RESET)"
	@docker-compose up -d
	@echo "$(GREEN)Application démarrée !$(RESET)"
	@echo "Accès: http://localhost:8080"
	@echo "PHPMyAdmin: http://localhost:8081"

## stop: Arrête l'application Docker
stop:
	@echo "$(YELLOW)Arrêt de BatiSaaS...$(RESET)"
	@docker-compose down
	@echo "$(GREEN)Application arrêtée !$(RESET)"

## restart: Redémarre l'application Docker
restart:
	@echo "$(YELLOW)Redémarrage de BatiSaaS...$(RESET)"
	@docker-compose restart
	@echo "$(GREEN)Application redémarrée !$(RESET)"

## logs: Affiche les logs de l'application
logs:
	@docker-compose logs -f

## logs-web: Affiche les logs du serveur web
logs-web:
	@docker-compose logs -f web

## logs-db: Affiche les logs de la base de données
logs-db:
	@docker-compose logs -f db

## shell: Ouvre un shell dans le container web
shell:
	@docker-compose exec web bash

## shell-db: Ouvre un shell MySQL
shell-db:
	@docker-compose exec db mysql -u batisaas_user -pbatisaas_password batisaas

## clean: Nettoie les fichiers temporaires
clean:
	@echo "$(YELLOW)Nettoyage...$(RESET)"
	@rm -rf storage/cache/*
	@rm -rf storage/logs/*.log
	@find . -type f -name "*.tmp" -delete
	@find . -type f -name "*.bak" -delete
	@echo "$(GREEN)Nettoyage terminé !$(RESET)"

## clean-all: Nettoyage complet (inclut Docker)
clean-all: clean
	@echo "$(YELLOW)Nettoyage complet...$(RESET)"
	@docker-compose down -v
	@rm -rf backups/*
	@echo "$(GREEN)Nettoyage complet terminé !$(RESET)"

## backup: Crée une sauvegarde de la base de données
backup:
	@echo "$(GREEN)Création de la sauvegarde...$(RESET)"
	@php scripts/backup.php
	@echo "$(GREEN)Sauvegarde créée dans le dossier backups/ !$(RESET)"

## optimize: Optimise l'application
optimize:
	@echo "$(GREEN)Optimisation de l'application...$(RESET)"
	@php scripts/optimize.php
	@echo "$(GREEN)Optimisation terminée !$(RESET)"

## monitor: Lance le monitoring du système
monitor:
	@echo "$(GREEN)Lancement du monitoring...$(RESET)"
	@php scripts/monitor.php

## test: Lance les tests unitaires
test:
	@echo "$(GREEN)Lancement des tests...$(RESET)"
	@if [ -f vendor/bin/phpunit ]; then \
		vendor/bin/phpunit; \
	else \
		echo "$(YELLOW)PHPUnit n'est pas installé$(RESET)"; \
	fi

## build: Construit l'image Docker
build:
	@echo "$(GREEN)Construction de l'image Docker...$(RESET)"
	@docker-compose build --no-cache
	@echo "$(GREEN)Image construite !$(RESET)"

## deploy: Déploie l'application en production
deploy:
	@echo "$(GREEN)Déploiement en production...$(RESET)"
	@bash scripts/deploy.sh
	@echo "$(GREEN)Déploiement terminé !$(RESET)"

## prod-start: Démarre en mode production
prod-start:
	@echo "$(GREEN)Démarrage en production...$(RESET)"
	@docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
	@echo "$(GREEN)Application démarrée en production !$(RESET)"

## dev-start: Démarre en mode développement
dev-start:
	@echo "$(GREEN)Démarrage en mode développement...$(RESET)"
	@php -S localhost:8000 -t public
	@echo "$(GREEN)Serveur de développement sur http://localhost:8000$(RESET)"

## permissions: Corrige les permissions des fichiers
permissions:
	@echo "$(GREEN)Correction des permissions...$(RESET)"
	@chmod -R 775 public/uploads storage backups config
	@chown -R www-data:www-data public/uploads storage backups config 2>/dev/null || true
	@echo "$(GREEN)Permissions corrigées !$(RESET)"

## update: Met à jour l'application
update:
	@echo "$(GREEN)Mise à jour de l'application...$(RESET)"
	@git pull origin main
	@make optimize
	@make restart
	@echo "$(GREEN)Mise à jour terminée !$(RESET)"

## health: Vérifie l'état de l'application
health:
	@echo "$(GREEN)Vérification de la santé de l'application...$(RESET)"
	@curl -f http://localhost:8080/health || echo "$(YELLOW)L'application ne répond pas$(RESET)"

## version: Affiche la version de l'application
version:
	@echo "$(GREEN)BatiSaaS v2.0.0$(RESET)"

## info: Affiche les informations système
info:
	@echo "$(GREEN)Informations système :$(RESET)"
	@echo "PHP Version: $(shell php -v | head -n 1)"
	@echo "MySQL: $(shell mysql --version)"
	@echo "Docker: $(shell docker --version)"
	@echo "Docker Compose: $(shell docker-compose --version)"
