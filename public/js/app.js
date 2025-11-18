/**
 * BatiSaaS - JavaScript principal
 */

// Auto-masquage des alertes après 5 secondes
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Confirmation de suppression
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Êtes-vous sûr ?')) {
                e.preventDefault();
            }
        });
    });

    // Toggle mobile menu
    const mobileMenuBtn = document.querySelector('.mobile-menu-toggle');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            document.querySelector('.navbar-menu').classList.toggle('active');
        });
    }

    // Gestion des lignes de devis/factures dynamiques
    initializeItemsManager();
});

/**
 * Gestion des lignes de devis/factures
 */
function initializeItemsManager() {
    const addItemBtn = document.getElementById('add-item');
    if (!addItemBtn) return;

    addItemBtn.addEventListener('click', function() {
        const itemsContainer = document.getElementById('items-container');
        const newItem = createItemRow();
        itemsContainer.appendChild(newItem);
        updateTotals();
    });

    // Event delegation pour les boutons de suppression et les champs
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('.item-row').remove();
            updateTotals();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.matches('.item-quantity, .item-price')) {
            updateTotals();
        }
    });
}

/**
 * Crée une nouvelle ligne de devis/facture
 */
function createItemRow() {
    const div = document.createElement('div');
    div.className = 'item-row';
    div.innerHTML = `
        <div class="form-row">
            <div class="form-group" style="flex: 2">
                <input type="text" name="item_description[]" placeholder="Description" required>
            </div>
            <div class="form-group">
                <input type="number" name="item_quantity[]" class="item-quantity" value="1" min="0" step="0.01" required>
            </div>
            <div class="form-group">
                <input type="text" name="item_unit[]" value="unité">
            </div>
            <div class="form-group">
                <input type="number" name="item_price[]" class="item-price" value="0" min="0" step="0.01" required>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-danger btn-sm remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    return div;
}

/**
 * Met à jour les totaux
 */
function updateTotals() {
    let subtotal = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        subtotal += quantity * price;
    });

    const discountPercent = parseFloat(document.querySelector('[name="discount_percent"]')?.value) || 0;
    const discountAmount = subtotal * (discountPercent / 100);
    const subtotalAfterDiscount = subtotal - discountAmount;
    const tvaRate = 20; // TVA par défaut
    const tvaAmount = subtotalAfterDiscount * (tvaRate / 100);
    const total = subtotalAfterDiscount + tvaAmount;

    // Affichage des totaux
    const subtotalEl = document.getElementById('subtotal');
    const discountEl = document.getElementById('discount-amount');
    const tvaEl = document.getElementById('tva-amount');
    const totalEl = document.getElementById('total');

    if (subtotalEl) subtotalEl.textContent = formatCurrency(subtotal);
    if (discountEl) discountEl.textContent = formatCurrency(discountAmount);
    if (tvaEl) tvaEl.textContent = formatCurrency(tvaAmount);
    if (totalEl) totalEl.textContent = formatCurrency(total);
}

/**
 * Formate un montant en euros
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

/**
 * Fonctions utilitaires
 */

// Copier dans le presse-papier
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copié dans le presse-papier', 'success');
    });
}

// Notification toast
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Export des fonctions globales
window.BatiSaaS = {
    copyToClipboard,
    showNotification,
    updateTotals,
    formatCurrency
};
