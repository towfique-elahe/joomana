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

$sessions_table = $wpdb->prefix . 'course_sessions';

// Get all session IDs for the student
$session_ids = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT id FROM $sessions_table WHERE JSON_CONTAINS(enrolled_students, %s)",
        '"' . $student_id . '"'
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

// Fetch session reports for the student
$student_reports = $session_id ? $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}student_reports WHERE session_id = %d AND student_id = %d",
    $session_id,
    $student_id
)) : [];

// Fetch Attendance Rate for all sessions
$attendance_data = $wpdb->get_results($wpdb->prepare(
    "SELECT attendance FROM {$wpdb->prefix}student_attendance WHERE student_id = %d",
    $student_id
));

$present_count = 0;
$absent_count = 0;
foreach ($attendance_data as $attendance) {
    if ($attendance->attendance === 'present') {
        $present_count++;
    } else {
        $absent_count++;
    }
}

// Fetch Assignment Submission Progress for all sessions
// Get total assignments for the student
$assignments_data = $wpdb->get_results($wpdb->prepare(
    "SELECT id FROM {$wpdb->prefix}course_assignments WHERE session_id IN (
        SELECT session_id FROM {$wpdb->prefix}student_attendance WHERE student_id = %d
    )",
    $student_id
));

$total_assignments = count($assignments_data);

// Get submitted assignments for the student
$submission_data = $wpdb->get_results($wpdb->prepare(
    "SELECT file FROM {$wpdb->prefix}student_submissions WHERE student_id = %d",
    $student_id
));

$submitted_count = 0;
foreach ($submission_data as $submission) {
    if (!empty($submission->file)) {
        $submitted_count++;
    }
}

$not_submitted_count = $total_assignments - $submitted_count;

// Fetch Performance Feedback from Teachers for all sessions
$feedback_data = $wpdb->get_results($wpdb->prepare(
    "SELECT comment FROM {$wpdb->prefix}student_reports WHERE student_id = %d",
    $student_id
));

$feedback_counts = [
    'Excellent' => 0,
    'Bon' => 0,
    'Moyen' => 0,
    'Faible' => 0,
];
foreach ($feedback_data as $feedback) {
    if (isset($feedback_counts[$feedback->comment])) {
        $feedback_counts[$feedback->comment]++;
    }
}

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

        <div class="row chart-container">
            <!-- Attendance Rate Chart -->
            <div class="chart-card">
                <h3 class="chart-heading">Taux de présence</h3>
                <canvas id="attendanceChart"></canvas>
            </div>

            <!-- Assignment Submission Progress Chart -->
            <div class="chart-card">
                <h3 class="chart-heading">Progrès des devoirs</h3>
                <canvas id="submissionChart"></canvas>
            </div>

            <!-- Performance Feedback Chart -->
            <div class="chart-card">
                <h3 class="chart-heading">Retour des enseignants</h3>
                <canvas id="feedbackChart"></canvas>
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
            <?php if ($student_reports) :?>

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
            <?php else :?>
            <p class="no-data">Aucun rapport de progression n'a été ajouté pour ce cours</p>
            <?php endif;?>

        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Attendance Rate Chart
const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
const attendanceChart = new Chart(attendanceCtx, {
    type: 'pie',
    data: {
        labels: ['Présent', 'Absent'],
        datasets: [{
            data: [<?php echo $present_count; ?>, <?php echo $absent_count; ?>],
            backgroundColor: ['#36a2eb', '#ff6384'],
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
        }
    }
});

// Assignment Submission Progress Chart
const submissionCtx = document.getElementById('submissionChart').getContext('2d');
const submissionChart = new Chart(submissionCtx, {
    type: 'pie',
    data: {
        labels: ['Soumis', 'Non soumis'],
        datasets: [{
            data: [<?php echo $submitted_count; ?>, <?php echo $not_submitted_count; ?>],
            backgroundColor: ['#4bc0c0', '#ff9f40'],
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
        }
    }
});

// Performance Feedback Chart
const feedbackCtx = document.getElementById('feedbackChart').getContext('2d');
const feedbackChart = new Chart(feedbackCtx, {
    type: 'pie',
    data: {
        labels: ['Excellent', 'Bon', 'Moyen', 'Faible'],
        datasets: [{
            data: [
                <?php echo $feedback_counts['Excellent']; ?>,
                <?php echo $feedback_counts['Bon']; ?>,
                <?php echo $feedback_counts['Moyen']; ?>,
                <?php echo $feedback_counts['Faible']; ?>
            ],
            backgroundColor: ['#9966ff', '#ffcd56', '#c9cbcf', '#ff6384'],
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
        }
    }
});
</script>

<?php require_once(get_template_directory() . '/student/templates/footer.php'); ?>