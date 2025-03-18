<?php

/* Template Name: Teacher | Dashboard */

// page title
global $pageTitle;
$pageTitle = 'Tableau De Bord';

require_once(get_template_directory() . '/teacher/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the current user
$user = wp_get_current_user();
$teacher_id = $user->ID;

global $wpdb;
$courses_table = $wpdb->prefix . 'courses';
$sessions_table = $wpdb->prefix . 'course_sessions';

// Ensure teacher_id is defined and treated as a string
$teacher_id_str = json_encode((string) $teacher_id);

// Assigned courses count
$assigned_course_count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM $courses_table WHERE JSON_CONTAINS(assigned_teachers, %s)", 
        $teacher_id_str
    )
);

// Active courses count (status = 'Upcoming' OR 'Ongoing')
$active_course_count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM $courses_table 
         WHERE JSON_CONTAINS(assigned_teachers, %s) 
         AND (status = %s OR status = %s)",
        $teacher_id_str, "Upcoming", "Ongoing"
    )
);

// Completed courses count (status = 'Completed')
$completed_course_count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM $courses_table 
         WHERE JSON_CONTAINS(assigned_teachers, %s) 
         AND (status = %s)",
        $teacher_id_str, "Completed"
    )
);

// Count total students from enrolled_students where teacher_id matches
$total_students = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT SUM(JSON_LENGTH(enrolled_students)) 
         FROM $sessions_table 
         WHERE teacher_id = %d",
        $teacher_id
    )
);

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/teacher/templates/sidebar.php'); ?>
    </div>
    <div id="teacherDashboard" class="main-content">
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

                <a href="<?php echo home_url('/teacher/course-management/'); ?>" class="statistic-box total-course">
                    <h4 class="statistic-title">
                        <i class="fas fa-book"></i> Total de cours
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($assigned_course_count); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/teacher/course-management/'); ?>"
                    class="statistic-box total-in-progress-course">
                    <h4 class="statistic-title">
                        <i class="fas fa-hourglass-end"></i> Total des cours en cours
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($active_course_count); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/teacher/course-management/'); ?>"
                    class="statistic-box total-completed-course">
                    <h4 class="statistic-title">
                        <i class="fas fa-check-circle"></i> Total de cours suivis
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($completed_course_count); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/teacher/course-management/'); ?>" class="statistic-box total-student">
                    <h4 class="statistic-title">
                        <i class="fas fa-user-graduate"></i> Total d'étudiants
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_students); ?>
                    </p>
                </a>

            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>