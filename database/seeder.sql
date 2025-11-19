-- BatiSaaS - Données de démonstration
-- Permet de tester immédiatement toutes les fonctionnalités de l'application
-- Exécuter après schema.sql

-- Insertion d'une entreprise de démonstration
INSERT INTO companies (id, name, email, phone, siret, address, city, postal_code, tva_number, capital, status, subscription_plan) VALUES
(1, 'BâtiPro Construction', 'contact@batipro.fr', '01 23 45 67 89', '12345678901234', '123 Avenue des Bâtisseurs', 'Paris', '75001', 'FR12345678901', 100000.00, 'active', 'premium');

-- Insertion d'utilisateurs (mot de passe : password pour tous)
INSERT INTO users (id, company_id, email, password, first_name, last_name, phone, role, status) VALUES
(1, 1, 'admin@batipro.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jean', 'Martin', '06 12 34 56 78', 'admin', 'active'),
(2, 1, 'chef@batipro.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie', 'Dupont', '06 23 45 67 89', 'manager', 'active'),
(3, 1, 'ouvrier1@batipro.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pierre', 'Lefebvre', '06 34 56 78 90', 'user', 'active'),
(4, 1, 'ouvrier2@batipro.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophie', 'Bernard', '06 45 67 89 01', 'user', 'active'),
(5, 1, 'technicien@batipro.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Thomas', 'Petit', '06 56 78 90 12', 'user', 'active');

-- Insertion de clients
INSERT INTO clients (id, company_id, type, civility, first_name, last_name, company_name, siret, address, city, postal_code, phone, email, status) VALUES
(1, 1, 'individual', 'M', 'François', 'Dubois', NULL, NULL, '45 Rue de la Paix', 'Lyon', '69001', '04 12 34 56 78', 'francois.dubois@example.com', 'active'),
(2, 1, 'company', 'M', 'Robert', 'Moreau', 'Immobilière du Centre', '98765432109876', '78 Boulevard Haussmann', 'Paris', '75008', '01 98 76 54 32', 'contact@immobiliere-centre.fr', 'active'),
(3, 1, 'individual', 'Mme', 'Claire', 'Laurent', NULL, NULL, '12 Avenue Victor Hugo', 'Marseille', '13001', '04 91 23 45 67', 'claire.laurent@example.com', 'active'),
(4, 1, 'company', 'M', 'Michel', 'Simon', 'Résidences Premium SARL', '11223344556677', '90 Rue du Commerce', 'Bordeaux', '33000', '05 56 78 90 12', 'contact@residences-premium.fr', 'active');

-- Insertion de fournisseurs
INSERT INTO fournisseurs (id, company_id, name, siret, address, city, postal_code, phone, email, website, contact_name, status) VALUES
(1, 1, 'Matériaux Pro', '11111111111111', '34 Zone Industrielle Nord', 'Rungis', '94150', '01 45 67 89 01', 'contact@materiaux-pro.fr', 'www.materiaux-pro.fr', 'Jacques Mercier', 'active'),
(2, 1, 'Outillage Expert', '22222222222222', '56 Rue de l\'Industrie', 'Villeurbanne', '69100', '04 78 90 12 34', 'ventes@outillage-expert.fr', 'www.outillage-expert.fr', 'Sophie Rousseau', 'active'),
(3, 1, 'Électricité Moderne', '33333333333333', '12 Avenue de l\'Électricité', 'Nantes', '44000', '02 40 12 34 56', 'contact@elec-moderne.fr', 'www.elec-moderne.fr', 'Laurent Vincent', 'active'),
(4, 1, 'Plomberie Plus', '44444444444444', '89 Rue des Artisans', 'Toulouse', '31000', '05 61 23 45 67', 'info@plomberie-plus.fr', 'www.plomberie-plus.fr', 'Marc Fournier', 'active');

-- Insertion de chantiers
INSERT INTO chantiers (id, company_id, client_id, name, reference, address, city, postal_code, description, start_date, end_date, estimated_budget, actual_cost, status, progress_percent, created_by) VALUES
(1, 1, 1, 'Rénovation Villa Dubois', 'CH-2024-001', '45 Rue de la Paix', 'Lyon', '69001', 'Rénovation complète d\'une villa : toiture, façade, plomberie et électricité', '2024-01-15', '2024-04-30', 125000.00, 78500.00, 'in_progress', 65.00, 1),
(2, 1, 2, 'Construction Résidence Le Parc', 'CH-2024-002', '78 Boulevard Haussmann', 'Paris', '75008', 'Construction d\'une résidence de 12 appartements', '2024-02-01', '2024-12-31', 850000.00, 425000.00, 'in_progress', 42.00, 1),
(3, 1, 3, 'Extension Maison Laurent', 'CH-2024-003', '12 Avenue Victor Hugo', 'Marseille', '13001', 'Extension de 40m² avec véranda', '2024-03-10', '2024-06-15', 65000.00, 15000.00, 'planned', 5.00, 2),
(4, 1, 4, 'Réhabilitation Immeuble Premium', 'CH-2024-004', '90 Rue du Commerce', 'Bordeaux', '33000', 'Réhabilitation complète d\'un immeuble de 8 étages', '2024-01-05', '2024-08-20', 450000.00, 285000.00, 'in_progress', 75.00, 1),
(5, 1, 1, 'Aménagement Combles Dubois', 'CH-2024-005', '45 Rue de la Paix', 'Lyon', '69001', 'Aménagement des combles en 2 chambres', '2024-05-01', '2024-07-15', 45000.00, 0.00, 'planned', 0.00, 2);

-- Affectation des utilisateurs aux chantiers
INSERT INTO chantier_users (company_id, chantier_id, user_id, role) VALUES
(1, 1, 2, 'Chef de chantier'),
(1, 1, 3, 'Maçon'),
(1, 1, 4, 'Électricien'),
(1, 2, 2, 'Chef de chantier'),
(1, 2, 3, 'Maçon'),
(1, 2, 5, 'Plombier'),
(1, 4, 2, 'Chef de chantier'),
(1, 4, 3, 'Maçon'),
(1, 4, 4, 'Électricien'),
(1, 4, 5, 'Plombier');

-- Insertion de tâches pour le chantier 1
INSERT INTO chantier_tasks (id, chantier_id, name, description, start_date, end_date, duration_days, assigned_to, status, progress_percent, position) VALUES
(1, 1, 'Démolition existant', 'Démolition des cloisons et anciens équipements', '2024-01-15', '2024-01-25', 10, 3, 'completed', 100.00, 1),
(2, 1, 'Maçonnerie', 'Reconstruction des murs porteurs', '2024-01-26', '2024-02-15', 20, 3, 'completed', 100.00, 2),
(3, 1, 'Plomberie', 'Installation complète du réseau de plomberie', '2024-02-16', '2024-03-05', 18, 5, 'in_progress', 80.00, 3),
(4, 1, 'Électricité', 'Mise aux normes électriques', '2024-02-20', '2024-03-10', 19, 4, 'in_progress', 75.00, 4),
(5, 1, 'Toiture', 'Réfection complète de la toiture', '2024-03-06', '2024-03-25', 19, 3, 'pending', 0.00, 5),
(6, 1, 'Façade', 'Ravalement et isolation façade', '2024-03-26', '2024-04-15', 20, 3, 'pending', 0.00, 6),
(7, 1, 'Finitions', 'Peinture et revêtements sols', '2024-04-16', '2024-04-30', 14, 4, 'pending', 0.00, 7);

-- Insertion de tâches pour le chantier 2
INSERT INTO chantier_tasks (id, chantier_id, name, description, start_date, end_date, duration_days, assigned_to, status, progress_percent, position) VALUES
(8, 2, 'Terrassement', 'Préparation du terrain', '2024-02-01', '2024-02-20', 19, 3, 'completed', 100.00, 1),
(9, 2, 'Fondations', 'Coulage des fondations', '2024-02-21', '2024-03-15', 23, 3, 'completed', 100.00, 2),
(10, 2, 'Structure béton', 'Montage de la structure', '2024-03-16', '2024-05-30', 75, 3, 'in_progress', 60.00, 3),
(11, 2, 'Toiture', 'Charpente et couverture', '2024-06-01', '2024-07-15', 44, 3, 'pending', 0.00, 4),
(12, 2, 'Cloisonnement', 'Montage des cloisons intérieures', '2024-07-16', '2024-08-30', 45, 3, 'pending', 0.00, 5);

-- Insertion de stocks
INSERT INTO stocks (id, company_id, name, reference, description, category, unit, quantity_current, quantity_min, quantity_max, unit_price, location, supplier_id, status) VALUES
(1, 1, 'Ciment gris 25kg', 'CIM-001', 'Sac de ciment gris 25kg', 'Matériaux', 'sac', 45.00, 20.00, 100.00, 8.50, 'Dépôt principal', 1, 'active'),
(2, 1, 'Parpaing 20x20x50', 'PAR-001', 'Parpaing standard 20x20x50cm', 'Matériaux', 'unité', 1250.00, 500.00, 2000.00, 1.20, 'Dépôt principal', 1, 'active'),
(3, 1, 'Câble électrique 2.5mm²', 'ELEC-001', 'Câble électrique 2.5mm² (rouleau 100m)', 'Électricité', 'rouleau', 8.00, 5.00, 15.00, 45.00, 'Atelier', 3, 'active'),
(4, 1, 'Tuyau PER Ø16', 'PLO-001', 'Tuyau PER diamètre 16mm (rouleau 100m)', 'Plomberie', 'rouleau', 6.00, 3.00, 12.00, 78.00, 'Atelier', 4, 'active'),
(5, 1, 'Plaque de plâtre BA13', 'PLA-001', 'Plaque de plâtre BA13 250x120cm', 'Matériaux', 'plaque', 85.00, 30.00, 150.00, 6.80, 'Dépôt principal', 1, 'active'),
(6, 1, 'Sable 0/4', 'SAB-001', 'Sable 0/4 (big bag 1 tonne)', 'Matériaux', 'tonne', 12.50, 5.00, 20.00, 32.00, 'Extérieur', 1, 'active'),
(7, 1, 'Disjoncteur différentiel 30mA', 'ELEC-002', 'Disjoncteur différentiel 30mA 40A', 'Électricité', 'unité', 15.00, 10.00, 30.00, 65.00, 'Atelier', 3, 'active'),
(8, 1, 'Robinet mélangeur', 'PLO-002', 'Robinet mélangeur lavabo chromé', 'Plomberie', 'unité', 8.00, 5.00, 20.00, 42.00, 'Atelier', 4, 'active');

-- Insertion de mouvements de stock
INSERT INTO stock_movements (company_id, stock_id, chantier_id, type, quantity, quantity_before, quantity_after, unit_price, total_price, reference, reason, performed_by, movement_date) VALUES
(1, 1, 1, 'out', 25.00, 70.00, 45.00, 8.50, 212.50, 'MOV-001', 'Sortie pour chantier Villa Dubois', 2, '2024-02-01 08:30:00'),
(1, 2, 1, 'out', 500.00, 1750.00, 1250.00, 1.20, 600.00, 'MOV-002', 'Sortie pour chantier Villa Dubois', 2, '2024-01-20 09:00:00'),
(1, 3, 1, 'out', 2.00, 10.00, 8.00, 45.00, 90.00, 'MOV-003', 'Sortie électricité Villa Dubois', 4, '2024-02-25 10:15:00'),
(1, 4, 1, 'out', 3.00, 9.00, 6.00, 78.00, 234.00, 'MOV-004', 'Sortie plomberie Villa Dubois', 5, '2024-02-18 11:00:00'),
(1, 5, 2, 'out', 65.00, 150.00, 85.00, 6.80, 442.00, 'MOV-005', 'Sortie pour Résidence Le Parc', 2, '2024-04-10 14:30:00'),
(1, 6, 'in', NULL, 5.00, 7.50, 12.50, 32.00, 160.00, 'MOV-006', 'Réapprovisionnement', 1, '2024-03-15 16:00:00');

-- Insertion de pointages / timesheets
INSERT INTO timesheets (company_id, user_id, chantier_id, clock_in, clock_out, clock_in_latitude, clock_in_longitude, break_duration, total_hours, status) VALUES
(1, 3, 1, '2024-03-18 07:30:00', '2024-03-18 17:00:00', 45.7640, 4.8357, 60, 8.50, 'validated'),
(1, 4, 1, '2024-03-18 07:45:00', '2024-03-18 17:15:00', 45.7640, 4.8357, 60, 8.50, 'validated'),
(1, 3, 1, '2024-03-19 07:35:00', '2024-03-19 17:05:00', 45.7640, 4.8357, 60, 8.50, 'validated'),
(1, 4, 1, '2024-03-19 07:40:00', '2024-03-19 17:10:00', 45.7640, 4.8357, 60, 8.50, 'validated'),
(1, 3, 2, '2024-03-20 08:00:00', '2024-03-20 18:00:00', 48.8566, 2.3522, 60, 9.00, 'validated'),
(1, 5, 2, '2024-03-20 08:00:00', '2024-03-20 18:00:00', 48.8566, 2.3522, 60, 9.00, 'validated'),
(1, 3, 1, '2024-03-21 07:30:00', NULL, 45.7640, 4.8357, 0, NULL, 'clocked_in');

-- Insertion de messages
INSERT INTO messages (company_id, sender_id, recipient_id, chantier_id, subject, message, is_read) VALUES
(1, 2, 3, 1, 'Début de la phase toiture', 'Bonjour Pierre, nous commençons la réfection de la toiture lundi prochain. Merci de prévoir le matériel nécessaire.', TRUE),
(1, 3, 2, 1, 'RE: Début de la phase toiture', 'Parfait, tout est prêt. J\'ai commandé les tuiles chez le fournisseur.', TRUE),
(1, 1, 2, 2, 'Point d\'avancement Résidence Le Parc', 'Pouvez-vous me faire un point sur l\'avancement du chantier Résidence Le Parc ? Le client demande des nouvelles.', TRUE),
(1, 2, 1, 2, 'RE: Point d\'avancement Résidence Le Parc', 'La structure béton avance bien, nous sommes à 60%. Nous serons dans les temps pour la fin prévue.', FALSE),
(1, 4, 5, 1, 'Problème alimentation électrique', 'Thomas, j\'ai besoin de ton aide sur l\'alimentation électrique de la pompe à chaleur. Tu peux passer demain ?', FALSE);

-- Insertion de punch lists (réserves)
INSERT INTO punch_lists (company_id, chantier_id, title, description, location, category, priority, status, reported_by, assigned_to, due_date) VALUES
(1, 1, 'Fissure mur salon', 'Petite fissure apparue sur le mur du salon après séchage', 'Salon, mur ouest', 'Maçonnerie', 'medium', 'open', 2, 3, '2024-04-10'),
(1, 1, 'Prise électrique défectueuse', 'La prise n°3 de la cuisine ne fonctionne pas', 'Cuisine, mur sud', 'Électricité', 'high', 'in_progress', 2, 4, '2024-03-25'),
(1, 2, 'Écart planéité dalle', 'La dalle du 2ème étage présente un écart de planéité de 8mm', 'Étage 2, zone B', 'Maçonnerie', 'high', 'resolved', 2, 3, '2024-03-20'),
(1, 4, 'Fuite tuyau WC', 'Légère fuite au niveau du raccord du WC appartement 3A', 'Appt 3A, WC', 'Plomberie', 'urgent', 'open', 2, 5, '2024-03-22'),
(1, 1, 'Porte mal ajustée', 'La porte de la chambre 2 frotte sur le sol', 'Chambre 2', 'Menuiserie', 'low', 'open', 3, NULL, '2024-04-15');

-- Insertion d'entrées du carnet de bord
INSERT INTO carnet_bord (company_id, chantier_id, date, weather, temperature, workers_count, work_description, materials_used, equipment_used, incidents, progress_notes, created_by) VALUES
(1, 1, '2024-03-18', 'ensoleillé', 18.5, 3, 'Poursuite des travaux de plomberie au rez-de-chaussée. Installation des radiateurs dans les chambres.', 'Tuyaux PER, radiateurs, raccords', 'Perceuse, cintreuse, niveau laser', NULL, 'Bon avancement, dans les temps prévus', 2),
(1, 1, '2024-03-19', 'nuageux', 16.0, 3, 'Finalisation de l\'installation électrique du rez-de-chaussée. Pose du tableau électrique principal.', 'Câbles 2.5mm², disjoncteurs, tableau électrique', 'Testeur électrique, tournevis, pince à dénuder', NULL, 'Électricité presque terminée au RDC', 2),
(1, 2, '2024-03-20', 'pluvieux', 12.0, 5, 'Coulage de la dalle du 3ème étage. Arrêt en milieu de journée à cause de la pluie.', 'Béton (12m³), treillis soudé, film polyane', 'Bétonnière, vibreur, règle', 'Pluie abondante l\'après-midi, arrêt des travaux à 14h', 'Reprise prévue dès amélioration météo', 2),
(1, 1, '2024-03-21', 'ensoleillé', 19.0, 3, 'Début de la préparation pour la réfection de la toiture. Mise en place des échafaudages.', 'Échafaudages, bâches de protection', 'Foreuse, boulonneuse', NULL, 'Échafaudages montés, prêts pour la toiture', 2);

-- Insertion de devis
INSERT INTO devis (id, company_id, client_id, number, date, validity_date, status, title, description, subtotal, tva_amount, total, created_by) VALUES
(1, 1, 1, 'DEV-2024-001', '2024-01-05', '2024-02-05', 'accepted', 'Rénovation Villa Dubois', 'Devis pour la rénovation complète de la villa', 104166.67, 20833.33, 125000.00, 1),
(2, 1, 2, 'DEV-2024-002', '2024-01-20', '2024-02-20', 'accepted', 'Construction Résidence Le Parc', 'Devis pour la construction d\'une résidence de 12 appartements', 708333.33, 141666.67, 850000.00, 1),
(3, 1, 3, 'DEV-2024-003', '2024-02-28', '2024-03-28', 'sent', 'Extension Maison Laurent', 'Devis pour l\'extension de 40m² avec véranda', 54166.67, 10833.33, 65000.00, 1),
(4, 1, 4, 'DEV-2024-004', '2024-03-10', '2024-04-10', 'draft', 'Rénovation Appartement T3', 'Devis pour rénovation complète appartement', 37500.00, 7500.00, 45000.00, 2);

-- Insertion de lignes de devis
INSERT INTO devis_items (devis_id, description, quantity, unit, unit_price, tva_rate, total, position) VALUES
(1, 'Réfection complète toiture 150m²', 150.00, 'm²', 85.00, 20.00, 12750.00, 1),
(1, 'Ravalement façade avec isolation', 200.00, 'm²', 120.00, 20.00, 24000.00, 2),
(1, 'Mise aux normes électricité complète', 1.00, 'forfait', 15000.00, 20.00, 15000.00, 3),
(1, 'Plomberie sanitaires et chauffage', 1.00, 'forfait', 25000.00, 20.00, 25000.00, 4),
(1, 'Maçonnerie et gros Suvre', 1.00, 'forfait', 27416.67, 20.00, 27416.67, 5),
(2, 'Terrassement et fondations', 1.00, 'forfait', 120000.00, 20.00, 120000.00, 1),
(2, 'Structure béton R+3', 1.00, 'forfait', 350000.00, 20.00, 350000.00, 2),
(2, 'Toiture et charpente', 1.00, 'forfait', 85000.00, 20.00, 85000.00, 3),
(2, 'Menuiseries extérieures', 1.00, 'forfait', 78333.33, 20.00, 78333.33, 4),
(2, 'Corps d\'état secondaires', 1.00, 'forfait', 75000.00, 20.00, 75000.00, 5);

-- Insertion de factures
INSERT INTO factures (id, company_id, client_id, devis_id, number, date, due_date, type, status, title, subtotal, tva_amount, total, paid_amount, remaining_amount, created_by) VALUES
(1, 1, 1, 1, 'FACT-2024-001', '2024-02-01', '2024-03-03', 'acompte', 'paid', 'Acompte 30% Rénovation Villa Dubois', 31250.00, 6250.00, 37500.00, 37500.00, 0.00, 1),
(2, 1, 1, 1, 'FACT-2024-002', '2024-03-15', '2024-04-15', 'facture', 'partially_paid', 'Avancement 50% Rénovation Villa Dubois', 52083.33, 10416.67, 62500.00, 25000.00, 37500.00, 1),
(3, 1, 2, 2, 'FACT-2024-003', '2024-03-01', '2024-04-01', 'acompte', 'paid', 'Acompte 40% Résidence Le Parc', 283333.33, 56666.67, 340000.00, 340000.00, 0.00, 1),
(4, 1, 4, NULL, 'FACT-2024-004', '2024-03-20', '2024-04-20', 'facture', 'sent', 'Travaux supplémentaires immeuble', 8333.33, 1666.67, 10000.00, 0.00, 10000.00, 1);

-- Insertion de lignes de facture
INSERT INTO facture_items (facture_id, description, quantity, unit, unit_price, tva_rate, total, position) VALUES
(1, 'Acompte 30% sur devis DEV-2024-001', 1.00, 'forfait', 31250.00, 20.00, 31250.00, 1),
(2, 'Avancement travaux 50%', 1.00, 'forfait', 52083.33, 20.00, 52083.33, 1),
(3, 'Acompte 40% sur devis DEV-2024-002', 1.00, 'forfait', 283333.33, 20.00, 283333.33, 1),
(4, 'Reprise maçonnerie suite sinistre', 10.00, 'heure', 65.00, 20.00, 650.00, 1),
(4, 'Fournitures complémentaires', 1.00, 'forfait', 7683.33, 20.00, 7683.33, 2);

-- Insertion de paiements
INSERT INTO payments (company_id, facture_id, amount, payment_date, payment_method, reference, created_by) VALUES
(1, 1, 37500.00, '2024-02-05', 'Virement bancaire', 'VIR-20240205-001', 1),
(1, 2, 25000.00, '2024-03-18', 'Chèque', 'CHQ-123456', 1),
(1, 3, 340000.00, '2024-03-05', 'Virement bancaire', 'VIR-20240305-002', 1);

-- Insertion de dépenses
INSERT INTO expenses (company_id, chantier_id, fournisseur_id, category, description, amount, tva_amount, date, payment_method, reference, created_by) VALUES
(1, 1, 1, 'Matériaux', 'Achat ciment et parpaings', 1850.00, 370.00, '2024-01-18', 'Carte bancaire', 'FACT-MAT-001', 2),
(1, 1, 3, 'Matériaux', 'Matériel électrique', 780.00, 156.00, '2024-02-22', 'Virement', 'FACT-ELEC-045', 2),
(1, 2, 1, 'Matériaux', 'Béton prêt à l\'emploi 25m³', 3250.00, 650.00, '2024-03-15', 'Virement', 'FACT-BET-089', 2),
(1, 1, 4, 'Matériaux', 'Équipement plomberie', 1240.00, 248.00, '2024-02-15', 'Chèque', 'FACT-PLO-234', 2),
(1, NULL, 2, 'Outillage', 'Disqueuse professionnelle', 420.00, 84.00, '2024-03-01', 'Carte bancaire', 'FACT-OUT-112', 1),
(1, 2, NULL, 'Carburant', 'Gasoil engins de chantier', 680.00, 136.00, '2024-03-20', 'Carte carburant', 'TOTAL-0321', 2);

-- Insertion de notifications
INSERT INTO notifications (company_id, user_id, type, title, message, action_url, related_type, related_id, is_read, priority) VALUES
(1, 2, 'task', 'Nouvelle tâche assignée', 'La tâche "Toiture" vous a été assignée sur le chantier Villa Dubois', '/chantiers/1/tasks', 'task', 5, FALSE, 'normal'),
(1, 1, 'payment', 'Paiement reçu', 'Paiement de 25 000¬ reçu pour la facture FACT-2024-002', '/factures/2', 'payment', 2, FALSE, 'normal'),
(1, 3, 'message', 'Nouveau message', 'Marie Dupont vous a envoyé un message concernant le chantier Villa Dubois', '/messages/1', 'message', 1, TRUE, 'normal'),
(1, 5, 'punch_list', 'Réserve urgente', 'Une nouvelle réserve urgente a été créée : Fuite tuyau WC', '/punch-lists/4', 'punch_list', 4, FALSE, 'high'),
(1, 1, 'invoice', 'Facture en retard', 'La facture FACT-2024-004 est en retard de paiement', '/factures/4', 'invoice', 4, FALSE, 'high');

-- Insertion de commentaires sur tâches
INSERT INTO task_comments (company_id, task_id, user_id, comment) VALUES
(1, 3, 2, 'Bon avancement sur la plomberie, encore 2 jours et c\'est terminé'),
(1, 3, 5, 'J\'ai installé tous les radiateurs du RDC, il reste l\'étage'),
(1, 4, 4, 'Le tableau électrique est posé, je commence le raccordement demain'),
(1, 10, 3, 'La structure du 3ème étage avance bien malgré la pluie d\'hier'),
(1, 1, 3, 'Démolition terminée, évacuation des gravats effectuée');

-- Insertion de paramètres
INSERT INTO settings (company_id, setting_key, setting_value) VALUES
(1, 'invoice_prefix', 'FACT'),
(1, 'quote_prefix', 'DEV'),
(1, 'default_tva_rate', '20.00'),
(1, 'invoice_payment_terms', '30'),
(1, 'company_logo', '/uploads/logos/batipro-logo.png'),
(1, 'email_notifications', 'true'),
(1, 'timezone', 'Europe/Paris'),
(1, 'currency', 'EUR'),
(1, 'date_format', 'd/m/Y'),
(1, 'pagination_limit', '25');

-- Insertion de quelques photos (exemples avec coordonnées GPS de Paris)
INSERT INTO photos (company_id, chantier_id, uploaded_by, title, description, file_path, thumbnail_path, latitude, longitude, taken_at, category) VALUES
(1, 1, 2, 'État initial façade', 'Photo de la façade avant travaux', '/uploads/photos/chantier-1/facade-avant.jpg', '/uploads/photos/chantier-1/thumb-facade-avant.jpg', 45.7640, 4.8357, '2024-01-15 09:30:00', 'before'),
(1, 1, 2, 'Fondations terminées', 'Fondations après coulage', '/uploads/photos/chantier-1/fondations.jpg', '/uploads/photos/chantier-1/thumb-fondations.jpg', 45.7640, 4.8357, '2024-02-10 14:20:00', 'progress'),
(1, 2, 2, 'Terrassement zone A', 'Avancement terrassement', '/uploads/photos/chantier-2/terrassement-a.jpg', '/uploads/photos/chantier-2/thumb-terrassement-a.jpg', 48.8566, 2.3522, '2024-02-15 10:00:00', 'progress'),
(1, 1, 4, 'Tableau électrique', 'Installation du nouveau tableau', '/uploads/photos/chantier-1/tableau-elec.jpg', '/uploads/photos/chantier-1/thumb-tableau-elec.jpg', 45.7640, 4.8357, '2024-03-19 16:45:00', 'progress');

-- Insertion de logs d'audit (RGPD)
INSERT INTO audit_logs (company_id, user_id, action, entity_type, entity_id, ip_address, user_agent) VALUES
(1, 1, 'login', 'user', 1, '192.168.1.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 1, 'create', 'chantier', 1, '192.168.1.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 2, 'update', 'chantier', 1, '192.168.1.15', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'),
(1, 1, 'create', 'invoice', 1, '192.168.1.10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 2, 'create', 'punch_list', 1, '192.168.1.15', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)');
