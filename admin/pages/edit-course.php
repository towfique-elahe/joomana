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
    // Sanitize user inputs
    $title = sanitize_text_field($_POST['title']);
    $description = wp_kses_post($_POST['description']);
    $category = sanitize_text_field($_POST['category']);
    $topic = sanitize_text_field($_POST['topic']);
    $grade = sanitize_text_field($_POST['grade']);
    $level = sanitize_text_field($_POST['level']);
    $max_students_per_group = intval($_POST['max_students_per_group']);
    $max_student_groups = intval($_POST['max_student_groups']);
    $max_teachers = intval($_POST['max_teachers']);
    $duration = intval($_POST['duration']);
    $required_credit = intval($_POST['required_credit']);
    $course_material = esc_url_raw($_POST['course_material']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $time_slot = sanitize_text_field($_POST['time_slot']);
    $assigned_teachers = isset($_POST['assigned_teachers']) ? json_encode($_POST['assigned_teachers']) : '';

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
                'required_credit'     => $required_credit,
                'course_material'      => $course_material,
                'start_date'          => $start_date,
                'time_slot'            => $time_slot,
                'assigned_teachers'   => $assigned_teachers,
                'image'                => $uploaded_image_path,
            ],
            ['id' => $course_id],
            [
                '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s',
            ],
            ['%d']
        );

        if ($updated !== false) {
            // Delete existing teacher associations
            $wpdb->delete(
                $wpdb->prefix . 'teacher_courses',
                ['course_id' => $course_id],
                ['%d']
            );

            // Insert new teacher associations
            $assigned_teachers_array = isset($_POST['assigned_teachers']) ? 
                array_map('intval', (array)$_POST['assigned_teachers']) : 
                [];

            $max_teachers = intval($_POST['max_teachers']);
            $total_teachers = count($assigned_teachers_array);

            for ($i = 0; $i < min($total_teachers, $max_teachers); $i++) {
                $teacher_id = $assigned_teachers_array[$i];
                $group_number = $i + 1;

                $wpdb->insert(
                    $wpdb->prefix . 'teacher_courses',
                    [
                        'teacher_id' => $teacher_id,
                        'course_id' => $course_id,
                        'group_number' => $group_number
                    ],
                    ['%d', '%d', '%d']
                );
            }

            wp_redirect(home_url('/admin/course-management/courses/?success=updated'));
            exit;
        } else {
            $error_message = 'Erreur lors de la mise à jour : ' . $wpdb->last_error;
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
                <a href="<?php echo home_url('/admin/dashboard/course-management'); ?>" class="breadcrumb-link">Gestion
                    de cours</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Modifier un cours</span>
            </div>
        </div>

        <div class="content-section">
            <form action="" class="add-form" method="post" enctype="multipart/form-data">
                <?php if ($error_message): ?>
                <div class="form-error">
                    <p>
                        <?php echo esc_html($error_message); ?>
                    </p>
                </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                <div class="form-success">
                    <p>
                        <?php echo esc_html($success_message); ?>
                    </p>
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
                                        $topics = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_topics");
                                        foreach ($topics as $topic) {
                                            $selected = $course->topic === $topic->topic ? 'selected' : '';
                                            echo '<option value="' . esc_attr($topic->topic) . '" ' . $selected . '>' . esc_html($topic->topic) . '</option>';
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
                            <label for="max_students_per_group">Nombre maximal d'étudiants/groupe</label>
                            <input type="number" id="max_students_per_group" name="max_students_per_group" min="1"
                                value="<?php echo esc_attr($course->max_students_per_group); ?>" placeholder="6">
                        </div>

                        <div class="col">
                            <label for="max_student_groups">Nombre maximal de groupes d'étudiants</label>
                            <input type="number" id="max_student_groups" name="max_student_groups" min="1"
                                value="<?php echo esc_attr($course->max_student_groups); ?>" placeholder="25">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="max_teachers">Max enseignants</label>
                            <input type="number" id="max_teachers" name="max_teachers" min="1"
                                value="<?php echo esc_attr($course->max_teachers); ?>" placeholder="25">
                        </div>

                        <div class="col">
                            <label for="duration">Durée (en heure)</label>
                            <input type="number" id="duration" name="duration" min="1"
                                value="<?php echo esc_attr($course->duration); ?>" placeholder="2">
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
                        <div class="calendar col">
                            <!-- Hidden inputs for date and time -->
                            <input type="hidden" id="start_date" name="start_date"
                                value="<?php echo esc_attr($course->start_date); ?>">
                            <input type="hidden" id="time_slot" name="time_slot"
                                value="<?php echo esc_attr($course->time_slot); ?>">

                            <div class="calendar-header row">
                                <div class="buttons">
                                    <div class="custom-select-wrapper">
                                        <select id="yearSelect">
                                            <option>2022</option>
                                            <option>2023</option>
                                            <option selected>2024</option>
                                            <option>2025</option>
                                        </select>
                                        <i class="fas fa-caret-down custom-arrow"></i>
                                    </div>
                                    <div class="custom-select-wrapper">
                                        <select id="monthSelect">
                                            <option value="1">Janvier</option>
                                            <option value="2">Février</option>
                                            <option value="3">Mars</option>
                                            <option value="4">Avril</option>
                                            <option value="5">Mai</option>
                                            <option value="6">Juin</option>
                                            <option value="7">Juillet</option>
                                            <option value="8">Août</option>
                                            <option value="9">Septembre</option>
                                            <option value="10">Octobre</option>
                                            <option value="11">Novembre</option>
                                            <option value="12" selected>Décembre</option>
                                        </select>
                                        <i class="fas fa-caret-down custom-arrow"></i>
                                    </div>
                                </div>

                                <div>
                                    <button class="button reset" id="resetButton">
                                        <i class="fas fa-undo"></i> Reprogrammer
                                    </button>
                                </div>

                                <div class="special-heading">Date de début</div>
                            </div>

                            <table class="table calendar-table" id="calendarTable">
                                <thead>
                                    <tr>
                                        <th>Dimanche</th>
                                        <th>Lundi</th>
                                        <th>Mardi</th>
                                        <th>Mercredi</th>
                                        <th>Jeudi</th>
                                        <th>Vendredi</th>
                                        <th>Samedi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Calendar dates will be populated dynamically -->
                                </tbody>
                            </table>

                            <table class="table time-table" id="timeTable">
                                <tbody>
                                    <tr>
                                        <td>8:00 AM - 10:00 AM</td>
                                        <td>10:00 AM - 12:00 PM</td>
                                        <td>12:00 PM - 2:00 PM</td>
                                        <td>2:00 PM - 4:00 PM</td>
                                    </tr>
                                    <tr>
                                        <td>4:00 PM - 6:00 PM</td>
                                        <td>6:00 PM - 8:00 PM</td>
                                        <td>8:00 PM - 10:00 PM</td>
                                        <td>10:00 PM - 12:00 AM</td>
                                    </tr>
                                    <tr>
                                        <td>12:00 AM - 2:00 AM</td>
                                        <td>2:00 AM - 4:00 AM</td>
                                        <td>4:00 AM - 6:00 AM</td>
                                        <td>6:00 AM - 8:00 AM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="assigned_teachers">Affecter des enseignants</label>
                            <div class="selected-teachers">
                                <?php
                                    $assigned_teachers = json_decode($course->assigned_teachers, true);
                                    // Define the default image path
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
                                    global $wpdb; // Access the global $wpdb object for database queries
                                    
                                    // Query the custom 'teachers' table
                                    $teachers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}teachers");
                                    
                                    // Define the default image path
                                    $default_image = get_template_directory_uri() . '/assets/image/user.png';
                            
                                    // Check if teachers are available
                                    if ($teachers) {
                                        foreach ($teachers as $teacher) {
                                            // Use the teacher's image if available, otherwise use the default image
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

<?php
require_once(get_template_directory() . '/admin/templates/footer.php');