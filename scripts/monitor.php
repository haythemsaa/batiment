<?php
/**
 * Script de monitoring système
 * Vérifie l'état de l'application et envoie des alertes
 *
 * Usage: php scripts/monitor.php
 * Cron: */5 * * * * php /path/to/scripts/monitor.php
 */

require_once __DIR__ . '/../config/database.php';

class Monitor {
    private $db;
    private $alerts = [];
    private $config = [
        'disk_threshold' => 90, // % utilisation disque
        'db_size_threshold' => 10 * 1024 * 1024 * 1024, // 10 GB
        'slow_query_threshold' => 2, // secondes
        'error_log_threshold' => 100, // nombre d'erreurs/jour
        'backup_age_threshold' => 24 * 3600, // 24 heures
    ];

    public function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            $this->addAlert('CRITICAL', 'Connexion BDD impossible: ' . $e->getMessage());
        }
    }

    /**
     * Exécute tous les checks
     */
    public function runAllChecks() {
        echo "=== MONITORING BATISAAS ===" . PHP_EOL;
        echo "Démarrage: " . date('Y-m-d H:i:s') . PHP_EOL . PHP_EOL;

        $this->checkDiskSpace();
        $this->checkDatabase();
        $this->checkBackups();
        $this->checkErrorLogs();
        $this->checkPerformance();
        $this->checkSecurity();
        $this->checkUploads();

        $this->displayResults();
        $this->sendAlertsIfNeeded();

        echo PHP_EOL . "Terminé: " . date('Y-m-d H:i:s') . PHP_EOL;
    }

    /**
     * Vérification espace disque
     */
    private function checkDiskSpace() {
        echo "✓ Vérification espace disque..." . PHP_EOL;

        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;
        $percentUsed = ($used / $total) * 100;

        echo sprintf("  Espace: %.2f GB / %.2f GB (%.1f%% utilisé)" . PHP_EOL,
            $used / (1024**3),
            $total / (1024**3),
            $percentUsed
        );

        if ($percentUsed >= $this->config['disk_threshold']) {
            $this->addAlert('WARNING', sprintf(
                'Espace disque critique: %.1f%% utilisé',
                $percentUsed
            ));
        }

        // Vérifier uploads
        $uploadsPath = __DIR__ . '/../public/uploads';
        if (is_dir($uploadsPath)) {
            $uploadsSize = $this->getDirectorySize($uploadsPath);
            echo sprintf("  Uploads: %.2f GB" . PHP_EOL, $uploadsSize / (1024**3));
        }
    }

    /**
     * Vérification base de données
     */
    private function checkDatabase() {
        echo "✓ Vérification base de données..." . PHP_EOL;

        if (!$this->db) {
            return;
        }

        try {
            // Taille de la BDD
            $stmt = $this->db->query("
                SELECT SUM(data_length + index_length) as size
                FROM information_schema.TABLES
                WHERE table_schema = '" . DB_NAME . "'
            ");
            $result = $stmt->fetch();
            $dbSize = $result['size'];

            echo sprintf("  Taille BDD: %.2f GB" . PHP_EOL, $dbSize / (1024**3));

            if ($dbSize >= $this->config['db_size_threshold']) {
                $this->addAlert('INFO', sprintf(
                    'Base de données volumineuse: %.2f GB',
                    $dbSize / (1024**3)
                ));
            }

            // Nombre d'enregistrements par table
            $tables = ['companies', 'users', 'chantiers', 'devis', 'factures',
                      'photos', 'timesheets', 'stocks', 'messages'];

            foreach ($tables as $table) {
                $stmt = $this->db->query("SELECT COUNT(*) as count FROM `$table`");
                $result = $stmt->fetch();
                echo sprintf("  %s: %d enregistrements" . PHP_EOL,
                    ucfirst($table),
                    $result['count']
                );
            }

            // Vérifier fragmentation
            $stmt = $this->db->query("
                SELECT table_name, data_free
                FROM information_schema.TABLES
                WHERE table_schema = '" . DB_NAME . "'
                AND data_free > 100 * 1024 * 1024
            ");
            $fragmented = $stmt->fetchAll();

            if (!empty($fragmented)) {
                $tableNames = array_column($fragmented, 'table_name');
                $this->addAlert('INFO', 'Tables fragmentées: ' . implode(', ', $tableNames));
                echo "  ⚠ Tables fragmentées détectées - Exécuter OPTIMIZE TABLE" . PHP_EOL;
            }

        } catch (PDOException $e) {
            $this->addAlert('ERROR', 'Erreur BDD: ' . $e->getMessage());
        }
    }

    /**
     * Vérification sauvegardes
     */
    private function checkBackups() {
        echo "✓ Vérification sauvegardes..." . PHP_EOL;

        $backupDir = __DIR__ . '/../storage/backups';

        if (!is_dir($backupDir)) {
            $this->addAlert('WARNING', 'Dossier backups introuvable');
            return;
        }

        $backups = glob($backupDir . '/*.sql');

        if (empty($backups)) {
            $this->addAlert('CRITICAL', 'Aucune sauvegarde trouvée');
            echo "  ❌ Aucune sauvegarde" . PHP_EOL;
            return;
        }

        // Dernière sauvegarde
        $latestBackup = max(array_map('filemtime', $backups));
        $age = time() - $latestBackup;

        echo sprintf("  Dernière sauvegarde: %s (il y a %s)" . PHP_EOL,
            date('Y-m-d H:i:s', $latestBackup),
            $this->formatDuration($age)
        );

        if ($age >= $this->config['backup_age_threshold']) {
            $this->addAlert('WARNING', sprintf(
                'Sauvegarde trop ancienne: %s',
                $this->formatDuration($age)
            ));
        }

        echo sprintf("  Total: %d sauvegardes" . PHP_EOL, count($backups));
    }

    /**
     * Vérification logs d'erreurs
     */
    private function checkErrorLogs() {
        echo "✓ Vérification logs d'erreurs..." . PHP_EOL;

        $logFile = __DIR__ . '/../storage/logs/error.log';

        if (!file_exists($logFile)) {
            echo "  Aucun log d'erreur" . PHP_EOL;
            return;
        }

        // Compter erreurs des dernières 24h
        $oneDayAgo = time() - 86400;
        $errors = 0;

        $handle = fopen($logFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                    $timestamp = strtotime($matches[1]);
                    if ($timestamp >= $oneDayAgo) {
                        $errors++;
                    }
                }
            }
            fclose($handle);
        }

        echo sprintf("  Erreurs (24h): %d" . PHP_EOL, $errors);

        if ($errors >= $this->config['error_log_threshold']) {
            $this->addAlert('WARNING', sprintf(
                'Trop d\'erreurs dans les logs: %d',
                $errors
            ));
        }

        // Taille du fichier log
        $logSize = filesize($logFile);
        echo sprintf("  Taille log: %.2f MB" . PHP_EOL, $logSize / (1024**2));

        if ($logSize > 100 * 1024 * 1024) { // 100 MB
            $this->addAlert('INFO', 'Fichier log volumineux, rotation recommandée');
        }
    }

    /**
     * Vérification performances
     */
    private function checkPerformance() {
        echo "✓ Vérification performances..." . PHP_EOL;

        if (!$this->db) {
            return;
        }

        try {
            // Requêtes lentes (si slow query log activé)
            $stmt = $this->db->query("SHOW VARIABLES LIKE 'slow_query_log'");
            $result = $stmt->fetch();

            if ($result && $result['Value'] === 'ON') {
                echo "  Slow query log: activé" . PHP_EOL;
            }

            // Connexions actives
            $stmt = $this->db->query("SHOW STATUS LIKE 'Threads_connected'");
            $result = $stmt->fetch();
            echo sprintf("  Connexions actives: %d" . PHP_EOL, $result['Value']);

            // Cache hit ratio
            $stmt = $this->db->query("SHOW STATUS LIKE 'Qcache_hits'");
            $hits = $stmt->fetch();
            $stmt = $this->db->query("SHOW STATUS LIKE 'Qcache_inserts'");
            $inserts = $stmt->fetch();

            if ($hits && $inserts && ($hits['Value'] + $inserts['Value']) > 0) {
                $hitRatio = $hits['Value'] / ($hits['Value'] + $inserts['Value']) * 100;
                echo sprintf("  Cache hit ratio: %.1f%%" . PHP_EOL, $hitRatio);

                if ($hitRatio < 80) {
                    $this->addAlert('INFO', sprintf(
                        'Cache hit ratio faible: %.1f%%',
                        $hitRatio
                    ));
                }
            }

        } catch (PDOException $e) {
            echo "  Erreur: " . $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * Vérification sécurité
     */
    private function checkSecurity() {
        echo "✓ Vérification sécurité..." . PHP_EOL;

        // Vérifier permissions fichiers sensibles
        $files = [
            __DIR__ . '/../config/database.php' => 0640,
            __DIR__ . '/../storage' => 0750,
            __DIR__ . '/../public/uploads' => 0755
        ];

        foreach ($files as $file => $expectedPerms) {
            if (file_exists($file)) {
                $perms = fileperms($file) & 0777;
                if ($perms != $expectedPerms) {
                    $this->addAlert('WARNING', sprintf(
                        'Permissions incorrectes: %s (attendu: %o, actuel: %o)',
                        basename($file),
                        $expectedPerms,
                        $perms
                    ));
                }
            }
        }

        // Vérifier version PHP
        $phpVersion = phpversion();
        echo "  Version PHP: $phpVersion" . PHP_EOL;

        if (version_compare($phpVersion, '8.0.0', '<')) {
            $this->addAlert('CRITICAL', 'Version PHP obsolète: ' . $phpVersion);
        }

        // Vérifier extensions critiques
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'gd'];
        $missingExtensions = [];

        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        if (!empty($missingExtensions)) {
            $this->addAlert('CRITICAL', 'Extensions manquantes: ' . implode(', ', $missingExtensions));
        } else {
            echo "  Extensions: OK" . PHP_EOL;
        }
    }

    /**
     * Vérification uploads
     */
    private function checkUploads() {
        echo "✓ Vérification uploads..." . PHP_EOL;

        $uploadDirs = [
            'photos' => __DIR__ . '/../public/uploads/photos',
            'documents' => __DIR__ . '/../public/uploads/documents',
            'carnet_bord' => __DIR__ . '/../public/uploads/carnet_bord',
            'punch_lists' => __DIR__ . '/../public/uploads/punch_lists',
        ];

        foreach ($uploadDirs as $name => $dir) {
            if (!is_dir($dir)) {
                $this->addAlert('WARNING', "Dossier $name manquant");
                continue;
            }

            if (!is_writable($dir)) {
                $this->addAlert('ERROR', "Dossier $name non accessible en écriture");
                continue;
            }

            $fileCount = count(glob($dir . '/*'));
            $dirSize = $this->getDirectorySize($dir);

            echo sprintf("  %s: %d fichiers (%.2f MB)" . PHP_EOL,
                ucfirst($name),
                $fileCount,
                $dirSize / (1024**2)
            );
        }
    }

    /**
     * Ajouter une alerte
     */
    private function addAlert($level, $message) {
        $this->alerts[] = [
            'level' => $level,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Afficher résultats
     */
    private function displayResults() {
        echo PHP_EOL . "=== RÉSULTATS ===" . PHP_EOL;

        if (empty($this->alerts)) {
            echo "✅ Tout est OK!" . PHP_EOL;
            return;
        }

        $colors = [
            'CRITICAL' => "\033[1;31m", // Rouge gras
            'ERROR' => "\033[0;31m",    // Rouge
            'WARNING' => "\033[0;33m",  // Jaune
            'INFO' => "\033[0;36m",     // Cyan
        ];
        $reset = "\033[0m";

        foreach ($this->alerts as $alert) {
            $color = $colors[$alert['level']] ?? '';
            echo sprintf("%s[%s] %s%s" . PHP_EOL,
                $color,
                $alert['level'],
                $alert['message'],
                $reset
            );
        }
    }

    /**
     * Envoyer alertes par email si nécessaire
     */
    private function sendAlertsIfNeeded() {
        $criticalAlerts = array_filter($this->alerts, function($alert) {
            return $alert['level'] === 'CRITICAL' || $alert['level'] === 'ERROR';
        });

        if (empty($criticalAlerts)) {
            return;
        }

        // TODO: Implémenter envoi email
        // Pour l'instant, écrire dans un fichier
        $alertFile = __DIR__ . '/../storage/logs/alerts.log';
        $content = date('Y-m-d H:i:s') . PHP_EOL;

        foreach ($criticalAlerts as $alert) {
            $content .= sprintf("[%s] %s" . PHP_EOL,
                $alert['level'],
                $alert['message']
            );
        }
        $content .= PHP_EOL;

        file_put_contents($alertFile, $content, FILE_APPEND);
    }

    /**
     * Calculer taille répertoire
     */
    private function getDirectorySize($dir) {
        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Formater durée
     */
    private function formatDuration($seconds) {
        if ($seconds < 60) {
            return sprintf('%d secondes', $seconds);
        } elseif ($seconds < 3600) {
            return sprintf('%d minutes', $seconds / 60);
        } elseif ($seconds < 86400) {
            return sprintf('%.1f heures', $seconds / 3600);
        } else {
            return sprintf('%.1f jours', $seconds / 86400);
        }
    }
}

// Exécution
$monitor = new Monitor();
$monitor->runAllChecks();
