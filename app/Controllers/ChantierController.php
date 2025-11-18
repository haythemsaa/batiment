<?php
/**
 * Contrôleur Chantier
 */
class ChantierController extends Controller
{
    private $chantierModel;
    private $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->chantierModel = new Chantier();
        $this->clientModel = new Client();
    }

    /**
     * Liste des chantiers
     */
    public function index()
    {
        $companyId = $_SESSION['company_id'];
        $chantiersList = $this->db->query(
            "SELECT ch.*, c.first_name, c.last_name, c.company_name, c.type as client_type
             FROM chantiers ch
             LEFT JOIN clients c ON ch.client_id = c.id
             WHERE ch.company_id = ?
             ORDER BY ch.start_date DESC",
            [$companyId]
        );

        $this->view('chantiers.index', ['chantiers' => $chantiersList]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $companyId = $_SESSION['company_id'];
        $clients = $this->clientModel->all($companyId);

        $this->view('chantiers.create', ['clients' => $clients]);
    }

    /**
     * Enregistrement d'un chantier
     */
    public function store()
    {
        $companyId = $_SESSION['company_id'];

        $chantierData = [
            'company_id' => $companyId,
            'client_id' => $this->post('client_id'),
            'name' => $this->post('name'),
            'reference' => $this->post('reference'),
            'address' => $this->post('address'),
            'postal_code' => $this->post('postal_code'),
            'city' => $this->post('city'),
            'description' => $this->post('description'),
            'start_date' => $this->post('start_date'),
            'end_date' => $this->post('end_date'),
            'estimated_budget' => $this->post('estimated_budget'),
            'status' => $this->post('status', 'planned'),
            'notes' => $this->post('notes'),
            'created_by' => $_SESSION['user_id']
        ];

        try {
            $chantierId = $this->chantierModel->create($chantierData);
            $this->setFlash('success', 'Chantier créé avec succès');
            $this->redirect('/chantiers/view/' . $chantierId);
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la création du chantier');
            $this->redirect('/chantiers/create');
        }
    }

    /**
     * Affichage d'un chantier
     */
    public function view($id)
    {
        $companyId = $_SESSION['company_id'];
        $chantier = $this->chantierModel->getWithDetails($id, $companyId);

        if (!$chantier) {
            $this->setFlash('error', 'Chantier non trouvé');
            $this->redirect('/chantiers');
            return;
        }

        // Calcul de la rentabilité
        $profitability = $this->chantierModel->calculateProfitability($id, $companyId);

        $this->view('chantiers.view', [
            'chantier' => $chantier,
            'profitability' => $profitability
        ]);
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $companyId = $_SESSION['company_id'];
        $chantier = $this->chantierModel->find($id, $companyId);

        if (!$chantier) {
            $this->setFlash('error', 'Chantier non trouvé');
            $this->redirect('/chantiers');
            return;
        }

        $clients = $this->clientModel->all($companyId);

        $this->view('chantiers.edit', [
            'chantier' => $chantier,
            'clients' => $clients
        ]);
    }

    /**
     * Mise à jour d'un chantier
     */
    public function update($id)
    {
        $companyId = $_SESSION['company_id'];

        $chantierData = [
            'client_id' => $this->post('client_id'),
            'name' => $this->post('name'),
            'reference' => $this->post('reference'),
            'address' => $this->post('address'),
            'postal_code' => $this->post('postal_code'),
            'city' => $this->post('city'),
            'description' => $this->post('description'),
            'start_date' => $this->post('start_date'),
            'end_date' => $this->post('end_date'),
            'estimated_budget' => $this->post('estimated_budget'),
            'actual_cost' => $this->post('actual_cost'),
            'status' => $this->post('status'),
            'progress_percent' => $this->post('progress_percent'),
            'notes' => $this->post('notes')
        ];

        try {
            $this->chantierModel->update($id, $chantierData, $companyId);
            $this->setFlash('success', 'Chantier mis à jour avec succès');
            $this->redirect('/chantiers/view/' . $id);
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la mise à jour');
            $this->redirect('/chantiers/edit/' . $id);
        }
    }

    /**
     * Vue Gantt du chantier
     */
    public function gantt($id)
    {
        $companyId = $_SESSION['company_id'];
        $chantier = $this->chantierModel->getWithDetails($id, $companyId);

        if (!$chantier) {
            $this->setFlash('error', 'Chantier non trouvé');
            $this->redirect('/chantiers');
            return;
        }

        $this->view('chantiers.gantt', ['chantier' => $chantier]);
    }
}
