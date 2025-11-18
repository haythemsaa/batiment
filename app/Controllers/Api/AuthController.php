<?php
/**
 * API - Contrôleur d'authentification
 */
class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Authentification API
     */
    public function login()
    {
        header('Content-Type: application/json');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->json(['error' => 'Email et mot de passe requis'], 400);
            return;
        }

        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            $this->json(['error' => 'Identifiants invalides'], 401);
            return;
        }

        // Génère un token API
        $token = $this->userModel->generateApiToken($user['id']);

        $this->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role'],
                'company_id' => $user['company_id']
            ]
        ]);
    }
}
