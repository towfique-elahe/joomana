<?php

/* Template Name: Parent | Payments */

// page title
global $pageTitle;
$pageTitle = 'Paiements';

require_once(get_template_directory() . '/parent/templates/header.php');
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/parent/templates/sidebar.php'); ?>
    </div>
    <div id="mainContent" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Paiements</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/parent/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Paiements</span>
            </div>
        </div>


    </div>
</div>

<?php require_once(get_template_directory() . '/parent/templates/footer.php'); ?>