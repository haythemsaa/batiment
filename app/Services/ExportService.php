<?php

namespace App\Services;

/**
 * Service d'export CSV/Excel
 */
class ExportService {

    /**
     * Exporte des données en CSV
     */
    public function exportCSV($data, $filename, $headers = []) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM UTF-8 pour Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // En-têtes
        if (!empty($headers)) {
            fputcsv($output, $headers, ';');
        } elseif (!empty($data)) {
            fputcsv($output, array_keys($data[0]), ';');
        }

        // Données
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Exporte les chantiers en CSV
     */
    public function exportChantiers($chantiers) {
        $data = [];
        foreach ($chantiers as $c) {
            $data[] = [
                'ID' => $c['id'],
                'Nom' => $c['name'],
                'Client' => $c['client_name'] ?? '',
                'Statut' => $c['status'],
                'Progression' => $c['progress'] . '%',
                'Budget estimé' => $c['estimated_budget'] . ' €',
                'Date début' => $c['start_date'],
                'Date fin' => $c['end_date'],
                'Créé le' => $c['created_at']
            ];
        }

        $this->exportCSV($data, 'chantiers_' . date('Y-m-d'));
    }

    /**
     * Exporte les factures en CSV
     */
    public function exportFactures($factures) {
        $data = [];
        foreach ($factures as $f) {
            $data[] = [
                'Numéro' => $f['number'],
                'Client' => $f['client_name'] ?? '',
                'Date' => $f['date'],
                'Échéance' => $f['due_date'],
                'Total HT' => number_format($f['total'] / 1.2, 2) . ' €',
                'TVA' => number_format($f['total'] - ($f['total'] / 1.2), 2) . ' €',
                'Total TTC' => $f['total'] . ' €',
                'Payé' => $f['paid_amount'] . ' €',
                'Reste' => ($f['total'] - $f['paid_amount']) . ' €',
                'Statut' => $f['status']
            ];
        }

        $this->exportCSV($data, 'factures_' . date('Y-m-d'));
    }

    /**
     * Exporte les clients en CSV
     */
    public function exportClients($clients) {
        $data = [];
        foreach ($clients as $c) {
            $data[] = [
                'ID' => $c['id'],
                'Type' => $c['type'],
                'Nom' => $c['name'],
                'Email' => $c['email'],
                'Téléphone' => $c['phone'],
                'Adresse' => $c['address'],
                'Code postal' => $c['postal_code'],
                'Ville' => $c['city'],
                'SIRET' => $c['siret'] ?? '',
                'Statut' => $c['status'],
                'Créé le' => $c['created_at']
            ];
        }

        $this->exportCSV($data, 'clients_' . date('Y-m-d'));
    }

    /**
     * Importe des clients depuis CSV
     */
    public function importClients($file, $companyId, $db) {
        if (($handle = fopen($file['tmp_name'], 'r')) !== FALSE) {
            // Ignorer la première ligne (en-têtes)
            fgetcsv($handle, 1000, ';');

            $imported = 0;
            $errors = [];

            while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO clients (
                            company_id, type, name, email, phone,
                            address, postal_code, city, siret, status, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");

                    $stmt->execute([
                        $companyId,
                        $data[1] ?? 'individual', // Type
                        $data[2] ?? '', // Nom
                        $data[3] ?? null, // Email
                        $data[4] ?? null, // Téléphone
                        $data[5] ?? null, // Adresse
                        $data[6] ?? null, // Code postal
                        $data[7] ?? null, // Ville
                        $data[8] ?? null, // SIRET
                        $data[9] ?? 'active' // Statut
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Ligne " . ($imported + 2) . ": " . $e->getMessage();
                }
            }

            fclose($handle);

            return [
                'success' => true,
                'imported' => $imported,
                'errors' => $errors
            ];
        }

        return ['success' => false, 'error' => 'Impossible de lire le fichier'];
    }

    /**
     * Génère un Excel simple (HTML table)
     */
    public function exportExcel($data, $filename, $headers = []) {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');

        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        echo '</head>';
        echo '<body>';
        echo '<table border="1">';

        // En-têtes
        if (!empty($headers)) {
            echo '<tr>';
            foreach ($headers as $header) {
                echo '<th>' . htmlspecialchars($header) . '</th>';
            }
            echo '</tr>';
        } elseif (!empty($data)) {
            echo '<tr>';
            foreach (array_keys($data[0]) as $key) {
                echo '<th>' . htmlspecialchars($key) . '</th>';
            }
            echo '</tr>';
        }

        // Données
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }

        echo '</table>';
        echo '</body></html>';
        exit;
    }
}
