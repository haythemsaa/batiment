<?php
/**
 * Contrôleur Client
 */
class ClientController extends Controller
{
    private $clientModel;

    public function __construct()
    {
        parent::__construct();
        $this->clientModel = new Client();
    }

    /**
     * Liste des clients
     */
    public function index()
    {
        $companyId = $_SESSION['company_id'];
        $clients = $this->clientModel->all($companyId);

        $this->view('clients.index', ['clients' => $clients]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $this->view('clients.create');
    }

    /**
     * Enregistrement d'un client
     */
    public function store()
    {
        $companyId = $_SESSION['company_id'];

        $clientData = [
            'company_id' => $companyId,
            'type' => $this->post('type', 'individual'),
            'civility' => $this->post('civility'),
            'first_name' => $this->post('first_name'),
            'last_name' => $this->post('last_name'),
            'company_name' => $this->post('company_name'),
            'siret' => $this->post('siret'),
            'address' => $this->post('address'),
            'postal_code' => $this->post('postal_code'),
            'city' => $this->post('city'),
            'country' => $this->post('country', 'France'),
            'phone' => $this->post('phone'),
            'mobile' => $this->post('mobile'),
            'email' => $this->post('email'),
            'notes' => $this->post('notes'),
            'status' => 'active'
        ];

        try {
            $clientId = $this->clientModel->create($clientData);
            $this->setFlash('success', 'Client créé avec succès');
            $this->redirect('/clients');
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la création du client');
            $this->redirect('/clients/create');
        }
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $companyId = $_SESSION['company_id'];
        $client = $this->clientModel->find($id, $companyId);

        if (!$client) {
            $this->setFlash('error', 'Client non trouvé');
            $this->redirect('/clients');
            return;
        }

        $this->view('clients.edit', ['client' => $client]);
    }

    /**
     * Mise à jour d'un client
     */
    public function update($id)
    {
        $companyId = $_SESSION['company_id'];

        $clientData = [
            'type' => $this->post('type'),
            'civility' => $this->post('civility'),
            'first_name' => $this->post('first_name'),
            'last_name' => $this->post('last_name'),
            'company_name' => $this->post('company_name'),
            'siret' => $this->post('siret'),
            'address' => $this->post('address'),
            'postal_code' => $this->post('postal_code'),
            'city' => $this->post('city'),
            'country' => $this->post('country'),
            'phone' => $this->post('phone'),
            'mobile' => $this->post('mobile'),
            'email' => $this->post('email'),
            'notes' => $this->post('notes')
        ];

        try {
            $this->clientModel->update($id, $clientData, $companyId);
            $this->setFlash('success', 'Client mis à jour avec succès');
            $this->redirect('/clients');
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la mise à jour');
            $this->redirect('/clients/edit/' . $id);
        }
    }
}
