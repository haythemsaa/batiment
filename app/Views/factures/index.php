<div class="page-header">
    <h1>
        <i class="fas fa-receipt"></i>
        Gestion des factures
    </h1>
    <a href="/factures/create" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nouvelle facture
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($factures)): ?>
        <div class="empty-state">
            <i class="fas fa-receipt fa-3x"></i>
            <h3>Aucune facture</h3>
            <p>Commencez par créer votre première facture</p>
            <a href="/factures/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Créer une facture
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Échéance</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Montant TTC</th>
                        <th>Reste à payer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factures as $facture): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($facture['number']) ?></strong>
                            <?php if ($facture['title']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($facture['title']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $facture['client_type'] === 'company'
                                ? htmlspecialchars($facture['company_name'])
                                : htmlspecialchars($facture['first_name'] . ' ' . $facture['last_name']) ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($facture['date'])) ?></td>
                        <td>
                            <?php if ($facture['due_date']): ?>
                                <?= date('d/m/Y', strtotime($facture['due_date'])) ?>
                                <?php
                                $isOverdue = strtotime($facture['due_date']) < time() && $facture['status'] !== 'paid';
                                if ($isOverdue):
                                ?>
                                <br><span class="badge badge-danger">En retard</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $typeLabel = match($facture['type']) {
                                'facture' => 'Facture',
                                'acompte' => 'Acompte',
                                'avoir' => 'Avoir',
                                default => ucfirst($facture['type'])
                            };
                            ?>
                            <span class="badge badge-secondary"><?= $typeLabel ?></span>
                        </td>
                        <td>
                            <?php
                            $badgeClass = match($facture['status']) {
                                'paid' => 'badge-success',
                                'sent' => 'badge-info',
                                'overdue' => 'badge-danger',
                                'partially_paid' => 'badge-warning',
                                default => 'badge-secondary'
                            };
                            $statusLabel = match($facture['status']) {
                                'draft' => 'Brouillon',
                                'sent' => 'Envoyée',
                                'paid' => 'Payée',
                                'partially_paid' => 'Partiellement payée',
                                'overdue' => 'En retard',
                                'cancelled' => 'Annulée',
                                default => ucfirst($facture['status'])
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                        </td>
                        <td><strong><?= number_format($facture['total'], 2, ',', ' ') ?> €</strong></td>
                        <td>
                            <?php if ($facture['remaining_amount'] > 0): ?>
                            <strong class="text-danger"><?= number_format($facture['remaining_amount'], 2, ',', ' ') ?> €</strong>
                            <?php else: ?>
                            <span class="text-success">Soldée</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/factures/view/<?= $facture['id'] ?>" class="btn btn-sm" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/factures/edit/<?= $facture['id'] ?>" class="btn btn-sm" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/factures/pdf/<?= $facture['id'] ?>" class="btn btn-sm" title="PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
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
