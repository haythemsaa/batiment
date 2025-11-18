<?php

namespace App\Models;

use App\Core\Model;

class Message extends Model {
    protected $table = 'messages';

    /**
     * Envoie un message
     */
    public function send($fromUserId, $companyId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (
                company_id, chantier_id, from_user_id, to_user_id,
                message, attachments, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $companyId,
            $data['chantier_id'] ?? null,
            $fromUserId,
            $data['to_user_id'] ?? null,
            $data['message'],
            $data['attachments'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Récupère les messages d'un chantier
     */
    public function getByChantier($chantierId, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   u1.name as from_user_name,
                   u2.name as to_user_name
            FROM {$this->table} m
            LEFT JOIN users u1 ON m.from_user_id = u1.id
            LEFT JOIN users u2 ON m.to_user_id = u2.id
            WHERE m.chantier_id = ?
            ORDER BY m.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $chantierId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Marque comme lu
     */
    public function markAsRead($id) {
        return $this->update($id, [
            'is_read' => true,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Compte les non lus
     */
    public function countUnread($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM {$this->table}
            WHERE to_user_id = ? AND is_read = FALSE
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch()['count'];
    }
}
