<?php
/**
 * Migration: Ajouter support de signature électronique aux documents
 * Créée le: 2024_01_15_120000
 */

class MigrationAddSignatureToDocuments {
    /**
     * Exécute la migration
     */
    public function up($pdo) {
        $pdo->exec("
            ALTER TABLE devis
            ADD COLUMN signature_client TEXT NULL AFTER notes,
            ADD COLUMN signature_date DATETIME NULL AFTER signature_client
        ");

        $pdo->exec("
            ALTER TABLE factures
            ADD COLUMN signature_client TEXT NULL AFTER notes,
            ADD COLUMN signature_date DATETIME NULL AFTER signature_client
        ");

        echo "  ✅ Colonnes de signature ajoutées aux tables devis et factures\n";
    }

    /**
     * Annule la migration
     */
    public function down($pdo) {
        $pdo->exec("
            ALTER TABLE devis
            DROP COLUMN signature_client,
            DROP COLUMN signature_date
        ");

        $pdo->exec("
            ALTER TABLE factures
            DROP COLUMN signature_client,
            DROP COLUMN signature_date
        ");

        echo "  ✅ Colonnes de signature supprimées\n";
    }
}
