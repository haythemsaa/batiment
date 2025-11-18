<?php

namespace App\Services;

/**
 * Service de génération de PDF pour devis et factures
 *
 * Note: Ce service nécessite l'installation de TCPDF via Composer:
 * composer require tecnickcom/tcpdf
 *
 * Alternativement, vous pouvez utiliser DomPDF ou mPDF selon vos préférences.
 */

class PdfService {
    private $company;

    public function __construct($company) {
        $this->company = $company;
    }

    /**
     * Génère un PDF de devis
     */
    public function generateDevis($devis, $client, $items) {
        // Vérifier si TCPDF est installé
        if (!class_exists('TCPDF')) {
            return $this->generateHtmlPdf($this->renderDevisHtml($devis, $client, $items), "Devis_{$devis['number']}.pdf");
        }

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Métadonnées
        $pdf->SetCreator('BatiSaaS');
        $pdf->SetAuthor($this->company['name']);
        $pdf->SetTitle("Devis {$devis['number']}");

        // Marges et auto page break
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Police
        $pdf->SetFont('helvetica', '', 10);

        // Ajouter une page
        $pdf->AddPage();

        // Contenu
        $html = $this->renderDevisHtml($devis, $client, $items);
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output
        return $pdf->Output("Devis_{$devis['number']}.pdf", 'D');
    }

    /**
     * Génère un PDF de facture
     */
    public function generateFacture($facture, $client, $items) {
        if (!class_exists('TCPDF')) {
            return $this->generateHtmlPdf($this->renderFactureHtml($facture, $client, $items), "Facture_{$facture['number']}.pdf");
        }

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator('BatiSaaS');
        $pdf->SetAuthor($this->company['name']);
        $pdf->SetTitle("Facture {$facture['number']}");

        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->SetFont('helvetica', '', 10);

        $pdf->AddPage();

        $html = $this->renderFactureHtml($facture, $client, $items);
        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output("Facture_{$facture['number']}.pdf", 'D');
    }

    /**
     * Génère le HTML d'un devis
     */
    private function renderDevisHtml($devis, $client, $items) {
        $html = '
        <style>
            body { font-family: Helvetica, Arial, sans-serif; font-size: 10pt; }
            h1 { color: #2563eb; font-size: 24pt; }
            h2 { color: #1e40af; font-size: 14pt; margin-top: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background-color: #2563eb; color: white; padding: 8px; text-align: left; }
            td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
            .text-right { text-align: right; }
            .total-row { background-color: #f8fafc; font-weight: bold; }
            .header { margin-bottom: 30px; }
            .info-box { background: #f8fafc; padding: 10px; margin: 10px 0; border-radius: 5px; }
        </style>

        <div class="header">
            <h1>' . htmlspecialchars($this->company['name']) . '</h1>
            <p>' . htmlspecialchars($this->company['address']) . '<br>
            ' . htmlspecialchars($this->company['postal_code']) . ' ' . htmlspecialchars($this->company['city']) . '<br>
            Tél: ' . htmlspecialchars($this->company['phone']) . ' | Email: ' . htmlspecialchars($this->company['email']) . '</p>
            ' . ($this->company['siret'] ? '<p>SIRET: ' . htmlspecialchars($this->company['siret']) . '</p>' : '') . '
        </div>

        <h2>DEVIS N° ' . htmlspecialchars($devis['number']) . '</h2>

        <div class="info-box">
            <table style="border: none;">
                <tr>
                    <td style="border: none; width: 50%;"><strong>Client:</strong><br>
                        ' . htmlspecialchars($client['name']) . '<br>
                        ' . htmlspecialchars($client['address']) . '<br>
                        ' . htmlspecialchars($client['postal_code']) . ' ' . htmlspecialchars($client['city']) . '
                    </td>
                    <td style="border: none; width: 50%;">
                        <strong>Date:</strong> ' . date('d/m/Y', strtotime($devis['date'])) . '<br>
                        <strong>Validité:</strong> ' . date('d/m/Y', strtotime($devis['validity_date'])) . '<br>
                        <strong>Statut:</strong> ' . ucfirst($devis['status']) . '
                    </td>
                </tr>
            </table>
        </div>

        ' . ($devis['title'] ? '<p><strong>Objet:</strong> ' . htmlspecialchars($devis['title']) . '</p>' : '') . '

        <h2>Détail des prestations</h2>

        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Description</th>
                    <th class="text-right">Quantité</th>
                    <th class="text-right">Prix unitaire</th>
                    <th class="text-right">TVA</th>
                    <th class="text-right">Total HT</th>
                </tr>
            </thead>
            <tbody>';

        $totalHT = 0;
        $totalTVA = 0;

        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $itemTVA = $itemTotal * ($item['tva_rate'] / 100);
            $totalHT += $itemTotal;
            $totalTVA += $itemTVA;

            $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['description']) . '</td>
                    <td class="text-right">' . number_format($item['quantity'], 2, ',', ' ') . '</td>
                    <td class="text-right">' . number_format($item['unit_price'], 2, ',', ' ') . ' €</td>
                    <td class="text-right">' . number_format($item['tva_rate'], 1, ',', '') . '%</td>
                    <td class="text-right">' . number_format($itemTotal, 2, ',', ' ') . ' €</td>
                </tr>';
        }

        $totalTTC = $totalHT + $totalTVA;

        $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>Total HT</strong></td>
                    <td class="text-right"><strong>' . number_format($totalHT, 2, ',', ' ') . ' €</strong></td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>TVA</strong></td>
                    <td class="text-right"><strong>' . number_format($totalTVA, 2, ',', ' ') . ' €</strong></td>
                </tr>
                <tr class="total-row" style="background-color: #2563eb; color: white;">
                    <td colspan="4" class="text-right"><strong>Total TTC</strong></td>
                    <td class="text-right"><strong>' . number_format($totalTTC, 2, ',', ' ') . ' €</strong></td>
                </tr>
            </tfoot>
        </table>';

        if ($devis['notes']) {
            $html .= '<h2>Conditions</h2><p>' . nl2br(htmlspecialchars($devis['notes'])) . '</p>';
        }

        return $html;
    }

    /**
     * Génère le HTML d'une facture
     */
    private function renderFactureHtml($facture, $client, $items) {
        $html = '
        <style>
            body { font-family: Helvetica, Arial, sans-serif; font-size: 10pt; }
            h1 { color: #10b981; font-size: 24pt; }
            h2 { color: #059669; font-size: 14pt; margin-top: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background-color: #10b981; color: white; padding: 8px; text-align: left; }
            td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
            .text-right { text-align: right; }
            .total-row { background-color: #f0fdf4; font-weight: bold; }
            .header { margin-bottom: 30px; }
            .info-box { background: #f0fdf4; padding: 10px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #10b981; }
            .payment-status { padding: 10px; margin: 20px 0; border-radius: 5px; }
            .paid { background-color: #d1fae5; border-left: 4px solid #10b981; }
            .unpaid { background-color: #fef3c7; border-left: 4px solid #f59e0b; }
        </style>

        <div class="header">
            <h1>' . htmlspecialchars($this->company['name']) . '</h1>
            <p>' . htmlspecialchars($this->company['address']) . '<br>
            ' . htmlspecialchars($this->company['postal_code']) . ' ' . htmlspecialchars($this->company['city']) . '<br>
            Tél: ' . htmlspecialchars($this->company['phone']) . ' | Email: ' . htmlspecialchars($this->company['email']) . '</p>
            ' . ($this->company['siret'] ? '<p>SIRET: ' . htmlspecialchars($this->company['siret']) . '</p>' : '') . '
        </div>

        <h2>FACTURE N° ' . htmlspecialchars($facture['number']) . '</h2>

        <div class="info-box">
            <table style="border: none;">
                <tr>
                    <td style="border: none; width: 50%;"><strong>Client:</strong><br>
                        ' . htmlspecialchars($client['name']) . '<br>
                        ' . htmlspecialchars($client['address']) . '<br>
                        ' . htmlspecialchars($client['postal_code']) . ' ' . htmlspecialchars($client['city']) . '
                    </td>
                    <td style="border: none; width: 50%;">
                        <strong>Date:</strong> ' . date('d/m/Y', strtotime($facture['date'])) . '<br>
                        <strong>Échéance:</strong> ' . date('d/m/Y', strtotime($facture['due_date'])) . '<br>
                        <strong>Statut:</strong> ' . ucfirst($facture['status']) . '
                    </td>
                </tr>
            </table>
        </div>';

        // Statut de paiement
        $remainingAmount = $facture['total'] - $facture['paid_amount'];
        if ($remainingAmount <= 0) {
            $html .= '<div class="payment-status paid"><strong>✓ FACTURE RÉGLÉE</strong></div>';
        } else {
            $html .= '<div class="payment-status unpaid"><strong>⚠ RESTE À PAYER: ' . number_format($remainingAmount, 2, ',', ' ') . ' €</strong></div>';
        }

        $html .= '<h2>Détail des prestations</h2>

        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Description</th>
                    <th class="text-right">Quantité</th>
                    <th class="text-right">Prix unitaire</th>
                    <th class="text-right">TVA</th>
                    <th class="text-right">Total HT</th>
                </tr>
            </thead>
            <tbody>';

        $totalHT = 0;
        $totalTVA = 0;

        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $itemTVA = $itemTotal * ($item['tva_rate'] / 100);
            $totalHT += $itemTotal;
            $totalTVA += $itemTVA;

            $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['description']) . '</td>
                    <td class="text-right">' . number_format($item['quantity'], 2, ',', ' ') . '</td>
                    <td class="text-right">' . number_format($item['unit_price'], 2, ',', ' ') . ' €</td>
                    <td class="text-right">' . number_format($item['tva_rate'], 1, ',', '') . '%</td>
                    <td class="text-right">' . number_format($itemTotal, 2, ',', ' ') . ' €</td>
                </tr>';
        }

        $totalTTC = $totalHT + $totalTVA;

        $html .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>Total HT</strong></td>
                    <td class="text-right"><strong>' . number_format($totalHT, 2, ',', ' ') . ' €</strong></td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>TVA</strong></td>
                    <td class="text-right"><strong>' . number_format($totalTVA, 2, ',', ' ') . ' €</strong></td>
                </tr>
                <tr class="total-row" style="background-color: #10b981; color: white;">
                    <td colspan="4" class="text-right"><strong>Total TTC</strong></td>
                    <td class="text-right"><strong>' . number_format($totalTTC, 2, ',', ' ') . ' €</strong></td>
                </tr>';

        if ($facture['paid_amount'] > 0) {
            $html .= '
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>Montant payé</strong></td>
                    <td class="text-right"><strong>' . number_format($facture['paid_amount'], 2, ',', ' ') . ' €</strong></td>
                </tr>
                <tr class="total-row" style="background-color: #f59e0b; color: white;">
                    <td colspan="4" class="text-right"><strong>Reste à payer</strong></td>
                    <td class="text-right"><strong>' . number_format($remainingAmount, 2, ',', ' ') . ' €</strong></td>
                </tr>';
        }

        $html .= '
            </tfoot>
        </table>';

        if ($facture['notes']) {
            $html .= '<h2>Notes</h2><p>' . nl2br(htmlspecialchars($facture['notes'])) . '</p>';
        }

        return $html;
    }

    /**
     * Génère un PDF à partir de HTML (fallback si TCPDF n'est pas installé)
     * Utilise wkhtmltopdf si disponible, sinon retourne le HTML
     */
    private function generateHtmlPdf($html, $filename) {
        // Vérifier si wkhtmltopdf est installé
        $wkhtmltopdf = exec('which wkhtmltopdf');

        if ($wkhtmltopdf) {
            $tempHtml = sys_get_temp_dir() . '/' . uniqid() . '.html';
            $tempPdf = sys_get_temp_dir() . '/' . $filename;

            file_put_contents($tempHtml, $html);

            exec("wkhtmltopdf $tempHtml $tempPdf");

            if (file_exists($tempPdf)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                readfile($tempPdf);

                unlink($tempHtml);
                unlink($tempPdf);
                exit;
            }
        }

        // Fallback: retourner le HTML
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . str_replace('.pdf', '.html', $filename) . '"');
        echo $html;
        exit;
    }
}
