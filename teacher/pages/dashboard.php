<?php

/* Template Name: Teacher | Dashboard */

// page title
global $pageTitle;
$pageTitle = 'Tableau De Bord';

require_once(get_template_directory() . '/teacher/templates/header.php');

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/teacher/templates/sidebar.php'); ?>
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
                <i class="fa fa-bar-chart section-icon" aria-hidden="true"></i>
                Statistiques
            </h3>
            <div class="section-body">

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
                <a href="<?php echo home_url('/teacher/student-management/'); ?>" class="statistic-box">
                    <h4 class="statistic-title">Nombre total d'étudiants</h4>
                    <p class="statistic-value">
                        <?php echo esc_html($student_count); ?>
                    </p>
                </a>

                <a href="#" class="statistic-box">
                    <h4 class="statistic-title">Nombre total de cours</h4>
                    <p class="statistic-value">0</p>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>