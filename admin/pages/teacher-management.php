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
        // Retrieve the teacher record from the custom table to get the file paths
        $teacher = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $delete_item_id");

        if ($teacher) {
            // Delete the associated files from WordPress media
            $file_keys = [
                'upload_cv',
                'upload_doc1',
                'upload_doc2',
                'upload_doc3',
                'upload_doc4',
                'upload_doc5',
                'upload_video'
            ];

            // Loop through each file and delete the media
            foreach ($file_keys as $file_key) {
                if (!empty($teacher->$file_key)) {
                    // Get attachment ID based on file URL
                    $file_url = $teacher->$file_key;
                    $attachment_id = attachment_url_to_postid($file_url);
                    
                    if ($attachment_id) {
                        // Delete the media file from the WordPress uploads
                        wp_delete_attachment($attachment_id, true);  // Pass 'true' to force delete the file from the server
                    }
                }
            }

            // Delete the teacher record from the custom table
            $deleted = $wpdb->delete($table_name, ['id' => $delete_item_id], ['%d']);

            if ($deleted) {
                // Attempt to delete the WordPress user with the same ID
                if (wp_delete_user($delete_item_id)) {
                    $success_message = 'L\'enseignant et l\'utilisateur associé ont été supprimés avec succès, ainsi que les fichiers.';
                } else {
                    $error_message = 'L\'enseignant a été supprimé, mais l\'utilisateur associé n\'a pas pu être supprimé.';
                }
            } else {
                $error_message = 'Erreur lors de la suppression de l\'enseignant.';
            }
        } else {
            $error_message = 'Aucun enseignant trouvé avec cet ID.';
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
                    <button onclick="filterStatus('approuvé', this)" class="button approuvé">Approuvé</button>
                    <button onclick="filterStatus('en-cours', this)" class="button en-cours">En Cours</button>
                    <button onclick="filterStatus('en-révision', this)" class="button en-révision">En Révision</button>
                    <button onclick="filterStatus('rejeté', this)" class="button rejeté">Rejeté</button>
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
                    <tr class="teacher-row"
                        data-status="<?php echo strtolower(str_replace(' ', '-', $teacher->status)); ?>">
                        <td class="name">
                            <a href="#">
                                <?php echo esc_html($teacher->first_name) . ' ' . esc_html($teacher->last_name); ?>
                            </a>
                        </td>
                        <td>n/a</td>
                        <td>n/a</td>
                        <td class="payment">
                            <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                            0
                        </td>
                        <td>
                            <span class="status <?php echo strtolower(str_replace(' ', '-', $teacher->status)); ?>">
                                <?php echo esc_html($teacher->status); ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/admin/teacher-management/teacher-details/?id=' . $teacher->id)); ?>"
                                class="action-button edit">
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
                        <td colspan="7" class="no-data">Aucun professeur trouvé.</td>
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