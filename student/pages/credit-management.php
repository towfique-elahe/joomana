<?php

/* Template Name: Student | Credit Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion De Crédit';

require_once(get_template_directory() . '/student/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get current user ID
$current_user = get_current_user_id();

// Get table name
global $wpdb;
$student_table = $wpdb->prefix . 'students';

// Get the student details by the current user
$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $current_user));

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/student/templates/sidebar.php'); ?>
    </div>
    <div id="studentCredit" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion De Crédit</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/student/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion De Crédit</span>
            </div>
        </div>

        <div class="content-section statistics">
            <div class="section-body">

                <!-- Available Credit Count -->
                <a href="javascript:void()" class="statistic-box total-teacher">
                    <h4 class="statistic-title">
                        <i class="fas fa-chalkboard-teacher"></i> Crédit disponible
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($student->credit); ?>
                    </p>
                </a>

            </div>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/student/templates/footer.php'); ?>