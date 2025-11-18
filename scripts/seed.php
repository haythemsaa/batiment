#!/usr/bin/env php
<?php
/**
 * Script de seeding (données de démonstration)
 * Usage: php scripts/seed.php
 *
 * Remplit la base de données avec des données de test
 */

require_once __DIR__ . '/../config/database.php';

echo "🌱 Seeding de la base de données...\n\n";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier qu'une entreprise existe déjà
    $stmt = $pdo->query("SELECT id FROM companies LIMIT 1");
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        echo "❌ Aucune entreprise trouvée. Exécutez d'abord install.php\n";
        exit(1);
    }

    $companyId = $company['id'];

    // 1. Créer des clients
    echo "👥 Création de clients...\n";

    $clients = [
        ['Jean Dupont', 'jean.dupont@email.fr', '0612345678', '12 rue de la Paix', '75001', 'Paris', 'individual'],
        ['Marie Martin', 'marie.martin@email.fr', '0687654321', '45 avenue Victor Hugo', '69002', 'Lyon', 'individual'],
        ['SARL Construction Plus', 'contact@constructionplus.fr', '0143556677', '88 boulevard Haussmann', '75008', 'Paris', 'company'],
        ['SCI Immobilière du Sud', 'sci@immosud.fr', '0491223344', '15 rue Paradis', '13001', 'Marseille', 'company'],
        ['Pierre Dubois', 'p.dubois@email.fr', '0623456789', '7 place Bellecour', '69002', 'Lyon', 'individual']
    ];

    $clientIds = [];
    foreach ($clients as $client) {
        $stmt = $pdo->prepare("
            INSERT INTO clients (company_id, type, name, email, phone, address, postal_code, city, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([
            $companyId,
            $client[6],
            $client[0],
            $client[1],
            $client[2],
            $client[3],
            $client[4],
            $client[5]
        ]);
        $clientIds[] = $pdo->lastInsertId();
        echo "  ✅ Client créé: {$client[0]}\n";
    }

    // 2. Créer des fournisseurs
    echo "\n🚚 Création de fournisseurs...\n";

    $fournisseurs = [
        ['Matériaux du Bâtiment', 'contact@materiaux-bat.fr', '0144556677', '25 rue industrielle', '93100', 'Montreuil'],
        ['Électricité Pro', 'info@electricite-pro.fr', '0145667788', '10 avenue de la République', '75011', 'Paris'],
        ['Plomberie Services', 'contact@plomberie-services.fr', '0146778899', '32 rue des Artisans', '92100', 'Boulogne'],
        ['Peinture & Décoration', 'vente@peinture-deco.fr', '0147889900', '18 boulevard Pasteur', '75015', 'Paris']
    ];

    foreach ($fournisseurs as $fournisseur) {
        $stmt = $pdo->prepare("
            INSERT INTO fournisseurs (company_id, name, email, phone, address, postal_code, city, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([
            $companyId,
            $fournisseur[0],
            $fournisseur[1],
            $fournisseur[2],
            $fournisseur[3],
            $fournisseur[4],
            $fournisseur[5]
        ]);
        echo "  ✅ Fournisseur créé: {$fournisseur[0]}\n";
    }

    // 3. Créer des chantiers
    echo "\n🏗️  Création de chantiers...\n";

    $chantiers = [
        ['Rénovation appartement 3 pièces', $clientIds[0], 'Rénovation complète', '2024-01-15', '2024-03-31', 25000, 'en_cours', 65],
        ['Construction maison individuelle', $clientIds[1], 'Construction neuve', '2024-02-01', '2024-10-31', 180000, 'en_cours', 40],
        ['Extension cuisine', $clientIds[2], 'Agrandissement', '2024-03-10', '2024-05-15', 35000, 'planifie', 0],
        ['Rénovation bureaux', $clientIds[3], 'Rénovation commerciale', '2023-11-01', '2024-01-31', 85000, 'termine', 100],
        ['Aménagement combles', $clientIds[4], 'Aménagement intérieur', '2024-01-20', '2024-04-30', 42000, 'en_cours', 55]
    ];

    $chantierIds = [];
    foreach ($chantiers as $chantier) {
        $stmt = $pdo->prepare("
            INSERT INTO chantiers (company_id, client_id, name, description, start_date, end_date, estimated_budget, status, progress, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $companyId,
            $chantier[1],
            $chantier[0],
            $chantier[2],
            $chantier[3],
            $chantier[4],
            $chantier[5],
            $chantier[6],
            $chantier[7]
        ]);
        $chantierIds[] = $pdo->lastInsertId();
        echo "  ✅ Chantier créé: {$chantier[0]}\n";
    }

    // 4. Créer des devis
    echo "\n📋 Création de devis...\n";

    $devisCount = 0;
    foreach ($chantierIds as $index => $chantierId) {
        $devisNumber = 'DEV-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
        $clientId = $chantiers[$index][1];
        $amount = $chantiers[$index][5];
        $status = $chantiers[$index][6] === 'planifie' ? 'envoye' : 'accepte';

        $stmt = $pdo->prepare("
            INSERT INTO devis (company_id, chantier_id, client_id, number, date, validity_date, total, status, created_at)
            VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_ADD(NOW(), INTERVAL 10 DAY), ?, ?, NOW())
        ");
        $stmt->execute([
            $companyId,
            $chantierId,
            $clientId,
            $devisNumber,
            $amount,
            $status
        ]);
        $devisCount++;
        echo "  ✅ Devis créé: $devisNumber\n";
    }

    // 5. Créer des factures pour les chantiers acceptés
    echo "\n🧾 Création de factures...\n";

    $factureCount = 0;
    foreach ($chantierIds as $index => $chantierId) {
        if ($chantiers[$index][6] !== 'planifie') {
            $factureNumber = 'FAC-' . date('Y') . '-' . str_pad($factureCount + 1, 4, '0', STR_PAD_LEFT);
            $clientId = $chantiers[$index][1];
            $amount = $chantiers[$index][5];
            $paidAmount = $chantiers[$index][6] === 'termine' ? $amount : $amount * 0.5;

            $stmt = $pdo->prepare("
                INSERT INTO factures (company_id, chantier_id, client_id, number, date, due_date, total, paid_amount, status, created_at)
                VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_ADD(NOW(), INTERVAL 15 DAY), ?, ?, ?, NOW())
            ");
            $status = $paidAmount >= $amount ? 'payee' : 'envoyee';
            $stmt->execute([
                $companyId,
                $chantierId,
                $clientId,
                $factureNumber,
                $amount,
                $paidAmount,
                $status
            ]);
            $factureCount++;
            echo "  ✅ Facture créée: $factureNumber\n";
        }
    }

    // 6. Créer des dépenses
    echo "\n💰 Création de dépenses...\n";

    $depenses = [
        ['Matériaux construction', $chantierIds[1], 15000, '2024-02-10', 'materiel'],
        ['Main d\'oeuvre', $chantierIds[0], 8000, '2024-01-20', 'main_doeuvre'],
        ['Équipement électrique', $chantierIds[0], 2500, '2024-02-05', 'materiel'],
        ['Peinture et finitions', $chantierIds[4], 3200, '2024-02-15', 'materiel'],
        ['Location nacelle', $chantierIds[1], 1800, '2024-02-20', 'equipement']
    ];

    foreach ($depenses as $depense) {
        $stmt = $pdo->prepare("
            INSERT INTO depenses (company_id, chantier_id, description, amount, date, category, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $companyId,
            $depense[1],
            $depense[0],
            $depense[2],
            $depense[3],
            $depense[4]
        ]);
        echo "  ✅ Dépense créée: {$depense[0]}\n";
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✨ Seeding terminé avec succès!\n";
    echo "📊 Résumé:\n";
    echo "  • Clients: " . count($clients) . "\n";
    echo "  • Fournisseurs: " . count($fournisseurs) . "\n";
    echo "  • Chantiers: " . count($chantiers) . "\n";
    echo "  • Devis: $devisCount\n";
    echo "  • Factures: $factureCount\n";
    echo "  • Dépenses: " . count($depenses) . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

} catch (PDOException $e) {
    echo "\n❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
