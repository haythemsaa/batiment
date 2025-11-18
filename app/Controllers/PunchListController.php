<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PunchList;
use App\Models\Chantier;
use App\Models\User;

class PunchListController extends Controller {
    private $punchListModel;
    private $chantierModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->punchListModel = new PunchList();
        $this->chantierModel = new Chantier();
        $this->userModel = new User();
    }

    /**
     * Liste des réserves
     */
    public function index() {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];
        $chantierId = $_GET['chantier_id'] ?? null;
        $status = $_GET['status'] ?? null;
        $priority = $_GET['priority'] ?? null;

        $filters = ['company_id' => $companyId];
        if ($chantierId) $filters['chantier_id'] = $chantierId;
        if ($status) $filters['status'] = $status;
        if ($priority) $filters['priority'] = $priority;

        $punchLists = $this->punchListModel->getAll($filters);
        $chantiers = $this->chantierModel->getByCompany($companyId);

        // Statistiques
        $stats = $this->punchListModel->getStats($companyId, $chantierId);

        $this->render('punch_lists/index', [
            'punchLists' => $punchLists,
            'chantiers' => $chantiers,
            'stats' => $stats,
            'currentChantierId' => $chantierId,
            'currentStatus' => $status,
            'currentPriority' => $priority
        ]);
    }

    /**
     * Créer une réserve
     */
    public function create() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyId = $_SESSION['user']['company_id'];
            $userId = $_SESSION['user']['id'];

            $data = [
                'company_id' => $companyId,
                'chantier_id' => (int) $_POST['chantier_id'],
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'category' => $_POST['category'],
                'priority' => $_POST['priority'],
                'zone' => $_POST['zone'] ?? null,
                'etage' => $_POST['etage'] ?? null,
                'assigned_to' => $_POST['assigned_to'] ?? null,
                'due_date' => $_POST['due_date'] ?? null,
                'created_by' => $userId
            ];

            // Gérer les photos
            if (isset($_FILES['photos']) && $_FILES['photos']['error'][0] === UPLOAD_ERR_OK) {
                $photos = [];
                foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {
                    $filename = uniqid() . '_' . $_FILES['photos']['name'][$key];
                    $uploadPath = __DIR__ . '/../../public/uploads/punch_lists/' . $filename;

                    if (!is_dir(dirname($uploadPath))) {
                        mkdir(dirname($uploadPath), 0755, true);
                    }

                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        $photos[] = $filename;
                    }
                }
                $data['photos'] = json_encode($photos);
            }

            $id = $this->punchListModel->create($data);

            if ($id) {
                $this->setFlash('success', 'Réserve créée avec succès');
                return $this->redirect('/punch-lists/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de la création');
            }
        }

        $companyId = $_SESSION['user']['company_id'];
        $chantiers = $this->chantierModel->getByCompany($companyId);
        $users = $this->userModel->getByCompany($companyId);

        $this->render('punch_lists/create', [
            'chantiers' => $chantiers,
            'users' => $users
        ]);
    }

    /**
     * Voir une réserve
     */
    public function view($id) {
        $this->requireAuth();

        $punchList = $this->punchListModel->find($id);

        if (!$punchList || $punchList['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Réserve introuvable');
            return $this->redirect('/punch-lists');
        }

        $this->render('punch_lists/view', [
            'punchList' => $punchList
        ]);
    }

    /**
     * Éditer une réserve
     */
    public function edit($id) {
        $this->requireAuth();

        $punchList = $this->punchListModel->find($id);

        if (!$punchList || $punchList['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Réserve introuvable');
            return $this->redirect('/punch-lists');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'category' => $_POST['category'],
                'priority' => $_POST['priority'],
                'zone' => $_POST['zone'] ?? null,
                'etage' => $_POST['etage'] ?? null,
                'assigned_to' => $_POST['assigned_to'] ?? null,
                'due_date' => $_POST['due_date'] ?? null
            ];

            if ($this->punchListModel->update($id, $data)) {
                $this->setFlash('success', 'Réserve mise à jour');
                return $this->redirect('/punch-lists/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de la mise à jour');
            }
        }

        $companyId = $_SESSION['user']['company_id'];
        $users = $this->userModel->getByCompany($companyId);

        $this->render('punch_lists/edit', [
            'punchList' => $punchList,
            'users' => $users
        ]);
    }

    /**
     * Supprimer une réserve
     */
    public function delete($id) {
        $this->requireAuth();

        $punchList = $this->punchListModel->find($id);

        if (!$punchList || $punchList['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Réserve introuvable');
            return $this->redirect('/punch-lists');
        }

        if ($this->punchListModel->delete($id)) {
            $this->setFlash('success', 'Réserve supprimée');
        } else {
            $this->setFlash('error', 'Erreur lors de la suppression');
        }

        return $this->redirect('/punch-lists?chantier_id=' . $punchList['chantier_id']);
    }

    /**
     * Résoudre une réserve
     */
    public function resolve($id) {
        $this->requireAuth();

        $punchList = $this->punchListModel->find($id);

        if (!$punchList || $punchList['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Réserve introuvable');
            return $this->redirect('/punch-lists');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'resolution_notes' => $_POST['resolution_notes'],
                'resolved_by' => $_SESSION['user']['id']
            ];

            // Gérer les photos de résolution
            if (isset($_FILES['resolution_photos']) && $_FILES['resolution_photos']['error'][0] === UPLOAD_ERR_OK) {
                $photos = [];
                foreach ($_FILES['resolution_photos']['tmp_name'] as $key => $tmpName) {
                    $filename = uniqid() . '_' . $_FILES['resolution_photos']['name'][$key];
                    $uploadPath = __DIR__ . '/../../public/uploads/punch_lists/' . $filename;

                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        $photos[] = $filename;
                    }
                }
                $data['resolution_photos'] = json_encode($photos);
            }

            if ($this->punchListModel->resolve($id, $data)) {
                $this->setFlash('success', 'Réserve marquée comme résolue');
            } else {
                $this->setFlash('error', 'Erreur lors de la résolution');
            }

            return $this->redirect('/punch-lists/view/' . $id);
        }

        $this->render('punch_lists/resolve', [
            'punchList' => $punchList
        ]);
    }

    /**
     * Vérifier une réserve
     */
    public function verify($id) {
        $this->requireAuth();

        $punchList = $this->punchListModel->find($id);

        if (!$punchList || $punchList['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Réserve introuvable');
            return $this->redirect('/punch-lists');
        }

        if ($punchList['status'] !== 'resolved') {
            $this->setFlash('error', 'La réserve doit être résolue avant d\'être vérifiée');
            return $this->redirect('/punch-lists/view/' . $id);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'verified_by' => $_SESSION['user']['id']
            ];

            if ($this->punchListModel->verify($id, $data)) {
                $this->setFlash('success', 'Réserve vérifiée');
            } else {
                $this->setFlash('error', 'Erreur lors de la vérification');
            }

            return $this->redirect('/punch-lists/view/' . $id);
        }
    }

    /**
     * Clôturer une réserve
     */
    public function close($id) {
        $this->requireAuth();

        $punchList = $this->punchListModel->find($id);

        if (!$punchList || $punchList['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Réserve introuvable');
            return $this->redirect('/punch-lists');
        }

        if ($punchList['status'] !== 'verified') {
            $this->setFlash('error', 'La réserve doit être vérifiée avant d\'être clôturée');
            return $this->redirect('/punch-lists/view/' . $id);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->punchListModel->close($id)) {
                $this->setFlash('success', 'Réserve clôturée');
            } else {
                $this->setFlash('error', 'Erreur lors de la clôture');
            }

            return $this->redirect('/punch-lists/view/' . $id);
        }
    }

    /**
     * Réouvrir une réserve
     */
    public function reopen($id) {
        $this->requireAuth();

        $punchList = $this->punchListModel->find($id);

        if (!$punchList || $punchList['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Réserve introuvable');
            return $this->redirect('/punch-lists');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'status' => 'open',
                'resolved_at' => null,
                'resolved_by' => null,
                'verified_at' => null,
                'verified_by' => null,
                'closed_at' => null
            ];

            if ($this->punchListModel->update($id, $data)) {
                $this->setFlash('success', 'Réserve réouverte');
            } else {
                $this->setFlash('error', 'Erreur lors de la réouverture');
            }

            return $this->redirect('/punch-lists/view/' . $id);
        }
    }

    /**
     * Liste des réserves d'un chantier
     */
    public function chantier($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/punch-lists');
        }

        $punchLists = $this->punchListModel->getAll([
            'company_id' => $companyId,
            'chantier_id' => $chantierId
        ]);

        $stats = $this->punchListModel->getStats($companyId, $chantierId);

        $this->render('punch_lists/chantier', [
            'chantier' => $chantier,
            'punchLists' => $punchLists,
            'stats' => $stats
        ]);
    }

    /**
     * Export PDF des réserves
     */
    public function export($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/punch-lists');
        }

        $punchLists = $this->punchListModel->getAll([
            'company_id' => $companyId,
            'chantier_id' => $chantierId
        ]);

        // Générer le PDF
        $this->render('punch_lists/export_pdf', [
            'chantier' => $chantier,
            'punchLists' => $punchLists
        ]);
    }
}
