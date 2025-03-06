<?php

/* Template Name: Parent | Dashboard */

// page title
global $pageTitle;
$pageTitle = 'Tableau De Bord';

require_once(get_template_directory() . '/parent/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the current user
$user = wp_get_current_user();
$parent_id = $user->ID;

global $wpdb;

// Query to count total courses
$child_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}students WHERE parent_id = %d",
    $parent_id
));

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/parent/templates/sidebar.php'); ?>
    </div>
    <div id="parentDashboard" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Tableau de bord</h2>
            <div class="content-breadcrumb">
                <span class="active">Tableau de bord</span>
            </div>
        </div>

        <div class="content-section statistics">
            <h3 class="section-heading">
                <i class="far fa-chart-bar"></i>
                Statistiques
            </h3>
            <div class="section-body">

                <a href="<?php echo home_url('/parent/child-management/'); ?>" class="statistic-box total-child">
                    <h4 class="statistic-title">
                        <i class="fas fa-user-graduate"></i> Total enfant
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($child_count); ?>
                    </p>
                </a>

            </div>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/parent/templates/footer.php'); ?>