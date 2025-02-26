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

// Function to get the count of a teacher's active courses
function get_teacher_active_courses_count($teacher_id) {
    global $wpdb;

    // Query to get the count of active courses assigned to the teacher
    $course_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$wpdb->prefix}teacher_courses tc
             INNER JOIN {$wpdb->prefix}courses c ON tc.course_id = c.id
             WHERE tc.teacher_id = %d AND tc.status = %s",
            $teacher_id,
            'En cours'
        )
    );

    return $course_count;
}

// Function to get the count of a teacher's completed courses
function get_teacher_completed_courses_count($teacher_id) {
    global $wpdb;

    // Query to get the count of completed courses assigned to the teacher
    $course_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$wpdb->prefix}teacher_courses tc
             INNER JOIN {$wpdb->prefix}courses c ON tc.course_id = c.id
             WHERE tc.teacher_id = %d AND tc.status = %s",
            $teacher_id,
            'Complété'
        )
    );

    return $course_count;
}

// Query to count total students
$student_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}student_courses WHERE teacher_id = %d",
    $teacher_id
));

// Query to count total courses
$course_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}teacher_courses WHERE teacher_id = %d",
    $teacher_id
));

// Query to count total active courses
$active_course_count = get_teacher_active_courses_count($teacher_id);

// Query to count total completed courses
$completed_course_count = get_teacher_completed_courses_count($teacher_id);

// Query to get total due
$total_due = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT due FROM {$wpdb->prefix}teachers WHERE id = %d",
    $teacher_id
));

// Query to get total revenue for the teacher
$total_revenue = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT SUM(deposit) FROM {$wpdb->prefix}teacher_payments WHERE user_id = %d",
    $teacher_id
));

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
                        <?php echo esc_html($course_count); ?>
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
                        <?php echo esc_html($student_count); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/teacher/revenues/'); ?>" class="statistic-box total-due">
                    <h4 class="statistic-title">
                        <i class="fas fa-coins"></i> Total dû
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_due); ?>
                        <span class="currecy"><i class="fas fa-euro-sign"></i></span>
                    </p>
                </a>

                <a href="<?php echo home_url('/teacher/revenues/'); ?>" class="statistic-box total-deposit">
                    <h4 class="statistic-title">
                        <i class="fas fa-wallet"></i> Chiffre d'affaires total
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_revenue); ?>
                        <span class="currecy"><i class="fas fa-euro-sign"></i></span>
                    </p>
                </a>

            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>