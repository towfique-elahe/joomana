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

// Get today's date and current time
$today_date = date('Y-m-d');
$current_time = date('H:i:s');

// Get upcoming sessions
$upcoming_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT id, session_date, slot1_start_time 
         FROM $session_table 
         WHERE JSON_CONTAINS(enrolled_students, %s) 
         AND (session_date > %s OR (session_date = %s AND slot1_start_time > %s))
         ORDER BY session_date ASC, slot1_start_time ASC",
        json_encode((string) $student_id), $today_date, $today_date, $current_time
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
    updateCountdown();
});
</script>

<?php require_once(get_template_directory() . '/student/templates/footer.php'); ?>