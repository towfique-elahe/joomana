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

global $wpdb;

$course_sessions = $wpdb->get_results("
    SELECT * FROM {$wpdb->prefix}course_sessions 
    ORDER BY created_at DESC
");

// Function to get course title
function get_course_title($course_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'courses';

    // Fetch the course title where the course ID matches
    $course_title = $wpdb->get_var($wpdb->prepare(
        "SELECT title FROM $table_name WHERE id = %d",
        $course_id
    ));

    return $course_title ? $course_title : 'Course not found'; // Return title or fallback message
}

// Function to get enrolled students count
function get_enrolled_students_count($session_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'course_sessions';

    // Fetch the enrolled_students array where the session ID matches
    $enrolled_students = $wpdb->get_var($wpdb->prepare(
        "SELECT enrolled_students FROM $table_name WHERE id = %d",
        $session_id
    ));

    // Decode the JSON array if it's stored as a JSON string
    $enrolled_students_array = json_decode($enrolled_students, true);

    // Return the count of enrolled students or 0 if the array is empty or not found
    return is_array($enrolled_students_array) ? count($enrolled_students_array) : 0;
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
                        <input type="text" placeholder="Rechercher une session" onkeyup="filterUser()">
                    </div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Cours</th>
                        <th>Groupe</th>
                        <th>Elèves</th>
                        <th>Temp 1</th>
                        <th>Temp 2</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="list">

                    <?php if (!empty($course_sessions)) : ?>
                    <?php foreach ($course_sessions as $session) : ?>
                    <?php
                        $session_id  = $session->id;
                        $session_date  = $session->session_date;
                        $course_id = $session->course_id;
                        $teacher_id = $session->teacher_id;
                        $group_number = $session->group_number;
                        $course_title = get_course_title($course_id);
                        $enrolled_students_count = get_enrolled_students_count($session_id);
                        $slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
                        $slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

                        global $wpdb;
                        $teacher_table = $wpdb->prefix . 'teachers';
                        $teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $teacher_id));
                    ?>
                    <tr class="course-row">
                        <td class="name">
                            <a
                                href="<?php echo esc_url(home_url('/admin/session-management/session-details/?session_id=' . $session_id)); ?>">
                                <?php echo esc_html($session_date); ?>
                            </a>
                        </td>
                        <td class="name title">
                            <?php echo esc_html($course_title); ?> <br>
                            <a href="<?php echo esc_url(home_url('/admin/teacher-management/teacher-details/?id=' . $teacher_id)); ?>"
                                class="teacher">Prof:
                                <?php echo esc_html($teacher->first_name) . ' ' . esc_html($teacher->last_name); ?></a>
                        </td>
                        <td>
                            <?php echo esc_html($group_number); ?>
                        </td>
                        <td>
                            <?php echo esc_html($enrolled_students_count); ?>
                        </td>
                        <td>
                            <?php echo esc_html($slot_1); ?>
                        </td>
                        <td>
                            <?php echo esc_html($slot_2); ?>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/admin/session-management/session-details/?session_id=' . $session_id)); ?>"
                                class=" action-button edit">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="7" class="no-data">Aucune session trouvée.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>