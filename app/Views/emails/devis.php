<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre devis <?= htmlspecialchars($devis['number']) ?></title>
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
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
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
            color: #2563eb;
            margin-top: 0;
        }
        .devis-info {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .devis-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .devis-info td {
            padding: 8px 0;
        }
        .devis-info .label {
            font-weight: bold;
            color: #64748b;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #2563eb;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            color: #64748b;
            font-size: 12px;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1><?= htmlspecialchars($company['name']) ?></h1>
            <p>Devis n° <?= htmlspecialchars($devis['number']) ?></p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Bonjour <?= htmlspecialchars($client_name) ?>,</h2>

            <p>Nous vous remercions de votre confiance et avons le plaisir de vous adresser notre devis.</p>

            <?php if ($devis['title']): ?>
            <p><strong>Objet :</strong> <?= htmlspecialchars($devis['title']) ?></p>
            <?php endif; ?>

            <!-- Devis Info -->
            <div class="devis-info">
                <table>
                    <tr>
                        <td class="label">Numéro :</td>
                        <td><?= htmlspecialchars($devis['number']) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Date :</td>
                        <td><?= date('d/m/Y', strtotime($devis['date'])) ?></td>
                    </tr>
                    <?php if ($devis['validity_date']): ?>
                    <tr>
                        <td class="label">Valable jusqu'au :</td>
                        <td><?= date('d/m/Y', strtotime($devis['validity_date'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Montant total TTC :</td>
                        <td><strong style="color: #2563eb; font-size: 18px;"><?= number_format($devis['total'], 2, ',', ' ') ?> €</strong></td>
                    </tr>
                </table>
            </div>

            <p>Vous trouverez en pièce jointe le détail de notre proposition.</p>

            <center>
                <a href="<?= APP_URL ?>/devis/view/<?= $devis['id'] ?>" class="button">
                    Consulter le devis en ligne
                </a>
            </center>

            <p>Pour toute question ou demande d'information complémentaire, n'hésitez pas à nous contacter.</p>

            <p>Nous restons à votre disposition.</p>

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
