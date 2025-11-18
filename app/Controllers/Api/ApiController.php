<?php
/**
 * Contrôleur de base pour l'API REST
 * Gère les réponses JSON standardisées et l'authentification
 */

namespace App\Controllers\Api;

use App\Core\Controller;

class ApiController extends Controller {
    protected $user;
    protected $company_id;

    public function __construct() {
        // Définir les headers CORS et JSON
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Gérer les requêtes OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // Authentifier l'utilisateur
        $this->authenticateRequest();
    }

    /**
     * Authentifie la requête via token Bearer
     */
    protected function authenticateRequest() {
        $headers = getallheaders();
        $token = null;

        // Récupérer le token depuis le header Authorization
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                $token = $matches[1];
            }
        }

        // Vérifier le token dans la base de données
        if ($token) {
            $stmt = $this->db->prepare("
                SELECT u.*, u.company_id
                FROM users u
                WHERE u.api_token = ? AND u.status = 'active'
            ");
            $stmt->execute([$token]);
            $this->user = $stmt->fetch();

            if ($this->user) {
                $this->company_id = $this->user['company_id'];
                return;
            }
        }

        // Si pas de token valide, retourner erreur 401
        $this->error('Unauthorized. Valid API token required.', 401);
    }

    /**
     * Retourne une réponse JSON de succès
     */
    protected function success($data = null, $message = 'Success', $code = 200) {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Retourne une réponse JSON d'erreur
     */
    protected function error($message = 'Error', $code = 400, $errors = null) {
        http_response_code($code);
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('c')
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Retourne une réponse paginée
     */
    protected function paginate($data, $total, $page = 1, $perPage = 20) {
        $totalPages = ceil($total / $perPage);

        $this->success([
            'items' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages
            ]
        ]);
    }

    /**
     * Valide les champs requis dans les données
     */
    protected function validateRequired($data, $required) {
        $errors = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = "Le champ $field est requis";
            }
        }

        if (!empty($errors)) {
            $this->error('Validation failed', 422, $errors);
        }
    }

    /**
     * Récupère les données JSON du body de la requête
     */
    protected function getJsonInput() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON: ' . json_last_error_msg(), 400);
        }

        return $data ?? [];
    }

    /**
     * Récupère les paramètres de pagination depuis la requête
     */
    protected function getPaginationParams() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? min(100, max(1, (int)$_GET['per_page'])) : 20;
        $offset = ($page - 1) * $perPage;

        return compact('page', 'perPage', 'offset');
    }

    /**
     * Vérifie que la ressource appartient à l'entreprise de l'utilisateur
     */
    protected function checkOwnership($table, $id, $idField = 'id') {
        $stmt = $this->db->prepare("
            SELECT company_id FROM $table WHERE $idField = ?
        ");
        $stmt->execute([$id]);
        $resource = $stmt->fetch();

        if (!$resource || $resource['company_id'] != $this->company_id) {
            $this->error('Resource not found or access denied', 404);
        }

        return true;
    }
}
