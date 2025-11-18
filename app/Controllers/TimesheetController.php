<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Timesheet;
use App\Models\Chantier;
use App\Models\User;

class TimesheetController extends Controller {
    private $timesheetModel;
    private $chantierModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->timesheetModel = new Timesheet();
        $this->chantierModel = new Chantier();
        $this->userModel = new User();
    }

    /**
     * Liste des feuilles de temps
     */
    public function index() {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];
        $userId = $_GET['user_id'] ?? $_SESSION['user']['id'];
        $chantierId = $_GET['chantier_id'] ?? null;
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-t');

        $filters = [
            'company_id' => $companyId,
            'user_id' => $userId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];

        if ($chantierId) {
            $filters['chantier_id'] = $chantierId;
        }

        $timesheets = $this->timesheetModel->getAll($filters);
        $stats = $this->timesheetModel->getStats($userId, $dateFrom, $dateTo);

        // Liste des utilisateurs et chantiers pour les filtres
        $users = $this->userModel->getByCompany($companyId);
        $chantiers = $this->chantierModel->getByCompany($companyId);

        $this->render('timesheets/index', [
            'timesheets' => $timesheets,
            'stats' => $stats,
            'users' => $users,
            'chantiers' => $chantiers,
            'currentUserId' => $userId,
            'currentChantierId' => $chantierId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);
    }

    /**
     * Pointage - Clock in/out
     */
    public function clock() {
        $this->requireAuth();

        $userId = $_SESSION['user']['id'];
        $companyId = $_SESSION['user']['company_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action']; // clock_in ou clock_out

            if ($action === 'clock_in') {
                $data = [
                    'company_id' => $companyId,
                    'chantier_id' => $_POST['chantier_id'] ?? null,
                    'latitude' => $_POST['latitude'] ?? null,
                    'longitude' => $_POST['longitude'] ?? null,
                    'notes' => $_POST['notes'] ?? null
                ];

                $id = $this->timesheetModel->clockIn($userId, $companyId, $data);

                if ($id) {
                    $this->setFlash('success', 'Pointage d\'entrée enregistré');
                } else {
                    $this->setFlash('error', 'Erreur lors du pointage');
                }
            } else if ($action === 'clock_out') {
                $id = $_POST['timesheet_id'];
                $data = [
                    'latitude' => $_POST['latitude'] ?? null,
                    'longitude' => $_POST['longitude'] ?? null,
                    'break_minutes' => (int) ($_POST['break_minutes'] ?? 0),
                    'notes_out' => $_POST['notes_out'] ?? null
                ];

                if ($this->timesheetModel->clockOut($id, $data)) {
                    $this->setFlash('success', 'Pointage de sortie enregistré');
                } else {
                    $this->setFlash('error', 'Erreur lors du pointage');
                }
            }

            return $this->redirect('/timesheets/clock');
        }

        // Vérifier si l'utilisateur a un pointage en cours
        $activeTimesheet = $this->timesheetModel->getActiveTimesheet($userId);
        $chantiers = $this->chantierModel->getByCompany($companyId);

        $this->render('timesheets/clock', [
            'activeTimesheet' => $activeTimesheet,
            'chantiers' => $chantiers
        ]);
    }

    /**
     * Créer une feuille de temps manuellement
     */
    public function create() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyId = $_SESSION['user']['company_id'];
            $userId = $_POST['user_id'] ?? $_SESSION['user']['id'];

            $data = [
                'company_id' => $companyId,
                'user_id' => $userId,
                'chantier_id' => $_POST['chantier_id'] ?? null,
                'date' => $_POST['date'],
                'clock_in' => $_POST['clock_in'],
                'clock_out' => $_POST['clock_out'],
                'break_minutes' => (int) ($_POST['break_minutes'] ?? 0),
                'notes' => $_POST['notes'] ?? null
            ];

            // Calculer les heures
            $in = strtotime($data['date'] . ' ' . $data['clock_in']);
            $out = strtotime($data['date'] . ' ' . $data['clock_out']);
            $totalMinutes = ($out - $in) / 60;
            $workMinutes = $totalMinutes - $data['break_minutes'];
            $data['total_hours'] = round($workMinutes / 60, 2);

            $id = $this->timesheetModel->create($data);

            if ($id) {
                $this->setFlash('success', 'Feuille de temps créée');
                return $this->redirect('/timesheets/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de la création');
            }
        }

        $companyId = $_SESSION['user']['company_id'];
        $users = $this->userModel->getByCompany($companyId);
        $chantiers = $this->chantierModel->getByCompany($companyId);

        $this->render('timesheets/create', [
            'users' => $users,
            'chantiers' => $chantiers
        ]);
    }

    /**
     * Voir une feuille de temps
     */
    public function view($id) {
        $this->requireAuth();

        $timesheet = $this->timesheetModel->find($id);

        if (!$timesheet || $timesheet['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Feuille de temps introuvable');
            return $this->redirect('/timesheets');
        }

        $this->render('timesheets/view', [
            'timesheet' => $timesheet
        ]);
    }

    /**
     * Éditer une feuille de temps
     */
    public function edit($id) {
        $this->requireAuth();

        $timesheet = $this->timesheetModel->find($id);

        if (!$timesheet || $timesheet['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Feuille de temps introuvable');
            return $this->redirect('/timesheets');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'chantier_id' => $_POST['chantier_id'] ?? null,
                'date' => $_POST['date'],
                'clock_in' => $_POST['clock_in'],
                'clock_out' => $_POST['clock_out'],
                'break_minutes' => (int) ($_POST['break_minutes'] ?? 0),
                'notes' => $_POST['notes'] ?? null
            ];

            // Recalculer les heures
            $in = strtotime($data['date'] . ' ' . $data['clock_in']);
            $out = strtotime($data['date'] . ' ' . $data['clock_out']);
            $totalMinutes = ($out - $in) / 60;
            $workMinutes = $totalMinutes - $data['break_minutes'];
            $data['total_hours'] = round($workMinutes / 60, 2);

            if ($this->timesheetModel->update($id, $data)) {
                $this->setFlash('success', 'Feuille de temps mise à jour');
                return $this->redirect('/timesheets/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de la mise à jour');
            }
        }

        $companyId = $_SESSION['user']['company_id'];
        $chantiers = $this->chantierModel->getByCompany($companyId);

        $this->render('timesheets/edit', [
            'timesheet' => $timesheet,
            'chantiers' => $chantiers
        ]);
    }

    /**
     * Supprimer une feuille de temps
     */
    public function delete($id) {
        $this->requireAuth();

        $timesheet = $this->timesheetModel->find($id);

        if (!$timesheet || $timesheet['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Feuille de temps introuvable');
            return $this->redirect('/timesheets');
        }

        if ($this->timesheetModel->delete($id)) {
            $this->setFlash('success', 'Feuille de temps supprimée');
        } else {
            $this->setFlash('error', 'Erreur lors de la suppression');
        }

        return $this->redirect('/timesheets');
    }

    /**
     * Rapport hebdomadaire
     */
    public function weekly() {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];
        $userId = $_GET['user_id'] ?? null;
        $weekStart = $_GET['week'] ?? date('Y-m-d', strtotime('monday this week'));

        $report = $this->timesheetModel->getWeeklyReport($companyId, $userId, $weekStart);
        $users = $this->userModel->getByCompany($companyId);

        $this->render('timesheets/weekly', [
            'report' => $report,
            'users' => $users,
            'currentUserId' => $userId,
            'weekStart' => $weekStart
        ]);
    }

    /**
     * Rapport mensuel
     */
    public function monthly() {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];
        $userId = $_GET['user_id'] ?? null;
        $month = $_GET['month'] ?? date('Y-m');

        $report = $this->timesheetModel->getMonthlyReport($companyId, $userId, $month);
        $users = $this->userModel->getByCompany($companyId);

        $this->render('timesheets/monthly', [
            'report' => $report,
            'users' => $users,
            'currentUserId' => $userId,
            'month' => $month
        ]);
    }

    /**
     * Validation par manager
     */
    public function validate($id) {
        $this->requireAuth();

        $timesheet = $this->timesheetModel->find($id);

        if (!$timesheet || $timesheet['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Feuille de temps introuvable');
            return $this->redirect('/timesheets');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'validated' => (bool) $_POST['validated'],
                'validated_by' => $_SESSION['user']['id'],
                'validated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->timesheetModel->update($id, $data)) {
                $this->setFlash('success', 'Validation enregistrée');
            } else {
                $this->setFlash('error', 'Erreur lors de la validation');
            }

            return $this->redirect('/timesheets/view/' . $id);
        }
    }
}
