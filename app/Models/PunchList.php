<?php

namespace App\Models;

use App\Core\Model;

class PunchList extends Model {
    protected $table = 'punch_lists';

    /**
     * Récupère les punch lists d'un chantier
     */
    public function getByChantier($chantierId, $status = null) {
        $sql = "
            SELECT p.*,
                   u1.name as assigned_to_name,
                   u2.name as created_by_name
            FROM {$this->table} p
            LEFT JOIN users u1 ON p.assigned_to = u1.id
            LEFT JOIN users u2 ON p.created_by = u2.id
            WHERE p.chantier_id = ?
        ";

        $params = [$chantierId];

        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY
            CASE p.priority
                WHEN 'critical' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
            END,
            p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Résout une punch list
     */
    public function resolve($id, $resolvedBy) {
        return $this->update($id, [
            'status' => 'resolved',
            'resolved_by' => $resolvedBy,
            'resolved_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Vérifie une punch list
     */
    public function verify($id, $verifiedBy) {
        return $this->update($id, [
            'status' => 'verified',
            'verified_by' => $verifiedBy,
            'verified_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Ferme une punch list
     */
    public function close($id) {
        return $this->update($id, ['status' => 'closed']);
    }

    /**
     * Statistiques punch lists
     */
    public function getStats($chantierId) {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN priority = 'critical' THEN 1 ELSE 0 END) as critical
            FROM {$this->table}
            WHERE chantier_id = ?
        ");
        $stmt->execute([$chantierId]);
        return $stmt->fetch();
    }
}
