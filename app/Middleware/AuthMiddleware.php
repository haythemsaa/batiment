<?php
/**
 * Middleware d'authentification
 */
class AuthMiddleware
{
    /**
     * Vérifie si l'utilisateur est authentifié
     */
    public function handle()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        return true;
    }
}
