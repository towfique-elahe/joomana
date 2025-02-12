<?php

/* Template Name: Student | Progress */

// page title
global $pageTitle;
$pageTitle = 'Progrès';

require_once(get_template_directory() . '/student/templates/header.php');
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/student/templates/sidebar.php'); ?>
    </div>
    <div id="mainContent" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Progrès</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/student/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Progrès</span>
            </div>
        </div>


    </div>
</div>

<?php require_once(get_template_directory() . '/student/templates/footer.php'); ?>