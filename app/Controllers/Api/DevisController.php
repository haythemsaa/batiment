<?php
/**
 * API - Contrôleur Devis
 */
class DevisController extends Controller
{
    private $devisModel;

    public function __construct()
    {
        parent::__construct();
        $this->devisModel = new Devis();
    }

    /**
     * Liste des devis
     * GET /api/devis
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

        $sql = "SELECT d.*, c.first_name, c.last_name, c.company_name, c.type as client_type
                FROM devis d
                LEFT JOIN clients c ON d.client_id = c.id
                WHERE d.company_id = ?";
        $params = [$companyId];

        if ($status) {
            $sql .= " AND d.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY d.date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $devisList = $this->db->query($sql, $params);

        // Formatte les données
        $result = array_map(function($devis) {
            return [
                'id' => $devis['id'],
                'number' => $devis['number'],
                'date' => $devis['date'],
                'validity_date' => $devis['validity_date'],
                'status' => $devis['status'],
                'title' => $devis['title'],
                'total' => (float)$devis['total'],
                'client' => [
                    'id' => $devis['client_id'],
                    'name' => $devis['client_type'] === 'company'
                        ? $devis['company_name']
                        : $devis['first_name'] . ' ' . $devis['last_name']
                ]
            ];
        }, $devisList);

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

    /**
     * Détails d'un devis
     * GET /api/devis/{id}
     */
    public function show($id)
    {
        $companyId = $_SESSION['api_company_id'] ?? null;

        if (!$companyId) {
            $this->json(['error' => 'Non autorisé'], 401);
            return;
        }

        $devis = $this->devisModel->getWithDetails($id, $companyId);

        if (!$devis) {
            $this->json(['error' => 'Devis non trouvé'], 404);
            return;
        }

        $this->json([
            'success' => true,
            'data' => $devis
        ]);
    }
}
