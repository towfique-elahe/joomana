<?php

/* Template Name: Admin | Dashboard */

// page title
global $pageTitle;
$pageTitle = 'Tableau De Bord';

require_once(get_template_directory() . '/admin/templates/header.php');

global $wpdb;

$teacher_table = $wpdb->prefix . 'teachers';
$student_table = $wpdb->prefix . 'students';
$parent_table = $wpdb->prefix . 'parents';
$course_table = $wpdb->prefix . 'courses';

$total_teachers = $wpdb->get_var("SELECT COUNT(*) FROM $teacher_table");
$total_in_review_teachers = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $teacher_table WHERE status = %s", 'En cours'));
$total_approved_teachers = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $teacher_table WHERE status = %s", 'Approuvé'));
$total_students = $wpdb->get_var("SELECT COUNT(*) FROM $student_table");
$total_parents = $wpdb->get_var("SELECT COUNT(*) FROM $parent_table");
$total_courses = $wpdb->get_var("SELECT COUNT(*) FROM $course_table");

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>

    <div id="adminDashboard" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Tableau de bord</h2>
            <div class="content-breadcrumb">
                <span class="active">Tableau de bord</span>
            </div>
        </div>

        <div class="content-section statistics">
            <h3 class="section-heading">
                <i class="fas fa-chart-bar"></i>
                Statistiques
            </h3>
            <div class="section-body">

                <!-- Total Teacher Count -->
                <a href="<?php echo home_url('/admin/teacher-management'); ?>" class="statistic-box total-teacher">
                    <h4 class="statistic-title">
                        <i class="fas fa-chalkboard-teacher"></i> Enseignants totaux
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_teachers); ?>
                    </p>
                </a>

                <!-- Total Approved Teacher Count -->
                <a href="<?php echo home_url('/admin/teacher-management'); ?>"
                    class="statistic-box total-approved-teacher">
                    <h4 class="statistic-title">
                        <i class="fas fa-user-check"></i> Enseignants agréés
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_approved_teachers); ?>
                    </p>
                </a>

                <!-- Total In Review Teacher Count -->
                <a href="<?php echo home_url('/admin/teacher-management'); ?>"
                    class="statistic-box total-in-review-teacher">
                    <h4 class="statistic-title">
                        <i class="fas fa-user-check"></i> Demandes des enseignants
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_in_review_teachers); ?>
                    </p>
                </a>

                <!-- Total Student Count -->
                <a href="<?php echo home_url('/admin/student-management'); ?>" class="statistic-box total-student">
                    <h4 class="statistic-title">
                        <i class="fas fa-user-graduate"></i> Elèves totaux
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_students); ?>
                    </p>
                </a>

                <!-- Total Parent Count -->
                <a href="<?php echo home_url('/admin/parent-management'); ?>" class="statistic-box total-parent">
                    <h4 class="statistic-title">
                        <i class="fas fa-users"></i> Parents totaux
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_parents); ?>
                    </p>
                </a>

                <!-- Total Course Count -->
                <a href="<?php echo home_url('/admin/course-management/courses'); ?>"
                    class="statistic-box total-course">
                    <h4 class="statistic-title">
                        <i class="fas fa-university"></i> Cours total
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_courses); ?>
                    </p>
                </a>

            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>