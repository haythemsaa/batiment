<div class="page-header">
    <h1>
        <i class="fas fa-cog"></i>
        Paramètres de l'entreprise
    </h1>
</div>

<form method="POST" action="/settings/update">
    <!-- Informations entreprise -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-building"></i> Informations de l'entreprise</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="name">Nom de l'entreprise *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($company['name']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="siret">SIRET</label>
                    <input type="text" id="siret" name="siret" value="<?= htmlspecialchars($company['siret'] ?? '') ?>" maxlength="14">
                </div>

                <div class="form-group">
                    <label for="tva_number">N° TVA intracommunautaire</label>
                    <input type="text" id="tva_number" name="tva_number" value="<?= htmlspecialchars($company['tva_number'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="capital">Capital social (€)</label>
                    <input type="number" id="capital" name="capital" value="<?= $company['capital'] ?? '' ?>" step="0.01">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" rows="2"><?= htmlspecialchars($company['address'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="postal_code">Code postal</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($company['postal_code'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($company['city'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($company['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($company['email'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Paramètres de facturation -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-receipt"></i> Paramètres de facturation</h2>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="tva_rate">Taux de TVA par défaut (%)</label>
                    <input type="number" id="tva_rate" name="tva_rate" value="<?= $company['tva_rate'] ?? 20 ?>" step="0.01" min="0" max="100">
                </div>

                <div class="form-group">
                    <label for="invoice_prefix">Préfixe factures</label>
                    <input type="text" id="invoice_prefix" name="invoice_prefix" value="<?= htmlspecialchars($settings['invoice_prefix'] ?? 'FA') ?>" maxlength="10">
                </div>

                <div class="form-group">
                    <label for="quote_prefix">Préfixe devis</label>
                    <input type="text" id="quote_prefix" name="quote_prefix" value="<?= htmlspecialchars($settings['quote_prefix'] ?? 'DEV') ?>" maxlength="10">
                </div>
            </div>

            <div class="form-group">
                <label for="payment_terms">Délai de paiement (jours)</label>
                <input type="number" id="payment_terms" name="payment_terms" value="<?= $settings['payment_terms'] ?? 30 ?>" min="0">
            </div>

            <div class="form-group">
                <label for="invoice_footer">Pied de page des documents</label>
                <textarea id="invoice_footer" name="invoice_footer" rows="3" placeholder="Texte affiché en bas des devis et factures"><?= htmlspecialchars($settings['invoice_footer'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Logo -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-image"></i> Logo de l'entreprise</h2>
        </div>
        <div class="card-body">
            <?php if ($company['logo']): ?>
            <div class="current-logo" style="margin-bottom: 1rem;">
                <img src="/storage/uploads/<?= htmlspecialchars($company['logo']) ?>" alt="Logo" style="max-width: 200px; max-height: 100px;">
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="logo">Télécharger un nouveau logo</label>
                <input type="file" id="logo" name="logo" accept="image/*">
                <small class="text-muted">Format recommandé : PNG ou JPG, 400x200px maximum</small>
            </div>
        </div>
    </div>

    <!-- Informations d'abonnement -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-credit-card"></i> Abonnement</h2>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Plan actuel</label>
                    <strong><?= ucfirst($company['subscription_plan'] ?? 'basic') ?></strong>
                </div>
                <div class="info-item">
                    <label>Statut</label>
                    <span class="badge badge-<?= $company['status'] == 'active' ? 'success' : 'danger' ?>">
                        <?= $company['status'] == 'active' ? 'Actif' : 'Inactif' ?>
                    </span>
                </div>
                <?php if ($company['subscription_expires_at']): ?>
                <div class="info-item">
                    <label>Expire le</label>
                    <span><?= date('d/m/Y', strtotime($company['subscription_expires_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i>
            Enregistrer les paramètres
        </button>
    </div>
</form>
