<?php

/* Template Name: Teacher | Bank Details */

// Start the session
if (!session_id()) {
    session_start();
}

// Page title
global $pageTitle;
$pageTitle = 'Informations Bancaires';

require_once(get_template_directory() . '/teacher/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// current user ID
$teacher_id = get_current_user_id();

global $wpdb;
$teacher_bankinfo_table = $wpdb->prefix . 'teacher_bank_details';

// Fetch teacher data
$bankinfo = $teacher_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_bankinfo_table WHERE teacher_id = %d", $teacher_id)) : null;

ob_start();

$error_message = ''; // Initialize error message variable
$success_message = ''; // Initialize success message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bank_details'])) {

    $error_message = '';
    $success_message = '';

    // Sanitize user inputs
    $bank_name = sanitize_text_field($_POST['bank_name']);
    $account_number = sanitize_text_field($_POST['account_number']);
    $account_holder = sanitize_text_field($_POST['account_holder']);
    $account_type = sanitize_text_field($_POST['account_type']);
    $swift_code = sanitize_text_field($_POST['swift_code']);
    $bank_address = sanitize_textarea_field($_POST['bank_address']);

    // Check if a record already exists for the teacher
    $existing_record = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $teacher_bankinfo_table WHERE teacher_id = %d",
        $teacher_id
    ));

    if ($existing_record) {
        // Update existing record
        $updated = $wpdb->update(
            $teacher_bankinfo_table,
            [
                'bank_name'      => $bank_name,
                'account_number' => $account_number,
                'account_holder' => $account_holder,
                'account_type'   => $account_type,
                'swift_code'     => $swift_code,
                'bank_address'   => $bank_address,
                'updated_at'     => current_time('mysql')
            ],
            [ 'teacher_id' => $teacher_id ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ],
            [ '%d' ]
        );

        if ($updated === false) {
            $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
        } else {
            $success_message = 'Bank details updated successfully.';
        }
    } else {
        // Insert new record
        $inserted = $wpdb->insert(
            $teacher_bank_details,
            [
                'teacher_id'     => $teacher_id,
                'bank_name'      => $bank_name,
                'account_number' => $account_number,
                'account_holder' => $account_holder,
                'account_type'   => $account_type,
                'swift_code'     => $swift_code,
                'bank_address'   => $bank_address,
                'created_at'     => current_time('mysql'),
                'updated_at'     => current_time('mysql')
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        if ($inserted === false) {
            $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
        } else {
            $success_message = 'Les coordonnées bancaires ont été mises à jour avec succès.';
        }
    }

    if ($success_message) {
        wp_redirect(home_url('/teacher/revenues/bank-details/'));
        exit;
    }
}

ob_end_clean();

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/teacher/templates/sidebar.php'); ?>
    </div>
    <div id="teacherBankDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Informations Bancaires</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/teacher/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/teacher/revenues'); ?>" class="breadcrumb-link">Revenus</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Informations Bancaires</span>
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

            </div>
        </div>

        <div class="content-section">
            <form action="" class="edit-form" method="post" enctype="multipart/form-data">
                <?php if ($error_message): ?>
                <div class="form-error">
                    <p><?php echo esc_html($error_message); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                <div class="form-success">
                    <p><?php echo esc_html($success_message); ?></p>
                </div>
                <?php endif; ?>

                <!-- Update Bank Details -->
                <section class="section col personal-information">
                    <h3 class="section-heading">Mettre à jour les détails bancaires</h3>

                    <div class="row">
                        <div class="col">
                            <label for="bank_name">Nom de la Banque <span class="required">*</span></label>
                            <input type="text" id="bank_name" name="bank_name" placeholder="Nom de la Banque"
                                value="<?php echo esc_attr($bankinfo->bank_name); ?>" required>
                        </div>
                        <div class="col">
                            <label for="account_number">Numéro de Compte <span class="required">*</span></label>
                            <input type="text" id="account_number" name="account_number" placeholder="Numéro de Compte"
                                value="<?php echo esc_attr($bankinfo->account_number); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="account_holder">Titulaire du Compte <span class="required">*</span></label>
                            <input type="text" id="account_holder" name="account_holder"
                                placeholder="Titulaire du Compte"
                                value="<?php echo esc_attr($bankinfo->account_holder); ?>" required>
                        </div>
                        <div class="col">
                            <label for="account_type">Type de Compte <span class="required">*</span></label>
                            <input type="text" id="account_type" name="account_type" placeholder="Type de Compte"
                                value="<?php echo esc_attr($bankinfo->account_type); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="swift_code">Code SWIFT <span class="required">*</span></label>
                            <input type="text" id="swift_code" name="swift_code" placeholder="Code SWIFT"
                                value="<?php echo esc_attr($bankinfo->swift_code); ?>" required>
                        </div>
                        <div class="col">
                            <label for="bank_address">Adresse de la Banque <span class="required">*</span></label>
                            <input id="bank_address" name="bank_address" placeholder="Adresse de la Banque"
                                value="<?php echo esc_textarea($bankinfo->bank_address); ?>" required>
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="submit" class="submit-button" name="update_bank_details">Mettre à jour</button>
                    </div>
                </section>
            </form>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>