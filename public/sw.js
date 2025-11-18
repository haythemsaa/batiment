/**
 * Service Worker pour BatiSaaS PWA
 * Mode hors ligne et synchronisation
 */

const CACHE_NAME = 'batisaas-v1.0.0';
const OFFLINE_URL = '/offline.html';

// Fichiers à mettre en cache immédiatement
const PRECACHE_ASSETS = [
    '/',
    '/offline.html',
    '/css/style.css',
    '/css/utilities.css',
    '/js/app.js',
    '/js/gantt.js',
    '/js/charts.js',
    '/js/notifications.js',
    '/manifest.json'
];

// Installation du Service Worker
self.addEventListener('install', (event) => {
    console.log('[SW] Installation...');

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Mise en cache des assets');
                return cache.addAll(PRECACHE_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activation du Service Worker
self.addEventListener('activate', (event) => {
    console.log('[SW] Activation...');

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => name !== CACHE_NAME)
                        .map((name) => caches.delete(name))
                );
            })
            .then(() => self.clients.claim())
    );
});

// Interception des requêtes
self.addEventListener('fetch', (event) => {
    // Ignorer les requêtes non-GET
    if (event.request.method !== 'GET') {
        return;
    }

    // Ignorer les requêtes de l'API (sauf pour les mettre en background sync)
    if (event.request.url.includes('/api/')) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                // Retourner le cache s'il existe
                if (cachedResponse) {
                    // Mettre à jour le cache en arrière-plan
                    fetch(event.request)
                        .then((response) => {
                            if (response && response.status === 200) {
                                caches.open(CACHE_NAME)
                                    .then((cache) => cache.put(event.request, response));
                            }
                        })
                        .catch(() => {});

                    return cachedResponse;
                }

                // Sinon, fetch depuis le réseau
                return fetch(event.request)
                    .then((response) => {
                        // Vérifier la validité de la réponse
                        if (!response || response.status !== 200 || response.type === 'error') {
                            return response;
                        }

                        // Cloner la réponse
                        const responseToCache = response.clone();

                        // Mettre en cache
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    })
                    .catch(() => {
                        // En cas d'erreur, afficher la page offline
                        if (event.request.mode === 'navigate') {
                            return caches.match(OFFLINE_URL);
                        }
                    });
            })
    );
});

// Background Sync pour les requêtes en attente
self.addEventListener('sync', (event) => {
    console.log('[SW] Background Sync:', event.tag);

    if (event.tag === 'sync-data') {
        event.waitUntil(syncPendingData());
    }
});

// Synchronisation des données en attente
async function syncPendingData() {
    try {
        const db = await openDB();
        const pendingRequests = await getPendingRequests(db);

        for (const request of pendingRequests) {
            try {
                const response = await fetch(request.url, request.options);

                if (response.ok) {
                    await removePendingRequest(db, request.id);
                    console.log('[SW] Requête synchronisée:', request.url);
                }
            } catch (error) {
                console.error('[SW] Erreur de synchronisation:', error);
            }
        }
    } catch (error) {
        console.error('[SW] Erreur sync:', error);
    }
}

// Notifications push
self.addEventListener('push', (event) => {
    console.log('[SW] Push reçu');

    let data = {};
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = { title: 'BatiSaaS', body: event.data.text() };
        }
    }

    const options = {
        body: data.body || 'Nouvelle notification',
        icon: '/images/icon-192x192.png',
        badge: '/images/badge-72x72.png',
        vibrate: [200, 100, 200],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: data.id || 'notification',
            url: data.url || '/'
        },
        actions: [
            {
                action: 'view',
                title: 'Voir',
                icon: '/images/view-icon.png'
            },
            {
                action: 'close',
                title: 'Fermer',
                icon: '/images/close-icon.png'
            }
        ],
        tag: data.tag || 'default',
        renotify: true,
        requireInteraction: false
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'BatiSaaS', options)
    );
});

// Clic sur notification
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification cliquée:', event.action);

    event.notification.close();

    if (event.action === 'view') {
        const urlToOpen = event.notification.data.url || '/';

        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true })
                .then((clientList) => {
                    // Chercher une fenêtre ouverte
                    for (const client of clientList) {
                        if (client.url === urlToOpen && 'focus' in client) {
                            return client.focus();
                        }
                    }

                    // Ouvrir une nouvelle fenêtre
                    if (clients.openWindow) {
                        return clients.openWindow(urlToOpen);
                    }
                })
        );
    }
});

// Partage de fichiers
self.addEventListener('fetch', (event) => {
    if (event.request.url.endsWith('/share') && event.request.method === 'POST') {
        event.respondWith(
            (async () => {
                const formData = await event.request.formData();
                const title = formData.get('title');
                const text = formData.get('text');
                const url = formData.get('url');
                const files = formData.getAll('photos');

                // Traiter les fichiers partagés
                console.log('[SW] Fichiers partagés:', files.length);

                // Rediriger vers la page d'upload
                return Response.redirect('/photos/upload?shared=true', 303);
            })()
        );
    }
});

// Gestion de la connectivité
self.addEventListener('online', () => {
    console.log('[SW] Connexion rétablie');
    self.registration.sync.register('sync-data');
});

self.addEventListener('offline', () => {
    console.log('[SW] Connexion perdue');
});

// Helpers IndexedDB pour le mode offline
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('BatiSaaSDB', 1);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            if (!db.objectStoreNames.contains('pendingRequests')) {
                db.createObjectStore('pendingRequests', { keyPath: 'id', autoIncrement: true });
            }

            if (!db.objectStoreNames.contains('offlineData')) {
                db.createObjectStore('offlineData', { keyPath: 'type' });
            }
        };
    });
}

function getPendingRequests(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['pendingRequests'], 'readonly');
        const store = transaction.objectStore('pendingRequests');
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function removePendingRequest(db, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['pendingRequests'], 'readwrite');
        const store = transaction.objectStore('pendingRequests');
        const request = store.delete(id);

        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

console.log('[SW] Service Worker chargé');
