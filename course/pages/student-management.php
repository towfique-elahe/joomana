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

// Get course_id from session
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {

    // Check the user's role and redirect accordingly
    if (in_array('student', (array) $user->roles)) {
        wp_redirect(home_url('/student/course-management/'));
        exit;
    } elseif (in_array('teacher', (array) $user->roles)) {
        wp_redirect(home_url('/teacher/course-management/'));
        exit;
    } else {
        // Default redirection for other roles or if no role is matched
        wp_redirect(home_url());
        exit;
    }
}
$course_id = intval($_GET['course_id']);

global $wpdb;

// for teacher users only
if (in_array('teacher', (array) $user->roles)) {
    // Query the custom table to get the student's teacher_id
    $student_courses_table  = $wpdb->prefix . 'student_courses'; // Ensure the table name is correct
    $teacher_id = $user->id;

    // Fetch the teacher's details using the teacher_id for the student
    $teacher_table = $wpdb->prefix. 'teachers';
    $teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $teacher_id));

    // Fetch all student IDs enrolled in the course for the given teacher group
    $enrolled_student_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT student_id FROM $student_courses_table WHERE course_id = %d AND teacher_id = %d",
            $course_id,
            $teacher_id
        )
    );

    // Check if any student IDs were found
    if (!empty($enrolled_student_ids)) {
        // Fetch student details from the students table
        $students_table = $wpdb->prefix . 'students';
        $student_ids_placeholder = implode(',', array_map('intval', $enrolled_student_ids)); // Sanitize IDs

        $enrolled_students = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $students_table WHERE id IN ($student_ids_placeholder)"
            )
        );
    }
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

        <div class="content-section user-list">
            <div class="filter-container">
                <div class="filter-bar">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Rechercher Un Étudiant" onkeyup="filterUser()">
                    </div>
                </div>
            </div>

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
                        // Check if any student IDs were found
                        if (!empty($enrolled_student_ids)) {
                            // Output or process the enrolled students
                            if (!empty($enrolled_students)) {
                                foreach ($enrolled_students as $student) {
                    ?>
                    <tr>
                        <td class="name">
                            <a
                                href="<?php echo esc_url(home_url('/course/student-management/student-details/?id=' . $student->id)); ?>">
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
                                    href="<?php echo esc_url(home_url('/course/student-management/student-details/?id=' . $student->id)); ?>">
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
                        <td colspan="4">Aucun détail étudiant n'a été trouvé pour les étudiants inscrits.</td>
                    </tr>
                    <?php
                                }
                            } else {
                    ?>
                    <tr>
                        <td colspan="4">Pourtant, aucun étudiant n'est inscrit à ce cours pour vous.</td>
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