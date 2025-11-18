<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\Chantier;

class MessageController extends Controller {
    private $messageModel;
    private $userModel;
    private $chantierModel;

    public function __construct() {
        parent::__construct();
        $this->messageModel = new Message();
        $this->userModel = new User();
        $this->chantierModel = new Chantier();
    }

    /**
     * Messagerie - Vue principale
     */
    public function index() {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];
        $userId = $_SESSION['user']['id'];

        // Conversations récentes
        $conversations = $this->messageModel->getConversations($companyId, $userId);

        // Nombre de messages non lus
        $unreadCount = $this->messageModel->getUnreadCount($userId);

        $this->render('messages/index', [
            'conversations' => $conversations,
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Conversation avec un utilisateur
     */
    public function conversation($recipientId) {
        $this->requireAuth();

        $userId = $_SESSION['user']['id'];
        $companyId = $_SESSION['user']['company_id'];

        // Récupérer les messages entre les deux utilisateurs
        $messages = $this->messageModel->getConversation($userId, $recipientId);

        // Marquer comme lus
        $this->messageModel->markAsRead($recipientId, $userId);

        // Infos du destinataire
        $recipient = $this->userModel->find($recipientId);

        if (!$recipient || $recipient['company_id'] !== $companyId) {
            $this->setFlash('error', 'Utilisateur introuvable');
            return $this->redirect('/messages');
        }

        $this->render('messages/conversation', [
            'messages' => $messages,
            'recipient' => $recipient
        ]);
    }

    /**
     * Envoyer un message
     */
    public function send() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/messages');
        }

        $userId = $_SESSION['user']['id'];
        $companyId = $_SESSION['user']['company_id'];

        $data = [
            'company_id' => $companyId,
            'sender_id' => $userId,
            'recipient_id' => (int) $_POST['recipient_id'],
            'subject' => $_POST['subject'] ?? null,
            'message' => $_POST['message'],
            'chantier_id' => $_POST['chantier_id'] ?? null,
            'parent_id' => $_POST['parent_id'] ?? null
        ];

        // Gérer les pièces jointes
        if (isset($_FILES['attachments']) && $_FILES['attachments']['error'][0] === UPLOAD_ERR_OK) {
            $attachments = [];
            foreach ($_FILES['attachments']['tmp_name'] as $key => $tmpName) {
                $filename = uniqid() . '_' . $_FILES['attachments']['name'][$key];
                $uploadPath = __DIR__ . '/../../public/uploads/messages/' . $filename;

                if (move_uploaded_file($tmpName, $uploadPath)) {
                    $attachments[] = $filename;
                }
            }
            $data['attachments'] = json_encode($attachments);
        }

        $id = $this->messageModel->send($data);

        if ($id) {
            // Envoyer une notification au destinataire
            // TODO: Notification système

            $this->json(['success' => true, 'message_id' => $id]);
        } else {
            $this->json(['success' => false, 'error' => 'Erreur lors de l\'envoi'], 500);
        }
    }

    /**
     * Nouveau message
     */
    public function compose() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->send();
        }

        $companyId = $_SESSION['user']['company_id'];
        $users = $this->userModel->getByCompany($companyId);
        $chantiers = $this->chantierModel->getByCompany($companyId);

        // Pré-remplir le destinataire si fourni
        $recipientId = $_GET['to'] ?? null;
        $chantierId = $_GET['chantier_id'] ?? null;

        $this->render('messages/compose', [
            'users' => $users,
            'chantiers' => $chantiers,
            'recipientId' => $recipientId,
            'chantierId' => $chantierId
        ]);
    }

    /**
     * Voir un message
     */
    public function view($id) {
        $this->requireAuth();

        $message = $this->messageModel->find($id);
        $userId = $_SESSION['user']['id'];
        $companyId = $_SESSION['user']['company_id'];

        if (!$message || $message['company_id'] !== $companyId) {
            $this->setFlash('error', 'Message introuvable');
            return $this->redirect('/messages');
        }

        // Vérifier que l'utilisateur est expéditeur ou destinataire
        if ($message['sender_id'] !== $userId && $message['recipient_id'] !== $userId) {
            $this->setFlash('error', 'Accès non autorisé');
            return $this->redirect('/messages');
        }

        // Marquer comme lu si c'est le destinataire
        if ($message['recipient_id'] === $userId && !$message['read_at']) {
            $this->messageModel->markAsRead($message['sender_id'], $userId);
        }

        // Récupérer les réponses
        $replies = $this->messageModel->getReplies($id);

        $this->render('messages/view', [
            'message' => $message,
            'replies' => $replies
        ]);
    }

    /**
     * Répondre à un message
     */
    public function reply($id) {
        $this->requireAuth();

        $message = $this->messageModel->find($id);
        $userId = $_SESSION['user']['id'];
        $companyId = $_SESSION['user']['company_id'];

        if (!$message || $message['company_id'] !== $companyId) {
            $this->setFlash('error', 'Message introuvable');
            return $this->redirect('/messages');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'company_id' => $companyId,
                'sender_id' => $userId,
                'recipient_id' => $message['sender_id'] === $userId ? $message['recipient_id'] : $message['sender_id'],
                'subject' => 'Re: ' . $message['subject'],
                'message' => $_POST['message'],
                'chantier_id' => $message['chantier_id'],
                'parent_id' => $id
            ];

            $replyId = $this->messageModel->send($data);

            if ($replyId) {
                $this->setFlash('success', 'Réponse envoyée');
                return $this->redirect('/messages/view/' . $id);
            } else {
                $this->setFlash('error', 'Erreur lors de l\'envoi');
            }
        }

        $this->render('messages/reply', [
            'message' => $message
        ]);
    }

    /**
     * Supprimer un message
     */
    public function delete($id) {
        $this->requireAuth();

        $message = $this->messageModel->find($id);
        $userId = $_SESSION['user']['id'];
        $companyId = $_SESSION['user']['company_id'];

        if (!$message || $message['company_id'] !== $companyId) {
            $this->setFlash('error', 'Message introuvable');
            return $this->redirect('/messages');
        }

        // Vérifier que l'utilisateur est expéditeur ou destinataire
        if ($message['sender_id'] !== $userId && $message['recipient_id'] !== $userId) {
            $this->setFlash('error', 'Accès non autorisé');
            return $this->redirect('/messages');
        }

        if ($this->messageModel->delete($id)) {
            $this->setFlash('success', 'Message supprimé');
        } else {
            $this->setFlash('error', 'Erreur lors de la suppression');
        }

        return $this->redirect('/messages');
    }

    /**
     * Messages d'un chantier
     */
    public function chantier($chantierId) {
        $this->requireAuth();

        $companyId = $_SESSION['user']['company_id'];

        $chantier = $this->chantierModel->find($chantierId);
        if (!$chantier || $chantier['company_id'] !== $companyId) {
            $this->setFlash('error', 'Chantier introuvable');
            return $this->redirect('/messages');
        }

        $messages = $this->messageModel->getByChantier($chantierId);

        $this->render('messages/chantier', [
            'chantier' => $chantier,
            'messages' => $messages
        ]);
    }

    /**
     * API: Récupérer les nouveaux messages (polling)
     */
    public function poll() {
        $this->requireAuth();

        $userId = $_SESSION['user']['id'];
        $lastId = $_GET['last_id'] ?? 0;

        $newMessages = $this->messageModel->getNewMessages($userId, $lastId);
        $unreadCount = $this->messageModel->getUnreadCount($userId);

        $this->json([
            'messages' => $newMessages,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Marquer comme lu
     */
    public function markRead($id) {
        $this->requireAuth();

        $message = $this->messageModel->find($id);
        $userId = $_SESSION['user']['id'];

        if ($message && $message['recipient_id'] === $userId) {
            $this->messageModel->update($id, ['read_at' => date('Y-m-d H:i:s')]);
        }

        $this->json(['success' => true]);
    }
}
