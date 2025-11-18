<div class="page-header">
    <h1>
        <i class="fas fa-users"></i>
        Gestion des clients
    </h1>
    <a href="/clients/create" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nouveau client
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($clients)): ?>
        <div class="empty-state">
            <i class="fas fa-users fa-3x"></i>
            <h3>Aucun client</h3>
            <p>Commencez par ajouter votre premier client</p>
            <a href="/clients/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Ajouter un client
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Ville</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                    <tr>
                        <td>
                            <span class="badge <?= $client['type'] === 'company' ? 'badge-info' : 'badge-secondary' ?>">
                                <?= $client['type'] === 'company' ? 'Entreprise' : 'Particulier' ?>
                            </span>
                        </td>
                        <td>
                            <strong>
                                <?= $client['type'] === 'company'
                                    ? htmlspecialchars($client['company_name'])
                                    : htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>
                            </strong>
                            <?php if ($client['type'] === 'company' && $client['siret']): ?>
                            <br><small class="text-muted">SIRET: <?= htmlspecialchars($client['siret']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($client['email']): ?>
                            <a href="mailto:<?= htmlspecialchars($client['email']) ?>">
                                <?= htmlspecialchars($client['email']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($client['phone']): ?>
                            <a href="tel:<?= htmlspecialchars($client['phone']) ?>">
                                <?= htmlspecialchars($client['phone']) ?>
                            </a>
                            <?php elseif ($client['mobile']): ?>
                            <a href="tel:<?= htmlspecialchars($client['mobile']) ?>">
                                <?= htmlspecialchars($client['mobile']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($client['city'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= $client['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                                <?= $client['status'] === 'active' ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/clients/edit/<?= $client['id'] ?>" class="btn btn-sm" title="Modifier">
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

<style>
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state i {
    color: var(--secondary-color);
    margin-bottom: 1rem;
}

.empty-state h3 {
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--secondary-color);
    margin-bottom: 2rem;
}
