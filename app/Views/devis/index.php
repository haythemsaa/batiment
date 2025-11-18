<div class="page-header">
    <h1>
        <i class="fas fa-file-invoice"></i>
        Gestion des devis
    </h1>
    <a href="/devis/create" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nouveau devis
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($devis)): ?>
        <div class="empty-state">
            <i class="fas fa-file-invoice fa-3x"></i>
            <h3>Aucun devis</h3>
            <p>Commencez par créer votre premier devis</p>
            <a href="/devis/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Créer un devis
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
                        <th>Validité</th>
                        <th>Statut</th>
                        <th>Montant TTC</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devis as $item): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($item['number']) ?></strong>
                            <?php if ($item['title']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($item['title']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            echo $item['client_type'] === 'company'
                                ? htmlspecialchars($item['company_name'])
                                : htmlspecialchars($item['first_name'] . ' ' . $item['last_name']);
                            ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($item['date'])) ?></td>
                        <td>
                            <?php if ($item['validity_date']): ?>
                                <?= date('d/m/Y', strtotime($item['validity_date'])) ?>
                                <?php
                                $isExpired = strtotime($item['validity_date']) < time();
                                if ($isExpired && $item['status'] !== 'accepted'):
                                ?>
                                <br><span class="badge badge-danger">Expiré</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $badgeClass = match($item['status']) {
                                'accepted' => 'badge-success',
                                'sent' => 'badge-info',
                                'rejected' => 'badge-danger',
                                'expired' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                            $statusLabel = match($item['status']) {
                                'draft' => 'Brouillon',
                                'sent' => 'Envoyé',
                                'accepted' => 'Accepté',
                                'rejected' => 'Refusé',
                                'expired' => 'Expiré',
                                default => ucfirst($item['status'])
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                        </td>
                        <td><strong><?= number_format($item['total'], 2, ',', ' ') ?> €</strong></td>
                        <td>
                            <div class="btn-group">
                                <a href="/devis/view/<?= $item['id'] ?>" class="btn btn-sm" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/devis/edit/<?= $item['id'] ?>" class="btn btn-sm" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/devis/pdf/<?= $item['id'] ?>" class="btn btn-sm" title="PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <?php if ($item['status'] === 'accepted' && !$item['converted_to_invoice']): ?>
                                <form method="POST" action="/devis/convert/<?= $item['id'] ?>" style="display:inline;">
                                    <button type="submit" class="btn btn-sm btn-success" title="Convertir en facture">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
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
