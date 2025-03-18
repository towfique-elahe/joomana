<?php

/* Template Name: Admin | Edit Course */

// Page title
global $pageTitle;
$pageTitle = 'Modifier un cours';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Fetch course details
if (!isset($_GET['edit_item_id']) || empty($_GET['edit_item_id'])) {
    wp_redirect(home_url('/admin/course-management/courses/'));
    exit;
}

$course_id = intval($_GET['edit_item_id']);
global $wpdb;
$table_name = $wpdb->prefix . 'courses';
$course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $course_id));

if (!$course) {
    wp_redirect(home_url('/admin/course-management/courses/'));
    exit;
}

ob_start();

$error_message = ''; // Initialize error message variable
$success_message = ''; // Initialize success message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_course'])) {
    global $wpdb;

    // Check if at least one teacher is selected
    $assigned_teachers_array = isset($_POST['assigned_teachers']) ? $_POST['assigned_teachers'] : array();
    if (empty($assigned_teachers_array)) {
        $error_message = 'Veuillez sélectionner au moins un enseignant.';
    }

    // Proceed only if there are no errors
    if (empty($error_message)) {
        // Sanitize and validate input data
        $title                = sanitize_text_field($_POST['title']);
        $description          = wp_kses_post($_POST['description']);
        $category             = sanitize_text_field($_POST['category']);
        $topic                = sanitize_text_field($_POST['topic']);
        $grade                = sanitize_text_field($_POST['grade']);
        $level                = sanitize_text_field($_POST['level']);
        $max_student_groups   = intval($_POST['max_student_groups']);
        $max_students_per_group = intval($_POST['max_students_per_group']);
        $max_teachers         = intval($_POST['max_teachers']);
        $duration             = intval($_POST['duration']);
        $required_credit      = floatval($_POST['required_credit']);
        $course_material      = esc_url_raw($_POST['course_material']);
        $start_date           = sanitize_text_field($_POST['start_date']);
        $end_date             = sanitize_text_field($_POST['end_date']);

        // Convert dates to DateTime format
        $start_date_obj = new DateTime($start_date);
        $end_date_obj   = new DateTime($end_date);

        // Get the selected days (recurring days)
        $recurring_days = isset($_POST['days']) ? $_POST['days'] : [];

        // Convert array inputs to JSON format
        $days_json           = json_encode($_POST['days'] ?? []);
        $assigned_teachers_json = json_encode($_POST['assigned_teachers'] ?? []);
        $enrolled_students_json = json_encode($_POST['enrolled_students'] ?? []);

        // Calculate total occurrences of recurring days between start and end date
        $total_days = 0;
        $session_days = [];
        $current_date = clone $start_date_obj;

        while ($current_date <= $end_date_obj) {
            $current_day_name = $current_date->format('l'); // Get day name (e.g., 'Tuesday')
            if (in_array($current_day_name, $recurring_days)) {
                $total_days++;
                $session_days[] = $current_date->format('Y-m-d'); // Store session date
            }
            $current_date->modify('+1 day'); // Move to the next day
        }

        $session_days_json = json_encode($session_days);

        // Handle image upload
        $uploaded_image_path = $course->image; // Default to current image
        if (isset($_FILES['upload_image']) && $_FILES['upload_image']['error'] === UPLOAD_ERR_OK) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');

            $allowed_types = ['image/jpeg', 'image/png'];
            if (in_array($_FILES['upload_image']['type'], $allowed_types)) {
                if ($_FILES['upload_image']['size'] <= 2 * 1024 * 1024) { // 2 MB limit
                    $uploaded_file = wp_handle_upload($_FILES['upload_image'], ['test_form' => false]);
                    if ($uploaded_file && !isset($uploaded_file['error'])) {
                        // Insert image into WordPress Media Library
                        $file = $uploaded_file['file'];
                        $attachment = [
                            'guid'           => $uploaded_file['url'],
                            'post_mime_type' => $_FILES['upload_image']['type'],
                            'post_title'     => sanitize_file_name($_FILES['upload_image']['name']),
                            'post_content'   => '',
                            'post_status'    => 'inherit',
                        ];
                        $attachment_id = wp_insert_attachment($attachment, $file);
                        if (!is_wp_error($attachment_id)) {
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file);
                            wp_update_attachment_metadata($attachment_id, $attachment_data);
                            $uploaded_image_path = wp_get_attachment_url($attachment_id);
                        } else {
                            $error_message = 'Erreur lors de l\'insertion de l\'image.';
                        }
                    } else {
                        $error_message = 'Erreur lors du téléchargement de l\'image : ' . $uploaded_file['error'];
                    }
                } else {
                    $error_message = 'La taille de l\'image ne doit pas dépasser 2 Mo.';
                }
            } else {
                $error_message = 'Format d\'image non valide. Seuls JPEG et PNG sont autorisés.';
            }
        }

        if (empty($error_message)) {
            // Update course in the database
            $updated = $wpdb->update(
                $table_name,
                [
                    'title'                => $title,
                    'description'          => $description,
                    'category'             => $category,
                    'topic'                => $topic,
                    'grade'                => $grade,
                    'level'                => $level,
                    'max_students_per_group' => $max_students_per_group,
                    'max_student_groups'   => $max_student_groups,
                    'max_teachers'         => $max_teachers,
                    'duration'             => $duration,
                    'image'                => $uploaded_image_path,
                    'required_credit'      => $required_credit,
                    'course_material'      => $course_material,
                    'start_date'           => $start_date,
                    'end_date'             => $end_date,
                    'days'                 => $days_json,
                    'total_days'           => $total_days,
                    'session_days'         => $session_days_json,
                    'assigned_teachers'    => $assigned_teachers_json,
                    'enrolled_students'    => $enrolled_students_json,
                ],
                ['id' => $course_id],
                [
                    '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%f', '%s', '%s', '%s', '%s', '%d', '%s', '%s'
                ]
            );

            if ($updated === false) {
                $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
                error_log('SQL Error: ' . $wpdb->last_error);
                exit('Error updating course: ' . esc_html($wpdb->last_error));
            } else {
                // Success message
                $success_message = 'Le cours a été mis à jour avec succès.';
                wp_redirect(home_url('/admin/course-management/courses/?success=updated'));
                exit;
            }
        }
    }
}

ob_end_clean();

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>

    <div id="adminEditCourse" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion de cours</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/course-management/courses'); ?>" class="breadcrumb-link">Gestion
                    de cours</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Modifier un cours</span>
            </div>
        </div>

        <div class="content-section">
            <form action="" class="add-form" method="post" enctype="multipart/form-data">

                <!-- Display error message -->
                <?php if ($error_message): ?>
                <div class="form-error">
                    <p><?php echo esc_html($error_message); ?></p>
                </div>
                <?php endif; ?>

                <!-- Display success message -->
                <?php if ($success_message): ?>
                <div class="form-success">
                    <p><?php echo esc_html($success_message); ?></p>
                </div>
                <?php endif; ?>

                <!-- Edit Course -->
                <section class="section col">
                    <h3 class="section-heading">Modifier le cours</h3>

                    <div class="row">
                        <div class="col">
                            <label for="title">Titre <span class="required">*</span></label>
                            <input type="text" id="title" name="title" value="<?php echo esc_attr($course->title); ?>"
                                placeholder="Tapez le titre" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" placeholder="Description du type"
                                rows="5"><?php echo esc_textarea($course->description); ?></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="">Image</label>
                            <?php 
                                $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png'; 
                                $image = !empty($course->image) ? esc_url($course->image) : esc_url($default_image);
                            ?>
                            <img src="<?php echo $image; ?>" alt="Cours image" class="current-image-preview">
                            <div class="upload-button">
                                <label for="upload_image" class="upload-label">
                                    Mettre à jour l'image <i class="fas fa-upload"></i>
                                </label>
                                <input type="file" id="upload_image" name="upload_image" accept="image/jpeg, image/png"
                                    class="upload-input">
                            </div>
                            <p class="text">(Images uniquement, JPEG/PNG, max 2 Mo)</p>
                            <p class="image-file-name">Aucun fichier sélectionné</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="category">Catégorie <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="category" name="category" required>
                                    <option value="" disabled>Sélectionnez une catégorie</option>
                                    <?php
                                        global $wpdb;
                                        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");
                                        foreach ($categories as $category) {
                                            $selected = $course->category === $category->category ? 'selected' : '';
                                            echo '<option value="' . esc_attr($category->category) . '" ' . $selected . '>' . esc_html($category->category) . '</option>';
                                        }
                                    ?>
                                </select>
                                <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                            </div>
                        </div>

                        <div class="col">
                            <label for="topic">Sujet <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="topic" name="topic" required>
                                    <option value="" disabled>Sélectionnez le sujet</option>
                                    <?php
                                        // Fetch topics for the selected category
                                        if (!empty($course->category)) {
                                            $topics = $wpdb->get_results($wpdb->prepare(
                                                "SELECT * FROM {$wpdb->prefix}course_topics WHERE category_id = (SELECT id FROM {$wpdb->prefix}course_categories WHERE category = %s)",
                                                $course->category
                                            ));

                                            foreach ($topics as $topic) {
                                                $selected = $course->topic === $topic->topic ? 'selected' : '';
                                                echo '<option value="' . esc_attr($topic->topic) . '" ' . $selected . '>' . esc_html($topic->topic) . '</option>';
                                            }
                                        }
                                    ?>
                                </select>
                                <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="grade">Grade <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="grade" name="grade" required>
                                    <option value="" disabled>Sélectionnez le Grade</option>
                                    <?php
                                        $grades = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_grades");
                                        foreach ($grades as $grade) {
                                            $selected = $course->grade === $grade->grade ? 'selected' : '';
                                            echo '<option value="' . esc_attr($grade->grade) . '" ' . $selected . '>' . esc_html($grade->grade) . '</option>';
                                        }
                                    ?>
                                </select>
                                <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                            </div>
                        </div>

                        <div class="col">
                            <label for="level">Niveau <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="level" name="level" required>
                                    <option value="" disabled>Sélectionnez le niveau</option>
                                    <?php
                                        $levels = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_levels");
                                        foreach ($levels as $level) {
                                            $selected = $course->level === $level->level ? 'selected' : '';
                                            echo '<option value="' . esc_attr($level->level) . '" ' . $selected . '>' . esc_html($level->level) . '</option>';
                                        }
                                    ?>
                                </select>
                                <i class="fas fa-chevron-down custom-arrow" style="color: #585858;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="max_students_per_group">Nombre maximal d'étudiants/groupe <span
                                    class="required">*</span></label>
                            <input type="number" id="max_students_per_group" name="max_students_per_group" min="1"
                                value="<?php echo esc_attr($course->max_students_per_group); ?>" placeholder="6"
                                required>
                        </div>

                        <div class="col">
                            <label for="max_student_groups">Nombre maximal de groupes d'étudiants <span
                                    class="required">*</span></label>
                            <input type="number" id="max_student_groups" name="max_student_groups" min="1"
                                value="<?php echo esc_attr($course->max_student_groups); ?>" placeholder="25" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="max_teachers">Max enseignants <span class="required">*</span></label>
                            <input type="number" id="max_teachers" name="max_teachers" min="1"
                                value="<?php echo esc_attr($course->max_teachers); ?>" placeholder="25" required>
                        </div>

                        <div class="col">
                            <label for="duration">Durée (en heure) <span class="required">*</span></label>
                            <input type="number" id="duration" name="duration" min="1"
                                value="<?php echo esc_attr($course->duration); ?>" placeholder="2" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="required_credit">Crédit <span class="required">*</span></label>
                            <input type="number" id="required_credit" name="required_credit" min="1"
                                value="<?php echo esc_attr($course->required_credit); ?>" placeholder="2" required>
                        </div>
                        <div class="col">
                            <label for="course_material">Matériel de cours <span class="required"></span></label>
                            <input type="url" name="course_material" id="course_material"
                                value="<?php echo esc_url($course->course_material); ?>"
                                placeholder="Lien vers le matériel de cours">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="start_date">Date de début <span class="required">*</span></label>
                            <input type="date" id="start_date" name="start_date"
                                value="<?php echo esc_attr($course->start_date); ?>">
                        </div>
                        <div class="col">
                            <label for="end_date">Date de fin <span class="required">*</span></label>
                            <input type="date" name="end_date" id="end_date"
                                value="<?php echo esc_attr($course->end_date); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="days">Jours</label>
                            <div class="row checkbox-group">
                                <?php
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    $course_days = json_decode($course->days, true);
                                    foreach ($days as $day) {
                                        $checked = in_array($day, $course_days) ? 'checked' : '';
                                        echo '<label class="row"><input type="checkbox" name="days[]" id="recurring' . $day . '" value="' . $day . '" ' . $checked . '>' . ucfirst(strtolower($day)) . '</label>';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Time slots for each day -->
                    <?php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        foreach ($days as $day) {
                            $day_lower = strtolower($day);
                            $course_slots = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}course_slots WHERE course_id = %d AND session_day = %s", $course_id, $day));

                            // Default values if no slots are found
                            $slot1_start_time = $course_slots ? $course_slots->slot1_start_time : '08:00';
                            $slot1_end_time = $course_slots ? $course_slots->slot1_end_time : '10:00';
                            $slot2_start_time = $course_slots ? $course_slots->slot2_start_time : '12:00';
                            $slot2_end_time = $course_slots ? $course_slots->slot2_end_time : '14:00';
                    ?>
                    <div class="day-time-slot">
                        <div class="col recurring-time-slots <?php echo $day_lower; ?> slot-1">
                            <h3><?php echo ucfirst(strtolower($day)); ?></h3>
                            <div class="row">
                                <div class="col">
                                    <label for="<?php echo $day_lower; ?>_slot1_start_time">Heure de début (Emplacement
                                        1) <span class="required">*</span></label>
                                    <input type="time" name="<?php echo $day_lower; ?>_slot1_start_time"
                                        id="<?php echo $day_lower; ?>_slot1_start_time"
                                        value="<?php echo esc_attr($slot1_start_time); ?>">
                                </div>
                                <div class="col">
                                    <label for="<?php echo $day_lower; ?>_slot1_end_time">Fin des temps (Emplacement 1)
                                        <span class="required">*</span></label>
                                    <input type="time" name="<?php echo $day_lower; ?>_slot1_end_time"
                                        id="<?php echo $day_lower; ?>_slot1_end_time"
                                        value="<?php echo esc_attr($slot1_end_time); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col recurring-time-slots <?php echo $day_lower; ?> slot-2">
                            <div class="row">
                                <div class="col">
                                    <label for="<?php echo $day_lower; ?>_slot2_start_time">Heure de début (Emplacement
                                        2) <span class="required">*</span></label>
                                    <input type="time" name="<?php echo $day_lower; ?>_slot2_start_time"
                                        id="<?php echo $day_lower; ?>_slot2_start_time"
                                        value="<?php echo esc_attr($slot2_start_time); ?>">
                                </div>
                                <div class="col">
                                    <label for="<?php echo $day_lower; ?>_slot2_end_time">Fin des temps (Emplacement 2)
                                        <span class="required">*</span></label>
                                    <input type="time" name="<?php echo $day_lower; ?>_slot2_end_time"
                                        id="<?php echo $day_lower; ?>_slot2_end_time"
                                        value="<?php echo esc_attr($slot2_end_time); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col">
                            <label for="assigned_teachers">Affecter des enseignants</label>
                            <div class="selected-teachers">
                                <?php
                                    $assigned_teachers = json_decode($course->assigned_teachers, true);
                                    $default_image = get_template_directory_uri() . '/assets/image/user.png';
                                    
                                    if ($assigned_teachers) {
                                        foreach ($assigned_teachers as $teacher_id) {
                                            $teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}teachers WHERE id = %d", $teacher_id));
                                            if ($teacher) {
                                                $image_url = !empty($teacher->image) ? esc_url($teacher->image) : esc_url($default_image);
                                                echo '<div class="teacher-card" data-id="' . esc_attr($teacher->id) . '">';
                                                echo '<img src="' . $image_url . '" alt="' . esc_attr($teacher->first_name) . ' ' . esc_attr($teacher->last_name) . '" class="teacher-image">';
                                                echo '<h3 class="teacher-name">' . esc_html($teacher->first_name) . ' ' . esc_html($teacher->last_name) . '</h3>';
                                                echo '<button class="remove-teacher" type="button">&#10060;</button>';
                                                echo '<input type="hidden" name="assigned_teachers[]" value="' . esc_attr($teacher->id) . '">';
                                                echo '</div>';
                                            }
                                        }
                                    }
                                ?>
                            </div>

                            <div class="search-teacher col">
                                <input type="text" id="search-teacher-input" placeholder="Recherche d'enseignants">
                                <div class="teacher-container" id="teacher-container">
                                    <?php
                                    global $wpdb;
                                    // Query the custom 'teachers' table where status is 'Approuvé'
                                    $teachers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}teachers WHERE status = 'Approuvé'");
                                    $default_image = get_template_directory_uri() . '/assets/image/user.png';

                                    if ($teachers) {
                                        foreach ($teachers as $teacher) {
                                            $image_url = !empty($teacher->image) ? esc_url($teacher->image) : esc_url($default_image);
                                            echo '<div class="teacher-card" data-id="' . esc_attr($teacher->id) . '">';
                                            echo '<img src="' . $image_url . '" alt="' . esc_attr($teacher->first_name) . ' ' . esc_attr($teacher->last_name) . '" class="teacher-image">';
                                            echo '<h3 class="teacher-name">' . esc_html($teacher->first_name) . ' ' . esc_html($teacher->last_name) . '</h3>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<p>Aucun enseignant trouvé.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="submit-button" name="edit_course">Modifier</button>
                </section>
            </form>
        </div>

    </div>
</div>

<!-- Add jQuery (if not already included) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Get all day checkboxes
    const days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

    days.forEach(day => {
        const dayCheck = document.getElementById(`recurring${day}`);
        const dayTimeSlot = document.querySelector(
            `.day-time-slot .recurring-time-slots.${day.toLowerCase()}`).parentElement;

        function toggleVisibility() {
            if (dayCheck.checked) {
                dayTimeSlot.style.display = "block"; // Show the entire day-time-slot
            } else {
                dayTimeSlot.style.display = "none"; // Hide the entire day-time-slot
            }
        }

        // Attach event listener to checkbox
        dayCheck.addEventListener("change", toggleVisibility);

        // Run the function on page load in case the checkbox is pre-checked
        toggleVisibility();
    });
});

jQuery(document).ready(function($) {
    // Event listener for category dropdown change
    $('#category').on('change', function() {
        var selectedCategory = $(this).val(); // Get the selected category

        if (selectedCategory) {
            // Clear the topic dropdown
            $('#topic').html('<option value="" disabled selected>Sélectionnez le sujet</option>');

            // Send AJAX request to fetch topics
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>', // WordPress AJAX endpoint
                type: 'POST',
                data: {
                    action: 'fetch_topics', // AJAX action
                    category: selectedCategory // Selected category
                },
                success: function(response) {
                    if (response.success) {
                        // Populate the topic dropdown
                        var topics = response.data;
                        if (topics.length > 0) {
                            topics.forEach(function(topic) {
                                $('#topic').append('<option value="' + topic.topic +
                                    '">' + topic.topic + '</option>');
                            });
                        } else {
                            $('#topic').append('<option disabled>No topics found</option>');
                        }
                    } else {
                        console.error('Error fetching topics');
                    }
                },
                error: function() {
                    console.error('AJAX request failed');
                }
            });
        }
    });
});
</script>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>