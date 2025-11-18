/**
 * BatiSaaS - Système de notifications en temps réel
 * Utilise Server-Sent Events (SSE) pour recevoir les notifications
 */

class NotificationManager {
    constructor() {
        this.eventSource = null;
        this.lastNotificationId = 0;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;

        this.init();
    }

    /**
     * Initialise le gestionnaire de notifications
     */
    init() {
        // Charger les notifications initiales
        this.loadNotifications();

        // Démarrer le stream SSE
        this.connectSSE();

        // Mettre à jour le compteur périodiquement (fallback si SSE échoue)
        setInterval(() => this.updateCount(), 30000);

        // Événements du dropdown
        this.setupDropdown();
    }

    /**
     * Charge les notifications depuis l'API
     */
    async loadNotifications() {
        try {
            const response = await fetch('/notifications/get?limit=10');
            const data = await response.json();

            this.renderNotifications(data.notifications);
            this.updateBadge(data.unread_count);

        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    /**
     * Se connecte au stream SSE
     */
    connectSSE() {
        if (this.eventSource) {
            this.eventSource.close();
        }

        this.eventSource = new EventSource(`/notifications/stream?lastId=${this.lastNotificationId}`);

        this.eventSource.addEventListener('notification', (e) => {
            const notification = JSON.parse(e.data);
            this.handleNewNotification(notification);
        });

        this.eventSource.addEventListener('ping', (e) => {
            // Heartbeat pour maintenir la connexion
            this.reconnectAttempts = 0;
        });

        this.eventSource.onerror = (error) => {
            console.error('SSE error:', error);
            this.isConnected = false;

            if (this.reconnectAttempts < this.maxReconnectAttempts) {
                this.reconnectAttempts++;
                setTimeout(() => this.connectSSE(), 5000 * this.reconnectAttempts);
            }
        };

        this.eventSource.onopen = () => {
            console.log('SSE connection established');
            this.isConnected = true;
            this.reconnectAttempts = 0;
        };
    }

    /**
     * Traite une nouvelle notification
     */
    handleNewNotification(notification) {
        this.lastNotificationId = notification.id;

        // Afficher une notification navigateur
        this.showBrowserNotification(notification);

        // Jouer un son (optionnel)
        this.playNotificationSound();

        // Ajouter à la liste
        this.prependNotification(notification);

        // Mettre à jour le compteur
        this.updateCount();
    }

    /**
     * Affiche une notification navigateur
     */
    showBrowserNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const browserNotif = new Notification(notification.title, {
                body: notification.message,
                icon: '/images/logo.png',
                tag: `notification-${notification.id}`
            });

            browserNotif.onclick = () => {
                window.focus();
                if (notification.link) {
                    window.location.href = notification.link;
                }
                browserNotif.close();
            };
        }
    }

    /**
     * Joue un son de notification
     */
    playNotificationSound() {
        const audio = new Audio('/sounds/notification.mp3');
        audio.volume = 0.3;
        audio.play().catch(() => {
            // Ignorer l'erreur si le son ne peut pas être joué
        });
    }

    /**
     * Ajoute une notification au début de la liste
     */
    prependNotification(notification) {
        const container = document.getElementById('notifications-list');
        if (!container) return;

        const item = this.createNotificationElement(notification);
        container.insertBefore(item, container.firstChild);

        // Limiter à 10 notifications affichées
        while (container.children.length > 10) {
            container.removeChild(container.lastChild);
        }
    }

    /**
     * Affiche les notifications dans le dropdown
     */
    renderNotifications(notifications) {
        const container = document.getElementById('notifications-list');
        if (!container) return;

        if (notifications.length === 0) {
            container.innerHTML = '<div class="notification-empty">Aucune notification</div>';
            return;
        }

        container.innerHTML = '';

        notifications.forEach(notification => {
            container.appendChild(this.createNotificationElement(notification));
        });
    }

    /**
     * Crée un élément DOM pour une notification
     */
    createNotificationElement(notification) {
        const item = document.createElement('a');
        item.className = `notification-item ${notification.is_read ? 'read' : 'unread'}`;
        item.href = notification.link || '#';
        item.dataset.id = notification.id;

        const iconClass = this.getIconForType(notification.type);
        const timeAgo = this.timeAgo(notification.created_at);

        item.innerHTML = `
            <div class="notification-icon">
                <i class="${iconClass}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                <div class="notification-message">${this.escapeHtml(notification.message)}</div>
                <div class="notification-time">${timeAgo}</div>
            </div>
            ${!notification.is_read ? '<div class="notification-dot"></div>' : ''}
        `;

        // Marquer comme lue au clic
        item.addEventListener('click', (e) => {
            if (!notification.is_read) {
                this.markAsRead(notification.id);
            }
        });

        return item;
    }

    /**
     * Retourne l'icône appropriée selon le type de notification
     */
    getIconForType(type) {
        const icons = {
            'chantier_created': 'fas fa-hard-hat text-primary',
            'chantier_status_changed': 'fas fa-sync text-info',
            'facture_overdue': 'fas fa-exclamation-triangle text-danger',
            'facture_paid': 'fas fa-check-circle text-success',
            'devis_accepted': 'fas fa-file-invoice text-success',
            'task_assigned': 'fas fa-tasks text-primary',
            'task_due_soon': 'fas fa-clock text-warning',
            'client_created': 'fas fa-user-plus text-info',
            'budget_exceeded': 'fas fa-exclamation-circle text-danger',
            'custom': 'fas fa-bell text-secondary'
        };

        return icons[type] || icons.custom;
    }

    /**
     * Met à jour le badge de compteur
     */
    updateBadge(count) {
        const badge = document.getElementById('notifications-badge');
        if (!badge) return;

        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    /**
     * Met à jour le compteur de notifications non lues
     */
    async updateCount() {
        try {
            const response = await fetch('/notifications/count');
            const data = await response.json();
            this.updateBadge(data.count);
        } catch (error) {
            console.error('Error updating notification count:', error);
        }
    }

    /**
     * Marque une notification comme lue
     */
    async markAsRead(id) {
        try {
            await fetch(`/notifications/mark-as-read/${id}`, {
                method: 'POST'
            });

            // Mettre à jour visuellement
            const item = document.querySelector(`[data-id="${id}"]`);
            if (item) {
                item.classList.remove('unread');
                item.classList.add('read');
                const dot = item.querySelector('.notification-dot');
                if (dot) dot.remove();
            }

            this.updateCount();

        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    /**
     * Marque toutes les notifications comme lues
     */
    async markAllAsRead() {
        try {
            await fetch('/notifications/mark-all-as-read', {
                method: 'POST'
            });

            // Mettre à jour visuellement
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                item.classList.add('read');
                const dot = item.querySelector('.notification-dot');
                if (dot) dot.remove();
            });

            this.updateBadge(0);

        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    /**
     * Configure le dropdown de notifications
     */
    setupDropdown() {
        const trigger = document.getElementById('notifications-trigger');
        const dropdown = document.getElementById('notifications-dropdown');

        if (!trigger || !dropdown) return;

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            dropdown.classList.toggle('show');
        });

        // Fermer si clic en dehors
        document.addEventListener('click', (e) => {
            if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Bouton "Tout marquer comme lu"
        const markAllBtn = document.getElementById('mark-all-read');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.markAllAsRead();
            });
        }
    }

    /**
     * Formate une date en "il y a X minutes/heures/jours"
     */
    timeAgo(dateString) {
        const date = new Date(dateString);
        const seconds = Math.floor((new Date() - date) / 1000);

        const intervals = {
            année: 31536000,
            mois: 2592000,
            semaine: 604800,
            jour: 86400,
            heure: 3600,
            minute: 60
        };

        for (const [name, secondsInInterval] of Object.entries(intervals)) {
            const interval = Math.floor(seconds / secondsInInterval);

            if (interval >= 1) {
                return `Il y a ${interval} ${name}${interval > 1 ? 's' : ''}`;
            }
        }

        return 'À l\'instant';
    }

    /**
     * Échappe le HTML pour prévenir les XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Demande la permission pour les notifications navigateur
     */
    static requestPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
}

// Initialiser le gestionnaire de notifications au chargement
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();

    // Demander la permission pour les notifications navigateur
    NotificationManager.requestPermission();
});
