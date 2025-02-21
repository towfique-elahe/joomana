<?php

/* Template Name: Parent | Child Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion Des Enfants';

require_once(get_template_directory() . '/parent/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Query to fetch all users with the role 'student'
$args = [
    'role'    => 'student',
    'orderby' => 'display_name',
    'order'   => 'ASC',
];
$students = get_users($args);
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/parent/templates/sidebar.php'); ?>
    </div>
    <div id="adminStudentManagement" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion des Enfants</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/parent/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion des Enfants</span>
            </div>
        </div>

        <div class="content-section user-list">
            <div class="search-bar">
                <input type="text" placeholder="Rechercher Un Étudiant" onkeyup="filterUser()">
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'étudiant</th>
                        <th>Grade</th>
                        <th>Niveau</th>
                        <th>Inscrit</th>
                        <th>Paiement</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="user-list">
                    <?php if (!empty($students)) : ?>
                    <?php foreach ($students as $student) : ?>
                    <?php
                            // Example of fetching custom data for each student
                            $grade = get_user_meta($student->ID, 'grade', true) ?: 'Non spécifié';
                            $level = get_user_meta($student->ID, 'level', true) ?: 'Non spécifié';
                            $enrolled = get_user_meta($student->ID, 'enrolled', true) ? 'Oui' : 'Non';
                            $payment = get_user_meta($student->ID, 'total_payment', true) ?: '0';
                            ?>
                    <!-- <tr>
                        <td>
                            <?php echo esc_html($student->ID); ?>
                        </td>
                        <td class="user-name">
                            <a href="<?php echo esc_url(get_author_posts_url($student->ID)); ?>">
                                <?php echo esc_html($student->display_name); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($grade); ?>
                        </td>
                        <td>
                            <?php echo esc_html($level); ?>
                        </td>
                        <td>
                            <?php echo esc_html($enrolled); ?>
                        </td>
                        <td class="payment">
                            <i class="fa fa-eur" aria-hidden="true"></i>
                            <?php echo esc_html($payment); ?>
                        </td>
                        <td class="action-btn">
                            <a href="#">
                                <i class="fa fa-user-o" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr> -->
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="7">Aucun étudiant trouvé.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/parent/templates/footer.php'); ?>