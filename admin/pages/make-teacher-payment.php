<?php

/* Template Name: Admin | Make Teacher Payment */

// Start the session
if (!session_id()) {
    session_start();
}

// Page title
global $pageTitle;
$pageTitle = 'Effectuer Le Paiement';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$teacher_table = $wpdb->prefix . 'teachers';
$teacher_bankinfo_table = $wpdb->prefix . 'teacher_bank_details';
$payments_table = $wpdb->prefix . 'teacher_payments';

// Handle form submission to set the teacher ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher-select']) && !empty($_POST['teacher-select'])) {
    $_SESSION['selected_teacher_id'] = intval($_POST['teacher-select']);
    // Redirect to the same page to avoid resubmission
    wp_redirect($_SERVER['REQUEST_URI']);
    exit;
}

// Retrieve selected teacher ID from session
$id = isset($_SESSION['selected_teacher_id']) ? intval($_SESSION['selected_teacher_id']) : 0;

// Fetch teacher data
$teacher = $id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $id)) : null;
$bankinfo = $id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_bankinfo_table WHERE teacher_id = %d", $id)) : null;

ob_start();

$error_message = ''; // Initialize error message variable
$success_message = ''; // Initialize success message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_payment'])) {
    global $wpdb;

    $error_message = '';
    $success_message = '';

    do {
        // Generate a more robust unique invoice number
        $invoice_number = 'JMI-' . uniqid() . '-' . bin2hex(random_bytes(4));
        // Ensure the invoice number is unique in the payments table
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $payments_table WHERE invoice_number = %s", $invoice_number));
    } while ($exists > 0);

    // Sanitize user inputs
    $user_id = sanitize_text_field($_POST['user_id']);
    $deposit = sanitize_text_field($_POST['deposit']);
    $currency = sanitize_textarea_field($_POST['currency']);
    $payment_method = sanitize_text_field($_POST['payment_method']);
    $status = sanitize_text_field($_POST['status']);

    if (empty($error_message)) {
        // Insert payment into the database
        $inserted = $wpdb->insert(
            $payments_table,
            [
                'invoice_number'        => $invoice_number,
                'user_id'               => $user_id,
                'deposit'               => $deposit,
                'currency'              => $currency,
                'payment_method'        => $payment_method,
                'status'                => $status,
            ],
            [
                '%s', '%d', '%f', '%s', '%s', '%s',
            ]
        );

        if ($inserted === false) {
            $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
        } else {
            $success_message = 'Le cours a été ajouté avec succès.';
            wp_redirect(home_url('/admin/teacher-payments/'));
            exit;
        }
    }
}

ob_end_clean();

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminMakeTeacherPayment" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Effectuer Le Paiement</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Effectuer Le Paiement</span>
            </div>
        </div>

        <div class="content-section">
            <h3 class="section-heading">Affecter des enseignants</h3>
            <div class="search-teacher">
                <?php
                    // Query the custom 'teachers' table
                    $teachers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}teachers");

                    // Check if teachers are available
                    if ($teachers) {
                        echo '<form method="post">';
                        echo '<select id="teacher-select" name="teacher-select" class="form-control" onchange="this.form.submit()">';
                        echo '<option value="" disabled selected>Choisir un enseignant</option>'; // Default placeholder option

                        foreach ($teachers as $teacher_option) {
                            $teacher_name = esc_html($teacher_option->first_name . ' ' . $teacher_option->last_name);
                            $teacher_id = intval($teacher_option->id);
                            $selected = ($id === $teacher_id) ? 'selected="selected"' : '';

                            echo '<option value="' . $teacher_id . '" ' . $selected . '>' . $teacher_name . ' [id: ' . $teacher_id . ']' . '</option>';
                        }

                        echo '</select>';
                        echo '</form>';
                    } else {
                        echo '<p>Aucun enseignant trouvé.</p>';
                    }
                    ?>
            </div>
        </div>

        <div class="row content-section list">
            <div class="col">
                <!-- bank details -->
                <div class="bank-details">
                    <h3 class="section-heading">Informations bancaires</h3>
                    <table class="table">
                        <tr>
                            <th>Nom de la banque</th>
                            <td>
                                <?= !empty($bankinfo->bank_name) ? $bankinfo->bank_name : '---' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Numéro de compte</th>
                            <td>
                                <?= !empty($bankinfo->account_number) ? $bankinfo->account_number : '---' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Titulaire du compte</th>
                            <td>
                                <?= !empty($bankinfo->account_holder) ? $bankinfo->account_holder : '---' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Type de compte</th>
                            <td>
                                <?= !empty($bankinfo->account_type) ? $bankinfo->account_type : '---' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Code BIC/SWIFT</th>
                            <td>
                                <?= !empty($bankinfo->swift_code) ? $bankinfo->swift_code : '---' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Adresse de la banque</th>
                            <td>
                                <?= !empty($bankinfo->bank_address) ? $bankinfo->bank_address : '---' ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- payment details -->
                <div class="payment-details">
                    <h3 class="section-heading">Informations de paiement</h3>
                    <form action="" class="add-form" method="post" enctype="multipart/form-data">
                        <?php if ($error_message): ?>
                        <div class="form-error">
                            <p>
                                <?php echo esc_html($error_message); ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <?php if ($success_message): ?>
                        <div class="form-success">
                            <p>
                                <?php echo esc_html($success_message); ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <table class="table">
                            <tr>
                                <th>Total dû</th>
                                <td>
                                    <?= !empty($teacher->due) ? $teacher->due : '---' ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Dépôt</th>
                                <td>
                                    <input type="number" name="deposit" id="deposit" min="5" max="" required>
                                </td>
                            </tr>
                            <tr>
                                <th>Restant dû</th>
                                <td>
                                    <?= !empty($teacher->due) ? $teacher->due : '---' ?>
                                </td>
                            </tr>
                        </table>
                        <input type="hidden" name="invoice_number" value="<?= $invoice_number ?>">
                        <input type="hidden" name="user_id" value="<?= $teacher->id ?>">
                        <input type="hidden" name="currency" value="EUR">
                        <input type="hidden" name="payment_method" value="BANK">
                        <input type="hidden" name="status" value="Complété">
                        <div class="action-buttons">
                            <button type="submit" class="submit-button" name="make_payment">Effectuer le dépôt</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>