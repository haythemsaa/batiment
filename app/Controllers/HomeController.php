<?php
/**
 * Contrôleur Home
 */
class HomeController extends Controller
{
    /**
     * Page d'accueil
     */
    public function index()
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->partial('home');
    }
}
