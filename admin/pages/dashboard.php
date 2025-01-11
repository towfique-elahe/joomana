<?php

/* Template Name: Admin | Dashboard */

// page title
global $pageTitle;
$pageTitle = 'Tableau De Bord';

require_once(get_template_directory() . '/admin/templates/header.php');

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
                <?php
                    function get_teacher_count() {
                        $user_query = new WP_User_Query(array(
                            'role' => 'teacher',
                            'fields' => 'ID',
                        ));
                        return $user_query->get_total();
                    }
                    $teacher_count = get_teacher_count();
                ?>
                <a href="<?php echo home_url('/admin/teacher-management'); ?>" class="statistic-box total-teacher">
                    <h4 class="statistic-title">
                        <i class="fas fa-chalkboard-teacher"></i> Enseignants totaux
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($teacher_count); ?>
                    </p>
                </a>

                <!-- Total Approved Teacher Count -->
                <?php
                    function get_approved_teacher_count() {
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'teachers';
                        $approved_count = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM $table_name WHERE status = %s",
                                'Approuvé'
                            )
                        );
                        return intval($approved_count);
                    }
                    $approved_teacher_count = get_approved_teacher_count();
                ?>
                <a href="<?php echo home_url('/admin/teacher-management'); ?>"
                    class="statistic-box total-approved-teacher">
                    <h4 class="statistic-title">
                        <i class="fas fa-user-check"></i> Enseignants agréés
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($approved_teacher_count); ?>
                    </p>
                </a>

                <!-- Total In Review Teacher Count -->
                <?php
                    function get_in_review_teacher() {
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'teachers';
                        $in_review_count = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM $table_name WHERE status = %s",
                                'En cours'
                            )
                        );
                        return intval($in_review_count);
                    }
                    $in_review_teacher_count = get_in_review_teacher();
                ?>
                <a href="<?php echo home_url('/admin/teacher-management'); ?>"
                    class="statistic-box total-in-review-teacher">
                    <h4 class="statistic-title">
                        <i class="fas fa-user-check"></i> Demandes des enseignants
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($in_review_teacher_count); ?>
                    </p>
                </a>

                <!-- Total Student Count -->
                <?php
                    function get_student_count() {
                        $user_query = new WP_User_Query(array(
                            'role' => 'student',
                            'fields' => 'ID',
                        ));
                        return $user_query->get_total();
                    }
                    $student_count = get_student_count();
                ?>
                <a href="<?php echo home_url('/admin/student-management'); ?>" class="statistic-box total-student">
                    <h4 class="statistic-title">
                        <i class="fas fa-user-graduate"></i> étudiants totaux
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($student_count); ?>
                    </p>
                </a>

                <!-- Total Parent Count -->
                <?php
                    function get_parent_count() {
                        $user_query = new WP_User_Query(array(
                            'role' => 'parent',
                            'fields' => 'ID',
                        ));
                        return $user_query->get_total();
                    }
                    $parent_count = get_parent_count();
                ?>
                <a href="<?php echo home_url('/admin/parent-management'); ?>" class="statistic-box total-parent">
                    <h4 class="statistic-title">
                        <i class="fas fa-users"></i> Parents totaux
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($parent_count); ?>
                    </p>
                </a>

                <!-- Total Course Count -->
                <?php
                    function get_course_count() {
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'courses';
                        $course_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                        return intval($course_count);
                    }
                    $total_course_count = get_course_count();
                ?>
                <a href="<?php echo home_url('/admin/course-management/courses'); ?>"
                    class="statistic-box total-course">
                    <h4 class="statistic-title">
                        <i class="fas fa-university"></i> cours total
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_course_count); ?>
                    </p>
                </a>

            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>