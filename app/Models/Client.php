<?php
/**
 * Modèle Client
 */
class Client extends Model
{
    protected $table = 'clients';

    /**
     * Récupère le nom complet du client
     */
    public function getFullName($client)
    {
        if ($client['type'] === 'company') {
            return $client['company_name'];
        }

        return trim($client['first_name'] . ' ' . $client['last_name']);
    }

    /**
     * Recherche des clients
     */
    public function search($query, $companyId)
    {
        $searchTerm = "%{$query}%";

        return $this->db->query(
            "SELECT * FROM clients
             WHERE company_id = ?
             AND (first_name LIKE ? OR last_name LIKE ? OR company_name LIKE ? OR email LIKE ?)
             ORDER BY last_name, first_name",
            [$companyId, $searchTerm, $searchTerm, $searchTerm, $searchTerm]
        );
    }
}
