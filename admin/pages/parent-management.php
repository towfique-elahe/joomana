<?php

/* Template Name: Admin | Parent Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion Parents';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Query to fetch all users with the role 'parent'
global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'parents' table
$parents = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}parents");

// Get total childs
function get_total_child_count($parent) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'students';

    $child_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE parent_id = %d",
        $parent->id
    ));

    return (int) $child_count;
}

// Get total payments
function get_total_payments($parent) {
    global $wpdb;
    
    // Replace 'prefix_' with your actual database prefix
    $table_name = $wpdb->prefix . 'payments';

    // Query to get total payments for the parent
    $total = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(amount) FROM {$table_name} WHERE user_id = %d",
        $parent->id
    ));

    // Return total payments or 0 if no payments found
    return ($total) ? $total : 0;
}

// Delete Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'parents';
    $delete_item_id = intval($_POST['delete_item_id']);

    if ($delete_item_id > 0) {
        require_once ABSPATH . 'wp-admin/includes/user.php'; // Ensure function is available
        
        // Delete the parent record from the custom table
        $deleted = $wpdb->delete($table_name, ['id' => $delete_item_id], ['%d']);

        if ($deleted) {
            // Attempt to delete the WordPress user with the same ID
            if (wp_delete_user($delete_item_id)) {
                $success_message = 'Le parent et l\'utilisateur associé ont été supprimés avec succès.';
            } else {
                $error_message = 'Le parent a été supprimé, mais l\'utilisateur associé n\'a pas pu être supprimé.';
            }
        } else {
            $error_message = 'Erreur lors de la suppression du parent.';
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
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminParentManagement" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion Parents</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion Parents</span>
            </div>
        </div>

        <div class="content-section list">
            <div class="filter-container">
                <div class="filter-bar">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Rechercher Un Parent" onkeyup="filterUser()">
                    </div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Nombre d'enfants</th>
                        <th>Achat Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="list">
                    <?php if (!empty($parents)) : ?>
                    <?php foreach ($parents as $parent) : ?>
                    <?php
                        $total_children = get_total_child_count($parent);
                        $total_payments = get_total_payments($parent);
                    ?>
                    <tr>
                        <td class="name">
                            <a
                                href="<?php echo esc_url(home_url('/admin/parent-management/parent-details/?id=' . $parent->id)); ?>">
                                <?php echo esc_html($parent->first_name) . ' ' . esc_html($parent->last_name); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($total_children); ?>
                        </td>
                        <td class="payment">
                            <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                            <?php echo esc_html( intval($total_payments) ); ?>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/admin/parent-management/parent-details/?id=' . $parent->id)); ?>"
                                class="action-button edit">
                                <i class="fas fa-info-circle"></i>
                            </a>
                            <form method="post" class="delete-form">
                                <input type="hidden" name="delete_item_id" value="<?php echo esc_attr($parent->id); ?>">
                                <button type="button" class="action-button delete open-modal" data-modal="deleteUser">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="5">Aucun parent trouvé.</td>
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

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>