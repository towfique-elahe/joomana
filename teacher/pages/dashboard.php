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

// Get today's date and current time
$today_date = date('Y-m-d');
$current_time = date('H:i:s'); // Get current time in HH:MM:SS format

// Get upcoming sessions
$upcoming_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT id, session_date, slot1_start_time 
         FROM $sessions_table 
         WHERE teacher_id = %d 
         AND (session_date > %s OR (session_date = %s AND slot1_start_time > %s))
         ORDER BY session_date ASC, slot1_start_time ASC",
        $teacher_id, $today_date, $today_date, $current_time
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
                        <i class="fas fa-user-graduate"></i> Total d'elèves
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_students ?? 0); ?>
                    </p>
                </a>

                <?php if (!empty($upcoming_sessions)) : ?>
                <?php foreach ($upcoming_sessions as $session) : ?>
                <?php
                    // Convert session date and time to readable format
                    $session_datetime = $session->session_date . ' ' . $session->slot1_start_time;
                    $session_timestamp = strtotime($session_datetime);
                ?>
                <a href="<?php echo site_url('/course/details/?session_id=' . $session->id); ?>"
                    class="statistic-box upcoming-session">
                    <h4 class="statistic-title">
                        <i class="fas fa-calendar-alt"></i> Prochaine Session
                    </h4>
                    <p class="countdown-timer" data-timestamp="<?php echo esc_attr($session_timestamp); ?>">
                        🕒 Loading countdown...
                    </p>
                    <p class="session-info">
                        📅 <?php echo date("M j, Y", strtotime($session->session_date)); ?> | 🕒
                        <?php echo date("H:i", strtotime($session->slot1_start_time)); ?>
                    </p>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- Countdown for upcoming sessions -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    function updateCountdown() {
        const countdownElements = document.querySelectorAll(".countdown-timer");

        countdownElements.forEach(function(element) {
            const sessionTimestamp = parseInt(element.getAttribute("data-timestamp")) * 1000;
            const now = new Date().getTime();
            const timeLeft = sessionTimestamp - now;

            if (timeLeft > 0) {
                const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                element.innerHTML = (days > 0 ? days + "d | " : "") + hours + "h : " + minutes +
                    "m : " +
                    seconds + "s";
            } else {
                element.innerHTML = "⏳ Session en cours...";
            }
        });
    }

    // Update countdown every second
    setInterval(updateCountdown, 1000);
    updateCountdown(); // Initial call
});
</script>


<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>