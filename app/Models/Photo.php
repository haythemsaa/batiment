<?php

namespace App\Models;

use App\Core\Model;

class Photo extends Model {
    protected $table = 'photos';

    /**
     * Upload une photo avec métadonnées
     */
    public function upload($file, $data) {
        $uploadDir = __DIR__ . '/../../public/uploads/photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $uploadDir . $filename;
        $thumbPath = $uploadDir . 'thumb_' . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Créer miniature
            $this->createThumbnail($filepath, $thumbPath, 300, 300);

            // Extraire dimensions
            list($width, $height) = getimagesize($filepath);

            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (
                    company_id, chantier_id, uploaded_by, title, description,
                    file_path, thumbnail_path, file_size, width, height,
                    category, zone, etage, latitude, longitude, annotations, taken_at, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $data['company_id'],
                $data['chantier_id'],
                $data['uploaded_by'],
                $data['title'] ?? null,
                $data['description'] ?? null,
                'uploads/photos/' . $filename,
                'uploads/photos/thumb_' . $filename,
                $file['size'],
                $width,
                $height,
                $data['category'] ?? 'autre',
                $data['zone'] ?? null,
                $data['etage'] ?? null,
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
                $data['annotations'] ?? null,
                $data['taken_at'] ?? date('Y-m-d H:i:s')
            ]);

            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Crée une miniature
     */
    private function createThumbnail($source, $dest, $maxWidth, $maxHeight) {
        list($width, $height, $type) = getimagesize($source);

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $img = imagecreatefromgif($source);
                break;
            default:
                return false;
        }

        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($thumb, $dest, 85);

        imagedestroy($thumb);
        imagedestroy($img);

        return true;
    }

    /**
     * Récupère les photos d'un chantier
     */
    public function getByChantier($chantierId, $category = null) {
        $sql = "
            SELECT p.*, u.name as uploaded_by_name
            FROM {$this->table} p
            LEFT JOIN users u ON p.uploaded_by = u.id
            WHERE p.chantier_id = ?
        ";

        $params = [$chantierId];

        if ($category) {
            $sql .= " AND p.category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY p.taken_at DESC, p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les photos avant/après d'un chantier
     */
    public function getBeforeAfter($chantierId) {
        $stmt = $this->db->prepare("
            SELECT category, file_path, thumbnail_path, title, taken_at
            FROM {$this->table}
            WHERE chantier_id = ?
              AND category IN ('avant', 'apres')
            ORDER BY taken_at ASC
        ");
        $stmt->execute([$chantierId]);

        $photos = ['avant' => [], 'apres' => []];
        foreach ($stmt->fetchAll() as $photo) {
            $photos[$photo['category']][] = $photo;
        }

        return $photos;
    }
}
