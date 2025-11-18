<?php
/**
 * Contrôleur de base
 */
class Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Charge une vue
     */
    protected function view($view, $data = [])
    {
        extract($data);

        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewFile)) {
            require_once APP_PATH . '/Views/layouts/header.php';
            require_once $viewFile;
            require_once APP_PATH . '/Views/layouts/footer.php';
        } else {
            die("View {$view} not found");
        }
    }

    /**
     * Charge une vue partielle (sans layout)
     */
    protected function partial($view, $data = [])
    {
        extract($data);

        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View {$view} not found");
        }
    }

    /**
     * Redirige vers une URL
     */
    protected function redirect($url)
    {
        header("Location: " . APP_URL . $url);
        exit;
    }

    /**
     * Retourne une réponse JSON
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Récupère un paramètre POST
     */
    protected function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Récupère un paramètre GET
     */
    protected function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Vérifie si l'utilisateur est authentifié
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Récupère l'utilisateur connecté
     */
    protected function getCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $this->db->queryOne(
            "SELECT * FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
    }

    /**
     * Récupère l'entreprise de l'utilisateur connecté (multi-tenant)
     */
    protected function getCurrentCompany()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return null;
        }

        return $this->db->queryOne(
            "SELECT * FROM companies WHERE id = ?",
            [$user['company_id']]
        );
    }

    /**
     * Génère un token CSRF
     */
    protected function generateCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie le token CSRF
     */
    protected function verifyCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Valide les données
     */
    protected function validate($data, $rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $ruleList = explode('|', $rule);

            foreach ($ruleList as $r) {
                if ($r === 'required' && empty($data[$field])) {
                    $errors[$field] = "Le champ {$field} est requis";
                }

                if (strpos($r, 'min:') === 0 && isset($data[$field])) {
                    $min = (int) substr($r, 4);
                    if (strlen($data[$field]) < $min) {
                        $errors[$field] = "Le champ {$field} doit contenir au moins {$min} caractères";
                    }
                }

                if ($r === 'email' && isset($data[$field])) {
                    if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "Le champ {$field} doit être une adresse email valide";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Flash message
     */
    protected function setFlash($type, $message)
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Récupère et supprime le flash message
     */
    protected function getFlash()
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
