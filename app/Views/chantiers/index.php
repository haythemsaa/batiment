<div class="page-header">
    <h1>
        <i class="fas fa-hammer"></i>
        Gestion des chantiers
    </h1>
    <a href="/chantiers/create" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nouveau chantier
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($chantiers)): ?>
        <div class="empty-state">
            <i class="fas fa-hammer fa-3x"></i>
            <h3>Aucun chantier</h3>
            <p>Commencez par créer votre premier chantier</p>
            <a href="/chantiers/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Créer un chantier
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Client</th>
                        <th>Dates</th>
                        <th>Statut</th>
                        <th>Progression</th>
                        <th>Budget estimé</th>
                        <th>Coût réel</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chantiers as $chantier): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($chantier['name']) ?></strong>
                            <?php if ($chantier['reference']): ?>
                            <br><small class="text-muted">Réf: <?= htmlspecialchars($chantier['reference']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $chantier['client_type'] === 'company'
                                ? htmlspecialchars($chantier['company_name'])
                                : htmlspecialchars($chantier['first_name'] . ' ' . $chantier['last_name']) ?>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($chantier['start_date'])) ?>
                            <br>
                            <small class="text-muted">→ <?= date('d/m/Y', strtotime($chantier['end_date'])) ?></small>
                        </td>
                        <td>
                            <?php
                            $badgeClass = match($chantier['status']) {
                                'completed' => 'badge-success',
                                'in_progress' => 'badge-info',
                                'suspended' => 'badge-warning',
                                'cancelled' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                            $statusLabel = match($chantier['status']) {
                                'planned' => 'Planifié',
                                'in_progress' => 'En cours',
                                'completed' => 'Terminé',
                                'suspended' => 'Suspendu',
                                'cancelled' => 'Annulé',
                                default => ucfirst($chantier['status'])
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $chantier['progress_percent'] ?>%">
                                    <?= number_format($chantier['progress_percent'], 0) ?>%
                                </div>
                            </div>
                        </td>
                        <td><?= number_format($chantier['estimated_budget'], 2, ',', ' ') ?> €</td>
                        <td>
                            <?php if ($chantier['actual_cost'] > 0): ?>
                            <strong><?= number_format($chantier['actual_cost'], 2, ',', ' ') ?> €</strong>
                            <?php
                            $overBudget = $chantier['actual_cost'] > $chantier['estimated_budget'];
                            if ($overBudget):
                            ?>
                            <br><span class="badge badge-danger">Dépassement</span>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/chantiers/view/<?= $chantier['id'] ?>" class="btn btn-sm" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/chantiers/edit/<?= $chantier['id'] ?>" class="btn btn-sm" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/chantiers/gantt/<?= $chantier['id'] ?>" class="btn btn-sm" title="Gantt">
                                    <i class="fas fa-chart-gantt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
