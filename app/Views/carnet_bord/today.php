<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>✏️ Entrée du Jour</h1>
        <p><?= htmlspecialchars($chantier['name']) ?> - <?= date('d/m/Y', strtotime($date)) ?></p>
    </div>
    <div>
        <a href="/carnet-bord/<?= $chantier['id'] ?>" class="btn btn-secondary">
            ← Retour au carnet
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Météo</label>
                        <select name="weather" class="form-control">
                            <option value="">Sélectionner</option>
                            <option value="ensoleille" <?= ($entry['weather'] ?? '') === 'ensoleille' ? 'selected' : '' ?>>
                                ☀️ Ensoleillé
                            </option>
                            <option value="nuageux" <?= ($entry['weather'] ?? '') === 'nuageux' ? 'selected' : '' ?>>
                                ⛅ Nuageux
                            </option>
                            <option value="pluie" <?= ($entry['weather'] ?? '') === 'pluie' ? 'selected' : '' ?>>
                                🌧️ Pluie
                            </option>
                            <option value="neige" <?= ($entry['weather'] ?? '') === 'neige' ? 'selected' : '' ?>>
                                🌨️ Neige
                            </option>
                            <option value="orage" <?= ($entry['weather'] ?? '') === 'orage' ? 'selected' : '' ?>>
                                ⛈️ Orage
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Température (°C)</label>
                        <input type="number"
                               name="temperature"
                               class="form-control"
                               value="<?= htmlspecialchars($entry['temperature'] ?? '') ?>"
                               placeholder="Ex: 18"
                               step="0.1">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nombre de personnes présentes</label>
                        <input type="number"
                               name="present_workers"
                               class="form-control"
                               value="<?= htmlspecialchars($entry['present_workers'] ?? '') ?>"
                               placeholder="Ex: 8"
                               min="0">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Travaux réalisés aujourd'hui *</label>
                <textarea name="work_done"
                          class="form-control"
                          rows="6"
                          required
                          placeholder="Décrire les travaux effectués..."><? = htmlspecialchars($entry['work_done'] ?? '') ?></textarea>
                <small class="text-muted">
                    Détaillez les travaux réalisés, les tâches accomplies, l'avancement
                </small>
            </div>

            <div class="form-group">
                <label>Matériaux utilisés</label>
                <textarea name="materials_used"
                          class="form-control"
                          rows="4"
                          placeholder="Lister les matériaux utilisés et quantités..."><? = htmlspecialchars($entry['materials_used'] ?? '') ?></textarea>
                <small class="text-muted">
                    Ex: Ciment 50 sacs, Parpaings 200 unités, etc.
                </small>
            </div>

            <div class="form-group">
                <label>Incidents, observations, points d'attention</label>
                <textarea name="incidents"
                          class="form-control"
                          rows="4"
                          placeholder="Noter tout incident, retard, ou point nécessitant attention..."><? = htmlspecialchars($entry['incidents'] ?? '') ?></textarea>
                <small class="text-muted">
                    Problèmes rencontrés, retards, manques, sécurité, etc.
                </small>
            </div>

            <div class="form-group">
                <label>Photos du jour</label>
                <input type="file"
                       name="photos[]"
                       class="form-control"
                       accept="image/*"
                       multiple
                       capture="environment">
                <small class="text-muted">
                    Formats: JPG, PNG - Plusieurs photos possibles
                </small>
            </div>

            <?php if (!empty($entry['photos'])): ?>
                <?php
                    $existingPhotos = json_decode($entry['photos'], true);
                    if ($existingPhotos):
                ?>
                    <div class="existing-photos mb-3">
                        <label>Photos existantes</label>
                        <div class="photo-grid">
                            <?php foreach ($existingPhotos as $photo): ?>
                                <img src="/uploads/carnet_bord/<?= $photo ?>"
                                     alt="Photo"
                                     class="thumbnail">
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="icon">💾</i> Enregistrer l'entrée
                </button>
                <a href="/carnet-bord/<?= $chantier['id'] ?>" class="btn btn-secondary">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.5rem;
}

.thumbnail {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 6px;
}

.form-actions {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
