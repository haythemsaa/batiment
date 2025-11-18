<div class="dashboard">
    <div class="dashboard-header">
        <h1>
            <i class="fas fa-home"></i>
            Tableau de bord
        </h1>
        <p>Bienvenue <?= $_SESSION['user_name'] ?></p>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-content">
                <h3><?= number_format($stats['devis']['total']) ?></h3>
                <p>Devis</p>
                <div class="stat-details">
                    <span class="badge badge-success"><?= $stats['devis']['accepted'] ?> acceptés</span>
                    <span class="badge badge-info"><?= $stats['devis']['sent'] ?> envoyés</span>
                </div>
            </div>
            <div class="stat-amount">
                <?= number_format($stats['devis']['amount'], 2) ?> €
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-content">
                <h3><?= number_format($stats['factures']['total']) ?></h3>
                <p>Factures</p>
                <div class="stat-details">
                    <span class="badge badge-success"><?= $stats['factures']['paid'] ?> payées</span>
                    <?php if ($stats['factures']['overdue'] > 0): ?>
                    <span class="badge badge-danger"><?= $stats['factures']['overdue'] ?> en retard</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-amount">
                <?= number_format($stats['factures']['amount'], 2) ?> €
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-hammer"></i>
            </div>
            <div class="stat-content">
                <h3><?= number_format($stats['chantiers']['total']) ?></h3>
                <p>Chantiers</p>
                <div class="stat-details">
                    <span class="badge badge-info"><?= $stats['chantiers']['in_progress'] ?> en cours</span>
                    <span class="badge badge-success"><?= $stats['chantiers']['completed'] ?> terminés</span>
                </div>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?= number_format($stats['clients']['total']) ?></h3>
                <p>Clients</p>
            </div>
        </div>
    </div>

    <!-- Revenus et créances -->
    <div class="grid-2">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-euro-sign"></i> Revenus</h2>
            </div>
            <div class="card-body">
                <div class="revenue-stats">
                    <div class="revenue-item">
                        <span>Total facturé</span>
                        <strong><?= number_format($stats['factures']['amount'], 2) ?> €</strong>
                    </div>
                    <div class="revenue-item">
                        <span>Reste à encaisser</span>
                        <strong class="text-warning"><?= number_format($stats['factures']['unpaid_amount'], 2) ?> €</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-chart-pie"></i> Activité</h2>
            </div>
            <div class="card-body">
                <div class="activity-summary">
                    <p><strong><?= $stats['chantiers']['in_progress'] ?></strong> chantiers en cours</p>
                    <p><strong><?= $stats['devis']['sent'] ?></strong> devis en attente de validation</p>
                    <p><strong><?= $stats['factures']['overdue'] ?></strong> factures en retard</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chantiers en cours -->
    <?php if (!empty($chantiersEnCours)): ?>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-hammer"></i> Chantiers en cours</h2>
            <a href="/chantiers" class="btn btn-sm">Voir tous</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Client</th>
                            <th>Dates</th>
                            <th>Progression</th>
                            <th>Budget</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chantiersEnCours as $chantier): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($chantier['name']) ?></strong>
                                <?php if ($chantier['reference']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($chantier['reference']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                echo $chantier['client_type'] === 'company'
                                    ? htmlspecialchars($chantier['company_name'])
                                    : htmlspecialchars($chantier['first_name'] . ' ' . $chantier['last_name']);
                                ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($chantier['start_date'])) ?>
                                <br>
                                <small class="text-muted">→ <?= date('d/m/Y', strtotime($chantier['end_date'])) ?></small>
                            </td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?= $chantier['progress_percent'] ?>%">
                                        <?= number_format($chantier['progress_percent'], 0) ?>%
                                    </div>
                                </div>
                            </td>
                            <td><?= number_format($chantier['estimated_budget'], 2) ?> €</td>
                            <td>
                                <a href="/chantiers/view/<?= $chantier['id'] ?>" class="btn btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Activités récentes -->
    <div class="grid-2">
        <!-- Devis récents -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-file-invoice"></i> Devis récents</h2>
                <a href="/devis" class="btn btn-sm">Voir tous</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentDevis)): ?>
                <div class="activity-list">
                    <?php foreach ($recentDevis as $devis): ?>
                    <div class="activity-item">
                        <div class="activity-info">
                            <strong><?= htmlspecialchars($devis['number']) ?></strong>
                            <p>
                                <?php
                                echo $devis['client_type'] === 'company'
                                    ? htmlspecialchars($devis['company_name'])
                                    : htmlspecialchars($devis['first_name'] . ' ' . $devis['last_name']);
                                ?>
                            </p>
                            <small><?= date('d/m/Y', strtotime($devis['date'])) ?></small>
                        </div>
                        <div class="activity-meta">
                            <span class="badge badge-<?= $devis['status'] === 'accepted' ? 'success' : ($devis['status'] === 'sent' ? 'info' : 'secondary') ?>">
                                <?= ucfirst($devis['status']) ?>
                            </span>
                            <strong><?= number_format($devis['total'], 2) ?> €</strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">Aucun devis pour le moment</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Factures récentes -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-receipt"></i> Factures récentes</h2>
                <a href="/factures" class="btn btn-sm">Voir toutes</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recentFactures)): ?>
                <div class="activity-list">
                    <?php foreach ($recentFactures as $facture): ?>
                    <div class="activity-item">
                        <div class="activity-info">
                            <strong><?= htmlspecialchars($facture['number']) ?></strong>
                            <p>
                                <?php
                                echo $facture['client_type'] === 'company'
                                    ? htmlspecialchars($facture['company_name'])
                                    : htmlspecialchars($facture['first_name'] . ' ' . $facture['last_name']);
                                ?>
                            </p>
                            <small><?= date('d/m/Y', strtotime($facture['date'])) ?></small>
                        </div>
                        <div class="activity-meta">
                            <span class="badge badge-<?= $facture['status'] === 'paid' ? 'success' : ($facture['status'] === 'sent' ? 'info' : 'warning') ?>">
                                <?= ucfirst($facture['status']) ?>
                            </span>
                            <strong><?= number_format($facture['total'], 2) ?> €</strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">Aucune facture pour le moment</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="quick-actions">
        <h2>Actions rapides</h2>
        <div class="quick-actions-grid">
            <a href="/devis/create" class="quick-action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Nouveau devis</span>
            </a>
            <a href="/factures/create" class="quick-action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Nouvelle facture</span>
            </a>
            <a href="/chantiers/create" class="quick-action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Nouveau chantier</span>
            </a>
            <a href="/clients/create" class="quick-action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Nouveau client</span>
            </a>
        </div>
    </div>
</div>
