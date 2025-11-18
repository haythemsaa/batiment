<?php

namespace App\Models;

use App\Core\Model;

class Document extends Model {
    protected $table = 'documents';

    /**
     * Upload un nouveau document
     */
    public function upload($file, $data) {
        $uploadDir = __DIR__ . '/../../public/uploads/documents/';
        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (
                    company_id, chantier_id, client_id, uploaded_by,
                    name, original_name, file_path, file_size, mime_type,
                    category, description, tags, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $data['company_id'],
                $data['chantier_id'] ?? null,
                $data['client_id'] ?? null,
                $data['uploaded_by'],
                $data['name'],
                $file['name'],
                'uploads/documents/' . $filename,
                $file['size'],
                $file['type'],
                $data['category'] ?? 'autre',
                $data['description'] ?? null,
                $data['tags'] ?? null
            ]);

            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Récupère les documents d'un chantier
     */
    public function getByChantier($chantierId) {
        $stmt = $this->db->prepare("
            SELECT d.*, u.name as uploaded_by_name
            FROM {$this->table} d
            LEFT JOIN users u ON d.uploaded_by = u.id
            WHERE d.chantier_id = ? AND d.is_latest = TRUE
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$chantierId]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère toutes les versions d'un document
     */
    public function getVersions($documentId) {
        $stmt = $this->db->prepare("
            SELECT d.*, u.name as uploaded_by_name
            FROM {$this->table} d
            LEFT JOIN users u ON d.uploaded_by = u.id
            WHERE (d.id = ? OR d.parent_id = ?)
            ORDER BY d.version DESC
        ");
        $stmt->execute([$documentId, $documentId]);
        return $stmt->fetchAll();
    }

    /**
     * Recherche de documents
     */
    public function search($companyId, $query) {
        $stmt = $this->db->prepare("
            SELECT d.*, u.name as uploaded_by_name
            FROM {$this->table} d
            LEFT JOIN users u ON d.uploaded_by = u.id
            WHERE d.company_id = ?
              AND d.is_latest = TRUE
              AND MATCH(d.name, d.description, d.tags) AGAINST(? IN NATURAL LANGUAGE MODE)
            ORDER BY d.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$companyId, $query]);
        return $stmt->fetchAll();
    }
}
