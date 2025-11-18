<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Gestion d'entreprise du bâtiment</title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="/dashboard">
                <i class="fas fa-hard-hat"></i>
                <span><?= APP_NAME ?></span>
            </a>
        </div>

        <div class="navbar-menu">
            <a href="/dashboard" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="/devis" class="nav-item">
                <i class="fas fa-file-invoice"></i>
                <span>Devis</span>
            </a>
            <a href="/factures" class="nav-item">
                <i class="fas fa-receipt"></i>
                <span>Factures</span>
            </a>
            <a href="/chantiers" class="nav-item">
                <i class="fas fa-hammer"></i>
                <span>Chantiers</span>
            </a>
            <a href="/clients" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Clients</span>
            </a>
        </div>

        <div class="navbar-user">
            <div class="user-info">
                <span class="user-name"><?= $_SESSION['user_name'] ?? 'Utilisateur' ?></span>
                <span class="user-role"><?= ucfirst($_SESSION['user_role'] ?? 'user') ?></span>
            </div>
            <div class="user-menu">
                <a href="/settings"><i class="fas fa-cog"></i></a>
                <a href="/logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="main-content">
        <?php
        // Affichage des messages flash
        if (isset($_SESSION['flash'])):
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
        ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= $flash['message'] ?>
        </div>
        <?php endif; ?>
