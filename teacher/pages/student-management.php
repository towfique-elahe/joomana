<?php

/* Template Name: Teacher | Student Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion Étudiants';

require_once(get_template_directory() . '/teacher/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Query to fetch all users with the role 'student'
global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'students' table
$students = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}students");
?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/teacher/templates/sidebar.php'); ?>
    </div>
    <div id="adminStudentManagement" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion étudiants</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/teacher/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion étudiants</span>
            </div>
        </div>

        <div class="content-section user-list">
            <div class="search-bar">
                <input type="text" placeholder="Rechercher Un Étudiant" onkeyup="filterUser()">
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'étudiant</th>
                        <th>Grade</th>
                        <th>Niveau</th>
                        <th>Inscrit</th>
                        <th>Paiement</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="user-list">
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
                                    $level_in_french = 'Basique';
                                    break;
                                case 'Intermediate':
                                    $level_in_french = 'Intermédiaire';
                                    break;
                                case 'Advanced':
                                    $level_in_french = 'Avancé';
                                    break;
                                default:
                                    $level_in_french = 'n/a';
                            }
                            $enrolled = $student->enrolled ?? 'n/a';
                            $payment = $student->total_payment ?? 'n/a';
                            ?>
                    <tr>
                        <td>
                            <?php echo esc_html($student->id); ?>
                        </td>
                        <td class="user-name">
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
                            <i class="fa fa-eur" aria-hidden="true"></i>
                            <?php echo esc_html($payment); ?>
                        </td>
                        <td class="action-btn">
                            <a href="#">
                                <i class="fa fa-user-o" aria-hidden="true"></i>
                            </a>
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

<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>