<?php
/**
 * Migration: Créer table de notifications
 * Créée le: 2024_01_20_140000
 */

class MigrationAddNotificationsTable {
    /**
     * Exécute la migration
     */
    public function up($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                company_id INT NOT NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                link VARCHAR(255) NULL,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                read_at DATETIME NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                INDEX idx_user_read (user_id, is_read),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        echo "  ✅ Table notifications créée avec succès\n";
    }

    /**
     * Annule la migration
     */
    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS notifications");
        echo "  ✅ Table notifications supprimée\n";
    }
}
