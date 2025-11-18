#!/usr/bin/env php
<?php
/**
 * Script de sauvegarde automatique
 * Usage: php scripts/backup.php
 *
 * Sauvegarde la base de données et les fichiers importants
 */

require_once __DIR__ . '/../config/database.php';

// Configuration
$backupDir = __DIR__ . '/../backups';
$timestamp = date('Y-m-d_H-i-s');

// Créer le dossier de sauvegarde s'il n'existe pas
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

echo "🔄 Démarrage de la sauvegarde...\n";

// 1. Sauvegarde de la base de données
echo "📦 Sauvegarde de la base de données...\n";

$backupFile = $backupDir . '/db_backup_' . $timestamp . '.sql';
$command = sprintf(
    'mysqldump -h%s -u%s %s %s > %s',
    DB_HOST,
    DB_USER,
    DB_PASS ? '-p' . escapeshellarg(DB_PASS) : '',
    DB_NAME,
    escapeshellarg($backupFile)
);

exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Base de données sauvegardée: " . basename($backupFile) . "\n";

    // Compresser la sauvegarde
    exec("gzip " . escapeshellarg($backupFile));
    echo "✅ Fichier compressé: " . basename($backupFile) . ".gz\n";
} else {
    echo "❌ Erreur lors de la sauvegarde de la base de données\n";
    exit(1);
}

// 2. Sauvegarde des fichiers uploadés
echo "📦 Sauvegarde des fichiers uploadés...\n";

$uploadsDir = __DIR__ . '/../public/uploads';
$uploadsBackup = $backupDir . '/uploads_backup_' . $timestamp . '.tar.gz';

if (is_dir($uploadsDir)) {
    exec("tar -czf " . escapeshellarg($uploadsBackup) . " -C " . escapeshellarg($uploadsDir) . " .");
    echo "✅ Fichiers uploadés sauvegardés: " . basename($uploadsBackup) . "\n";
} else {
    echo "⚠️  Dossier uploads non trouvé\n";
}

// 3. Nettoyage des anciennes sauvegardes (garder les 7 dernières)
echo "🧹 Nettoyage des anciennes sauvegardes...\n";

$backupFiles = glob($backupDir . '/*');
usort($backupFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$filesToDelete = array_slice($backupFiles, 14); // Garder 7 sauvegardes DB + 7 uploads
foreach ($filesToDelete as $file) {
    unlink($file);
    echo "🗑️  Supprimé: " . basename($file) . "\n";
}

echo "\n✨ Sauvegarde terminée avec succès!\n";
echo "📁 Emplacement: $backupDir\n";

// Calculer la taille totale des sauvegardes
$totalSize = 0;
foreach (glob($backupDir . '/*') as $file) {
    $totalSize += filesize($file);
}

echo "💾 Taille totale: " . formatBytes($totalSize) . "\n";

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
