<?php
/**
 * Contrôleur Fournisseur
 */
class FournisseurController extends Controller
{
    /**
     * Liste des fournisseurs
     */
    public function index()
    {
        $companyId = $_SESSION['company_id'];
        $fournisseurs = $this->db->query(
            "SELECT * FROM fournisseurs WHERE company_id = ? ORDER BY name",
            [$companyId]
        );

        $this->view('fournisseurs.index', ['fournisseurs' => $fournisseurs]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $this->view('fournisseurs.create');
    }

    /**
     * Enregistrement d'un fournisseur
     */
    public function store()
    {
        $companyId = $_SESSION['company_id'];

        $data = [
            'company_id' => $companyId,
            'name' => $this->post('name'),
            'siret' => $this->post('siret'),
            'address' => $this->post('address'),
            'postal_code' => $this->post('postal_code'),
            'city' => $this->post('city'),
            'country' => $this->post('country', 'France'),
            'phone' => $this->post('phone'),
            'email' => $this->post('email'),
            'website' => $this->post('website'),
            'contact_name' => $this->post('contact_name'),
            'notes' => $this->post('notes'),
            'status' => 'active'
        ];

        try {
            $this->db->execute(
                "INSERT INTO fournisseurs (company_id, name, siret, address, postal_code, city, country, phone, email, website, contact_name, notes, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                array_values($data)
            );

            $this->setFlash('success', 'Fournisseur créé avec succès');
            $this->redirect('/fournisseurs');
        } catch (Exception $e) {
            $this->setFlash('error', 'Erreur lors de la création du fournisseur');
            $this->redirect('/fournisseurs/create');
        }
    }
}
