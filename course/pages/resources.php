<?php

/* Template Name: Course | Resources */

// page title
global $pageTitle;
$pageTitle = 'Ressources';

require_once(get_template_directory() . '/course/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the current user
$user = wp_get_current_user();
$default_user_image = esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png');

// Get course_id from session
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {

    // Check the user's role and redirect accordingly
    if (in_array('student', (array) $user->roles)) {
        wp_redirect(home_url('/student/course-management/'));
        exit;
    } elseif (in_array('teacher', (array) $user->roles)) {
        wp_redirect(home_url('/teacher/course-management/'));
        exit;
    } else {
        // Default redirection for other roles or if no role is matched
        wp_redirect(home_url());
        exit;
    }
}
$course_id = intval($_GET['course_id']);

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/course/templates/sidebar.php'); ?>
    </div>
    <div id="courseResources" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Ressources</h2>
            <div class="content-breadcrumb">
                <?php 
                    if (current_user_can('student')) {
                ?>
                <a href="<?php echo home_url('/student/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <?php 
                    } elseif (current_user_can('teacher')) {
                ?>
                <a href="<?php echo home_url('/teacher/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <?php } ?>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <?php 
                    if (current_user_can('student')) {
                ?>
                <a href="<?php echo home_url('/student/course-management'); ?>" class="breadcrumb-link">Gestion de
                    cours</a>
                <?php 
                    } elseif (current_user_can('teacher')) {
                ?>
                <a href="<?php echo home_url('/teacher/course-management'); ?>" class="breadcrumb-link">Gestion de
                    cours</a>
                <?php } ?>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Ressources</span>
            </div>
        </div>

        <div class="content-section">
            <div class="row">
                <button type="button" class="button add-resource open-modal" data-modal="addResource">
                    <i class="fas fa-plus"></i> Ajouter une ressource
                </button>
            </div>

            <div class="row file-container">
                <div class="file-card">
                    <div class="file-top">
                        <p class="file-type assignment">Assignment</p>
                        <button type="button" class="button file-delete open-modal" data-modal="fileDelete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <div class="file-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                    </div>
                    <div class="file-bottom row">
                        <div class="col">
                            <h3 class="file-title">Fichier 1</h3>
                            <p class="file-uploaded-time">
                                Téléchargé: 2025-02-20 | 19:15:00
                            </p>
                        </div>
                        <div class="col">
                            <a href="" class="download-button">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="file-card">
                    <div class="file-top">
                        <p class="file-type report">progress report</p>
                        <button type="button" class="button file-delete open-modal" data-modal="fileDelete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <div class="file-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                    </div>
                    <div class="file-bottom row">
                        <div class="col">
                            <h3 class="file-title">Fichier 1</h3>
                            <p class="file-uploaded-time">
                                Téléchargé: 2025-02-20 | 19:15:00
                            </p>
                        </div>
                        <div class="col">
                            <a href="" class="download-button">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="file-card">
                    <div class="file-top">
                        <p class="file-type slide">Slide</p>
                        <button type="button" class="button file-delete open-modal" data-modal="fileDelete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <div class="file-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                    </div>
                    <div class="file-bottom row">
                        <div class="col">
                            <h3 class="file-title">Fichier 1</h3>
                            <p class="file-uploaded-time">
                                Téléchargé: 2025-02-20 | 19:15:00
                            </p>
                        </div>
                        <div class="col">
                            <a href="" class="download-button">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<!-- Add Resource Modal -->
<div id="addResource" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h4 class="modal-heading">Ajouter un fichier de ressources</h4>

        <form method="post" action="" class="form add-resource-form">
            <input type="hidden" name="action" value="add_resource">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">

            <!-- select file type -->
            <section class="section col file-type">
                <div class="row">
                    <div class="col">
                        <label for="file_type">File Type</label>
                        <select name="file_type" id="file_type" required>
                            <option value="">Assignment</option>
                            <option value="">Progress Report</option>
                            <option value="">Course Slide</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- add assignment -->
            <section class="section col add-assignment">
                <div class="row">
                    <div class="col">
                        <label for="submission_deadline">Submission Deadline</label>
                        <input type="datetime-local" name="submission_deadline" id="submission_deadline">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="upload-file-button">
                            <label for="upload_assignment" class="upload-cv-label">
                                Télécharger la tâche <ion-icon name="document-attach-outline"></ion-icon>
                            </label>
                            <input type="file" id="upload_assignment" name="upload_assignment" accept=".pdf"
                                class="upload-file-input">
                        </div>
                        <p class="text">(PDF uniquement, max 2 Mo)</p>
                        <p class="file-name">Aucun fichier sélectionné</p>
                    </div>
                </div>
            </section>

            <!-- add report -->
            <section class="section col add-report">
                <div class="row">
                    <div class="col">
                        <label for="student_id">Student</label>
                        <select name="student_id" id="student_id">
                            <option value="">Student 1</option>
                            <option value="">Student 2</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="band_score">Band Score</label>
                        <input type="number" name="band_score" id="band_score" min="0" max="100">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="comment">Comment</label>
                        <textarea name="comment" id="comment"></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="upload-file-button">
                            <label for="upload_report" class="upload-cv-label">
                                Télécharger le rapport d'avancement <ion-icon name="document-attach-outline"></ion-icon>
                            </label>
                            <input type="file" id="upload_report" name="upload_report" accept=".pdf"
                                class="upload-file-input">
                        </div>
                        <p class="text">(PDF uniquement, max 2 Mo)</p>
                        <p class="file-name">Aucun fichier sélectionné</p>
                    </div>
                </div>
            </section>

            <!-- add slide -->
            <section class="section col add-slide">
                <div class="row">
                    <div class="col">
                        <div class="upload-file-button">
                            <label for="upload_slide" class="upload-cv-label">
                                Télécharger la diapositive <ion-icon name="document-attach-outline"></ion-icon>
                            </label>
                            <input type="file" id="upload_slide" name="upload_slide" accept=".pdf"
                                class="upload-file-input">
                        </div>
                        <p class="text">(PDF uniquement, max 2 Mo)</p>
                        <p class="file-name">Aucun fichier sélectionné</p>
                    </div>
                </div>
            </section>

            <div class="modal-actions">
                <button type="submit" class="modal-button confirm">Ajouter</button>
                <button type="button" class="modal-button cancel close-modal">Annuler</button>
            </div>
        </form>

    </div>
</div>

<!-- File Delete Modal -->
<div id="fileDelete" class="modal">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <h4 class="modal-heading">
            <i class="fas fa-exclamation-triangle" style="color: crimson"></i> Avertissement
        </h4>
        <p class="modal-info">Etes-vous sûr de vouloir supprimer le fichier ?</p>
        <form action="" method="post">
            <input type="hidden" name="action" value="delete_file">
            <div class="modal-actions">
                <button id="confirmCancel" class="modal-button delete">Confirmer</button>
                <button class="modal-button cancel close-modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<?php require_once(get_template_directory() . '/course/templates/footer.php'); ?>