<?php

/* Template Name: Admin | Teacher Details */

// page title
global $pageTitle;
$pageTitle = "Détails De L'enseignant";

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$error_message = ''; // Initialize error message variable
$success_message = ''; // Initialize success message variable

// Check if the id is present in the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the details of the user from wp users table
if ($id > 0) {
    $wp_user = get_user_by('ID', $id);
} else {
    echo "Invalid user ID.";
}

global $wpdb;
$teacher_table = $wpdb->prefix . 'teachers';
$teacher_bankinfo_table = $wpdb->prefix . 'teacher_bank_details ';

// Fetch the details of the teacher using the ID
$teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $id));
$bankinfo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_bankinfo_table WHERE teacher_id = %d", $id));

$payments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}teacher_payments WHERE teacher_id = $teacher->id");

// Query to get total dues
$total_dues = (int) $wpdb->get_var($wpdb->prepare( 
    "SELECT SUM(amount) FROM {$wpdb->prefix}teacher_payments 
     WHERE status = %s",
    'due'
));

// Query to get total deposits
$total_deposits = (int) $wpdb->get_var($wpdb->prepare( 
    "SELECT SUM(amount) FROM {$wpdb->prefix}teacher_payments 
     WHERE status = %s",
    'completed'
));

if (!$teacher) {
    // Handle case when the teacher does not exist
    wp_die("L'enseignant demandé n'a pas pu être trouvé.");
}

    // Handle form submission for updating the topic
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_teacher_status'])) {
        // Sanitize user input
        $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if (!empty($new_status)) {
            if (!$error_message) {
                $data = array(
                    'status' => $new_status,
                );

                // Update the topic in the database
                $updated = $wpdb->update(
                    $teacher_table,
                    $data, // Column => Value
                    array('id' => $id), // Where condition
                    array('%s'), // Data type for each value (status)
                    array('%d') // Data type for where condition
                );

                if ($updated === false) {
                    // Handle potential database errors
                    $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
                } else {
                    // Set success message
                    $success_message = 'Le statut a été mis à jour avec succès.';

                    // Redirect to prevent duplicate submission
                    wp_redirect(home_url('/admin/teacher-management/teacher-details/?id='.$id));
                    exit;
                }
            }
        } else {
            $error_message = 'Veuillez choisir un statut valide.';
        }
    }

    $teacher_id = $teacher->id;

    global $wpdb;
    $sessions_table = $wpdb->prefix . 'course_sessions';

    $sessions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $sessions_table 
             WHERE teacher_id = %d",
            $teacher_id
        )
    );

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminTeacherDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails De L'enseignant</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/teacher-management/'); ?>" class="breadcrumb-link">Gestion
                    Enseignants</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Détails De L'enseignant</span>
            </div>
        </div>

        <div class="row content-section">
            <div class="col left-content">

                <!-- personal details -->
                <div class="section user-profile">
                    <div class="profile-top">
                        <div class="col">
                            <h3 class="profile-name">
                                <?php echo esc_html($teacher->first_name) . " " . esc_html($teacher->last_name); ?>
                            </h3>
                            <p class="profile-username">
                                <?php echo esc_html($wp_user->user_login); ?>
                            </p>
                            <p class="status <?php echo strtolower(str_replace(' ', '-', $teacher->status)); ?>">
                                <?php echo esc_html($teacher->status); ?>
                            </p>
                        </div>
                        <img src="<?php echo !empty($teacher->image) ? esc_url($teacher->image) : esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png'); ?>"
                            alt="User Image" class="profile-image">
                    </div>
                    <div class="profile-details">
                        <div class="row detail-row">
                            <span class="col detail-label">Email:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($wp_user->user_email); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Téléphone:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($teacher->phone); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Adresse:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($teacher->address); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">ville/quartier:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($teacher->city); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Pays/Région:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($teacher->country) . " - " . esc_html($teacher->postal_code); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Total à payer:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($total_dues); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Total terminé:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($total_deposits); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- qualifications -->
                <div class="section">
                    <h3 class="section-heading">Formation et qualifications</h3>
                    <div class="profile-details">
                        <div class="row detail-row">
                            <span class="col detail-label">Degré:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($teacher->degree); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Institution:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($teacher->institute); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Année:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($teacher->graduation_year); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col right-content">
                <div class="row">
                    <div class="col">
                        <!-- interested subjects -->
                        <div class="section">
                            <h3 class="section-heading">Sujets d'intérêt</h3>
                            <?php 
                            // Split the string into an array using commas as the delimiter
                            $subjects = explode(',', $teacher->subjects_of_interest);
            
                            if (!empty($subjects)) {
                                foreach ($subjects as $subject) {
                            ?>
                            <div class="detail-row">
                                <span class="row detail-value">
                                    <i class="far fa-check-circle"></i>
                                    <?php echo esc_html(trim($subject)); ?>
                                </span>
                            </div>
                            <?php
                                }
                            } else {
                                echo 'Aucun sujet trouvé.';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="col">
                        <!-- update status -->
                        <form action="" class="edit-form" method="post" enctype="multipart/form-data">
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

                            <section class="section col">
                                <h3 class="section-heading">Statut de la demande</h3>
                                <div class="row">
                                    <div class="col">
                                        <div class="custom-select-wrapper">
                                            <select id="status" name="status" required>
                                                <?php
                                                    // Get the current status
                                                    $current_status = $teacher->status;
                                                    $statuses = ["En cours", "En révision", "Rejeté", "Approuvé"];

                                                    // Output the current status as the selected option
                                                    echo '<option value="' . esc_attr($current_status) . '" selected>' . esc_html($current_status) . '</option>';

                                                    // Filter and display other statuses
                                                    foreach ($statuses as $status) {
                                                        if ($status !== $current_status) {
                                                            echo '<option value="' . esc_attr($status) . '">' . esc_html($status) . '</option>';
                                                        }
                                                    }
                                                ?>
                                            </select>
                                            <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="submit-button" name="edit_teacher_status">Mise à
                                    jour</button>
                            </section>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <!-- motivation -->
                        <div class="section">
                            <h3 class="section-heading">Vos Motivations</h3>
                            <div class="row detail-row">
                                <span class="col detail-value">
                                    <?php echo esc_html($teacher->motivation_of_joining); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <!-- cv -->
                        <div class="section">
                            <h3 class="section-heading">CV mis à jour</h3>
                            <?php if (!empty($teacher->upload_cv)) : ?>
                            <div class="docs">
                                <a href="<?php echo esc_url($teacher->upload_cv); ?>" target="_blank" class="doc">
                                    <i class="fas fa-paperclip"></i> CV
                                </a>
                            </div>
                            <?php else : ?>
                            <div class="docs">
                                <p class="no-data">Aucun CV téléchargé</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- other docs -->
                        <div class="section">
                            <h3 class="section-heading">Autres documents</h3>

                            <?php if (!empty($teacher->upload_doc1)) : ?>
                            <div class="docs">
                                <a href="<?php echo esc_url($teacher->upload_doc1); ?>" target="_blank" class="doc">
                                    <i class="fas fa-paperclip"></i> Document 1
                                </a>
                            </div>
                            <?php else : ?>
                            <div class="docs">
                                <p class="no-data">Aucun document téléchargé</p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($teacher->upload_doc2)) : ?>
                            <div class="docs">
                                <a href="<?php echo esc_url($teacher->upload_doc2); ?>" target="_blank" class="doc">
                                    <i class="fas fa-paperclip"></i> Document 2
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($teacher->upload_doc3)) : ?>
                            <div class="docs">
                                <a href="<?php echo esc_url($teacher->upload_doc3); ?>" target="_blank" class="doc">
                                    <i class="fas fa-paperclip"></i> Document 3
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($teacher->upload_doc4)) : ?>
                            <div class="docs">
                                <a href="<?php echo esc_url($teacher->upload_doc4); ?>" target="_blank" class="doc">
                                    <i class="fas fa-paperclip"></i> Document 4
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($teacher->upload_doc5)) : ?>
                            <div class="docs">
                                <a href="<?php echo esc_url($teacher->upload_doc5); ?>" target="_blank" class="doc">
                                    <i class="fas fa-paperclip"></i> Document 5
                                </a>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="col">
                        <!-- intro video -->
                        <div class="section">
                            <h3 class="section-heading">Vidéo d'introduction</h3>

                            <?php if (!empty($teacher->upload_video)) : ?>
                            <div class="docs">
                                <video src="<?php echo esc_url($teacher->upload_video); ?>" controls
                                    class="video"></video>
                            </div>
                            <?php else : ?>
                            <div class="docs">
                                <p class="no-data">Vidéo non téléchargée</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row content-section list">
            <div class="col">
                <!-- assigned courses -->
                <?php
                    if ($sessions) {
                ?>
                <div class="assigned-courses">
                    <h3 class="section-heading">Cours assignés</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Temps 1</th>
                                <th>Temps 2</th>
                            </tr>
                        </thead>
                        <tbody id="list">
                            <?php
                                foreach ($sessions as $session) {
                                    $course_id = $session->course_id;
                                    $group_number = $session->group_number;
                                    $session_date = $session->session_date;
                                    $session_date = date('M d, Y', strtotime($session->session_date));
                                    $status = $session->status;
                                    $slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
                                    $slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

                                    $table_name = $wpdb->prefix . 'courses';
                                    $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $course_id));

                                    // Translation map
                                    $translations = array(
                                        'upcoming'  => 'À venir',
                                        'ongoing'   => 'En cours',
                                        'completed' => 'Terminé',
                                        'cancelled' => 'Annulé',
                                    );

                                    // Convert status to lowercase just in case
                                    $status_key = strtolower($status);

                                    // Translate
                                    $french_status = isset($translations[$status_key]) ? $translations[$status_key] : $status;
                            ?>
                            <tr>
                                <td class="name">
                                    <a
                                        href="<?php echo esc_url(home_url('/admin/session-management/session-details/?session_id=' . $session->id)); ?>">
                                        <?php echo esc_html($course->title); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo esc_html($french_status); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($session_date); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($slot_1); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($slot_2); ?>
                                </td>
                            </tr>
                            <?php
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
                    }
                ?>

                <!-- bank details -->
                <?php
                    if (!empty($bankinfo)) {
                 ?>
                <div class="bank-details">
                    <h3 class="section-heading">Coordonnées Bancaires</h3>
                    <table class="table">
                        <tr>
                            <th>Nom de la banque</th>
                            <td>
                                <?= $bankinfo->bank_name ?>
                            </td>
                        </tr>
                        <tr>
                            <?php
                                if ($teacher->country == 'France') {
                            ?>
                            <th>IBAN</th>
                            <?php
                                } else {
                            ?>
                            <th>Numéro de compte</th>
                            <?php
                                }
                            ?>
                            <td>
                                <?= $bankinfo->account_number ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Titulaire du compte</th>
                            <td>
                                <?= $bankinfo->account_holder ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Type de compte</th>
                            <td>
                                <?= $bankinfo->account_type ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Code BIC/SWIFT</th>
                            <td>
                                <?= $bankinfo->swift_code ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Adresse de la banque</th>
                            <td>
                                <?= $bankinfo->bank_address ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php
                   } 
                 ?>

                <!-- payments history -->
                <div class="user-payments">
                    <h3 class="section-heading">Historique des paiements</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Numéro de facture</th>
                                <th>Paiement</th>
                                <th>Méthode</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Invoice</th>
                            </tr>
                        </thead>
                        <tbody id="list">
                            <?php if (!empty($payments)) : ?>
                            <?php 
                        foreach ($payments as $payment) : 
                            $user = get_user_by('id', $payment->teacher_id);
                        ?>
                            <tr>
                                <td class="invoice-number">
                                    <?php echo esc_html($payment->invoice_number); ?>
                                </td>
                                <td class="payment">
                                    <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                                    <?php echo intval($payment->amount) == $payment->amount ? intval($payment->amount) : number_format($payment->amount, 2); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($payment->payment_method); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($payment->status === 'in progress' ? 'En attente' : ($payment->status === 'due' ? 'À payer' : ($payment->status === 'completed' ? 'Terminé' : $payment->status))); ?>
                                </td>
                                <td>
                                    <?php echo esc_html(date('M d, Y', strtotime($payment->created_at))); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo esc_url(home_url('/admin/teacher-payments/invoice/?id=' . $payment->id)); ?>"
                                            target="_blank" class="invoice"><i class="fas fa-receipt"></i></a>
                                        <a href="<?php echo esc_url(home_url('/admin/teacher-payments/invoice/pdf/?id=' . $payment->id)); ?>"
                                            target="_blank" class="pdf"><i class="fas fa-file-pdf"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else : ?>
                            <tr>
                                <td colspan="7" class="no-data">Aucun paiement trouvé.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>