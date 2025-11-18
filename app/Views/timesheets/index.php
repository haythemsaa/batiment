<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>⏱️ Feuilles de Temps</h1>
        <p>Pointage et suivi des heures de travail</p>
    </div>
    <div>
        <a href="/timesheets/clock" class="btn btn-primary">
            <i class="icon">⏰</i> Pointage
        </a>
        <a href="/timesheets/create" class="btn btn-secondary">
            ➕ Saisie manuelle
        </a>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['total_hours'] ?? 0, 1) ?>h</div>
            <div class="stat-label">Heures totales</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['avg_hours_per_day'] ?? 0, 1) ?>h</div>
            <div class="stat-label">Moyenne/jour</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_days'] ?? 0 ?></div>
            <div class="stat-label">Jours travaillés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-value"><?= number_format($stats['total_cost'] ?? 0, 2) ?> €</div>
            <div class="stat-label">Coût total</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="filters-form">
            <div class="row">
                <div class="col-md-3">
                    <label>Employé</label>
                    <select name="user_id" class="form-control" onchange="this.form.submit()">
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= $currentUserId == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Chantier</label>
                    <select name="chantier_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Tous</option>
                        <?php foreach ($chantiers as $chantier): ?>
                            <option value="<?= $chantier['id'] ?>" <?= $currentChantierId == $chantier['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($chantier['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Du</label>
                    <input type="date" name="date_from" class="form-control" value="<?= $dateFrom ?>" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <label>Au</label>
                    <input type="date" name="date_to" class="form-control" value="<?= $dateTo ?>" onchange="this.form.submit()">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <a href="/timesheets/weekly?user_id=<?= $currentUserId ?>" class="btn btn-sm btn-secondary">Hebdo</a>
                        <a href="/timesheets/monthly?user_id=<?= $currentUserId ?>" class="btn btn-sm btn-secondary">Mensuel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des feuilles de temps -->
<div class="card">
    <div class="card-body">
        <?php if (empty($timesheets)): ?>
            <div class="empty-state">
                <p><strong>Aucune feuille de temps</strong></p>
                <p>Commencez par pointer votre arrivée ou créer une saisie manuelle.</p>
                <div class="mt-2">
                    <a href="/timesheets/clock" class="btn btn-primary">Pointage</a>
                    <a href="/timesheets/create" class="btn btn-secondary">Saisie manuelle</a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employé</th>
                            <th>Chantier</th>
                            <th>Arrivée</th>
                            <th>Départ</th>
                            <th>Pause</th>
                            <th>Total heures</th>
                            <th>GPS</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($timesheets as $timesheet): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($timesheet['date'])) ?></td>
                                <td><?= htmlspecialchars($timesheet['user_name']) ?></td>
                                <td>
                                    <?php if ($timesheet['chantier_id']): ?>
                                        <a href="/chantiers/view/<?= $timesheet['chantier_id'] ?>">
                                            <?= htmlspecialchars($timesheet['chantier_name'] ?? 'Chantier #' . $timesheet['chantier_id']) ?>
                                        </a>
                                    <?php else: ?>
                                        <em>Non assigné</em>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('H:i', strtotime($timesheet['clock_in'])) ?></td>
                                <td>
                                    <?php if ($timesheet['clock_out']): ?>
                                        <?= date('H:i', strtotime($timesheet['clock_out'])) ?>
                                    <?php else: ?>
                                        <span class="badge badge-success">En cours</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $timesheet['break_minutes'] ?? 0 ?> min</td>
                                <td>
                                    <strong><?= number_format($timesheet['total_hours'] ?? 0, 2) ?>h</strong>
                                </td>
                                <td>
                                    <?php if ($timesheet['latitude_in'] && $timesheet['longitude_in']): ?>
                                        <a href="https://maps.google.com/?q=<?= $timesheet['latitude_in'] ?>,<?= $timesheet['longitude_in'] ?>"
                                           target="_blank"
                                           title="Voir sur Google Maps">
                                            📍
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($timesheet['validated']): ?>
                                        <span class="badge badge-success">✓ Validé</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/timesheets/view/<?= $timesheet['id'] ?>" class="btn btn-sm btn-secondary">Voir</a>
                                    <?php if (!$timesheet['validated'] && $timesheet['user_id'] == $_SESSION['user']['id']): ?>
                                        <a href="/timesheets/edit/<?= $timesheet['id'] ?>" class="btn btn-sm btn-primary">Éditer</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
                            <td>
                                <strong>
                                    <?php
                                        $totalHours = array_sum(array_column($timesheets, 'total_hours'));
                                        echo number_format($totalHours, 2);
                                    ?>h
                                </strong>
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
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

.stat-card.stat-success {
    border-left: 4px solid #10b981;
}

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

.badge-success {
    background-color: #10b981;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
}

.badge-warning {
    background-color: #f59e0b;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
}

.table-info {
    background-color: #dbeafe;
    font-weight: 600;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
