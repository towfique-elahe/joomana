<?php

/* Template Name: Admin | Edit Course Grade */

// Page title
global $pageTitle;
$pageTitle = 'Notes étudiant';

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
    $table_name = $wpdb->prefix . 'course_grades';

    // Fetch the details of the grade using the ID
    $grade = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $edit_item_id));

    if (!$grade) {
        // Handle case when the grade does not exist
        wp_die('Le niveau demandé est introuvable.');
    }

    // Handle form submission for updating the grade
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_course_grade'])) {
        // Sanitize user input
        $new_grade = isset($_POST['grade']) ? sanitize_text_field($_POST['grade']) : '';

        if (!empty($new_grade)) {
            // Check if the grade already exists (excluding the current grade being edited)
            $existing_grade = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE grade = %s AND id != %d",
                $new_grade,
                $edit_item_id
            ));

            if ($existing_grade > 0) {
                $error_message = 'Le niveau existe déjà dans la base de données.';
            } else {
                // Update the grade in the database
                $updated = $wpdb->update(
                    $table_name,
                    array('grade' => $new_grade), // Column => Value
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
                    wp_redirect(home_url('/admin/course-management/grades/'));
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
    <div id="adminCourseGrade" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Notes étudiant</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Notes étudiant</span>
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

                <!-- Edit Grade -->
                <section class="section col personal-information">
                    <h3 class="section-heading">Mettre à jour la note</h3>
                    <div class="row">
                        <div class="col">
                            <label for="grade">Nom de Grade <span class="required">*</span></label>
                            <input type="text" id="grade" name="grade" placeholder="Nom de Grade"
                                value="<?php echo esc_attr($grade->grade); ?>" required>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit" class="submit-button" name="edit_course_grade">Mise à jour</button>
                        <a href="<?php echo home_url('/admin/course-management/grades/'); ?>"
                            class="cancel-button">Annuler</a>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>