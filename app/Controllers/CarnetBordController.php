<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CarnetBord;
use App\Models\Chantier;
use App\Models\User;

class CarnetBordController extends Controller {
    private $carnetBordModel;
    private $chantierModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->carnetBordModel = new CarnetBord();
        $this->chantierModel = new Chantier();
        $this->userModel = new User();
    }

    /**
     * Carnet de bord d'un chantier
     */
    public function index($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/chantiers');
        }

        $entries = $this->carnetBordModel->getByChantier($chantierId);

        $this->render('carnet_bord/index', [
            'chantier' => $chantier,
            'entries' => $entries
        ]);
    }

    /**
     * Créer/éditer l'entrée du jour
     */
    public function today($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];
        $userId = $_SESSION['user']['id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/chantiers');
        }

        $today = date('Y-m-d');
        $entry = $this->carnetBordModel->getByDate($chantierId, $today);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'weather' => $_POST['weather'] ?? null,
                'temperature' => $_POST['temperature'] ?? null,
                'present_workers' => $_POST['present_workers'] ?? null,
                'work_done' => $_POST['work_done'],
                'materials_used' => $_POST['materials_used'] ?? null,
                'incidents' => $_POST['incidents'] ?? null,
                'created_by' => $userId
            ];

            // Gérer les photos
            if (isset($_FILES['photos']) && $_FILES['photos']['error'][0] === UPLOAD_ERR_OK) {
                $photos = [];
                foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {
                    $filename = uniqid() . '_' . $_FILES['photos']['name'][$key];
                    $uploadPath = __DIR__ . '/../../public/uploads/carnet_bord/' . $filename;

                    if (!is_dir(dirname($uploadPath))) {
                        mkdir(dirname($uploadPath), 0755, true);
                    }

                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        $photos[] = $filename;
                    }
                }
                $data['photos'] = json_encode($photos);
            }

            $id = $this->carnetBordModel->createOrUpdate($chantierId, $companyId, $today, $data);

            if ($id) {
                $this->setFlash('success', 'Entrée du jour enregistrée');
                return $this->redirect('/carnet-bord/' . $chantierId);
            } else {
                $this->setFlash('error', 'Erreur lors de l\'enregistrement');
            }
        }

        $this->render('carnet_bord/today', [
            'chantier' => $chantier,
            'entry' => $entry,
            'date' => $today
        ]);
    }

    /**
     * Voir une entrée spécifique
     */
    public function view($id) {
        $this->requireAuth();

        $entry = $this->carnetBordModel->find($id);

        if (!$entry || $entry['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Entrée introuvable');
            return $this->redirect('/chantiers');
        }

        $chantier = $this->chantierModel->find($entry['chantier_id']);

        $this->render('carnet_bord/view', [
            'entry' => $entry,
            'chantier' => $chantier
        ]);
    }

    /**
     * Éditer une entrée
     */
    public function edit($id) {
        $this->requireAuth();

        $entry = $this->carnetBordModel->find($id);

        if (!$entry || $entry['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Entrée introuvable');
            return $this->redirect('/chantiers');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'weather' => $_POST['weather'] ?? null,
                'temperature' => $_POST['temperature'] ?? null,
                'present_workers' => $_POST['present_workers'] ?? null,
                'work_done' => $_POST['work_done'],
                'materials_used' => $_POST['materials_used'] ?? null,
                'incidents' => $_POST['incidents'] ?? null
            ];

            if ($this->carnetBordModel->update($id, $data)) {
                $this->setFlash('success', 'Entrée mise à jour');
                return $this->redirect('/carnet-bord/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de la mise à jour');
            }
        }

        $chantier = $this->chantierModel->find($entry['chantier_id']);

        $this->render('carnet_bord/edit', [
            'entry' => $entry,
            'chantier' => $chantier
        ]);
    }

    /**
     * Supprimer une entrée
     */
    public function delete($id) {
        $this->requireAuth();

        $entry = $this->carnetBordModel->find($id);

        if (!$entry || $entry['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Entrée introuvable');
            return $this->redirect('/chantiers');
        }

        $chantierId = $entry['chantier_id'];

        if ($this->carnetBordModel->delete($id)) {
            $this->setFlash('success', 'Entrée supprimée');
        } else {
            $this->setFlash('error', 'Erreur lors de la suppression');
        }

        return $this->redirect('/carnet-bord/' . $chantierId);
    }

    /**
     * Rapport hebdomadaire
     */
    public function weekly($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/chantiers');
        }

        $weekStart = $_GET['week'] ?? date('Y-m-d', strtotime('monday this week'));
        $entries = $this->carnetBordModel->getWeeklyReport($chantierId, $weekStart);

        $this->render('carnet_bord/weekly', [
            'chantier' => $chantier,
            'entries' => $entries,
            'weekStart' => $weekStart
        ]);
    }

    /**
     * Export PDF du carnet de bord
     */
    public function export($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/chantiers');
        }

        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-t');

        $entries = $this->carnetBordModel->getAll([
            'company_id' => $companyId,
            'chantier_id' => $chantierId
        ]);

        // Filtrer par dates
        $entries = array_filter($entries, function($entry) use ($dateFrom, $dateTo) {
            return $entry['date'] >= $dateFrom && $entry['date'] <= $dateTo;
        });

        $this->render('carnet_bord/export_pdf', [
            'chantier' => $chantier,
            'entries' => $entries,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ]);
    }

    /**
     * Calendrier du carnet de bord
     */
    public function calendar($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/chantiers');
        }

        $month = $_GET['month'] ?? date('Y-m');
        $entries = $this->carnetBordModel->getByChantier($chantierId, 365);

        // Grouper par date
        $entriesByDate = [];
        foreach ($entries as $entry) {
            $entriesByDate[$entry['date']] = $entry;
        }

        $this->render('carnet_bord/calendar', [
            'chantier' => $chantier,
            'entriesByDate' => $entriesByDate,
            'month' => $month
        ]);
    }
}
