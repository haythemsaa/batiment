<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>📦 Gestion des Stocks</h1>
        <p>Suivi des matériaux et inventaire en temps réel</p>
    </div>
    <div>
        <a href="/stocks/create" class="btn btn-primary">
            <i class="icon">➕</i> Nouvel Article
        </a>
        <a href="/stocks/inventory" class="btn btn-secondary">
            📋 Inventaire
        </a>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_items'] ?></div>
            <div class="stat-label">Articles au stock</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-warning">
            <div class="stat-value"><?= $stats['low_stock_count'] ?></div>
            <div class="stat-label">Alertes stock faible</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-success">
            <div class="stat-value"><?= number_format($stats['total_value'], 2) ?> €</div>
            <div class="stat-label">Valeur totale</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="filters-form">
            <div class="row">
                <div class="col-md-4">
                    <label>Catégorie</label>
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="">Toutes</option>
                        <option value="materiau" <?= $currentCategory === 'materiau' ? 'selected' : '' ?>>Matériaux</option>
                        <option value="outillage" <?= $currentCategory === 'outillage' ? 'selected' : '' ?>>Outillage</option>
                        <option value="equipement" <?= $currentCategory === 'equipement' ? 'selected' : '' ?>>Équipement</option>
                        <option value="consommable" <?= $currentCategory === 'consommable' ? 'selected' : '' ?>>Consommables</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Stock faible uniquement</label>
                    <select name="low_stock" class="form-control" onchange="this.form.submit()">
                        <option value="0" <?= !$showLowStock ? 'selected' : '' ?>>Non</option>
                        <option value="1" <?= $showLowStock ? 'selected' : '' ?>>Oui</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="/stocks/valuation" class="btn btn-secondary w-100">
                        💰 Valorisation du stock
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Alertes stock faible -->
<?php if (!empty($alerts)): ?>
    <div class="alert alert-warning">
        <h4>⚠️ Alertes Stock Faible (<?= count($alerts) ?>)</h4>
        <ul class="mb-0">
            <?php foreach ($alerts as $alert): ?>
                <li>
                    <strong><?= htmlspecialchars($alert['name']) ?></strong>:
                    <?= $alert['quantity'] ?> <?= $alert['unit'] ?>
                    (min: <?= $alert['min_quantity'] ?>)
                    - <a href="/stocks/view/<?= $alert['id'] ?>">Réapprovisionner</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Liste des stocks -->
<div class="card">
    <div class="card-body">
        <?php if (empty($stocks)): ?>
            <div class="empty-state">
                <p><strong>Aucun article en stock</strong></p>
                <p>Commencez par ajouter vos premiers articles.</p>
                <a href="/stocks/create" class="btn btn-primary mt-2">Créer un article</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Référence</th>
                            <th>Catégorie</th>
                            <th>Quantité</th>
                            <th>Min</th>
                            <th>Emplacement</th>
                            <th>Prix unitaire</th>
                            <th>Valeur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $stock): ?>
                            <?php
                                $isLowStock = $stock['quantity'] <= $stock['min_quantity'];
                                $value = $stock['quantity'] * ($stock['unit_price'] ?? 0);
                            ?>
                            <tr class="<?= $isLowStock ? 'table-warning' : '' ?>">
                                <td>
                                    <strong><?= htmlspecialchars($stock['name']) ?></strong>
                                    <?php if ($isLowStock): ?>
                                        <span class="badge badge-warning">Stock faible</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($stock['reference'] ?? '-') ?></td>
                                <td>
                                    <?php
                                        $categoryLabels = [
                                            'materiau' => '🧱 Matériau',
                                            'outillage' => '🔧 Outillage',
                                            'equipement' => '⚙️ Équipement',
                                            'consommable' => '📦 Consommable'
                                        ];
                                        echo $categoryLabels[$stock['category']] ?? $stock['category'];
                                    ?>
                                </td>
                                <td>
                                    <strong><?= $stock['quantity'] ?></strong> <?= $stock['unit'] ?>
                                </td>
                                <td><?= $stock['min_quantity'] ?> <?= $stock['unit'] ?></td>
                                <td><?= htmlspecialchars($stock['location'] ?? '-') ?></td>
                                <td><?= $stock['unit_price'] ? number_format($stock['unit_price'], 2) . ' €' : '-' ?></td>
                                <td><strong><?= number_format($value, 2) ?> €</strong></td>
                                <td>
                                    <a href="/stocks/view/<?= $stock['id'] ?>" class="btn btn-sm btn-secondary">Voir</a>
                                    <a href="/stocks/movement/<?= $stock['id'] ?>" class="btn btn-sm btn-primary">Mouvement</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
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

.stat-card.stat-warning {
    border-left: 4px solid #f59e0b;
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

.table-warning {
    background-color: #fef3c7 !important;
}

.badge-warning {
    background-color: #f59e0b;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
