<?php
/**
 * Migration: Créer table de gestion des documents
 * Créée le: 2024_02_01_100000
 */

class MigrationCreateDocumentsTable {
    /**
     * Exécute la migration
     */
    public function up($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS documents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                chantier_id INT NULL,
                client_id INT NULL,
                uploaded_by INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size INT NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                category ENUM('plan', 'photo', 'contrat', 'facture', 'devis', 'rapport', 'autre') DEFAULT 'autre',
                version INT DEFAULT 1,
                parent_id INT NULL COMMENT 'ID du document parent si version',
                description TEXT NULL,
                tags VARCHAR(500) NULL,
                is_latest BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE CASCADE,
                FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
                FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (parent_id) REFERENCES documents(id) ON DELETE CASCADE,
                INDEX idx_chantier (chantier_id),
                INDEX idx_client (client_id),
                INDEX idx_category (category),
                INDEX idx_latest (is_latest),
                FULLTEXT INDEX idx_search (name, description, tags)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        echo "  ✅ Table documents créée avec succès\n";
    }

    /**
     * Annule la migration
     */
    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS documents");
        echo "  ✅ Table documents supprimée\n";
    }
}
