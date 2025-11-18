<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;

class NotificationsController extends Controller {
    private $notificationModel;

    public function __construct() {
        parent::__construct();
        $this->notificationModel = new Notification();
    }

    /**
     * Page de liste des notifications
     */
    public function index() {
        $this->requireAuth();

        $notifications = $this->notificationModel->getByUser($_SESSION['user_id']);

        $this->view('notifications/index', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Récupère les notifications (AJAX/API)
     */
    public function get() {
        $this->requireAuth();

        $onlyUnread = isset($_GET['unread']) && $_GET['unread'] === 'true';
        $limit = isset($_GET['limit']) ? min(100, (int)$_GET['limit']) : 20;

        $notifications = $this->notificationModel->getByUser($_SESSION['user_id'], $limit, $onlyUnread);
        $unreadCount = $this->notificationModel->countUnread($_SESSION['user_id']);

        header('Content-Type: application/json');
        echo json_encode([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
        exit;
    }

    /**
     * Marque une notification comme lue
     */
    public function markAsRead($id) {
        $this->requireAuth();

        $notification = $this->notificationModel->find($id);

        if (!$notification || $notification['user_id'] != $_SESSION['user_id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Notification not found']);
            exit;
        }

        $this->notificationModel->markAsRead($id);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function markAllAsRead() {
        $this->requireAuth();

        $this->notificationModel->markAllAsRead($_SESSION['user_id']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Stream SSE pour les notifications en temps réel
     */
    public function stream() {
        $this->requireAuth();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable nginx buffering

        $userId = $_SESSION['user_id'];
        $lastId = isset($_GET['lastId']) ? (int)$_GET['lastId'] : 0;

        // Fonction pour envoyer un événement SSE
        $sendEvent = function($event, $data) {
            echo "event: $event\n";
            echo "data: " . json_encode($data) . "\n\n";
            ob_flush();
            flush();
        };

        // Boucle de streaming
        $startTime = time();
        $maxDuration = 300; // 5 minutes max

        while (time() - $startTime < $maxDuration) {
            // Vérifier les nouvelles notifications
            $stmt = $this->db->prepare("
                SELECT * FROM notifications
                WHERE user_id = ? AND id > ?
                ORDER BY id ASC
            ");
            $stmt->execute([$userId, $lastId]);
            $newNotifications = $stmt->fetchAll();

            if (!empty($newNotifications)) {
                foreach ($newNotifications as $notification) {
                    $sendEvent('notification', $notification);
                    $lastId = $notification['id'];
                }
            }

            // Envoyer un ping pour maintenir la connexion
            $sendEvent('ping', ['time' => time()]);

            // Attendre 2 secondes avant la prochaine vérification
            sleep(2);

            // Vérifier si la connexion est toujours active
            if (connection_aborted()) {
                break;
            }
        }

        exit;
    }

    /**
     * Compte les notifications non lues
     */
    public function count() {
        $this->requireAuth();

        $count = $this->notificationModel->countUnread($_SESSION['user_id']);

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }

    /**
     * Supprime une notification
     */
    public function delete($id) {
        $this->requireAuth();

        $notification = $this->notificationModel->find($id);

        if (!$notification || $notification['user_id'] != $_SESSION['user_id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Notification not found']);
            exit;
        }

        $this->notificationModel->delete($id);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
