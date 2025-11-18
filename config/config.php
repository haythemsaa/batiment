<?php
/**
 * Configuration principale de l'application
 */

// Mode debug
define('DEBUG_MODE', true);

// Timezone
date_default_timezone_set('Europe/Paris');

// Configuration de l'application
define('APP_NAME', 'BatiSaaS');
define('APP_URL', 'http://localhost');
define('APP_VERSION', '1.0.0');

// Chemins
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');

// Configuration des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// Configuration de sécurité
define('CSRF_TOKEN_NAME', '_token');
define('SALT', 'BatiSaaS_Secret_Salt_2024'); // À changer en production

// Configuration email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_FROM_EMAIL', 'noreply@batisaas.com');
define('SMTP_FROM_NAME', APP_NAME);

// Configuration PDF
define('PDF_LOGO_PATH', PUBLIC_PATH . '/assets/img/logo.png');

// Locale
define('LOCALE', 'fr_FR');
define('CURRENCY', 'EUR');
define('CURRENCY_SYMBOL', '€');

// Pagination
define('ITEMS_PER_PAGE', 20);

// Types de documents
define('DOC_TYPE_DEVIS', 'devis');
define('DOC_TYPE_FACTURE', 'facture');
define('DOC_TYPE_AVOIR', 'avoir');

// Statuts des documents
define('STATUS_DRAFT', 'draft');
define('STATUS_SENT', 'sent');
define('STATUS_ACCEPTED', 'accepted');
define('STATUS_REJECTED', 'rejected');
define('STATUS_PAID', 'paid');
define('STATUS_CANCELLED', 'cancelled');

// Statuts des chantiers
define('CHANTIER_STATUS_PLANNED', 'planned');
define('CHANTIER_STATUS_IN_PROGRESS', 'in_progress');
define('CHANTIER_STATUS_COMPLETED', 'completed');
define('CHANTIER_STATUS_SUSPENDED', 'suspended');
