<?php

/* Template Name: Admin | Session Groups */

// page title
global $pageTitle;
$pageTitle = 'Groupes De Séances';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get course id from session
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    wp_redirect(home_url('/admin/session-management/courses/'));
    exit;
}
$course_id = intval($_GET['course_id']);

// Query to fetch all users with the role 'course'
global $wpdb; // Access the global $wpdb object for database queries

// Fetch course wise groups
$groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}teacher_courses WHERE course_id = $course_id");

function get_teacher_name($teacher_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'teachers'; // Adjusted table name with prefix

    // Query to get the teacher's first and last name
    $teacher = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT first_name, last_name FROM $table_name WHERE id = %d",
            $teacher_id
        )
    );

    // Return the full name if found, otherwise return "Teacher not found"
    if ($teacher) {
        return $teacher->first_name . ' ' . $teacher->last_name;
    } else {
        return "Teacher not found";
    }
}

// Function to get student count based on course_id and group_number
function get_student_count_by_course_and_group($course_id, $group_number) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_courses';

    // Query to get the count of distinct student IDs for the given course_id and group_number
    $student_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT student_id) FROM $table_name WHERE course_id = %d AND group_number = %d",
            $course_id,
            $group_number
        )
    );

    return intval($student_count);
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>

    <div id="adminSessionGroups" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Groupes De Séances</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/session-management/courses/'); ?>" class="breadcrumb-link">Gestion
                    des
                    sessions</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Groupes De Séances</span>
            </div>
        </div>

        <div class="content-section list">

            <table class="table">
                <thead>
                    <tr>
                        <th>Groupe</th>
                        <th>Professeur</th>
                        <th>Étudiants</th>
                        <th>Statut</th>
                        <th>Date d'attribution</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="list">

                    <?php if (!empty($groups)) : ?>
                    <?php foreach ($groups as $group) : ?>
                    <?php
                        $teacher_id = $group->teacher_id;
                        $group_number = $group->group_number;
                        $teacher_name = get_teacher_name($teacher_id);
                        $student_count = get_student_count_by_course_and_group($course_id, $group_number);
                    ?>
                    <tr class="course-row">
                        <td class="name title">
                            <a
                                href="<?php echo esc_url(home_url('/admin/session-management/courses/groups/group-details/?group_number=' . $group->group_number . '&course_id=' . $course_id)); ?>">
                                # <?php echo esc_html($group->group_number); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($teacher_name); ?>
                        </td>
                        <td>
                            <?php echo esc_html($student_count); ?>
                        </td>
                        <td>
                            <?php echo esc_html($group->status); ?>
                        </td>
                        <td>
                            <?php echo esc_html(date('d F, Y', strtotime($group->assigned_date))); ?>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/admin/session-management/courses/groups/group-details/?group_number=' . $group->group_number . '&course_id=' . $course_id)); ?>"
                                class="action-button edit">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="6" class="no-data">Aucun groupe trouvé pour le cours.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>