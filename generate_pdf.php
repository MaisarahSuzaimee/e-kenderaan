<?php
require __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;

// Capture HTML output
ob_start();
include 'report_template.php';
$html = ob_get_clean();

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Download
$dompdf->stream("laporan.pdf", ["Attachment" => true]);