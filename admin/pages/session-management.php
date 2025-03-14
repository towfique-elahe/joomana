<?php

/* Template Name: Admin | Session Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion Des Sessions';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Query to fetch all users with the role 'course'
global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'courses' table
$courses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}courses");

// Function to get group count, course wise
function get_group_count_by_course_id($course_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_courses';

    // Query to get the count of distinct group numbers for the given course_id
    $group_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT group_number) FROM $table_name WHERE course_id = %d",
            $course_id
        )
    );

    return intval($group_count);
}

// Function to get teacher count, course wise
function get_teacher_count_by_course_id($course_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_courses';

    // Query to get the count of distinct teacher IDs for the given course_id
    $teacher_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT teacher_id) FROM $table_name WHERE course_id = %d",
            $course_id
        )
    );

    return intval($teacher_count);
}

// Function to get student count, course wise
function get_student_count_by_course_id($course_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_courses';

    // Query to get the count of distinct student IDs for the given course_id
    $student_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT student_id) FROM $table_name WHERE course_id = %d",
            $course_id
        )
    );

    return intval($student_count);
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>

    <div id="adminSessionManagement" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion des sessions</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion des sessions</span>
            </div>
        </div>

        <div class="content-section list">
            <div class="filter-container">
                <div class="filter-bar">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Rechercher Un Cours" onkeyup="filterUser()">
                    </div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Récurrent</th>
                        <th>Groupes</th>
                        <th>Enseignants</th>
                        <th>Étudiants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="list">

                    <?php if (!empty($courses)) : ?>
                    <?php foreach ($courses as $course) : ?>
                    <?php
                        $course_id = $course->id;
                        $group_count = get_group_count_by_course_id($course_id);
                        $teacher_count = get_teacher_count_by_course_id($course_id);
                        $student_count = get_student_count_by_course_id($course_id);
                    ?>
                    <tr class="course-row">
                        <td class="name title">
                            <a
                                href="<?php echo esc_url(home_url('/admin/session-management/courses/groups/?course_id=' . $course->id)); ?>">
                                <?php echo esc_html($course->title); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($course->is_recurring ? 'Oui' : 'Non'); ?>
                        </td>
                        <td>
                            <?php echo esc_html($group_count); ?>
                        </td>
                        <td>
                            <?php echo esc_html($teacher_count); ?>
                        </td>
                        <td>
                            <?php echo esc_html($student_count); ?>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/admin/session-management/courses/groups/?course_id=' . $course->id)); ?>"
                                class="action-button edit">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="6" class="no-data">Aucun cours trouvé.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>