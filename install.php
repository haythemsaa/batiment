<?php
/**
 * BatiSaaS - Script d'installation automatique
 *
 * Ce script automatise l'installation de l'application :
 * - Vérification des prérequis
 * - Configuration de la base de données
 * - Création des tables
 * - Création du compte administrateur
 * - Configuration initiale
 */

// Désactiver l'affichage des erreurs en production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si l'installation a déjà été effectuée
if (file_exists(__DIR__ . '/config/.installed')) {
    die('L\'application est déjà installée. Supprimez le fichier config/.installed pour réinstaller.');
}

// Étape actuelle de l'installation
$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Traitement des étapes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            // Vérification des prérequis
            $step = 2;
            break;

        case 2:
            // Configuration de la base de données
            $db_host = $_POST['db_host'] ?? 'localhost';
            $db_name = $_POST['db_name'] ?? 'batisaas';
            $db_user = $_POST['db_user'] ?? 'root';
            $db_pass = $_POST['db_pass'] ?? '';

            try {
                // Connexion sans base de données pour la créer
                $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Création de la base de données
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `$db_name`");

                // Lecture et exécution du schéma
                $schema = file_get_contents(__DIR__ . '/database/schema.sql');
                $pdo->exec($schema);

                // Sauvegarde de la configuration
                $config_content = "<?php\n\n";
                $config_content .= "define('DB_HOST', '$db_host');\n";
                $config_content .= "define('DB_NAME', '$db_name');\n";
                $config_content .= "define('DB_USER', '$db_user');\n";
                $config_content .= "define('DB_PASS', '$db_pass');\n";
                $config_content .= "define('DB_CHARSET', 'utf8mb4');\n";

                file_put_contents(__DIR__ . '/config/database.php', $config_content);

                $success = 'Base de données configurée avec succès !';
                $step = 3;

            } catch (PDOException $e) {
                $error = 'Erreur de connexion : ' . $e->getMessage();
            }
            break;

        case 3:
            // Création du compte administrateur
            require_once __DIR__ . '/config/database.php';

            try {
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                    DB_USER,
                    DB_PASS
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $company_name = $_POST['company_name'];
                $company_email = $_POST['company_email'];
                $admin_name = $_POST['admin_name'];
                $admin_email = $_POST['admin_email'];
                $admin_password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);

                // Créer l'entreprise
                $stmt = $pdo->prepare("
                    INSERT INTO companies (name, email, created_at)
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$company_name, $company_email]);
                $company_id = $pdo->lastInsertId();

                // Créer l'utilisateur admin
                $stmt = $pdo->prepare("
                    INSERT INTO users (company_id, name, email, password, role, status, created_at)
                    VALUES (?, ?, ?, ?, 'admin', 'active', NOW())
                ");
                $stmt->execute([$company_id, $admin_name, $admin_email, $admin_password]);

                // Marquer l'installation comme terminée
                file_put_contents(__DIR__ . '/config/.installed', date('Y-m-d H:i:s'));

                $success = 'Installation terminée avec succès !';
                $step = 4;

            } catch (PDOException $e) {
                $error = 'Erreur lors de la création du compte : ' . $e->getMessage();
            }
            break;
    }
}

// Fonction de vérification des prérequis
function checkRequirements() {
    $requirements = [
        'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'Extension PDO' => extension_loaded('pdo'),
        'Extension PDO MySQL' => extension_loaded('pdo_mysql'),
        'Extension mbstring' => extension_loaded('mbstring'),
        'Extension JSON' => extension_loaded('json'),
        'Dossier config/ inscriptible' => is_writable(__DIR__ . '/config'),
        'Dossier public/uploads/ inscriptible' => is_writable(__DIR__ . '/public/uploads'),
    ];

    return $requirements;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - BatiSaaS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .install-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }

        .install-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .install-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .install-header p {
            opacity: 0.9;
        }

        .install-body {
            padding: 40px;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e5e7eb;
            z-index: 1;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }

        .step.active .step-circle {
            background: #2563eb;
            color: white;
        }

        .step.completed .step-circle {
            background: #10b981;
            color: white;
        }

        .step-label {
            font-size: 12px;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #374151;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .btn {
            padding: 12px 30px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .requirements-list {
            list-style: none;
        }

        .requirements-list li {
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .requirements-list li.ok {
            background: #d1fae5;
        }

        .requirements-list li.error {
            background: #fee2e2;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-success {
            background: #10b981;
            color: white;
        }

        .badge-error {
            background: #ef4444;
            color: white;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 40px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>🏗️ BatiSaaS</h1>
            <p>Installation de votre application de gestion de chantiers</p>
        </div>

        <div class="install-body">
            <!-- Indicateur de progression -->
            <div class="steps">
                <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">
                    <div class="step-circle">1</div>
                    <div class="step-label">Prérequis</div>
                </div>
                <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">
                    <div class="step-circle">2</div>
                    <div class="step-label">Base de données</div>
                </div>
                <div class="step <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>">
                    <div class="step-circle">3</div>
                    <div class="step-label">Configuration</div>
                </div>
                <div class="step <?= $step >= 4 ? 'active' : '' ?>">
                    <div class="step-circle">4</div>
                    <div class="step-label">Terminé</div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Étape 1 : Vérification des prérequis -->
            <?php if ($step == 1): ?>
                <h2>Vérification des prérequis</h2>
                <p style="color: #6b7280; margin-bottom: 20px;">
                    Vérification de la configuration de votre serveur...
                </p>

                <ul class="requirements-list">
                    <?php
                    $requirements = checkRequirements();
                    $all_ok = true;
                    foreach ($requirements as $name => $status):
                        if (!$status) $all_ok = false;
                    ?>
                        <li class="<?= $status ? 'ok' : 'error' ?>">
                            <span><?= $name ?></span>
                            <span class="badge <?= $status ? 'badge-success' : 'badge-error' ?>">
                                <?= $status ? '✓ OK' : '✗ Erreur' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <form method="POST" style="margin-top: 30px;">
                    <button type="submit" class="btn" <?= !$all_ok ? 'disabled' : '' ?>>
                        Continuer
                    </button>
                </form>
            <?php endif; ?>

            <!-- Étape 2 : Configuration de la base de données -->
            <?php if ($step == 2): ?>
                <h2>Configuration de la base de données</h2>
                <p style="color: #6b7280; margin-bottom: 20px;">
                    Entrez les informations de connexion à votre base de données MySQL.
                </p>

                <form method="POST">
                    <div class="form-group">
                        <label>Hôte de la base de données</label>
                        <input type="text" name="db_host" value="localhost" required>
                    </div>

                    <div class="form-group">
                        <label>Nom de la base de données</label>
                        <input type="text" name="db_name" value="batisaas" required>
                    </div>

                    <div class="form-group">
                        <label>Utilisateur</label>
                        <input type="text" name="db_user" value="root" required>
                    </div>

                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="password" name="db_pass">
                    </div>

                    <button type="submit" class="btn">Créer la base de données</button>
                </form>
            <?php endif; ?>

            <!-- Étape 3 : Configuration du compte administrateur -->
            <?php if ($step == 3): ?>
                <h2>Créer votre compte</h2>
                <p style="color: #6b7280; margin-bottom: 20px;">
                    Créez le compte administrateur principal de votre entreprise.
                </p>

                <form method="POST">
                    <h3 style="margin-bottom: 15px; color: #374151;">Informations de l'entreprise</h3>

                    <div class="form-group">
                        <label>Nom de l'entreprise</label>
                        <input type="text" name="company_name" required>
                    </div>

                    <div class="form-group">
                        <label>Email de l'entreprise</label>
                        <input type="email" name="company_email" required>
                    </div>

                    <h3 style="margin: 30px 0 15px; color: #374151;">Compte administrateur</h3>

                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="admin_name" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="admin_email" required>
                    </div>

                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="password" name="admin_password" required minlength="6">
                    </div>

                    <button type="submit" class="btn">Terminer l'installation</button>
                </form>
            <?php endif; ?>

            <!-- Étape 4 : Installation terminée -->
            <?php if ($step == 4): ?>
                <div style="text-align: center;">
                    <div class="success-icon">✓</div>
                    <h2 style="color: #10b981; margin-bottom: 10px;">Installation réussie !</h2>
                    <p style="color: #6b7280; margin-bottom: 30px;">
                        Votre application BatiSaaS est prête à être utilisée.
                    </p>

                    <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: left;">
                        <h3 style="margin-bottom: 10px;">Prochaines étapes :</h3>
                        <ol style="margin-left: 20px; color: #374151;">
                            <li style="margin-bottom: 8px;">Supprimez le fichier <code>install.php</code> pour des raisons de sécurité</li>
                            <li style="margin-bottom: 8px;">Connectez-vous avec vos identifiants administrateur</li>
                            <li style="margin-bottom: 8px;">Configurez les paramètres de votre entreprise</li>
                            <li>Commencez à gérer vos chantiers !</li>
                        </ol>
                    </div>

                    <a href="/login" class="btn" style="display: inline-block; text-decoration: none;">
                        Accéder à l'application
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
