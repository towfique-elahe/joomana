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
                <?php
                // Function to get the count of users with the role 'teacher'
                function get_teacher_count() {
                    $user_query = new WP_User_Query(array(
                        'role' => 'teacher',
                        'fields' => 'ID',
                    ));
                    return $user_query->get_total();
                }
                
                // Output the count
                $teacher_count = get_teacher_count();
                ?>
                <a href="<?php echo home_url('/admin/teacher-management'); ?>" class="statistic-box">
                    <h4 class="statistic-title">
                        <i class="fas fa-chalkboard-teacher"></i> Enseignants totaux
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($teacher_count); ?>
                    </p>
                </a>

                <?php
                    function get_approved_teacher_count() {
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'teachers';
                        $approved_count = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM $table_name WHERE status = %s",
                                'approved'
                            )
                        );
                        return intval($approved_count);
                    }
                    $approved_teacher_count = get_approved_teacher_count();
                ?>

                <a href="<?php echo home_url('/admin/teacher-management'); ?>" class="statistic-box">
                    <h4 class="statistic-title">
                        <i class="fas fa-user-check"></i> Enseignants agréés
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($approved_teacher_count); ?>
                    </p>
                </a>


                <?php
                // Function to get the count of users with the role 'student'
                function get_student_count() {
                    $user_query = new WP_User_Query(array(
                        'role' => 'student',
                        'fields' => 'ID',
                    ));
                
                    return $user_query->get_total();
                }
                
                // Output the count
                $student_count = get_student_count();
                ?>
                <a href="<?php echo home_url('/admin/student-management'); ?>" class="statistic-box">
                    <h4 class="statistic-title">
                        <i class="fas fa-user-graduate"></i> étudiants totaux
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($student_count); ?>
                    </p>
                </a>

                <?php
                // Function to get the count of users with the role 'parent'
                function get_parent_count() {
                    $user_query = new WP_User_Query(array(
                        'role' => 'parent',
                        'fields' => 'ID',
                    ));
                
                    return $user_query->get_total();
                }
                
                // Output the count
                $parent_count = get_parent_count();
                ?>
                <a href="<?php echo home_url('/admin/parent-management'); ?>" class="statistic-box">
                    <h4 class="statistic-title">
                        <i class="fas fa-users"></i> Parents totaux
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($parent_count); ?>
                    </p>
                </a>

                <?php
                    function get_course_count() {
                        $course_query = new WP_User_Query(array(
                            'fields' => 'ID',
                        ));
                    
                        return $course_query->get_total();
                    }
                    $course_count = get_course_count();
                ?>

                <a href="<?php echo home_url('/admin/course-management/courses'); ?>" class="statistic-box">
                    <h4 class="statistic-title">
                        <i class="fas fa-university"></i> cours total
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($course_count); ?>
                    </p>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>