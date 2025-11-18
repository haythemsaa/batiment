<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Task;
use App\Models\Chantier;
use App\Models\User;

class TasksController extends Controller {
    private $taskModel;
    private $chantierModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->taskModel = new Task();
        $this->chantierModel = new Chantier();
        $this->userModel = new User();
    }

    /**
     * Liste toutes les tâches de l'utilisateur
     */
    public function index() {
        $this->requireAuth();

        $userId = $_SESSION['user_id'];
        $status = $_GET['status'] ?? null;

        $tasks = $this->taskModel->getByUser($userId, $status);
        $overdueTasks = $this->taskModel->getOverdue($_SESSION['company_id']);

        $this->view('tasks/index', [
            'tasks' => $tasks,
            'overdueTasks' => $overdueTasks,
            'currentStatus' => $status
        ]);
    }

    /**
     * Affiche les tâches d'un chantier
     */
    public function chantier($chantierId) {
        $this->requireAuth();

        $chantier = $this->chantierModel->find($chantierId);

        if (!$chantier || $chantier['company_id'] != $_SESSION['company_id']) {
            redirect('/chantiers');
        }

        $tasks = $this->taskModel->getByChantier($chantierId);
        $stats = $this->taskModel->getStatsForChantier($chantierId);
        $users = $this->userModel->getByCompany($_SESSION['company_id']);

        $this->view('tasks/chantier', [
            'chantier' => $chantier,
            'tasks' => $tasks,
            'stats' => $stats,
            'users' => $users
        ]);
    }

    /**
     * Formulaire de création de tâche
     */
    public function create() {
        $this->requireAuth();

        $chantierId = $_GET['chantier_id'] ?? null;
        $chantier = null;

        if ($chantierId) {
            $chantier = $this->chantierModel->find($chantierId);
            if (!$chantier || $chantier['company_id'] != $_SESSION['company_id']) {
                redirect('/tasks');
            }
        }

        $chantiers = $this->chantierModel->getByCompany($_SESSION['company_id']);
        $users = $this->userModel->getByCompany($_SESSION['company_id']);

        $this->view('tasks/create', [
            'chantier' => $chantier,
            'chantiers' => $chantiers,
            'users' => $users
        ]);
    }

    /**
     * Enregistre une nouvelle tâche
     */
    public function store() {
        $this->requireAuth();

        $data = [
            'company_id' => $_SESSION['company_id'],
            'chantier_id' => $_POST['chantier_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? null,
            'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null,
            'priority' => $_POST['priority'] ?? 'medium',
            'status' => $_POST['status'] ?? 'todo',
            'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null
        ];

        // Vérifier que le chantier appartient à l'entreprise
        $chantier = $this->chantierModel->find($data['chantier_id']);
        if (!$chantier || $chantier['company_id'] != $_SESSION['company_id']) {
            $_SESSION['error'] = "Chantier invalide";
            redirect('/tasks/create');
        }

        $id = $this->taskModel->create($data);

        $_SESSION['success'] = "Tâche créée avec succès";
        redirect('/tasks/chantier/' . $data['chantier_id']);
    }

    /**
     * Formulaire d'édition de tâche
     */
    public function edit($id) {
        $this->requireAuth();

        $task = $this->taskModel->find($id);

        if (!$task || $task['company_id'] != $_SESSION['company_id']) {
            redirect('/tasks');
        }

        $chantiers = $this->chantierModel->getByCompany($_SESSION['company_id']);
        $users = $this->userModel->getByCompany($_SESSION['company_id']);

        $this->view('tasks/edit', [
            'task' => $task,
            'chantiers' => $chantiers,
            'users' => $users
        ]);
    }

    /**
     * Met à jour une tâche
     */
    public function update($id) {
        $this->requireAuth();

        $task = $this->taskModel->find($id);

        if (!$task || $task['company_id'] != $_SESSION['company_id']) {
            redirect('/tasks');
        }

        $data = [
            'chantier_id' => $_POST['chantier_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? null,
            'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null,
            'priority' => $_POST['priority'] ?? 'medium',
            'status' => $_POST['status'] ?? 'todo',
            'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null
        ];

        // Si la tâche passe à "completed", enregistrer la date
        if ($data['status'] === 'completed' && $task['status'] !== 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        $this->taskModel->update($id, $data);

        $_SESSION['success'] = "Tâche mise à jour avec succès";
        redirect('/tasks/chantier/' . $task['chantier_id']);
    }

    /**
     * Marque une tâche comme complétée (AJAX)
     */
    public function complete($id) {
        $this->requireAuth();

        $task = $this->taskModel->find($id);

        if (!$task || $task['company_id'] != $_SESSION['company_id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
            exit;
        }

        $this->taskModel->complete($id);

        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Change le statut d'une tâche (AJAX)
     */
    public function updateStatus($id) {
        $this->requireAuth();

        $task = $this->taskModel->find($id);

        if (!$task || $task['company_id'] != $_SESSION['company_id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
            exit;
        }

        $status = $_POST['status'] ?? null;
        $validStatuses = ['todo', 'in_progress', 'completed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status']);
            exit;
        }

        $data = ['status' => $status];

        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        $this->taskModel->update($id, $data);

        echo json_encode(['success' => true, 'status' => $status]);
        exit;
    }

    /**
     * Supprime une tâche
     */
    public function delete($id) {
        $this->requireAuth();

        $task = $this->taskModel->find($id);

        if (!$task || $task['company_id'] != $_SESSION['company_id']) {
            redirect('/tasks');
        }

        $chantierId = $task['chantier_id'];

        $this->taskModel->delete($id);

        $_SESSION['success'] = "Tâche supprimée avec succès";
        redirect('/tasks/chantier/' . $chantierId);
    }
}
