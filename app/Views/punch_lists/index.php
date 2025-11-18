<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>📋 Liste des Réserves</h1>
        <p>Suivi des défauts et réserves de chantier</p>
    </div>
    <div>
        <a href="/punch-lists/create" class="btn btn-primary">
            <i class="icon">➕</i> Nouvelle réserve
        </a>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
            <div class="stat-label">Total réserves</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-danger">
            <div class="stat-value"><?= $stats['open'] ?? 0 ?></div>
            <div class="stat-label">Ouvertes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-warning">
            <div class="stat-value"><?= $stats['in_progress'] ?? 0 ?></div>
            <div class="stat-label">En cours</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-value"><?= $stats['closed'] ?? 0 ?></div>
            <div class="stat-label">Clôturées</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="filters-form">
            <div class="row">
                <div class="col-md-3">
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
                    <label>Statut</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Tous</option>
                        <option value="open" <?= $currentStatus === 'open' ? 'selected' : '' ?>>Ouvert</option>
                        <option value="in_progress" <?= $currentStatus === 'in_progress' ? 'selected' : '' ?>>En cours</option>
                        <option value="resolved" <?= $currentStatus === 'resolved' ? 'selected' : '' ?>>Résolu</option>
                        <option value="verified" <?= $currentStatus === 'verified' ? 'selected' : '' ?>>Vérifié</option>
                        <option value="closed" <?= $currentStatus === 'closed' ? 'selected' : '' ?>>Clôturé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Priorité</label>
                    <select name="priority" class="form-control" onchange="this.form.submit()">
                        <option value="">Toutes</option>
                        <option value="critique" <?= $currentPriority === 'critique' ? 'selected' : '' ?>>⚠️ Critique</option>
                        <option value="haute" <?= $currentPriority === 'haute' ? 'selected' : '' ?>>🔴 Haute</option>
                        <option value="moyenne" <?= $currentPriority === 'moyenne' ? 'selected' : '' ?>>🟡 Moyenne</option>
                        <option value="faible" <?= $currentPriority === 'faible' ? 'selected' : '' ?>>🟢 Faible</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des réserves -->
<div class="card">
    <div class="card-body">
        <?php if (empty($punchLists)): ?>
            <div class="empty-state">
                <p><strong>Aucune réserve</strong></p>
                <p>Créez votre première réserve pour suivre les défauts de chantier.</p>
                <a href="/punch-lists/create" class="btn btn-primary mt-2">Nouvelle réserve</a>
            </div>
        <?php else: ?>
            <div class="punch-list-grid">
                <?php foreach ($punchLists as $item): ?>
                    <div class="punch-card priority-<?= $item['priority'] ?> status-<?= $item['status'] ?>">
                        <div class="punch-header">
                            <div class="punch-priority">
                                <?php
                                    $priorities = [
                                        'critique' => '⚠️',
                                        'haute' => '🔴',
                                        'moyenne' => '🟡',
                                        'faible' => '🟢'
                                    ];
                                    echo $priorities[$item['priority']] ?? '';
                                ?>
                            </div>
                            <div class="punch-status">
                                <?php
                                    $statuses = [
                                        'open' => 'Ouvert',
                                        'in_progress' => 'En cours',
                                        'resolved' => 'Résolu',
                                        'verified' => 'Vérifié',
                                        'closed' => 'Clôturé'
                                    ];
                                    echo $statuses[$item['status']] ?? $item['status'];
                                ?>
                            </div>
                        </div>

                        <h3 class="punch-title">
                            <a href="/punch-lists/view/<?= $item['id'] ?>">
                                <?= htmlspecialchars($item['title']) ?>
                            </a>
                        </h3>

                        <p class="punch-description">
                            <?= htmlspecialchars(substr($item['description'], 0, 100)) ?>
                            <?= strlen($item['description']) > 100 ? '...' : '' ?>
                        </p>

                        <div class="punch-meta">
                            <span>📁 <?= htmlspecialchars($item['category']) ?></span>
                            <?php if ($item['zone']): ?>
                                <span>📍 <?= htmlspecialchars($item['zone']) ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($item['assigned_to']): ?>
                            <div class="punch-assigned">
                                👤 <?= htmlspecialchars($item['assigned_to_name'] ?? 'Utilisateur #' . $item['assigned_to']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="punch-footer">
                            <span class="punch-date">
                                <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                            </span>
                            <a href="/punch-lists/view/<?= $item['id'] ?>" class="btn btn-sm btn-primary">
                                Voir
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-card.stat-danger { border-left: 4px solid #ef4444; }
.stat-card.stat-warning { border-left: 4px solid #f59e0b; }
.stat-card.stat-success { border-left: 4px solid #10b981; }

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.punch-list-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.punch-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.punch-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.punch-card.priority-critique { border-left: 4px solid #dc2626; }
.punch-card.priority-haute { border-left: 4px solid #ef4444; }
.punch-card.priority-moyenne { border-left: 4px solid #f59e0b; }
.punch-card.priority-faible { border-left: 4px solid #10b981; }

.punch-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.punch-priority {
    font-size: 1.5rem;
}

.punch-status {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-open .punch-status { background: #fee2e2; color: #991b1b; }
.status-in_progress .punch-status { background: #fef3c7; color: #92400e; }
.status-resolved .punch-status { background: #dbeafe; color: #1e40af; }
.status-verified .punch-status { background: #d1fae5; color: #065f46; }
.status-closed .punch-status { background: #e5e7eb; color: #374151; }

.punch-title {
    font-size: 1.125rem;
    margin-bottom: 0.5rem;
}

.punch-title a {
    color: #1f2937;
    text-decoration: none;
}

.punch-title a:hover {
    color: #2563eb;
}

.punch-description {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.punch-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.punch-assigned {
    font-size: 0.875rem;
    color: #2563eb;
    margin-bottom: 1rem;
}

.punch-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.punch-date {
    font-size: 0.875rem;
    color: #6b7280;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
