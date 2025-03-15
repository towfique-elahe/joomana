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

    // Check if at least one teacher is selected
    $assigned_teachers_array = isset($_POST['assigned_teachers']) ? $_POST['assigned_teachers'] : array();
    if (empty($assigned_teachers_array)) {
        $error_message = 'Veuillez sélectionner au moins un enseignant.';
    }
    
    // Proceed only if there are no errors
    if (empty($error_message)) {
        // Sanitize user inputs
        $is_recurring = isset($_POST['is_recurring']) ? filter_var($_POST['is_recurring'], FILTER_VALIDATE_BOOLEAN) : false;
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
        $time_slot = sanitize_text_field($_POST['time_slot']);
        $assigned_teachers_array = isset($_POST['assigned_teachers']) ? $_POST['assigned_teachers'] : array();
        $assigned_teachers_json = json_encode($assigned_teachers_array);

        // Handle start_date and end_date based on is_recurring
        if ($is_recurring) {
            $start_date = sanitize_text_field($_POST['recurring_start_date']);
            $end_date = sanitize_text_field($_POST['recurring_end_date']);
        } else {
            $start_date = sanitize_text_field($_POST['start_date']);
            $end_date = null; // No end_date for non-recurring courses
        }

        // Handle image upload (unchanged)
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
                    'is_recurring'         => (int) $is_recurring,
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
                    'course_material'      => $course_material,
                    'start_date'           => $start_date,
                    'end_date'             => $end_date,
                    'time_slot'            => $time_slot,
                    'assigned_teachers'    => $assigned_teachers_json,
                    'image'                => $uploaded_image_path,
                ],
                [
                    '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s',
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

                            // If the course is recurring, insert data into the recurring_class_sessions table for each teacher's group
                            if ($is_recurring) {
                                // Sanitize recurring session data
                                $recurring_days = isset($_POST['recurring_days']) ? array_map('sanitize_text_field', $_POST['recurring_days']) : [];
                                $recurring_days_json = json_encode($recurring_days); // Convert array to JSON
                                $recurring_start_time_1 = sanitize_text_field($_POST['recurring_start_time_1']);
                                $recurring_end_time_1 = sanitize_text_field($_POST['recurring_end_time_1']);
                                $recurring_start_time_2 = sanitize_text_field($_POST['recurring_start_time_2']);
                                $recurring_end_time_2 = sanitize_text_field($_POST['recurring_end_time_2']);

                                // Insert recurring session data into the recurring_class_sessions table for this group
                                $insert_recurring_session = $wpdb->insert(
                                    $wpdb->prefix . 'recurring_class_sessions',
                                    [
                                        'course_id'            => $course_id,
                                        'group_number'         => $group_number, // Use the same group number as the teacher
                                        'recurring_start_date' => $start_date,
                                        'recurring_end_date'   => $end_date,
                                        'recurring_days'       => $recurring_days_json,
                                        'recurring_start_time_1' => $recurring_start_time_1,
                                        'recurring_end_time_1'   => $recurring_end_time_1,
                                        'recurring_start_time_2' => $recurring_start_time_2,
                                        'recurring_end_time_2'   => $recurring_end_time_2,
                                    ],
                                    [
                                        '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
                                    ]
                                );

                                if ($insert_recurring_session === false) {
                                    error_log("Error inserting recurring session for group $group_number: " . $wpdb->last_error);
                                }
                            } else {

                                // Insert non-recurring session data into the class_sessions table for this group
                                $insert_class_session = $wpdb->insert(
                                    $wpdb->prefix . 'class_sessions',
                                    [
                                        'course_id'    => $course_id,
                                        'group_number' => $group_number, // Use the same group number as the teacher
                                        'start_date'   => $start_date,
                                        'time_slot'    => $time_slot,
                                    ],
                                    [
                                        '%d', '%d', '%s', '%s'
                                    ]
                                );

                                if ($insert_class_session === false) {
                                    error_log("Error inserting class session for group $group_number: " . $wpdb->last_error);
                                }
                            }

                            // Insert payment for the teacher
                            $teacher_table = $wpdb->prefix . 'teachers';
                            $payments_table = $wpdb->prefix . 'teacher_payments';

                            // Fetch teacher data
                            $teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $teacher_id));

                            // Generate a unique invoice number
                            do {
                                $invoice_number = 'JMI-' . uniqid() . '-' . bin2hex(random_bytes(4));
                                $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $payments_table WHERE invoice_number = %s", $invoice_number));
                            } while ($exists > 0);

                            // Set payment details
                            $currency = 'EUR';
                            $payment_method = 'Bank';
                            $status = 'in progress';
                            $deposit = ($teacher->country === 'France') ? 26 : 13; // Set deposit amount based on teacher's country

                            $due = floatval($teacher->due); // Get past due amount

                            // Insert payment into the database
                            $inserted_payment = $wpdb->insert(
                                $payments_table,
                                [
                                    'invoice_number'        => $invoice_number,
                                    'teacher_id'           => $teacher_id,
                                    'due'                  => $due,
                                    'deposit'             => $deposit,
                                    'currency'            => $currency,
                                    'payment_method'      => $payment_method,
                                    'status'              => $status,
                                ],
                                [
                                    '%s', '%d', '%f', '%s', '%s', '%s',
                                ]
                            );

                            if ($inserted_payment === false) {
                                error_log("Error inserting payment for teacher $teacher_id: " . $wpdb->last_error);
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
                <a href="<?php echo home_url('/admin/course-management/courses'); ?>" class="breadcrumb-link">Gestion
                    de cours</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Ajouter un cours</span>
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

                <!-- Add Course -->
                <section class="section col">
                    <h3 class="section-heading">Ajouter un nouveau cours</h3>

                    <div class="row">
                        <label for="is_recurring">Récurrent</label>
                        <input type="checkbox" id="is_recurring" name="is_recurring" value="true">
                    </div>

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
                global $wpdb;
                $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}course_categories");
                if ($categories) {
                    foreach ($categories as $category) {
                        echo '<option value="' . esc_attr($category->category) . '">' . esc_html($category->category) . '</option>';
                    }
                } else {
                    echo '<option disabled>No categories found</option>';
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
                                    <!-- Topics will be populated dynamically here -->
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
                        <div class="col">
                            <label for="course_material">Matériel de cours <span class="required"></span></label>
                            <input type="url" name="course_material" id="course_material"
                                placeholder="Lien vers le matériel de cours">
                        </div>
                    </div>

                    <div class="row recurring-dates">
                        <div class="col">
                            <label for="recurring_start_date">Date de début <span class="required">*</span></label>
                            <input type="date" id="recurring_start_date" name="recurring_start_date">
                        </div>
                        <div class="col">
                            <label for="recurring_end_date">Date de fin <span class="required">*</span></label>
                            <input type="date" name="recurring_end_date" id="recurring_end_date">
                        </div>
                    </div>

                    <div class="row recurring-days">
                        <div class="col">
                            <label for="recurring_days">Jours</label>
                            <div class="row checkbox-group">
                                <label class="row"><input type="checkbox" name="recurring_days[]" value="Monday">
                                    Lundi</label>
                                <label class="row"><input type="checkbox" name="recurring_days[]" value="Tuesday">
                                    Mardi</label>
                                <label class="row"><input type="checkbox" name="recurring_days[]" value="Wednesday">
                                    Mercredi</label>
                                <label class="row"><input type="checkbox" name="recurring_days[]" value="Thursday">
                                    Jeudi</label>
                                <label class="row"><input type="checkbox" name="recurring_days[]" value="Friday">
                                    Vendredi</label>
                                <label class="row"><input type="checkbox" name="recurring_days[]" value="Saturday">
                                    Samedi</label>
                                <label class="row"><input type="checkbox" name="recurring_days[]" value="Sunday">
                                    Dimanche</label>
                            </div>
                        </div>
                    </div>

                    <div class="col recurring-time-slots slot-1">
                        <div class="row">
                            <div class="col">
                                <label for="recurring_start_time_1">Heure de début (Emplacement 1) <span
                                        class="required">*</span></label>
                                <input type="time" name="recurring_start_time_1" id="recurring_start_time_1">
                            </div>
                            <div class="col">
                                <label for="recurring_end_time_1">Fin des temps (Emplacement 1) <span
                                        class="required">*</span></label>
                                <input type="time" name="recurring_end_time_1" id="recurring_end_time_1">
                            </div>
                        </div>
                    </div>

                    <div class="col recurring-time-slots slot-2">
                        <div class="row">
                            <div class="col">
                                <label for="recurring_start_time_2">Heure de début (Emplacement 2) <span
                                        class="required">*</span></label>
                                <input type="time" name="recurring_start_time_2" id="recurring_start_time_2">
                            </div>
                            <div class="col">
                                <label for="recurring_end_time_2">Fin des temps (Emplacement 2) <span
                                        class="required">*</span></label>
                                <input type="time" name="recurring_end_time_2" id="recurring_end_time_2">
                            </div>
                        </div>
                    </div>

                    <div class="row calendar-container">
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
                                            echo '<span class="teacher-status available">Disponible</span>';
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

                    <button type="submit" class="submit-button" name="add_course">Ajouter</button>
                </section>
            </form>
        </div>

    </div>
</div>

<!-- Add jQuery (if not already included) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const recurringCheckbox = document.getElementById("is_recurring");
    const calendar = document.querySelector(".calendar-container");
    const dateInputs = document.querySelector(".recurring-dates");
    const dayInputs = document.querySelector(".recurring-days");
    const timeSlot1Inputs = document.querySelector(".recurring-time-slots.slot-1");
    const timeSlot2Inputs = document.querySelector(".recurring-time-slots.slot-2");

    function toggleVisibility() {
        if (recurringCheckbox.checked) {
            calendar.style.display = "none";
            dateInputs.style.display = "flex";
            dayInputs.style.display = "flex";
            timeSlot1Inputs.style.display = "flex";
            timeSlot2Inputs.style.display = "flex";
        } else {
            calendar.style.display = "flex";
            dateInputs.style.display = "none";
            dayInputs.style.display = "none";
            timeSlot1Inputs.style.display = "none";
            timeSlot2Inputs.style.display = "none";
        }
    }

    // Attach event listener to checkbox
    recurringCheckbox.addEventListener("change", toggleVisibility);

    // Run the function on page load in case the checkbox is pre-checked
    toggleVisibility();
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