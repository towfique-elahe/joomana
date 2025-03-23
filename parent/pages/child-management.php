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

$parent_id = get_current_user_id(); // Get currently logged-in parent ID

// Query the custom 'students' table for current parents children
$childs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}students WHERE parent_id = $parent_id");

// Delete Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    $delete_item_id = intval($_POST['delete_item_id']);

    if ($delete_item_id > 0) {
        require_once ABSPATH . 'wp-admin/includes/user.php'; // Ensure function is available
        
        // Attempt to delete the WordPress user
        if (wp_delete_user($delete_item_id)) {
            $success_message = 'L\'utilisateur a été supprimé avec succès.';
        } else {
            $error_message = 'Erreur lors de la suppression de l\'utilisateur.';
        }

        // Prevent form resubmission
        wp_redirect($_SERVER['REQUEST_URI']);
        exit;
    } else {
        $error_message = 'ID invalide.';
    }
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/parent/templates/sidebar.php'); ?>
    </div>
    <div id="parentChildManagement" class="main-content">
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

        <div class="content-section list">
            <div class="filter-container">
                <!-- Add Child -->
                <a href="<?php echo home_url('/parent/child-management/add-child/'); ?>" class="button add-button">
                    <i class="fas fa-plus-circle"></i>
                    Ajouter un enfant
                </a>
            </div>

            <div class="filter-container">
                <div class="select-filters">
                    <!-- Grade Filter -->
                    <div class="custom-select-wrapper">
                        <select id="grade-filter" onchange="filterBySelect()">
                            <option value="all">Tous les classe</option>
                            <?php
                                global $wpdb;
                                $grades = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_grades");
                                if ($grades) {
                                    foreach ($grades as $grade) {
                                        echo '<option value="' . esc_attr($grade->grade) . '">' . esc_html($grade->grade) . '</option>';
                                    }
                                } else {
                                    echo '<option disabled>No classe found</option>';
                                }
                            ?>
                        </select>
                        <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                    </div>

                    <!-- Level Filter -->
                    <div class="custom-select-wrapper">
                        <select id="level-filter" onchange="filterBySelect()">
                            <option value="all">Tous les niveaux</option>
                            <?php
                                global $wpdb;
                                $levels = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_levels");
                                if ($levels) {
                                    foreach ($levels as $level) {
                                        echo '<option value="' . esc_attr($level->level) . '">' . esc_html($level->level) . '</option>';
                                    }
                                } else {
                                    echo '<option disabled>No level found</option>';
                                }
                            ?>
                        </select>
                        <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                    </div>
                </div>

                <!-- Search Filter -->
                <div class="filter-bar">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Rechercher un enfant" onkeyup="filterUser()">
                    </div>
                </div>
            </div>

            <!-- Child Table -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom de l'enfant</th>
                        <th>Classe</th>
                        <th>Niveau</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="list">
                    <?php if (!empty($childs)) : ?>
                    <?php foreach ($childs as $child) : ?>
                    <tr data-grade="<?php echo strtolower($child->grade); ?>"
                        data-level="<?php echo strtolower($child->level); ?>">
                        <td class="name">
                            <a
                                href="<?php echo esc_url(home_url('/parent/child-management/child-details/?id=' . $child->id)); ?>">
                                <?php echo esc_html($child->first_name) . ' ' . esc_html($child->last_name); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($child->grade); ?>
                        </td>
                        <td>
                            <?php echo esc_html($child->level); ?>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/parent/child-management/child-details/?id=' . $child->id)); ?>"
                                class="action-button edit">
                                <i class="fas fa-info-circle"></i>
                            </a>
                            <form method="post" class="delete-form">
                                <input type="hidden" name="delete_item_id" value="<?php echo esc_attr($child->id); ?>">
                                <button type="button" class="action-button delete open-modal" data-modal="deleteUser">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="5" class="no-data">Aucun enfant trouvé.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteUser" class="modal">
    <div class="modal-content">
        <span class="modal-close">
            <i class="fas fa-times"></i>
        </span>
        <h4 class="modal-heading">
            <i class="fas fa-exclamation-triangle" style="color: crimson"></i> Avertissement
        </h4>
        <p class="modal-info">Êtes-vous sûr de vouloir supprimer ce niveau ?</p>
        <div class="modal-actions">
            <button id="confirmBtn" class="modal-button delete">Supprimer</button>
            <button id="cancelBtn" class="modal-button cancel">Annuler</button>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/parent/templates/footer.php'); ?>