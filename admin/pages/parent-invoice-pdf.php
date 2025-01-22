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

// Fetch payment and parent details
$payment_table = $wpdb->prefix . 'payments';
$parent_table = $wpdb->prefix . 'parents';

$payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $payment_table WHERE id = %d", $id));
if (!$payment) {
    die('Payment not found');
}

$parent = $wpdb->get_row($wpdb->prepare("SELECT * FROM $parent_table WHERE id = %d", $payment->user_id));
if (!$parent) {
    die('Parent not found');
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
        <h1 class="header">Invoice</h1>
        <p><strong>Invoice Number:</strong> <?= esc_html($payment->invoice_number); ?></p>
        <p><strong>Date:</strong> <?= esc_html(date('M d, Y', strtotime($payment->created_at))); ?></p>

        <h3>Détails des parents</h3>
        <p><strong>Name:</strong> <?= esc_html($parent->first_name) . ' ' . esc_html($parent->last_name); ?></p>
        <p><strong>Email:</strong> <?= esc_html($parent->email); ?></p>
        <p><strong>Phone:</strong> <?= esc_html($parent->phone); ?></p>
        <p><strong>Address:</strong> <?= esc_html($parent->address); ?></p>
        <p><strong>City:</strong> <?= esc_html($parent->city); ?></p>
        <p><strong>Zip Code:</strong> <?= esc_html($parent->zipcode); ?></p>
        <p><strong>Country:</strong> <?= esc_html($parent->country); ?></p>

        <h3>Détails de paiement</h3>
        <table class="table">
            <tr>
                <th>Credit</th>
                <td><?= esc_html($payment->credit); ?></td>
            </tr>
            <tr>
                <th>Total Price</th>
                <td><?= esc_html($payment->amount); ?></td>
            </tr>
            <tr>
                <th>Currency</th>
                <td><?= esc_html($payment->currency); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?= esc_html($payment->status); ?></td>
            </tr>
            <tr>
                <th>Payment Method</th>
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