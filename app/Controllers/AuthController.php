<?php
/**
 * Contrôleur d'authentification
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
     * Affiche le formulaire de connexion
     */
    public function loginForm()
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->partial('auth.login');
    }

    /**
     * Traite la connexion
     */
    public function login()
    {
        $email = $this->post('email');
        $password = $this->post('password');

        $errors = $this->validate([
            'email' => $email,
            'password' => $password
        ], [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs');
            $this->redirect('/login');
            return;
        }

        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            $this->setFlash('error', 'Email ou mot de passe incorrect');
            $this->redirect('/login');
            return;
        }

        // Vérifie que l'entreprise a un abonnement actif
        $companyModel = new Company();
        if (!$companyModel->hasActiveSubscription($user['company_id'])) {
            $this->setFlash('error', 'Votre abonnement a expiré. Veuillez contacter le support.');
            $this->redirect('/login');
            return;
        }

        // Démarre la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

        // Met à jour la dernière connexion
        $this->userModel->updateLastLogin($user['id']);

        $this->setFlash('success', 'Bienvenue ' . $_SESSION['user_name']);
        $this->redirect('/dashboard');
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function registerForm()
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->partial('auth.register');
    }

    /**
     * Traite l'inscription
     */
    public function register()
    {
        $companyName = $this->post('company_name');
        $email = $this->post('email');
        $password = $this->post('password');
        $firstName = $this->post('first_name');
        $lastName = $this->post('last_name');

        $errors = $this->validate([
            'company_name' => $companyName,
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName
        ], [
            'company_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'first_name' => 'required',
            'last_name' => 'required'
        ]);

        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('/register');
            return;
        }

        // Vérifie si l'email existe déjà
        $existingUser = $this->db->queryOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existingUser) {
            $this->setFlash('error', 'Cet email est déjà utilisé');
            $this->redirect('/register');
            return;
        }

        $this->db->beginTransaction();

        try {
            // Crée l'entreprise
            $companyModel = new Company();
            $companyId = $companyModel->create([
                'name' => $companyName,
                'email' => $email,
                'status' => 'active',
                'subscription_plan' => 'trial',
                'subscription_expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
            ]);

            // Crée l'utilisateur admin
            $userId = $this->userModel->createUser([
                'company_id' => $companyId,
                'email' => $email,
                'password' => $password,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => 'admin',
                'status' => 'active'
            ]);

            $this->db->commit();

            $this->setFlash('success', 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.');
            $this->redirect('/login');
        } catch (Exception $e) {
            $this->db->rollback();
            $this->setFlash('error', 'Une erreur est survenue lors de la création du compte');
            $this->redirect('/register');
        }
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        session_destroy();
        $this->redirect('/login');
    }
}
