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



// Delete Logic

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'students';
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
                            <option value="troisième">Troisième</option>
                            <option value="quatrième">Quatrième</option>
                            <option value="cinquième">Cinquième</option>
                            <option value="sixième">Sixième</option>
                        </select>
                        <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                    </div>

                    <!-- Level Filter -->
                    <div class="custom-select-wrapper">
                        <select id="level-filter" onchange="filterBySelect()">
                            <option value="all">Tous les niveaux</option>
                            <option value="débutant">Débutant</option>
                            <option value="fort">Fort</option>
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
                // Example of fetching custom data for each student
                $grade = $student->grade_level;
                $grade_in_french = '';
                switch ($grade) {
                    case 'Third':
                        $grade_in_french = 'Troisième';
                        break;
                    case 'Fourth':
                        $grade_in_french = 'Quatrième';
                        break;
                    case 'Fifth':
                        $grade_in_french = 'Cinquième';
                        break;
                    case 'Sixth':
                        $grade_in_french = 'Sixième';
                        break;
                    default:
                        $grade_in_french = 'n/a';
                }
                $level = $student->math_level ?? 'n/a';
                $level_in_french = '';
                switch ($level) {
                    case 'Beginner':
                        $level_in_french = 'Débutant';
                        break;
                    case 'Advanced':
                        $level_in_french = 'Fort';
                        break;
                    default:
                        $level_in_french = 'n/a';
                }
                $enrolled = $student->enrolled ?? 'n/a';
                $total_payment = $student->total_payment ?? 0;
            ?>
                    <tr data-grade="<?php echo strtolower($grade_in_french); ?>"
                        data-level="<?php echo strtolower($level_in_french); ?>">
                        <td class="name">
                            <a href="<?php echo esc_url(get_author_posts_url($student->id)); ?>">
                                <?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html($grade_in_french); ?>
                        </td>
                        <td>
                            <?php echo esc_html($level_in_french); ?>
                        </td>
                        <td>
                            <?php echo esc_html($enrolled); ?>
                        </td>
                        <td class="payment">
                            <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                            <?php echo esc_html($total_payment); ?>
                        </td>
                        <td class="action-buttons">
                            <a href="#" class="action-button edit">
                                <i class="fas fa-info-circle"></i>
                            </a>
                            <form method="post" class="delete-form">
                                <input type="hidden" name="delete_item_id"
                                    value="<?php echo esc_attr($student->id); ?>">
                                <button type="button" class="action-button delete open-modal">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="7">Aucun étudiant trouvé.</td>
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