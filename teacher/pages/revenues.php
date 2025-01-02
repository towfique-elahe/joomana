<?php

/* Template Name: Teacher | Revenues */

// page title
global $pageTitle;
$pageTitle = 'Revenus';

require_once(get_template_directory() . '/teacher/templates/header.php');
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/teacher/templates/sidebar.php'); ?>
    </div>
    <div id="mainContent" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Revenus</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/teacher/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Revenus</span>
            </div>
        </div>


    </div>
</div>

<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>