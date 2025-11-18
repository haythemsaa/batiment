<div class="page-header">
    <h1>
        <i class="fas fa-file-invoice"></i>
        Devis <?= htmlspecialchars($devis['number']) ?>
    </h1>
    <div class="btn-group">
        <a href="/devis/edit/<?= $devis['id'] ?>" class="btn btn-outline">
            <i class="fas fa-edit"></i>
            Modifier
        </a>
        <a href="/devis/pdf/<?= $devis['id'] ?>" class="btn btn-primary" target="_blank">
            <i class="fas fa-file-pdf"></i>
            Générer PDF
        </a>
        <?php if ($devis['status'] === 'accepted' && !$devis['converted_to_invoice']): ?>
        <form method="POST" action="/devis/convert/<?= $devis['id'] ?>" style="display:inline;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-exchange-alt"></i>
                Convertir en facture
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="grid-2">
    <!-- Informations du devis -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-info-circle"></i> Informations</h2>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Numéro</label>
                    <strong><?= htmlspecialchars($devis['number']) ?></strong>
                </div>
                <div class="info-item">
                    <label>Date</label>
                    <span><?= date('d/m/Y', strtotime($devis['date'])) ?></span>
                </div>
                <div class="info-item">
                    <label>Validité</label>
                    <span>
                        <?= $devis['validity_date'] ? date('d/m/Y', strtotime($devis['validity_date'])) : '-' ?>
                    </span>
                </div>
                <div class="info-item">
                    <label>Statut</label>
                    <?php
                    $badgeClass = match($devis['status']) {
                        'accepted' => 'badge-success',
                        'sent' => 'badge-info',
                        'rejected' => 'badge-danger',
                        default => 'badge-secondary'
                    };
                    $statusLabel = match($devis['status']) {
                        'draft' => 'Brouillon',
                        'sent' => 'Envoyé',
                        'accepted' => 'Accepté',
                        'rejected' => 'Refusé',
                        default => ucfirst($devis['status'])
                    };
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                </div>
                <?php if ($devis['title']): ?>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <label>Titre</label>
                    <strong><?= htmlspecialchars($devis['title']) ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($devis['description']): ?>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <label>Description</label>
                    <p><?= nl2br(htmlspecialchars($devis['description'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Informations client -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user"></i> Client</h2>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item" style="grid-column: 1 / -1;">
                    <label>Nom</label>
                    <strong>
                        <?= $devis['client']['type'] === 'company'
                            ? htmlspecialchars($devis['client']['company_name'])
                            : htmlspecialchars($devis['client']['first_name'] . ' ' . $devis['client']['last_name']) ?>
                    </strong>
                </div>
                <?php if ($devis['client']['address']): ?>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <label>Adresse</label>
                    <p>
                        <?= htmlspecialchars($devis['client']['address']) ?><br>
                        <?= htmlspecialchars($devis['client']['postal_code']) ?> <?= htmlspecialchars($devis['client']['city']) ?>
                    </p>
                </div>
                <?php endif; ?>
                <?php if ($devis['client']['email']): ?>
                <div class="info-item">
                    <label>Email</label>
                    <a href="mailto:<?= htmlspecialchars($devis['client']['email']) ?>">
                        <?= htmlspecialchars($devis['client']['email']) ?>
                    </a>
                </div>
                <?php endif; ?>
                <?php if ($devis['client']['phone']): ?>
                <div class="info-item">
                    <label>Téléphone</label>
                    <a href="tel:<?= htmlspecialchars($devis['client']['phone']) ?>">
                        <?= htmlspecialchars($devis['client']['phone']) ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Lignes du devis -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> Détail</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Quantité</th>
                        <th>Unité</th>
                        <th style="text-align: right;">Prix unitaire HT</th>
                        <th style="text-align: right;">Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devis['items'] as $item): ?>
                    <tr>
                        <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
                        <td style="text-align: right;"><?= number_format($item['quantity'], 2, ',', ' ') ?></td>
                        <td><?= htmlspecialchars($item['unit']) ?></td>
                        <td style="text-align: right;"><?= number_format($item['unit_price'], 2, ',', ' ') ?> €</td>
                        <td style="text-align: right;"><strong><?= number_format($item['total'], 2, ',', ' ') ?> €</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>Sous-total HT</strong></td>
                        <td style="text-align: right;"><strong><?= number_format($devis['subtotal'], 2, ',', ' ') ?> €</strong></td>
                    </tr>
                    <?php if ($devis['discount_amount'] > 0): ?>
                    <tr>
                        <td colspan="4" style="text-align: right;">
                            Remise (<?= number_format($devis['discount_percent'], 2) ?>%)
                        </td>
                        <td style="text-align: right;">- <?= number_format($devis['discount_amount'], 2, ',', ' ') ?> €</td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>TVA (20%)</strong></td>
                        <td style="text-align: right;"><strong><?= number_format($devis['tva_amount'], 2, ',', ' ') ?> €</strong></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right;"><strong>TOTAL TTC</strong></td>
                        <td style="text-align: right;"><strong class="text-primary"><?= number_format($devis['total'], 2, ',', ' ') ?> €</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php if ($devis['notes'] || $devis['terms']): ?>
<div class="grid-2">
    <?php if ($devis['notes']): ?>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-sticky-note"></i> Notes internes</h2>
        </div>
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($devis['notes'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($devis['terms']): ?>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-file-contract"></i> Conditions générales</h2>
        </div>
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($devis['terms'])) ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.info-item label {
    display: block;
    font-size: 0.875rem;
    color: var(--secondary-color);
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    font-weight: 600;
}

.info-item strong {
    font-size: 1.1rem;
}

.total-row {
    background: var(--light-color);
    font-size: 1.25rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}
