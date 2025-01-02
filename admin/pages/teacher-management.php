<?php

/* Template Name: Admin | Teacher Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion Enseignants';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Query to fetch all users with the role 'teacher'
global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'teachers' table
$teachers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}teachers");



// Delete Logic

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'teachers';
    $delete_item_id = intval($_POST['delete_item_id']);

    if ($delete_item_id > 0) {
        $deleted = $wpdb->delete($table_name, ['id' => $delete_item_id], ['%d']);

        if ($deleted) {
            $success_message = 'Le niveau a été supprimé avec succès.';
        } else {
            $error_message = 'Erreur lors de la suppression du niveau.';
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
    <div id="adminTeacherManagement" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion Enseignants</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion Enseignants</span>
            </div>
        </div>

        <div class="content-section list">
            <div class="filter-container">
                <!-- Status Filter Buttons -->
                <div class="status-filter">
                    <button onclick="filterStatus('all', this)" class="button all active">Tous</button>
                    <button onclick="filterStatus('approved', this)" class="button approved">Approuvé</button>
                    <button onclick="filterStatus('in-review', this)" class="button in-review">En revue</button>
                    <button onclick="filterStatus('on-hold', this)" class="button on-hold">En attente</button>
                    <button onclick="filterStatus('rejected', this)" class="button rejected">Rejeté</button>
                </div>

                <div class="filter-bar">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Rechercher Un Professeur" onkeyup="filterUser()">
                    </div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Compétence</th>
                        <th>Attribuer Un Cours</th>
                        <th>Paiement Total</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="list">

                    <?php if (!empty($teachers)) : ?>
                    <?php foreach ($teachers as $teacher) : ?>
                    <?php
                $skills = $teacher->skills ?? 'n/a';
                $courses = $teacher->assigned_courses ?? 'n/a';
                $payment = $teacher->total_payment ?? 0;
                $status = $teacher->status;
                $status_in_french = '';
                switch ($status) {
                    case 'in review':
                        $status_in_french = 'En revue';
                        break;
                    case 'on hold':
                        $status_in_french = 'En attente';
                        break;
                    case 'rejected':
                        $status_in_french = 'Rejeté';
                        break;
                    case 'approved':
                        $status_in_french = 'Approuvé';
                        break;
                    default:
                        $status_in_french = 'n/a';
                        break;
                }
            ?>
                    <tr class="teacher-row" data-status="<?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                        <td class="name">
                            <a href="#">
                                <?php echo esc_html($teacher->first_name) . ' ' . esc_html($teacher->last_name); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($skills); ?>
                        </td>
                        <td>
                            <?php echo esc_html($courses); ?>
                        </td>
                        <td class="payment">
                            <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                            <?php echo esc_html($payment); ?>
                        </td>
                        <td>
                            <span class="status <?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                                <?php echo esc_html($status_in_french); ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <a href="#" class="action-button edit">
                                <i class="fas fa-info-circle"></i>
                            </a>
                            <form method="post" class="delete-form">
                                <input type="hidden" name="delete_item_id"
                                    value="<?php echo esc_attr($teacher->id); ?>">
                                <button type="button" class="action-button delete open-modal">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
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
        </div>

    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="modal" class="modal">
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