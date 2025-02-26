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
// Get the student details by the current user
$student_table = $wpdb->prefix . 'students';
$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $student_id));

global $wpdb;

// Function to get the count of a student's active courses
function get_student_active_courses_count($student_id) {
    global $wpdb;

    // Query to get the count of active courses assigned to the student
    $course_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$wpdb->prefix}student_courses sc
             INNER JOIN {$wpdb->prefix}courses c ON sc.course_id = c.id
             WHERE sc.student_id = %d AND sc.status = %s",
            $student_id,
            'En cours'
        )
    );

    return $course_count;
}

// Function to get the count of a student's completed courses
function get_student_completed_courses_count($student_id) {
    global $wpdb;

    // Query to get the count of completed courses assigned to the student
    $course_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$wpdb->prefix}student_courses sc
             INNER JOIN {$wpdb->prefix}courses c ON sc.course_id = c.id
             WHERE sc.student_id = %d AND sc.status = %s",
            $student_id,
            'Complété'
        )
    );

    return $course_count;
}

// Query to count total courses
$course_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}student_courses WHERE student_id = %d",
    $student_id
));

// Query to count total active courses
$active_course_count = get_student_active_courses_count($student_id);

// Query to count total completed courses
$completed_course_count = get_student_completed_courses_count($student_id);

// Query to get total payments
$total_payments = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT SUM(amount) FROM {$wpdb->prefix}payments WHERE user_id = %d",
    $student_id
));

// Query to get total credit purchased
$total_credit = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT SUM(credit) FROM {$wpdb->prefix}payments WHERE user_id = %d",
    $student_id
));

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
                        <?php echo esc_html($course_count); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/student/course-management/'); ?>"
                    class="statistic-box total-in-progress-course">
                    <h4 class="statistic-title">
                        <i class="fas fa-hourglass-end"></i> Total des cours en cours
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($active_course_count); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/student/course-management/'); ?>"
                    class="statistic-box total-completed-course">
                    <h4 class="statistic-title">
                        <i class="fas fa-check-circle"></i> Total de cours suivis
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($completed_course_count); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/student/payments/'); ?>" class="statistic-box total-payments">
                    <h4 class="statistic-title">
                        <i class="fas fa-exchange-alt"></i> Paiements totaux
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_payments); ?>
                        <span class="currecy"><i class="fas fa-euro-sign"></i></span>
                    </p>
                </a>

                <a href="<?php echo home_url('/student/credit-management/'); ?>"
                    class="statistic-box total-purchased-credit">
                    <h4 class="statistic-title">
                        <i class="fas fa-shopping-bag"></i> Crédit d'achat total
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_credit); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/student/credit-management/'); ?>"
                    class="statistic-box total-available-credit">
                    <h4 class="statistic-title">
                        <i class="fas fa-coins"></i> Crédit total disponible
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