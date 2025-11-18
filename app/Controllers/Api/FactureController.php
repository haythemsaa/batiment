<?php
/**
 * API - Contrôleur Facture
 */
class FactureController extends Controller
{
    private $factureModel;

    public function __construct()
    {
        parent::__construct();
        $this->factureModel = new Facture();
    }

    /**
     * Liste des factures
     * GET /api/factures
     */
    public function index()
    {
        $companyId = $_SESSION['api_company_id'] ?? null;

        if (!$companyId) {
            $this->json(['error' => 'Non autorisé'], 401);
            return;
        }

        $status = $_GET['status'] ?? null;
        $limit = min((int)($_GET['limit'] ?? 20), 100);
        $offset = (int)($_GET['offset'] ?? 0);

        $sql = "SELECT f.*, c.first_name, c.last_name, c.company_name, c.type as client_type
                FROM factures f
                LEFT JOIN clients c ON f.client_id = c.id
                WHERE f.company_id = ?";
        $params = [$companyId];

        if ($status) {
            $sql .= " AND f.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY f.date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $facturesList = $this->db->query($sql, $params);

        // Formatte les données
        $result = array_map(function($facture) {
            return [
                'id' => $facture['id'],
                'number' => $facture['number'],
                'date' => $facture['date'],
                'due_date' => $facture['due_date'],
                'status' => $facture['status'],
                'type' => $facture['type'],
                'total' => (float)$facture['total'],
                'paid_amount' => (float)$facture['paid_amount'],
                'remaining_amount' => (float)$facture['remaining_amount'],
                'client' => [
                    'id' => $facture['client_id'],
                    'name' => $facture['client_type'] === 'company'
                        ? $facture['company_name']
                        : $facture['first_name'] . ' ' . $facture['last_name']
                ]
            ];
        }, $facturesList);

        $this->json([
            'success' => true,
            'data' => $result,
            'meta' => [
                'limit' => $limit,
                'offset' => $offset,
                'count' => count($result)
            ]
        ]);
    }
}
