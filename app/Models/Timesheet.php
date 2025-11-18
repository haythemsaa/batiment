<?php

namespace App\Models;

use App\Core\Model;

class Timesheet extends Model {
    protected $table = 'timesheets';

    /**
     * Pointage entrée
     */
    public function clockIn($userId, $companyId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (
                company_id, user_id, chantier_id, date, clock_in,
                latitude_in, longitude_in, notes, created_at
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $companyId,
            $userId,
            $data['chantier_id'] ?? null,
            date('Y-m-d'),
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['notes'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Pointage sortie
     */
    public function clockOut($id, $data) {
        $timesheet = $this->find($id);
        if (!$timesheet || $timesheet['clock_out']) {
            return false;
        }

        $clockIn = new \DateTime($timesheet['clock_in']);
        $clockOut = new \DateTime();
        $diff = $clockOut->diff($clockIn);

        $totalMinutes = ($diff->h * 60) + $diff->i;
        $breakMinutes = $data['break_duration'] ?? 0;
        $workMinutes = $totalMinutes - $breakMinutes;
        $totalHours = round($workMinutes / 60, 2);

        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET clock_out = NOW(),
                latitude_out = ?,
                longitude_out = ?,
                break_duration = ?,
                total_hours = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $breakMinutes,
            $totalHours,
            $id
        ]);
    }

    /**
     * Récupère les pointages d'un utilisateur
     */
    public function getByUser($userId, $period = 'month') {
        $dateCondition = match($period) {
            'today' => 'DATE(t.date) = CURDATE()',
            'week' => 't.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
            'month' => 'MONTH(t.date) = MONTH(CURDATE()) AND YEAR(t.date) = YEAR(CURDATE())',
            default => '1=1'
        };

        $stmt = $this->db->prepare("
            SELECT t.*, c.name as chantier_name
            FROM {$this->table} t
            LEFT JOIN chantiers c ON t.chantier_id = c.id
            WHERE t.user_id = ? AND $dateCondition
            ORDER BY t.date DESC, t.clock_in DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Statistiques de temps
     */
    public function getStats($userId, $period = 'month') {
        $dateCondition = match($period) {
            'week' => 'date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
            'month' => 'MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())',
            default => '1=1'
        };

        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_days,
                SUM(total_hours) as total_hours,
                AVG(total_hours) as avg_hours,
                SUM(CASE WHEN total_hours > 8 THEN total_hours - 8 ELSE 0 END) as overtime_hours
            FROM {$this->table}
            WHERE user_id = ? AND $dateCondition AND clock_out IS NOT NULL
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Valide un timesheet
     */
    public function approve($id, $approvedBy) {
        return $this->update($id, [
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
    }
}
