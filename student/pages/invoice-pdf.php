<?php
// Include WordPress
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Include Dompdf
require_once get_template_directory() . '/vendor/autoload.php'; // Use correct path if installed manually.

use Dompdf\Dompdf;

// Check if ID is passed
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    die('Invalid Payment ID');
}

global $wpdb;

// Fetch payment and student details
$payment_table = $wpdb->prefix . 'payments';
$student_table = $wpdb->prefix . 'students';

$payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $payment_table WHERE id = %d", $id));
if (!$payment) {
    die('Payment not found');
}

$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $payment->user_id));
if (!$student) {
    die('Student not found');
}

// Generate HTML for the invoice
ob_start();
?>
<!DOCTYPE html>
<html>

<head>
    <style>
    body {
        font-family: "Poppins", sans-serif;
    }

    .invoice {
        padding: 20px;
    }

    .header {
        text-align: center;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .table th,
    .table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .table th {
        background-color: #f2f2f2;
    }
    </style>
</head>

<body>
    <div class="invoice">
        <h1 class="header">Facture</h1>
        <p><strong>Numéro de facture:</strong> <?= esc_html($payment->invoice_number); ?></p>
        <p><strong>Date:</strong> <?= esc_html(date('M d, Y', strtotime($payment->created_at))); ?></p>

        <h3>Détails sur l'étudiant</h3>
        <p><strong>Nom:</strong> <?= esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?></p>
        <p><strong>Genre:</strong> <?= esc_html($student->gender); ?></p>
        <p><strong>Date de naissance:</strong> <?= esc_html(date('d M, Y', strtotime($student->date_of_birth))); ?></p>

        <h3>Détails de paiement</h3>
        <table class="table">
            <tr>
                <th>Crédit</th>
                <td><?= esc_html($payment->credit); ?></td>
            </tr>
            <tr>
                <th>Prix ​​total</th>
                <td><?= esc_html($payment->amount); ?></td>
            </tr>
            <tr>
                <th>Devise</th>
                <td><?= esc_html($payment->currency); ?></td>
            </tr>
            <tr>
                <th>Statut</th>
                <td><?= esc_html($payment->status); ?></td>
            </tr>
            <tr>
                <th>Mode de paiement</th>
                <td><?= esc_html($payment->payment_method); ?></td>
            </tr>
        </table>
    </div>
</body>

</html>
<?php
$html = ob_get_clean();

// Instantiate Dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output PDF
$dompdf->stream('invoice-' . $id . '.pdf', ['Attachment' => 0]); // Set Attachment to 1 for download.
exit;