<?php

/* Template Name: Teacher | Resources */

// Page title
global $pageTitle;
$pageTitle = 'Ressources';

require_once(get_template_directory() . '/teacher/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get the current user
$user = wp_get_current_user();
$teacher_id = $user->ID;

$sessions_table = $wpdb->prefix . 'course_sessions';

// Get all session IDs for the teacher
$session_ids = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT id FROM $sessions_table WHERE teacher_id = %d",
        $teacher_id
    )
);

// Check if session_id is set in the URL and is valid
$session_id = isset($_GET['session_id']) && in_array($_GET['session_id'], $session_ids) ? intval($_GET['session_id']) : null;

// If no session_id is set, select the first session
if (!$session_id && !empty($session_ids)) {
    $session_id = $session_ids[0];
    // Redirect to the same page with the first session_id in the URL
    wp_redirect(add_query_arg('session_id', $session_id));
    exit;
}

// Fetch session assignments for the teacher
$course_assignments = $session_id ? $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}course_assignments WHERE session_id = %d",
    $session_id
)) : [];

// Fetch session slides for the teacher
$course_slides = $session_id ? $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}course_slides WHERE session_id = %d",
    $session_id
)) : [];

// Fetch student reports for the teacher
$student_reports = $session_id ? $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}student_reports WHERE session_id = %d",
    $session_id
)) : [];

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/teacher/templates/sidebar.php'); ?>
    </div>
    <div id="teacherResources" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Ressources</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/teacher/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Ressources</span>
            </div>
        </div>

        <div class="select-session">
            <?php
            // Only proceed if there are session IDs
            if (!empty($session_ids)) {
                // Convert array into a comma-separated string for SQL IN clause
                $placeholders = implode(',', array_fill(0, count($session_ids), '%d'));

                // Prepare the query dynamically
                $query = $wpdb->prepare(
                    "SELECT id, course_id, session_date FROM $sessions_table WHERE id IN ($placeholders)",
                    ...$session_ids
                );

                // Fetch matching sessions
                $sessions = $wpdb->get_results($query);

                if ($sessions) : ?>
            <div class="custom-select-wrapper">
                <label for="sessionSelect">Sélectionnez une session</label>
                <select id="sessionSelect">
                    <?php 
                        foreach ($sessions as $session) :
                        $date = date('j M, y', strtotime($session->session_date));
                        $course_id = $session->course_id;
                        global $wpdb;
                        $courses_table = $wpdb->prefix . 'courses';
                        $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $courses_table WHERE id = %d", $course_id));
                    ?>
                    <option value="<?php echo esc_attr($session->id); ?>"
                        <?php echo ($session->id == $session_id) ? 'selected' : ''; ?>>
                        <?php echo 'Date: ' . esc_html($date) . ' | ' . esc_html($course->title); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-chevron-down custom-arrow"></i>
            </div>
            <?php endif;
            }
            ?>
        </div>

        <div class="row file-container">
            <?php if ($course_assignments || $course_slides || $student_reports) : ?>

            <!-- Display Session Assignments -->
            <?php foreach ($course_assignments as $assignment) : ?>
            <div class="file-card">
                <div class="file-top">
                    <p class="file-type assignment">Affectation</p>
                    <a href="<?php echo esc_url($assignment->file); ?>" class="download-button" download>
                        <i class="fas fa-download"></i>
                    </a>
                    <div class="file-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                </div>
                <div class="file-bottom row">
                    <div class="col">
                        <h3 class="file-title">Affectation | <?php echo basename($assignment->file); ?></h3>
                        <p class="file-info">
                            Date limite: <?php echo date('d M, y', strtotime($assignment->deadline)); ?>
                        </p>
                        <p class="file-info">
                            Téléchargé: <?php echo date('d M, y', strtotime($assignment->created_at)); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Display Session Slides -->
            <?php foreach ($course_slides as $slide) : ?>
            <div class="file-card">
                <div class="file-top">
                    <p class="file-type slide">Diapositive</p>
                    <a href="<?php echo esc_url($slide->file); ?>" class="download-button" download>
                        <i class="fas fa-download"></i>
                    </a>
                    <div class="file-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                </div>
                <div class="file-bottom row">
                    <div class="col">
                        <h3 class="file-title">Diapositive | <?php echo basename($slide->file); ?></h3>
                        <p class="file-info">
                            Téléchargé: <?php echo date('d M, y', strtotime($slide->created_at)); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Display Student Reports -->
            <?php foreach ($student_reports as $report) : ?>
            <div class="file-card">
                <div class="file-top">
                    <p class="file-type report">Rapport</p>
                    <a href="<?php echo esc_url($report->file); ?>" class="download-button" download>
                        <i class="fas fa-download"></i>
                    </a>
                    <div class="file-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                </div>
                <div class="file-bottom row">
                    <div class="col">
                        <h3 class="file-title">Rapport | <?php echo basename($report->file); ?></h3>
                        <?php
                                if (in_array('teacher', (array) $user->roles)) {
                                    $student_id = $report->student_id;
                                    // Fetch the student's details using the student_id
                                    $student_table = $wpdb->prefix . 'students';
                                    $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $student_id));
                                    if ($student) {
                                ?>
                        <p class="file-info">
                            Étudiant:
                            <a href="<?php echo esc_url(home_url('/session/student-management/student-details/?id=' . $student->id . '&session_id=' . $session_id)); ?>"
                                class="accent">
                                <?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?>
                            </a>
                        </p>
                        <?php
                                    }
                                }
                                ?>
                        <p class="file-info">
                            Commentaire: <?php echo esc_html($report->comment); ?>
                        </p>
                        <p class="file-info">
                            Téléchargé: <?php echo date('d M, y', strtotime($report->created_at)); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else : ?>
            <p class="no-data">Aucune ressource n'a été ajoutée pour ce cours</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var sessionSelect = document.getElementById("sessionSelect");

    if (sessionSelect) {
        // If no session is selected, select the first one and update the URL
        if (!window.location.search.includes('session_id')) {
            var firstSessionId = sessionSelect.options[0].value;
            window.location.href = window.location.pathname + "?session_id=" + encodeURIComponent(
                firstSessionId);
        }

        sessionSelect.addEventListener("change", function() {
            var selectedCourse = this.value;
            if (selectedCourse) {
                window.location.href = window.location.pathname + "?session_id=" + encodeURIComponent(
                    selectedCourse);
            }
        });
    }
});
</script>

<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>