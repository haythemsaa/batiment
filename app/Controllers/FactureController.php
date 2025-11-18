<?php
/**
 * Contrôleur Facture
 */
class FactureController extends Controller
{
    private $factureModel;
    private $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->factureModel = new Facture();
        $this->clientModel = new Client();
    }

    /**
     * Liste des factures
     */
    public function index()
    {
        $companyId = $_SESSION['company_id'];
        $facturesList = $this->db->query(
            "SELECT f.*, c.first_name, c.last_name, c.company_name, c.type as client_type
             FROM factures f
             LEFT JOIN clients c ON f.client_id = c.id
             WHERE f.company_id = ?
             ORDER BY f.date DESC",
            [$companyId]
        );

        $this->view('factures.index', ['factures' => $facturesList]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $companyId = $_SESSION['company_id'];
        $clients = $this->clientModel->all($companyId);

        $this->view('factures.create', [
            'clients' => $clients,
            'number' => $this->factureModel->generateNumber($companyId)
        ]);
    }

    /**
     * Enregistrement d'une facture
     */
    public function store()
    {
        $companyId = $_SESSION['company_id'];

        $factureData = [
            'company_id' => $companyId,
            'client_id' => $this->post('client_id'),
            'number' => $this->post('number'),
            'date' => $this->post('date'),
            'due_date' => $this->post('due_date'),
            'type' => $this->post('type', 'facture'),
            'status' => $this->post('status', 'draft'),
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'discount_percent' => $this->post('discount_percent', 0),
            'notes' => $this->post('notes'),
            'terms' => $this->post('terms'),
            'created_by' => $_SESSION['user_id']
        ];

        // Calcul des montants (simplifié pour l'exemple)
        $items = $this->post('items', []);
        $total = array_reduce($items, function($sum, $item) {
            return $sum + ($item['quantity'] * $item['unit_price']);
        }, 0);

        $factureData['subtotal'] = $total;
        $factureData['total'] = $total * 1.20; // TVA 20%
        $factureData['tva_amount'] = $total * 0.20;
        $factureData['remaining_amount'] = $factureData['total'];

        try {
            $factureId = $this->factureModel->create($factureData);
            $this->setFlash('success', 'Facture créée avec succès');
            $this->redirect('/factures/view/' . $factureId);
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la création de la facture');
            $this->redirect('/factures/create');
        }
    }

    /**
     * Affichage d'une facture
     */
    public function view($id)
    {
        $companyId = $_SESSION['company_id'];
        $facture = $this->factureModel->getWithDetails($id, $companyId);

        if (!$facture) {
            $this->setFlash('error', 'Facture non trouvée');
            $this->redirect('/factures');
            return;
        }

        $company = $this->getCurrentCompany();

        $this->view('factures.view', [
            'facture' => $facture,
            'company' => $company
        ]);
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $companyId = $_SESSION['company_id'];
        $facture = $this->factureModel->getWithDetails($id, $companyId);

        if (!$facture) {
            $this->setFlash('error', 'Facture non trouvée');
            $this->redirect('/factures');
            return;
        }

        $clients = $this->clientModel->all($companyId);

        $this->view('factures.edit', [
            'facture' => $facture,
            'clients' => $clients
        ]);
    }

    /**
     * Mise à jour d'une facture
     */
    public function update($id)
    {
        $companyId = $_SESSION['company_id'];

        $factureData = [
            'client_id' => $this->post('client_id'),
            'date' => $this->post('date'),
            'due_date' => $this->post('due_date'),
            'status' => $this->post('status'),
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'notes' => $this->post('notes')
        ];

        try {
            $this->factureModel->update($id, $factureData, $companyId);
            $this->setFlash('success', 'Facture mise à jour avec succès');
            $this->redirect('/factures/view/' . $id);
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la mise à jour');
            $this->redirect('/factures/edit/' . $id);
        }
    }

    /**
     * Génération PDF
     */
    public function generatePDF($id)
    {
        $companyId = $_SESSION['company_id'];
        $facture = $this->factureModel->getWithDetails($id, $companyId);

        if (!$facture) {
            echo "Facture non trouvée";
            return;
        }

        $company = $this->getCurrentCompany();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="facture-' . $facture['number'] . '.pdf"');

        // Génération du PDF (à implémenter avec une librairie)
        echo "PDF de la facture " . $facture['number'];
    }

    /**
     * Envoi par email
     */
    public function sendEmail($id)
    {
        $companyId = $_SESSION['company_id'];
        $facture = $this->factureModel->getWithDetails($id, $companyId);

        if (!$facture) {
            $this->json(['error' => 'Facture non trouvée'], 404);
            return;
        }

        // Envoi de l'email (à implémenter avec PHPMailer ou autre)
        $this->json(['success' => true, 'message' => 'Facture envoyée par email']);
    }
}
