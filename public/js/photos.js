/**
 * Gestionnaire avancé de photos avec GPS et prévisualisation
 * BatiSaaS v2.0
 */

class PhotoManager {
    constructor() {
        this.position = null;
        this.selectedFiles = [];
        this.init();
    }

    init() {
        this.setupGeolocation();
        this.setupDragAndDrop();
        this.setupFileInput();
        this.setupPreview();
    }

    /**
     * Récupération géolocalisation GPS
     */
    setupGeolocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.position = position;
                    console.log('GPS activé:', position.coords.latitude, position.coords.longitude);
                    this.showGPSStatus(true);
                },
                (error) => {
                    console.warn('GPS non disponible:', error.message);
                    this.showGPSStatus(false);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }
    }

    /**
     * Afficher statut GPS
     */
    showGPSStatus(active) {
        const statusEl = document.getElementById('gpsStatus');
        if (statusEl) {
            if (active) {
                statusEl.className = 'gps-status active';
                statusEl.innerHTML = '✓ GPS activé - Position enregistrée';
            } else {
                statusEl.className = 'gps-status inactive';
                statusEl.innerHTML = '⚠ GPS non disponible - Photos sans géolocalisation';
            }
        }
    }

    /**
     * Drag & Drop
     */
    setupDragAndDrop() {
        const dropZone = document.getElementById('dropZone');
        if (!dropZone) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('drag-over');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('drag-over');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            this.handleFiles(files);
        }, false);
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    /**
     * Input file
     */
    setupFileInput() {
        const fileInput = document.getElementById('photoInput');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                this.handleFiles(e.target.files);
            });
        }
    }

    /**
     * Traiter les fichiers
     */
    handleFiles(files) {
        const fileArray = Array.from(files);
        fileArray.forEach(file => {
            if (file.type.startsWith('image/')) {
                this.selectedFiles.push(file);
                this.createPreview(file);
            }
        });
    }

    /**
     * Créer prévisualisation
     */
    createPreview(file) {
        const previewGrid = document.getElementById('photoPreview');
        if (!previewGrid) return;

        const reader = new FileReader();
        const index = this.selectedFiles.indexOf(file);

        reader.onload = (e) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'photo-preview-item';
            previewItem.dataset.index = index;

            previewItem.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-photo" onclick="photoManager.removePhoto(${index})">×</button>
                <div class="metadata">
                    <input type="text"
                           name="title[${index}]"
                           class="form-control form-control-sm mb-1"
                           placeholder="Titre de la photo">
                    <input type="text"
                           name="zone[${index}]"
                           class="form-control form-control-sm mb-1"
                           placeholder="Zone (ex: Salon)">
                    <input type="text"
                           name="etage[${index}]"
                           class="form-control form-control-sm mb-1"
                           placeholder="Étage">
                    <select name="category[${index}]" class="form-control form-control-sm">
                        <option value="pendant">Pendant travaux</option>
                        <option value="avant">Avant travaux</option>
                        <option value="apres">Après travaux</option>
                        <option value="defaut">Défaut</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                ${this.position ? '<div class="gps-badge">📍 GPS</div>' : ''}
            `;

            previewGrid.appendChild(previewItem);

            // Extraire données EXIF
            this.extractEXIF(file, index);
        };

        reader.readAsDataURL(file);
    }

    /**
     * Extraire données EXIF
     */
    async extractEXIF(file, index) {
        // Placeholder pour extraction EXIF
        // En production, utiliser une bibliothèque comme exif-js
        console.log('EXIF extraction pour:', file.name);
    }

    /**
     * Supprimer photo
     */
    removePhoto(index) {
        this.selectedFiles.splice(index, 1);
        const previewItem = document.querySelector(`[data-index="${index}"]`);
        if (previewItem) {
            previewItem.remove();
        }
    }

    /**
     * Configuration prévisualisation
     */
    setupPreview() {
        // Initialiser lightbox pour voir photos en grand
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('photo-thumbnail')) {
                this.openLightbox(e.target.src);
            }
        });
    }

    /**
     * Ouvrir lightbox
     */
    openLightbox(src) {
        const lightbox = document.createElement('div');
        lightbox.className = 'lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-content">
                <span class="lightbox-close">&times;</span>
                <img src="${src}" alt="Photo">
            </div>
        `;

        document.body.appendChild(lightbox);

        lightbox.querySelector('.lightbox-close').addEventListener('click', () => {
            lightbox.remove();
        });

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                lightbox.remove();
            }
        });
    }

    /**
     * Obtenir coordonnées GPS
     */
    getGPSCoordinates() {
        if (this.position) {
            return {
                latitude: this.position.coords.latitude,
                longitude: this.position.coords.longitude,
                accuracy: this.position.coords.accuracy
            };
        }
        return null;
    }
}

// Styles CSS pour le gestionnaire de photos
const photoStyles = `
<style>
.drop-zone {
    border: 3px dashed #cbd5e1;
    border-radius: 12px;
    padding: 3rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: #f8fafc;
}

.drop-zone.drag-over {
    border-color: #2563eb;
    background: #eff6ff;
}

.drop-zone:hover {
    border-color: #94a3b8;
}

.gps-status {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin: 1rem 0;
    font-size: 0.875rem;
    text-align: center;
}

.gps-status.active {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.gps-status.inactive {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #f59e0b;
}

.photo-preview-item {
    position: relative;
    background: #f5f5f5;
    border-radius: 8px;
    overflow: hidden;
}

.photo-preview-item img {
    width: 100%;
    aspect-ratio: 4/3;
    object-fit: cover;
}

.remove-photo {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(220, 38, 38, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
    transition: all 0.2s;
}

.remove-photo:hover {
    background: rgba(220, 38, 38, 1);
    transform: scale(1.1);
}

.gps-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(37, 99, 235, 0.9);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
}

.lightbox {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.95);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.lightbox-content img {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
}

.lightbox-close {
    position: absolute;
    top: -40px;
    right: 0;
    color: white;
    font-size: 40px;
    cursor: pointer;
    font-weight: bold;
}

.lightbox-close:hover {
    color: #f59e0b;
}
</style>
`;

// Initialiser au chargement de la page
let photoManager;
document.addEventListener('DOMContentLoaded', () => {
    photoManager = new PhotoManager();
});
