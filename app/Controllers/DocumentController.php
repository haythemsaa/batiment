<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Document;
use App\Models\Chantier;
use App\Models\User;

class DocumentController extends Controller {
    private $documentModel;
    private $chantierModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->documentModel = new Document();
        $this->chantierModel = new Chantier();
        $this->userModel = new User();
    }

    /**
     * Liste des documents
     */
    public function index() {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];
        $chantierId = $_GET['chantier_id'] ?? null;
        $category = $_GET['category'] ?? null;

        $filters = ['company_id' => $companyId];
        if ($chantierId) $filters['chantier_id'] = $chantierId;
        if ($category) $filters['category'] = $category;

        // Uniquement les documents les plus récents (is_latest = 1)
        $filters['is_latest'] = 1;

        $documents = $this->documentModel->getAll($filters);
        $chantiers = $this->chantierModel->getByCompany($companyId);

        $this->render('documents/index', [
            'documents' => $documents,
            'chantiers' => $chantiers,
            'currentChantierId' => $chantierId,
            'currentCategory' => $category
        ]);
    }

    /**
     * Upload de document
     */
    public function upload() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyId = $_SESSION['user']['company_id'];
            $userId = $_SESSION['user']['id'];

            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $this->setFlash('error', 'Erreur lors de l\'upload');
                return $this->redirect('/documents');
            }

            $file = $_FILES['file'];
            $uploadDir = __DIR__ . '/../../public/uploads/documents/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = uniqid() . '_' . basename($file['name']);
            $filepath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                $this->setFlash('error', 'Erreur lors de l\'enregistrement du fichier');
                return $this->redirect('/documents');
            }

            $data = [
                'company_id' => $companyId,
                'chantier_id' => $_POST['chantier_id'] ?? null,
                'name' => $_POST['name'] ?: pathinfo($file['name'], PATHINFO_FILENAME),
                'description' => $_POST['description'] ?? null,
                'category' => $_POST['category'] ?? 'autre',
                'file_path' => '/uploads/documents/' . $filename,
                'file_size' => $file['size'],
                'mime_type' => $file['type'],
                'uploaded_by' => $userId,
                'version' => 1,
                'is_latest' => 1
            ];

            $id = $this->documentModel->create($data);

            if ($id) {
                $this->setFlash('success', 'Document uploadé avec succès');
                return $this->redirect('/documents/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de la création');
            }
        }

        $companyId = $_SESSION['user']['company_id'];
        $chantiers = $this->chantierModel->getByCompany($companyId);

        $this->render('documents/upload', [
            'chantiers' => $chantiers
        ]);
    }

    /**
     * Voir un document
     */
    public function view($id) {
        $this->requireAuth();

        $document = $this->documentModel->find($id);

        if (!$document || $document['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Document introuvable');
            return $this->redirect('/documents');
        }

        // Récupérer toutes les versions
        $versions = $this->documentModel->getVersions($id);

        $this->render('documents/view', [
            'document' => $document,
            'versions' => $versions
        ]);
    }

    /**
     * Télécharger un document
     */
    public function download($id) {
        $this->requireAuth();

        $document = $this->documentModel->find($id);

        if (!$document || $document['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Document introuvable');
            return $this->redirect('/documents');
        }

        $filepath = __DIR__ . '/../../public' . $document['file_path'];

        if (!file_exists($filepath)) {
            $this->setFlash('error', 'Fichier introuvable');
            return $this->redirect('/documents/view/' . $id);
        }

        // Incrémenter le compteur de téléchargements
        $this->documentModel->incrementDownloads($id);

        // Envoyer le fichier
        header('Content-Type: ' . $document['mime_type']);
        header('Content-Disposition: attachment; filename="' . $document['name'] . '"');
        header('Content-Length: ' . $document['file_size']);
        readfile($filepath);
        exit;
    }

    /**
     * Éditer un document
     */
    public function edit($id) {
        $this->requireAuth();

        $document = $this->documentModel->find($id);

        if (!$document || $document['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Document introuvable');
            return $this->redirect('/documents');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? null,
                'category' => $_POST['category'],
                'chantier_id' => $_POST['chantier_id'] ?? null
            ];

            if ($this->documentModel->update($id, $data)) {
                $this->setFlash('success', 'Document mis à jour');
                return $this->redirect('/documents/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de la mise à jour');
            }
        }

        $companyId = $_SESSION['user']['company_id'];
        $chantiers = $this->chantierModel->getByCompany($companyId);

        $this->render('documents/edit', [
            'document' => $document,
            'chantiers' => $chantiers
        ]);
    }

    /**
     * Upload nouvelle version
     */
    public function newVersion($parentId) {
        $this->requireAuth();

        $parent = $this->documentModel->find($parentId);

        if (!$parent || $parent['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Document introuvable');
            return $this->redirect('/documents');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyId = $_SESSION['user']['company_id'];
            $userId = $_SESSION['user']['id'];

            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $this->setFlash('error', 'Erreur lors de l\'upload');
                return $this->redirect('/documents/view/' . $parentId);
            }

            $file = $_FILES['file'];
            $uploadDir = __DIR__ . '/../../public/uploads/documents/';
            $filename = uniqid() . '_' . basename($file['name']);
            $filepath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                $this->setFlash('error', 'Erreur lors de l\'enregistrement du fichier');
                return $this->redirect('/documents/view/' . $parentId);
            }

            // Marquer l'ancienne version comme non-latest
            $this->documentModel->update($parentId, ['is_latest' => 0]);

            // Trouver la version parente racine
            $rootParentId = $parent['parent_id'] ?? $parentId;

            $data = [
                'company_id' => $companyId,
                'chantier_id' => $parent['chantier_id'],
                'name' => $parent['name'],
                'description' => $_POST['description'] ?? $parent['description'],
                'category' => $parent['category'],
                'file_path' => '/uploads/documents/' . $filename,
                'file_size' => $file['size'],
                'mime_type' => $file['type'],
                'uploaded_by' => $userId,
                'parent_id' => $rootParentId,
                'version' => $parent['version'] + 1,
                'is_latest' => 1
            ];

            $id = $this->documentModel->create($data);

            if ($id) {
                $this->setFlash('success', 'Nouvelle version uploadée (v' . $data['version'] . ')');
                return $this->redirect('/documents/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de la création');
            }
        }

        $this->render('documents/new_version', [
            'document' => $parent
        ]);
    }

    /**
     * Supprimer un document
     */
    public function delete($id) {
        $this->requireAuth();

        $document = $this->documentModel->find($id);

        if (!$document || $document['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Document introuvable');
            return $this->redirect('/documents');
        }

        // Supprimer toutes les versions
        $versions = $this->documentModel->getVersions($document['parent_id'] ?? $id);

        foreach ($versions as $version) {
            $filepath = __DIR__ . '/../../public' . $version['file_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            $this->documentModel->delete($version['id']);
        }

        $this->setFlash('success', 'Document et toutes ses versions supprimés');
        return $this->redirect('/documents');
    }

    /**
     * Partager un document
     */
    public function share($id) {
        $this->requireAuth();

        $document = $this->documentModel->find($id);

        if (!$document || $document['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Document introuvable');
            return $this->redirect('/documents');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'shared_with' => json_encode($_POST['users'] ?? []),
                'public_url' => $_POST['generate_link'] ? uniqid('doc_', true) : null,
                'expires_at' => $_POST['expires_at'] ?? null
            ];

            if ($this->documentModel->update($id, $data)) {
                $this->setFlash('success', 'Document partagé');
            } else {
                $this->setFlash('error', 'Erreur lors du partage');
            }

            return $this->redirect('/documents/view/' . $id);
        }

        $companyId = $_SESSION['user']['company_id'];
        $users = $this->userModel->getByCompany($companyId);

        $this->render('documents/share', [
            'document' => $document,
            'users' => $users
        ]);
    }

    /**
     * Documents d'un chantier
     */
    public function chantier($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/documents');
        }

        $documents = $this->documentModel->getAll([
            'company_id' => $companyId,
            'chantier_id' => $chantierId,
            'is_latest' => 1
        ]);

        // Grouper par catégorie
        $documentsByCategory = [];
        foreach ($documents as $doc) {
            $cat = $doc['category'];
            if (!isset($documentsByCategory[$cat])) {
                $documentsByCategory[$cat] = [];
            }
            $documentsByCategory[$cat][] = $doc;
        }

        $this->render('documents/chantier', [
            'chantier' => $chantier,
            'documents' => $documents,
            'documentsByCategory' => $documentsByCategory
        ]);
    }

    /**
     * Signer un document
     */
    public function sign($id) {
        $this->requireAuth();

        $document = $this->documentModel->find($id);

        if (!$document || $document['company_id'] !== $_SESSION['user']['company_id']) {
            $this->setFlash('error', 'Document introuvable');
            return $this->redirect('/documents');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'signature' => $_POST['signature'], // Base64 de la signature
                'signed_by' => $_SESSION['user']['id'],
                'signed_at' => date('Y-m-d H:i:s')
            ];

            if ($this->documentModel->update($id, $data)) {
                $this->setFlash('success', 'Document signé');
            } else {
                $this->setFlash('error', 'Erreur lors de la signature');
            }

            return $this->redirect('/documents/view/' . $id);
        }

        $this->render('documents/sign', [
            'document' => $document
        ]);
    }
}
