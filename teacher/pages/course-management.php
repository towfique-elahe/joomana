<?php

/* Template Name: Teacher | Course Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion De Cours';

require_once(get_template_directory() . '/teacher/templates/header.php');

// function to get teacher's active courses
function get_teacher_active_courses($teacher_id) {
    global $wpdb;

    // Query to get the courses assigned to the teacher with status 'En cours'
    $courses = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT c.* 
             FROM {$wpdb->prefix}teacher_courses tc
             INNER JOIN {$wpdb->prefix}courses c ON tc.course_id = c.id
             WHERE tc.teacher_id = %d AND tc.status = %s",
            $teacher_id,
            'En cours'
        )
    );

    return $courses;
}

// function to get teacher's completed courses
function get_teacher_completed_courses($teacher_id) {
    global $wpdb;

    // Query to get the courses assigned to the teacher with status 'En cours'
    $courses = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT c.* 
             FROM {$wpdb->prefix}teacher_courses tc
             INNER JOIN {$wpdb->prefix}courses c ON tc.course_id = c.id
             WHERE tc.teacher_id = %d AND tc.status = %s",
            $teacher_id,
            'Complété'
        )
    );

    return $courses;
}

// Get the current user ID
$teacher_id = get_current_user_id();

// Get the teacher's active courses
$active_courses = get_teacher_active_courses($teacher_id);

// Get the teacher's completed courses
$completed_courses = get_teacher_completed_courses($teacher_id);

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/teacher/templates/sidebar.php'); ?>
    </div>
    <div id="teacherCourses" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion de cours</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/teacher/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion de cours</span>
            </div>
        </div>

        <div class="content-section">
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
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="completed">
                    <div class="row">
                        <div class="col">
                            <div class="courses">
                                <?php foreach ($completed_courses as $course): ?>
                                <div class="course-card">
                                    <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                        alt="Course Image" class="course-image">
                                    <span class="course-tag completed">Completed</span>
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
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>

<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>