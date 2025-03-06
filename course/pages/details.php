<?php

/* Template Name: Course | Details */

// Page title
global $pageTitle;
$pageTitle = 'Détails du cours';

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
$course_id = intval($_GET['course_id']);

global $wpdb;
$courses_table = $wpdb->prefix . 'courses';
$course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $courses_table WHERE id = %d", $course_id));
$group_number = 0;

if (!$course) {
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

if (in_array('parent', (array) $user->roles)) {
    $student_id = intval($_GET['student_id']);
    // Query the custom table to get the student's teacher_id
    $student_courses_table  = $wpdb->prefix . 'student_courses'; // Ensure the table name is correct
    $teacher_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT teacher_id FROM $student_courses_table  WHERE student_id = %d AND course_id = %d",
            $student_id,
            $course_id
        )
    );
    $student_group = $wpdb->get_var($wpdb->prepare(
        "SELECT group_number FROM {$wpdb->prefix}student_courses WHERE student_id = %d AND course_id = %d LIMIT 1",
        $student_id,
        $course_id
    ));
    if ($student_group) {
        $group_number = intval($student_group);
    }

    // Fetch the teacher's details using the teacher_id for the student
    $teacher_table = $wpdb->prefix. 'teachers';
    $teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $teacher_id));

    // Fetch all student IDs enrolled in the course for the given teacher group
    $enrolled_student_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT student_id FROM $student_courses_table WHERE course_id = %d AND teacher_id = %d",
            $course_id,
            $teacher_id
        )
    );

    // Check if any student IDs were found
    if (!empty($enrolled_student_ids)) {
        // Fetch student details from the students table
        $students_table = $wpdb->prefix . 'students';
        $student_ids_placeholder = implode(',', array_map('intval', $enrolled_student_ids)); // Sanitize IDs

        $enrolled_students = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $students_table WHERE id IN ($student_ids_placeholder)"
            )
        );
    }

    // Fetch existing class link for course_id and group_number
    $class_links_table = $wpdb->prefix . "course_class_links";
    // Check if a record already exists for this course and group
    $existing_class_link = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $class_links_table WHERE course_id = %d AND group_number = %d",
        $course_id, $group_number
    ));

} elseif (in_array('student', (array) $user->roles)) {
    $student_id = $user->ID;
    // Query the custom table to get the student's teacher_id
    $student_courses_table  = $wpdb->prefix . 'student_courses'; // Ensure the table name is correct
    $teacher_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT teacher_id FROM $student_courses_table  WHERE student_id = %d AND course_id = %d",
            $student_id,
            $course_id
        )
    );
    $student_group = $wpdb->get_var($wpdb->prepare(
        "SELECT group_number FROM {$wpdb->prefix}student_courses WHERE student_id = %d AND course_id = %d LIMIT 1",
        $student_id,
        $course_id
    ));
    if ($student_group) {
        $group_number = intval($student_group);
    }

    // Fetch the teacher's details using the teacher_id for the student
    $teacher_table = $wpdb->prefix. 'teachers';
    $teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $teacher_id));

    // Fetch all student IDs enrolled in the course for the given teacher group
    $enrolled_student_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT student_id FROM $student_courses_table WHERE course_id = %d AND teacher_id = %d",
            $course_id,
            $teacher_id
        )
    );

    // Check if any student IDs were found
    if (!empty($enrolled_student_ids)) {
        // Fetch student details from the students table
        $students_table = $wpdb->prefix . 'students';
        $student_ids_placeholder = implode(',', array_map('intval', $enrolled_student_ids)); // Sanitize IDs

        $enrolled_students = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $students_table WHERE id IN ($student_ids_placeholder)"
            )
        );
    }

    // Fetch existing class link for course_id and group_number
    $class_links_table = $wpdb->prefix . "course_class_links";
    // Check if a record already exists for this course and group
    $existing_class_link = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $class_links_table WHERE course_id = %d AND group_number = %d",
        $course_id, $group_number
    ));

} elseif (in_array('teacher', (array) $user->roles)) {
    // Query the custom table to get the student's teacher_id
    $student_courses_table  = $wpdb->prefix . 'student_courses'; // Ensure the table name is correct
    $teacher_id = $user->ID;
    $teacher_group = $wpdb->get_var($wpdb->prepare(
        "SELECT group_number FROM {$wpdb->prefix}teacher_courses WHERE teacher_id = %d AND course_id = %d LIMIT 1",
        $teacher_id,
        $course_id
    ));
    if ($teacher_group) {
        $group_number = intval($teacher_group);
    }

    // Fetch the teacher's details using the teacher_id for the student
    $teacher_table = $wpdb->prefix. 'teachers';
    $teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $teacher_id));

    // Fetch all student IDs enrolled in the course for the given teacher group
    $enrolled_student_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT student_id FROM $student_courses_table WHERE course_id = %d AND teacher_id = %d",
            $course_id,
            $teacher_id
        )
    );

    // Check if any student IDs were found
    if (!empty($enrolled_student_ids)) {
        // Fetch student details from the students table
        $students_table = $wpdb->prefix . 'students';
        $student_ids_placeholder = implode(',', array_map('intval', $enrolled_student_ids)); // Sanitize IDs

        $enrolled_students = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $students_table WHERE id IN ($student_ids_placeholder)"
            )
        );
    }

    // Fetch existing class link for course_id and group_number
    $class_links_table = $wpdb->prefix . "course_class_links";
    // Check if a record already exists for this course and group
    $existing_class_link = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $class_links_table WHERE course_id = %d AND group_number = %d",
        $course_id, $group_number
    ));

    // Handle add/edit class links
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save_class_link') {
        global $wpdb;
        $table_name = $wpdb->prefix . "course_class_links";

        $class_link = esc_url($_POST['class_link']);
        $class_link_id = isset($_POST['class_link_id']) ? intval($_POST['class_link_id']) : 0;

        if ($class_link_id > 0) {
            // Update existing record
            $wpdb->update(
                $table_name,
                [
                    'class_link' => $class_link,
                    'updated_at' => current_time('mysql')
                ],
                [ 'id' => $class_link_id ]
            );
        } else {
            // Check if a record already exists for this course and group
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE course_id = %d AND group_number = %d",
                $course_id, $group_number
            ));
            
            if ($existing) {
                // Update the existing record
                $wpdb->update(
                    $table_name,
                    [ 'class_link' => $class_link, 'updated_at' => current_time('mysql') ],
                    [ 'id' => $existing ]
                );
            } else {
                // Insert new record
                $wpdb->insert(
                    $table_name,
                    [
                        'course_id' => $course_id,
                        'group_number' => $group_number,
                        'class_link' => $class_link,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ]
                );
            }
        }

        // Redirect to prevent resubmission
        wp_safe_redirect($_SERVER['REQUEST_URI']);
        exit;
    }

    // Handle reschedule course class
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reschedule_course') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'courses';

        // Sanitize user inputs
        $start_date = sanitize_text_field($_POST['start_date']);
        $time_slot = sanitize_text_field($_POST['time_slot']);
    
        // Update course in the database
        $wpdb->update(
            $table_name,
            [
                'start_date' => $start_date,
                'time_slot'  => $time_slot,
            ],
            ['id' => $course_id],
            [
                '%s', '%s',
            ],
            ['%d']
        );
    
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
    <div id="courseDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails du cours</h2>
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
                    enfants</a>
                <?php 
                    } elseif (current_user_can('parent')) {
                ?>
                <a href="<?php echo home_url('/parent/child-management'); ?>" class="breadcrumb-link">Gestion de
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
                <span class="active">Détails du cours</span>
            </div>
        </div>

        <div class="content-section">
            <div class="row">
                <div class="col section">
                    <div class="row course-details">
                        <div class="col course-info">
                            <h4 class="course-title">
                                <?php echo esc_html($course->title);?>
                            </h4>

                            <!-- class schedule -->
                            <div class="col class-schedule">
                                <h4 class="schedule-title">Horaire des cours</h4>
                                <ul>
                                    <li>
                                        Date:
                                        <span class="value">
                                            <?php echo esc_html(date('M d, Y', strtotime($course->start_date))); ?>
                                        </span>
                                    </li>
                                    <li>
                                        Time:
                                        <span class="value">
                                            <?php echo esc_html($course->time_slot);?>
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <?php
                                if (current_user_can('student') || current_user_can('parent')) {
                            ?>
                            <!-- teacher details -->
                            <div class="col teacher-details">
                                <h4 class="teacher-title">Détails de l'enseignant</h4>

                                <div class="row">
                                    <img src="<?php echo !empty($teacher->image) ? esc_url($teacher->image) : $default_user_image; ?>"
                                        alt="" class="teacher-image">

                                    <div class="col teacher-info">
                                        <h4 class="teacher-name">
                                            <?php echo esc_html($teacher->first_name) . ' ' . esc_html($teacher->last_name); ?>
                                        </h4>
                                        <p class="teacher-data">
                                            <?php echo esc_html($teacher->motivation_of_joining);?>
                                        </p>
                                        <p class="teacher-data">
                                            <?php echo esc_html($teacher->email);?>
                                        </p>
                                        <p class="teacher-data">
                                            <?php echo esc_html($teacher->phone);?>
                                        </p>
                                    </div>
                                </div>

                            </div>
                            <?php
                                }
                            ?>

                            <?php
                                if (current_user_can('student') || current_user_can('parent')) {
                            ?>
                            <!-- meeting details -->
                            <div class="col meeting-details">
                                <h4 class="meeting-title">Réunion de classe</h4>
                                <div class="meeting-link-container">
                                    <?php
                                        if($existing_class_link->class_link) {
                                            echo '<p class="meeting-link" id="classLink">[ '. esc_html($existing_class_link->class_link) . ' ]</p>';
                                        } else {
                                            echo '<p class="meeting-link">[ Pas encore attribué ]</p>';
                                        }
                                    ?>

                                    <?php if($existing_class_link->class_link) { ?>
                                    <button class="meeting-link-copy" onclick="copyToClipboard()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <?php } ?>
                                </div>

                                <?php if($existing_class_link->class_link) { ?>
                                <a href="<?php echo esc_url($existing_class_link->class_link); ?>" target="_blank"
                                    class="button">
                                    <i class="fas fa-external-link-square-alt"></i> Rejoindre
                                </a>
                                <?php } ?>
                            </div>
                            <?php
                                }
                            ?>

                            <?php
                                if (in_array('teacher', (array) $user->roles)) {
                            ?>
                            <!-- meeting details -->
                            <div class="col meeting-details">
                                <h4 class="meeting-title">Réunion de classe</h4>
                                <div class="meeting-link-container">
                                    <?php
                                        if($existing_class_link->class_link) {
                                            echo '<p class="meeting-link" id="classLink">[ '. esc_html($existing_class_link->class_link) . ' ]</p>';
                                        } else {
                                            echo '<p class="meeting-link">[ Pas encore attribué ]</p>';
                                        }
                                    ?>

                                    <?php if($existing_class_link->class_link) { ?>
                                    <button class="meeting-link-copy" onclick="copyToClipboard()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <?php } ?>
                                </div>

                                <?php if($existing_class_link->class_link) { ?>
                                <a href="<?php echo esc_url($existing_class_link->class_link); ?>" target="_blank"
                                    class="button">
                                    <i class="fas fa-external-link-square-alt"></i> Rejoindre
                                </a>
                                <?php } ?>
                            </div>

                            <!-- settings -->
                            <div class="col settings">
                                <h4 class="settings-title">Paramètres</h4>
                                <div class="buttons">
                                    <button type="button" class="button add-link open-modal" data-modal="addLinkModal">
                                        <i class="fas fa-link"></i> ajouter/mettre à jour le lien de classe
                                    </button>
                                    <button type="button" class="button reprogram open-modal"
                                        data-modal="reprogramModal">
                                        <i class="fas fa-sync-alt"></i> Reprogrammer
                                    </button>
                                    <form method="post" class="delete-form">
                                        <input type="hidden" name="cancel_course_id" value="<?php echo $course_id; ?>">
                                        <button type="button" class="button cancel open-modal" data-modal="cancelModal">
                                            <i class="fas fa-times"></i> Annuler
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php
                                }
                            ?>

                        </div>

                        <div class="col student-list">
                            <h4 class="list-title">Liste des étudiants</h4>

                            <ul class="list">

                                <?php
                                        // Check if any student IDs were found
                                        if (!empty($enrolled_student_ids)) {
                                            // Output or process the enrolled students
                                            if (!empty($enrolled_students)) {
                                                foreach ($enrolled_students as $student) {
                                    ?>
                                <li class="row list-item">
                                    <img src="<?php echo !empty($student->image) ? esc_url($student->image) : $default_user_image; ?>"
                                        alt="" class="student-image">

                                    <div class="col student-info">
                                        <h5 class="student-name">
                                            <?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?>
                                        </h5>
                                        <p class="student-data">
                                            <?php echo esc_html($student->grade);?>
                                        </p>
                                    </div>
                                </li>
                                <?php
                                            }
                                        } else {
                                            echo "Aucun détail étudiant n'a été trouvé pour les étudiants inscrits.";
                                        }
                                    } else {
                                        echo "Pourtant, aucun étudiant n'est inscrit à ce cours pour vous.";
                                    }

                                ?>

                            </ul>

                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add/Update Class Link Modal -->
<div id="addLinkModal" class="modal">
    <div class="modal-content">
        <span class="modal-close">
            <i class="fas fa-times"></i>
        </span>
        <h4 class="modal-heading">Ajouter un lien de classe</h4>

        <form method="post" action="" class="form add-class-link">
            <input type="hidden" name="action" value="save_class_link">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            <input type="hidden" name="class_link_id" id="classLinkId" value="<?= $existing_class_link -> id; ?>">

            <div class="row">
                <div class="col">
                    <label for="classLink">Lien Zoom</label>
                    <input type="url" name="class_link" id="classLink" value="<?= $existing_class_link -> class_link ?>"
                        required>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="modal-button confirm">Enregistrer</button>
                <button type="button" class="modal-button cancel close-modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Reprogrammer Modal -->
<div id="reprogramModal" class="modal">
    <div class="modal-content">
        <span class="modal-close">
            <i class="fas fa-times"></i>
        </span>
        <h4 class="modal-heading">Reprogrammer le cours</h4>
        <form method="post" action="">
            <input type="hidden" name="action" value="reschedule_course">

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

            <div class="modal-actions">
                <button type="submit" class="modal-button confirm">Reprogrammer</button>
                <button type="button" class="modal-button cancel close-modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="modal">
    <div class="modal-content">
        <span class="modal-close">
            <i class="fas fa-times"></i>
        </span>
        <h4 class="modal-heading">
            <i class="fas fa-exclamation-triangle" style="color: crimson"></i> Avertissement
        </h4>
        <p class="modal-info">Etes-vous sûr de vouloir annuler le cours ?</p>
        <form action="" method="post">
            <input type="hidden" name="action" value="cancel_class">

            <div class="row">
                <div class="col">
                    <label for="reason">Raison</label>
                    <textarea name="reason" id="reason" required></textarea>
                </div>
            </div>

            <div class="modal-actions">
                <button id="confirmCancel" class="modal-button delete">Confirmer</button>
                <button class="modal-button cancel close-modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<script>
function copyToClipboard() {
    var classLink = document.getElementById("classLink").textContent;
    classLink = classLink.replace(/\[|\]/g, '').trim(); // Remove brackets

    var tempInput = document.createElement("input");
    tempInput.value = classLink;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);

    alert("Lien copié: " + classLink);
}
</script>

<?php require_once(get_template_directory() . '/course/templates/footer.php'); ?>