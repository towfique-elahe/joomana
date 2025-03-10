<?php

/* Template Name: Admin | Edit Course Category */

// Page title
global $pageTitle;
$pageTitle = 'Niveaux Étudiant';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$error_message = ''; // Initialize error message variable
$success_message = ''; // Initialize success message variable

// Check if the edit_item_id is present in the URL
$edit_item_id = isset($_GET['edit_item_id']) ? intval($_GET['edit_item_id']) : 0;

if ($edit_item_id > 0) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'course_categories';

    // Fetch the details of the category using the ID
    $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $edit_item_id));

    if (!$category) {
        // Handle case when the category does not exist
        wp_die('Le niveau demandé est introuvable.');
    }

    // Handle form submission for updating the category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_course_category'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Sanitize user input
        $new_category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $new_image = isset($_FILES['upload_image']) ? $_FILES['upload_image'] : null;

        if (!empty($new_category)) {
            // Check if the category already exists (excluding the current category being edited)
            $existing_category = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE category = %s AND id != %d",
                $new_category,
                $edit_item_id
            ));

            if ($existing_category > 0) {
                $error_message = 'Le niveau existe déjà dans la base de données.';
            } else {
                // If a new image is uploaded, handle the image upload
                if ($new_image && $new_image['error'] === UPLOAD_ERR_OK) {
                    // Delete the old image from WordPress uploads and the database if there was one
                    if (!empty($category->image)) {
                        // Get attachment ID from the URL and delete the file
                        $old_image_id = attachment_url_to_postid($category->image);
                        if ($old_image_id) {
                            wp_delete_attachment($old_image_id, true); // true to delete permanently
                        }
                    }

                    // Upload the new image to the media library
                    $uploaded_file = wp_handle_upload($new_image, ['test_form' => false]);

                    if ($uploaded_file && !isset($uploaded_file['error'])) {
                        // Insert the uploaded file into the media library
                        $file = $uploaded_file['file'];
                        $attachment = array(
                            'guid'           => $uploaded_file['url'],
                            'post_mime_type' => $new_image['type'],
                            'post_title'     => sanitize_file_name($new_image['name']),
                            'post_content'   => '',
                            'post_status'    => 'inherit'
                        );
                        $attachment_id = wp_insert_attachment($attachment, $file);
                        if (!is_wp_error($attachment_id)) {
                            // Generate metadata for the attachment
                            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file);
                            wp_update_attachment_metadata($attachment_id, $attachment_data);
                            $new_image_url = wp_get_attachment_url($attachment_id); // URL to save in DB
                        } else {
                            $error_message = 'Erreur lors de l\'insertion de l\'image dans la bibliothèque de médias.';
                        }
                    } else {
                        $error_message = 'Erreur lors du téléchargement de l\'image : ' . $uploaded_file['error'];
                    }
                }

                // If no error with the image upload or no image uploaded, update the database
                if (!$error_message) {
                    $data = array(
                        'category' => $new_category,
                    );

                    // If a new image was uploaded, add it to the data
                    if (!empty($new_image_url)) {
                        $data['image'] = $new_image_url;
                    }

                    // Update the category in the database
                    $updated = $wpdb->update(
                        $table_name,
                        $data, // Column => Value
                        array('id' => $edit_item_id), // Where condition
                        array('%s', '%s'), // Data type for each value
                        array('%d') // Data type for where condition
                    );

                    if ($updated === false) {
                        // Handle potential database errors
                        $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
                    } else {
                        // Set success message
                        $success_message = 'Le niveau a été mis à jour avec succès.';

                        // Redirect to prevent duplicate submission
                        wp_redirect(home_url('/admin/course-management/categories/'));
                        exit;
                    }
                }
            }
        } else {
            $error_message = 'Veuillez entrer un nom de niveau valide.';
        }
    }
} else {
    // Redirect or display an error message if no ID is provided
    wp_redirect(home_url('/admin/course-management/'));
    exit;
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminCourseCategory" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Niveaux étudiant</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Niveaux étudiant</span>
            </div>
        </div>

        <div class="content-section">
            <form action="" class="edit-form" method="post" enctype="multipart/form-data">
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

                <!-- Edit Category -->
                <section class="section col personal-information">
                    <h3 class="section-heading">Mettre à jour le niveau</h3>

                    <div class="row">
                        <div class="col">
                            <label for="category">Nom du Niveau <span class="required">*</span></label>
                            <input type="text" id="category" name="category" placeholder="Nom du Niveau"
                                value="<?php echo esc_attr($category->category); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="">Catégorie d'image</label>
                            <?php if (!empty($category->image)) : ?>
                            <?php 
                                // Get the attachment ID from the image URL
                                $attachment_id = attachment_url_to_postid($category->image);
                                
                                // Get the lowest resolution image (thumbnail size)
                                $image = wp_get_attachment_image_src($attachment_id, 'medium');
                                
                                // If image is found, display it
                                if ($image) {
                                    $image_url = esc_url($image[0]); // The URL of the lowest resolution image
                                }
                            ?>
                            <img src="<?php echo isset($image_url) ? $image_url : esc_url($category->image); ?>"
                                alt="<?php echo esc_attr($category->category); ?>" class="category-image medium">
                            <?php endif; ?>
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
                    <div class="buttons">
                        <button type="submit" class="submit-button" name="edit_course_category">Mise à jour</button>
                        <a href="<?php echo home_url('/admin/course-management/categories/'); ?>"
                            class="cancel-button">Annuler</a>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>