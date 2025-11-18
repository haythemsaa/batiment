# Changelog

Tous les changements notables de ce projet seront documentés dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [1.0.0] - 2024-11-18

### Ajouté
- Architecture MVC complète en PHP
- Système d'authentification multi-tenant
- Module de gestion des devis
  - Création, édition, suppression de devis
  - Conversion devis → facture
  - Génération PDF
  - Gestion des lignes de devis avec calculs automatiques
- Module de gestion des factures
  - Création de factures, acomptes, avoirs
  - Suivi des paiements
  - Gestion des échéances
  - Génération PDF
- Module de gestion des chantiers
  - Planification et suivi
  - Gestion des tâches
  - Diagramme de Gantt
  - Calcul de rentabilité
  - Suivi des interventions
- Module de gestion des clients
  - Clients particuliers et entreprises
  - Historique complet
- Tableau de bord avec analytics
  - Statistiques en temps réel
  - Graphiques de revenus
  - Indicateurs clés (KPI)
  - Activités récentes
- API REST complète
  - Authentification par token
  - Endpoints pour devis, factures, chantiers
  - Pagination et filtres
- Interface utilisateur responsive
  - Design moderne avec CSS Grid/Flexbox
  - Compatible mobile, tablette, desktop
  - Navigation intuitive
  - Composants réutilisables
- Gestion des fournisseurs
- Suivi des dépenses
- Gestion des heures de travail
- Système de paramètres par entreprise
- Sécurité
  - Protection CSRF
  - Hashage bcrypt des mots de passe
  - Requêtes préparées (PDO)
  - Validation des données
  - Sessions sécurisées

### Documentation
- README.md complet
- Guide d'installation (INSTALL.md)
- Configuration Docker
- Exemples d'utilisation API
- Documentation du code

### Infrastructure
- Configuration Apache avec .htaccess
- Docker Compose pour développement
- Scripts de sauvegarde
- Gestion des logs
- GitIgnore configuré

## [Futur] - À venir

### Prévu pour v1.1.0
- [ ] Intégration PHPMailer pour envoi d'emails
- [ ] Génération PDF avancée avec TCPDF
- [ ] Module de signature électronique
- [ ] Export comptable
- [ ] Facturation électronique (Factur-X)

### Prévu pour v1.2.0
- [ ] Application mobile React Native
- [ ] Notifications push
- [ ] Intégration paiement en ligne
- [ ] Module de messagerie interne
- [ ] Gestion des stocks

### Prévu pour v2.0.0
- [ ] Multi-langues (i18n)
- [ ] Thèmes personnalisables
- [ ] Plugins et extensions
- [ ] Marketplace d'intégrations
- [ ] Intelligence artificielle pour prévisions

---

## Types de changements
- `Ajouté` pour les nouvelles fonctionnalités.
- `Modifié` pour les changements aux fonctionnalités existantes.
- `Déprécié` pour les fonctionnalités qui seront bientôt supprimées.
- `Supprimé` pour les fonctionnalités supprimées.
- `Corrigé` pour les corrections de bugs.
- `Sécurité` en cas de vulnérabilités.
