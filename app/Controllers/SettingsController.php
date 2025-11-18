<?php
/**
 * Contrôleur Settings
 */
class SettingsController extends Controller
{
    /**
     * Page des paramètres
     */
    public function index()
    {
        $company = $this->getCurrentCompany();
        $settings = $this->getSettings($_SESSION['company_id']);

        $this->view('settings.index', [
            'company' => $company,
            'settings' => $settings
        ]);
    }

    /**
     * Mise à jour des paramètres
     */
    public function update()
    {
        $companyId = $_SESSION['company_id'];

        $this->db->beginTransaction();

        try {
            // Mise à jour des informations de l'entreprise
            $this->db->execute(
                "UPDATE companies SET
                    name = ?,
                    siret = ?,
                    address = ?,
                    postal_code = ?,
                    city = ?,
                    phone = ?,
                    email = ?,
                    tva_number = ?,
                    tva_rate = ?,
                    capital = ?
                 WHERE id = ?",
                [
                    $this->post('name'),
                    $this->post('siret'),
                    $this->post('address'),
                    $this->post('postal_code'),
                    $this->post('city'),
                    $this->post('phone'),
                    $this->post('email'),
                    $this->post('tva_number'),
                    $this->post('tva_rate', 20),
                    $this->post('capital'),
                    $companyId
                ]
            );

            // Mise à jour des paramètres supplémentaires
            $settings = [
                'invoice_prefix' => $this->post('invoice_prefix', 'FA'),
                'quote_prefix' => $this->post('quote_prefix', 'DEV'),
                'invoice_footer' => $this->post('invoice_footer'),
                'payment_terms' => $this->post('payment_terms', '30'),
            ];

            foreach ($settings as $key => $value) {
                $this->db->execute(
                    "INSERT INTO settings (company_id, setting_key, setting_value)
                     VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE setting_value = ?",
                    [$companyId, $key, $value, $value]
                );
            }

            $this->db->commit();
            $this->setFlash('success', 'Paramètres mis à jour avec succès');
        } catch (Exception $e) {
            $this->db->rollback();
            $this->setFlash('error', 'Erreur lors de la mise à jour des paramètres');
        }

        $this->redirect('/settings');
    }

    /**
     * Récupère les paramètres
     */
    private function getSettings($companyId)
    {
        $results = $this->db->query(
            "SELECT setting_key, setting_value FROM settings WHERE company_id = ?",
            [$companyId]
        );

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }
}
