<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Chantier;

class PlanningController extends Controller {
    private $chantierModel;

    public function __construct() {
        parent::__construct();
        $this->chantierModel = new Chantier();
    }

    /**
     * Vue Gantt des chantiers
     */
    public function gantt() {
        $this->requireAuth();

        $chantiers = $this->chantierModel->getByCompany($_SESSION['company_id']);

        $this->view('planning/gantt', [
            'chantiers' => $chantiers
        ]);
    }

    /**
     * API pour récupérer les données Gantt (JSON)
     */
    public function ganttData() {
        $this->requireAuth();

        $chantiers = $this->chantierModel->getByCompany($_SESSION['company_id']);

        // Préparer les données pour le Gantt
        $tasks = [];
        foreach ($chantiers as $chantier) {
            $tasks[] = [
                'id' => $chantier['id'],
                'name' => $chantier['name'],
                'start_date' => $chantier['start_date'],
                'end_date' => $chantier['end_date'],
                'progress' => $chantier['progress'],
                'status' => $chantier['status'],
                'client_name' => $chantier['client_name'] ?? '',
                'estimated_budget' => $chantier['estimated_budget'],
                'dependencies' => json_decode($chantier['dependencies'] ?? '[]', true)
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($tasks);
        exit;
    }

    /**
     * Vue calendrier mensuel
     */
    public function calendar() {
        $this->requireAuth();

        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        // Récupérer les chantiers du mois
        $stmt = $this->db->prepare("
            SELECT c.*, cl.name as client_name
            FROM chantiers c
            LEFT JOIN clients cl ON c.client_id = cl.id
            WHERE c.company_id = ?
              AND (
                  (YEAR(c.start_date) = ? AND MONTH(c.start_date) = ?)
                  OR (YEAR(c.end_date) = ? AND MONTH(c.end_date) = ?)
                  OR (c.start_date <= ? AND c.end_date >= ?)
              )
            ORDER BY c.start_date ASC
        ");

        $firstDay = "$year-$month-01";
        $lastDay = date('Y-m-t', strtotime($firstDay));

        $stmt->execute([
            $_SESSION['company_id'],
            $year, $month,
            $year, $month,
            $lastDay, $firstDay
        ]);

        $chantiers = $stmt->fetchAll();

        $this->view('planning/calendar', [
            'chantiers' => $chantiers,
            'month' => $month,
            'year' => $year
        ]);
    }

    /**
     * Planning des ressources (équipes/utilisateurs)
     */
    public function resources() {
        $this->requireAuth();

        // Récupérer tous les utilisateurs
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE company_id = ? AND status = 'active'
        ");
        $stmt->execute([$_SESSION['company_id']]);
        $users = $stmt->fetchAll();

        // Pour chaque utilisateur, récupérer ses tâches assignées
        $resourcePlanning = [];
        foreach ($users as $user) {
            $stmt = $this->db->prepare("
                SELECT t.*, c.name as chantier_name
                FROM tasks t
                LEFT JOIN chantiers c ON t.chantier_id = c.id
                WHERE t.assigned_to = ?
                  AND t.status NOT IN ('completed', 'cancelled')
                ORDER BY t.due_date ASC
            ");
            $stmt->execute([$user['id']]);

            $resourcePlanning[] = [
                'user' => $user,
                'tasks' => $stmt->fetchAll()
            ];
        }

        $this->view('planning/resources', [
            'resourcePlanning' => $resourcePlanning
        ]);
    }

    /**
     * Vue timeline (ligne du temps)
     */
    public function timeline() {
        $this->requireAuth();

        $chantiers = $this->chantierModel->getByCompany($_SESSION['company_id']);

        // Grouper par statut
        $byStatus = [
            'planifie' => [],
            'en_cours' => [],
            'termine' => []
        ];

        foreach ($chantiers as $chantier) {
            if (isset($byStatus[$chantier['status']])) {
                $byStatus[$chantier['status']][] = $chantier;
            }
        }

        $this->view('planning/timeline', [
            'chantiers' => $chantiers,
            'byStatus' => $byStatus
        ]);
    }
}
