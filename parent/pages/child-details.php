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
    wp_die("L'étudiant demandé n'a pas pu être trouvé.");
}

// function to get student's active courses
function get_student_active_courses($student_id) {
    global $wpdb;

    // Query to get the courses assigned to the student with status 'En cours'
    $courses = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT c.* 
             FROM {$wpdb->prefix}student_courses sc
             INNER JOIN {$wpdb->prefix}courses c ON sc.course_id = c.id
             WHERE sc.student_id = %d AND sc.status = %s",
            $student_id,
            'En cours'
        )
    );

    return $courses;
}

// get current student
$student_id = $student->id;

// function to get student's completed courses
function get_student_completed_courses($student_id) {
    global $wpdb;

    // Query to get the courses assigned to the student with status 'En cours'
    $courses = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT c.* 
             FROM {$wpdb->prefix}student_courses sc
             INNER JOIN {$wpdb->prefix}courses c ON sc.course_id = c.id
             WHERE sc.student_id = %d AND sc.status = %s",
            $student_id,
            'Complété'
        )
    );

    return $courses;
}

// Get the student's active courses
$active_courses = get_student_active_courses($student_id);

// Get the student's completed courses
$completed_courses = get_student_completed_courses($student_id);

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
                            <span class="col detail-label">Grade:</span>
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
                        <a class="nav-link active" data-toggle="tab" href="#active">Cours en cours</a>
                        <a class="nav-link" data-toggle="tab" href="#completed">Cours terminés</a>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="active">
                            <div class="row">
                                <div class="col">
                                    <div class="courses">
                                        <?php 
                                    $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
                                    if (!empty($active_courses)): 
                                        foreach ($active_courses as $course): 
                                ?>
                                        <div class="course-card">
                                            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                                alt="Course Image" class="course-image">
                                            <span class="course-tag in-progress">En cours</span>
                                            <h3 class="course-title">
                                                <?php echo esc_html($course->title); ?>
                                            </h3>
                                            <div class="course-info">
                                                <p class="date">
                                                    <?php echo esc_html(date('M d, Y', strtotime($course->start_date))); ?>
                                                </p>
                                                <p class="time">
                                                    <?php echo esc_html($course->time_slot); ?>
                                                </p>
                                            </div>
                                            <div class="course-footer">
                                                <a href="<?php echo site_url('/course/details/?course_id=' . $course->id); ?>"
                                                    class="course-btn">Voir les détails</a>
                                            </div>
                                        </div>
                                        <?php endforeach; else: ?>
                                        <p class="no-data">Aucun cours en cours.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="completed">
                            <div class="row">
                                <div class="col">
                                    <div class="courses">
                                        <?php if (!empty($completed_courses)): 
                                foreach ($completed_courses as $course): ?>
                                        <div class="course-card">
                                            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                                alt="Course Image" class="course-image">
                                            <span class="course-tag completed">Complété</span>
                                            <h3 class="course-title">
                                                <?php echo esc_html($course->title); ?>
                                            </h3>
                                            <div class="course-info">
                                                <p class="date">
                                                    <?php echo esc_html(date('M d, Y', strtotime($course->start_date))); ?>
                                                </p>
                                                <p class="time">
                                                    <?php echo esc_html($course->time_slot); ?>
                                                </p>
                                            </div>
                                            <div class="course-footer">
                                                <a href="<?php echo site_url('/course/details/?course_id=' . $course->id); ?>"
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
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/parent/templates/footer.php'); ?>