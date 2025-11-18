<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Gestion d'entreprise du bâtiment</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="landing-page">
    <header class="hero">
        <nav class="landing-nav">
            <div class="logo">
                <i class="fas fa-hard-hat"></i>
                <span><?= APP_NAME ?></span>
            </div>
            <div class="nav-actions">
                <a href="/login" class="btn btn-outline">Connexion</a>
                <a href="/register" class="btn btn-primary">Essai gratuit</a>
            </div>
        </nav>

        <div class="hero-content">
            <h1>La plateforme complète pour gérer votre entreprise du bâtiment</h1>
            <p class="hero-subtitle">Devis, factures, chantiers, clients... Tout ce dont vous avez besoin en un seul endroit</p>
            <div class="hero-cta">
                <a href="/register" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket"></i>
                    Commencer gratuitement
                </a>
                <p class="cta-note">Essai gratuit de 30 jours - Aucune carte bancaire requise</p>
            </div>
        </div>
    </header>

    <section class="features">
        <div class="container">
            <h2>Fonctionnalités principales</h2>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3>Devis & Factures</h3>
                    <p>Créez des devis professionnels et convertissez-les en factures en un clic. Signature électronique incluse.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-hammer"></i>
                    </div>
                    <h3>Gestion de chantiers</h3>
                    <p>Planifiez et suivez vos chantiers avec diagramme de Gantt. Gérez les interventions et le suivi terrain.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Analyse de rentabilité</h3>
                    <p>Suivez vos revenus, dépenses et marges en temps réel. Pilotez votre activité avec des tableaux de bord.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Gestion clients</h3>
                    <p>Centralisez toutes les informations de vos clients. Historique complet des devis, factures et chantiers.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Application mobile</h3>
                    <p>Accédez à vos données depuis le terrain. Signature électronique et prise de photos sur chantier.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>100% sécurisé</h3>
                    <p>Vos données sont protégées et sauvegardées. Conformité RGPD et loi anti-fraude TVA.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2>Prêt à simplifier votre gestion ?</h2>
            <p>Rejoignez des centaines d'entreprises du bâtiment qui nous font confiance</p>
            <a href="/register" class="btn btn-primary btn-lg">
                <i class="fas fa-rocket"></i>
                Essayer gratuitement
            </a>
        </div>
    </section>

    <footer class="landing-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> - Tous droits réservés</p>
        </div>
    </footer>
</body>
</html>
