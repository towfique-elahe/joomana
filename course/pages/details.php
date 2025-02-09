<?php

/* Template Name: Course | Details */

// page title
global $pageTitle;
$pageTitle = 'Détails du cours';

require_once(get_template_directory() . '/course/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the current user
$user = wp_get_current_user();
$default_user_image = esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png');

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
$courses_table = $wpdb->prefix . 'courses';
$course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $courses_table WHERE id = %d", $course_id));

if (!$course) {
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



if (in_array('student', (array) $user->roles)) {
    // Query the custom table to get the student's teacher_id
    $student_courses_table  = $wpdb->prefix . 'student_courses'; // Ensure the table name is correct
    $teacher_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT teacher_id FROM $student_courses_table  WHERE student_id = %d AND course_id = %d",
            $user->id,
            $course_id
        )
    );

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
    <div id="courseDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails du cours</h2>
            <div class="content-breadcrumb">
                <?php 
                    if (current_user_can('student')) {
                ?>
                <a href="<?php echo home_url('/student/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <?php 
                    } elseif (current_user_can('teacher')) {
                ?>
                <a href="<?php echo home_url('/teacher/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <?php } ?>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <?php 
                    if (current_user_can('student')) {
                ?>
                <a href="<?php echo home_url('/student/course-management'); ?>" class="breadcrumb-link">Gestion de
                    cours</a>
                <?php 
                    } elseif (current_user_can('teacher')) {
                ?>
                <a href="<?php echo home_url('/teacher/course-management'); ?>" class="breadcrumb-link">Gestion de
                    cours</a>
                <?php } ?>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Détails du cours</span>
            </div>
        </div>

        <div class="content-section">
            <div class="row">
                <div class="col section">
                    <div class="row course-details">
                        <div class="col course-info">
                            <h4 class="course-title">
                                <?php echo esc_html($course->title);?>
                            </h4>

                            <!-- class schedule -->
                            <div class="col class-schedule">
                                <h4 class="schedule-title">Horaire des cours</h4>
                                <ul>
                                    <li>
                                        Date:
                                        <span class="value">
                                            <?php echo esc_html(date('M d, Y', strtotime($course->start_date))); ?>
                                        </span>
                                    </li>
                                    <li>
                                        Time:
                                        <span class="value">
                                            <?php echo esc_html($course->time_slot);?>
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <!-- teacher details -->
                            <div class="col teacher-details">
                                <h4 class="teacher-title">Détails de l'enseignant</h4>

                                <div class="row">
                                    <img src="<?php echo !empty($teacher->image) ? esc_url($teacher->image) : $default_user_image; ?>"
                                        alt="" class="teacher-image">

                                    <div class="col teacher-info">
                                        <h4 class="teacher-name">
                                            <?php echo esc_html($teacher->first_name) . ' ' . esc_html($teacher->last_name); ?>
                                        </h4>
                                        <p class="teacher-data">
                                            <?php echo esc_html($teacher->motivation_of_joining);?>
                                        </p>
                                        <p class="teacher-data">
                                            <?php echo esc_html($teacher->email);?>
                                        </p>
                                        <p class="teacher-data">
                                            <?php echo esc_html($teacher->phone);?>
                                        </p>
                                    </div>
                                </div>

                            </div>

                            <!-- meeting details -->
                            <div class="col meeting-details">
                                <h4 class="meeting-title">Réunion Zoom</h4>

                                <a href="" class="button">Rejoindre</a>
                            </div>

                        </div>

                        <div class="col student-list">
                            <h4 class="list-title">Liste des étudiants</h4>

                            <ul class="list">

                                <?php
                                        // Check if any student IDs were found
                                        if (!empty($enrolled_student_ids)) {
                                            // Output or process the enrolled students
                                            if (!empty($enrolled_students)) {
                                                foreach ($enrolled_students as $student) {
                                    ?>
                                <li class="row list-item">
                                    <img src="<?php echo !empty($student->image) ? esc_url($student->image) : $default_user_image; ?>"
                                        alt="" class="student-image">

                                    <div class="col student-info">
                                        <h5 class="student-name">
                                            <?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?>
                                        </h5>
                                        <p class="student-data">
                                            <?php echo esc_html($student->grade);?>
                                        </p>
                                    </div>
                                </li>
                                <?php
                                            }
                                        } else {
                                            echo "No student details found for the enrolled students.";
                                        }
                                    } else {
                                        echo "No students enrolled in this course for the given teacher.";
                                    }

                                ?>

                            </ul>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/course/templates/footer.php'); ?>