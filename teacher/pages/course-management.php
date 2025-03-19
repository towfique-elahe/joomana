<?php

/* Template Name: Teacher | Course Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion De Cours';

require_once(get_template_directory() . '/teacher/templates/header.php');

// Get the current user ID
$teacher_id = get_current_user_id();

global $wpdb;
$sessions_table = $wpdb->prefix . 'course_sessions';

$upcomming_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
         WHERE teacher_id = %d 
         AND status IN ('upcoming')",
        $teacher_id
    )
);

$ongoing_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
         WHERE teacher_id = %d 
         AND status IN ('ongoing')",
        $teacher_id
    )
);

$completed_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
         WHERE teacher_id = %d 
         AND status IN ('completed')",
        $teacher_id
    )
);

$cancelled_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
         WHERE teacher_id = %d 
         AND status IN ('cancelled')",
        $teacher_id
    )
);

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
                                        <a href="<?php echo site_url('/course/details/?session_id=' . $session->id); ?>"
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
                                        <a href="<?php echo site_url('/course/details/?session_id=' . $session->id); ?>"
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
                                        <a href="<?php echo site_url('/course/details/?session_id=' . $session->id); ?>"
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
                                        <a href="<?php echo site_url('/course/details/?session_id=' . $session->id); ?>"
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

<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>