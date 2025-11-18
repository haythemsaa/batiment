<?php

namespace App\Models;

use App\Core\Model;

class Task extends Model {
    protected $table = 'tasks';

    /**
     * Récupère toutes les tâches d'un chantier
     */
    public function getByChantier($chantierId) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as assigned_to_name
            FROM {$this->table} t
            LEFT JOIN users u ON t.assigned_to = u.id
            WHERE t.chantier_id = ?
            ORDER BY
                CASE t.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END,
                t.due_date ASC,
                t.created_at DESC
        ");
        $stmt->execute([$chantierId]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les tâches assignées à un utilisateur
     */
    public function getByUser($userId, $status = null) {
        $sql = "
            SELECT t.*, c.name as chantier_name, cl.name as client_name
            FROM {$this->table} t
            LEFT JOIN chantiers c ON t.chantier_id = c.id
            LEFT JOIN clients cl ON c.client_id = cl.id
            WHERE t.assigned_to = ?
        ";

        $params = [$userId];

        if ($status) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY t.due_date ASC, t.priority ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les tâches en retard
     */
    public function getOverdue($companyId) {
        $stmt = $this->db->prepare("
            SELECT t.*, c.name as chantier_name, u.name as assigned_to_name
            FROM {$this->table} t
            LEFT JOIN chantiers c ON t.chantier_id = c.id
            LEFT JOIN users u ON t.assigned_to = u.id
            WHERE t.company_id = ?
              AND t.status NOT IN ('completed', 'cancelled')
              AND t.due_date < CURDATE()
            ORDER BY t.due_date ASC
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les statistiques des tâches pour un chantier
     */
    public function getStatsForChantier($chantierId) {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as todo,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN due_date < CURDATE() AND status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) as overdue
            FROM {$this->table}
            WHERE chantier_id = ?
        ");
        $stmt->execute([$chantierId]);
        return $stmt->fetch();
    }

    /**
     * Marque une tâche comme complétée
     */
    public function complete($id) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET status = 'completed', completed_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Assigne une tâche à un utilisateur
     */
    public function assign($id, $userId) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET assigned_to = ?
            WHERE id = ?
        ");
        return $stmt->execute([$userId, $id]);
    }
}
