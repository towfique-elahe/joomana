<?php

/* Template Name: Admin | Session Group Submissions */

// Page title
global $pageTitle;
$pageTitle = 'Soumissions De Groupe';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get course ID and group number from session
if (!isset($_GET['group_number']) || empty($_GET['group_number']) || !isset($_GET['course_id']) || empty($_GET['course_id'])) {
    wp_redirect(home_url('/admin/session-management/courses/groups/'));
    exit;
}

// Sanitize and retrieve the values
$course_id = intval($_GET['course_id']);
$group_number = intval($_GET['group_number']);

// Fetch files from the database
$submissions = [];

    // Fetch submissions
    $submissions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}student_submissions WHERE course_id = %d AND group_number = %d",
        $course_id,
        $group_number
    ));

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>

    <div id="adminSessionGroupSubmissions" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails du groupe</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/session-management/courses/'); ?>" class="breadcrumb-link">Gestion
                    des
                    sessions</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/session-management/courses/groups/'); ?>"
                    class="breadcrumb-link">Groupes De
                    Séances</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/session-management/courses/groups/group-details'); ?>"
                    class="breadcrumb-link">Détails du groupe</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Soumissions de groupe</span>
            </div>
        </div>

        <div class="content-section">

            <div class="row file-container">
                <?php if ($submissions) :?>

                <!-- Display Submissions -->
                <?php foreach ($submissions as $submission) : ?>
                <div class="file-card">
                    <div class="file-top">
                        <p class="file-type submission">Soumission</p>
                        <div class="file-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                    </div>
                    <div class="file-bottom row">
                        <div class="col">
                            <h3 class="file-title">Soumission | <?php echo basename($submission->file); ?></h3>
                            <?php
                                    $student_id = $submission->student_id;
                                    // Fetch the student's details using the student_id
                                    $student_table = $wpdb->prefix. 'students';
                                    $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $student_id));
                            ?>
                            <p class="file-info">
                                Étudiant:
                                <a href="<?php echo esc_url(home_url('/admin/student-management/student-details/?id=' . $student->id)); ?>"
                                    class="accent"><?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?></a>
                            </p>
                            <p class="file-info">
                                Téléchargé: <?php echo date('d M, y', strtotime($submission->created_at)); ?>
                            </p>
                        </div>
                        <div class="col">
                            <a href="<?php echo esc_url($submission->file); ?>" class="download-button" download>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else :?>
                <p class="no-data">Aucune soumission n'a été ajoutée pour ce cours</p>
                <?php endif;?>

            </div>

        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>