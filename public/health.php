<?php
/**
 * Health Check Endpoint pour BatiSaaS
 *
 * Utilisé par Docker, Kubernetes, et outils de monitoring
 * pour vérifier que l'application fonctionne correctement
 */

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'application' => 'BatiSaaS',
    'version' => '2.0.0',
    'checks' => []
];

// Vérification de la configuration
try {
    if (file_exists(__DIR__ . '/../config/database.php')) {
        require_once __DIR__ . '/../config/database.php';
        $health['checks']['config'] = 'OK';
    } else {
        $health['checks']['config'] = 'MISSING';
        $health['status'] = 'degraded';
    }
} catch (Exception $e) {
    $health['checks']['config'] = 'ERROR';
    $health['status'] = 'unhealthy';
}

// Vérification de la base de données
try {
    if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Test simple de requête
        $stmt = $pdo->query("SELECT 1");
        $health['checks']['database'] = 'OK';
        $health['database_name'] = DB_NAME;
    } else {
        $health['checks']['database'] = 'NOT_CONFIGURED';
        $health['status'] = 'degraded';
    }
} catch (PDOException $e) {
    $health['checks']['database'] = 'ERROR';
    $health['database_error'] = $e->getMessage();
    $health['status'] = 'unhealthy';
}

// Vérification des dossiers d'upload
$upload_dirs = [
    'photos' => __DIR__ . '/uploads/photos',
    'documents' => __DIR__ . '/uploads/documents',
    'thumbnails' => __DIR__ . '/uploads/thumbnails',
    'logos' => __DIR__ . '/uploads/logos'
];

$upload_status = [];
foreach ($upload_dirs as $name => $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        $upload_status[$name] = 'OK';
    } else {
        $upload_status[$name] = 'NOT_WRITABLE';
        $health['status'] = 'degraded';
    }
}
$health['checks']['upload_dirs'] = $upload_status;

// Vérification des dossiers de stockage
$storage_dirs = [
    'logs' => __DIR__ . '/../storage/logs',
    'cache' => __DIR__ . '/../storage/cache',
    'sessions' => __DIR__ . '/../storage/sessions'
];

$storage_status = [];
foreach ($storage_dirs as $name => $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        $storage_status[$name] = 'OK';
    } else {
        $storage_status[$name] = 'NOT_WRITABLE';
        $health['status'] = 'degraded';
    }
}
$health['checks']['storage_dirs'] = $storage_status;

// Vérification de PHP
$health['checks']['php_version'] = PHP_VERSION;
$health['checks']['php_extensions'] = [
    'pdo' => extension_loaded('pdo') ? 'OK' : 'MISSING',
    'pdo_mysql' => extension_loaded('pdo_mysql') ? 'OK' : 'MISSING',
    'mbstring' => extension_loaded('mbstring') ? 'OK' : 'MISSING',
    'json' => extension_loaded('json') ? 'OK' : 'MISSING',
    'gd' => extension_loaded('gd') ? 'OK' : 'MISSING',
    'zip' => extension_loaded('zip') ? 'OK' : 'MISSING'
];

// Vérifier si des extensions critiques manquent
foreach ($health['checks']['php_extensions'] as $ext => $status) {
    if ($status === 'MISSING' && in_array($ext, ['pdo', 'pdo_mysql', 'mbstring', 'json'])) {
        $health['status'] = 'unhealthy';
    }
}

// Vérification de l'espace disque
$disk_free = disk_free_space(__DIR__);
$disk_total = disk_total_space(__DIR__);
$disk_percent = round(($disk_free / $disk_total) * 100, 2);

$health['checks']['disk_space'] = [
    'free' => round($disk_free / 1024 / 1024 / 1024, 2) . ' GB',
    'total' => round($disk_total / 1024 / 1024 / 1024, 2) . ' GB',
    'free_percent' => $disk_percent . '%'
];

if ($disk_percent < 10) {
    $health['status'] = 'degraded';
    $health['warnings'][] = 'Espace disque faible (moins de 10%)';
}

// Vérification de la mémoire
$memory_limit = ini_get('memory_limit');
$memory_usage = round(memory_get_usage(true) / 1024 / 1024, 2);

$health['checks']['memory'] = [
    'limit' => $memory_limit,
    'usage' => $memory_usage . ' MB'
];

// Déterminer le code de statut HTTP
$http_code = 200;
if ($health['status'] === 'degraded') {
    $http_code = 200; // Toujours 200 mais signaler le problème
}
if ($health['status'] === 'unhealthy') {
    $http_code = 503; // Service Unavailable
}

http_response_code($http_code);
echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
