<?php

namespace App\Models;

use App\Core\Model;

class Stock extends Model {
    protected $table = 'stocks';

    /**
     * Récupère les stocks avec alertes
     */
    public function getWithAlerts($companyId) {
        $stmt = $this->db->prepare("
            SELECT s.*, f.name as fournisseur_name,
                   CASE WHEN s.quantity_current <= s.quantity_alert THEN 1 ELSE 0 END as is_low
            FROM {$this->table} s
            LEFT JOIN fournisseurs f ON s.fournisseur_id = f.id
            WHERE s.company_id = ?
            ORDER BY is_low DESC, s.name ASC
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    /**
     * Enregistre un mouvement de stock
     */
    public function addMovement($stockId, $type, $quantity, $data) {
        // Récupérer le stock actuel
        $stock = $this->find($stockId);
        if (!$stock) return false;

        // Calculer nouvelle quantité
        $newQuantity = $stock['quantity_current'];
        switch ($type) {
            case 'entree':
            case 'retour':
                $newQuantity += $quantity;
                break;
            case 'sortie':
                $newQuantity -= $quantity;
                break;
            case 'ajustement':
                $newQuantity = $quantity;
                break;
        }

        // Enregistrer le mouvement
        $stmt = $this->db->prepare("
            INSERT INTO stock_movements (
                company_id, stock_id, chantier_id, type, quantity,
                unit_price, notes, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $stock['company_id'],
            $stockId,
            $data['chantier_id'] ?? null,
            $type,
            $quantity,
            $data['unit_price'] ?? null,
            $data['notes'] ?? null,
            $data['created_by']
        ]);

        // Mettre à jour le stock
        $this->update($stockId, ['quantity_current' => $newQuantity]);

        return true;
    }

    /**
     * Récupère l'historique des mouvements
     */
    public function getMovements($stockId, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT sm.*, u.name as created_by_name, c.name as chantier_name
            FROM stock_movements sm
            LEFT JOIN users u ON sm.created_by = u.id
            LEFT JOIN chantiers c ON sm.chantier_id = c.id
            WHERE sm.stock_id = ?
            ORDER BY sm.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $stockId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Valorisation du stock
     */
    public function getValorisation($companyId) {
        $stmt = $this->db->prepare("
            SELECT
                SUM(quantity_current * unit_price) as valeur_totale,
                COUNT(*) as total_articles,
                SUM(CASE WHEN quantity_current <= quantity_alert THEN 1 ELSE 0 END) as articles_alerte
            FROM {$this->table}
            WHERE company_id = ?
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetch();
    }
}
