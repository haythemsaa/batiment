<div class="page-header">
    <h1>
        <i class="fas fa-truck"></i>
        Gestion des fournisseurs
    </h1>
    <a href="/fournisseurs/create" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nouveau fournisseur
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($fournisseurs)): ?>
        <div class="empty-state">
            <i class="fas fa-truck fa-3x"></i>
            <h3>Aucun fournisseur</h3>
            <p>Commencez par ajouter votre premier fournisseur</p>
            <a href="/fournisseurs/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Ajouter un fournisseur
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Ville</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fournisseurs as $fournisseur): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($fournisseur['name']) ?></strong>
                            <?php if ($fournisseur['siret']): ?>
                            <br><small class="text-muted">SIRET: <?= htmlspecialchars($fournisseur['siret']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($fournisseur['contact_name']): ?>
                                <?= htmlspecialchars($fournisseur['contact_name']) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($fournisseur['email']): ?>
                            <a href="mailto:<?= htmlspecialchars($fournisseur['email']) ?>">
                                <?= htmlspecialchars($fournisseur['email']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($fournisseur['phone']): ?>
                            <a href="tel:<?= htmlspecialchars($fournisseur['phone']) ?>">
                                <?= htmlspecialchars($fournisseur['phone']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($fournisseur['city'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= $fournisseur['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                                <?= $fournisseur['status'] === 'active' ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/fournisseurs/edit/<?= $fournisseur['id'] ?>" class="btn btn-sm" title="Modifier">
                                    <i class="fas fa-edit"></i>
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
