<?php
/**
 * Contrôleur Rapports
 */
class RapportController extends Controller
{
    /**
     * Page des rapports
     */
    public function index()
    {
        $companyId = $_SESSION['company_id'];

        // Période par défaut : ce mois-ci
        $startDate = $this->get('start_date', date('Y-m-01'));
        $endDate = $this->get('end_date', date('Y-m-t'));

        // Statistiques de la période
        $stats = [
            'revenue' => $this->getRevenue($companyId, $startDate, $endDate),
            'expenses' => $this->getExpenses($companyId, $startDate, $endDate),
            'quotes_sent' => $this->getQuotesSent($companyId, $startDate, $endDate),
            'quotes_accepted' => $this->getQuotesAccepted($companyId, $startDate, $endDate),
            'invoices_paid' => $this->getInvoicesPaid($companyId, $startDate, $endDate),
        ];

        $stats['profit'] = $stats['revenue'] - $stats['expenses'];
        $stats['margin'] = $stats['revenue'] > 0 ? ($stats['profit'] / $stats['revenue'] * 100) : 0;

        // Top clients
        $topClients = $this->getTopClients($companyId, $startDate, $endDate);

        // Chantiers par statut
        $chantiersByStatus = $this->getChantiersByStatus($companyId);

        $this->view('rapports.index', [
            'stats' => $stats,
            'topClients' => $topClients,
            'chantiersByStatus' => $chantiersByStatus,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * Rapport de rentabilité
     */
    public function rentabilite()
    {
        $companyId = $_SESSION['company_id'];

        // Rentabilité par chantier
        $chantiers = $this->db->query(
            "SELECT ch.*, c.first_name, c.last_name, c.company_name, c.type as client_type
             FROM chantiers ch
             LEFT JOIN clients c ON ch.client_id = c.id
             WHERE ch.company_id = ?
             ORDER BY ch.start_date DESC",
            [$companyId]
        );

        // Calculer la rentabilité pour chaque chantier
        foreach ($chantiers as &$chantier) {
            $revenue = $chantier['estimated_budget'] ?? 0;
            $cost = $chantier['actual_cost'] ?? 0;
            $chantier['profit'] = $revenue - $cost;
            $chantier['margin'] = $revenue > 0 ? (($revenue - $cost) / $revenue * 100) : 0;
        }

        $this->view('rapports.rentabilite', ['chantiers' => $chantiers]);
    }

    // Méthodes privées pour les statistiques

    private function getRevenue($companyId, $startDate, $endDate)
    {
        $result = $this->db->queryOne(
            "SELECT SUM(paid_amount) as total
             FROM factures
             WHERE company_id = ? AND date BETWEEN ? AND ?",
            [$companyId, $startDate, $endDate]
        );
        return $result['total'] ?? 0;
    }

    private function getExpenses($companyId, $startDate, $endDate)
    {
        $result = $this->db->queryOne(
            "SELECT SUM(amount) as total
             FROM expenses
             WHERE company_id = ? AND date BETWEEN ? AND ?",
            [$companyId, $startDate, $endDate]
        );
        return $result['total'] ?? 0;
    }

    private function getQuotesSent($companyId, $startDate, $endDate)
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as total
             FROM devis
             WHERE company_id = ? AND date BETWEEN ? AND ? AND status != 'draft'",
            [$companyId, $startDate, $endDate]
        );
        return $result['total'] ?? 0;
    }

    private function getQuotesAccepted($companyId, $startDate, $endDate)
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as total
             FROM devis
             WHERE company_id = ? AND date BETWEEN ? AND ? AND status = 'accepted'",
            [$companyId, $startDate, $endDate]
        );
        return $result['total'] ?? 0;
    }

    private function getInvoicesPaid($companyId, $startDate, $endDate)
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as total
             FROM factures
             WHERE company_id = ? AND date BETWEEN ? AND ? AND status = 'paid'",
            [$companyId, $startDate, $endDate]
        );
        return $result['total'] ?? 0;
    }

    private function getTopClients($companyId, $startDate, $endDate)
    {
        return $this->db->query(
            "SELECT c.*, SUM(f.total) as total_amount
             FROM clients c
             INNER JOIN factures f ON c.id = f.client_id
             WHERE c.company_id = ? AND f.date BETWEEN ? AND ?
             GROUP BY c.id
             ORDER BY total_amount DESC
             LIMIT 10",
            [$companyId, $startDate, $endDate]
        );
    }

    private function getChantiersByStatus($companyId)
    {
        return $this->db->query(
            "SELECT status, COUNT(*) as count
             FROM chantiers
             WHERE company_id = ?
             GROUP BY status",
            [$companyId]
        );
    }
}
