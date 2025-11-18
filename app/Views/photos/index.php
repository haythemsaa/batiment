<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>📸 Galerie Photos</h1>
        <p>Gérez vos photos de chantiers avec géolocalisation</p>
    </div>
    <div>
        <a href="/photos/upload" class="btn btn-primary">
            <i class="icon">📤</i> Upload Photos
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="filters-form">
            <div class="row">
                <div class="col-md-4">
                    <label>Chantier</label>
                    <select name="chantier_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Tous les chantiers</option>
                        <?php foreach ($chantiers as $chantier): ?>
                            <option value="<?= $chantier['id'] ?>" <?= $currentChantierId == $chantier['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($chantier['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Catégorie</label>
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="">Toutes</option>
                        <option value="avant" <?= $currentCategory === 'avant' ? 'selected' : '' ?>>Avant travaux</option>
                        <option value="pendant" <?= $currentCategory === 'pendant' ? 'selected' : '' ?>>Pendant travaux</option>
                        <option value="apres" <?= $currentCategory === 'apres' ? 'selected' : '' ?>>Après travaux</option>
                        <option value="defaut" <?= $currentCategory === 'defaut' ? 'selected' : '' ?>>Défauts</option>
                        <option value="autre" <?= $currentCategory === 'autre' ? 'selected' : '' ?>>Autre</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Zone</label>
                    <input type="text" name="zone" class="form-control" value="<?= htmlspecialchars($currentZone ?? '') ?>" placeholder="Ex: Salon, Cuisine...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">Filtrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Galerie par chantier et catégorie -->
<?php if (empty($groupedPhotos)): ?>
    <div class="alert alert-info">
        <p><strong>Aucune photo</strong></p>
        <p>Commencez par uploader vos premières photos de chantier.</p>
        <a href="/photos/upload" class="btn btn-primary btn-sm mt-2">Upload Photos</a>
    </div>
<?php else: ?>
    <?php foreach ($groupedPhotos as $chantierId => $categories): ?>
        <?php
            $chantier = array_values(array_filter($chantiers, fn($c) => $c['id'] == $chantierId))[0] ?? null;
            if (!$chantier) continue;
        ?>

        <div class="card mb-4">
            <div class="card-header">
                <h3>
                    <a href="/chantiers/view/<?= $chantierId ?>">
                        <?= htmlspecialchars($chantier['name']) ?>
                    </a>
                </h3>
                <div class="card-actions">
                    <a href="/photos/before-after/<?= $chantierId ?>" class="btn btn-sm btn-secondary">
                        Avant/Après
                    </a>
                    <a href="/photos/report/<?= $chantierId ?>" class="btn btn-sm btn-secondary">
                        Rapport PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php foreach ($categories as $category => $photos): ?>
                    <div class="photo-category mb-4">
                        <h4 class="mb-3">
                            <?php
                                $categoryLabels = [
                                    'avant' => '🏗️ Avant travaux',
                                    'pendant' => '🔨 Pendant travaux',
                                    'apres' => '✅ Après travaux',
                                    'defaut' => '⚠️ Défauts',
                                    'autre' => '📷 Autre'
                                ];
                                echo $categoryLabels[$category] ?? $category;
                            ?>
                            (<?= count($photos) ?>)
                        </h4>

                        <div class="photo-grid">
                            <?php foreach ($photos as $photo): ?>
                                <div class="photo-card">
                                    <a href="/photos/view/<?= $photo['id'] ?>" class="photo-link">
                                        <img src="<?= $photo['thumbnail_path'] ?? $photo['file_path'] ?>"
                                             alt="<?= htmlspecialchars($photo['title'] ?? '') ?>"
                                             loading="lazy">
                                        <?php if ($photo['zone']): ?>
                                            <div class="photo-badge"><?= htmlspecialchars($photo['zone']) ?></div>
                                        <?php endif; ?>
                                        <?php if ($photo['latitude'] && $photo['longitude']): ?>
                                            <div class="photo-badge badge-gps">📍 GPS</div>
                                        <?php endif; ?>
                                    </a>
                                    <div class="photo-info">
                                        <div class="photo-title">
                                            <?= htmlspecialchars($photo['title'] ?: 'Sans titre') ?>
                                        </div>
                                        <div class="photo-meta">
                                            <?= date('d/m/Y', strtotime($photo['taken_at'] ?? $photo['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.photo-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.photo-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.photo-link {
    position: relative;
    display: block;
    aspect-ratio: 4/3;
    overflow: hidden;
    background: #f5f5f5;
}

.photo-link img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
}

.badge-gps {
    top: auto;
    bottom: 8px;
    background: rgba(37, 99, 235, 0.9);
}

.photo-info {
    padding: 12px;
}

.photo-title {
    font-weight: 600;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.photo-meta {
    font-size: 0.875rem;
    color: #666;
}

.photo-category h4 {
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 8px;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
