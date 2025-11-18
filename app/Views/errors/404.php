<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page non trouvée - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="error-page">
        <div class="error-container">
            <div class="error-code">404</div>
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1>Page non trouvée</h1>
            <p>Désolé, la page que vous recherchez n'existe pas ou a été déplacée.</p>

            <div class="error-actions">
                <a href="/dashboard" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Retour au tableau de bord
                </a>
                <a href="javascript:history.back()" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Page précédente
                </a>
            </div>

            <div class="error-help">
                <p>Besoin d'aide ?</p>
                <ul>
                    <li><a href="/dashboard">Tableau de bord</a></li>
                    <li><a href="/devis">Devis</a></li>
                    <li><a href="/factures">Factures</a></li>
                    <li><a href="/chantiers">Chantiers</a></li>
                    <li><a href="/clients">Clients</a></li>
                </ul>
            </div>
        </div>
    </div>

    <style>
        .error-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
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
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 1rem;
        }

        .error-icon {
            font-size: 4rem;
            color: var(--warning-color);
            margin-bottom: 1.5rem;
        }

        .error-container h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }

        .error-container p {
            color: var(--secondary-color);
            margin-bottom: 2rem;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .error-help {
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .error-help p {
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .error-help ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .error-help a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .error-help a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 5rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .error-help ul {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</body>
</html>
