<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFService {
    public static function generate($html, $filename = 'document.pdf', $paper = 'A4', $orientation = 'portrait') {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    public static function generateLeaseAgreement($tenantName, $propertyTitle, $rent, $startDate, $endDate) {
        $html = "
        <html><head><style>
            body { font-family: 'DejaVu Sans', sans-serif; color: #1c1b18; padding: 40px; font-size: 12px; line-height: 1.6; }
            h1 { font-size: 18px; letter-spacing: 4px; text-transform: uppercase; color: #3a322c; margin-bottom: 4px; }
            hr { border: none; border-top: 1px solid #d6d2c8; margin: 16px 0; }
            h2 { font-size: 14px; color: #3a322c; margin: 24px 0 8px; }
            table { width: 100%; border-collapse: collapse; margin: 12px 0; }
            td, th { padding: 8px 12px; text-align: left; border-bottom: 1px solid #e1ddd4; }
            th { background: #f2efe9; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
            .footer { margin-top: 40px; font-size: 10px; color: #9a9086; text-align: center; }
            .signature { margin-top: 32px; }
            .signature div { display: inline-block; width: 200px; border-top: 1px solid #1c1b18; padding-top: 6px; margin-right: 40px; font-size: 11px; }
        </style></head><body>
            <h1>AURA ESTATES</h1>
            <p style='font-size:10px; color:#5c5349'>Lease Agreement</p>
            <hr>
            <h2>Parties</h2>
            <p><strong>Lessor:</strong> Aura Estates Management LLC<br>
            <strong>Lessee:</strong> $tenantName</p>
            <h2>Property</h2>
            <p>$propertyTitle</p>
            <h2>Terms</h2>
            <table>
                <tr><th>Monthly Rent</th><td>$$rent</td></tr>
                <tr><th>Start Date</th><td>$startDate</td></tr>
                <tr><th>End Date</th><td>$endDate</td></tr>
                <tr><th>Security Deposit</th><td>$" . number_format($rent * 2, 2) . "</td></tr>
            </table>
            <h2>Terms & Conditions</h2>
            <p>1. Rent is due on the 1st of each month. Late payments after the 5th incur a 5% fee.<br>
            2. Lessee shall maintain the property in good condition.<br>
            3. No subletting without written consent from Lessor.<br>
            4. 60 days notice required for lease termination.<br>
            5. Pets subject to $500 deposit and monthly $50 fee.</p>
            <div class='signature'>
                <div>Lessor Signature</div>
                <div>Lessee Signature</div>
                <div>Date</div>
            </div>
            <div class='footer'>Aura Estates · Generated on " . date('M j, Y') . "</div>
        </body></html>";
        return self::generate($html, "lease-$tenantName.pdf");
    }

    public static function generateInvoice($to, $items, $total, $invoiceNo) {
        $rows = '';
        foreach ($items as $item) {
            $rows .= "<tr><td>{$item['description']}</td><td>\${$item['amount']}</td></tr>";
        }
        $html = "
        <html><head><style>
            body { font-family: 'DejaVu Sans', sans-serif; color: #1c1b18; padding: 40px; font-size: 12px; }
            h1 { font-size: 18px; letter-spacing: 4px; text-transform: uppercase; color: #3a322c; }
            .invoice-no { font-size: 10px; color: #5c5349; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #f2efe9; padding: 8px 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
            td { padding: 8px 12px; border-bottom: 1px solid #e1ddd4; }
            .total { font-weight: bold; font-size: 14px; }
            .footer { margin-top: 40px; font-size: 10px; color: #9a9086; text-align: center; }
        </style></head><body>
            <h1>AURA ESTATES</h1>
            <p class='invoice-no'>Invoice #$invoiceNo · " . date('M j, Y') . "</p>
            <hr>
            <p><strong>Bill To:</strong> $to</p>
            <table><tr><th>Description</th><th>Amount</th></tr>$rows</table>
            <p class='total'>Total: \$$total</p>
            <p style='font-size:11px; color:#5c5349'>Payment due within 15 days.</p>
            <div class='footer'>Aura Estates · Thank you for your business.</div>
        </body></html>";
        return self::generate($html, "invoice-$invoiceNo.pdf");
    }
}
