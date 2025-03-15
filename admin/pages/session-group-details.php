<?php

/* Template Name: Admin | Session Group Details */

// Page title
global $pageTitle;
$pageTitle = 'Détails Du Groupe';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$default_user_image = esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png');

// Get course ID and group number from session
if (!isset($_GET['group_number']) || empty($_GET['group_number']) || !isset($_GET['course_id']) || empty($_GET['course_id'])) {
    wp_redirect(home_url('/admin/session-management/courses/groups/'));
    exit;
}

// Sanitize and retrieve the values
$course_id = intval($_GET['course_id']);
$group_number = intval($_GET['group_number']);

global $wpdb;
$courses_table = $wpdb->prefix . 'courses';
$course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $courses_table WHERE id = %d", $course_id));

// Query to get the teacher_id
$teacher_id = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT teacher_id FROM wp_teacher_courses WHERE group_number = %d AND course_id = %d",
        $group_number,
        $course_id
    )
);

// Query to get teacher details
$teacher_table = $wpdb->prefix . 'teachers';
$teacher = $wpdb->get_row($wpdb->prepare("SELECT * FROM $teacher_table WHERE id = %d", $teacher_id));

// Fetch existing class link for course_id and group_number
$class_links_table = $wpdb->prefix . "course_class_links";
$existing_class_link = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $class_links_table WHERE course_id = %d AND group_number = %d",
        $course_id, $group_number
    )
);

// Fetch enrolled students
$student_courses_table = $wpdb->prefix . 'student_courses';
$enrolled_student_ids = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT student_id FROM $student_courses_table WHERE course_id = %d AND teacher_id = %d",
        $course_id,
        $teacher_id
    )
);

if (!empty($enrolled_student_ids)) {
    $students_table = $wpdb->prefix . 'students';
    $student_ids_placeholder = implode(',', array_map('intval', $enrolled_student_ids));

    $enrolled_students = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $students_table WHERE id IN ($student_ids_placeholder)"
        )
    );
}

// Fetch recurring sessions
$recurring_sessions_table = $wpdb->prefix . 'recurring_class_sessions';

$recurring_session = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM $recurring_sessions_table WHERE course_id = %d AND group_number = %d LIMIT 1",
        $course_id,
        $group_number
    )
);

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
            ['class_link' => $class_link, 'updated_at' => current_time('mysql')],
            ['id' => $class_link_id]
        );
    } else {
        // Check if a record already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE course_id = %d AND group_number = %d",
            $course_id, $group_number
        ));
        
        if ($existing) {
            $wpdb->update(
                $table_name,
                ['class_link' => $class_link, 'updated_at' => current_time('mysql')],
                ['id' => $existing]
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
        ['start_date' => $start_date, 'time_slot' => $time_slot],
        ['id' => $course_id],
        ['%s', '%s'],
        ['%d']
    );

    wp_safe_redirect($_SERVER['REQUEST_URI']);
    exit;
}

// Handle reschedule recurring course class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reschedule_recurring_course') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'recurring_class_sessions';

    // Sanitize user inputs
    $recurring_start_date = sanitize_text_field($_POST['recurring_start_date']);
    $recurring_end_date = sanitize_text_field($_POST['recurring_end_date']);
    $recurring_days = !empty($_POST['recurring_days']) ? json_encode(array_map('sanitize_text_field', $_POST['recurring_days'])) : json_encode([]);
    $recurring_start_time_1 = sanitize_text_field($_POST['recurring_start_time_1']);
    $recurring_end_time_1 = sanitize_text_field($_POST['recurring_end_time_1']);
    $recurring_start_time_2 = sanitize_text_field($_POST['recurring_start_time_2']);
    $recurring_end_time_2 = sanitize_text_field($_POST['recurring_end_time_2']);

    // Check if the recurring session already exists
    $existing_session_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table_name WHERE course_id = %d AND group_number = %d",
            $course_id,
            $group_number
        )
    );

    if ($existing_session_id) {
        // Update existing recurring session
        $wpdb->update(
            $table_name,
            [
                'recurring_start_date' => $recurring_start_date,
                'recurring_end_date' => $recurring_end_date,
                'recurring_days' => $recurring_days,
                'recurring_start_time_1' => $recurring_start_time_1,
                'recurring_end_time_1' => $recurring_end_time_1,
                'recurring_start_time_2' => $recurring_start_time_2,
                'recurring_end_time_2' => $recurring_end_time_2,
                'updated_at' => current_time('mysql')
            ],
            [ 'id' => $existing_session_id ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ],
            [ '%d' ]
        );
    } else {
        // Insert new recurring session if not found
        $wpdb->insert(
            $table_name,
            [
                'course_id' => $course_id,
                'group_number' => $group_number,
                'recurring_start_date' => $recurring_start_date,
                'recurring_end_date' => $recurring_end_date,
                'recurring_days' => $recurring_days,
                'recurring_start_time_1' => $recurring_start_time_1,
                'recurring_end_time_1' => $recurring_end_time_1,
                'recurring_start_time_2' => $recurring_start_time_2,
                'recurring_end_time_2' => $recurring_end_time_2,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            [ '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );
    }

    // Redirect to prevent resubmission
    wp_safe_redirect($_SERVER['REQUEST_URI']);
    exit;
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>

    <div id="adminSessionGroupDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails du groupe</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/session-management/courses/'); ?>" class="breadcrumb-link">Gestion
                    des
                    sessions</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/session-management/courses/groups/'); ?>"
                    class="breadcrumb-link">Groupes De
                    Séances</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Détails du groupe</span>
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
                                        Récurrent:
                                        <span class="value">
                                            <?php echo $course->is_recurring ? 'Oui' : 'Non'; ?>
                                        </span>
                                    </li>
                                    <?php
                                        if(!$course->is_recurring) {
                                            ?>
                                    <li>
                                        Date:
                                        <span class="value">
                                            <?php echo esc_html(date('M d, Y', strtotime($course->start_date))); ?>
                                        </span>
                                    </li>
                                    <li>
                                        Temps:
                                        <span class="value">
                                            <?php echo esc_html($course->time_slot);?>
                                        </span>
                                    </li>
                                    <?php
                                        }
                                    ?>
                                    <?php
                                        if($course->is_recurring) {
                                            ?>
                                    <li>
                                        Date de début:
                                        <span class="value">
                                            <?php echo esc_html(date('M d, Y', strtotime($recurring_session->recurring_start_date))); ?>
                                        </span>
                                    </li>
                                    <li>
                                        Date de fin:
                                        <span class="value">
                                            <?php echo esc_html(date('M d, Y', strtotime($recurring_session->recurring_end_date))); ?>
                                        </span>
                                    </li>
                                    <li>
                                        Jours récurrents :
                                        <span class="value">
                                            <?php 
                                                $recurring_days = json_decode($recurring_session->recurring_days, true);
                                                
                                                // Mapping English days to French
                                                $days_translation = [
                                                    "Monday" => "Lundi",
                                                    "Tuesday" => "Mardi",
                                                    "Wednesday" => "Mercredi",
                                                    "Thursday" => "Jeudi",
                                                    "Friday" => "Vendredi",
                                                    "Saturday" => "Samedi",
                                                    "Sunday" => "Dimanche"
                                                ];

                                                if (!empty($recurring_days) && is_array($recurring_days)) {
                                                    $translated_days = array_map(function($day) use ($days_translation) {
                                                        return $days_translation[$day] ?? $day;
                                                    }, $recurring_days);

                                                    echo esc_html(implode(', ', $translated_days));
                                                } else {
                                                    echo "Aucun jour récurrent";
                                                }
                                            ?>
                                        </span>
                                    </li>
                                    <li>
                                        1er créneau horaire:
                                        <span class="value">
                                            <?php 
                                                echo date('h:i A', strtotime($recurring_session->recurring_start_time_1)) . ' - ' . 
                                                    date('h:i A', strtotime($recurring_session->recurring_end_time_1)); 
                                            ?>
                                        </span>
                                    </li>
                                    <li>
                                        2ème créneau horaire:
                                        <span class="value">
                                            <?php 
                                                echo date('h:i A', strtotime($recurring_session->recurring_start_time_2)) . ' - ' . 
                                                    date('h:i A', strtotime($recurring_session->recurring_end_time_2)); 
                                            ?>
                                        </span>
                                    </li>
                                    <?php
                                        }
                                    ?>
                                </ul>
                            </div>

                            <!-- teacher details -->
                            <div class="col teacher-details">
                                <h4 class="teacher-title">Détails de l'enseignant</h4>

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

                            </div>

                            <!-- resources and submissions -->
                            <div class="col settings">
                                <h4 class="settings-title">Ressources et soumissions</h4>
                                <div class="buttons">
                                    <a href="<?php echo esc_url(home_url('/admin/session-management/courses/groups/group-resources/?group_number=' . $group_number . '&course_id=' . $course_id)); ?>"
                                        class="button">
                                        <i class="fas fa-folder-open"></i> Ressources du cours
                                    </a>
                                    <a href="<?php echo esc_url(home_url('/admin/session-management/courses/groups/group-submissions/?group_number=' . $group_number . '&course_id=' . $course_id)); ?>"
                                        class="button">
                                        <i class="fas fa-tasks"></i> Soumissions des étudiants
                                    </a>
                                </div>
                            </div>

                            <!-- settings -->
                            <div class="col settings">
                                <h4 class="settings-title">Paramètres</h4>
                                <div class="buttons">
                                    <button type="button" class="button add-link open-modal" data-modal="addLinkModal">
                                        <i class="fas fa-link"></i> ajouter/mettre à jour le lien de classe
                                    </button>
                                    <?php
                                        if ($course->is_recurring) {
                                            ?>
                                    <button type="button" class="button reprogram open-modal"
                                        data-modal="recurringReprogramModal">
                                        <i class="fas fa-sync-alt"></i> Reprogrammer
                                    </button>
                                    <?php
                                        } else {
                                            ?>
                                    <button type="button" class="button reprogram open-modal"
                                        data-modal="reprogramModal">
                                        <i class="fas fa-sync-alt"></i> Reprogrammer
                                    </button>
                                    <?php
                                        }
                                    ?>
                                    <form method="post" class="delete-form">
                                        <input type="hidden" name="cancel_course_id" value="<?php echo $course_id; ?>">
                                        <button type="button" class="button cancel open-modal" data-modal="cancelModal">
                                            <i class="fas fa-times"></i> Annuler
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>

                        <div class="col">
                            <!-- student list -->
                            <div class="col course-sidebar">
                                <h4 class="sidebar-title">Liste des étudiants</h4>

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
                                            <a href="<?php echo esc_url(home_url('/admin/student-management/student-details/?id=' . $student->id)); ?>"
                                                class="student-name">
                                                <?php echo esc_html($student->first_name) . ' ' . esc_html($student->last_name); ?>
                                            </a>
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

<!-- Recurring Reprogrammer Modal -->
<div id="recurringReprogramModal" class="modal">
    <div class="modal-content">
        <span class="modal-close">
            <i class="fas fa-times"></i>
        </span>
        <h4 class="modal-heading">Reprogrammer le cours</h4>
        <form method="post" action="">
            <input type="hidden" name="action" value="reschedule_recurring_course">

            <div class="row recurring-dates">
                <div class="col">
                    <label for="recurring_start_date">Date de début <span class="required">*</span></label>
                    <input type="date" id="recurring_start_date" name="recurring_start_date"
                        value="<?php echo !empty($recurring_session->recurring_start_date) ? esc_attr($recurring_session->recurring_start_date) : ''; ?>">
                </div>
                <div class="col">
                    <label for="recurring_end_date">Date de fin <span class="required">*</span></label>
                    <input type="date" name="recurring_end_date" id="recurring_end_date"
                        value="<?php echo !empty($recurring_session->recurring_end_date) ? esc_attr($recurring_session->recurring_end_date) : ''; ?>">
                </div>
            </div>

            <div class="row recurring-days">
                <div class="col">
                    <label for="recurring_days">Jours</label>
                    <div class="row checkbox-group">
                        <?php
                        $recurring_days = !empty($recurring_session->recurring_days) ? json_decode($recurring_session->recurring_days, true) : [];
                        $days_translation = [
                            "Monday" => "Lundi",
                            "Tuesday" => "Mardi",
                            "Wednesday" => "Mercredi",
                            "Thursday" => "Jeudi",
                            "Friday" => "Vendredi",
                            "Saturday" => "Samedi",
                            "Sunday" => "Dimanche"
                        ];
                        foreach ($days_translation as $eng => $fr) {
                            $checked = in_array($eng, $recurring_days) ? 'checked' : '';
                            echo "<label class='row'><input type='checkbox' name='recurring_days[]' value='$eng' $checked> $fr</label>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col recurring-time-slots slot-1">
                <div class="row">
                    <div class="col">
                        <label for="recurring_start_time_1">Heure de début (Emplacement 1) <span
                                class="required">*</span></label>
                        <input type="time" name="recurring_start_time_1" id="recurring_start_time_1"
                            value="<?php echo !empty($recurring_session->recurring_start_time_1) ? esc_attr($recurring_session->recurring_start_time_1) : ''; ?>">
                    </div>
                    <div class="col">
                        <label for="recurring_end_time_1">Fin des temps (Emplacement 1) <span
                                class="required">*</span></label>
                        <input type="time" name="recurring_end_time_1" id="recurring_end_time_1"
                            value="<?php echo !empty($recurring_session->recurring_end_time_1) ? esc_attr($recurring_session->recurring_end_time_1) : ''; ?>">
                    </div>
                </div>
            </div>

            <div class="col recurring-time-slots slot-2">
                <div class="row">
                    <div class="col">
                        <label for="recurring_start_time_2">Heure de début (Emplacement 2) <span
                                class="required">*</span></label>
                        <input type="time" name="recurring_start_time_2" id="recurring_start_time_2"
                            value="<?php echo !empty($recurring_session->recurring_start_time_2) ? esc_attr($recurring_session->recurring_start_time_2) : ''; ?>">
                    </div>
                    <div class="col">
                        <label for="recurring_end_time_2">Fin des temps (Emplacement 2) <span
                                class="required">*</span></label>
                        <input type="time" name="recurring_end_time_2" id="recurring_end_time_2"
                            value="<?php echo !empty($recurring_session->recurring_end_time_2) ? esc_attr($recurring_session->recurring_end_time_2) : ''; ?>">
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="modal-button confirm">Reprogrammer</button>
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

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>