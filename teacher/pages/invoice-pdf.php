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

// Fetch payment and teacher details
$payment_table = $wpdb->prefix . 'teacher_payments';
$teacher_table = $wpdb->prefix . 'teachers';

$payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $payment_table WHERE id = %d", $id));
if (!$payment) {
    die('Payment not found');
}

$teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $payment->teacher_id));
if (!$teacher) {
    die('Teacher not found');
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

        <h3>Détails de l'enseignant</h3>
        <p><strong>Nom:</strong> <?= esc_html($teacher->first_name) . ' ' . esc_html($teacher->last_name); ?></p>
        <p><strong>Téléphone:</strong> <?= esc_html($teacher->phone); ?></p>
        <p><strong>E-mail:</strong> <?= esc_html($teacher->email); ?></p>
        <p><strong>Adresse:</strong> <?= esc_html($teacher->address); ?></p>
        <p><strong>Ville:</strong> <?= esc_html($teacher->city); ?></p>
        <p><strong>Code Postal:</strong> <?= esc_html($teacher->postal_code); ?></p>
        <p><strong>Pays:</strong> <?= esc_html($teacher->country); ?></p>

        <h3>Détails de paiement</h3>
        <table class="table">
            <tr>
                <th>Paiement total</th>
                <td><?php echo intval($payment->amount) == $payment->amount ? intval($payment->amount) : number_format($payment->amount, 2); ?>
                </td>
            </tr>
            <tr>
                <th>Devise</th>
                <td><?= esc_html($payment->currency) ?></td>
            </tr>
            <tr>
                <th>Statut</th>
                <td><?php echo esc_html($payment->status === 'in progress' ? 'En attente' : ($payment->status === 'due' ? 'À payer' : ($payment->status === 'completed' ? 'Terminé' : $payment->status))); ?>
                </td>
            </tr>
            <tr>
                <th>Mode de paiement</th>
                <td><?= esc_html($payment->payment_method) ?></td>
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