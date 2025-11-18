<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis <?= htmlspecialchars($devis['number']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
        }

        .company-info {
            width: 50%;
        }

        .company-info h1 {
            color: #2563eb;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .document-info {
            width: 40%;
            text-align: right;
        }

        .document-title {
            background: #2563eb;
            color: white;
            padding: 10px 15px;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .parties {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .party {
            width: 48%;
            background: #f8fafc;
            padding: 15px;
            border-left: 3px solid #2563eb;
        }

        .party h3 {
            color: #2563eb;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead {
            background: #2563eb;
            color: white;
        }

        .items-table th,
        .items-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }

        .items-table th {
            font-weight: bold;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .items-table tfoot {
            background: #f1f5f9;
            font-weight: bold;
        }

        .items-table tfoot .total-row {
            background: #2563eb;
            color: white;
            font-size: 14px;
        }

        .text-right {
            text-align: right;
        }

        .terms {
            margin-top: 30px;
            padding: 15px;
            background: #f8fafc;
            border-left: 3px solid #64748b;
        }

        .terms h3 {
            color: #64748b;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }

        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            border-top: 2px solid #e2e8f0;
            padding-top: 10px;
        }

        .signature-box p {
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="company-info">
            <h1><?= htmlspecialchars($company['name']) ?></h1>
            <p><strong>SIRET:</strong> <?= htmlspecialchars($company['siret'] ?? '') ?></p>
            <p><?= htmlspecialchars($company['address']) ?></p>
            <p><?= htmlspecialchars($company['postal_code']) ?> <?= htmlspecialchars($company['city']) ?></p>
            <p><strong>Tél:</strong> <?= htmlspecialchars($company['phone']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($company['email']) ?></p>
        </div>
        <div class="document-info">
            <div class="document-title">DEVIS</div>
            <p><strong>N°:</strong> <?= htmlspecialchars($devis['number']) ?></p>
            <p><strong>Date:</strong> <?= date('d/m/Y', strtotime($devis['date'])) ?></p>
            <?php if ($devis['validity_date']): ?>
            <p><strong>Valable jusqu'au:</strong> <?= date('d/m/Y', strtotime($devis['validity_date'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Parties -->
    <div class="parties">
        <div class="party">
            <h3>Émetteur</h3>
            <p><strong><?= htmlspecialchars($company['name']) ?></strong></p>
            <p><?= htmlspecialchars($company['address']) ?></p>
            <p><?= htmlspecialchars($company['postal_code']) ?> <?= htmlspecialchars($company['city']) ?></p>
        </div>
        <div class="party">
            <h3>Client</h3>
            <p><strong>
                <?= $devis['client']['type'] === 'company'
                    ? htmlspecialchars($devis['client']['company_name'])
                    : htmlspecialchars($devis['client']['first_name'] . ' ' . $devis['client']['last_name']) ?>
            </strong></p>
            <?php if ($devis['client']['address']): ?>
            <p><?= htmlspecialchars($devis['client']['address']) ?></p>
            <p><?= htmlspecialchars($devis['client']['postal_code']) ?> <?= htmlspecialchars($devis['client']['city']) ?></p>
            <?php endif; ?>
            <?php if ($devis['client']['email']): ?>
            <p>Email: <?= htmlspecialchars($devis['client']['email']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Titre et description -->
    <?php if ($devis['title'] || $devis['description']): ?>
    <div style="margin-bottom: 20px;">
        <?php if ($devis['title']): ?>
        <h2 style="color: #2563eb; margin-bottom: 10px;"><?= htmlspecialchars($devis['title']) ?></h2>
        <?php endif; ?>
        <?php if ($devis['description']): ?>
        <p><?= nl2br(htmlspecialchars($devis['description'])) ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Tableau des prestations -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th style="width: 10%; text-align: right;">Qté</th>
                <th style="width: 10%;">Unité</th>
                <th style="width: 15%; text-align: right;">Prix unit. HT</th>
                <th style="width: 15%; text-align: right;">Total HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($devis['items'] as $item): ?>
            <tr>
                <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
                <td class="text-right"><?= number_format($item['quantity'], 2, ',', ' ') ?></td>
                <td><?= htmlspecialchars($item['unit']) ?></td>
                <td class="text-right"><?= number_format($item['unit_price'], 2, ',', ' ') ?> €</td>
                <td class="text-right"><?= number_format($item['total'], 2, ',', ' ') ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Sous-total HT</strong></td>
                <td class="text-right"><strong><?= number_format($devis['subtotal'], 2, ',', ' ') ?> €</strong></td>
            </tr>
            <?php if ($devis['discount_amount'] > 0): ?>
            <tr>
                <td colspan="4" class="text-right">Remise (<?= number_format($devis['discount_percent'], 2) ?>%)</td>
                <td class="text-right">- <?= number_format($devis['discount_amount'], 2, ',', ' ') ?> €</td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="4" class="text-right"><strong>TVA (20%)</strong></td>
                <td class="text-right"><strong><?= number_format($devis['tva_amount'], 2, ',', ' ') ?> €</strong></td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>TOTAL TTC</strong></td>
                <td class="text-right"><strong><?= number_format($devis['total'], 2, ',', ' ') ?> €</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Conditions -->
    <?php if ($devis['terms']): ?>
    <div class="terms">
        <h3>Conditions générales</h3>
        <p><?= nl2br(htmlspecialchars($devis['terms'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <p><strong>Signature de l'entreprise</strong></p>
            <br><br><br>
        </div>
        <div class="signature-box">
            <p><strong>Bon pour accord - Signature du client</strong></p>
            <p style="font-size: 10px; margin-top: 5px;">
                Date : ___/___/______<br>
                Signature précédée de la mention "Bon pour accord"
            </p>
            <br><br>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p><?= htmlspecialchars($company['name']) ?> - SIRET: <?= htmlspecialchars($company['siret'] ?? '') ?></p>
        <?php if ($company['capital']): ?>
        <p>Capital social : <?= number_format($company['capital'], 2, ',', ' ') ?> €</p>
        <?php endif; ?>
        <?php if ($company['tva_number']): ?>
        <p>N° TVA intracommunautaire : <?= htmlspecialchars($company['tva_number']) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
