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

    // Check required fields
    if (empty($_POST['description'])) {
        $error_message = 'Veuillez fournir une description du cours.';
    } elseif (empty($_POST['start_date'])) {
        $error_message = 'Veuillez sélectionner une date de début.';
    } elseif (empty($_POST['end_date'])) {
        $error_message = 'Veuillez sélectionner une date de fin.';
    } elseif (empty($_POST['days'])) {
        $error_message = 'Veuillez sélectionner au moins un jour.';
    } elseif (empty($_POST['assigned_teachers'])) {
        $error_message = 'Veuillez sélectionner au moins un enseignant.';
    }

    // Check time slots for selected days
    if (empty($error_message)) {
        $days = $_POST['days'];
        foreach ($days as $day) {
            $day_lower = strtolower($day);
            if (empty($_POST[$day_lower . '_slot1_start_time']) || empty($_POST[$day_lower . '_slot1_end_time'])) {
                $error_message = "Veuillez remplir tous les créneaux horaires pour le $day.";
                break;
            }
        }
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
        $duration             = sanitize_text_field($_POST['duration']);
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
            // Database table
            $courses_table = $wpdb->prefix . 'courses';

            // Insert data into the courses table
            $inserted = $wpdb->insert(
                $courses_table,
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
                [
                    '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%d', '%s', '%s'
                ]
            );

            if ($inserted === false) {
                $error_message = 'Erreur: ' . esc_html($wpdb->last_error);
                error_log('SQL Error: ' . $wpdb->last_error);
                exit('Error inserting course: ' . esc_html($wpdb->last_error));
            } else {
                $course_id = $wpdb->insert_id; // Get the ID of the newly inserted course

                // Insert course slots into the course_slots table
                foreach ($recurring_days as $day) {
                    $slot1_start_time = sanitize_text_field($_POST[strtolower($day) . '_slot1_start_time']);
                    $slot1_end_time   = sanitize_text_field($_POST[strtolower($day) . '_slot1_end_time']);
                    $slot2_start_time = sanitize_text_field($_POST[strtolower($day) . '_slot2_start_time']);
                    $slot2_end_time   = sanitize_text_field($_POST[strtolower($day) . '_slot2_end_time']);

                    $wpdb->insert(
                        $wpdb->prefix . 'course_slots',
                        [
                            'course_id'        => $course_id,
                            'session_day'      => $day,
                            'slot1_start_time' => $slot1_start_time,
                            'slot1_end_time'   => $slot1_end_time,
                            'slot2_start_time' => $slot2_start_time,
                            'slot2_end_time'   => $slot2_end_time,
                        ],
                        [
                            '%d', '%s', '%s', '%s', '%s', '%s'
                        ]
                    );
                }

                // Success message
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
                            <label for="grade">Classe <span class="required">*</span></label>
                            <div class="custom-select-wrapper">
                                <select id="grade" name="grade" required>
                                    <option value="" disabled selected>Sélectionnez le classe</option>

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
                                                            echo '<option disabled>No Classe found</option>';
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
                            <label for="max_students_per_group">Nombre maximal d'elèvess/groupe <span
                                    class="required">*</span></label>
                            <input type="number" id="max_students_per_group" name="max_students_per_group" min="1"
                                placeholder="6" required>
                        </div>

                        <div class="col">
                            <label for="max_student_groups">Nombre maximal de groupes d'elèvess <span
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
                            <input type="text" id="duration" name="duration" placeholder="1h 30m" required>
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

                    <div class="row">
                        <div class="col">
                            <label for="start_date">Date de début <span class="required">*</span></label>
                            <input type="date" id="start_date" name="start_date" required>
                        </div>
                        <div class="col">
                            <label for="end_date">Date de fin <span class="required">*</span></label>
                            <input type="date" name="end_date" id="end_date" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="days">Jours</label>
                            <div class="row checkbox-group">
                                <label class="row"><input type="checkbox" name="days[]" id="recurringMonday"
                                        value="Monday">
                                    Lundi</label>
                                <label class="row"><input type="checkbox" name="days[]" id="recurringTuesday"
                                        value="Tuesday">
                                    Mardi</label>
                                <label class="row"><input type="checkbox" name="days[]" id="recurringWednesday"
                                        value="Wednesday">
                                    Mercredi</label>
                                <label class="row"><input type="checkbox" name="days[]" id="recurringThursday"
                                        value="Thursday">
                                    Jeudi</label>
                                <label class="row"><input type="checkbox" name="days[]" id="recurringFriday"
                                        value="Friday">
                                    Vendredi</label>
                                <label class="row"><input type="checkbox" name="days[]" id="recurringSaturday"
                                        value="Saturday">
                                    Samedi</label>
                                <label class="row"><input type="checkbox" name="days[]" id="recurringSunday"
                                        value="Sunday">
                                    Dimanche</label>
                            </div>
                        </div>
                    </div>

                    <!-- Monday -->
                    <div class="day-time-slot">
                        <div class="col recurring-time-slots monday slot-1">
                            <h3>Lundi</h3>
                            <div class="row">
                                <div class="col">
                                    <label for="monday_slot1_start_time">Heure de début (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="monday_slot1_start_time" id="monday_slot1_start_time">
                                </div>
                                <div class="col">
                                    <label for="monday_slot1_end_time">Fin des temps (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="monday_slot1_end_time" id="monday_slot1_end_time">
                                </div>
                            </div>
                        </div>
                        <div class="col recurring-time-slots monday slot-2">
                            <div class="row">
                                <div class="col">
                                    <label for="monday_slot2_start_time">Heure de début (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="monday_slot2_start_time" id="monday_slot2_start_time">
                                </div>
                                <div class="col">
                                    <label for="monday_slot2_end_time">Fin des temps (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="monday_slot2_end_time" id="monday_slot2_end_time">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tuesday -->
                    <div class="day-time-slot">
                        <div class="col recurring-time-slots tuesday slot-1">
                            <h3>Mardi</h3>
                            <div class="row">
                                <div class="col">
                                    <label for="tuesday_slot1_start_time">Heure de début (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="tuesday_slot1_start_time" id="tuesday_slot1_start_time">
                                </div>
                                <div class="col">
                                    <label for="tuesday_slot1_end_time">Fin des temps (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="tuesday_slot1_end_time" id="tuesday_slot1_end_time">
                                </div>
                            </div>
                        </div>
                        <div class="col recurring-time-slots tuesday slot-2">
                            <div class="row">
                                <div class="col">
                                    <label for="tuesday_slot2_start_time">Heure de début (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="tuesday_slot2_start_time" id="tuesday_slot2_start_time">
                                </div>
                                <div class="col">
                                    <label for="tuesday_slot2_end_time">Fin des temps (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="tuesday_slot2_end_time" id="tuesday_slot2_end_time">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Wednesday -->
                    <div class="day-time-slot">
                        <div class="col recurring-time-slots wednesday slot-1">
                            <h3>Mercredi</h3>
                            <div class="row">
                                <div class="col">
                                    <label for="wednesday_slot1_start_time">Heure de début (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="wednesday_slot1_start_time"
                                        id="wednesday_slot1_start_time">
                                </div>
                                <div class="col">
                                    <label for="wednesday_slot1_end_time">Fin des temps (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="wednesday_slot1_end_time" id="wednesday_slot1_end_time">
                                </div>
                            </div>
                        </div>
                        <div class="col recurring-time-slots wednesday slot-2">
                            <div class="row">
                                <div class="col">
                                    <label for="wednesday_slot2_start_time">Heure de début (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="wednesday_slot2_start_time"
                                        id="wednesday_slot2_start_time">
                                </div>
                                <div class="col">
                                    <label for="wednesday_slot2_end_time">Fin des temps (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="wednesday_slot2_end_time" id="wednesday_slot2_end_time">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thursday -->
                    <div class="day-time-slot">
                        <div class="col recurring-time-slots thursday slot-1">
                            <h3>Jeudi</h3>
                            <div class="row">
                                <div class="col">
                                    <label for="thursday_slot1_start_time">Heure de début (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="thursday_slot1_start_time" id="thursday_slot1_start_time">
                                </div>
                                <div class="col">
                                    <label for="thursday_slot1_end_time">Fin des temps (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="thursday_slot1_end_time" id="thursday_slot1_end_time">
                                </div>
                            </div>
                        </div>
                        <div class="col recurring-time-slots thursday slot-2">
                            <div class="row">
                                <div class="col">
                                    <label for="thursday_slot2_start_time">Heure de début (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="thursday_slot2_start_time" id="thursday_slot2_start_time">
                                </div>
                                <div class="col">
                                    <label for="thursday_slot2_end_time">Fin des temps (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="thursday_slot2_end_time" id="thursday_slot2_end_time">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Friday -->
                    <div class="day-time-slot">
                        <div class="col recurring-time-slots friday slot-1">
                            <h3>Vendredi</h3>
                            <div class="row">
                                <div class="col">
                                    <label for="friday_slot1_start_time">Heure de début (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="friday_slot1_start_time" id="friday_slot1_start_time">
                                </div>
                                <div class="col">
                                    <label for="friday_slot1_end_time">Fin des temps (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="friday_slot1_end_time" id="friday_slot1_end_time">
                                </div>
                            </div>
                        </div>
                        <div class="col recurring-time-slots friday slot-2">
                            <div class="row">
                                <div class="col">
                                    <label for="friday_slot2_start_time">Heure de début (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="friday_slot2_start_time" id="friday_slot2_start_time">
                                </div>
                                <div class="col">
                                    <label for="friday_slot2_end_time">Fin des temps (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="friday_slot2_end_time" id="friday_slot2_end_time">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Saturday -->
                    <div class="day-time-slot">
                        <div class="col recurring-time-slots saturday slot-1">
                            <h3>Samedi</h3>
                            <div class="row">
                                <div class="col">
                                    <label for="saturday_slot1_start_time">Heure de début (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="saturday_slot1_start_time" id="saturday_slot1_start_time">
                                </div>
                                <div class="col">
                                    <label for="saturday_slot1_end_time">Fin des temps (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="saturday_slot1_end_time" id="saturday_slot1_end_time">
                                </div>
                            </div>
                        </div>
                        <div class="col recurring-time-slots saturday slot-2">
                            <div class="row">
                                <div class="col">
                                    <label for="saturday_slot2_start_time">Heure de début (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="saturday_slot2_start_time" id="saturday_slot2_start_time">
                                </div>
                                <div class="col">
                                    <label for="saturday_slot2_end_time">Fin des temps (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="saturday_slot2_end_time" id="saturday_slot2_end_time">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sunday -->
                    <div class="day-time-slot">
                        <div class="col recurring-time-slots sunday slot-1">
                            <h3>Dimanche</h3>
                            <div class="row">
                                <div class="col">
                                    <label for="sunday_slot1_start_time">Heure de début (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="sunday_slot1_start_time" id="sunday_slot1_start_time">
                                </div>
                                <div class="col">
                                    <label for="sunday_slot1_end_time">Fin des temps (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="sunday_slot1_end_time" id="sunday_slot1_end_time">
                                </div>
                            </div>
                        </div>
                        <div class="col recurring-time-slots sunday slot-2">
                            <div class="row">
                                <div class="col">
                                    <label for="sunday_slot2_start_time">Heure de début (Emplacement 2) <span
                                            class="required">*</span></label>
                                    <input type="time" name="sunday_slot2_start_time" id="sunday_slot2_start_time">
                                </div>
                                <div class="col">
                                    <label for="sunday_slot2_end_time">Fin des temps (Emplacement 1) <span
                                            class="required">*</span></label>
                                    <input type="time" name="sunday_slot2_end_time" id="sunday_slot2_end_time">
                                </div>
                            </div>
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
                                    
                                    // Query the custom 'teachers' table where status is 'Approuvé'
                                    $teachers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}teachers WHERE status = 'Approuvé'");
                                    
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