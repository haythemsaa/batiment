<?php
/**
 * BatiSaaS - Plateforme de gestion pour entreprises du bâtiment
 * Point d'entrée principal de l'application
 */

// Démarrage de la session
session_start();

// Configuration de l'application
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/app/Controllers/',
        __DIR__ . '/app/Models/',
        __DIR__ . '/app/Core/',
        __DIR__ . '/app/Middleware/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Gestion des erreurs en mode développement
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Initialisation du routeur
$router = new Router();

// Routes publiques
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// Routes protégées (nécessitent authentification)
$router->group(['middleware' => 'auth'], function($router) {
    // Dashboard
    $router->get('/dashboard', 'DashboardController@index');

    // Gestion des devis
    $router->get('/devis', 'DevisController@index');
    $router->get('/devis/create', 'DevisController@create');
    $router->post('/devis/store', 'DevisController@store');
    $router->get('/devis/edit/{id}', 'DevisController@edit');
    $router->post('/devis/update/{id}', 'DevisController@update');
    $router->get('/devis/view/{id}', 'DevisController@view');
    $router->get('/devis/pdf/{id}', 'DevisController@generatePDF');
    $router->post('/devis/convert/{id}', 'DevisController@convertToInvoice');

    // Gestion des factures
    $router->get('/factures', 'FactureController@index');
    $router->get('/factures/create', 'FactureController@create');
    $router->post('/factures/store', 'FactureController@store');
    $router->get('/factures/edit/{id}', 'FactureController@edit');
    $router->post('/factures/update/{id}', 'FactureController@update');
    $router->get('/factures/view/{id}', 'FactureController@view');
    $router->get('/factures/pdf/{id}', 'FactureController@generatePDF');
    $router->post('/factures/send/{id}', 'FactureController@sendEmail');

    // Gestion des chantiers
    $router->get('/chantiers', 'ChantierController@index');
    $router->get('/chantiers/create', 'ChantierController@create');
    $router->post('/chantiers/store', 'ChantierController@store');
    $router->get('/chantiers/edit/{id}', 'ChantierController@edit');
    $router->post('/chantiers/update/{id}', 'ChantierController@update');
    $router->get('/chantiers/view/{id}', 'ChantierController@view');
    $router->get('/chantiers/gantt/{id}', 'ChantierController@gantt');

    // Gestion des clients
    $router->get('/clients', 'ClientController@index');
    $router->get('/clients/create', 'ClientController@create');
    $router->post('/clients/store', 'ClientController@store');
    $router->get('/clients/edit/{id}', 'ClientController@edit');
    $router->post('/clients/update/{id}', 'ClientController@update');

    // Gestion des fournisseurs
    $router->get('/fournisseurs', 'FournisseurController@index');
    $router->get('/fournisseurs/create', 'FournisseurController@create');
    $router->post('/fournisseurs/store', 'FournisseurController@store');

    // Rapports et analytics
    $router->get('/rapports', 'RapportController@index');
    $router->get('/rapports/rentabilite', 'RapportController@rentabilite');

    // Paramètres
    $router->get('/settings', 'SettingsController@index');
    $router->post('/settings/update', 'SettingsController@update');
});

// Routes API (pour application mobile)
$router->group(['prefix' => 'api', 'middleware' => 'api'], function($router) {
    $router->post('/auth/login', 'Api\AuthController@login');
    $router->get('/devis', 'Api\DevisController@index');
    $router->get('/factures', 'Api\FactureController@index');
    $router->get('/chantiers', 'Api\ChantierController@index');
});

// Dispatch de la requête
$router->dispatch();
