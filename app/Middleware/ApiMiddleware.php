<?php
/**
 * Middleware pour les routes API
 */
class ApiMiddleware
{
    /**
     * Vérifie le token d'authentification API
     */
    public function handle()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? '';

        // Retire le préfixe "Bearer "
        $token = str_replace('Bearer ', '', $token);

        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            exit;
        }

        // Vérifie le token
        $db = Database::getInstance();
        $user = $db->queryOne(
            "SELECT * FROM users WHERE api_token = ? AND api_token_expires_at > NOW()",
            [$token]
        );

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide ou expiré']);
            exit;
        }

        // Stocke l'utilisateur dans la session
        $_SESSION['api_user_id'] = $user['id'];
        $_SESSION['api_company_id'] = $user['company_id'];

        return true;
    }
}
