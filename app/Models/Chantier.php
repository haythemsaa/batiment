<?php
/**
 * Modèle Chantier
 */
class Chantier extends Model
{
    protected $table = 'chantiers';

    /**
     * Récupère un chantier avec ses détails
     */
    public function getWithDetails($id, $companyId)
    {
        $chantier = $this->find($id, $companyId);

        if (!$chantier) {
            return null;
        }

        // Récupère le client
        $chantier['client'] = $this->db->queryOne(
            "SELECT * FROM clients WHERE id = ?",
            [$chantier['client_id']]
        );

        // Récupère les tâches
        $chantier['tasks'] = $this->db->query(
            "SELECT * FROM chantier_tasks WHERE chantier_id = ? ORDER BY position",
            [$id]
        );

        // Récupère les interventions
        $chantier['interventions'] = $this->db->query(
            "SELECT * FROM interventions WHERE chantier_id = ? ORDER BY date DESC",
            [$id]
        );

        // Récupère les dépenses
        $chantier['expenses'] = $this->db->query(
            "SELECT * FROM expenses WHERE chantier_id = ? ORDER BY date DESC",
            [$id]
        );

        return $chantier;
    }

    /**
     * Calcule la rentabilité d'un chantier
     */
    public function calculateProfitability($id, $companyId)
    {
        $chantier = $this->find($id, $companyId);

        if (!$chantier) {
            return null;
        }

        $revenue = $chantier['estimated_budget'] ?? 0;
        $costs = $chantier['actual_cost'] ?? 0;

        return [
            'revenue' => $revenue,
            'costs' => $costs,
            'profit' => $revenue - $costs,
            'margin_percent' => $revenue > 0 ? (($revenue - $costs) / $revenue * 100) : 0
        ];
    }
}
