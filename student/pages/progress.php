<?php

/* Template Name: Student | Progress */

// page title
global $pageTitle;
$pageTitle = 'Progrès';

require_once(get_template_directory() . '/student/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get the current user
$user = wp_get_current_user();
$student_id = $user->ID;

// Get all course IDs for the student
$course_ids = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT course_id FROM {$wpdb->prefix}student_courses WHERE student_id = %d",
        $student_id
    )
);

$course_id = intval($_GET['course_id']);

$course_ids = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT course_id FROM {$wpdb->prefix}student_courses WHERE student_id = %d",
        $student_id
    )
);

$student_group = $wpdb->get_var($wpdb->prepare(
    "SELECT group_number FROM {$wpdb->prefix}student_courses WHERE student_id = %d AND course_id = %d LIMIT 1",
    $student_id,
    $course_id
));
if ($student_group) {
    $group_number = intval($student_group);
}

// Fetch student reports for the student
$student_reports = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}student_reports WHERE course_id = %d AND group_number = %d AND student_id = %d",
    $course_id,
    $group_number,
    $student_id
));

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/student/templates/sidebar.php'); ?>
    </div>
    <div id="studentProgress" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Progrès</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/student/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Progrès</span>
            </div>
        </div>

        <div class="select-course">
            <?php
            // Only proceed if there are course IDs
            if (!empty($course_ids)) {
            // Convert array into a comma-separated string for SQL IN clause
            $placeholders = implode(',', array_fill(0, count($course_ids), '%d'));

            // Prepare the query dynamically
            $query = $wpdb->prepare(
            "SELECT id, title FROM {$wpdb->prefix}courses WHERE id IN ($placeholders)",
            ...$course_ids
            );

            // Fetch matching courses
            $courses = $wpdb->get_results($query);

            // Get the first course ID for auto-selection
            $first_course_id = !empty($courses) ? $courses[0]->id : null;

            if ($courses): ?>
            <div class="custom-select-wrapper">
                <label for="courseSelect">Sélectionnez un cours</label>
                <select id="courseSelect">
                    <?php foreach ($courses as $course): ?>
                    <option value="<?php echo esc_attr($course->id); ?>" <?php echo ($course->id ==
                        $_GET['course_id']
                        ??
                        $first_course_id) ? 'selected' : ''; ?>>
                        <?php echo esc_html($course->title); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-chevron-down custom-arrow"></i>
            </div>

            <script>
            document.addEventListener("DOMContentLoaded", function() {
                var courseSelect = document.getElementById("courseSelect");

                if (courseSelect) {
                    var selectedCourse = courseSelect.value;
                    var urlParams = new URLSearchParams(window.location.search);

                    // If no course_id in URL, set it to the first course automatically
                    if (!urlParams.has("course_id") && selectedCourse) {
                        window.location.href = window.location.pathname + "?course_id=" + encodeURIComponent(
                            selectedCourse);
                    }

                    courseSelect.addEventListener("change", function() {
                        var selectedCourse = this.value;
                        if (selectedCourse) {
                            window.location.href = window.location.pathname + "?course_id=" +
                                encodeURIComponent(selectedCourse);
                        }
                    });
                }
            });
            </script>
            <?php endif;
            }
            ?>

        </div>

        <div class="row file-container">
            <?php if ($student_reports) :?>

            <!-- Display Student Reports -->
            <?php foreach ($student_reports as $report) : ?>
            <div class="file-card">
                <div class="file-top">
                    <p class="file-type report">Progress Report</p>
                    <?php
                            if (in_array('teacher', (array) $user->roles)) {
                        ?>
                    <form method="post" class="delete-form">
                        <input type="hidden" name="delete_item_id" value="<?php echo esc_attr($report->id); ?>">
                        <input type="hidden" name="file_type" id="deleteFileType" value="Progress Report">
                        <button type="button" class="button file-delete open-modal" data-modal="fileDelete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                    <?php
                            }
                        ?>
                    <div class="file-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                </div>
                <div class="file-bottom row">
                    <div class="col">
                        <h3 class="file-title">
                            <?php echo basename($report->file); ?>
                        </h3>
                        <p class="file-uploaded-time">
                            Téléchargé:
                            <?php echo date('Y-m-d | H:i:s', strtotime($report->created_at)); ?>
                        </p>
                    </div>
                    <div class="col">
                        <a href="<?php echo esc_url($report->file); ?>" class="download-button" download>
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else :?>
            <p class="no-data">Aucun rapport de progression n'a été ajouté pour ce cours</p>
            <?php endif;?>

        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/student/templates/footer.php'); ?>