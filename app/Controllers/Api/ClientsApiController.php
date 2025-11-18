<?php
/**
 * API Controller pour les clients
 * Endpoints: /api/clients
 */

namespace App\Controllers\Api;

class ClientsApiController extends ApiController {

    /**
     * GET /api/clients
     * Liste tous les clients avec pagination
     */
    public function index() {
        $params = $this->getPaginationParams();

        $where = ['company_id = ?'];
        $bindings = [$this->company_id];

        // Filtres
        if (isset($_GET['type'])) {
            $where[] = 'type = ?';
            $bindings[] = $_GET['type'];
        }

        if (isset($_GET['status'])) {
            $where[] = 'status = ?';
            $bindings[] = $_GET['status'];
        }

        if (isset($_GET['search'])) {
            $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
            $search = '%' . $_GET['search'] . '%';
            $bindings[] = $search;
            $bindings[] = $search;
            $bindings[] = $search;
        }

        $whereClause = implode(' AND ', $where);

        // Total
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM clients WHERE $whereClause
        ");
        $stmt->execute($bindings);
        $total = $stmt->fetch()['total'];

        // Données
        $stmt = $this->db->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM chantiers WHERE client_id = c.id) as chantiers_count,
                   (SELECT SUM(total) FROM factures WHERE client_id = c.id) as total_factures
            FROM clients c
            WHERE $whereClause
            ORDER BY c.name ASC
            LIMIT {$params['perPage']} OFFSET {$params['offset']}
        ");
        $stmt->execute($bindings);
        $clients = $stmt->fetchAll();

        $this->paginate($clients, $total, $params['page'], $params['perPage']);
    }

    /**
     * GET /api/clients/:id
     * Récupère un client spécifique
     */
    public function show($id) {
        $this->checkOwnership('clients', $id);

        $stmt = $this->db->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM chantiers WHERE client_id = c.id) as chantiers_count,
                   (SELECT COUNT(*) FROM devis WHERE client_id = c.id) as devis_count,
                   (SELECT COUNT(*) FROM factures WHERE client_id = c.id) as factures_count,
                   (SELECT SUM(total) FROM factures WHERE client_id = c.id AND status = 'payee') as total_paye
            FROM clients c
            WHERE c.id = ? AND c.company_id = ?
        ");
        $stmt->execute([$id, $this->company_id]);
        $client = $stmt->fetch();

        if (!$client) {
            $this->error('Client not found', 404);
        }

        $this->success($client);
    }

    /**
     * POST /api/clients
     * Crée un nouveau client
     */
    public function store() {
        $data = $this->getJsonInput();

        $this->validateRequired($data, ['name', 'type']);

        // Validation email unique
        if (isset($data['email'])) {
            $stmt = $this->db->prepare("
                SELECT id FROM clients WHERE email = ? AND company_id = ?
            ");
            $stmt->execute([$data['email'], $this->company_id]);
            if ($stmt->fetch()) {
                $this->error('Email already exists', 422, ['email' => 'Cet email existe déjà']);
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO clients (
                company_id, type, name, email, phone,
                address, postal_code, city, siret,
                status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $this->company_id,
            $data['type'],
            $data['name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['postal_code'] ?? null,
            $data['city'] ?? null,
            $data['siret'] ?? null,
            $data['status'] ?? 'active'
        ]);

        $id = $this->db->lastInsertId();

        $this->success(['id' => $id], 'Client created successfully', 201);
    }

    /**
     * PUT /api/clients/:id
     * Met à jour un client
     */
    public function update($id) {
        $this->checkOwnership('clients', $id);

        $data = $this->getJsonInput();

        // Validation email unique (sauf pour ce client)
        if (isset($data['email'])) {
            $stmt = $this->db->prepare("
                SELECT id FROM clients WHERE email = ? AND company_id = ? AND id != ?
            ");
            $stmt->execute([$data['email'], $this->company_id, $id]);
            if ($stmt->fetch()) {
                $this->error('Email already exists', 422, ['email' => 'Cet email existe déjà']);
            }
        }

        $fields = [];
        $bindings = [];

        $allowedFields = ['type', 'name', 'email', 'phone', 'address',
                         'postal_code', 'city', 'siret', 'status', 'notes'];

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
            UPDATE clients
            SET " . implode(', ', $fields) . ", updated_at = NOW()
            WHERE id = ? AND company_id = ?
        ");

        $stmt->execute($bindings);

        $this->success(['id' => $id], 'Client updated successfully');
    }

    /**
     * DELETE /api/clients/:id
     * Supprime un client
     */
    public function delete($id) {
        $this->checkOwnership('clients', $id);

        // Vérifier si le client a des chantiers
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM chantiers WHERE client_id = ?
        ");
        $stmt->execute([$id]);
        if ($stmt->fetch()['count'] > 0) {
            $this->error('Cannot delete client with existing chantiers', 422);
        }

        $stmt = $this->db->prepare("
            DELETE FROM clients WHERE id = ? AND company_id = ?
        ");
        $stmt->execute([$id, $this->company_id]);

        $this->success(null, 'Client deleted successfully');
    }

    /**
     * GET /api/clients/:id/chantiers
     * Récupère tous les chantiers d'un client
     */
    public function chantiers($id) {
        $this->checkOwnership('clients', $id);

        $stmt = $this->db->prepare("
            SELECT c.*,
                   (SELECT SUM(amount) FROM depenses WHERE chantier_id = c.id) as total_depenses
            FROM chantiers c
            WHERE c.client_id = ? AND c.company_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$id, $this->company_id]);
        $chantiers = $stmt->fetchAll();

        $this->success($chantiers);
    }
}
