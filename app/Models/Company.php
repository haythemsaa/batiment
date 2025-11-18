<?php
/**
 * Modèle Company
 */
class Company extends Model
{
    protected $table = 'companies';
    protected $multiTenant = false;

    /**
     * Vérifie si l'abonnement est actif
     */
    public function hasActiveSubscription($companyId)
    {
        $company = $this->find($companyId);

        if (!$company) {
            return false;
        }

        if ($company['status'] !== 'active') {
            return false;
        }

        if ($company['subscription_expires_at'] && strtotime($company['subscription_expires_at']) < time()) {
            return false;
        }

        return true;
    }

    /**
     * Récupère les paramètres de l'entreprise
     */
    public function getSettings($companyId)
    {
        $settings = $this->db->query(
            "SELECT setting_key, setting_value FROM settings WHERE company_id = ?",
            [$companyId]
        );

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }

        return $result;
    }
}
