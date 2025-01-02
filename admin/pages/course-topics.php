<?php

/* Template Name: Admin | Course Topics */

// Page title
global $pageTitle;
$pageTitle = 'Sujets de Cours';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'course_topics' table
$topics = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_topics");

ob_start();

$error_message = ''; // Initialize error message variable
$success_message = ''; // Initialize success message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course_topic'])) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_topics';

    // Sanitize user input
    $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : ''; // Sanitize category input
    $grade = isset($_POST['grade']) ? sanitize_text_field($_POST['grade']) : ''; // Sanitize grade input
    $level = isset($_POST['level']) ? sanitize_text_field($_POST['level']) : ''; // Sanitize level input
    $uploaded_image_path = '';

    if (!empty($topic) && !empty($category)) {  // Check if both topic and category are provided
        // Check if the topic already exists
        $existing_topic = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE topic = %s", $topic));

        if ($existing_topic > 0) {
            $error_message = 'Le sujet existe déjà dans la base de données.';
        } else {
            // Handle file upload to WordPress Media Library (already present code)
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
                // Insert new topic into the custom table
                $inserted = $wpdb->insert(
                    $table_name,
                    array(
                        'topic' => $topic,
                        'category' => $category,
                        'grade' => $grade,
                        'level' => $level,
                        'image' => $uploaded_image_path, // Save image URL
                    ),
                    array(
                        '%s', // Topic
                        '%s', // Category
                        '%s', // Grade
                        '%s', // Level
                        '%s', // Image path
                    )
                );

                if ($inserted === false) {
                    $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
                } else {
                    $success_message = 'Le sujet a été ajouté avec succès.';

                    // Redirect to prevent duplicate submission
                    wp_redirect($_SERVER['REQUEST_URI']);
                    exit;
                }
            }
        }
    } else {
        $error_message = 'Veuillez entrer un nom de sujet valide et choisir une catégorie.';
    }
}

ob_end_clean();

// Delete Logic

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_topics';
    $delete_item_id = intval($_POST['delete_item_id']);

    if ($delete_item_id > 0) {
        // Get the topic details, assuming the topic has an image field
        $topic = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $delete_item_id));

        if ($topic) {
            // Check if there's an image and delete it
            if (!empty($topic->image)) {
                // Get attachment ID from the URL and delete the file
                $old_image_id = attachment_url_to_postid($topic->image);
                if ($old_image_id) {
                    wp_delete_attachment($old_image_id, true); // true to delete permanently
                }
            }

            // Delete the course topic from the database
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
    <div id="adminCourseTopic" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Sujets de Cours</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Sujets de Cours</span>
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

                <!-- Add Topic -->
                <section class="section col personal-information">
                    <h3 class="section-heading">Ajouter un nouveau sujet de cours</h3>

                    <div class="row">
                        <div class="col">
                            <label for="topic">Nom du sujet <span class="required">*</span></label>
                            <input type="text" id="topic" name="topic" placeholder="Nom du sujet" required>
                        </div>

                        <div class="col">
                            <label for="category">Catégorie de sujet <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="category" name="category">
                                    <option value="" disabled selected>Sélectionnez la catégorie de sujet</option>

                                    <?php
                                        global $wpdb; // Access the global $wpdb object for database queries

                                        // Query the custom 'course_categories' table
                                        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");

                                        // Check if categories are available
                                        if ($categories) {
                                            foreach ($categories as $category) {
                                                echo '<option value="' . esc_attr($category->category) . '">' . esc_html($category->category) . '</option>';
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
                                <select id="grade" name="grade">
                                    <option value="" disabled selected>Sélectionnez la matière Niveau</option>

                                    <?php
                                        global $wpdb; // Access the global $wpdb object for database queries

                                        // Query the custom 'course_grades' table
                                        $grades = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_grades");

                                        // Check if grades are available
                                        if ($grades) {
                                            foreach ($grades as $grade) {
                                                echo '<option value="' . esc_attr($grade->grade) . '">' . esc_html($grade->grade) . '</option>';
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
                                <select id="level" name="level">
                                    <option value="" disabled selected>Sélectionnez le niveau de sujet</option>

                                    <?php
                                        global $wpdb; // Access the global $wpdb object for database queries

                                        // Query the custom 'course_levels' table
                                        $levels = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_levels");

                                        // Check if levels are available
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
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="">Image du sujet</label>
                            <div class="upload-button">
                                <label for="upload_image" class="upload-label">
                                    Télécharger l'image du sujet <i class="fas fa-upload"></i>
                                </label>
                                <input type="file" id="upload_image" name="upload_image" accept="image/jpeg, image/png"
                                    class="upload-input">
                            </div>
                            <p class="text">(Images uniquement, JPEG/PNG, max 2 Mo)</p>
                            <p class="image-file-name">Aucun fichier sélectionné</p>
                        </div>
                    </div>

                    <button type="submit" class="submit-button" name="add_course_topic">Ajouter</button>
                </section>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Sujet</th>
                        <th>catégorie</th>
                        <th>Grade</th>
                        <th>Niveaus</th>
                        <th>Cours</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="list">
                    <?php if (!empty($topics)) : ?>
                    <?php foreach ($topics as $topic) : ?>
                    <tr>
                        <td class="topic-name">
                            <?php echo esc_html($topic->topic); ?>
                        </td>
                        <td class="category-name">
                            <?php echo esc_html($topic->category); ?>
                        </td>
                        <td class="grade-name">
                            <?php echo esc_html($topic->grade); ?>
                        </td>
                        <td class="level-name">
                            <?php echo esc_html($topic->level); ?>
                        </td>
                        <td class="course-count">
                            <?php
                                global $wpdb;

                                $course_topic = $topic->topic;

                                if (!empty($course_topic)) {
                                    $course_count = $wpdb->get_var($wpdb->prepare(
                                        "SELECT COUNT(*) FROM {$wpdb->prefix}courses WHERE topic = %s",
                                        $course_topic
                                    ));
                                    // Output the result
                                    echo esc_html($course_count);
                                }
                            ?>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/admin/course-management/topics/edit-topic/?edit_item_id=' . $topic->id)); ?>"
                                class="action-button edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="post" class="delete-form">
                                <input type="hidden" name="delete_item_id" value="<?php echo esc_attr($topic->id); ?>">
                                <button type="button" class="action-button delete open-modal">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="6" class="no-data">Aucun sujet trouvé.</td>
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
        <p class="modal-info">Êtes-vous sûr de vouloir supprimer ce sujet ?</p>
        <div class="modal-actions">
            <button id="confirmBtn" class="modal-button delete">Supprimer</button>
            <button id="cancelBtn" class="modal-button cancel">Annuler</button>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>