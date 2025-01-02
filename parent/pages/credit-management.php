<?php

/* Template Name: Parent | Credit Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion De Crédit';

require_once(get_template_directory() . '/parent/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Query to fetch all users with the role 'teacher'
$args = [
    'role'    => 'teacher',
    'orderby' => 'display_name',
    'order'   => 'ASC',
];
$teachers = get_users($args);
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/parent/templates/sidebar.php'); ?>
    </div>
    <div id="adminTeacherManagement" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion de crédit</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/parent/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion de crédit</span>
            </div>
        </div>

        <!-- <div class="content-section user-list">
            <div class="search-bar">
                <input type="text" placeholder="Rechercher Un Professeur" onkeyup="filterUser()">
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Compétence</th>
                        <th>Attribuer Un Cours</th>
                        <th>Paiement Total</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="user-list">
                    <?php if (!empty($teachers)) : ?>
                    <?php foreach ($teachers as $teacher) : ?>
                    <?php
                            // Example of fetching custom data for each teacher
                            $skills = get_user_meta($teacher->ID, 'skills', true) ?: 'Non spécifié';
                            $courses = get_user_meta($teacher->ID, 'assigned_courses', true) ?: 0;
                            $payment = get_user_meta($teacher->ID, 'total_payment', true) ?: 0;
                            $status = get_user_meta($teacher->ID, 'status', true) ?: 'In Review';
                            ?>
                    <tr>
                        <td>
                            <?php echo esc_html($teacher->ID); ?>
                        </td>
                        <td class="user-name">
                            <a href="<?php echo esc_url(get_author_posts_url($teacher->ID)); ?>">
                                <?php echo esc_html($teacher->display_name); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($skills); ?>
                        </td>
                        <td>
                            <?php echo esc_html($courses); ?>
                        </td>
                        <td class="payment">
                            <i class="fa fa-eur" aria-hidden="true"></i>
                            <?php echo esc_html($payment); ?>
                        </td>
                        <td>
                            <span class="status <?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                                <?php echo esc_html($status); ?>
                            </span>
                        </td>
                        <td class="action-btn">
                            <a href="#">
                                <i class="fa fa-user-o" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="7">Aucun professeur trouvé.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div> -->
    </div>
</div>

<?php require_once(get_template_directory() . '/parent/templates/footer.php'); ?>