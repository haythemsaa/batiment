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

// Helpers
require_once __DIR__ . '/app/Helpers/Helpers.php';

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

    // Planning & Gantt
    $router->get('/planning/gantt', 'PlanningController@gantt');
    $router->get('/planning/calendar', 'PlanningController@calendar');
    $router->get('/planning/timeline', 'PlanningController@timeline');

    // Gestion des tâches
    $router->get('/tasks', 'TasksController@index');
    $router->get('/tasks/chantier/{id}', 'TasksController@chantier');
    $router->post('/tasks/create', 'TasksController@create');
    $router->post('/tasks/update/{id}', 'TasksController@update');
    $router->post('/tasks/delete/{id}', 'TasksController@delete');

    // Notifications
    $router->get('/notifications', 'NotificationsController@index');
    $router->get('/notifications/stream', 'NotificationsController@stream');
    $router->post('/notifications/mark-read/{id}', 'NotificationsController@markRead');
    $router->post('/notifications/mark-all-read', 'NotificationsController@markAllRead');

    // Galerie Photos
    $router->get('/photos', 'PhotoController@index');
    $router->get('/photos/upload', 'PhotoController@upload');
    $router->post('/photos/upload', 'PhotoController@upload');
    $router->get('/photos/view/{id}', 'PhotoController@view');
    $router->get('/photos/edit/{id}', 'PhotoController@edit');
    $router->post('/photos/edit/{id}', 'PhotoController@edit');
    $router->post('/photos/delete/{id}', 'PhotoController@delete');
    $router->get('/photos/before-after/{id}', 'PhotoController@beforeAfter');
    $router->get('/photos/report/{id}', 'PhotoController@report');

    // Gestion des Stocks
    $router->get('/stocks', 'StockController@index');
    $router->get('/stocks/create', 'StockController@create');
    $router->post('/stocks/create', 'StockController@create');
    $router->get('/stocks/view/{id}', 'StockController@view');
    $router->get('/stocks/edit/{id}', 'StockController@edit');
    $router->post('/stocks/edit/{id}', 'StockController@edit');
    $router->post('/stocks/delete/{id}', 'StockController@delete');
    $router->get('/stocks/movement/{id}', 'StockController@movement');
    $router->post('/stocks/movement/{id}', 'StockController@movement');
    $router->get('/stocks/inventory', 'StockController@inventory');
    $router->post('/stocks/inventory', 'StockController@inventory');
    $router->get('/stocks/valuation', 'StockController@valuation');

    // Feuilles de Temps
    $router->get('/timesheets', 'TimesheetController@index');
    $router->get('/timesheets/clock', 'TimesheetController@clock');
    $router->post('/timesheets/clock', 'TimesheetController@clock');
    $router->get('/timesheets/create', 'TimesheetController@create');
    $router->post('/timesheets/create', 'TimesheetController@create');
    $router->get('/timesheets/view/{id}', 'TimesheetController@view');
    $router->get('/timesheets/edit/{id}', 'TimesheetController@edit');
    $router->post('/timesheets/edit/{id}', 'TimesheetController@edit');
    $router->post('/timesheets/delete/{id}', 'TimesheetController@delete');
    $router->get('/timesheets/weekly', 'TimesheetController@weekly');
    $router->get('/timesheets/monthly', 'TimesheetController@monthly');
    $router->post('/timesheets/validate/{id}', 'TimesheetController@validate');

    // Messagerie
    $router->get('/messages', 'MessageController@index');
    $router->get('/messages/compose', 'MessageController@compose');
    $router->post('/messages/compose', 'MessageController@compose');
    $router->get('/messages/conversation/{id}', 'MessageController@conversation');
    $router->get('/messages/view/{id}', 'MessageController@view');
    $router->get('/messages/reply/{id}', 'MessageController@reply');
    $router->post('/messages/reply/{id}', 'MessageController@reply');
    $router->post('/messages/delete/{id}', 'MessageController@delete');
    $router->get('/messages/chantier/{id}', 'MessageController@chantier');
    $router->get('/messages/poll', 'MessageController@poll');
    $router->post('/messages/mark-read/{id}', 'MessageController@markRead');

    // Liste des Réserves (Punch List)
    $router->get('/punch-lists', 'PunchListController@index');
    $router->get('/punch-lists/create', 'PunchListController@create');
    $router->post('/punch-lists/create', 'PunchListController@create');
    $router->get('/punch-lists/view/{id}', 'PunchListController@view');
    $router->get('/punch-lists/edit/{id}', 'PunchListController@edit');
    $router->post('/punch-lists/edit/{id}', 'PunchListController@edit');
    $router->post('/punch-lists/delete/{id}', 'PunchListController@delete');
    $router->get('/punch-lists/resolve/{id}', 'PunchListController@resolve');
    $router->post('/punch-lists/resolve/{id}', 'PunchListController@resolve');
    $router->post('/punch-lists/verify/{id}', 'PunchListController@verify');
    $router->post('/punch-lists/close/{id}', 'PunchListController@close');
    $router->post('/punch-lists/reopen/{id}', 'PunchListController@reopen');
    $router->get('/punch-lists/chantier/{id}', 'PunchListController@chantier');
    $router->get('/punch-lists/export/{id}', 'PunchListController@export');

    // Carnet de Bord
    $router->get('/carnet-bord/{id}', 'CarnetBordController@index');
    $router->get('/carnet-bord/today/{id}', 'CarnetBordController@today');
    $router->post('/carnet-bord/today/{id}', 'CarnetBordController@today');
    $router->get('/carnet-bord/view/{id}', 'CarnetBordController@view');
    $router->get('/carnet-bord/edit/{id}', 'CarnetBordController@edit');
    $router->post('/carnet-bord/edit/{id}', 'CarnetBordController@edit');
    $router->post('/carnet-bord/delete/{id}', 'CarnetBordController@delete');
    $router->get('/carnet-bord/weekly/{id}', 'CarnetBordController@weekly');
    $router->get('/carnet-bord/export/{id}', 'CarnetBordController@export');
    $router->get('/carnet-bord/calendar/{id}', 'CarnetBordController@calendar');

    // Gestion Documentaire
    $router->get('/documents', 'DocumentController@index');
    $router->get('/documents/upload', 'DocumentController@upload');
    $router->post('/documents/upload', 'DocumentController@upload');
    $router->get('/documents/view/{id}', 'DocumentController@view');
    $router->get('/documents/download/{id}', 'DocumentController@download');
    $router->get('/documents/edit/{id}', 'DocumentController@edit');
    $router->post('/documents/edit/{id}', 'DocumentController@edit');
    $router->get('/documents/new-version/{id}', 'DocumentController@newVersion');
    $router->post('/documents/new-version/{id}', 'DocumentController@newVersion');
    $router->post('/documents/delete/{id}', 'DocumentController@delete');
    $router->get('/documents/share/{id}', 'DocumentController@share');
    $router->post('/documents/share/{id}', 'DocumentController@share');
    $router->get('/documents/chantier/{id}', 'DocumentController@chantier');
    $router->get('/documents/sign/{id}', 'DocumentController@sign');
    $router->post('/documents/sign/{id}', 'DocumentController@sign');
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
