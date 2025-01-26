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
                            <span class="col detail-label">Entreprise:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($teacher->company_name); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Paiement total:</span>
                            <span class="col detail-value">n/a</span>
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
                <div class="assigned-courses">
                    <h3 class="section-heading">Cours assignés</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Statut</th>
                                <th>Date de début</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody id="list">
                            <tr>
                                <td>Cours 1: Lorem, ipsum dolor...</td>
                                <td>Complété</td>
                                <td>Sep 29, 2024</td>
                                <td class="action-buttons">
                                    <a href="#" class="action-button edit">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>Cours 2: Lorem, ipsum dolor...</td>
                                <td>Complété</td>
                                <td>Dec 16, 2024</td>
                                <td class="action-buttons">
                                    <a href="#" class="action-button edit">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>Cours 3: Lorem, ipsum dolor...</td>
                                <td>En cours</td>
                                <td>Jan 11, 2025</td>
                                <td class="action-buttons">
                                    <a href="#" class="action-button edit">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>Cours 4: Lorem, ipsum dolor...</td>
                                <td>En attente</td>
                                <td>Feb 12, 2025</td>
                                <td class="action-buttons">
                                    <a href="#" class="action-button edit">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>Cours 5: Lorem, ipsum dolor...</td>
                                <td>En attente</td>
                                <td>Mar 1, 2025</td>
                                <td class="action-buttons">
                                    <a href="#" class="action-button edit">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- bank details -->
                <?php
                    if (!empty($bankinfo)) {
                 ?>
                <div class="bank-details">
                    <h3 class="section-heading">Coordonnées Bancaires</h3>
                    <table class="table">
                        <tr>
                            <th>Nom de la banque</th>
                            <td><?= $bankinfo->bank_name ?></td>
                        </tr>
                        <tr>
                            <th>Numéro de compte</th>
                            <td><?= $bankinfo->account_number ?></td>
                        </tr>
                        <tr>
                            <th>Titulaire du compte</th>
                            <td><?= $bankinfo->account_holder ?></td>
                        </tr>
                        <tr>
                            <th>Type de compte</th>
                            <td><?= $bankinfo->account_type ?></td>
                        </tr>
                        <tr>
                            <th>Code BIC/SWIFT</th>
                            <td><?= $bankinfo->swift_code ?></td>
                        </tr>
                        <tr>
                            <th>Adresse de la banque</th>
                            <td><?= $bankinfo->bank_address ?></td>
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
                                <th>Commande</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Mode de paiement</th>
                                <th>Invoice</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody id="list">
                            <tr>
                                <td>#1001</td>
                                <td>Sep 29, 2024</td>
                                <td class="payment">
                                    <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                                    5.00
                                </td>
                                <td>PAYPAL</td>
                                <td>
                                    <a href="#" class="invoice"><i class="fas fa-receipt"></i></a>
                                </td>
                                <td class="action-buttons">
                                    <a href="#" class="action-button edit">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>#1002</td>
                                <td>Dec 3, 2024</td>
                                <td class="payment">
                                    <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                                    10.00
                                </td>
                                <td>BANK TRANSFER</td>
                                <td>
                                    <a href="#" class="invoice"><i class="fas fa-receipt"></i></a>
                                </td>
                                <td class="action-buttons">
                                    <a href="#" class="action-button edit">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>#1003</td>
                                <td>Jan 01, 2025</td>
                                <td class="payment">
                                    <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                                    5.00
                                </td>
                                <td>STRIPE</td>
                                <td>
                                    <a href="#" class="invoice"><i class="fas fa-receipt"></i></a>
                                </td>
                                <td class="action-buttons">
                                    <a href="#" class="action-button edit">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>#1004</td>
                                <td>Jan 10, 2025</td>
                                <td class="payment">
                                    <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                                    10.00
                                </td>
                                <td>MASTER CARD</td>
                                <td>
                                    <a href="#" class="invoice"><i class="fas fa-receipt"></i></a>
                                </td>
                                <td class="action-buttons">
                                    <a href="#" class="action-button edit">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>