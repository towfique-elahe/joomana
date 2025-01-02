<?php

/* Template Name: Student | Course Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion De Cours';

require_once(get_template_directory() . '/student/templates/header.php');
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/student/templates/sidebar.php'); ?>
    </div>
    <div id="mainContent" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion de cours</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/student/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion de cours</span>
            </div>
        </div>


    </div>
</div>

<?php require_once(get_template_directory() . '/student/templates/footer.php'); ?>