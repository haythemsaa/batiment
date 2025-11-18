<?php
/**
 * Migration: Créer table de gestion des tâches
 * Créée le: 2024_01_25_100000
 */

class MigrationCreateTasksTable {
    /**
     * Exécute la migration
     */
    public function up($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tasks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                chantier_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT NULL,
                assigned_to INT NULL,
                priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
                status ENUM('todo', 'in_progress', 'completed', 'cancelled') DEFAULT 'todo',
                due_date DATE NULL,
                completed_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE CASCADE,
                FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_chantier (chantier_id),
                INDEX idx_status (status),
                INDEX idx_assigned (assigned_to),
                INDEX idx_due_date (due_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        echo "  ✅ Table tasks créée avec succès\n";
    }

    /**
     * Annule la migration
     */
    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS tasks");
        echo "  ✅ Table tasks supprimée\n";
    }
}
