<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Photo;
use App\Models\Chantier;

class PhotoController extends Controller {
    private $photoModel;
    private $chantierModel;

    public function __construct() {
        parent::__construct();
        $this->photoModel = new Photo();
        $this->chantierModel = new Chantier();
    }

    /**
     * Galerie photos - Vue principale
     */
    public function index() {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];
        $chantierId = $_GET['chantier_id'] ?? null;
        $category = $_GET['category'] ?? null;
        $zone = $_GET['zone'] ?? null;

        $filters = ['company_id' => $companyId];
        if ($chantierId) $filters['chantier_id'] = $chantierId;
        if ($category) $filters['category'] = $category;
        if ($zone) $filters['zone'] = $zone;

        $photos = $this->photoModel->getAll($filters);
        $chantiers = $this->chantierModel->getByCompany($companyId);

        // Grouper par chantier et catégorie
        $groupedPhotos = [];
        foreach ($photos as $photo) {
            $chId = $photo['chantier_id'];
            $cat = $photo['category'];
            if (!isset($groupedPhotos[$chId])) {
                $groupedPhotos[$chId] = [];
            }
            if (!isset($groupedPhotos[$chId][$cat])) {
                $groupedPhotos[$chId][$cat] = [];
            }
            $groupedPhotos[$chId][$cat][] = $photo;
        }

        $this->render('photos/index', [
            'photos' => $photos,
            'groupedPhotos' => $groupedPhotos,
            'chantiers' => $chantiers,
            'currentChantierId' => $chantierId,
            'currentCategory' => $category,
            'currentZone' => $zone
        ]);
    }

    /**
     * Upload de photos
     */
    public function upload() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyId = $_SESSION['user']['company_id'];
            $userId = $_SESSION['user']['id'];

            if (!isset($_FILES['photos']) || !isset($_POST['chantier_id'])) {
                $this->setFlash('error', 'Données manquantes');
                return $this->redirect('/photos');
            }

            $chantierId = (int) $_POST['chantier_id'];
            $files = $_FILES['photos'];
            $uploadedCount = 0;

            // Gérer upload multiple
            $fileCount = is_array($files['name']) ? count($files['name']) : 1;

            for ($i = 0; $i < $fileCount; $i++) {
                $file = [
                    'name' => is_array($files['name']) ? $files['name'][$i] : $files['name'],
                    'type' => is_array($files['type']) ? $files['type'][$i] : $files['type'],
                    'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
                    'error' => is_array($files['error']) ? $files['error'][$i] : $files['error'],
                    'size' => is_array($files['size']) ? $files['size'][$i] : $files['size']
                ];

                if ($file['error'] === UPLOAD_ERR_OK) {
                    $data = [
                        'company_id' => $companyId,
                        'chantier_id' => $chantierId,
                        'uploaded_by' => $userId,
                        'title' => $_POST['title'][$i] ?? null,
                        'description' => $_POST['description'][$i] ?? null,
                        'category' => $_POST['category'][$i] ?? 'autre',
                        'zone' => $_POST['zone'][$i] ?? null,
                        'etage' => $_POST['etage'][$i] ?? null,
                        'latitude' => $_POST['latitude'][$i] ?? null,
                        'longitude' => $_POST['longitude'][$i] ?? null
                    ];

                    if ($this->photoModel->upload($file, $data)) {
                        $uploadedCount++;
                    }
                }
            }

            $this->setFlash('success', "$uploadedCount photo(s) téléchargée(s) avec succès");
            return $this->redirect('/photos?chantier_id=' . $chantierId);
        }

        // Afficher formulaire
        $companyId = $_SESSION['user']['company_id'];
        $chantiers = $this->chantierModel->getByCompany($companyId);

        $this->render('photos/upload', [
            'chantiers' => $chantiers
        ]);
    }

    /**
     * Visualiser une photo
     */
    public function view($id) {
        $this->requireAuth();

        $photo = $this->photoModel->find($id);

        if (!$photo || $photo['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Photo introuvable');
            return $this->redirect('/photos');
        }

        // Photos avant/après du même endroit
        $relatedPhotos = [];
        if ($photo['zone']) {
            $relatedPhotos = $this->photoModel->getAll([
                'company_id' => $photo['company_id'],
                'chantier_id' => $photo['chantier_id'],
                'zone' => $photo['zone']
            ]);
        }

        $this->render('photos/view', [
            'photo' => $photo,
            'relatedPhotos' => $relatedPhotos
        ]);
    }

    /**
     * Éditer une photo
     */
    public function edit($id) {
        $this->requireAuth();

        $photo = $this->photoModel->find($id);

        if (!$photo || $photo['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Photo introuvable');
            return $this->redirect('/photos');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? null,
                'description' => $_POST['description'] ?? null,
                'category' => $_POST['category'] ?? 'autre',
                'zone' => $_POST['zone'] ?? null,
                'etage' => $_POST['etage'] ?? null,
                'annotations' => $_POST['annotations'] ?? null
            ];

            if ($this->photoModel->update($id, $data)) {
                $this->setFlash('success', 'Photo mise à jour');
            } else {
                $this->setFlash('error', 'Erreur lors de la mise à jour');
            }

            return $this->redirect('/photos/view/' . $id);
        }

        $this->render('photos/edit', [
            'photo' => $photo
        ]);
    }

    /**
     * Supprimer une photo
     */
    public function delete($id) {
        $this->requireAuth();

        $photo = $this->photoModel->find($id);

        if (!$photo || $photo['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Photo introuvable');
            return $this->redirect('/photos');
        }

        if ($this->photoModel->delete($id)) {
            // Supprimer les fichiers physiques
            if (file_exists($photo['file_path'])) {
                unlink($photo['file_path']);
            }
            if ($photo['thumbnail_path'] && file_exists($photo['thumbnail_path'])) {
                unlink($photo['thumbnail_path']);
            }

            $this->setFlash('success', 'Photo supprimée');
        } else {
            $this->setFlash('error', 'Erreur lors de la suppression');
        }

        return $this->redirect('/photos?chantier_id=' . $photo['chantier_id']);
    }

    /**
     * Comparaison avant/après
     */
    public function beforeAfter($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/photos');
        }

        $comparisons = $this->photoModel->getBeforeAfterComparison($chantierId);

        $this->render('photos/before_after', [
            'chantier' => $chantier,
            'comparisons' => $comparisons
        ]);
    }

    /**
     * Rapport photo avec export PDF
     */
    public function report($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/photos');
        }

        $photos = $this->photoModel->getAll([
            'company_id' => $companyId,
            'chantier_id' => $chantierId
        ]);

        // Grouper par catégorie
        $photosByCategory = [];
        foreach ($photos as $photo) {
            $cat = $photo['category'];
            if (!isset($photosByCategory[$cat])) {
                $photosByCategory[$cat] = [];
            }
            $photosByCategory[$cat][] = $photo;
        }

        $this->render('photos/report', [
            'chantier' => $chantier,
            'photos' => $photos,
            'photosByCategory' => $photosByCategory
        ]);
    }
}
