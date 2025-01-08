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

// Check if the id is present in the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the details of the user from wp users table
if ($id > 0) {
    $wp_user = get_user_by('ID', $id);
} else {
    echo "Invalid user ID.";
}

global $wpdb;
$table_name = $wpdb->prefix . 'teachers';

// Fetch the details of the teacher using the ID
$teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

if (!$teacher) {
    // Handle case when the teacher does not exist
    wp_die("L'enseignant demandé n'a pas pu être trouvé.");
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminStudentDetails" class="main-content">
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

        <div class="content-section">
            <div class="row">
                <div class="col">
                    <div class="section user-profile">
                        <div class="profile-top">
                            <img src="<?php echo !empty($teacher->image) ? esc_url($teacher->image) : esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png'); ?>"
                                alt="User Image" class="profile-image">

                            <h3 class="profile-name">
                                <?php echo esc_html($teacher->first_name) . " " . esc_html($teacher->last_name); ?></h3>
                            <p class="profile-username"><?php echo esc_html($wp_user->user_login); ?></p>
                            <p class="profile-status"><?php echo esc_html($teacher->status); ?></p>
                        </div>
                        <div class="profile-details">
                            <div class="row detail-row">
                                <span class="col detail-label">Email:</span>
                                <span class="col detail-value"><?php echo esc_html($wp_user->user_email); ?></span>
                            </div>
                            <div class="row detail-row">
                                <span class="col detail-label">Téléphone:</span>
                                <span class="col detail-value"><?php echo esc_html($teacher->phone); ?></span>
                            </div>
                            <div class="row detail-row">
                                <span class="col detail-label">Adresse:</span>
                                <span
                                    class="col detail-value"><?php echo esc_html($teacher->address) . ", " . esc_html($teacher->city) . ", " . esc_html($teacher->country) . "-" . esc_html($teacher->postal_code); ?></span>
                            </div>
                            <div class="row detail-row">
                                <span class="col detail-label">Nom de l'entreprise:</span>
                                <span class="col detail-value"><?php echo esc_html($teacher->company_name); ?></span>
                            </div>
                            <div class="row detail-row">
                                <span class="col detail-label">Paiement total:</span>
                                <span class="col detail-value">n/a</span>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-heading">Formation et qualifications</h3>
                        <div class="profile-details">
                            <div class="row detail-row">
                                <span class="col detail-label">Dernier Degré:</span>
                                <span class="col detail-value"><?php echo esc_html($teacher->degree); ?></span>
                            </div>
                            <div class="row detail-row">
                                <span class="col detail-label">Institution d'obtention:</span>
                                <span class="col detail-value"><?php echo esc_html($teacher->institute); ?></span>
                            </div>
                            <div class="row detail-row">
                                <span class="col detail-label">Année d'obtention du diplôme:</span>
                                <span class="col detail-value"><?php echo esc_html($teacher->graduation_year); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-heading">Vos Motivations</h3>
                        <p class="para"><?php echo esc_html($teacher->motivation_of_joining); ?></p>
                    </div>

                </div>

                <div class="col">
                    <div class="section user-courses">
                        <h3 class="section-heading">Statut de la demande</h3>

                    </div>

                    <div class="section user-payments">
                        <h3 class="section-heading">Paiements</h3>
                        <!-- <div class="payment-list">
                            <div class="payment-item">
                                <div class="payment-date">20/01/2022</div>
                                <div class="payment-details">
                                    <h4 class="payment-course">Mathématiques</h4>
                                    <span class="payment-amount">20.00</span>
                                    <span class="payment-status">Payé</span>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>