<?php

/* Template Name: Admin | Teacher Invoice */

// page title
global $pageTitle;
$pageTitle = "Facture enseignant";

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if the id is present in the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

global $wpdb;

$teacher_payments_table = $wpdb->prefix . 'teacher_payments'; 

// Fetch the details of the invoice details from payments table
if ($id > 0) {
    $payment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_payments_table WHERE id = %d", $id));
} else {
    echo "Invalid Payment ID.";
}


// Fetch the details of the teacher using the ID
$teacher_table = $wpdb->prefix . 'teachers';
$teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $payment->teacher_id));

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>

    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: "Poppins";
    }

    html {
        font-size: 14px;
    }

    body {
        background: #f7f7f7;
    }

    .button-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 1rem;
    }

    .button {
        font-family: "Poppins";
        font-size: 1rem;
        font-weight: 500;
        padding: .4rem 1rem;
        border: 1px solid;
        border-radius: .3rem;
        cursor: pointer;
        transition: ease .3s;
    }

    .button.print {
        background-color: #d9e8f0;
        border-color: #0d71a3;
        color: #0d71a3;
    }

    .button.print:hover {
        background-color: #0d71a3;
        color: #fff;
    }

    .button.close {
        background-color: #ffe2e6;
        border-color: #fa657e;
        color: #fa657e;
    }

    .button.close:hover {
        background-color: #fa657e;
        color: #fff;
    }

    .print-area {
        background-color: #fff;
        width: 100%;
        max-width: 1080px;
        margin: 0 auto;
        border-radius: .7rem;
        padding: 1rem 2rem;
        padding-bottom: 2rem;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        border-radius: .3rem;
    }

    .table th,
    .table td {
        font-size: 1rem;
        border: 1px solid #ddd;
        padding: .5rem 1rem;
        text-align: left;
        vertical-align: middle;
    }

    .table th {
        font-weight: 500;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }

    .header-section .brand-title,
    .header-section .invoice-heading {
        font-size: 2rem;
        font-weight: 600;
    }

    .address-section {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }

    .address-section .customer-name {
        font-size: 1.2rem;
        font-weight: 600;
    }

    @media print {
        html {
            font-size: 12px;
        }

        body {
            background: #fff;
        }

        .button-container {
            display: none;
        }

        .print-area {
            padding: 0;
        }
    }
    </style>
</head>

<body>
    <div class="button-container">
        <button class="button print" onclick="window.print()">Imprimer</button>
        <button class="button close" onclick="window.history.back()">Retourner</button>
    </div>
    <div class="print-area" id='printArea'>
        <div id="invoice">
            <!-- header -->
            <div class="header-section">
                <div class="col">
                    <h2 class="brand-title"><?php echo get_bloginfo('name'); ?></h2>
                </div>
                <div class="col">
                    <h2 class="invoice-heading">Facture</h2>
                    <table class="table invoice-info-table">
                        <tr>
                            <th>Numéro de facture</th>
                            <td><?= esc_html($payment->invoice_number) ?></td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td><?= esc_html(date('M d, Y', strtotime($payment->created_at))) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- address -->
            <div class="address-section">
                <table class="table bill-to-table">
                    <tr>
                        <th colspan="2">Détails de l'enseignant</th>
                    </tr>
                    <tr>
                        <th>Nom</th>
                        <td>
                            <h3 class="customer-name">
                                <?= esc_html($teacher->first_name) . ' ' . esc_html($teacher->last_name) ?>
                            </h3>
                        </td>
                    </tr>
                    <tr>
                        <th>Téléphone</th>
                        <td>
                            <?= esc_html($teacher->phone) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>E-mail</th>
                        <td>
                            <?= esc_html($teacher->email) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Adresse</th>
                        <td>
                            <?= esc_html($teacher->address) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Ville</th>
                        <td>
                            <?= esc_html($teacher->city) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Code Postal</th>
                        <td>
                            <?= esc_html($teacher->postal_code) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Pays</th>
                        <td>
                            <?= esc_html($teacher->country) ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- items -->
            <div class="items-section">
                <table class="table items-table">
                    <tr>
                        <th colspan="2">Détails de paiement</th>
                    </tr>
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
        </div>
    </div>
</body>

</html>