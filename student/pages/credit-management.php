<?php

/* Template Name: Student | Credit Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion De Crédit';

require_once(get_template_directory() . '/student/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Query to fetch all users with the role 'student'
global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'students' table
$students = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}students");
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/student/templates/sidebar.php'); ?>
    </div>
    <div id="adminStudentManagement" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion De Crédit</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/student/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion De Crédit</span>
            </div>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/student/templates/footer.php'); ?>