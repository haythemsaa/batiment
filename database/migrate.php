#!/usr/bin/env php
<?php
/**
 * Système de migrations de base de données
 * Usage: php database/migrate.php [up|down|status|create]
 *
 * Exemples:
 *   php database/migrate.php up              - Exécute toutes les migrations en attente
 *   php database/migrate.php down            - Rollback la dernière migration
 *   php database/migrate.php status          - Affiche l'état des migrations
 *   php database/migrate.php create nom      - Crée une nouvelle migration
 */

require_once __DIR__ . '/../config/database.php';

class MigrationRunner {
    private $pdo;
    private $migrationsDir;

    public function __construct() {
        $this->pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->migrationsDir = __DIR__ . '/migrations';

        $this->createMigrationsTable();
    }

    /**
     * Crée la table de suivi des migrations
     */
    private function createMigrationsTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_migration (migration)
            )
        ");
    }

    /**
     * Récupère toutes les migrations disponibles
     */
    private function getAllMigrations() {
        $files = glob($this->migrationsDir . '/*.php');
        $migrations = [];

        foreach ($files as $file) {
            $migrations[] = basename($file, '.php');
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Récupère les migrations déjà exécutées
     */
    private function getExecutedMigrations() {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère les migrations en attente
     */
    private function getPendingMigrations() {
        $all = $this->getAllMigrations();
        $executed = $this->getExecutedMigrations();
        return array_diff($all, $executed);
    }

    /**
     * Exécute les migrations en attente
     */
    public function up() {
        $pending = $this->getPendingMigrations();

        if (empty($pending)) {
            echo "✅ Aucune migration en attente\n";
            return;
        }

        $batch = $this->getNextBatchNumber();

        echo "🔄 Exécution des migrations...\n\n";

        foreach ($pending as $migration) {
            echo "⏳ Migration: $migration\n";

            try {
                require_once $this->migrationsDir . '/' . $migration . '.php';

                $className = $this->getClassName($migration);
                $instance = new $className();
                $instance->up($this->pdo);

                // Enregistrer la migration
                $stmt = $this->pdo->prepare("
                    INSERT INTO migrations (migration, batch) VALUES (?, ?)
                ");
                $stmt->execute([$migration, $batch]);

                echo "✅ Migration réussie: $migration\n\n";

            } catch (Exception $e) {
                echo "❌ Erreur lors de la migration: " . $e->getMessage() . "\n";
                return;
            }
        }

        echo "✨ Toutes les migrations ont été exécutées avec succès!\n";
    }

    /**
     * Rollback de la dernière migration
     */
    public function down() {
        $stmt = $this->pdo->query("
            SELECT migration, batch FROM migrations
            ORDER BY batch DESC, id DESC
            LIMIT 1
        ");

        $lastMigration = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lastMigration) {
            echo "ℹ️  Aucune migration à annuler\n";
            return;
        }

        $migration = $lastMigration['migration'];
        echo "🔄 Rollback: $migration\n";

        try {
            require_once $this->migrationsDir . '/' . $migration . '.php';

            $className = $this->getClassName($migration);
            $instance = new $className();
            $instance->down($this->pdo);

            // Supprimer de la table migrations
            $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = ?");
            $stmt->execute([$migration]);

            echo "✅ Rollback réussi: $migration\n";

        } catch (Exception $e) {
            echo "❌ Erreur lors du rollback: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Affiche l'état des migrations
     */
    public function status() {
        $all = $this->getAllMigrations();
        $executed = $this->getExecutedMigrations();

        echo "📊 État des migrations:\n\n";

        if (empty($all)) {
            echo "ℹ️  Aucune migration trouvée\n";
            return;
        }

        foreach ($all as $migration) {
            $status = in_array($migration, $executed) ? '✅ Exécutée' : '⏳ En attente';
            echo "$status - $migration\n";
        }

        echo "\n";
        echo "Total: " . count($all) . " migration(s)\n";
        echo "Exécutées: " . count($executed) . " migration(s)\n";
        echo "En attente: " . count($this->getPendingMigrations()) . " migration(s)\n";
    }

    /**
     * Crée un nouveau fichier de migration
     */
    public function create($name) {
        $timestamp = date('Y_m_d_His');
        $filename = $timestamp . '_' . $name . '.php';
        $filepath = $this->migrationsDir . '/' . $filename;

        $className = $this->getClassName($timestamp . '_' . $name);

        $template = <<<PHP
<?php
/**
 * Migration: $name
 * Créée le: {$timestamp}
 */

class $className {
    /**
     * Exécute la migration
     */
    public function up(\$pdo) {
        \$pdo->exec("
            -- Ajoutez ici vos requêtes SQL
            -- Exemple:
            -- ALTER TABLE chantiers ADD COLUMN new_field VARCHAR(255);
        ");
    }

    /**
     * Annule la migration
     */
    public function down(\$pdo) {
        \$pdo->exec("
            -- Ajoutez ici les requêtes pour annuler la migration
            -- Exemple:
            -- ALTER TABLE chantiers DROP COLUMN new_field;
        ");
    }
}

PHP;

        file_put_contents($filepath, $template);
        echo "✅ Migration créée: $filename\n";
        echo "📝 Éditez le fichier: $filepath\n";
    }

    /**
     * Récupère le numéro du prochain batch
     */
    private function getNextBatchNumber() {
        $stmt = $this->pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['max_batch'] ?? 0) + 1;
    }

    /**
     * Convertit un nom de fichier en nom de classe
     */
    private function getClassName($migration) {
        // Enlever le timestamp
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migration);
        // Convertir en CamelCase
        $parts = explode('_', $name);
        $className = 'Migration';
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }
        return $className;
    }
}

// Traitement de la commande
$command = $argv[1] ?? 'status';
$arg = $argv[2] ?? null;

try {
    $runner = new MigrationRunner();

    switch ($command) {
        case 'up':
            $runner->up();
            break;

        case 'down':
            $runner->down();
            break;

        case 'status':
            $runner->status();
            break;

        case 'create':
            if (!$arg) {
                echo "❌ Veuillez spécifier un nom pour la migration\n";
                echo "Usage: php database/migrate.php create nom_de_la_migration\n";
                exit(1);
            }
            $runner->create($arg);
            break;

        default:
            echo "❌ Commande inconnue: $command\n\n";
            echo "Commandes disponibles:\n";
            echo "  up      - Exécute toutes les migrations en attente\n";
            echo "  down    - Rollback la dernière migration\n";
            echo "  status  - Affiche l'état des migrations\n";
            echo "  create  - Crée une nouvelle migration\n";
            exit(1);
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
