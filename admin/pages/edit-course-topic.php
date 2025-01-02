<?php

/* Template Name: Admin | Edit Course Topic */

// Page title
global $pageTitle;
$pageTitle = 'Edit Course Topic';

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
    $table_name = $wpdb->prefix . 'course_topics'; // Assuming 'course_topics' table for topics

    // Fetch the details of the topic using the ID
    $topic = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $edit_item_id));

    if (!$topic) {
        // Handle case when the topic does not exist
        wp_die('Le sujet demandé est introuvable.');
    }

    // Handle form submission for updating the topic
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_course_topic'])) {
        // Sanitize user input
        $new_topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';
        $new_category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : ''; // Get selected category
        $new_grade = isset($_POST['grade']) ? sanitize_text_field($_POST['grade']) : ''; // Get selected grade
        $new_level = isset($_POST['level']) ? sanitize_text_field($_POST['level']) : ''; // Get selected level
        $new_image = isset($_FILES['upload_image']) ? $_FILES['upload_image'] : null;

        if (!empty($new_topic) && !empty($new_category) && !empty($new_grade) && !empty($new_level)) {
            // Check if the topic already exists (excluding the current topic being edited)
            $existing_topic = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE topic = %s AND id != %d",
                $new_topic,
                $edit_item_id
            ));

            if ($existing_topic > 0) {
                $error_message = 'Le sujet existe déjà dans la base de données.';
            } else {
                // If a new image is uploaded, handle the image upload
                if ($new_image && $new_image['error'] === UPLOAD_ERR_OK) {
                    // Delete the old image from WordPress uploads and the database if there was one
                    if (!empty($topic->image)) {
                        // Get attachment ID from the URL and delete the file
                        $old_image_id = attachment_url_to_postid($topic->image);
                        if ($old_image_id) {
                            wp_delete_attachment($old_image_id, true); // true to delete permanently
                        }
                    }

                    // Upload the new image to the media library
                    $uploaded_image = media_handle_upload('upload_image', 0); // 0 means no post association

                    if (is_wp_error($uploaded_image)) {
                        $error_message = 'Erreur lors du téléchargement de l\'image.';
                    } else {
                        // Get the URL of the new uploaded image
                        $new_image_url = wp_get_attachment_url($uploaded_image);
                    }
                }

                // If no error with the image upload or no image uploaded, update the database
                if (!$error_message) {
                    $data = array(
                        'topic' => $new_topic,
                        'category' => $new_category, // Update the category
                        'grade' => $new_grade, // Update the grade
                        'level' => $new_level, // Update the level
                    );

                    // If a new image was uploaded, add it to the data
                    if (!empty($new_image_url)) {
                        $data['image'] = $new_image_url;
                    }

                    // Update the topic in the database
                    $updated = $wpdb->update(
                        $table_name,
                        $data, // Column => Value
                        array('id' => $edit_item_id), // Where condition
                        array('%s', '%s', '%s', '%s', '%s'), // Data type for each value (topic, category, grade, level, image)
                        array('%d') // Data type for where condition
                    );

                    if ($updated === false) {
                        // Handle potential database errors
                        $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
                    } else {
                        // Set success message
                        $success_message = 'Le sujet a été mis à jour avec succès.';

                        // Redirect to prevent duplicate submission
                        wp_redirect(home_url('/admin/course-management/topics/'));
                        exit;
                    }
                }
            }
        } else {
            $error_message = 'Veuillez entrer un nom de sujet valide et choisir une catégorie.';
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
    <div id="adminCourseTopic" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Editer le sujet du cours</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Sujet du cours</span>
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

                <!-- Edit Topic -->
                <section class="section col personal-information">
                    <h3 class="section-heading">Mettre à jour le sujet</h3>

                    <div class="row">
                        <div class="col">
                            <label for="topic">Nom du Sujet <span class="required">*</span></label>
                            <input type="text" id="topic" name="topic" placeholder="Nom du Sujet"
                                value="<?php echo esc_attr($topic->topic); ?>" required>
                        </div>

                        <div class="col">
                            <label for="category">Catégorie de sujet <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="category" name="category" required>
                                    <?php
                                        global $wpdb; // Access the global $wpdb object for database queries

                                        // Query the custom 'course_categories' table
                                        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");

                                        // Check if categories are available
                                        if ($categories) {
                                            foreach ($categories as $category) {
                                                // Check if the current category matches the topic's category
                                                $selected = ($category->category === $topic->category) ? 'selected' : '';
                                                echo '<option value="' . esc_attr($category->category) . '" ' . $selected . '>' . esc_html($category->category) . '</option>';
                                            }
                                        } else {
                                            echo '<option disabled>No categories found</option>';
                                        }
                                    ?>

                                </select>
                                <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="grade">Matière Niveau <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="grade" name="grade" required>
                                    <?php
                                        global $wpdb; // Access the global $wpdb object for database queries

                                        // Query the custom 'course_grades' table
                                        $grades = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_grades");

                                        // Check if grades are available
                                        if ($grades) {
                                            foreach ($grades as $grade) {
                                                // Check if the current grade matches the topic's grade
                                                $selected = ($grade->grade === $topic->grade) ? 'selected' : '';
                                                echo '<option value="' . esc_attr($grade->grade) . '" ' . $selected . '>' . esc_html($grade->grade) . '</option>';
                                            }
                                        } else {
                                            echo '<option disabled>No grade found</option>';
                                        }
                                    ?>

                                </select>
                                <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                            </div>
                        </div>

                        <div class="col">
                            <label for="level">Niveau de la matière <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="level" name="level" required>
                                    <?php
                                        global $wpdb; // Access the global $wpdb object for database queries

                                        // Query the custom 'course_levels' table
                                        $levels = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_levels");

                                        // Check if levels are available
                                        if ($levels) {
                                            foreach ($levels as $level) {
                                                // Check if the current level matches the topic's level
                                                $selected = ($level->level === $topic->level) ? 'selected' : '';
                                                echo '<option value="' . esc_attr($level->level) . '" ' . $selected . '>' . esc_html($level->level) . '</option>';
                                            }
                                        } else {
                                            echo '<option disabled>No level found</option>';
                                        }
                                    ?>

                                </select>
                                <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="">Image du Sujet</label>
                            <?php if (!empty($topic->image)) : ?>
                            <?php 
                                // Get the attachment ID from the image URL
                                $attachment_id = attachment_url_to_postid($topic->image);
                                 
                                // Get the lowest resolution image (thumbnail size)
                                $image = wp_get_attachment_image_src($attachment_id, 'medium');
                                 
                                // If image is found, display it
                                if ($image) {
                                    $image_url = esc_url($image[0]); // The URL of the lowest resolution image
                                }
                            ?>
                            <img src="<?php echo isset($image_url) ? $image_url : esc_url($topic->image); ?>"
                                alt="<?php echo esc_attr($topic->topic); ?>" class="topic-image medium">
                            <?php endif; ?>
                            <div class="upload-button">
                                <label for="upload_image" class="upload-label">
                                    Upload Topic Image <i class="fas fa-upload"></i>
                                </label>
                                <input type="file" id="upload_image" name="upload_image" accept="image/jpeg, image/png"
                                    class="upload-input">
                            </div>
                            <p class="text">(Images only, JPEG/PNG, max 2 MB)</p>
                            <p class="image-file-name">No file selected</p>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="submit-button" name="edit_course_topic">Mise à jour</button>
                        <a href="<?php echo home_url('/admin/course-management/topics/'); ?>"
                            class="cancel-button">Annuler</a>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>