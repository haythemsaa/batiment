<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Erreur serveur - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="error-page">
        <div class="error-container">
            <div class="error-code">500</div>
            <div class="error-icon">
                <i class="fas fa-server"></i>
            </div>
            <h1>Erreur serveur</h1>
            <p>Désolé, une erreur est survenue sur le serveur. Nous travaillons à résoudre ce problème.</p>

            <div class="error-actions">
                <a href="/dashboard" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Retour au tableau de bord
                </a>
                <a href="javascript:location.reload()" class="btn btn-outline">
                    <i class="fas fa-redo"></i>
                    Réessayer
                </a>
            </div>

            <div class="error-help">
                <p>Le problème persiste ?</p>
                <p>Contactez notre support technique :</p>
                <a href="mailto:support@batisaas.com" class="btn btn-outline btn-sm">
                    <i class="fas fa-envelope"></i>
                    support@batisaas.com
                </a>
            </div>
        </div>
    </div>

    <style>
        .error-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            padding: 2rem;
        }

        .error-container {
            max-width: 600px;
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .error-code {
            font-size: 8rem;
            font-weight: 800;
            color: var(--danger-color);
            line-height: 1;
            margin-bottom: 1rem;
        }

        .error-icon {
            font-size: 4rem;
            color: var(--danger-color);
            margin-bottom: 1.5rem;
        }

        .error-container h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }

        .error-container p {
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .error-help {
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 5rem;
            }

            .error-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
