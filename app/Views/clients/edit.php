<div class="page-header">
    <h1>
        <i class="fas fa-users"></i>
        Modifier le client
    </h1>
    <a href="/clients" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i>
        Retour
    </a>
</div>

<form method="POST" action="/clients/update/<?= $client['id'] ?>">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-info-circle"></i> Type de client</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Type de client *</label>
                <div style="display: flex; gap: 2rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="type" value="individual" <?= $client['type'] == 'individual' ? 'checked' : '' ?> onchange="toggleClientType()">
                        <span>Particulier</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="type" value="company" <?= $client['type'] == 'company' ? 'checked' : '' ?> onchange="toggleClientType()">
                        <span>Entreprise</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-header">
                <h2 id="info-header"><i class="fas fa-user"></i> Informations</h2>
            </div>
            <div class="card-body">
                <div id="individual-fields" style="display: <?= $client['type'] == 'individual' ? 'block' : 'none' ?>">
                    <div class="form-group">
                        <label for="civility">Civilité</label>
                        <select id="civility" name="civility">
                            <option value="">-- Sélectionner --</option>
                            <option value="M" <?= $client['civility'] == 'M' ? 'selected' : '' ?>>M.</option>
                            <option value="Mme" <?= $client['civility'] == 'Mme' ? 'selected' : '' ?>>Mme</option>
                            <option value="Autre" <?= $client['civility'] == 'Autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Prénom *</label>
                            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($client['first_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Nom *</label>
                            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($client['last_name'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div id="company-fields" style="display: <?= $client['type'] == 'company' ? 'block' : 'none' ?>">
                    <div class="form-group">
                        <label for="company_name">Raison sociale *</label>
                        <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($client['company_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="siret">SIRET</label>
                        <input type="text" id="siret" name="siret" value="<?= htmlspecialchars($client['siret'] ?? '') ?>" maxlength="14">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-phone"></i> Contact</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($client['email'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile</label>
                        <input type="tel" id="mobile" name="mobile" value="<?= htmlspecialchars($client['mobile'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-map-marker-alt"></i> Adresse</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" rows="2"><?= htmlspecialchars($client['address'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="postal_code">Code postal</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($client['postal_code'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($client['city'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="country">Pays</label>
                    <input type="text" id="country" name="country" value="<?= htmlspecialchars($client['country'] ?? 'France') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-sticky-note"></i> Notes</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="notes">Notes internes</label>
                <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($client['notes'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="/clients" class="btn btn-outline">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i>
            Enregistrer les modifications
        </button>
    </div>
</form>

<script>
function toggleClientType() {
    const type = document.querySelector('input[name="type"]:checked').value;
    const individualFields = document.getElementById('individual-fields');
    const companyFields = document.getElementById('company-fields');
    const infoHeader = document.getElementById('info-header');

    if (type === 'company') {
        individualFields.style.display = 'none';
        companyFields.style.display = 'block';
        infoHeader.innerHTML = '<i class="fas fa-building"></i> Informations entreprise';
        document.getElementById('first_name').removeAttribute('required');
        document.getElementById('last_name').removeAttribute('required');
        document.getElementById('company_name').setAttribute('required', 'required');
    } else {
        individualFields.style.display = 'block';
        companyFields.style.display = 'none';
        infoHeader.innerHTML = '<i class="fas fa-user"></i> Informations personnelles';
        document.getElementById('first_name').setAttribute('required', 'required');
        document.getElementById('last_name').setAttribute('required', 'required');
        document.getElementById('company_name').removeAttribute('required');
    }
}

// Init au chargement
document.addEventListener('DOMContentLoaded', toggleClientType);
</script>
