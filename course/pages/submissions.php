<?php

/* Template Name: Course | Submissions */

// page title
global $pageTitle;
$pageTitle = 'Soumission des devoirs';

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

$group_number = 0;
if (in_array('student', (array) $user->roles)) {
    $student_id = $user->ID;
    $student_group = $wpdb->get_var($wpdb->prepare(
        "SELECT group_number FROM {$wpdb->prefix}student_courses WHERE student_id = %d AND course_id = %d LIMIT 1",
        $student_id,
        $course_id
    ));
    if ($student_group) {
        $group_number = intval($student_group);
    }
} elseif (in_array('teacher', (array) $user->roles)) {
    $teacher_id = $user->ID;
    $teacher_group = $wpdb->get_var($wpdb->prepare(
        "SELECT group_number FROM {$wpdb->prefix}teacher_courses WHERE teacher_id = %d AND course_id = %d LIMIT 1",
        $teacher_id,
        $course_id
    ));
    if ($teacher_group) {
        $group_number = intval($teacher_group);
    }
}

// Handle assignment upload for students
if (in_array('student', (array) $user->roles)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_assignment') {
        global $wpdb;
        $table_name = $wpdb->prefix . "student_submissions";
        $file_field = "upload_assignment";

        // Include the necessary WordPress file handling functions
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Handle file upload
        $uploaded_file_url = upload_file($file_field);

        if ($uploaded_file_url) {
            $wpdb->insert($table_name, [
                'course_id' => $course_id,
                'group_number' => $group_number,
                'student_id' => $student_id,
                'file' => $uploaded_file_url,
                'created_at' => current_time('mysql'),
            ]);

            // Redirect to prevent resubmission
            wp_safe_redirect($_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

// Function to handle file upload
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
            } else {
                error_log('Attachment Error: ' . $attachment_id->get_error_message());
            }
        } else {
            error_log('Upload Error: ' . $uploaded_file['error']);
        }
    } else {
        error_log('File Error: ' . $_FILES[$file_key]['error']);
    }
    return null;
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/course/templates/sidebar.php'); ?>
    </div>
    <div id="courseSubmissions" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Soumission des devoirs</h2>
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
                <span class="active">Soumission des devoirs</span>
            </div>
        </div>

        <div class="content-section">
            <?php
                if (in_array('student', (array) $user->roles)) {
            ?>
            <form method="post" action="" class="form add-assignment-form" enctype="multipart/form-data"
                id="assignmentForm">
                <input type="hidden" name="action" value="add_assignment">

                <div class="row">
                    <div class="upload-file-button">
                        <label for="uploadAssignment" class="upload-file-label">
                            <i class="fas fa-plus"></i> Joindre votre devoir
                        </label>
                        <input type="file" id="uploadAssignment" name="upload_assignment" accept=".pdf"
                            class="upload-file-input">
                    </div>
                    <div class="col">
                        <p class="text">(PDF uniquement, max 2 Mo)</p>
                        <p class="file-name" id="assignmentFileName">Aucun fichier sélectionné</p>
                    </div>
                </div>

            </form>
            <?php
                }
            ?>
        </div>

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

<script>
document.addEventListener("DOMContentLoaded", function() {
    const assignmentFileInput = document.getElementById("uploadAssignment");
    const assignmentFileNameDisplay = document.getElementById("assignmentFileName");
    const assignmentForm = document.getElementById("assignmentForm");

    // Handle file input changes for assignment
    assignmentFileInput.addEventListener("change", function() {
        handleFileInputChange(assignmentFileInput, assignmentFileNameDisplay, assignmentForm);
    });

    // Function to handle file input changes and auto-submit
    function handleFileInputChange(fileInput, fileNameDisplay, form) {
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

            // Auto-submit the form
            form.submit();
        } else {
            fileNameDisplay.textContent = "No file selected";
        }
    }
});
</script>

<?php require_once(get_template_directory() . '/course/templates/footer.php'); ?>