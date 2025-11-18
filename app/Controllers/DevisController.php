<?php
/**
 * Contrôleur Devis
 */
class DevisController extends Controller
{
    private $devisModel;
    private $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->devisModel = new Devis();
        $this->clientModel = new Client();
    }

    /**
     * Liste des devis
     */
    public function index()
    {
        $companyId = $_SESSION['company_id'];
        $devisList = $this->db->query(
            "SELECT d.*, c.first_name, c.last_name, c.company_name, c.type as client_type
             FROM devis d
             LEFT JOIN clients c ON d.client_id = c.id
             WHERE d.company_id = ?
             ORDER BY d.date DESC",
            [$companyId]
        );

        $this->view('devis.index', ['devis' => $devisList]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $companyId = $_SESSION['company_id'];
        $clients = $this->clientModel->all($companyId);

        $this->view('devis.create', [
            'clients' => $clients,
            'number' => $this->devisModel->generateNumber($companyId)
        ]);
    }

    /**
     * Enregistrement d'un devis
     */
    public function store()
    {
        $companyId = $_SESSION['company_id'];

        $devisData = [
            'client_id' => $this->post('client_id'),
            'number' => $this->post('number'),
            'date' => $this->post('date'),
            'validity_date' => $this->post('validity_date'),
            'status' => $this->post('status', 'draft'),
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'discount_percent' => $this->post('discount_percent', 0),
            'notes' => $this->post('notes'),
            'terms' => $this->post('terms'),
            'created_by' => $_SESSION['user_id']
        ];

        // Récupère les lignes
        $items = [];
        $descriptions = $this->post('item_description', []);
        $quantities = $this->post('item_quantity', []);
        $units = $this->post('item_unit', []);
        $prices = $this->post('item_price', []);

        foreach ($descriptions as $index => $description) {
            if (!empty($description)) {
                $items[] = [
                    'description' => $description,
                    'quantity' => $quantities[$index] ?? 1,
                    'unit' => $units[$index] ?? 'unité',
                    'unit_price' => $prices[$index] ?? 0
                ];
            }
        }

        try {
            $devisId = $this->devisModel->createWithItems($devisData, $items, $companyId);
            $this->setFlash('success', 'Devis créé avec succès');
            $this->redirect('/devis/view/' . $devisId);
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la création du devis');
            $this->redirect('/devis/create');
        }
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $companyId = $_SESSION['company_id'];
        $devis = $this->devisModel->getWithDetails($id, $companyId);

        if (!$devis) {
            $this->setFlash('error', 'Devis non trouvé');
            $this->redirect('/devis');
            return;
        }

        $clients = $this->clientModel->all($companyId);

        $this->view('devis.edit', [
            'devis' => $devis,
            'clients' => $clients
        ]);
    }

    /**
     * Mise à jour d'un devis
     */
    public function update($id)
    {
        $companyId = $_SESSION['company_id'];

        $devisData = [
            'client_id' => $this->post('client_id'),
            'date' => $this->post('date'),
            'validity_date' => $this->post('validity_date'),
            'status' => $this->post('status'),
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'discount_percent' => $this->post('discount_percent', 0),
            'notes' => $this->post('notes'),
            'terms' => $this->post('terms')
        ];

        // Récupère les lignes
        $items = [];
        $descriptions = $this->post('item_description', []);
        $quantities = $this->post('item_quantity', []);
        $units = $this->post('item_unit', []);
        $prices = $this->post('item_price', []);

        foreach ($descriptions as $index => $description) {
            if (!empty($description)) {
                $items[] = [
                    'description' => $description,
                    'quantity' => $quantities[$index] ?? 1,
                    'unit' => $units[$index] ?? 'unité',
                    'unit_price' => $prices[$index] ?? 0
                ];
            }
        }

        try {
            $this->devisModel->updateWithItems($id, $devisData, $items, $companyId);
            $this->setFlash('success', 'Devis mis à jour avec succès');
            $this->redirect('/devis/view/' . $id);
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la mise à jour du devis');
            $this->redirect('/devis/edit/' . $id);
        }
    }

    /**
     * Affichage d'un devis
     */
    public function view($id)
    {
        $companyId = $_SESSION['company_id'];
        $devis = $this->devisModel->getWithDetails($id, $companyId);

        if (!$devis) {
            $this->setFlash('error', 'Devis non trouvé');
            $this->redirect('/devis');
            return;
        }

        $company = $this->getCurrentCompany();

        $this->view('devis.view', [
            'devis' => $devis,
            'company' => $company
        ]);
    }

    /**
     * Génération PDF
     */
    public function generatePDF($id)
    {
        $companyId = $_SESSION['company_id'];
        $devis = $this->devisModel->getWithDetails($id, $companyId);

        if (!$devis) {
            $this->setFlash('error', 'Devis non trouvé');
            $this->redirect('/devis');
            return;
        }

        $company = $this->getCurrentCompany();

        // Génération du HTML pour le PDF
        ob_start();
        include APP_PATH . '/Views/devis/pdf.php';
        $html = ob_get_clean();

        // Headers pour le téléchargement
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="devis-' . $devis['number'] . '.pdf"');

        echo $html; // En production, utiliser une librairie comme TCPDF ou mPDF
    }

    /**
     * Conversion en facture
     */
    public function convertToInvoice($id)
    {
        $companyId = $_SESSION['company_id'];
        $factureModel = new Facture();

        try {
            $factureId = $factureModel->createFromDevis($id, $companyId);

            if ($factureId) {
                $this->setFlash('success', 'Devis converti en facture avec succès');
                $this->redirect('/factures/view/' . $factureId);
            } else {
                $this->setFlash('error', 'Erreur lors de la conversion');
                $this->redirect('/devis/view/' . $id);
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la conversion');
            $this->redirect('/devis/view/' . $id);
        }
    }
}
