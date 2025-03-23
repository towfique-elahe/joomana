<?php

/* Template Name: Course | Resources */

// Page title
global $pageTitle;
$pageTitle = 'Ressources';

require_once(get_template_directory() . '/course/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the current user
$user = wp_get_current_user();

// Get course_id from session
if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {
    // Check the user's role and redirect accordingly
    if (in_array('parent', (array) $user->roles)) {
        wp_redirect(home_url('/parent/course-management/'));
        exit;
    } elseif (in_array('student', (array) $user->roles)) {
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
$session_id = intval($_GET['session_id']);

// Role base queries
if (in_array('teacher', (array) $user->roles)) {
    
    $sessions_table = $wpdb->prefix . 'course_sessions';
    $students_table = $wpdb->prefix . 'students';
    
    $teacher_id = $user->ID;
    
    // Fetch the enrolled_students array where the session ID matches
    $enrolled_students = $wpdb->get_var($wpdb->prepare(
        "SELECT enrolled_students FROM $sessions_table WHERE id = %d",
        $session_id
    ));

    // Decode the JSON array if it's stored as a JSON string
    $enrolled_students_array = json_decode($enrolled_students, true);

    // If it's not an array or empty, return an empty array
    if (!is_array($enrolled_students_array) || empty($enrolled_students_array)) {
        return [];
    }

    // Convert student IDs to a comma-separated string for SQL query
    $placeholders = implode(',', array_fill(0, count($enrolled_students_array), '%d'));
    
    // Fetch student details
    $query = "SELECT * FROM $students_table WHERE id IN ($placeholders)";
    $prepared_query = $wpdb->prepare($query, ...$enrolled_students_array);

    $students = $wpdb->get_results($prepared_query);
    
} elseif (in_array('student', (array) $user->roles)) {
    $student_id = $user->ID;
} elseif (in_array('parent', (array) $user->roles)) {
    $student_id = intval($_GET['student_id']);
}

// Handle resource file upload for teachers
if (in_array('teacher', (array) $user->roles)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_resource') {
        global $wpdb;
        $table_name = "";
        $file_field = "";
        $uploaded_file_url = "";

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Determine the selected file type
        $file_type = sanitize_text_field($_POST['file_type']);

        // Handle file upload
        function upload_file($file_key) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
                $upload_overrides = ['test_form' => false];
                $uploaded_file = wp_handle_upload($_FILES[$file_key], $upload_overrides);

                if ($uploaded_file && !isset($uploaded_file['error'])) {
                    // Insert into WordPress Media Library
                    $file_path = $uploaded_file['file'];
                    $attachment = [
                        'guid'           => $uploaded_file['url'],
                        'post_mime_type' => $_FILES[$file_key]['type'],
                        'post_title'     => sanitize_file_name($_FILES[$file_key]['name']),
                        'post_content'   => '',
                        'post_status'    => 'inherit',
                    ];
                    $attachment_id = wp_insert_attachment($attachment, $file_path);

                    if (!is_wp_error($attachment_id)) {
                        $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                        wp_update_attachment_metadata($attachment_id, $attach_data);
                        return wp_get_attachment_url($attachment_id);
                    }
                }
            }
            return null;
        }

        if ($file_type == "Assignment") {
            $table_name = $wpdb->prefix . "course_assignments";
            $file_field = "upload_assignment";
            $uploaded_file_url = upload_file($file_field);

            if ($uploaded_file_url) {
                $wpdb->insert($table_name, [
                    'session_id' => $session_id,
                    'teacher_id' => $teacher_id,
                    'deadline' => sanitize_text_field($_POST['submission_deadline']),
                    'file' => $uploaded_file_url,
                    'created_at' => current_time('mysql'),
                ]);
            }
        } elseif ($file_type == "Progress Report") {
            $table_name = $wpdb->prefix . "student_reports";
            $file_field = "upload_report";
            $uploaded_file_url = upload_file($file_field);

            $allowed_comments = ['Excellent', 'Bon', 'Moyen', 'Faible'];
            $comment = in_array($_POST['comment'], $allowed_comments, true) ? $_POST['comment'] : null;
            
            if ($uploaded_file_url) {
                $wpdb->insert($table_name, [
                    'session_id' => $session_id,
                    'teacher_id' => $teacher_id,
                    'student_id' => intval($_POST['student_id']),
                    'comment' => $comment,
                    'file' => $uploaded_file_url,
                    'created_at' => current_time('mysql'),
                ]);
            }
        } elseif ($file_type == "Course Slide") {
            $table_name = $wpdb->prefix . "course_slides";
            $file_field = "upload_slide";
            $uploaded_file_url = upload_file($file_field);

            if ($uploaded_file_url) {
                $wpdb->insert($table_name, [
                    'session_id' => $session_id,
                    'teacher_id' => $teacher_id,
                    'file' => $uploaded_file_url,
                    'created_at' => current_time('mysql'),
                ]);
            }
        }

        // Redirect to prevent resubmission
        wp_safe_redirect($_SERVER['REQUEST_URI']);
        exit;
    }
}

// Fetch files from the database
$course_assignments = [];
$course_slides = [];
$student_reports = [];

if (in_array('teacher', (array) $user->roles)) {
    // Fetch course assignments for the teacher
    $course_assignments = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}course_assignments WHERE session_id = %d",
        $session_id
    ));

    // Fetch course slides for the teacher
    $course_slides = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}course_slides WHERE session_id = %d",
        $session_id
    ));

    // Fetch student reports for the teacher
    $student_reports = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}student_reports WHERE session_id = %d",
        $session_id
    ));
} elseif (current_user_can('student') || current_user_can('parent')) {
    // Fetch course assignments for the student
    $course_assignments = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}course_assignments WHERE session_id = %d",
        $session_id
    ));

    // Fetch course slides for the student
    $course_slides = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}course_slides WHERE session_id = %d",
        $session_id
    ));

    // Fetch student reports for the student
    $student_reports = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}student_reports WHERE session_id = %d AND student_id = %d",
        $session_id,
        $student_id
    ));
}

// Handle file deletion for teachers
if (in_array('teacher', (array) $user->roles)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
        global $wpdb;

        // Get the file ID from the form submission
        $file_id = intval($_POST['delete_item_id']);
        $file_type = sanitize_text_field($_POST['file_type']);

        // Determine the table and file URL based on the file type
        $table_name = '';
        $file_url = '';

        if ($file_type == 'Assignment') {
            $table_name = $wpdb->prefix . 'course_assignments';
            $file_url = $wpdb->get_var($wpdb->prepare(
                "SELECT file FROM $table_name WHERE id = %d",
                $file_id
            ));
        } elseif ($file_type == 'Progress Report') {
            $table_name = $wpdb->prefix . 'student_reports';
            $file_url = $wpdb->get_var($wpdb->prepare(
                "SELECT file FROM $table_name WHERE id = %d",
                $file_id
            ));
        } elseif ($file_type == 'Course Slide') {
            $table_name = $wpdb->prefix . 'course_slides';
            $file_url = $wpdb->get_var($wpdb->prepare(
                "SELECT file FROM $table_name WHERE id = %d",
                $file_id
            ));
        }

        // If the file URL is found, delete the file from the media library
        if ($file_url) {
            $attachment_id = attachment_url_to_postid($file_url);
            if ($attachment_id) {
                wp_delete_attachment($attachment_id, true); // Force delete the file
            }
        }

        // Delete the record from the database
        $wpdb->delete($table_name, ['id' => $file_id]);

        // Redirect to prevent resubmission
        wp_safe_redirect($_SERVER['REQUEST_URI']);
        exit;
    }
}

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
                    } elseif (current_user_can('parent')) {
                ?>
                <a href="<?php echo home_url('/parent/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
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
                <a href="<?php echo home_url('/student/course-management'); ?>" class="breadcrumb-link">Gestion des
                    cours</a>
                <?php 
                    } elseif (current_user_can('parent')) {
                ?>
                <a href="<?php echo home_url('/parent/child-management'); ?>" class="breadcrumb-link">Gestion des
                    enfants</a>
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
            <?php
                if (in_array('teacher', (array) $user->roles)) {
            ?>
            <div class="row">
                <button type="button" class="button add-resource open-modal" data-modal="addResource">
                    <i class="fas fa-plus"></i> Ajouter une ressource
                </button>
            </div>
            <?php
                }
            ?>

            <div class="row file-container">
                <?php if ($course_assignments || $course_slides || $student_reports) :?>

                <!-- Display Course Assignments -->
                <?php foreach ($course_assignments as $assignment) : ?>
                <div class="file-card">
                    <div class="file-top">
                        <p class="file-type assignment">Affectation</p>
                        <?php
                            if (in_array('teacher', (array) $user->roles)) {
                        ?>
                        <form method="post" class="delete-form">
                            <input type="hidden" name="delete_item_id" value="<?php echo esc_attr($assignment->id); ?>">
                            <input type="hidden" name="file_type" id="deleteFileType" value="Assignment">
                            <button type="button" class="button file-delete open-modal" data-modal="fileDelete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        <?php
                            }
                        ?>
                        <a href="<?php echo esc_url($assignment->file); ?>" class="download-button" download>
                            <i class="fas fa-download"></i>
                        </a>
                        <div class="file-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                    </div>
                    <div class="file-bottom row">
                        <div class="col">
                            <h3 class="file-title">Affectation | <?php echo basename($assignment->file); ?></h3>
                            <p class="file-info">
                                Date limite: <?php echo date('d M, y', strtotime($assignment->deadline)); ?>
                            </p>
                            <p class="file-info">
                                Téléchargé: <?php echo date('d M, y', strtotime($assignment->created_at)); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Display Course Slides -->
                <?php foreach ($course_slides as $slide) : ?>
                <div class="file-card">
                    <div class="file-top">
                        <p class="file-type slide">Diapositive</p>
                        <?php
                            if (in_array('teacher', (array) $user->roles)) {
                        ?>
                        <form method="post" class="delete-form">
                            <input type="hidden" name="delete_item_id" value="<?php echo esc_attr($slide->id); ?>">
                            <input type="hidden" name="file_type" id="deleteFileType" value="Course Slide">
                            <button type="button" class="button file-delete open-modal" data-modal="fileDelete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        <?php
                            }
                        ?>
                        <a href="<?php echo esc_url($slide->file); ?>" class="download-button" download>
                            <i class="fas fa-download"></i>
                        </a>
                        <div class="file-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                    </div>
                    <div class="file-bottom row">
                        <div class="col">
                            <h3 class="file-title">Diapositive | <?php echo basename($slide->file); ?></h3>
                            <p class="file-info">
                                Téléchargé: <?php echo date('d M, y', strtotime($slide->created_at)); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Display Student Reports -->
                <?php foreach ($student_reports as $report) : ?>
                <div class="file-card">
                    <div class="file-top">
                        <p class="file-type report">Rapport</p>
                        <?php
                            if (in_array('teacher', (array) $user->roles)) {
                        ?>
                        <form method="post" class="delete-form">
                            <input type="hidden" name="delete_item_id" value="<?php echo esc_attr($report->id); ?>">
                            <input type="hidden" name="file_type" id="deleteFileType" value="Progress Report">
                            <button type="button" class="button file-delete open-modal" data-modal="fileDelete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        <?php
                            }
                        ?>
                        <a href="<?php echo esc_url($report->file); ?>" class="download-button" download>
                            <i class="fas fa-download"></i>
                        </a>
                        <div class="file-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                    </div>
                    <div class="file-bottom row">
                        <div class="col">
                            <h3 class="file-title">Rapport | <?php echo basename($report->file); ?></h3>
                            <?php
                                if (in_array('teacher', (array) $user->roles)) {
                                    $student_id = $report->student_id;
                                    // Fetch the student's details using the student_id
                                    $student_table = $wpdb->prefix. 'students';
                                    $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $student_id));
                            ?>
                            <p class="file-info">
                                Elèves:
                                <a href="<?php echo esc_url(home_url('/course/student-management/student-details/?student_id=' . $student->id . '&session_id=' . $session_id)); ?>"
                                    class="accent"><?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?></a>
                            </p>
                            <?php
                                }
                            ?>
                            <p class="file-info">
                                Commentaire: <?php echo esc_html($report->comment); ?>
                            </p>
                            <p class="file-info">
                                Téléchargé: <?php echo date('d M, y', strtotime($report->created_at)); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else :?>
                <p class="no-data">Aucune ressource n'a été ajoutée pour ce cours</p>
                <?php endif;?>

            </div>

        </div>
    </div>
</div>

<!-- Add Resource Modal -->
<div id="addResource" class="modal">
    <div class="modal-content">
        <span class="modal-close">
            <i class="fas fa-times"></i>
        </span>
        <h4 class="modal-heading">Ajouter un fichier de ressources</h4>

        <form method="post" action="" class="form add-resource-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_resource">

            <!-- Select file type -->
            <section class="section col file-type">
                <div class="row">
                    <div class="col">
                        <label for="file_type">Type de fichier</label>
                        <select name="file_type" id="file_type" required>
                            <option value="Assignment">Affectation</option>
                            <option value="Progress Report">Rapport d'avancement</option>
                            <option value="Course Slide">Diapositive du cours</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Add assignment -->
            <section class="section col add-assignment">
                <div class="row">
                    <div class="col">
                        <label for="submission_deadline">Date limite de soumission</label>
                        <input type="datetime-local" name="submission_deadline" id="submission_deadline">
                    </div>
                </div>
                <!-- Assignment File Input -->
                <div class="row">
                    <div class="col">
                        <div class="upload-file-button">
                            <label for="uploadAssignment" class="upload-file-label">
                                Télécharger la tâche <ion-icon name="document-attach-outline"></ion-icon>
                            </label>
                            <input type="file" id="uploadAssignment" name="upload_assignment" accept=".pdf"
                                class="upload-file-input">
                        </div>
                        <p class="text">(PDF uniquement, max 2 Mo)</p>
                        <p class="file-name" id="assignmentFileName">Aucun fichier sélectionné</p>
                    </div>
                </div>
            </section>

            <!-- Add report -->
            <section class="section col add-report">
                <div class="row">
                    <div class="col">
                        <label for="student_id">Elèves</label>
                        <select name="student_id" id="student_id">
                            <?php
                                    if (!empty($students)) {
                                        foreach ($students as $student) {
                            ?>
                            <option value="<?= $student->id ?>">
                                <?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?>
                            </option>
                            <?php
                                        }
                                    } else {
                            ?>
                            <option value="">Aucun elèves trouvé</option>
                            <?php
                                        }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="comment">Commentaire</label>
                        <div class="custom-select-wrapper">
                            <select name="comment" id="comment">
                                <option value="">-- Choisissez une option --</option>
                                <option value="Excellent">Excellent</option>
                                <option value="Bon">Bon</option>
                                <option value="Moyen">Moyen</option>
                                <option value="Faible">Faible</option>
                            </select>
                            <i class="fas fa-chevron-down custom-arrow"></i>
                        </div>
                    </div>
                </div>
                <!-- Report File Input -->
                <div class="row">
                    <div class="col">
                        <div class="upload-file-button">
                            <label for="uploadReport" class="upload-file-label">
                                Télécharger le rapport d'avancement <ion-icon name="document-attach-outline"></ion-icon>
                            </label>
                            <input type="file" id="uploadReport" name="upload_report" accept=".pdf"
                                class="upload-file-input">
                        </div>
                        <p class="text">(PDF uniquement, max 2 Mo)</p>
                        <p class="file-name" id="reportFileName">Aucun fichier sélectionné</p>
                    </div>
                </div>
            </section>

            <!-- Add slide -->
            <section class="section col add-slide">
                <!-- Slide File Input -->
                <div class="row">
                    <div class="col">
                        <div class="upload-file-button">
                            <label for="uploadSlide" class="upload-file-label">
                                Télécharger la diapositive <ion-icon name="document-attach-outline"></ion-icon>
                            </label>
                            <input type="file" id="uploadSlide" name="upload_slide" accept=".pdf"
                                class="upload-file-input">
                        </div>
                        <p class="text">(PDF uniquement, max 2 Mo)</p>
                        <p class="file-name" id="slideFileName">Aucun fichier sélectionné</p>
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
        <span class="modal-close">
            <i class="fas fa-times"></i>
        </span>
        <h4 class="modal-heading">
            <i class="fas fa-exclamation-triangle" style="color: crimson"></i> Avertissement
        </h4>
        <p class="modal-info">Êtes-vous sûr de vouloir supprimer ce niveau ?</p>
        <div class="modal-actions">
            <button id="confirmBtn" class="modal-button delete">Supprimer</button>
            <button id="cancelBtn" class="modal-button cancel close-modal">Annuler</button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const fileTypeSelect = document.getElementById("file_type");
    const addAssignmentSection = document.querySelector(".add-assignment");
    const addReportSection = document.querySelector(".add-report");
    const addSlideSection = document.querySelector(".add-slide");

    // File inputs and their corresponding name displays
    const assignmentFileInput = document.getElementById("uploadAssignment");
    const reportFileInput = document.getElementById("uploadReport");
    const slideFileInput = document.getElementById("uploadSlide");

    const assignmentFileNameDisplay = document.getElementById("assignmentFileName");
    const reportFileNameDisplay = document.getElementById("reportFileName");
    const slideFileNameDisplay = document.getElementById("slideFileName");

    function updateFormVisibility() {
        const selectedValue = fileTypeSelect.value;

        addAssignmentSection.style.display = "none";
        addReportSection.style.display = "none";
        addSlideSection.style.display = "none";

        if (selectedValue === "Assignment") {
            addAssignmentSection.style.display = "flex";
        } else if (selectedValue === "Progress Report") {
            addReportSection.style.display = "flex";
        } else {
            addSlideSection.style.display = "flex"; // Default
        }
    }

    // Set default visibility on page load
    updateFormVisibility();

    // Update visibility on change
    fileTypeSelect.addEventListener("change", updateFormVisibility);

    // Handle file input changes for assignment
    assignmentFileInput.addEventListener("change", function() {
        handleFileInputChange(assignmentFileInput, assignmentFileNameDisplay);
    });

    // Handle file input changes for report
    reportFileInput.addEventListener("change", function() {
        handleFileInputChange(reportFileInput, reportFileNameDisplay);
    });

    // Handle file input changes for slide
    slideFileInput.addEventListener("change", function() {
        handleFileInputChange(slideFileInput, slideFileNameDisplay);
    });

    // Function to handle file input changes
    function handleFileInputChange(fileInput, fileNameDisplay) {
        const file = fileInput.files[0];

        if (file) {
            const validFormats = ["application/pdf"];

            if (!validFormats.includes(file.type)) {
                alert("Only PDF files are allowed.");
                fileInput.value = "";
                fileNameDisplay.textContent = "No file selected";
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert("File size exceeds 2MB. Please upload a smaller file.");
                fileInput.value = "";
                fileNameDisplay.textContent = "No file selected";
                return;
            }

            // Display the selected file name
            fileNameDisplay.textContent = `Selected File: ${file.name}`;
        } else {
            fileNameDisplay.textContent = "No file selected";
        }
    }
});
</script>

<?php require_once(get_template_directory() . '/course/templates/footer.php'); ?>