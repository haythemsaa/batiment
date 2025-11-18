<?php
/**
 * API Controller pour les chantiers
 * Endpoints: /api/chantiers
 */

namespace App\Controllers\Api;

class ChantiersApiController extends ApiController {

    /**
     * GET /api/chantiers
     * Liste tous les chantiers avec pagination
     */
    public function index() {
        $params = $this->getPaginationParams();

        // Filtres optionnels
        $where = ['company_id = ?'];
        $bindings = [$this->company_id];

        if (isset($_GET['status'])) {
            $where[] = 'status = ?';
            $bindings[] = $_GET['status'];
        }

        if (isset($_GET['client_id'])) {
            $where[] = 'client_id = ?';
            $bindings[] = $_GET['client_id'];
        }

        $whereClause = implode(' AND ', $where);

        // Compter le total
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM chantiers WHERE $whereClause
        ");
        $stmt->execute($bindings);
        $total = $stmt->fetch()['total'];

        // Récupérer les données
        $stmt = $this->db->prepare("
            SELECT c.*, cl.name as client_name
            FROM chantiers c
            LEFT JOIN clients cl ON c.client_id = cl.id
            WHERE $whereClause
            ORDER BY c.created_at DESC
            LIMIT {$params['perPage']} OFFSET {$params['offset']}
        ");
        $stmt->execute($bindings);
        $chantiers = $stmt->fetchAll();

        $this->paginate($chantiers, $total, $params['page'], $params['perPage']);
    }

    /**
     * GET /api/chantiers/:id
     * Récupère un chantier spécifique
     */
    public function show($id) {
        $this->checkOwnership('chantiers', $id);

        $stmt = $this->db->prepare("
            SELECT c.*,
                   cl.name as client_name,
                   cl.email as client_email,
                   cl.phone as client_phone,
                   (SELECT COUNT(*) FROM depenses WHERE chantier_id = c.id) as depenses_count,
                   (SELECT SUM(amount) FROM depenses WHERE chantier_id = c.id) as total_depenses
            FROM chantiers c
            LEFT JOIN clients cl ON c.client_id = cl.id
            WHERE c.id = ? AND c.company_id = ?
        ");
        $stmt->execute([$id, $this->company_id]);
        $chantier = $stmt->fetch();

        if (!$chantier) {
            $this->error('Chantier not found', 404);
        }

        $this->success($chantier);
    }

    /**
     * POST /api/chantiers
     * Crée un nouveau chantier
     */
    public function store() {
        $data = $this->getJsonInput();

        // Validation
        $this->validateRequired($data, ['name', 'client_id', 'start_date']);

        // Vérifier que le client appartient à l'entreprise
        $this->checkOwnership('clients', $data['client_id']);

        $stmt = $this->db->prepare("
            INSERT INTO chantiers (
                company_id, client_id, name, description,
                start_date, end_date, estimated_budget,
                status, progress, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $this->company_id,
            $data['client_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['start_date'],
            $data['end_date'] ?? null,
            $data['estimated_budget'] ?? 0,
            $data['status'] ?? 'planifie',
            $data['progress'] ?? 0
        ]);

        $id = $this->db->lastInsertId();

        $this->success(['id' => $id], 'Chantier created successfully', 201);
    }

    /**
     * PUT /api/chantiers/:id
     * Met à jour un chantier
     */
    public function update($id) {
        $this->checkOwnership('chantiers', $id);

        $data = $this->getJsonInput();

        $fields = [];
        $bindings = [];

        $allowedFields = ['name', 'description', 'client_id', 'start_date', 'end_date',
                         'estimated_budget', 'status', 'progress'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $bindings[] = $data[$field];
            }
        }

        if (empty($fields)) {
            $this->error('No valid fields to update', 400);
        }

        $bindings[] = $id;
        $bindings[] = $this->company_id;

        $stmt = $this->db->prepare("
            UPDATE chantiers
            SET " . implode(', ', $fields) . ", updated_at = NOW()
            WHERE id = ? AND company_id = ?
        ");

        $stmt->execute($bindings);

        $this->success(['id' => $id], 'Chantier updated successfully');
    }

    /**
     * DELETE /api/chantiers/:id
     * Supprime un chantier
     */
    public function delete($id) {
        $this->checkOwnership('chantiers', $id);

        $stmt = $this->db->prepare("
            DELETE FROM chantiers WHERE id = ? AND company_id = ?
        ");
        $stmt->execute([$id, $this->company_id]);

        $this->success(null, 'Chantier deleted successfully');
    }

    /**
     * GET /api/chantiers/:id/stats
     * Statistiques d'un chantier
     */
    public function stats($id) {
        $this->checkOwnership('chantiers', $id);

        // Récupérer les statistiques
        $stmt = $this->db->prepare("
            SELECT
                c.*,
                (SELECT SUM(amount) FROM depenses WHERE chantier_id = c.id) as total_depenses,
                (SELECT COUNT(*) FROM depenses WHERE chantier_id = c.id) as depenses_count,
                (SELECT SUM(total) FROM factures WHERE chantier_id = c.id) as total_factures,
                (SELECT SUM(paid_amount) FROM factures WHERE chantier_id = c.id) as total_paye
            FROM chantiers c
            WHERE c.id = ? AND c.company_id = ?
        ");
        $stmt->execute([$id, $this->company_id]);
        $stats = $stmt->fetch();

        if (!$stats) {
            $this->error('Chantier not found', 404);
        }

        // Calculer la rentabilité
        $stats['marge'] = ($stats['total_factures'] ?? 0) - ($stats['total_depenses'] ?? 0);
        $stats['taux_marge'] = $stats['total_factures'] > 0
            ? round(($stats['marge'] / $stats['total_factures']) * 100, 2)
            : 0;

        $this->success($stats);
    }
}
