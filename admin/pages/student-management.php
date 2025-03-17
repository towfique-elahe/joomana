<?php

/* Template Name: Admin | Student Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion Étudiants';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Query to fetch all users with the role 'student'
global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'students' table
$students = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}students");

// Get total enrolled courses
function get_total_enrolled_courses($student) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'courses';

    $total_courses = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE JSON_CONTAINS(enrolled_students, %s)",
        json_encode((string) $student->id) // Convert to string since JSON stores numeric and string IDs
    ));

    return (int) $total_courses;
}

// Get total payments
function get_total_payments($student) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'payments';

    $total = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(amount) FROM {$table_name} WHERE user_id = %d",
        $student->id
    ));

    // Return total payments or 0 if no payments found
    return ($total) ? $total : 0;
}

// Delete Student Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    $delete_item_id = intval($_POST['delete_item_id']);

    if ($delete_item_id > 0) {
        require_once ABSPATH . 'wp-admin/includes/user.php'; // Ensure function is available
        
        // Attempt to delete the WordPress user
        if (wp_delete_user($delete_item_id)) {
            $success_message = 'L\'utilisateur a été supprimé avec succès.';
        } else {
            $error_message = 'Erreur lors de la suppression de l\'utilisateur.';
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
    <div id="adminStudentManagement" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion étudiants</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion étudiants</span>
            </div>
        </div>

        <div class="content-section list">
            <div class="filter-container">
                <div class="select-filters">
                    <!-- Grade Filter -->
                    <div class="custom-select-wrapper">
                        <select id="grade-filter" onchange="filterBySelect()">
                            <option value="all">Tous les grades</option>
                            <?php
                                global $wpdb;
                                $grades = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_grades");
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

                    <!-- Level Filter -->
                    <div class="custom-select-wrapper">
                        <select id="level-filter" onchange="filterBySelect()">
                            <option value="all">Tous les niveaux</option>
                            <?php
                                global $wpdb;
                                $levels = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_levels");
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

                <div class="filter-bar">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Rechercher Un Étudiant" onkeyup="filterUser()">
                    </div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Nom d'étudiant</th>
                        <th>Grade</th>
                        <th>Niveau</th>
                        <th>Inscrit</th>
                        <th>Paiement total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="list">
                    <?php if (!empty($students)) : ?>
                    <?php foreach ($students as $student) : ?>
                    <?php
                        $total_courses = get_total_enrolled_courses($student);
                        $total_payments = get_total_payments($student);
                    ?>
                    <tr data-grade="<?php echo strtolower($student->grade); ?>"
                        data-level="<?php echo strtolower($student->level); ?>">
                        <td class="name">
                            <a
                                href="<?php echo esc_url(home_url('/admin/student-management/student-details/?id=' . $student->id)); ?>">
                                <?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($student->grade); ?>
                        </td>
                        <td>
                            <?php echo esc_html($student->level); ?>
                        </td>
                        <td>
                            <?php echo esc_html($total_courses); ?>
                        </td>
                        <td class="payment">
                            <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                            <?php echo esc_html( intval($total_payments) ); ?>
                        </td>
                        <td class="action-buttons">
                            <a href="<?php echo esc_url(home_url('/admin/student-management/student-details/?id=' . $student->id)); ?>"
                                class="action-button edit">
                                <i class="fas fa-info-circle"></i>
                            </a>
                            <form method="post" class="delete-form">
                                <input type="hidden" name="delete_item_id"
                                    value="<?php echo esc_attr($student->id); ?>">
                                <button type="button" class="action-button delete open-modal" data-modal="deleteUser">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="7" class="no-data">Aucun étudiant trouvé.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteUser" class="modal">
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