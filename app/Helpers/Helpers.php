<?php
/**
 * Fonctions helpers globales
 */

/**
 * Formate un montant en euros
 */
function formatCurrency($amount, $decimals = 2)
{
    return number_format($amount, $decimals, ',', ' ') . ' €';
}

/**
 * Formate une date
 */
function formatDate($date, $format = 'd/m/Y')
{
    if (empty($date)) {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Sécurise une chaîne HTML
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Génère une URL
 */
function url($path = '')
{
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Génère une URL d'asset
 */
function asset($path)
{
    return APP_URL . '/public/' . ltrim($path, '/');
}

/**
 * Retourne l'URL actuelle
 */
function currentUrl()
{
    return $_SERVER['REQUEST_URI'];
}

/**
 * Vérifie si l'URL actuelle correspond à un chemin
 */
function isActiveRoute($path)
{
    return strpos(currentUrl(), $path) === 0;
}

/**
 * Génère un token CSRF
 */
function csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Génère un champ hidden CSRF
 */
function csrf_field()
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
}

/**
 * Tronque un texte
 */
function truncate($text, $length = 100, $suffix = '...')
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Retourne le nom du statut en français
 */
function getStatusLabel($status, $type = 'devis')
{
    $labels = [
        'devis' => [
            'draft' => 'Brouillon',
            'sent' => 'Envoyé',
            'accepted' => 'Accepté',
            'rejected' => 'Refusé',
            'expired' => 'Expiré',
        ],
        'facture' => [
            'draft' => 'Brouillon',
            'sent' => 'Envoyée',
            'paid' => 'Payée',
            'partially_paid' => 'Partiellement payée',
            'overdue' => 'En retard',
            'cancelled' => 'Annulée',
        ],
        'chantier' => [
            'planned' => 'Planifié',
            'in_progress' => 'En cours',
            'completed' => 'Terminé',
            'suspended' => 'Suspendu',
            'cancelled' => 'Annulé',
        ]
    ];

    return $labels[$type][$status] ?? ucfirst($status);
}

/**
 * Retourne la classe badge pour un statut
 */
function getStatusBadgeClass($status, $type = 'devis')
{
    $classes = [
        'devis' => [
            'draft' => 'badge-secondary',
            'sent' => 'badge-info',
            'accepted' => 'badge-success',
            'rejected' => 'badge-danger',
            'expired' => 'badge-danger',
        ],
        'facture' => [
            'draft' => 'badge-secondary',
            'sent' => 'badge-info',
            'paid' => 'badge-success',
            'partially_paid' => 'badge-warning',
            'overdue' => 'badge-danger',
            'cancelled' => 'badge-secondary',
        ],
        'chantier' => [
            'planned' => 'badge-secondary',
            'in_progress' => 'badge-info',
            'completed' => 'badge-success',
            'suspended' => 'badge-warning',
            'cancelled' => 'badge-danger',
        ]
    ];

    return $classes[$type][$status] ?? 'badge-secondary';
}

/**
 * Calcule la TVA
 */
function calculateTVA($amount, $rate = 20)
{
    return $amount * ($rate / 100);
}

/**
 * Calcule le TTC
 */
function calculateTTC($amountHT, $tvaRate = 20)
{
    return $amountHT * (1 + $tvaRate / 100);
}

/**
 * Vérifie si un fichier est une image
 */
function isImage($filename)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
}

/**
 * Génère un nom de fichier sécurisé
 */
function sanitizeFilename($filename)
{
    $filename = mb_strtolower($filename);
    $filename = preg_replace('/[^a-z0-9._-]/', '_', $filename);
    return $filename;
}

/**
 * Retourne la taille d'un fichier formatée
 */
function formatFileSize($bytes)
{
    $units = ['o', 'Ko', 'Mo', 'Go'];
    $i = 0;

    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }

    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Envoie un email (placeholder - à implémenter avec PHPMailer)
 */
function sendEmail($to, $subject, $body, $attachments = [])
{
    // TODO: Implémenter avec PHPMailer
    // Pour l'instant, utilise mail() de PHP

    $headers = [
        'From: ' . SMTP_FROM_EMAIL,
        'Reply-To: ' . SMTP_FROM_EMAIL,
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];

    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * Log une erreur
 */
function logError($message, $context = [])
{
    $logFile = STORAGE_PATH . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';

    $logMessage = "[{$timestamp}] {$message} {$contextStr}\n";

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Retourne l'utilisateur connecté
 */
function auth()
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;

    if ($user === null) {
        $db = Database::getInstance();
        $user = $db->queryOne(
            "SELECT * FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
    }

    return $user;
}

/**
 * Vérifie une permission
 */
function can($permission)
{
    $user = auth();
    if (!$user) {
        return false;
    }

    // Les admins peuvent tout faire
    if ($user['role'] === 'admin') {
        return true;
    }

    // Logique de permissions par rôle
    $permissions = [
        'manager' => ['create', 'edit', 'view', 'delete'],
        'user' => ['create', 'edit', 'view']
    ];

    return in_array($permission, $permissions[$user['role']] ?? []);
}

/**
 * Debugging helper
 */
function dd(...$vars)
{
    echo '<pre style="background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 0.5rem; margin: 1rem; overflow: auto;">';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    die();
}
