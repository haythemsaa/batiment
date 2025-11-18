<div class="page-header">
    <h1>
        <i class="fas fa-file-invoice"></i>
        Nouveau devis
    </h1>
    <a href="/devis" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i>
        Retour
    </a>
</div>

<form method="POST" action="/devis/store" id="devis-form">
    <div class="grid-2">
        <!-- Informations générales -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Informations générales</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="number">Numéro *</label>
                    <input type="text" id="number" name="number" value="<?= $number ?>" required readonly>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date">Date *</label>
                        <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="validity_date">Date de validité</label>
                        <input type="date" id="validity_date" name="validity_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="client_id">Client *</label>
                    <select id="client_id" name="client_id" required>
                        <option value="">-- Sélectionner un client --</option>
                        <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>">
                            <?= $client['type'] === 'company'
                                ? htmlspecialchars($client['company_name'])
                                : htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Titre</label>
                    <input type="text" id="title" name="title" placeholder="Ex: Rénovation salle de bain">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="draft">Brouillon</option>
                        <option value="sent">Envoyé</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Notes et conditions -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-sticky-note"></i> Notes et conditions</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="notes">Notes internes</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="Notes visibles uniquement par vous"></textarea>
                </div>

                <div class="form-group">
                    <label for="terms">Conditions générales</label>
                    <textarea id="terms" name="terms" rows="6" placeholder="Conditions affichées sur le devis">Devis valable 30 jours.
Acompte de 30% à la commande.
Paiement du solde à la livraison.</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Lignes du devis -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-list"></i> Lignes du devis</h2>
            <button type="button" id="add-item" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i>
                Ajouter une ligne
            </button>
        </div>
        <div class="card-body">
            <div id="items-container">
                <div class="item-row">
                    <div class="form-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 0.5rem; align-items: end;">
                        <div class="form-group">
                            <label>Description *</label>
                            <input type="text" name="item_description[]" placeholder="Description de la prestation" required>
                        </div>
                        <div class="form-group">
                            <label>Quantité *</label>
                            <input type="number" name="item_quantity[]" class="item-quantity" value="1" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Unité</label>
                            <input type="text" name="item_unit[]" value="unité">
                        </div>
                        <div class="form-group">
                            <label>Prix unitaire HT *</label>
                            <input type="number" name="item_price[]" class="item-price" value="0" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm remove-item" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Totaux -->
    <div class="card">
        <div class="card-body">
            <div style="max-width: 400px; margin-left: auto;">
                <div class="form-group">
                    <label for="discount_percent">Remise (%)</label>
                    <input type="number" id="discount_percent" name="discount_percent" value="0" min="0" max="100" step="0.01">
                </div>

                <div class="totals">
                    <div class="total-row">
                        <span>Sous-total HT :</span>
                        <strong id="subtotal">0,00 €</strong>
                    </div>
                    <div class="total-row">
                        <span>Remise :</span>
                        <strong id="discount-amount">0,00 €</strong>
                    </div>
                    <div class="total-row">
                        <span>TVA (20%) :</span>
                        <strong id="tva-amount">0,00 €</strong>
                    </div>
                    <div class="total-row total-final">
                        <span>Total TTC :</span>
                        <strong id="total">0,00 €</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="/devis" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i>
            Enregistrer le devis
        </button>
    </div>
</form>

<style>
.totals {
    border-top: 2px solid var(--border-color);
    padding-top: 1rem;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.total-final {
    border-top: 2px solid var(--border-color);
    font-size: 1.25rem;
    color: var(--primary-color);
    padding-top: 1rem;
    margin-top: 0.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.item-row {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.item-row:last-child {
    border-bottom: none;
}

.btn-group {
    display: flex;
    gap: 0.25rem;
}
