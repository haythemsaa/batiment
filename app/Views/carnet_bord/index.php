<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>📔 Carnet de Bord</h1>
        <p><?= htmlspecialchars($chantier['name']) ?></p>
    </div>
    <div>
        <a href="/carnet-bord/today/<?= $chantier['id'] ?>" class="btn btn-primary">
            <i class="icon">✏️</i> Entrée du jour
        </a>
        <a href="/carnet-bord/weekly/<?= $chantier['id'] ?>" class="btn btn-secondary">
            📊 Rapport hebdo
        </a>
        <a href="/carnet-bord/export/<?= $chantier['id'] ?>" class="btn btn-secondary">
            📄 Export PDF
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>🏗️ <?= htmlspecialchars($chantier['name']) ?></h3>
                <p class="text-muted mb-0">
                    <?= date('d/m/Y', strtotime($chantier['date_debut'])) ?>
                    →
                    <?= date('d/m/Y', strtotime($chantier['date_fin'])) ?>
                </p>
            </div>
            <div>
                <a href="/carnet-bord/calendar/<?= $chantier['id'] ?>" class="btn btn-secondary">
                    📅 Vue calendrier
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Entrées du carnet de bord -->
<div class="timeline">
    <?php if (empty($entries)): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <p><strong>Aucune entrée</strong></p>
                    <p>Commencez par remplir l'entrée du jour.</p>
                    <a href="/carnet-bord/today/<?= $chantier['id'] ?>" class="btn btn-primary mt-2">
                        Entrée du jour
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($entries as $entry): ?>
            <div class="timeline-item">
                <div class="timeline-marker">
                    <?= date('d', strtotime($entry['date'])) ?>
                    <small><?= date('M', strtotime($entry['date'])) ?></small>
                </div>
                <div class="timeline-content">
                    <div class="card">
                        <div class="card-body">
                            <div class="entry-header">
                                <h4><?= date('l d F Y', strtotime($entry['date'])) ?></h4>
                                <div class="entry-meta">
                                    <?php if ($entry['weather']): ?>
                                        <span class="weather-badge">
                                            <?php
                                                $weatherIcons = [
                                                    'ensoleille' => '☀️',
                                                    'nuageux' => '⛅',
                                                    'pluie' => '🌧️',
                                                    'neige' => '🌨️'
                                                ];
                                                echo $weatherIcons[$entry['weather']] ?? '🌤️';
                                            ?>
                                            <?= htmlspecialchars($entry['weather']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($entry['temperature']): ?>
                                        <span class="temp-badge">🌡️ <?= $entry['temperature'] ?>°C</span>
                                    <?php endif; ?>
                                    <?php if ($entry['present_workers']): ?>
                                        <span class="workers-badge">👷 <?= $entry['present_workers'] ?> personnes</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="entry-section">
                                <h5>📋 Travaux réalisés</h5>
                                <p><?= nl2br(htmlspecialchars($entry['work_done'])) ?></p>
                            </div>

                            <?php if ($entry['materials_used']): ?>
                                <div class="entry-section">
                                    <h5>📦 Matériaux utilisés</h5>
                                    <p><?= nl2br(htmlspecialchars($entry['materials_used'])) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($entry['incidents']): ?>
                                <div class="entry-section alert-warning">
                                    <h5>⚠️ Incidents / Observations</h5>
                                    <p><?= nl2br(htmlspecialchars($entry['incidents'])) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($entry['photos']): ?>
                                <div class="entry-photos">
                                    <?php
                                        $photos = json_decode($entry['photos'], true);
                                        if ($photos):
                                    ?>
                                        <h5>📸 Photos (<?= count($photos) ?>)</h5>
                                        <div class="photo-grid-small">
                                            <?php foreach ($photos as $photo): ?>
                                                <img src="/uploads/carnet_bord/<?= $photo ?>"
                                                     alt="Photo"
                                                     class="thumbnail"
                                                     onclick="viewPhoto(this.src)">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="entry-footer">
                                <small class="text-muted">
                                    Par <?= htmlspecialchars($entry['created_by_name']) ?>
                                    le <?= date('d/m/Y à H:i', strtotime($entry['created_at'])) ?>
                                </small>
                                <div>
                                    <a href="/carnet-bord/view/<?= $entry['id'] ?>" class="btn btn-sm btn-secondary">
                                        Voir
                                    </a>
                                    <a href="/carnet-bord/edit/<?= $entry['id'] ?>" class="btn btn-sm btn-primary">
                                        Éditer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal pour voir photo -->
<div id="photoModal" class="modal" onclick="this.style.display='none'">
    <span class="modal-close">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<style>
.timeline {
    position: relative;
    padding-left: 3rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 2rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -3rem;
    width: 4rem;
    height: 4rem;
    background: white;
    border: 3px solid #2563eb;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #2563eb;
    z-index: 1;
}

.timeline-marker small {
    font-size: 0.625rem;
    text-transform: uppercase;
}

.timeline-content {
    margin-left: 1rem;
}

.entry-header {
    margin-bottom: 1.5rem;
}

.entry-header h4 {
    margin-bottom: 0.5rem;
    color: #1f2937;
    text-transform: capitalize;
}

.entry-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.weather-badge,
.temp-badge,
.workers-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.875rem;
    background: #f3f4f6;
}

.entry-section {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
}

.entry-section h5 {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #374151;
}

.entry-section.alert-warning {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
}

.entry-photos {
    margin-bottom: 1.5rem;
}

.photo-grid-small {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.thumbnail {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 6px;
    cursor: pointer;
    transition: transform 0.2s;
}

.thumbnail:hover {
    transform: scale(1.05);
}

.entry-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    padding-top: 50px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
}

.modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 90%;
}

.modal-close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}
</style>

<script>
function viewPhoto(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('photoModal').style.display = 'block';
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
