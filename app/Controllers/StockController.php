<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Stock;
use App\Models\Chantier;

class StockController extends Controller {
    private $stockModel;
    private $chantierModel;

    public function __construct() {
        parent::__construct();
        $this->stockModel = new Stock();
        $this->chantierModel = new Chantier();
    }

    /**
     * Liste des stocks
     */
    public function index() {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];
        $category = $_GET['category'] ?? null;
        $lowStock = isset($_GET['low_stock']) ? (bool) $_GET['low_stock'] : false;

        $filters = ['company_id' => $companyId];
        if ($category) $filters['category'] = $category;

        $stocks = $this->stockModel->getAll($filters);

        // Filtrer les stocks faibles
        if ($lowStock) {
            $stocks = array_filter($stocks, function($stock) {
                return $stock['quantity'] <= $stock['min_quantity'];
            });
        }

        // Alertes stock faible
        $alerts = $this->stockModel->getLowStockAlerts($companyId);

        // Statistiques
        $stats = [
            'total_items' => count($stocks),
            'low_stock_count' => count($alerts),
            'total_value' => array_sum(array_map(function($s) {
                return $s['quantity'] * ($s['unit_price'] ?? 0);
            }, $stocks))
        ];

        $this->render('stocks/index', [
            'stocks' => $stocks,
            'alerts' => $alerts,
            'stats' => $stats,
            'currentCategory' => $category,
            'showLowStock' => $lowStock
        ]);
    }

    /**
     * Créer un article
     */
    public function create() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyId = $_SESSION['user']['company_id'];

            $data = [
                'company_id' => $companyId,
                'name' => $_POST['name'],
                'reference' => $_POST['reference'] ?? null,
                'category' => $_POST['category'],
                'description' => $_POST['description'] ?? null,
                'unit' => $_POST['unit'],
                'quantity' => (int) $_POST['quantity'],
                'min_quantity' => (int) ($_POST['min_quantity'] ?? 0),
                'unit_price' => $_POST['unit_price'] ?? null,
                'supplier' => $_POST['supplier'] ?? null,
                'location' => $_POST['location'] ?? null
            ];

            $id = $this->stockModel->create($data);

            if ($id) {
                // Créer un mouvement initial
                $this->stockModel->addMovement($id, 'ajustement', $data['quantity'], [
                    'notes' => 'Stock initial',
                    'created_by' => $_SESSION['user']['id']
                ]);

                $this->setFlash('success', 'Article créé avec succès');
                return $this->redirect('/stocks/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de la création');
            }
        }

        $this->render('stocks/create');
    }

    /**
     * Voir un article
     */
    public function view($id) {
        $this->requireAuth();

        $stock = $this->stockModel->find($id);

        if (!$stock || $stock['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Article introuvable');
            return $this->redirect('/stocks');
        }

        // Historique des mouvements
        $movements = $this->stockModel->getMovements($id);

        // Prévisions
        $forecast = $this->stockModel->getForecast($id);

        $this->render('stocks/view', [
            'stock' => $stock,
            'movements' => $movements,
            'forecast' => $forecast
        ]);
    }

    /**
     * Éditer un article
     */
    public function edit($id) {
        $this->requireAuth();

        $stock = $this->stockModel->find($id);

        if (!$stock || $stock['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Article introuvable');
            return $this->redirect('/stocks');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'reference' => $_POST['reference'] ?? null,
                'category' => $_POST['category'],
                'description' => $_POST['description'] ?? null,
                'unit' => $_POST['unit'],
                'min_quantity' => (int) ($_POST['min_quantity'] ?? 0),
                'unit_price' => $_POST['unit_price'] ?? null,
                'supplier' => $_POST['supplier'] ?? null,
                'location' => $_POST['location'] ?? null
            ];

            if ($this->stockModel->update($id, $data)) {
                $this->setFlash('success', 'Article mis à jour');
                return $this->redirect('/stocks/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de la mise à jour');
            }
        }

        $this->render('stocks/edit', [
            'stock' => $stock
        ]);
    }

    /**
     * Supprimer un article
     */
    public function delete($id) {
        $this->requireAuth();

        $stock = $this->stockModel->find($id);

        if (!$stock || $stock['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Article introuvable');
            return $this->redirect('/stocks');
        }

        if ($this->stockModel->delete($id)) {
            $this->setFlash('success', 'Article supprimé');
        } else {
            $this->setFlash('error', 'Erreur lors de la suppression');
        }

        return $this->redirect('/stocks');
    }

    /**
     * Ajouter un mouvement (entrée/sortie)
     */
    public function movement($id) {
        $this->requireAuth();

        $stock = $this->stockModel->find($id);

        if (!$stock || $stock['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Article introuvable');
            return $this->redirect('/stocks');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['type']; // entree, sortie, ajustement, retour
            $quantity = (int) $_POST['quantity'];

            $data = [
                'chantier_id' => $_POST['chantier_id'] ?? null,
                'reference' => $_POST['reference'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'created_by' => $_SESSION['user']['id']
            ];

            if ($this->stockModel->addMovement($id, $type, $quantity, $data)) {
                $this->setFlash('success', 'Mouvement enregistré');
            } else {
                $this->setFlash('error', 'Erreur lors de l\'enregistrement');
            }

            return $this->redirect('/stocks/view/' . $id);
        }

        // Afficher formulaire
        $companyId = $_SESSION['user']['company_id'];
        $chantiers = $this->chantierModel->getByCompany($companyId);

        $this->render('stocks/movement', [
            'stock' => $stock,
            'chantiers' => $chantiers
        ]);
    }

    /**
     * Inventaire
     */
    public function inventory() {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Traiter l'inventaire
            foreach ($_POST['stocks'] as $stockId => $data) {
                $counted = (int) $data['counted'];
                $stock = $this->stockModel->find($stockId);

                if ($stock && $stock['quantity'] != $counted) {
                    $diff = $counted - $stock['quantity'];
                    $this->stockModel->addMovement($stockId, 'ajustement', abs($diff), [
                        'notes' => 'Inventaire - Écart: ' . $diff,
                        'created_by' => $_SESSION['user']['id']
                    ]);
                }
            }

            $this->setFlash('success', 'Inventaire enregistré');
            return $this->redirect('/stocks');
        }

        $stocks = $this->stockModel->getAll(['company_id' => $companyId]);

        $this->render('stocks/inventory', [
            'stocks' => $stocks,
            'date' => date('Y-m-d')
        ]);
    }

    /**
     * Rapport de valorisation
     */
    public function valuation() {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];
        $valuation = $this->stockModel->getValuation($companyId);

        $this->render('stocks/valuation', [
            'valuation' => $valuation
        ]);
    }
}
