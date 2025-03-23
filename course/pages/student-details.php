<?php

/* Template Name: Course | Student Management */

// page title
global $pageTitle;
$pageTitle = "Détails Sur L'elèves";

require_once(get_template_directory() . '/course/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the current user
$user = wp_get_current_user();

// Get student_id from session
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {

    // Check the user's role and redirect accordingly
    if (in_array('teacher', (array) $user->roles)) {
        wp_redirect(home_url('/teacher/course-management/'));
        exit;
    } else {
        // Default redirection for other roles or if no role is matched
        wp_redirect(home_url());
        exit;
    }
}
$student_id = intval($_GET['student_id']);

// Get course_id from session
if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {

    // Check the user's role and redirect accordingly
    if (in_array('teacher', (array) $user->roles)) {
        wp_redirect(home_url('/teacher/course-management/'));
        exit;
    } else {
        // Default redirection for other roles or if no role is matched
        wp_redirect(home_url());
        exit;
    }
}
$session_id = intval($_GET['session_id']);

global $wpdb;

// for teacher users only
if (in_array('teacher', (array) $user->roles)) {

    // Fetch the student's details using the student_id for the teacher
    $student_table = $wpdb->prefix. 'students';
    $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $student_id));

    if (!$student) {
        // Handle case when the student does not exist
        wp_die("L'elèves demandé n'a pas pu être trouvé.");
    }
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/course/templates/sidebar.php'); ?>
    </div>
    <div id="courseStudentDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails Sur L'elèves</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/course/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/course/student-management'); ?>" class="breadcrumb-link">Gestion
                    elèves</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Détails Sur L'elèves</span>
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
                    </div>
                    <div class="profile-details">
                        <div class="row detail-row">
                            <span class="col detail-label">Date de naissance:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($student->date_of_birth); ?>
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
            </div>

        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/course/templates/footer.php'); ?>