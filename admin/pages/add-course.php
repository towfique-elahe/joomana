<?php

/* Template Name: Admin | Course Management */

// page title
global $pageTitle;
$pageTitle = 'Ajouter un cours';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

ob_start();

$error_message = ''; // Initialize error message variable
$success_message = ''; // Initialize success message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    global $wpdb;

    $error_message = '';
    $success_message = '';

    // Sanitize user inputs
    $title = sanitize_text_field($_POST['title']);
    $description = sanitize_textarea_field($_POST['description']);
    $category = sanitize_text_field($_POST['category']);
    $topic = sanitize_text_field($_POST['topic']);
    $grade = sanitize_text_field($_POST['grade']);
    $level = sanitize_text_field($_POST['level']);
    $max_students_per_group = intval($_POST['max_students_per_group']);
    $max_student_groups = intval($_POST['max_student_groups']);
    $max_teachers = intval($_POST['max_teachers']);
    $duration = intval($_POST['duration']);
    $required_credit = intval($_POST['required_credit']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $time_slot = sanitize_text_field($_POST['time_slot']);
    // Retrieve the array of teacher IDs submitted via "assigned_teachers[]"
    $assigned_teachers_array = isset($_POST['assigned_teachers']) ? $_POST['assigned_teachers'] : array();

    // (Optional) If you want to store the teacher assignments as a JSON string in the courses table:
    $assigned_teachers_json = json_encode($assigned_teachers_array);

    // Handle image upload
    $uploaded_image_path = '';
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
        // Insert course into the database
        $table_name = $wpdb->prefix . 'courses';
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'courses',
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
                'required_credit'      => $required_credit,
                'start_date'           => $start_date,
                'time_slot'            => $time_slot,
                'assigned_teachers'    => $assigned_teachers_json,
                'image'                => $uploaded_image_path,
            ],
            [
                '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s',
            ]
        );
        
        // Get course ID AFTER successful insertion
        if ($inserted === false) {
            error_log("Error inserting course: " . $wpdb->last_error);
            exit("Error inserting course: " . esc_html($wpdb->last_error)); // Stop execution if failed
        }
        
        $course_id = $wpdb->insert_id;
    
        if ($inserted === false) {
            $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
        } else {
            // Assign teachers to the course
            if ($course_id) {
                if (!empty($assigned_teachers_array) && is_array($assigned_teachers_array)) {
                    $total_teachers = count($assigned_teachers_array);
                    for ($i = 0; $i < min($total_teachers, $max_teachers); $i++) {
                        $teacher_id = intval($assigned_teachers_array[$i]);
                        $group_number = $i + 1; // For group numbering
                
                        $insert_teacher = $wpdb->insert(
                            $wpdb->prefix . 'teacher_courses',
                            [
                                'teacher_id'   => $teacher_id,
                                'course_id'    => $course_id,
                                'group_number' => $group_number,
                            ],
                            [ '%d', '%d', '%d' ]
                        );
                
                        if ($insert_teacher === false) {
                            error_log("Error inserting teacher-course relation: " . $wpdb->last_error);
                        }
                    }
                }
            } else {
                error_log("Error: Course ID not retrieved after insertion!");
            }

            $success_message = 'Le cours a été ajouté avec succès.';
            wp_redirect(home_url('/admin/course-management/courses/'));
            exit;
        }
    }
}

ob_end_clean();

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>

    <div id="adminAddCourse" class="main-content">
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
                <span class="active">Ajouter un cours</span>
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

                <!-- Add Course -->
                <section class="section col">
                    <h3 class="section-heading">Ajouter un nouveau cours</h3>

                    <div class="row">
                        <div class="col">
                            <label for="title">Titre <span class="required">*</span></label>
                            <input type="text" id="title" name="title" placeholder="Tapez le titre" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" placeholder="Description du type"
                                rows="5"></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="">Image</label>
                            <div class="upload-button">
                                <label for="upload_image" class="upload-label">
                                    Télécharger l'image <i class="fas fa-upload"></i>
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
                                    <option value="" disabled selected>Sélectionnez une catégorie</option>

                                    <?php
                                                        global $wpdb; // Access the global $wpdb object for database queries
                
                                                        // Query the custom 'course_categories' table
                                                        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");
                
                                                        // Check if categories are available
                                                        if ($categories) {
                                                            foreach ($categories as $category) {
                                                                echo '<option value="' . esc_attr($category->category) . '">' . esc_html($category->category) . '</option>';
                                                            }
                                                        } else {
                                                            echo '<option disabled>No categorie found</option>';
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
                                    <option value="" disabled selected>Sélectionnez le sujet</option>

                                    <?php
                                                        global $wpdb; // Access the global $wpdb object for database queries
                
                                                        // Query the custom 'course_topics' table
                                                        $topics = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_topics");
                
                                                        // Check if topics are available
                                                        if ($topics) {
                                                            foreach ($topics as $topic) {
                                                                echo '<option value="' . esc_attr($topic->topic) . '">' . esc_html($topic->topic) . '</option>';
                                                            }
                                                        } else {
                                                            echo '<option disabled>No sujets found</option>';
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
                                    <option value="" disabled selected>Sélectionnez le Grade</option>

                                    <?php
                                                        global $wpdb; // Access the global $wpdb object for database queries
                
                                                        // Query the custom 'course_grades' table
                                                        $grades = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_grades");
                
                                                        // Check if grades are available
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
                        </div>

                        <div class="col">
                            <label for="level">Niveau <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="level" name="level" required>
                                    <option value="" disabled selected>Sélectionnez le niveau</option>

                                    <?php
                                                        global $wpdb; // Access the global $wpdb object for database queries
                
                                                        // Query the custom 'course_levels' table
                                                        $levels = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_levels");
                
                                                        // Check if levels are available
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
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="max_students_per_group">Nombre maximal d'étudiants/groupe <span
                                    class="required">*</span></label>
                            <input type="number" id="max_students_per_group" name="max_students_per_group" min="1"
                                placeholder="6" required>
                        </div>

                        <div class="col">
                            <label for="max_student_groups">Nombre maximal de groupes d'étudiants <span
                                    class="required">*</span></label>
                            <input type="number" id="max_student_groups" name="max_student_groups" min="1"
                                placeholder="25" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="max_teachers">Max enseignants <span class="required">*</span></label>
                            <input type="number" id="max_teachers" name="max_teachers" min="1" placeholder="25"
                                required>
                        </div>

                        <div class="col">
                            <label for="duration">Durée (en heure) <span class="required">*</span></label>
                            <input type="number" id="duration" name="duration" min="1" placeholder="2" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="required_credit">Crédit <span class="required">*</span></label>
                            <input type="number" id="required_credit" name="required_credit" min="1" placeholder="2"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="assigned_teachers">Affecter des enseignants</label>
                            <div class="selected-teachers">
                                <!-- Placeholder for selected teachers -->
                            </div>
                            <div class="search-teacher col">
                                <input type="text" id="search-teacher-input" placeholder="Recherche d'enseignant">
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

                    <div class="row">
                        <div class="calendar col">
                            <!-- date input -->
                            <input type="hidden" id="start_date" name="start_date">
                            <!-- time input -->
                            <input type="hidden" id="time_slot" name="time_slot">

                            <div class="calendar-header row">
                                <div class="buttons">
                                    <div class="custom-select-wrapper">
                                        <select id="yearSelect">
                                            <option>2024</option>
                                            <option selected>2025</option>
                                            <option>2026</option>
                                            <option>2027</option>
                                            <option>2028</option>
                                            <option>2029</option>
                                            <option>2030</option>
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
                                    <button class="button reset" id="resetButton"><i class="fas fa-undo"></i>
                                        Reprogrammer</button>
                                </div>

                                <div class="special-heading">Date de début</div>
                            </div>
                            <table class="table calendar-table" id="calendarTable">
                                <thead>
                                    <tr>
                                        <th>dimanche</th>
                                        <th>lundi</th>
                                        <th>Mardi</th>
                                        <th>Mercredi</th>
                                        <th>Jeudi</th>
                                        <th>vendredi</th>
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
                                        <td>2:00 PM - 4:00 AM</td>
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

                    <button type="submit" class="submit-button" name="add_course">Ajouter</button>
                </section>
            </form>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>