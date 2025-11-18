<?php
/**
 * Contrôleur Dashboard
 */
class DashboardController extends Controller
{
    /**
     * Tableau de bord principal
     */
    public function index()
    {
        $companyId = $_SESSION['company_id'];

        // Statistiques
        $stats = [
            'devis' => [
                'total' => $this->getDevisStats($companyId),
                'sent' => $this->getDevisStats($companyId, 'sent'),
                'accepted' => $this->getDevisStats($companyId, 'accepted'),
                'amount' => $this->getDevisTotalAmount($companyId)
            ],
            'factures' => [
                'total' => $this->getFacturesStats($companyId),
                'paid' => $this->getFacturesStats($companyId, 'paid'),
                'overdue' => $this->getOverdueInvoices($companyId),
                'amount' => $this->getFacturesTotalAmount($companyId),
                'unpaid_amount' => $this->getUnpaidAmount($companyId)
            ],
            'chantiers' => [
                'total' => $this->getChantiersStats($companyId),
                'in_progress' => $this->getChantiersStats($companyId, 'in_progress'),
                'completed' => $this->getChantiersStats($companyId, 'completed')
            ],
            'clients' => [
                'total' => $this->getClientsStats($companyId)
            ]
        ];

        // Activités récentes
        $recentDevis = $this->getRecentDevis($companyId);
        $recentFactures = $this->getRecentFactures($companyId);
        $recentChantiers = $this->getRecentChantiers($companyId);

        // Chantiers en cours
        $chantiersEnCours = $this->getChantiersEnCours($companyId);

        $this->view('dashboard.index', [
            'stats' => $stats,
            'recentDevis' => $recentDevis,
            'recentFactures' => $recentFactures,
            'recentChantiers' => $recentChantiers,
            'chantiersEnCours' => $chantiersEnCours
        ]);
    }

    private function getDevisStats($companyId, $status = null)
    {
        $sql = "SELECT COUNT(*) as total FROM devis WHERE company_id = ?";
        $params = [$companyId];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    private function getDevisTotalAmount($companyId)
    {
        $result = $this->db->queryOne(
            "SELECT SUM(total) as amount FROM devis WHERE company_id = ? AND status = 'accepted'",
            [$companyId]
        );
        return $result['amount'] ?? 0;
    }

    private function getFacturesStats($companyId, $status = null)
    {
        $sql = "SELECT COUNT(*) as total FROM factures WHERE company_id = ?";
        $params = [$companyId];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    private function getFacturesTotalAmount($companyId)
    {
        $result = $this->db->queryOne(
            "SELECT SUM(total) as amount FROM factures WHERE company_id = ?",
            [$companyId]
        );
        return $result['amount'] ?? 0;
    }

    private function getUnpaidAmount($companyId)
    {
        $result = $this->db->queryOne(
            "SELECT SUM(remaining_amount) as amount FROM factures
             WHERE company_id = ? AND status != 'paid' AND status != 'cancelled'",
            [$companyId]
        );
        return $result['amount'] ?? 0;
    }

    private function getOverdueInvoices($companyId)
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as total FROM factures
             WHERE company_id = ? AND due_date < CURDATE() AND status != 'paid' AND status != 'cancelled'",
            [$companyId]
        );
        return $result['total'] ?? 0;
    }

    private function getChantiersStats($companyId, $status = null)
    {
        $sql = "SELECT COUNT(*) as total FROM chantiers WHERE company_id = ?";
        $params = [$companyId];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $result = $this->db->queryOne($sql, $params);
        return $result['total'] ?? 0;
    }

    private function getClientsStats($companyId)
    {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as total FROM clients WHERE company_id = ?",
            [$companyId]
        );
        return $result['total'] ?? 0;
    }

    private function getRecentDevis($companyId, $limit = 5)
    {
        return $this->db->query(
            "SELECT d.*, c.first_name, c.last_name, c.company_name, c.type as client_type
             FROM devis d
             LEFT JOIN clients c ON d.client_id = c.id
             WHERE d.company_id = ?
             ORDER BY d.created_at DESC
             LIMIT ?",
            [$companyId, $limit]
        );
    }

    private function getRecentFactures($companyId, $limit = 5)
    {
        return $this->db->query(
            "SELECT f.*, c.first_name, c.last_name, c.company_name, c.type as client_type
             FROM factures f
             LEFT JOIN clients c ON f.client_id = c.id
             WHERE f.company_id = ?
             ORDER BY f.created_at DESC
             LIMIT ?",
            [$companyId, $limit]
        );
    }

    private function getRecentChantiers($companyId, $limit = 5)
    {
        return $this->db->query(
            "SELECT ch.*, c.first_name, c.last_name, c.company_name, c.type as client_type
             FROM chantiers ch
             LEFT JOIN clients c ON ch.client_id = c.id
             WHERE ch.company_id = ?
             ORDER BY ch.created_at DESC
             LIMIT ?",
            [$companyId, $limit]
        );
    }

    private function getChantiersEnCours($companyId)
    {
        return $this->db->query(
            "SELECT ch.*, c.first_name, c.last_name, c.company_name, c.type as client_type
             FROM chantiers ch
             LEFT JOIN clients c ON ch.client_id = c.id
             WHERE ch.company_id = ? AND ch.status = 'in_progress'
             ORDER BY ch.start_date",
            [$companyId]
        );
    }
}
