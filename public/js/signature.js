/**
 * BatiSaaS - Gestion de signature électronique
 * Utilise Signature Pad pour capturer les signatures
 */

class SignatureManager {
    constructor(canvasId, options = {}) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) {
            console.error('Canvas not found:', canvasId);
            return;
        }

        this.options = Object.assign({
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 0.5,
            maxWidth: 2.5,
            onBegin: null,
            onEnd: null
        }, options);

        this.signaturePad = new SignaturePad(this.canvas, this.options);
        this.resizeCanvas();

        window.addEventListener('resize', () => this.resizeCanvas());
        this.setupButtons();
    }

    /**
     * Redimensionne le canvas
     */
    resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = this.canvas.getBoundingClientRect();

        this.canvas.width = rect.width * ratio;
        this.canvas.height = rect.height * ratio;
        this.canvas.getContext('2d').scale(ratio, ratio);

        this.signaturePad.clear();
    }

    /**
     * Configure les boutons de contrôle
     */
    setupButtons() {
        // Bouton effacer
        const clearBtn = document.getElementById(this.canvas.id + '-clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => this.clear());
        }

        // Bouton annuler
        const undoBtn = document.getElementById(this.canvas.id + '-undo');
        if (undoBtn) {
            undoBtn.addEventListener('click', () => this.undo());
        }

        // Bouton sauvegarder
        const saveBtn = document.getElementById(this.canvas.id + '-save');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.save());
        }
    }

    /**
     * Efface la signature
     */
    clear() {
        this.signaturePad.clear();
    }

    /**
     * Annule la dernière action
     */
    undo() {
        const data = this.signaturePad.toData();
        if (data) {
            data.pop();
            this.signaturePad.fromData(data);
        }
    }

    /**
     * Vérifie si le pad est vide
     */
    isEmpty() {
        return this.signaturePad.isEmpty();
    }

    /**
     * Récupère la signature en base64
     */
    toDataURL(type = 'image/png') {
        if (this.isEmpty()) {
            alert('Veuillez d\'abord signer');
            return null;
        }
        return this.signaturePad.toDataURL(type);
    }

    /**
     * Charge une signature depuis base64
     */
    fromDataURL(dataURL) {
        this.signaturePad.fromDataURL(dataURL);
    }

    /**
     * Sauvegarde la signature
     */
    save() {
        const dataURL = this.toDataURL();
        if (!dataURL) return;

        // Stocke dans un champ hidden
        const hiddenInput = document.getElementById(this.canvas.id + '-data');
        if (hiddenInput) {
            hiddenInput.value = dataURL;
        }

        // Callback personnalisé
        if (this.options.onSave) {
            this.options.onSave(dataURL);
        }

        return dataURL;
    }

    /**
     * Active/désactive le pad
     */
    setEnabled(enabled) {
        if (enabled) {
            this.signaturePad.on();
        } else {
            this.signaturePad.off();
        }
    }
}

/**
 * Initialise les signatures sur la page
 */
function initSignatures() {
    const signatureCanvases = document.querySelectorAll('[data-signature]');

    signatureCanvases.forEach(canvas => {
        const options = {
            onEnd: () => {
                // Auto-sauvegarde à chaque fin de trait
                const manager = canvas._signatureManager;
                if (manager) {
                    const hiddenInput = document.getElementById(canvas.id + '-data');
                    if (hiddenInput && !manager.isEmpty()) {
                        hiddenInput.value = manager.toDataURL();
                    }
                }
            }
        };

        canvas._signatureManager = new SignatureManager(canvas.id, options);
    });
}

/**
 * Récupère le gestionnaire de signature d'un canvas
 */
function getSignatureManager(canvasId) {
    const canvas = document.getElementById(canvasId);
    return canvas ? canvas._signatureManager : null;
}

// Auto-init au chargement
document.addEventListener('DOMContentLoaded', initSignatures);

// Export pour utilisation globale
window.SignatureManager = SignatureManager;
window.getSignatureManager = getSignatureManager;
