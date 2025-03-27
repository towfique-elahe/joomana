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

// Query to get total dues
$total_dues = (int) $wpdb->get_var($wpdb->prepare( 
    "SELECT SUM(amount) FROM {$wpdb->prefix}teacher_payments 
     WHERE teacher_id = %d AND status = %s",
    $teacher->id, 'due'
));

ob_start();

$error_message = ''; // Initialize error message variable
$success_message = ''; // Initialize success message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_payment'])) {
    global $wpdb;

    $amount_paid = floatval($_POST['amount']);
    $remaining = $amount_paid;

    // Get all due payments for this teacher, ordered by oldest
    $due_payments = $wpdb->get_results($wpdb->prepare("
        SELECT id, amount 
        FROM {$payments_table}
        WHERE teacher_id = %d AND status = %s
        ORDER BY created_at ASC
    ", $teacher->id, 'due'));

    $wpdb->query('START TRANSACTION'); // optional, adds safety

    foreach ($due_payments as $payment) {
        if ($remaining >= floatval($payment->amount)) {
            // Full payment covered – mark as completed
            $wpdb->update(
                $payments_table,
                ['status' => 'completed'],
                ['id' => $payment->id],
                ['%s'],
                ['%d']
            );

            $remaining -= floatval($payment->amount);
        } else {
            // Not enough remaining to cover this payment – stop here
            break;
        }
    }

    $wpdb->query('COMMIT');

    $success_message = 'Paiement effectué avec succès.';
    wp_redirect(home_url('/admin/teacher-payments/'));
    exit;
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
                                    <span id="totalDue"><?= $total_dues ?></span> €
                                </td>
                            </tr>
                            <tr>
                                <th>Dépôt</th>
                                <td>
                                    <input type="number" name="amount" id="amount"
                                        min="<?= ($teacher->country === 'France') ? 26 : 10 ?>" max="<?= $total_dues ?>"
                                        step="<?= ($teacher->country === 'France') ? 26 : 10 ?>" required> €
                                </td>
                            </tr>
                            <tr>
                                <th>Restant dû</th>
                                <td>
                                    <span id="remainingDue"><?= $total_dues ?></span> €
                                </td>
                            </tr>
                        </table>
                        <div class="action-buttons">
                            <button type="submit" class="submit-button" name="make_payment">Effectuer le dépôt</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalDue = parseFloat(document.getElementById('totalDue').textContent);
    const depositInput = document.getElementById('deposit');
    const remainingSpan = document.getElementById('remainingDue');

    depositInput.addEventListener('input', function() {
        let deposit = parseFloat(depositInput.value) || 0;
        let remaining = (totalDue - deposit).toFixed(2);

        if (remaining < 0) {
            remaining = totalDue.toFixed(2);
            depositInput.value = "";
        }

        remainingSpan.textContent = remaining;
    });
});
</script>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>