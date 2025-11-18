<?php

namespace App\Models;

use App\Core\Model;

class CarnetBord extends Model {
    protected $table = 'carnet_bord';

    /**
     * Crée ou met à jour l'entrée du jour
     */
    public function createOrUpdate($chantierId, $companyId, $date, $data) {
        // Vérifier si une entrée existe déjà
        $stmt = $this->db->prepare("
            SELECT id FROM {$this->table}
            WHERE chantier_id = ? AND date = ?
        ");
        $stmt->execute([$chantierId, $date]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Mise à jour
            $this->update($existing['id'], $data);
            return $existing['id'];
        } else {
            // Création
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (
                    company_id, chantier_id, date, weather, temperature,
                    present_workers, work_done, materials_used, incidents,
                    photos, created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $companyId,
                $chantierId,
                $date,
                $data['weather'] ?? null,
                $data['temperature'] ?? null,
                $data['present_workers'] ?? null,
                $data['work_done'],
                $data['materials_used'] ?? null,
                $data['incidents'] ?? null,
                $data['photos'] ?? null,
                $data['created_by']
            ]);

            return $this->db->lastInsertId();
        }
    }

    /**
     * Récupère le carnet d'un chantier
     */
    public function getByChantier($chantierId, $limit = 30) {
        $stmt = $this->db->prepare("
            SELECT cb.*, u.name as created_by_name
            FROM {$this->table} cb
            LEFT JOIN users u ON cb.created_by = u.id
            WHERE cb.chantier_id = ?
            ORDER BY cb.date DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $chantierId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère l'entrée d'une date spécifique
     */
    public function getByDate($chantierId, $date) {
        $stmt = $this->db->prepare("
            SELECT cb.*, u.name as created_by_name
            FROM {$this->table} cb
            LEFT JOIN users u ON cb.created_by = u.id
            WHERE cb.chantier_id = ? AND cb.date = ?
        ");
        $stmt->execute([$chantierId, $date]);
        return $stmt->fetch();
    }

    /**
     * Génère un rapport hebdomadaire
     */
    public function getWeeklyReport($chantierId, $weekStart) {
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

        $stmt = $this->db->prepare("
            SELECT *
            FROM {$this->table}
            WHERE chantier_id = ?
              AND date BETWEEN ? AND ?
            ORDER BY date ASC
        ");
        $stmt->execute([$chantierId, $weekStart, $weekEnd]);
        return $stmt->fetchAll();
    }
}
