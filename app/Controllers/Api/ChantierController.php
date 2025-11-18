<?php
/**
 * API - Contrôleur Chantier
 */
class ChantierController extends Controller
{
    private $chantierModel;

    public function __construct()
    {
        parent::__construct();
        $this->chantierModel = new Chantier();
    }

    /**
     * Liste des chantiers
     * GET /api/chantiers
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

        $sql = "SELECT ch.*, c.first_name, c.last_name, c.company_name, c.type as client_type
                FROM chantiers ch
                LEFT JOIN clients c ON ch.client_id = c.id
                WHERE ch.company_id = ?";
        $params = [$companyId];

        if ($status) {
            $sql .= " AND ch.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY ch.start_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $chantiersList = $this->db->query($sql, $params);

        // Formatte les données
        $result = array_map(function($chantier) {
            return [
                'id' => $chantier['id'],
                'name' => $chantier['name'],
                'reference' => $chantier['reference'],
                'start_date' => $chantier['start_date'],
                'end_date' => $chantier['end_date'],
                'status' => $chantier['status'],
                'progress_percent' => (float)$chantier['progress_percent'],
                'estimated_budget' => (float)$chantier['estimated_budget'],
                'actual_cost' => (float)$chantier['actual_cost'],
                'address' => $chantier['address'],
                'city' => $chantier['city'],
                'client' => [
                    'id' => $chantier['client_id'],
                    'name' => $chantier['client_type'] === 'company'
                        ? $chantier['company_name']
                        : $chantier['first_name'] . ' ' . $chantier['last_name']
                ]
            ];
        }, $chantiersList);

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
