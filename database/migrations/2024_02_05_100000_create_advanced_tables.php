<?php
/**
 * Migration: Tables pour galerie photos, chat, stocks, timesheet, audit
 * Créée le: 2024_02_05_100000
 */

class MigrationCreateAdvancedTables {
    public function up($pdo) {
        // Table galerie photos
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS photos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                chantier_id INT NOT NULL,
                uploaded_by INT NOT NULL,
                title VARCHAR(255) NULL,
                description TEXT NULL,
                file_path VARCHAR(500) NOT NULL,
                thumbnail_path VARCHAR(500) NULL,
                file_size INT NOT NULL,
                width INT NULL,
                height INT NULL,
                category ENUM('avant', 'pendant', 'apres', 'defaut', 'autre') DEFAULT 'autre',
                zone VARCHAR(100) NULL,
                etage VARCHAR(50) NULL,
                latitude DECIMAL(10, 8) NULL,
                longitude DECIMAL(11, 8) NULL,
                annotations TEXT NULL COMMENT 'JSON des annotations',
                taken_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE CASCADE,
                FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_chantier (chantier_id),
                INDEX idx_category (category),
                INDEX idx_zone (zone)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Table messagerie/chat
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                chantier_id INT NULL,
                from_user_id INT NOT NULL,
                to_user_id INT NULL COMMENT 'NULL = message de groupe',
                message TEXT NOT NULL,
                attachments TEXT NULL COMMENT 'JSON des fichiers joints',
                is_read BOOLEAN DEFAULT FALSE,
                read_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE CASCADE,
                FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_chantier (chantier_id),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Table stocks/matériaux
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS stocks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                reference VARCHAR(100) NULL,
                description TEXT NULL,
                unit VARCHAR(50) NOT NULL COMMENT 'unité: pièce, m², kg, etc',
                quantity_current DECIMAL(10, 2) NOT NULL DEFAULT 0,
                quantity_alert DECIMAL(10, 2) NOT NULL DEFAULT 10,
                unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0,
                fournisseur_id INT NULL,
                category VARCHAR(100) NULL,
                location VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id) ON DELETE SET NULL,
                INDEX idx_quantity (quantity_current),
                INDEX idx_category (category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Table mouvements de stock
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS stock_movements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                stock_id INT NOT NULL,
                chantier_id INT NULL,
                type ENUM('entree', 'sortie', 'ajustement', 'retour') NOT NULL,
                quantity DECIMAL(10, 2) NOT NULL,
                unit_price DECIMAL(10, 2) NULL,
                notes TEXT NULL,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
                FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_stock (stock_id),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Table timesheet/pointage
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS timesheets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                user_id INT NOT NULL,
                chantier_id INT NULL,
                date DATE NOT NULL,
                clock_in DATETIME NOT NULL,
                clock_out DATETIME NULL,
                break_duration INT DEFAULT 0 COMMENT 'en minutes',
                total_hours DECIMAL(5, 2) NULL,
                latitude_in DECIMAL(10, 8) NULL,
                longitude_in DECIMAL(11, 8) NULL,
                latitude_out DECIMAL(10, 8) NULL,
                longitude_out DECIMAL(11, 8) NULL,
                notes TEXT NULL,
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                approved_by INT NULL,
                approved_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE SET NULL,
                FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_user_date (user_id, date),
                INDEX idx_chantier (chantier_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Table logs d'audit
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                user_id INT NULL,
                action VARCHAR(100) NOT NULL COMMENT 'create, update, delete, login, etc',
                entity_type VARCHAR(100) NOT NULL COMMENT 'chantier, devis, facture, etc',
                entity_id INT NULL,
                old_values TEXT NULL COMMENT 'JSON des anciennes valeurs',
                new_values TEXT NULL COMMENT 'JSON des nouvelles valeurs',
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_entity (entity_type, entity_id),
                INDEX idx_created (created_at),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Table carnet de bord
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS carnet_bord (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                chantier_id INT NOT NULL,
                date DATE NOT NULL,
                weather VARCHAR(50) NULL COMMENT 'ensoleillé, pluvieux, neige, etc',
                temperature INT NULL,
                present_workers TEXT NULL COMMENT 'JSON liste des présents',
                work_done TEXT NOT NULL,
                materials_used TEXT NULL COMMENT 'JSON des matériaux utilisés',
                incidents TEXT NULL,
                photos TEXT NULL COMMENT 'JSON des IDs de photos',
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                UNIQUE KEY unique_chantier_date (chantier_id, date),
                INDEX idx_date (date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Table punch lists (listes de réserves)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS punch_lists (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                chantier_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                category ENUM('defaut', 'finition', 'securite', 'conformite', 'autre') DEFAULT 'autre',
                priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                status ENUM('open', 'in_progress', 'resolved', 'verified', 'closed') DEFAULT 'open',
                zone VARCHAR(100) NULL,
                assigned_to INT NULL,
                photo_id INT NULL,
                due_date DATE NULL,
                resolved_at DATETIME NULL,
                resolved_by INT NULL,
                verified_at DATETIME NULL,
                verified_by INT NULL,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE CASCADE,
                FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_chantier (chantier_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        echo "  ✅ Tables avancées créées: photos, messages, stocks, timesheets, audit_logs, carnet_bord, punch_lists\n";
    }

    public function down($pdo) {
        $pdo->exec("DROP TABLE IF EXISTS punch_lists");
        $pdo->exec("DROP TABLE IF EXISTS carnet_bord");
        $pdo->exec("DROP TABLE IF EXISTS audit_logs");
        $pdo->exec("DROP TABLE IF EXISTS timesheets");
        $pdo->exec("DROP TABLE IF EXISTS stock_movements");
        $pdo->exec("DROP TABLE IF EXISTS stocks");
        $pdo->exec("DROP TABLE IF EXISTS messages");
        $pdo->exec("DROP TABLE IF EXISTS photos");
        echo "  ✅ Tables avancées supprimées\n";
    }
}
