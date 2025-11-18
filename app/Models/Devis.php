<?php
/**
 * Modèle Devis
 */
class Devis extends Model
{
    protected $table = 'devis';

    /**
     * Génère un numéro de devis unique
     */
    public function generateNumber($companyId)
    {
        $year = date('Y');
        $prefix = "DEV-{$year}-";

        $lastDevis = $this->db->queryOne(
            "SELECT number FROM devis WHERE company_id = ? AND number LIKE ? ORDER BY id DESC LIMIT 1",
            [$companyId, $prefix . '%']
        );

        if ($lastDevis) {
            $lastNumber = (int) str_replace($prefix, '', $lastDevis['number']);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Récupère un devis avec ses lignes et son client
     */
    public function getWithDetails($id, $companyId)
    {
        $devis = $this->find($id, $companyId);

        if (!$devis) {
            return null;
        }

        // Récupère le client
        $devis['client'] = $this->db->queryOne(
            "SELECT * FROM clients WHERE id = ?",
            [$devis['client_id']]
        );

        // Récupère les lignes
        $devis['items'] = $this->db->query(
            "SELECT * FROM devis_items WHERE devis_id = ? ORDER BY position",
            [$id]
        );

        return $devis;
    }

    /**
     * Crée un devis avec ses lignes
     */
    public function createWithItems($devisData, $items, $companyId)
    {
        $this->db->beginTransaction();

        try {
            // Calcul des totaux
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $discountAmount = $devisData['discount_percent'] ? ($subtotal * $devisData['discount_percent'] / 100) : 0;
            $subtotalAfterDiscount = $subtotal - $discountAmount;
            $tvaAmount = $subtotalAfterDiscount * ($devisData['tva_rate'] ?? 20) / 100;
            $total = $subtotalAfterDiscount + $tvaAmount;

            $devisData['subtotal'] = $subtotal;
            $devisData['discount_amount'] = $discountAmount;
            $devisData['tva_amount'] = $tvaAmount;
            $devisData['total'] = $total;
            $devisData['company_id'] = $companyId;

            // Crée le devis
            $devisId = $this->create($devisData);

            // Crée les lignes
            foreach ($items as $position => $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];

                $this->db->execute(
                    "INSERT INTO devis_items (devis_id, description, quantity, unit, unit_price, tva_rate, total, position)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $devisId,
                        $item['description'],
                        $item['quantity'],
                        $item['unit'] ?? 'unité',
                        $item['unit_price'],
                        $item['tva_rate'] ?? 20,
                        $itemTotal,
                        $position
                    ]
                );
            }

            $this->db->commit();
            return $devisId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Met à jour un devis avec ses lignes
     */
    public function updateWithItems($id, $devisData, $items, $companyId)
    {
        $this->db->beginTransaction();

        try {
            // Calcul des totaux
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $discountAmount = $devisData['discount_percent'] ? ($subtotal * $devisData['discount_percent'] / 100) : 0;
            $subtotalAfterDiscount = $subtotal - $discountAmount;
            $tvaAmount = $subtotalAfterDiscount * ($devisData['tva_rate'] ?? 20) / 100;
            $total = $subtotalAfterDiscount + $tvaAmount;

            $devisData['subtotal'] = $subtotal;
            $devisData['discount_amount'] = $discountAmount;
            $devisData['tva_amount'] = $tvaAmount;
            $devisData['total'] = $total;

            // Met à jour le devis
            $this->update($id, $devisData, $companyId);

            // Supprime les anciennes lignes
            $this->db->execute("DELETE FROM devis_items WHERE devis_id = ?", [$id]);

            // Crée les nouvelles lignes
            foreach ($items as $position => $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];

                $this->db->execute(
                    "INSERT INTO devis_items (devis_id, description, quantity, unit, unit_price, tva_rate, total, position)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $id,
                        $item['description'],
                        $item['quantity'],
                        $item['unit'] ?? 'unité',
                        $item['unit_price'],
                        $item['tva_rate'] ?? 20,
                        $itemTotal,
                        $position
                    ]
                );
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
