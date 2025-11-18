<?php

namespace App\Models;

use App\Core\Model;

class Notification extends Model {
    protected $table = 'notifications';

    /**
     * Crée une nouvelle notification
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (
                user_id, company_id, type, title, message, link, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $data['user_id'],
            $data['company_id'],
            $data['type'],
            $data['title'],
            $data['message'],
            $data['link'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Récupère les notifications d'un utilisateur
     */
    public function getByUser($userId, $limit = 50, $onlyUnread = false) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE user_id = ?
        ";

        if ($onlyUnread) {
            $sql .= " AND is_read = FALSE";
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Compte les notifications non lues
     */
    public function countUnread($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM {$this->table}
            WHERE user_id = ? AND is_read = FALSE
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch()['count'] ?? 0;
    }

    /**
     * Marque une notification comme lue
     */
    public function markAsRead($id) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET is_read = TRUE, read_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead($userId) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET is_read = TRUE, read_at = NOW()
            WHERE user_id = ? AND is_read = FALSE
        ");
        return $stmt->execute([$userId]);
    }

    /**
     * Supprime les anciennes notifications (>30 jours)
     */
    public function deleteOld($days = 30) {
        $stmt = $this->db->prepare("
            DELETE FROM {$this->table}
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        return $stmt->execute([$days]);
    }

    /**
     * Envoie une notification à tous les utilisateurs d'une entreprise
     */
    public function notifyCompany($companyId, $data) {
        // Récupérer tous les utilisateurs actifs de l'entreprise
        $stmt = $this->db->prepare("
            SELECT id FROM users WHERE company_id = ? AND status = 'active'
        ");
        $stmt->execute([$companyId]);
        $users = $stmt->fetchAll();

        foreach ($users as $user) {
            $this->create(array_merge($data, [
                'user_id' => $user['id'],
                'company_id' => $companyId
            ]));
        }
    }
}
