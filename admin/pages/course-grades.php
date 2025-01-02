<?php

/* Template Name: Admin | Course Grades */

// page title
global $pageTitle;
$pageTitle = 'Notes Étudiant';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'course_grades' table
$grades = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_grades");

ob_start();

$error_message = ''; // Initialize error message variable
$success_message = ''; // Initialize success message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course_grade'])) {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'course_grades';

    // Sanitize user input
    $grade = isset($_POST['grade']) ? sanitize_text_field($_POST['grade']) : '';

    if (!empty($grade)) {
        // Check if the grade already exists
        $existing_grade = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE grade = %s", $grade));

        if ($existing_grade > 0) {
            $error_message = 'La note existe déjà dans la base de données.';
        } else {
            // Insert new grade into the custom table
            $inserted = $wpdb->insert(
                $table_name,
                array(
                    'grade' => $grade, // Column name => Value
                ),
                array(
                    '%s' // Data type for each value
                )
            );

            if ($inserted === false) {
                // Handle potential database errors
                $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
            } else {
                // Set success message
                $success_message = 'La note a été ajoutée avec succès.';

                // Redirect to prevent duplicate submission
                wp_redirect($_SERVER['REQUEST_URI']);
                exit;
            }
        }
    } else {
        $error_message = 'Veuillez entrer un nom de note valide.';
    }
}

ob_end_clean();

// Delete Logic

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_grades';
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
            <form action="" class="add-form" method="post">
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

                <!-- Add Grade -->
                <section class="section col personal-information">
                    <h3 class="section-heading">Ajouter un nouveau niveau</h3>
                    <div class="row">
                        <div class="col">
                            <label for="grade">Nom du Niveau <span class="required">*</span></label>
                            <input type="text" id="grade" name="grade" placeholder="Nom du Niveau" required>
                        </div>
                    </div>
                    <button type="submit" class="submit-button" name="add_course_grade">Ajouter</button>
                </section>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Nom du Niveau</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="list">
                    <?php if (!empty($grades)) : ?>
                    <?php foreach ($grades as $grade) : ?>
                    <tr>
                        <td class="item-name">
                            <?php echo esc_html($grade->grade); ?>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/admin/course-management/grades/edit-grade/?edit_item_id=' . $grade->id)); ?>"
                                class="action-button edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="post" class="delete-form">
                                <input type="hidden" name="delete_item_id" value="<?php echo esc_attr($grade->id); ?>">
                                <button type="button" class="action-button delete open-modal">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="2" class="no-data">Aucune note trouvée.</td>
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