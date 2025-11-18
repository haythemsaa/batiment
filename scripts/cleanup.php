#!/usr/bin/env php
<?php
/**
 * Script de nettoyage
 * Usage: php scripts/cleanup.php
 *
 * Nettoie les fichiers temporaires, logs anciens, sessions expirées
 */

echo "🧹 Démarrage du nettoyage...\n\n";

$totalCleaned = 0;
$totalFiles = 0;

// 1. Nettoyage des logs anciens (> 30 jours)
echo "📋 Nettoyage des logs anciens...\n";

$logsDir = __DIR__ . '/../logs';
if (is_dir($logsDir)) {
    $logFiles = glob($logsDir . '/*.log');
    $cutoffTime = time() - (30 * 24 * 60 * 60); // 30 jours

    foreach ($logFiles as $file) {
        if (filemtime($file) < $cutoffTime) {
            $size = filesize($file);
            unlink($file);
            $totalCleaned += $size;
            $totalFiles++;
            echo "  🗑️  Supprimé: " . basename($file) . "\n";
        }
    }
}

echo "  ✅ " . $totalFiles . " fichier(s) supprimé(s)\n\n";

// 2. Nettoyage du cache
echo "🗄️  Nettoyage du cache...\n";

$cacheDir = __DIR__ . '/../cache';
$cacheFiles = 0;
$cacheSize = 0;

if (is_dir($cacheDir)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() !== 'gitkeep') {
            $cacheSize += $file->getSize();
            unlink($file->getRealPath());
            $cacheFiles++;
        }
    }

    $totalCleaned += $cacheSize;
    $totalFiles += $cacheFiles;
}

echo "  ✅ " . $cacheFiles . " fichier(s) de cache supprimé(s)\n\n";

// 3. Nettoyage des fichiers temporaires
echo "📁 Nettoyage des fichiers temporaires...\n";

$tempDir = __DIR__ . '/../temp';
$tempFiles = 0;
$tempSize = 0;

if (is_dir($tempDir)) {
    $files = glob($tempDir . '/*');

    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== '.gitkeep') {
            $tempSize += filesize($file);
            unlink($file);
            $tempFiles++;
            echo "  🗑️  Supprimé: " . basename($file) . "\n";
        }
    }

    $totalCleaned += $tempSize;
    $totalFiles += $tempFiles;
}

echo "  ✅ " . $tempFiles . " fichier(s) temporaire(s) supprimé(s)\n\n";

// 4. Nettoyage des sessions expirées
echo "🔐 Nettoyage des sessions expirées...\n";

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Supprimer les sessions de plus de 7 jours
    $stmt = $pdo->prepare("
        DELETE FROM sessions
        WHERE last_activity < DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
    $sessionsCleaned = $stmt->rowCount();

    echo "  ✅ " . $sessionsCleaned . " session(s) expirée(s) supprimée(s)\n\n";

} catch (PDOException $e) {
    echo "  ⚠️  Impossible de nettoyer les sessions: " . $e->getMessage() . "\n\n";
}

// 5. Optimisation de la base de données
echo "⚡ Optimisation des tables de la base de données...\n";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $pdo->exec("OPTIMIZE TABLE `$table`");
        echo "  ✅ Table optimisée: $table\n";
    }

    echo "\n";

} catch (PDOException $e) {
    echo "  ⚠️  Erreur lors de l'optimisation: " . $e->getMessage() . "\n\n";
}

// Résumé
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✨ Nettoyage terminé!\n";
echo "📊 Résumé:\n";
echo "  • Fichiers supprimés: $totalFiles\n";
echo "  • Espace libéré: " . formatBytes($totalCleaned) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
