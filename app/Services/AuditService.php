<?php

namespace App\Services;

/**
 * Service de logs d'audit
 * Traçabilité complète de toutes les actions
 */
class AuditService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Enregistre une action dans l'audit trail
     */
    public function log($companyId, $userId, $action, $entityType, $entityId = null, $oldValues = null, $newValues = null) {
        $stmt = $this->db->prepare("
            INSERT INTO audit_logs (
                company_id, user_id, action, entity_type, entity_id,
                old_values, new_values, ip_address, user_agent, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $companyId,
            $userId,
            $action,
            $entityType,
            $entityId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    /**
     * Log création d'entité
     */
    public function logCreate($companyId, $userId, $entityType, $entityId, $data) {
        $this->log($companyId, $userId, 'create', $entityType, $entityId, null, $data);
    }

    /**
     * Log mise à jour d'entité
     */
    public function logUpdate($companyId, $userId, $entityType, $entityId, $oldData, $newData) {
        $this->log($companyId, $userId, 'update', $entityType, $entityId, $oldData, $newData);
    }

    /**
     * Log suppression d'entité
     */
    public function logDelete($companyId, $userId, $entityType, $entityId, $data) {
        $this->log($companyId, $userId, 'delete', $entityType, $entityId, $data, null);
    }

    /**
     * Log connexion utilisateur
     */
    public function logLogin($companyId, $userId, $success = true) {
        $this->log($companyId, $userId, $success ? 'login' : 'login_failed', 'user', $userId, null, ['success' => $success]);
    }

    /**
     * Log déconnexion
     */
    public function logLogout($companyId, $userId) {
        $this->log($companyId, $userId, 'logout', 'user', $userId);
    }

    /**
     * Récupère l'historique d'audit
     */
    public function getHistory($companyId, $filters = []) {
        $where = ['company_id = ?'];
        $params = [$companyId];

        if (isset($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (isset($filters['entity_type'])) {
            $where[] = 'entity_type = ?';
            $params[] = $filters['entity_type'];
        }

        if (isset($filters['entity_id'])) {
            $where[] = 'entity_id = ?';
            $params[] = $filters['entity_id'];
        }

        if (isset($filters['action'])) {
            $where[] = 'action = ?';
            $params[] = $filters['action'];
        }

        if (isset($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['date_from'];
        }

        if (isset($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $where);
        $limit = $filters['limit'] ?? 100;
        $offset = $filters['offset'] ?? 0;

        $stmt = $this->db->prepare("
            SELECT a.*, u.name as user_name
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE $whereClause
            ORDER BY a.created_at DESC
            LIMIT $limit OFFSET $offset
        ");

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupère l'historique d'une entité spécifique
     */
    public function getEntityHistory($companyId, $entityType, $entityId) {
        $stmt = $this->db->prepare("
            SELECT a.*, u.name as user_name
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.company_id = ?
              AND a.entity_type = ?
              AND a.entity_id = ?
            ORDER BY a.created_at DESC
        ");

        $stmt->execute([$companyId, $entityType, $entityId]);
        return $stmt->fetchAll();
    }

    /**
     * Statistiques d'audit
     */
    public function getStats($companyId, $period = '30 days') {
        $stmt = $this->db->prepare("
            SELECT
                action,
                entity_type,
                COUNT(*) as count,
                DATE(created_at) as date
            FROM audit_logs
            WHERE company_id = ?
              AND created_at >= DATE_SUB(NOW(), INTERVAL $period)
            GROUP BY action, entity_type, DATE(created_at)
            ORDER BY created_at DESC
        ");

        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    /**
     * Nettoie les vieux logs (RGPD)
     */
    public function cleanOldLogs($days = 365) {
        $stmt = $this->db->prepare("
            DELETE FROM audit_logs
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");

        return $stmt->execute([$days]);
    }
}
