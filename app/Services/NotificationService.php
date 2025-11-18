<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService {
    private $notificationModel;

    public function __construct() {
        $this->notificationModel = new Notification();
    }

    /**
     * Notifie la création d'un nouveau chantier
     */
    public function chantierCreated($chantier, $createdByUserId) {
        $this->notificationModel->notifyCompany($chantier['company_id'], [
            'type' => 'chantier_created',
            'title' => 'Nouveau chantier',
            'message' => "Le chantier \"{$chantier['name']}\" a été créé",
            'link' => "/chantiers/view/{$chantier['id']}"
        ]);
    }

    /**
     * Notifie le changement de statut d'un chantier
     */
    public function chantierStatusChanged($chantier, $oldStatus, $newStatus) {
        $statusLabels = [
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'suspendu' => 'Suspendu',
            'annule' => 'Annulé'
        ];

        $this->notificationModel->notifyCompany($chantier['company_id'], [
            'type' => 'chantier_status_changed',
            'title' => 'Statut de chantier modifié',
            'message' => "Le chantier \"{$chantier['name']}\" est passé de {$statusLabels[$oldStatus]} à {$statusLabels[$newStatus]}",
            'link' => "/chantiers/view/{$chantier['id']}"
        ]);
    }

    /**
     * Notifie qu'une facture est en retard
     */
    public function factureOverdue($facture, $client) {
        $this->notificationModel->notifyCompany($facture['company_id'], [
            'type' => 'facture_overdue',
            'title' => 'Facture en retard',
            'message' => "La facture {$facture['number']} pour {$client['name']} est en retard de paiement",
            'link' => "/factures/view/{$facture['id']}"
        ]);
    }

    /**
     * Notifie qu'une facture a été payée
     */
    public function facturePaid($facture, $client) {
        $this->notificationModel->notifyCompany($facture['company_id'], [
            'type' => 'facture_paid',
            'title' => 'Facture payée',
            'message' => "La facture {$facture['number']} pour {$client['name']} a été payée",
            'link' => "/factures/view/{$facture['id']}"
        ]);
    }

    /**
     * Notifie qu'un devis a été accepté
     */
    public function devisAccepted($devis, $client) {
        $this->notificationModel->notifyCompany($devis['company_id'], [
            'type' => 'devis_accepted',
            'title' => 'Devis accepté',
            'message' => "Le devis {$devis['number']} pour {$client['name']} a été accepté",
            'link' => "/devis/view/{$devis['id']}"
        ]);
    }

    /**
     * Notifie qu'une tâche a été assignée
     */
    public function taskAssigned($task, $assignedToUserId, $chantier) {
        $this->notificationModel->create([
            'user_id' => $assignedToUserId,
            'company_id' => $task['company_id'],
            'type' => 'task_assigned',
            'title' => 'Nouvelle tâche assignée',
            'message' => "La tâche \"{$task['title']}\" vous a été assignée pour le chantier \"{$chantier['name']}\"",
            'link' => "/tasks/chantier/{$task['chantier_id']}"
        ]);
    }

    /**
     * Notifie qu'une tâche arrive bientôt à échéance
     */
    public function taskDueSoon($task, $assignedToUserId, $chantier) {
        $this->notificationModel->create([
            'user_id' => $assignedToUserId,
            'company_id' => $task['company_id'],
            'type' => 'task_due_soon',
            'title' => 'Tâche bientôt en retard',
            'message' => "La tâche \"{$task['title']}\" arrive à échéance le " . date('d/m/Y', strtotime($task['due_date'])),
            'link' => "/tasks/chantier/{$task['chantier_id']}"
        ]);
    }

    /**
     * Notifie l'ajout d'un nouveau client
     */
    public function clientCreated($client) {
        $this->notificationModel->notifyCompany($client['company_id'], [
            'type' => 'client_created',
            'title' => 'Nouveau client',
            'message' => "Le client \"{$client['name']}\" a été ajouté",
            'link' => "/clients/view/{$client['id']}"
        ]);
    }

    /**
     * Notifie qu'un budget de chantier est dépassé
     */
    public function budgetExceeded($chantier, $estimatedBudget, $actualCost) {
        $this->notificationModel->notifyCompany($chantier['company_id'], [
            'type' => 'budget_exceeded',
            'title' => 'Budget dépassé',
            'message' => "Le chantier \"{$chantier['name']}\" a dépassé son budget estimé de " . number_format($actualCost - $estimatedBudget, 2) . " €",
            'link' => "/chantiers/view/{$chantier['id']}"
        ]);
    }

    /**
     * Envoie une notification personnalisée
     */
    public function sendCustom($userId, $companyId, $title, $message, $link = null, $type = 'custom') {
        $this->notificationModel->create([
            'user_id' => $userId,
            'company_id' => $companyId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link
        ]);
    }
}
