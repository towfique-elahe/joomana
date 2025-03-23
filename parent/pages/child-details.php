<?php

/* Template Name: Parent | Child Details */

// page title
global $pageTitle;
$pageTitle = "Détails de l'enfant";

require_once(get_template_directory() . '/parent/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if the id is present in the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the details of the user from wp users table
if ($id > 0) {
    $wp_user = get_user_by('ID', $id);
} else {
    echo "Invalid user ID.";
}

global $wpdb;
$student_table = $wpdb->prefix . 'students';

// Fetch the details of the student using the ID
$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $id));

if (!$student) {
    // Handle case when the student does not exist
    wp_die("L'elèves demandé n'a pas pu être trouvé.");
}

// get current student
$student_id = $student->id;

$sessions_table = $wpdb->prefix . 'course_sessions';

$upcomming_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
        WHERE JSON_CONTAINS(enrolled_students, %s) 
        AND status IN ('upcoming')",
        '"' . $student_id . '"'
    )
);

$ongoing_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
        WHERE JSON_CONTAINS(enrolled_students, %s) 
        AND status IN ('ongoing')",
        '"' . $student_id . '"'
    )
);

$completed_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
        WHERE JSON_CONTAINS(enrolled_students, %s) 
        AND status IN ('completed')",
        '"' . $student_id . '"'
    )
);

$cancelled_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
        WHERE JSON_CONTAINS(enrolled_students, %s) 
        AND status IN ('cancelled')",
        '"' . $student_id . '"'
    )
);

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
        <?php require_once(get_template_directory() . '/parent/templates/sidebar.php'); ?>
    </div>
    <div id="parentChildDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails de l'enfant</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/parent/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/parent/child-management'); ?>" class="breadcrumb-link">Gestion Des
                    Enfants</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Détails de l'enfant</span>
            </div>
        </div>

        <div class="content-section">
            <div class="row">
                <div class="col section user-profile">
                    <div class="profile-top">
                        <img src="<?php echo !empty($student->image) ? esc_url($student->image) : esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png'); ?>"
                            alt="User Image" class="profile-image">

                        <h3 class="profile-name">
                            <?php echo esc_html($student->first_name) . " " . esc_html($student->last_name); ?>
                        </h3>
                        <p class="profile-username">
                            <?php echo esc_html($wp_user->user_login); ?>
                        </p>
                    </div>
                    <div class="profile-details">
                        <div class="row detail-row">
                            <span class="col detail-label">Email:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($wp_user->user_email); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Date de naissance:</span>
                            <span class="col detail-value">
                                <?php echo esc_html(date('M d, Y', strtotime($student->date_of_birth))); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Genre:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($student->gender); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Classe:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($student->grade); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Niveau:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($student->level); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col section user-courses">

                    <h3 class="section-heading">Cours Enregistrés</h3>

                    <ul class="nav nav-tabs" id="courseTabs">
                        <a class="nav-link active" data-toggle="tab" href="#upcoming">Cours à venir</a>
                        <a class="nav-link" data-toggle="tab" href="#ongoing">Cours en cours</a>
                        <a class="nav-link" data-toggle="tab" href="#completed">Cours terminés</a>
                        <a class="nav-link" data-toggle="tab" href="#cancelled">Cours annulés</a>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="upcoming">
                            <div class="row">
                                <div class="col">
                                    <div class="courses">
                                        <?php 
                                    $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
                                    if (!empty($upcomming_sessions)): 
                                        foreach ($upcomming_sessions as $session): 
                                            $course_id = $session->course_id;
                                            $group_number = $session->group_number;
                                            $session_date = $session->session_date;
                                            $session_date = date('M d, Y', strtotime($session->session_date));
                                            $status = $session->status;
                                            $slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
                                            $slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

                                            $table_name = $wpdb->prefix . 'courses';
                                            $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $course_id));
                                ?>
                                        <div class="course-card">
                                            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                                alt="Course Image" class="course-image">
                                            <span class="course-tag in-progress"><?php echo esc_html($status); ?></span>
                                            <h3 class="course-title">
                                                <?php echo esc_html($course->title); ?>
                                            </h3>
                                            <div class="course-info">
                                                <p class="date">
                                                    Groupe:
                                                    <?php echo esc_html($group_number);?>
                                                </p>
                                                <p class="date">
                                                    Date:
                                                    <?php echo esc_html($session_date);?>
                                                </p>
                                                <p class="date">
                                                    Temps 1:
                                                    <?php echo esc_html($slot_1);?>
                                                </p>
                                                <p class="date">
                                                    Temps 2:
                                                    <?php echo esc_html($slot_2);?>
                                                </p>
                                            </div>
                                            <div class="course-footer">
                                                <a href="<?php echo esc_url(site_url('/course/details/?session_id=' . $session->id . '&student_id=' . $student_id)); ?>"
                                                    class="course-btn">Voir les détails</a>
                                            </div>
                                        </div>
                                        <?php endforeach; else: ?>
                                        <p class="no-data">Aucun cours à venir.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="ongoing">
                            <div class="row">
                                <div class="col">
                                    <div class="courses">
                                        <?php 
                                    $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
                                    if (!empty($ongoing_sessions)): 
                                        foreach ($ongoing_sessions as $session): 
                                            $course_id = $session->course_id;
                                            $group_number = $session->group_number;
                                            $session_date = $session->session_date;
                                            $session_date = date('M d, Y', strtotime($session->session_date));
                                            $status = $session->status;
                                            $slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
                                            $slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

                                            $table_name = $wpdb->prefix . 'courses';
                                            $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $course_id));
                                ?>
                                        <div class="course-card">
                                            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                                alt="Course Image" class="course-image">
                                            <span class="course-tag in-progress"><?php echo esc_html($status); ?></span>
                                            <h3 class="course-title">
                                                <?php echo esc_html($course->title); ?>
                                            </h3>
                                            <div class="course-info">
                                                <p class="date">
                                                    Groupe:
                                                    <?php echo esc_html($group_number);?>
                                                </p>
                                                <p class="date">
                                                    Date:
                                                    <?php echo esc_html($session_date);?>
                                                </p>
                                                <p class="date">
                                                    Temps 1:
                                                    <?php echo esc_html($slot_1);?>
                                                </p>
                                                <p class="date">
                                                    Temps 2:
                                                    <?php echo esc_html($slot_2);?>
                                                </p>
                                            </div>
                                            <div class="course-footer">
                                                <a href="<?php echo esc_url(site_url('/course/details/?session_id=' . $session->id . '&student_id=' . $student_id)); ?>"
                                                    class="course-btn">Voir les détails</a>
                                            </div>
                                        </div>
                                        <?php endforeach; else: ?>
                                        <p class="no-data">Pas de cours en cours.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="completed">
                            <div class="row">
                                <div class="col">
                                    <div class="courses">
                                        <?php 
                                    $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
                                    if (!empty($completed_sessions)): 
                                        foreach ($completed_sessions as $session): 
                                            $course_id = $session->course_id;
                                            $group_number = $session->group_number;
                                            $session_date = $session->session_date;
                                            $session_date = date('M d, Y', strtotime($session->session_date));
                                            $status = $session->status;
                                            $slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
                                            $slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

                                            $table_name = $wpdb->prefix . 'courses';
                                            $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $course_id));
                                ?>
                                        <div class="course-card">
                                            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                                alt="Course Image" class="course-image">
                                            <span class="course-tag in-progress"><?php echo esc_html($status); ?></span>
                                            <h3 class="course-title">
                                                <?php echo esc_html($course->title); ?>
                                            </h3>
                                            <div class="course-info">
                                                <p class="date">
                                                    Groupe:
                                                    <?php echo esc_html($group_number);?>
                                                </p>
                                                <p class="date">
                                                    Date:
                                                    <?php echo esc_html($session_date);?>
                                                </p>
                                                <p class="date">
                                                    Temps 1:
                                                    <?php echo esc_html($slot_1);?>
                                                </p>
                                                <p class="date">
                                                    Temps 2:
                                                    <?php echo esc_html($slot_2);?>
                                                </p>
                                            </div>
                                            <div class="course-footer">
                                                <a href="<?php echo esc_url(site_url('/course/details/?session_id=' . $session->id . '&student_id=' . $student_id)); ?>"
                                                    class="course-btn">Voir les détails</a>
                                            </div>
                                        </div>
                                        <?php endforeach; else: ?>
                                        <p class="no-data">Aucun cours terminé.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="cancelled">
                            <div class="row">
                                <div class="col">
                                    <div class="courses">
                                        <?php 
                                    $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
                                    if (!empty($cancelled_sessions)): 
                                        foreach ($cancelled_sessions as $session): 
                                            $course_id = $session->course_id;
                                            $group_number = $session->group_number;
                                            $session_date = $session->session_date;
                                            $session_date = date('M d, Y', strtotime($session->session_date));
                                            $status = $session->status;
                                            $slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
                                            $slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

                                            $table_name = $wpdb->prefix . 'courses';
                                            $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $course_id));
                                ?>
                                        <div class="course-card">
                                            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                                alt="Course Image" class="course-image">
                                            <span class="course-tag in-progress"><?php echo esc_html($status); ?></span>
                                            <h3 class="course-title">
                                                <?php echo esc_html($course->title); ?>
                                            </h3>
                                            <div class="course-info">
                                                <p class="date">
                                                    Groupe:
                                                    <?php echo esc_html($group_number);?>
                                                </p>
                                                <p class="date">
                                                    Date:
                                                    <?php echo esc_html($session_date);?>
                                                </p>
                                                <p class="date">
                                                    Temps 1:
                                                    <?php echo esc_html($slot_1);?>
                                                </p>
                                                <p class="date">
                                                    Temps 2:
                                                    <?php echo esc_html($slot_2);?>
                                                </p>
                                            </div>
                                            <div class="course-footer">
                                                <a href="<?php echo esc_url(site_url('/course/details/?session_id=' . $session->id . '&student_id=' . $student_id)); ?>"
                                                    class="course-btn">Voir les détails</a>
                                            </div>
                                        </div>
                                        <?php endforeach; else: ?>
                                        <p class="no-data">Aucun cours annulé.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
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

<?php require_once(get_template_directory() . '/parent/templates/footer.php'); ?>