<?php

/* Template Name: Course | Student Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion Étudiants';

require_once(get_template_directory() . '/course/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the current user
$user = wp_get_current_user();

// Get session_id from session
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

    // Query the custom table to get the student's teacher_id
    $sessions_table = $wpdb->prefix . 'course_sessions';
    $students_table = $wpdb->prefix . 'students';

    // Fetch the enrolled_students array where the session ID matches
    $enrolled_students = $wpdb->get_var($wpdb->prepare(
        "SELECT enrolled_students FROM $sessions_table WHERE id = %d",
        $session_id
    ));

    // Decode the JSON array if it's stored as a JSON string
    $enrolled_students_array = json_decode($enrolled_students, true);

    // If it's not an array or empty, return an empty array
    if (!is_array($enrolled_students_array) || empty($enrolled_students_array)) {
        return [];
    }

    // Convert student IDs to a comma-separated string for SQL query
    $placeholders = implode(',', array_fill(0, count($enrolled_students_array), '%d'));
    
    // Fetch student details
    $query = "SELECT * FROM $students_table WHERE id IN ($placeholders)";
    $prepared_query = $wpdb->prepare($query, ...$enrolled_students_array);

    $students = $wpdb->get_results($prepared_query);

}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/course/templates/sidebar.php'); ?>
    </div>
    <div id="courseStudentManagement" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion étudiants</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/course/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion étudiants</span>
            </div>
        </div>

        <div class="content-section list">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom d'étudiant</th>
                        <th>Grade</th>
                        <th>Niveau</th>
                        <th>Détails</th>
                    </tr>
                </thead>
                <tbody id="list">
                    <?php
                            if (!empty($students)) {
                                foreach ($students as $student) {
                    ?>
                    <tr>
                        <td class="name">
                            <a
                                href="<?php echo esc_url(home_url('/course/student-management/student-details/?student_id=' . $student->id . '&session_id=' . $session_id)); ?>">
                                <?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($student->grade); ?>
                        </td>
                        <td>
                            <?php echo esc_html($student->level); ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a
                                    href="<?php echo esc_url(home_url('/course/student-management/student-details/?student_id=' . $student->id . '&session_id=' . $session_id)); ?>">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php
                                }
                            } else {
                    ?>
                    <tr>
                        <td colspan="4">Aucun étudiant trouvé.</td>
                    </tr>
                    <?php
                                }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/course/templates/footer.php'); ?>