<?php
/**
 * Modèle User
 */
class User extends Model
{
    protected $table = 'users';
    protected $multiTenant = false; // Les users sont déjà filtrés par company_id

    /**
     * Vérifie les credentials de connexion
     */
    public function authenticate($email, $password)
    {
        $user = $this->db->queryOne(
            "SELECT * FROM users WHERE email = ? AND status = 'active'",
            [$email]
        );

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function createUser($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->create($data);
    }

    /**
     * Génère un token API
     */
    public function generateApiToken($userId)
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 year'));

        $this->db->execute(
            "UPDATE users SET api_token = ?, api_token_expires_at = ? WHERE id = ?",
            [$token, $expiresAt, $userId]
        );

        return $token;
    }

    /**
     * Met à jour la dernière connexion
     */
    public function updateLastLogin($userId)
    {
        $this->db->execute(
            "UPDATE users SET last_login_at = NOW() WHERE id = ?",
            [$userId]
        );
    }
}
