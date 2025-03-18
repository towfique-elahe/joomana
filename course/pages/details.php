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

global $wpdb;
$courses_table = $wpdb->prefix . 'courses';
$sessions_table = $wpdb->prefix . 'course_sessions';

$session = $wpdb->get_row($wpdb->prepare("SELECT * FROM $sessions_table WHERE id = %d", $session_id));

$course_id = $session->course_id;

$course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $courses_table WHERE id = %d", $course_id));

$course_id = $session->course_id;
$group_number = $session->group_number;
$teacher_id = $session->teacher_id;
$session_date = $session->session_date;
$status = $session->status;
$class_link = $session->class_link;
$slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
$slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

// Function to get enrolled students details
function get_enrolled_students($session_id) {
    global $wpdb;
    $sessions_table = $wpdb->prefix . 'course_sessions';
    $students_table = $wpdb->prefix . 'students';

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
    
    return $wpdb->get_results($prepared_query);
}

// Query to get teacher details
$teacher_table = $wpdb->prefix . 'teachers';
$teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $teacher_id));

if (!$session) {
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

if (in_array('teacher', (array) $user->roles)) {

    // Check if the teacher is already marked as present
    $teacher_attendance_table = $wpdb->prefix . "teacher_attendance";
    $teacher_attendance_status = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT attendance FROM $teacher_attendance_table 
            WHERE teacher_id = %d AND session_id = %d",
            $teacher_id, $session_id
        )
    );

    // Handle add/edit class links
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save_class_link') {

        $class_link = esc_url($_POST['class_link']);

            // Update existing record
            $wpdb->update(
                $sessions_table,
                ['class_link' => $class_link, 'updated_at' => current_time('mysql')],
                ['id' => $session_id]
            );

        wp_safe_redirect($_SERVER['REQUEST_URI']);
        exit;
    }

    // Handle session reschedule
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reschedule_session') {

        // Sanitize user inputs
        $session_date = sanitize_text_field($_POST['session_date']);
        $slot1_start_time = sanitize_text_field($_POST['slot1_start_time']);
        $slot1_end_time = sanitize_text_field($_POST['slot1_end_time']);
        $slot2_start_time = sanitize_text_field($_POST['slot2_start_time']);
        $slot2_end_time = sanitize_text_field($_POST['slot2_end_time']);

        // Update session date and time
        $update_data = [
            'session_date'      => $session_date,
            'slot1_start_time'  => $slot1_start_time,
            'slot1_end_time'    => $slot1_end_time,
            'slot2_start_time'  => $slot2_start_time,
            'slot2_end_time'    => $slot2_end_time,
            'updated_at'        => current_time('mysql'),
        ];

        $where_condition = [ 'id' => $session_id ];

        $wpdb->update(
            $sessions_table,
            $update_data,
            $where_condition,
            ['%s', '%s', '%s', '%s', '%s', '%s'],
            ['%d']
        );

        // Redirect to prevent resubmission
        wp_safe_redirect($_SERVER['REQUEST_URI']);
        exit;
    }

    // Handle student attendance
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_attendance'])) {
        global $wpdb;

        // Get form data
        $student_id = intval($_POST['student_id']);
        $session_id = intval($_POST['session_id']);
        $attendance = sanitize_text_field($_POST['student_attendance']);

        // Check if a record already exists
        $existing_record = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}student_attendance 
                WHERE student_id = %d AND session_id = %d",
                $student_id, $session_id
            )
        );

        if ($existing_record) {
            // Update existing record
            $wpdb->update(
                "{$wpdb->prefix}student_attendance",
                array('attendance' => $attendance),
                array('id' => $existing_record)
            );
        } else {
            // Insert new record
            $wpdb->insert(
                "{$wpdb->prefix}student_attendance",
                array(
                    'student_id' => $student_id,
                    'session_id' => $session_id,
                    'attendance' => $attendance
                )
            );
        }

        // Redirect to the same URL to prevent form resubmission
        wp_safe_redirect($_SERVER['REQUEST_URI']);
        exit;
    }

    // Handle teacher attendance
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'teacher_attendance') {
        global $wpdb;

        // Update teacher paymment
        $payments_table = $wpdb->prefix . 'teacher_payments';

        // Update the teacher's payment status to 'due'
        $wpdb->update(
            $payments_table, 
            array(
                'status' => 'due'  // Update status column
            ), 
            array('id' => intval($session_id)),
            array('%s'),
            array('%d') // ID format: integer
        );
        
        // Get form data and sanitize
        $teacher_id = intval($_POST['teacher_id']);
        $session_id = intval($_POST['session_id']);
        $attendance = sanitize_text_field($_POST['teacher_attendance']);

        $table_name = $wpdb->prefix . "teacher_attendance";

        // Check if a record already exists
        $existing_record = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_name 
                WHERE teacher_id = %d AND course_id = %d AND group_number = %d",
                $teacher_id, $course_id, $group_number
            )
        );

        if ($existing_record) {
            // Update existing record
            $wpdb->update(
                $table_name,
                array('attendance' => $attendance),
                array('id' => $existing_record)
            );
        } else {
            // Insert new record
            $wpdb->insert(
                $table_name,
                array(
                    'teacher_id' => $teacher_id,
                    'session_id' => $session_id,
                    'attendance' => $attendance
                )
            );
        }

        // Redirect to the same URL to prevent form resubmission
        wp_safe_redirect($_SERVER['REQUEST_URI']);
        exit;
    }

    // Handle class cancelling
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_class') {
        // Retrieve form data
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $group_number = isset($_POST['group_number']) ? intval($_POST['group_number']) : 0;
        $cancelled_reason = isset($_POST['cancelled_reason']) ? sanitize_text_field($_POST['cancelled_reason']) : '';

        // Validate input
        if ($course_id <= 0 || $group_number <= 0 || empty($cancelled_reason)) {
            wp_die('Invalid input.');
        }

        // Check if the course is recurring
        global $wpdb;
        $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_courses WHERE id = %d", $course_id));
        if (!$course) {
            wp_die('Course not found.');
        }

        // Determine which table to update based on whether the course is recurring
        $table_name = $course->is_recurring ? 'wp_recurring_class_sessions' : 'wp_class_sessions';

        // Prepare the data to be updated
        $data = array(
            'status' => 'cancelled',
            'cancelled_reason' => $cancelled_reason,
        );

        // If the course is recurring, set recurring columns to NULL
        if ($course->is_recurring) {
            $data['recurring_start_date'] = null;
            $data['recurring_end_date'] = null;
            $data['recurring_days'] = null;
            $data['recurring_start_time_1'] = null;
            $data['recurring_end_time_1'] = null;
            $data['recurring_start_time_2'] = null;
            $data['recurring_end_time_2'] = null;
        } else {
            // If the course is not recurring, set non-recurring columns to NULL
            $data['start_date'] = null;
            $data['time_slot'] = null;
        }

        // Prepare the where clause
        $where = array(
            'course_id' => $course_id,
            'group_number' => $group_number,
        );

        // Update the database
        $updated = $wpdb->update($table_name, $data, $where);

        if ($updated === false) {
            wp_die('Error updating the database.');
        } else {
            // Redirect to the same URL to prevent form resubmission
            wp_safe_redirect($_SERVER['REQUEST_URI']);
            exit;
        }
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

                            <!-- session-details -->
                            <div class="col session-details">
                                <ul>
                                    <li>
                                        Date:
                                        <span class="value">
                                            <?php echo esc_html(date('M d, Y', strtotime($session_date))); ?>
                                        </span>
                                    </li>
                                    <li>
                                        Groupe:
                                        <span class="value">
                                            <?php echo esc_html($group_number);?>
                                        </span>
                                    </li>
                                    <li>
                                        Temps 1:
                                        <span class="value">
                                            <?php echo esc_html($slot_1);?>
                                        </span>
                                    </li>
                                    <li>
                                        Temps 2:
                                        <span class="value">
                                            <?php echo esc_html($slot_2);?>
                                        </span>
                                    </li>
                                    <li>
                                        Statut:
                                        <span class="value">
                                            <?php echo esc_html($status);?>
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <?php
                                if (current_user_can('student') || current_user_can('parent')) {
                            ?>
                            <!-- teacher details -->
                            <div class="col teacher-details">
                                <h4 class="teacher-title">Détails de Prof</h4>

                                <a href="<?php echo esc_url(home_url('/admin/teacher-management/teacher-details/?id=' . $teacher->id)); ?>"
                                    class="teacher-name">
                                    <?php echo esc_html($teacher->first_name) . ' ' . esc_html($teacher->last_name); ?>
                                    <a>

                                        <div class="row">
                                            <img src="<?php echo !empty($teacher->image) ? esc_url($teacher->image) : $default_user_image; ?>"
                                                alt="" class="teacher-image">

                                            <div class="col teacher-info">
                                                <?php
                                            $countries = [
                                                'Afghanistan' => 'af',
                                                'Afrique du Sud' => 'za',
                                                'Albanie' => 'al',
                                                'Algérie' => 'dz',
                                                'Allemagne' => 'de',
                                                'Andorre' => 'ad',
                                                'Angola' => 'ao',
                                                'Antigua-et-Barbuda' => 'ag',
                                                'Arabie Saoudite' => 'sa',
                                                'Argentine' => 'ar',
                                                'Arménie' => 'am',
                                                'Australie' => 'au',
                                                'Autriche' => 'at',
                                                'Azerbaïdjan' => 'az',
                                                'Bahamas' => 'bs',
                                                'Bahreïn' => 'bh',
                                                'Bangladesh' => 'bd',
                                                'Barbade' => 'bb',
                                                'Belgique' => 'be',
                                                'Belize' => 'bz',
                                                'Bénin' => 'bj',
                                                'Bhoutan' => 'bt',
                                                'Biélorussie' => 'by',
                                                'Bolivie' => 'bo',
                                                'Bosnie-Herzégovine' => 'ba',
                                                'Botswana' => 'bw',
                                                'Brésil' => 'br',
                                                'Brunei' => 'bn',
                                                'Bulgarie' => 'bg',
                                                'Burkina Faso' => 'bf',
                                                'Burundi' => 'bi',
                                                'Cambodge' => 'kh',
                                                'Cameroun' => 'cm',
                                                'Canada' => 'ca',
                                                'Cap-Vert' => 'cv',
                                                'Chili' => 'cl',
                                                'Chine' => 'cn',
                                                'Chypre' => 'cy',
                                                'Colombie' => 'co',
                                                'Comores' => 'km',
                                                'Corée du Nord' => 'kp',
                                                'Corée du Sud' => 'kr',
                                                'Costa Rica' => 'cr',
                                                'Croatie' => 'hr',
                                                'Cuba' => 'cu',
                                                'Danemark' => 'dk',
                                                'Djibouti' => 'dj',
                                                'Dominique' => 'dm',
                                                'Égypte' => 'eg',
                                                'Émirats arabes unis' => 'ae',
                                                'Équateur' => 'ec',
                                                'Érythrée' => 'er',
                                                'Espagne' => 'es',
                                                'Estonie' => 'ee',
                                                'États-Unis' => 'us',
                                                'Éthiopie' => 'et',
                                                'Fidji' => 'fj',
                                                'Finlande' => 'fi',
                                                'France' => 'fr',
                                                'Gabon' => 'ga',
                                                'Gambie' => 'gm',
                                                'Géorgie' => 'ge',
                                                'Ghana' => 'gh',
                                                'Grèce' => 'gr',
                                                'Grenade' => 'gd',
                                                'Guatemala' => 'gt',
                                                'Guinée' => 'gn',
                                                'Guinée-Bissau' => 'gw',
                                                'Guinée équatoriale' => 'gq',
                                                'Guyana' => 'gy',
                                                'Haïti' => 'ht',
                                                'Honduras' => 'hn',
                                                'Hongrie' => 'hu',
                                                'Inde' => 'in',
                                                'Indonésie' => 'id',
                                                'Irak' => 'iq',
                                                'Iran' => 'ir',
                                                'Irlande' => 'ie',
                                                'Islande' => 'is',
                                                'Israël' => 'il',
                                                'Italie' => 'it',
                                                'Jamaïque' => 'jm',
                                                'Japon' => 'jp',
                                                'Jordanie' => 'jo',
                                                'Kazakhstan' => 'kz',
                                                'Kenya' => 'ke',
                                                'Kirghizistan' => 'kg',
                                                'Kiribati' => 'ki',
                                                'Koweït' => 'kw',
                                                'Laos' => 'la',
                                                'Lesotho' => 'ls',
                                                'Lettonie' => 'lv',
                                                'Liban' => 'lb',
                                                'Liberia' => 'lr',
                                                'Libye' => 'ly',
                                                'Liechtenstein' => 'li',
                                                'Lituanie' => 'lt',
                                                'Luxembourg' => 'lu',
                                                'Macédoine du Nord' => 'mk',
                                                'Madagascar' => 'mg',
                                                'Malaisie' => 'my',
                                                'Malawi' => 'mw',
                                                'Maldives' => 'mv',
                                                'Mali' => 'ml',
                                                'Malte' => 'mt',
                                                'Maroc' => 'ma',
                                                'Maurice' => 'mu',
                                                'Mauritanie' => 'mr',
                                                'Mexique' => 'mx',
                                                'Micronésie' => 'fm',
                                                'Moldavie' => 'md',
                                                'Monaco' => 'mc',
                                                'Mongolie' => 'mn',
                                                'Monténégro' => 'me',
                                                'Mozambique' => 'mz',
                                                'Myanmar' => 'mm',
                                                'Namibie' => 'na',
                                                'Népal' => 'np',
                                                'Nicaragua' => 'ni',
                                                'Niger' => 'ne',
                                                'Nigeria' => 'ng',
                                                'Norvège' => 'no',
                                                'Nouvelle-Zélande' => 'nz',
                                                'Oman' => 'om',
                                                'Ouganda' => 'ug',
                                                'Ouzbékistan' => 'uz',
                                                'Pakistan' => 'pk',
                                                'Panama' => 'pa',
                                                'Papouasie-Nouvelle-Guinée' => 'pg',
                                                'Paraguay' => 'py',
                                                'Pays-Bas' => 'nl',
                                                'Pérou' => 'pe',
                                                'Philippines' => 'ph',
                                                'Pologne' => 'pl',
                                                'Portugal' => 'pt',
                                                'Qatar' => 'qa',
                                                'République centrafricaine' => 'cf',
                                                'République démocratique du Congo' => 'cd',
                                                'République dominicaine' => 'do',
                                                'République du Congo' => 'cg',
                                                'Roumanie' => 'ro',
                                                'Royaume-Uni' => 'gb',
                                                'Russie' => 'ru',
                                                'Rwanda' => 'rw',
                                                'Saint-Marin' => 'sm',
                                                'Sénégal' => 'sn',
                                                'Serbie' => 'rs',
                                                'Singapour' => 'sg',
                                                'Somalie' => 'so',
                                                'Soudan' => 'sd',
                                                'Sri Lanka' => 'lk',
                                                'Suède' => 'se',
                                                'Suisse' => 'ch',
                                                'Syrie' => 'sy',
                                                'Tadjikistan' => 'tj',
                                                'Tanzanie' => 'tz',
                                                'Tchad' => 'td',
                                                'Thaïlande' => 'th',
                                                'Togo' => 'tg',
                                                'Tunisie' => 'tn',
                                                'Turquie' => 'tr',
                                                'Ukraine' => 'ua',
                                                'Uruguay' => 'uy',
                                                'Venezuela' => 've',
                                                'Vietnam' => 'vn',
                                                'Yémen' => 'ye',
                                                'Zambie' => 'zm',
                                                'Zimbabwe' => 'zw',
                                            ];

                                            $country_name = $teacher->country;
                                            $country_code = isset($countries[$country_name]) ? $countries[$country_name] : 'un';
                                        ?>
                                                <p class="teacher-data">
                                                    <span class="label">
                                                        <i class="fas fa-globe-europe"></i> Pays :
                                                    </span>
                                                    <img src="https://flagcdn.com/24x18/<?php echo $country_code; ?>.png"
                                                        alt="<?php echo esc_html($country_name); ?>"
                                                        style="vertical-align:middle; margin-right:5px;">
                                                    <?php echo esc_html($country_name); ?>
                                                </p>

                                                <p class="teacher-data">
                                                    <span class="label">
                                                        <i class="fas fa-envelope"></i> E-mail:
                                                    </span>
                                                    <a href="mailto:<?php echo esc_html($teacher->email);?>">
                                                        <?php echo esc_html($teacher->email);?>
                                                    </a>
                                                </p>
                                                <p class="teacher-data">
                                                    <span class="label">
                                                        <i class="fas fa-phone"></i> Téléphone:
                                                    </span>
                                                    <a href="tel:<?php echo esc_html($teacher->phone);?>">
                                                        <?php echo esc_html($teacher->phone);?>
                                                    </a>
                                                </p>
                                                <p class="teacher-data">
                                                    <span class="label">
                                                        <i class="fas fa-comment"></i> Motivation:
                                                    </span>
                                                    <?php echo esc_html($teacher->motivation_of_joining);?>
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
                                        if($class_link) {
                                            echo '<p class="meeting-link" id="classLink">[ '. esc_html($class_link) . ' ]</p>';
                                        } else {
                                            echo '<p class="meeting-link">[ Pas encore attribué ]</p>';
                                        }
                                    ?>

                                    <?php if($class_link) { ?>
                                    <button class="meeting-link-copy" onclick="copyToClipboard()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <?php } ?>
                                </div>

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
                                        if($class_link) {
                                            echo '<p class="meeting-link" id="classLink">[ '. esc_html($class_link) . ' ]</p>';
                                        } else {
                                            echo '<p class="meeting-link">[ Pas encore attribué ]</p>';
                                        }
                                    ?>

                                    <?php if($class_link) { ?>
                                    <button class="meeting-link-copy" onclick="copyToClipboard()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <?php } ?>
                                </div>

                            </div>

                            <!-- settings -->
                            <div class="col settings">
                                <h4 class="settings-title">Paramètres</h4>
                                <div class="buttons">

                                    <!-- update class link -->
                                    <button type="button" class="button add-link open-modal" data-modal="addLinkModal">
                                        <i class="fas fa-link"></i> ajouter/mettre à jour le lien de classe
                                    </button>

                                    <!-- reprogram session -->
                                    <button type="button" class="button reprogram open-modal"
                                        data-modal="sessionReprogramModal">
                                        <i class="fas fa-sync-alt"></i> Reprogrammer
                                    </button>

                                    <!-- teacher attendance -->
                                    <?php if ($teacher_attendance_status !== 'present') : ?>
                                    <button type="button" class="button teacher-attendance open-modal"
                                        data-modal="teacherAttendanceModal">
                                        <i class="fas fa-user-check"></i> Marquer ma présence
                                    </button>
                                    <?php endif; ?>

                                    <!-- cancel class -->
                                    <!-- <form method="post" class="delete-form">
                                        <input type="hidden" name="cancel_course_id" value="<?php echo $course_id; ?>">
                                        <button type="button" class="button cancel open-modal" data-modal="cancelModal">
                                            <i class="fas fa-times"></i> Annuler
                                        </button>
                                    </form> -->

                                </div>
                            </div>
                            <?php
                                }
                            ?>

                        </div>

                        <div class="col">
                            <!-- student list -->
                            <div class="col course-sidebar">
                                <h4 class="sidebar-title">Liste des étudiants</h4>

                                <ul class="list">

                                    <?php
                                        $students = get_enrolled_students($session_id);
                                        if ($students) {
                                            foreach ($students as $student) {
                                    ?>
                                    <li class="row list-item">
                                        <img src="<?php echo !empty($student->image) ? esc_url($student->image) : $default_user_image; ?>"
                                            alt="" class="student-image">

                                        <div class="col student-info">
                                            <a href="<?php echo esc_url(home_url('/course/student-management/student-details/?student_id=' . $student->id . '&session_id=' . $session_id)); ?>"
                                                class="student-name">
                                                <?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?>
                                            </a>
                                            <p class="student-data">
                                                <?php echo esc_html($student->grade);?>
                                            </p>
                                        </div>

                                        <?php
                                            if (current_user_can('teacher')) {
                                                // Check student attendance
                                                $student_attendance_table = $wpdb->prefix . "student_attendance";
                                                $student_attendance_status = $wpdb->get_var(
                                                    $wpdb->prepare(
                                                        "SELECT attendance FROM $student_attendance_table 
                                                        WHERE student_id = %d AND session_id = %d",
                                                        $student->id, $session_id
                                                    )
                                                );
                                        ?>
                                        <div class="student-ttendance">
                                            <form method="post" class="attendance-form"
                                                data-student-id="<?php echo $session_id; ?>">

                                                <input type="hidden" name="student_id"
                                                    value="<?php echo $student->id; ?>">
                                                <input type="hidden" name="session_id"
                                                    value="<?php echo $session_id; ?>">

                                                <select name="student_attendance"
                                                    class="form-select student-ttendance-select">
                                                    <option value="" disabled
                                                        <?= !$student_attendance_status ? 'selected' : ''; ?>>
                                                        ATN</option>
                                                    <option value="absent"
                                                        <?= ($student_attendance_status == 'absent') ? 'selected' : ''; ?>>
                                                        A
                                                    </option>
                                                    <option value="present"
                                                        <?= ($student_attendance_status == 'present') ? 'selected' : ''; ?>>
                                                        P
                                                    </option>
                                                </select>
                                            </form>
                                        </div>
                                        <?php
                                            }
                                        ?>
                                    </li>
                                    <?php
                                            }
                                        } else {
                                            echo '<p class="no-date">Aucun étudiant trouvé.</p>';
                                        }
                                    ?>

                                </ul>
                            </div>

                            <?php
                                if (current_user_can('teacher')) {
                            ?>
                            <!-- course materials -->
                            <div class="col course-sidebar">
                                <h4 class="sidebar-title">Matériel de cours</h4>
                                <div class="material-link-container row">
                                    <?php
                                        if($course->course_material) {
                                            echo '<p class="material-link">'. esc_html($course->course_material) . '</p>';
                                        } else {
                                            echo '<p class="material-link">[ Pas encore attribué ]</p>';
                                        }
                                        
                                        if($course->course_material) { ?>
                                    <a href="<?php echo esc_url($course->course_material) ?>" class="material-link-icon"
                                        target="_blank">
                                        <i class="fas fa-external-link-square-alt"></i>
                                    </a>
                                    <?php } ?>
                                </div>

                            </div>
                            <?php
                                }
                            ?>

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

            <div class="row">
                <div class="col">
                    <label for="classLink">Lien de classe</label>
                    <input type="url" name="class_link" id="classLink" value="<?= $class_link ?>" required>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="modal-button confirm">Enregistrer</button>
                <button type="button" class="modal-button cancel close-modal">Annuler</button>
            </div>
        </form>
    </div>
</div>

<!-- Session Reprogrammer Modal -->
<div id="sessionReprogramModal" class="modal">
    <div class="modal-content">
        <span class="modal-close">
            <i class="fas fa-times"></i>
        </span>
        <h4 class="modal-heading">Reprogrammer le cours</h4>
        <form method="post" action="">
            <input type="hidden" name="action" value="reschedule_session">

            <!-- session date -->
            <div class="row session-date">
                <div class="col">
                    <label for="session_date">Date de début <span class="required">*</span></label>
                    <input type="date" id="session_date" name="session_date"
                        value="<?php echo !empty($session_date) ? esc_attr($session_date) : ''; ?>">
                </div>
            </div>

            <!-- time slot 1 -->
            <div class="row">
                <div class="col">
                    <label for="slot1_start_time">Heure de début (Emplacement 1) <span class="required">*</span></label>
                    <input type="time" name="slot1_start_time" id="slot1_start_time"
                        value="<?php echo !empty($session->slot1_start_time) ? esc_attr($session->slot1_start_time) : ''; ?>">
                </div>
                <div class="col">
                    <label for="slot1_end_time">Fin des temps (Emplacement 1) <span class="required">*</span></label>
                    <input type="time" name="slot1_end_time" id="slot1_end_time"
                        value="<?php echo !empty($session->slot1_end_time) ? esc_attr($session->slot1_end_time) : ''; ?>">
                </div>
            </div>

            <!-- time slot 2 -->
            <div class="row">
                <div class="col">
                    <label for="slot2_start_time">Heure de début (Emplacement 2) <span class="required">*</span></label>
                    <input type="time" name="slot2_start_time" id="slot2_start_time"
                        value="<?php echo !empty($session->slot2_start_time) ? esc_attr($session->slot2_start_time) : ''; ?>">
                </div>
                <div class="col">
                    <label for="slot2_end_time">Fin des temps (Emplacement 2) <span class="required">*</span></label>
                    <input type="time" name="slot2_end_time" id="slot2_end_time"
                        value="<?php echo !empty($session->slot2_end_time) ? esc_attr($session->slot2_end_time) : ''; ?>">
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

<!-- Teacher Attendance Modal -->
<div id="teacherAttendanceModal" class="modal">
    <div class="modal-content">
        <span class="modal-close">
            <i class="fas fa-times"></i>
        </span>
        <h4 class="modal-heading">
            <i class="fas fa-exclamation-triangle" style="color: crimson"></i> Avertissement
        </h4>
        <p class="modal-info">« Vous prétendez avoir assisté au cours, notez-le ? »</p>
        <form method="post" action="">
            <input type="hidden" name="action" value="teacher_attendance">
            <input type="hidden" name="session_id" value="<?php echo esc_attr($session_id); ?>">
            <input type="hidden" name="teacher_id" value="<?php echo esc_attr($teacher_id); ?>">
            <input type="hidden" name="teacher_attendance" value="present">

            <div class="modal-actions">
                <button type="submit" id="confirmTeacherAttendance" class="modal-button confirm">Confirmer</button>
                <button type="button" class="modal-button cancel close-modal">Annuler</button>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
jQuery(document).ready(function($) {
    // Handle form submission
    $('#teacherAttendanceModal form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Submit the form via AJAX or standard POST
        $.ajax({
            url: '', // Submit to the same page
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Redirect or show a success message
                window.location.reload(); // Reload the page to reflect changes
            },
            error: function(xhr, status, error) {
                console.error('Error submitting form:', error);
            }
        });

        // Close the modal after submission
        $('#teacherAttendanceModal').hide();
    });
});
</script>
<script>
jQuery(document).ready(function($) {
    // Handle change event for attendance select
    $('.attendance-select').on('change', function() {
        var form = $(this).closest('.attendance-form');
        form.submit(); // Submit the form
    });
});
</script>

<?php require_once(get_template_directory() . '/course/templates/footer.php'); ?>