<?php

/* Template Name: Admin | Course Categories */

// Page title
global $pageTitle;
$pageTitle = 'Catégories de Cours';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'course_categories' table
$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");

ob_start();

$error_message = ''; // Initialize error message variable
$success_message = ''; // Initialize success message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course_category'])) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_categories';

    // Sanitize user input
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $uploaded_image_path = '';

    if (!empty($category)) {
        // Check if the category already exists
        $existing_category = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE category = %s", $category));

        if ($existing_category > 0) {
            $error_message = 'La catégorie existe déjà dans la base de données.';
        } else {
            // Handle file upload to WordPress Media Library
            if (isset($_FILES['upload_image']) && $_FILES['upload_image']['error'] === UPLOAD_ERR_OK) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                $allowed_types = ['image/jpeg', 'image/png'];

                if (in_array($_FILES['upload_image']['type'], $allowed_types)) {
                    if ($_FILES['upload_image']['size'] <= 2 * 1024 * 1024) {
                        // Upload the file to WordPress Media Library
                        $uploaded_file = wp_handle_upload($_FILES['upload_image'], ['test_form' => false]);
                        
                        if ($uploaded_file && !isset($uploaded_file['error'])) {
                            // Insert the uploaded file into the media library
                            $file = $uploaded_file['file'];
                            $attachment = array(
                                'guid'           => $uploaded_file['url'], 
                                'post_mime_type' => $_FILES['upload_image']['type'],
                                'post_title'     => sanitize_file_name($_FILES['upload_image']['name']),
                                'post_content'   => '',
                                'post_status'    => 'inherit'
                            );
                            $attachment_id = wp_insert_attachment($attachment, $file);
                            if (!is_wp_error($attachment_id)) {
                                // Generate metadata for the attachment
                                require_once(ABSPATH . 'wp-admin/includes/image.php');
                                $attachment_data = wp_generate_attachment_metadata($attachment_id, $file);
                                wp_update_attachment_metadata($attachment_id, $attachment_data);
                                $uploaded_image_path = wp_get_attachment_url($attachment_id); // URL to save in DB
                            } else {
                                $error_message = 'Erreur lors de l\'insertion de l\'image dans la bibliothèque de médias.';
                            }
                        } else {
                            $error_message = 'Erreur lors du téléchargement de l\'image : ' . $uploaded_file['error'];
                        }
                    } else {
                        $error_message = 'La taille de l\'image ne doit pas dépasser 2 Mo.';
                    }
                } else {
                    $error_message = 'Format d\'image non valide. Seuls JPEG et PNG sont autorisés.';
                }
            }

            if (empty($error_message)) {
                // Insert new category into the custom table
                $inserted = $wpdb->insert(
                    $table_name,
                    array(
                        'category' => $category,
                        'image' => $uploaded_image_path, // Save image URL
                    ),
                    array(
                        '%s', // Category
                        '%s', // Image path
                    )
                );

                if ($inserted === false) {
                    $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
                } else {
                    $success_message = 'La catégorie a été ajoutée avec succès.';

                    // Redirect to prevent duplicate submission
                    wp_redirect($_SERVER['REQUEST_URI']);
                    exit;
                }
            }
        }
    } else {
        $error_message = 'Veuillez entrer un nom de catégorie valide.';
    }
}

ob_end_clean();

// Delete Logic

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_categories';
    $delete_item_id = intval($_POST['delete_item_id']);

    if ($delete_item_id > 0) {
        // Get the category details, assuming the category has an image field
        $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $delete_item_id));

        if ($category) {
            // Check if there's an image and delete it
            if (!empty($category->image)) {
                // Get attachment ID from the URL and delete the file
                $old_image_id = attachment_url_to_postid($category->image);
                if ($old_image_id) {
                    wp_delete_attachment($old_image_id, true); // true to delete permanently
                }
            }

            // Delete the course category from the database
            $deleted = $wpdb->delete($table_name, ['id' => $delete_item_id], ['%d']);

            if ($deleted) {
                $success_message = 'Le niveau a été supprimé avec succès.';
            } else {
                $error_message = 'Erreur lors de la suppression du niveau.';
            }
        } else {
            $error_message = 'Catégorie non trouvée.';
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
    <div id="adminCourseCategory" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Catégories de Cours</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Catégories de Cours</span>
            </div>
        </div>

        <div class="content-section">
            <form action="" class="add-form" method="post" enctype="multipart/form-data">
                <?php if ($error_message): ?>
                <div class="form-error">
                    <p><?php echo esc_html($error_message); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                <div class="form-success">
                    <p><?php echo esc_html($success_message); ?></p>
                </div>
                <?php endif; ?>

                <!-- Add Category -->
                <section class="section col personal-information">
                    <h3 class="section-heading">Ajouter une nouvelle catégorie de cours</h3>

                    <div class="row">
                        <div class="col">
                            <label for="category">Nom de la catégorie <span class="required">*</span></label>
                            <input type="text" id="category" name="category" placeholder="Nom de la catégorie" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="">Catégorie d'image</label>
                            <div class="upload-button">
                                <label for="upload_image" class="upload-label">
                                    Upload Category Image <i class="fas fa-upload"></i>
                                </label>
                                <input type="file" id="upload_image" name="upload_image" accept="image/jpeg, image/png"
                                    class="upload-input">
                            </div>
                            <p class="text">(Images only, JPEG/PNG, max 2 MB)</p>
                            <p class="image-file-name">No file selected</p>
                        </div>
                    </div>

                    <button type="submit" class="submit-button" name="add_course_category">Ajouter</button>
                </section>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Cours</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="list">
                    <?php if (!empty($categories)) : ?>
                    <?php foreach ($categories as $category) : ?>
                    <tr>
                        <td class="category-name">
                            <?php echo esc_html($category->category); ?>
                        </td>
                        <td>
                            <?php
                                global $wpdb;

                                $course_category = $category->category;

                                if (!empty($course_category)) {
                                    $course_count = $wpdb->get_var($wpdb->prepare(
                                        "SELECT COUNT(*) FROM {$wpdb->prefix}courses WHERE category = %s",
                                        $course_category
                                    ));
                                    // Output the result
                                    echo esc_html($course_count);
                                }
                            ?>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/admin/course-management/categories/edit-category/?edit_item_id=' . $category->id)); ?>"
                                class="action-button edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="post" class="delete-form">
                                <input type="hidden" name="delete_item_id"
                                    value="<?php echo esc_attr($category->id); ?>">
                                <button type="button" class="action-button delete open-modal">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="4" class="no-data">Aucune catégorie trouvée.</td>
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