<?php

/* Template Name: Course | Submissions */

// Page title
global $pageTitle;
$pageTitle = 'Soumission des devoirs';

require_once(get_template_directory() . '/course/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the current user
$user = wp_get_current_user();

// Get session_id from session
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

if (in_array('parent', (array) $user->roles)) {
    $student_id = intval($_GET['student_id']);
} elseif (in_array('student', (array) $user->roles)) {
    $student_id = $user->ID;
} elseif (in_array('teacher', (array) $user->roles)) {
    $teacher_id = $user->ID;
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
                'session_id' => $session_id,
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

// Fetch files from the database
$submissions = [];

if (in_array('teacher', (array) $user->roles)) {
    // Fetch submissions for the teacher
    $submissions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}student_submissions WHERE session_id = %d",
        $session_id
    ));

} elseif (current_user_can('student') || current_user_can('parent')) {
    // Fetch submissions for the student
    $submissions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}student_submissions WHERE session_id = %d AND student_id = %d",
        $session_id,
        $student_id
    ));

}

// Handle file deletion for students
if (in_array('student', (array) $user->roles)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
        global $wpdb;

        // Get the file ID from the form submission
        $file_id = intval($_POST['delete_item_id']);

        // Determine the table and file URL based on the file type
        $table_name = $wpdb->prefix . 'student_submissions';
        $file_url = $wpdb->get_var($wpdb->prepare(
            "SELECT file FROM $table_name WHERE id = %d",
            $file_id
        ));

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
    <div id="courseSubmissions" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Soumission des devoirs</h2>
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

            <div class="row file-container">
                <?php if ($submissions) :?>

                <!-- Display Submissions -->
                <?php foreach ($submissions as $submission) : ?>
                <div class="file-card">
                    <div class="file-top">
                        <p class="file-type submission">Soumission</p>
                        <?php
                            if (in_array('student', (array) $user->roles)) {
                        ?>
                        <form method="post" class="delete-form">
                            <input type="hidden" name="delete_item_id" value="<?php echo esc_attr($submission->id); ?>">
                            <button type="button" class="button file-delete open-modal" data-modal="fileDelete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        <?php
                            }
                        ?>
                        <a href="<?php echo esc_url($submission->file); ?>" class="download-button" download>
                            <i class="fas fa-download"></i>
                        </a>
                        <div class="file-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                    </div>
                    <div class="file-bottom row">
                        <div class="col">
                            <h3 class="file-title">Soumission | <?php echo basename($submission->file); ?></h3>
                            <?php
                                if (in_array('teacher', (array) $user->roles)) {
                                    $student_id = $submission->student_id;
                                    // Fetch the student's details using the student_id
                                    $student_table = $wpdb->prefix. 'students';
                                    $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $student_id));
                            ?>
                            <p class="file-info">
                                Elèves:
                                <a href="<?php echo esc_url(home_url('/course/student-management/student-details/?id=' . $student->id . '&session_id=' . $session_id)); ?>"
                                    class="accent"><?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?></a>
                            </p>
                            <?php
                                }
                            ?>
                            <p class="file-info">
                                Téléchargé: <?php echo date('d M, y', strtotime($submission->created_at)); ?>
                            </p>
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