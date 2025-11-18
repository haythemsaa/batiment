<div class="page-header">
    <h1>
        <i class="fas fa-chart-pie"></i>
        Rapport de rentabilité
    </h1>
    <a href="/rapports" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i>
        Retour
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Chantier</th>
                        <th>Client</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Budget estimé</th>
                        <th style="text-align: right;">Coût réel</th>
                        <th style="text-align: right;">Profit/Perte</th>
                        <th style="text-align: right;">Marge</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chantiers as $chantier): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($chantier['name']) ?></strong>
                            <?php if ($chantier['reference']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($chantier['reference']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $chantier['client_type'] === 'company'
                                ? htmlspecialchars($chantier['company_name'])
                                : htmlspecialchars($chantier['first_name'] . ' ' . $chantier['last_name']) ?>
                        </td>
                        <td>
                            <?php
                            $statusLabels = [
                                'planned' => 'Planifié',
                                'in_progress' => 'En cours',
                                'completed' => 'Terminé',
                                'suspended' => 'Suspendu',
                                'cancelled' => 'Annulé'
                            ];
                            $statusClasses = [
                                'planned' => 'badge-secondary',
                                'in_progress' => 'badge-info',
                                'completed' => 'badge-success',
                                'suspended' => 'badge-warning',
                                'cancelled' => 'badge-danger'
                            ];
                            ?>
                            <span class="badge <?= $statusClasses[$chantier['status']] ?? 'badge-secondary' ?>">
                                <?= $statusLabels[$chantier['status']] ?? ucfirst($chantier['status']) ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <?= number_format($chantier['estimated_budget'], 2, ',', ' ') ?> €
                        </td>
                        <td style="text-align: right;">
                            <?= number_format($chantier['actual_cost'], 2, ',', ' ') ?> €
                        </td>
                        <td style="text-align: right;">
                            <strong class="<?= $chantier['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $chantier['profit'] >= 0 ? '+' : '' ?><?= number_format($chantier['profit'], 2, ',', ' ') ?> €
                            </strong>
                        </td>
                        <td style="text-align: right;">
                            <span class="badge <?= $chantier['margin'] >= 20 ? 'badge-success' : ($chantier['margin'] >= 10 ? 'badge-warning' : 'badge-danger') ?>">
                                <?= number_format($chantier['margin'], 1) ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <?php
                    $totalRevenue = array_sum(array_column($chantiers, 'estimated_budget'));
                    $totalCost = array_sum(array_column($chantiers, 'actual_cost'));
                    $totalProfit = $totalRevenue - $totalCost;
                    $totalMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue * 100) : 0;
                    ?>
                    <tr style="background: var(--light-color); font-weight: bold;">
                        <td colspan="3">TOTAL</td>
                        <td style="text-align: right;"><?= number_format($totalRevenue, 2, ',', ' ') ?> €</td>
                        <td style="text-align: right;"><?= number_format($totalCost, 2, ',', ' ') ?> €</td>
                        <td style="text-align: right;">
                            <strong class="<?= $totalProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= $totalProfit >= 0 ? '+' : '' ?><?= number_format($totalProfit, 2, ',', ' ') ?> €
                            </strong>
                        </td>
                        <td style="text-align: right;">
                            <span class="badge <?= $totalMargin >= 20 ? 'badge-success' : ($totalMargin >= 10 ? 'badge-warning' : 'badge-danger') ?>">
                                <?= number_format($totalMargin, 1) ?>%
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Légende des marges -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-info-circle"></i> Légende des marges</h2>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div>
                <span class="badge badge-success">Excellente</span>
                <span class="text-muted">≥ 20%</span>
            </div>
            <div>
                <span class="badge badge-warning">Correcte</span>
                <span class="text-muted">10% - 19%</span>
            </div>
            <div>
                <span class="badge badge-danger">Faible</span>
                <span class="text-muted">< 10%</span>
            </div>
        </div>
    </div>
</div>
