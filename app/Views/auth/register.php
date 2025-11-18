<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-hard-hat"></i>
                <h1>Créer votre compte</h1>
                <p>Essai gratuit de 30 jours</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                <?= $_SESSION['flash']['message'] ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form method="POST" action="/register" class="auth-form">
                <div class="form-group">
                    <label for="company_name">
                        <i class="fas fa-building"></i>
                        Nom de l'entreprise
                    </label>
                    <input type="text" id="company_name" name="company_name" required autofocus>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">
                            <i class="fas fa-user"></i>
                            Prénom
                        </label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Nom</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Mot de passe (min. 6 caractères)
                    </label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i>
                    Créer mon compte
                </button>
            </form>

            <div class="auth-footer">
                <p>Déjà un compte ? <a href="/login">Se connecter</a></p>
            </div>
        </div>
    </div>
</body>
</html>
