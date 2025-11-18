<?php
/**
 * Modèle Facture
 */
class Facture extends Model
{
    protected $table = 'factures';

    /**
     * Génère un numéro de facture unique
     */
    public function generateNumber($companyId, $type = 'facture')
    {
        $year = date('Y');
        $prefix = $type === 'avoir' ? "AV-{$year}-" : "FA-{$year}-";

        $lastFacture = $this->db->queryOne(
            "SELECT number FROM factures WHERE company_id = ? AND number LIKE ? ORDER BY id DESC LIMIT 1",
            [$companyId, $prefix . '%']
        );

        if ($lastFacture) {
            $lastNumber = (int) str_replace($prefix, '', $lastFacture['number']);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Récupère une facture avec ses détails
     */
    public function getWithDetails($id, $companyId)
    {
        $facture = $this->find($id, $companyId);

        if (!$facture) {
            return null;
        }

        // Récupère le client
        $facture['client'] = $this->db->queryOne(
            "SELECT * FROM clients WHERE id = ?",
            [$facture['client_id']]
        );

        // Récupère les lignes
        $facture['items'] = $this->db->query(
            "SELECT * FROM facture_items WHERE facture_id = ? ORDER BY position",
            [$id]
        );

        // Récupère les paiements
        $facture['payments'] = $this->db->query(
            "SELECT * FROM payments WHERE facture_id = ? ORDER BY payment_date DESC",
            [$id]
        );

        return $facture;
    }

    /**
     * Crée une facture depuis un devis
     */
    public function createFromDevis($devisId, $companyId)
    {
        $devisModel = new Devis();
        $devis = $devisModel->getWithDetails($devisId, $companyId);

        if (!$devis) {
            return false;
        }

        $this->db->beginTransaction();

        try {
            // Crée la facture
            $factureData = [
                'company_id' => $companyId,
                'client_id' => $devis['client_id'],
                'devis_id' => $devisId,
                'number' => $this->generateNumber($companyId),
                'date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'type' => 'facture',
                'status' => 'draft',
                'title' => $devis['title'],
                'description' => $devis['description'],
                'subtotal' => $devis['subtotal'],
                'discount_percent' => $devis['discount_percent'],
                'discount_amount' => $devis['discount_amount'],
                'tva_amount' => $devis['tva_amount'],
                'total' => $devis['total'],
                'remaining_amount' => $devis['total'],
                'notes' => $devis['notes'],
                'terms' => $devis['terms'],
                'created_by' => $_SESSION['user_id'] ?? null
            ];

            $factureId = $this->create($factureData);

            // Copie les lignes
            foreach ($devis['items'] as $item) {
                $this->db->execute(
                    "INSERT INTO facture_items (facture_id, description, quantity, unit, unit_price, tva_rate, total, position)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $factureId,
                        $item['description'],
                        $item['quantity'],
                        $item['unit'],
                        $item['unit_price'],
                        $item['tva_rate'],
                        $item['total'],
                        $item['position']
                    ]
                );
            }

            // Marque le devis comme converti
            $this->db->execute(
                "UPDATE devis SET converted_to_invoice = TRUE, invoice_id = ? WHERE id = ?",
                [$factureId, $devisId]
            );

            $this->db->commit();
            return $factureId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
