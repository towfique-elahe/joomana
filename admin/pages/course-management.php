<?php

/* Template Name: Admin | Course Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion De Cours';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Query to fetch all users with the role 'course'
global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'courses' table
$courses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}courses");



// Delete Logic

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'courses';
    $delete_item_id = intval($_POST['delete_item_id']);

    if ($delete_item_id > 0) {
        // Get the course details, assuming the course has an image field
        $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $delete_item_id));

        if ($course) {
            // Check if there's an image and delete it
            if (!empty($course->image)) {
                // Get attachment ID from the URL and delete the file
                $old_image_id = attachment_url_to_postid($course->image);
                if ($old_image_id) {
                    wp_delete_attachment($old_image_id, true); // true to delete permanently
                }
            }

            // Delete the course course from the database
            $deleted = $wpdb->delete($table_name, ['id' => $delete_item_id], ['%d']);

            if ($deleted) {
                $success_message = 'Le sujet a été supprimé avec succès.';
            } else {
                $error_message = 'Erreur lors de la suppression du sujet.';
            }
        } else {
            $error_message = 'Sujet non trouvé.';
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

    <div id="adminCourseManagement" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion de cours</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion de cours</span>
            </div>
        </div>

        <div class="content-section list">
            <div class="filter-container">
                <a href="<?php echo home_url('/admin/course-management/courses/add-course/'); ?>"
                    class="button add-button">
                    <i class="fas fa-plus-circle"></i>
                    Ajouter un Cours
                </a>

                <div class="filter-bar">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Rechercher Un Cours" onkeyup="filterUser()">
                    </div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Grade</th>
                        <th>Niveau</th>
                        <th>Start Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="list">

                    <?php if (!empty($courses)) : ?>
                    <?php foreach ($courses as $course) : ?>
                    <tr class="course-row">
                        <td class="name title">
                            <a href="#">
                                <?php echo esc_html($course->title); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($course->category); ?>
                        </td>
                        <td>
                            <?php echo esc_html($course->grade); ?>
                        </td>
                        <td>
                            <?php echo esc_html($course->level); ?>
                        </td>
                        <td>
                            <?php echo esc_html($course->start_date); ?>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/admin/course-management/courses/edit-course/?edit_item_id=' . $course->id)); ?>"
                                class="action-button edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="post" class="delete-form">
                                <input type="hidden" name="delete_item_id" value="<?php echo esc_attr($course->id); ?>">
                                <button type="button" class="action-button delete open-modal">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="6" class="no-data">Aucun professeur trouvé.</td>
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