<?php

/* Template Name: Admin | Student Details */

// page title
global $pageTitle;
$pageTitle = "Détails Sur L'étudiant";

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
$table_name = $wpdb->prefix . 'students';

// Fetch the details of the student using the ID
$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

if (!$student) {
    // Handle case when the student does not exist
    wp_die("L'étudiant demandé n'a pas pu être trouvé.");
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminStudentDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails Sur L'étudiant</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/student-management/'); ?>" class="breadcrumb-link">Gestion
                    étudiants</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Détails Sur L'étudiant</span>
            </div>
        </div>

        <div class="content-section">
            <div class="row">
                <div class="col section user-profile">
                    <div class="profile-top">
                        <img src="<?php echo !empty($student->image) ? esc_url($student->image) : esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png'); ?>"
                            alt="User Image" class="profile-image">

                        <h3 class="profile-name">
                            <?php echo esc_html($student->first_name) . " " . esc_html($student->last_name); ?></h3>
                        <p class="profile-username"><?php echo esc_html($wp_user->user_login); ?></p>
                    </div>
                    <div class="profile-details">
                        <div class="row detail-row">
                            <span class="col detail-label">Email:</span>
                            <span class="col detail-value"><?php echo esc_html($wp_user->user_email); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Date de naissance:</span>
                            <span class="col detail-value"><?php echo esc_html($student->date_of_birth); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Genre:</span>
                            <span class="col detail-value"><?php echo esc_html($student->gender); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Grade:</span>
                            <span class="col detail-value"><?php echo esc_html($student->grade); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Niveau:</span>
                            <span class="col detail-value"><?php echo esc_html($student->level); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Paiement total:</span>
                            <span class="col detail-value">n/a</span>
                        </div>
                    </div>
                </div>

                <div class="col section user-courses">
                    <h3 class="section-heading">Cours Enregistrés</h3>
                    <!-- <div class="course-list">
                        <div class="course-item">
                            <div class="course-image">
                                <img src="<?php echo get_stylesheet_directory_uri(). '/assets/image/image-placeholder.png';?>"
                                    alt="Course Image">
                            </div>
                            <div class="course-details">
                                <h4 class="course-title">Mathématiques</h4>
                                <span class="course-student">Six</span>
                                <span class="course-level">Débutant</span>
                                <span class="course-payment">20.00</span>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>

            <div class="row">
                <div class="col">
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