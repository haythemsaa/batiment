<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre facture <?= htmlspecialchars($facture['number']) ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #10b981;
            margin-top: 0;
        }
        .facture-info {
            background: #f0fdf4;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
        }
        .facture-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .facture-info td {
            padding: 8px 0;
        }
        .facture-info .label {
            font-weight: bold;
            color: #064e3b;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #10b981;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #059669;
        }
        .alert {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            color: #64748b;
            font-size: 12px;
        }
        .footer a {
            color: #10b981;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1><?= htmlspecialchars($company['name']) ?></h1>
            <p>Facture n° <?= htmlspecialchars($facture['number']) ?></p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Bonjour <?= htmlspecialchars($client_name) ?>,</h2>

            <p>Nous vous remercions pour votre confiance. Veuillez trouver ci-joint votre facture.</p>

            <!-- Facture Info -->
            <div class="facture-info">
                <table>
                    <tr>
                        <td class="label">Numéro :</td>
                        <td><?= htmlspecialchars($facture['number']) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Date :</td>
                        <td><?= date('d/m/Y', strtotime($facture['date'])) ?></td>
                    </tr>
                    <?php if ($facture['due_date']): ?>
                    <tr>
                        <td class="label">Échéance :</td>
                        <td><?= date('d/m/Y', strtotime($facture['due_date'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Montant total TTC :</td>
                        <td><strong style="color: #10b981; font-size: 20px;"><?= number_format($facture['total'], 2, ',', ' ') ?> €</strong></td>
                    </tr>
                    <?php if ($facture['remaining_amount'] > 0): ?>
                    <tr>
                        <td class="label">Reste à payer :</td>
                        <td><strong style="color: #f59e0b; font-size: 18px;"><?= number_format($facture['remaining_amount'], 2, ',', ' ') ?> €</strong></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <?php if ($facture['remaining_amount'] > 0): ?>
            <div class="alert">
                <strong>⚠️ Paiement</strong><br>
                Merci de bien vouloir effectuer le règlement avant le <?= date('d/m/Y', strtotime($facture['due_date'])) ?>.
            </div>

            <p><strong>Modalités de paiement :</strong></p>
            <ul>
                <li>Virement bancaire</li>
                <li>Chèque à l'ordre de <?= htmlspecialchars($company['name']) ?></li>
                <li>Espèces</li>
            </ul>
            <?php else: ?>
            <div style="background-color: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong>✓ Facture réglée</strong><br>
                Nous vous remercions pour votre paiement.
            </div>
            <?php endif; ?>

            <center>
                <a href="<?= APP_URL ?>/factures/view/<?= $facture['id'] ?>" class="button">
                    Consulter la facture en ligne
                </a>
            </center>

            <p>Pour toute question, n'hésitez pas à nous contacter.</p>

            <p>Cordialement,<br>
            <strong><?= htmlspecialchars($company['name']) ?></strong></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <?= htmlspecialchars($company['name']) ?><br>
                <?= htmlspecialchars($company['address']) ?><br>
                <?= htmlspecialchars($company['postal_code']) ?> <?= htmlspecialchars($company['city']) ?><br>
                Tél: <?= htmlspecialchars($company['phone']) ?> |
                Email: <a href="mailto:<?= htmlspecialchars($company['email']) ?>"><?= htmlspecialchars($company['email']) ?></a>
            </p>
            <?php if ($company['siret']): ?>
            <p>SIRET: <?= htmlspecialchars($company['siret']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
