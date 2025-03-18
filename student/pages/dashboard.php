<?php

/* Template Name: Student | Dashboard */

// page title
global $pageTitle;
$pageTitle = 'Tableau De Bord';

require_once(get_template_directory() . '/student/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the current user
$user = wp_get_current_user();
$student_id = $user->ID;

global $wpdb;

$session_table = $wpdb->prefix . 'course_sessions';

$total_course_count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM $session_table 
         WHERE JSON_CONTAINS(enrolled_students, %s)",
        json_encode((string) $student_id)
    )
);

$total_active_course_count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM $session_table 
         WHERE JSON_CONTAINS(enrolled_students, %s) 
         AND status IN (%s, %s)",
        json_encode((string) $student_id), 'upcoming', 'ongoing'
    )
);

$total_completed_course_count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM $session_table 
         WHERE JSON_CONTAINS(enrolled_students, %s) 
         AND status IN (%s)",
        json_encode((string) $student_id), 'completed'
    )
);

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/student/templates/sidebar.php'); ?>
    </div>
    <div id="studentDashboard" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Tableau de bord</h2>
            <div class="content-breadcrumb">
                <span class="active">Tableau de bord</span>
            </div>
        </div>

        <div class="content-section statistics">

            <h3 class="section-heading">
                <i class="far fa-chart-bar"></i>
                Statistiques
            </h3>

            <div class="section-body">

                <a href="<?php echo home_url('/student/course-management/'); ?>" class="statistic-box total-course">
                    <h4 class="statistic-title">
                        <i class="fas fa-book"></i> Total de cours
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_course_count); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/student/course-management/'); ?>"
                    class="statistic-box total-in-progress-course">
                    <h4 class="statistic-title">
                        <i class="fas fa-hourglass-end"></i> Total des cours en cours
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_active_course_count); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/student/course-management/'); ?>"
                    class="statistic-box total-completed-course">
                    <h4 class="statistic-title">
                        <i class="fas fa-check-circle"></i> Total de cours suivis
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_completed_course_count); ?>
                    </p>
                </a>

            </div>

        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/student/templates/footer.php'); ?>