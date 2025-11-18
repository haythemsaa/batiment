<div class="page-header">
    <h1>
        <i class="fas fa-chart-bar"></i>
        Rapports et analytics
    </h1>
</div>

<!-- Filtres de période -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="/rapports" class="filter-form">
            <div class="form-row" style="align-items: end;">
                <div class="form-group">
                    <label for="start_date">Date de début</label>
                    <input type="date" id="start_date" name="start_date" value="<?= $startDate ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">Date de fin</label>
                    <input type="date" id="end_date" name="end_date" value="<?= $endDate ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                        Filtrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- KPIs -->
<div class="stats-grid">
    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-euro-sign"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['revenue'], 2, ',', ' ') ?> €</h3>
            <p>Chiffre d'affaires</p>
        </div>
    </div>

    <div class="stat-card stat-danger">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['expenses'], 2, ',', ' ') ?> €</h3>
            <p>Dépenses</p>
        </div>
    </div>

    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['profit'], 2, ',', ' ') ?> €</h3>
            <p>Bénéfice net</p>
            <div class="stat-details">
                <span class="badge <?= $stats['margin'] >= 0 ? 'badge-success' : 'badge-danger' ?>">
                    <?= number_format($stats['margin'], 1) ?>% marge
                </span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-info">
        <div class="stat-icon">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-content">
            <h3><?= $stats['quotes_sent'] > 0 ? number_format(($stats['quotes_accepted'] / $stats['quotes_sent']) * 100, 1) : 0 ?>%</h3>
            <p>Taux de conversion devis</p>
            <div class="stat-details">
                <span class="badge badge-info"><?= $stats['quotes_accepted'] ?> / <?= $stats['quotes_sent'] ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Graphiques -->
<div class="grid-2">
    <!-- Top clients -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-trophy"></i> Top 10 Clients</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($topClients)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th style="text-align: right;">CA généré</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topClients as $client): ?>
                        <tr>
                            <td>
                                <?= $client['type'] === 'company'
                                    ? htmlspecialchars($client['company_name'])
                                    : htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>
                            </td>
                            <td style="text-align: right;">
                                <strong><?= number_format($client['total_amount'], 2, ',', ' ') ?> €</strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">Aucune donnée pour cette période</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chantiers par statut -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-hammer"></i> Chantiers par statut</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($chantiersByStatus)): ?>
            <div class="status-chart">
                <?php foreach ($chantiersByStatus as $stat): ?>
                <div class="status-item">
                    <div class="status-label">
                        <?php
                        $labels = [
                            'planned' => 'Planifiés',
                            'in_progress' => 'En cours',
                            'completed' => 'Terminés',
                            'suspended' => 'Suspendus',
                            'cancelled' => 'Annulés'
                        ];
                        ?>
                        <?= $labels[$stat['status']] ?? ucfirst($stat['status']) ?>
                    </div>
                    <div class="status-count">
                        <strong><?= $stat['count'] ?></strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-muted">Aucun chantier</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Accès rapides -->
<div class="quick-actions">
    <h2>Rapports détaillés</h2>
    <div class="quick-actions-grid">
        <a href="/rapports/rentabilite" class="quick-action-btn">
            <i class="fas fa-chart-pie"></i>
            <span>Rentabilité par chantier</span>
        </a>
        <a href="/rapports/clients" class="quick-action-btn">
            <i class="fas fa-users"></i>
            <span>Analyse clients</span>
        </a>
        <a href="/rapports/tresorerie" class="quick-action-btn">
            <i class="fas fa-coins"></i>
            <span>Trésorerie</span>
        </a>
        <a href="/rapports/export" class="quick-action-btn">
            <i class="fas fa-file-excel"></i>
            <span>Export comptable</span>
        </a>
    </div>
</div>

<style>
.filter-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 1rem;
}

.status-chart {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--light-color);
    border-radius: 0.5rem;
}

.status-label {
    font-weight: 500;
}

.status-count strong {
    font-size: 1.5rem;
    color: var(--primary-color);
}
</style>
