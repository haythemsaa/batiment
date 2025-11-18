<?php
/**
 * Script d'optimisation de l'application
 * Nettoie, optimise et améliore les performances
 *
 * Usage: php scripts/optimize.php [--force]
 */

require_once __DIR__ . '/../config/database.php';

class Optimizer {
    private $db;
    private $force = false;
    private $stats = [
        'tables_optimized' => 0,
        'cache_cleared' => 0,
        'logs_cleaned' => 0,
        'old_files_deleted' => 0,
        'space_freed' => 0
    ];

    public function __construct($force = false) {
        $this->force = $force;

        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            die("Erreur connexion BDD: " . $e->getMessage() . PHP_EOL);
        }
    }

    /**
     * Exécute toutes les optimisations
     */
    public function runAll() {
        echo "=== OPTIMISATION BATISAAS ===" . PHP_EOL;
        echo "Démarrage: " . date('Y-m-d H:i:s') . PHP_EOL . PHP_EOL;

        if (!$this->force) {
            echo "Mode: Standard" . PHP_EOL;
            echo "Utilisez --force pour les optimisations agressives" . PHP_EOL . PHP_EOL;
        } else {
            echo "⚠️  Mode: Force (optimisations agressives)" . PHP_EOL . PHP_EOL;
        }

        $this->optimizeDatabase();
        $this->cleanCache();
        $this->cleanLogs();
        $this->cleanOldFiles();
        $this->optimizeUploads();
        $this->rebuildIndexes();

        $this->displayResults();

        echo PHP_EOL . "Terminé: " . date('Y-m-d H:i:s') . PHP_EOL;
    }

    /**
     * Optimisation base de données
     */
    private function optimizeDatabase() {
        echo "► Optimisation base de données..." . PHP_EOL;

        try {
            // Récupérer toutes les tables
            $stmt = $this->db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            echo sprintf("  Traitement de %d tables..." . PHP_EOL, count($tables));

            foreach ($tables as $table) {
                // Analyser la fragmentation
                $stmt = $this->db->query("
                    SELECT data_free
                    FROM information_schema.TABLES
                    WHERE table_schema = '" . DB_NAME . "'
                    AND table_name = '$table'
                ");
                $result = $stmt->fetch();
                $dataFree = $result['data_free'] ?? 0;

                // Optimiser si fragmentée
                if ($dataFree > 1024 * 1024 || $this->force) { // 1 MB
                    echo sprintf("  - %s (%.2f MB fragmentés)...",
                        $table,
                        $dataFree / (1024**2)
                    );

                    $this->db->exec("OPTIMIZE TABLE `$table`");
                    $this->stats['tables_optimized']++;
                    $this->stats['space_freed'] += $dataFree;

                    echo " ✓" . PHP_EOL;
                }
            }

            // Analyser toutes les tables
            echo "  Analyse des tables..." . PHP_EOL;
            foreach ($tables as $table) {
                $this->db->exec("ANALYZE TABLE `$table`");
            }

            echo "  ✓ Optimisation terminée" . PHP_EOL;

        } catch (PDOException $e) {
            echo "  ✗ Erreur: " . $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * Nettoyage du cache
     */
    private function cleanCache() {
        echo "► Nettoyage du cache..." . PHP_EOL;

        $cacheDir = __DIR__ . '/../storage/cache';

        if (!is_dir($cacheDir)) {
            echo "  Dossier cache inexistant" . PHP_EOL;
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $deleted = 0;
        $sizeFreed = 0;

        foreach ($files as $file) {
            if ($file->isFile()) {
                $sizeFreed += $file->getSize();
                unlink($file->getRealPath());
                $deleted++;
            } elseif ($file->isDir()) {
                rmdir($file->getRealPath());
            }
        }

        $this->stats['cache_cleared'] = $deleted;
        $this->stats['space_freed'] += $sizeFreed;

        echo sprintf("  ✓ %d fichiers supprimés (%.2f MB)" . PHP_EOL,
            $deleted,
            $sizeFreed / (1024**2)
        );
    }

    /**
     * Nettoyage des logs
     */
    private function cleanLogs() {
        echo "► Nettoyage des logs..." . PHP_EOL;

        $logsDir = __DIR__ . '/../storage/logs';
        $retentionDays = $this->force ? 30 : 90;
        $cutoffDate = time() - ($retentionDays * 86400);

        if (!is_dir($logsDir)) {
            echo "  Dossier logs inexistant" . PHP_EOL;
            return;
        }

        $deleted = 0;
        $sizeFreed = 0;

        $files = glob($logsDir . '/*.log');
        foreach ($files as $file) {
            $mtime = filemtime($file);

            // Supprimer les vieux logs ou compresser les gros fichiers
            if ($mtime < $cutoffDate) {
                $size = filesize($file);
                unlink($file);
                $deleted++;
                $sizeFreed += $size;
            } elseif (filesize($file) > 50 * 1024 * 1024 && $this->force) { // 50 MB
                // Rotation: garder seulement les 1000 dernières lignes
                $this->rotateLogFile($file);
            }
        }

        // Nettoyer les audit logs RGPD (365 jours)
        try {
            $stmt = $this->db->prepare("
                DELETE FROM audit_logs
                WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY)
            ");
            $stmt->execute();
            $auditDeleted = $stmt->rowCount();

            if ($auditDeleted > 0) {
                echo sprintf("  ✓ %d logs d'audit nettoyés (RGPD)" . PHP_EOL, $auditDeleted);
            }
        } catch (PDOException $e) {
            echo "  ✗ Erreur nettoyage audit: " . $e->getMessage() . PHP_EOL;
        }

        $this->stats['logs_cleaned'] = $deleted;
        $this->stats['space_freed'] += $sizeFreed;

        echo sprintf("  ✓ %d fichiers log supprimés (%.2f MB)" . PHP_EOL,
            $deleted,
            $sizeFreed / (1024**2)
        );
    }

    /**
     * Nettoyage des vieux fichiers
     */
    private function cleanOldFiles() {
        echo "► Nettoyage des fichiers temporaires..." . PHP_EOL;

        $directories = [
            __DIR__ . '/../storage/temp' => 7,  // 7 jours
            __DIR__ . '/../storage/exports' => 30, // 30 jours
        ];

        $totalDeleted = 0;
        $totalFreed = 0;

        foreach ($directories as $dir => $retentionDays) {
            if (!is_dir($dir)) {
                continue;
            }

            $cutoffDate = time() - ($retentionDays * 86400);
            $deleted = 0;
            $sizeFreed = 0;

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if ($file->isFile() && $file->getMTime() < $cutoffDate) {
                    $sizeFreed += $file->getSize();
                    unlink($file->getRealPath());
                    $deleted++;
                }
            }

            if ($deleted > 0) {
                echo sprintf("  - %s: %d fichiers (%.2f MB)" . PHP_EOL,
                    basename($dir),
                    $deleted,
                    $sizeFreed / (1024**2)
                );
            }

            $totalDeleted += $deleted;
            $totalFreed += $sizeFreed;
        }

        $this->stats['old_files_deleted'] = $totalDeleted;
        $this->stats['space_freed'] += $totalFreed;

        if ($totalDeleted > 0) {
            echo sprintf("  ✓ Total: %d fichiers supprimés (%.2f MB)" . PHP_EOL,
                $totalDeleted,
                $totalFreed / (1024**2)
            );
        } else {
            echo "  ✓ Aucun fichier à nettoyer" . PHP_EOL;
        }
    }

    /**
     * Optimisation des uploads
     */
    private function optimizeUploads() {
        echo "► Optimisation des uploads..." . PHP_EOL;

        // Vérifier et recréer miniatures manquantes
        $photosDir = __DIR__ . '/../public/uploads/photos';

        if (!is_dir($photosDir)) {
            echo "  Dossier photos inexistant" . PHP_EOL;
            return;
        }

        $photos = glob($photosDir . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
        $thumbnailsCreated = 0;

        foreach ($photos as $photo) {
            $thumbPath = str_replace(basename($photo), 'thumb_' . basename($photo), $photo);

            if (!file_exists($thumbPath)) {
                if ($this->createThumbnail($photo, $thumbPath, 300, 300)) {
                    $thumbnailsCreated++;
                }
            }
        }

        if ($thumbnailsCreated > 0) {
            echo sprintf("  ✓ %d miniatures créées" . PHP_EOL, $thumbnailsCreated);
        } else {
            echo "  ✓ Toutes les miniatures existent" . PHP_EOL;
        }

        // Nettoyer les orphelins (fichiers sans enregistrement BDD)
        if ($this->force) {
            echo "  Recherche de fichiers orphelins..." . PHP_EOL;
            // TODO: Implémenter nettoyage des orphelins
        }
    }

    /**
     * Reconstruire les index
     */
    private function rebuildIndexes() {
        if (!$this->force) {
            return;
        }

        echo "► Reconstruction des index..." . PHP_EOL;

        try {
            // Lister toutes les tables avec index
            $stmt = $this->db->query("
                SELECT DISTINCT table_name
                FROM information_schema.STATISTICS
                WHERE table_schema = '" . DB_NAME . "'
            ");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                echo "  - $table...";
                $this->db->exec("ALTER TABLE `$table` ENGINE=InnoDB");
                echo " ✓" . PHP_EOL;
            }

            echo "  ✓ Index reconstruits" . PHP_EOL;

        } catch (PDOException $e) {
            echo "  ✗ Erreur: " . $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * Rotation d'un fichier log
     */
    private function rotateLogFile($file) {
        $lines = file($file);
        $keep = array_slice($lines, -1000);
        file_put_contents($file, implode('', $keep));

        echo sprintf("  ✓ Rotation: %s" . PHP_EOL, basename($file));
    }

    /**
     * Créer une miniature
     */
    private function createThumbnail($source, $dest, $maxWidth, $maxHeight) {
        $imageInfo = getimagesize($source);
        if (!$imageInfo) {
            return false;
        }

        list($width, $height, $type) = $imageInfo;

        // Calculer nouvelles dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);

        // Créer image source
        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($source);
                break;
            default:
                return false;
        }

        // Créer miniature
        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        // Préserver transparence pour PNG
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }

        imagecopyresampled($thumb, $src, 0, 0, 0, 0,
            $newWidth, $newHeight, $width, $height);

        // Sauvegarder
        $success = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $success = imagejpeg($thumb, $dest, 85);
                break;
            case IMAGETYPE_PNG:
                $success = imagepng($thumb, $dest, 8);
                break;
        }

        imagedestroy($src);
        imagedestroy($thumb);

        return $success;
    }

    /**
     * Afficher résultats
     */
    private function displayResults() {
        echo PHP_EOL . "=== RÉSULTATS ===" . PHP_EOL;
        echo sprintf("Tables optimisées: %d" . PHP_EOL, $this->stats['tables_optimized']);
        echo sprintf("Fichiers cache supprimés: %d" . PHP_EOL, $this->stats['cache_cleared']);
        echo sprintf("Fichiers log nettoyés: %d" . PHP_EOL, $this->stats['logs_cleaned']);
        echo sprintf("Fichiers temporaires supprimés: %d" . PHP_EOL, $this->stats['old_files_deleted']);
        echo sprintf("Espace libéré: %.2f MB" . PHP_EOL, $this->stats['space_freed'] / (1024**2));
    }
}

// Parsing arguments
$force = in_array('--force', $argv);

// Exécution
$optimizer = new Optimizer($force);
$optimizer->runAll();
