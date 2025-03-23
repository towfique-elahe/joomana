<?php

/* Template Name: Admin | Edit Course Level */

// Page title
global $pageTitle;
$pageTitle = 'Niveaux Elèves';

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
    $table_name = $wpdb->prefix . 'course_levels';

    // Fetch the details of the level using the ID
    $level = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $edit_item_id));

    if (!$level) {
        // Handle case when the level does not exist
        wp_die('Le niveau demandé est introuvable.');
    }

    // Handle form submission for updating the level
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_course_level'])) {
        // Sanitize user input
        $new_level = isset($_POST['level']) ? sanitize_text_field($_POST['level']) : '';

        if (!empty($new_level)) {
            // Check if the level already exists (excluding the current level being edited)
            $existing_level = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE level = %s AND id != %d",
                $new_level,
                $edit_item_id
            ));

            if ($existing_level > 0) {
                $error_message = 'Le niveau existe déjà dans la base de données.';
            } else {
                // Update the level in the database
                $updated = $wpdb->update(
                    $table_name,
                    array('level' => $new_level), // Column => Value
                    array('id' => $edit_item_id), // Where condition
                    array('%s'), // Data type for each value
                    array('%d') // Data type for where condition
                );

                if ($updated === false) {
                    // Handle potential database errors
                    $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
                } else {
                    // Set success message
                    $success_message = 'Le niveau a été mis à jour avec succès.';

                    // Redirect to prevent duplicate submission
                    wp_redirect(home_url('/admin/course-management/levels/'));
                    exit;
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
    <div id="adminCourseLevel" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Niveaux Elèves</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Niveaux Elèves</span>
            </div>
        </div>

        <div class="content-section">
            <form action="" class="edit-form" method="post">
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

                <!-- Edit Level -->
                <section class="section col personal-information">
                    <h3 class="section-heading">Mettre à jour le niveau</h3>
                    <div class="row">
                        <div class="col">
                            <label for="level">Nom du Niveau <span class="required">*</span></label>
                            <input type="text" id="level" name="level" placeholder="Nom du Niveau"
                                value="<?php echo esc_attr($level->level); ?>" required>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="submit-button" name="edit_course_level">Mise à jour</button>
                        <a href="<?php echo home_url('/admin/course-management/levels/'); ?>"
                            class="cancel-button">Annuler</a>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>