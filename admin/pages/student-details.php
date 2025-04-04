<?php

/* Template Name: Admin | Student Details */

// page title
global $pageTitle;
$pageTitle = "Détails Sur L'elèves";

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if the id is present in the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the details of the user from wp users table
if ($id > 0) {
    $wp_user = get_user_by('ID', $id);
} else {
    echo "Invalid user ID.";
}

global $wpdb;
$student_table = $wpdb->prefix . 'students';
$payment_table = $wpdb->prefix . 'payments'; 

// Fetch the details of the student using the ID
$student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $student_table WHERE id = %d", $id));
$payments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $payment_table WHERE user_id = %d", $id));
$studentTotalPayment = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT SUM(amount) FROM $payment_table WHERE user_id = %d",
        $id
    )
);

if (!$student) {
    // Handle case when the student does not exist
    wp_die("L'elèves demandé n'a pas pu être trouvé.");
}

$student_id = $student->id;

$sessions_table = $wpdb->prefix . 'course_sessions';

$upcomming_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
        WHERE JSON_CONTAINS(enrolled_students, %s) 
        AND status IN ('upcoming')",
        '"' . $student_id . '"'
    )
);

$ongoing_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
        WHERE JSON_CONTAINS(enrolled_students, %s) 
        AND status IN ('ongoing')",
        '"' . $student_id . '"'
    )
);

$completed_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
        WHERE JSON_CONTAINS(enrolled_students, %s) 
        AND status IN ('completed')",
        '"' . $student_id . '"'
    )
);

$cancelled_sessions = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $sessions_table 
        WHERE JSON_CONTAINS(enrolled_students, %s) 
        AND status IN ('cancelled')",
        '"' . $student_id . '"'
    )
);

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminStudentDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails Sur L'elèves</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/student-management/'); ?>" class="breadcrumb-link">Gestion
                    étudiants</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Détails Sur L'elèves</span>
            </div>
        </div>

        <div class="content-section">
            <div class="row">
                <div class="col section user-profile">
                    <div class="profile-top">
                        <img src="<?php echo !empty($student->image) ? esc_url($student->image) : esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png'); ?>"
                            alt="User Image" class="profile-image">

                        <h3 class="profile-name">
                            <?php echo esc_html($student->first_name) . " " . esc_html($student->last_name); ?>
                        </h3>
                        <p class="profile-username">
                            <?php echo esc_html($wp_user->user_login); ?>
                        </p>
                    </div>
                    <div class="profile-details">
                        <div class="row detail-row">
                            <span class="col detail-label">Email:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($wp_user->user_email); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Date de naissance:</span>
                            <span class="col detail-value">
                                <?php echo esc_html( date('M j, Y', strtotime($student->date_of_birth)) ); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Genre:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($student->gender); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Classe:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($student->grade); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Niveau:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($student->level); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Paiement total:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($studentTotalPayment !== null ? $studentTotalPayment : 0); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Crédit disponible:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($student->credit); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col section user-courses">

                    <h3 class="section-heading">Cours Enregistrés</h3>

                    <ul class="nav nav-tabs" id="courseTabs">
                        <a class="nav-link active" data-toggle="tab" href="#upcoming">Cours à venir</a>
                        <a class="nav-link" data-toggle="tab" href="#ongoing">Cours en cours</a>
                        <a class="nav-link" data-toggle="tab" href="#completed">Cours terminés</a>
                        <a class="nav-link" data-toggle="tab" href="#cancelled">Cours annulés</a>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="upcoming">
                            <div class="row">
                                <div class="col">
                                    <div class="courses">
                                        <?php 
                                    $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
                                    if (!empty($upcomming_sessions)): 
                                        foreach ($upcomming_sessions as $session): 
                                            $course_id = $session->course_id;
                                            $session_date = $session->session_date;
                                            $session_date = date('M d, Y', strtotime($session->session_date));
                                            $status = $session->status;
                                            $slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
                                            $slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

                                            $table_name = $wpdb->prefix . 'courses';
                                            $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $course_id));

                                            // Translation map
                                            $translations = array(
                                                'upcoming'  => 'À venir',
                                                'ongoing'   => 'En cours',
                                                'completed' => 'Terminé',
                                                'cancelled' => 'Annulé',
                                            );

                                            // Convert status to lowercase just in case
                                            $status_key = strtolower($status);

                                            // Translate
                                            $french_status = isset($translations[$status_key]) ? $translations[$status_key] : $status;
                                            
                                ?>
                                        <div class="course-card">
                                            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                                alt="Course Image" class="course-image">
                                            <span
                                                class="course-tag in-progress"><?php echo esc_html($french_status); ?></span>
                                            <h3 class="course-title">
                                                <?php echo esc_html($course->title); ?>
                                            </h3>
                                            <div class="course-info">
                                                <p class="date">
                                                    Date:
                                                    <?php echo esc_html($session_date);?>
                                                </p>
                                                <p class="date">
                                                    Temps 1:
                                                    <?php echo esc_html($slot_1);?>
                                                </p>
                                                <p class="date">
                                                    Temps 2:
                                                    <?php echo esc_html($slot_2);?>
                                                </p>
                                            </div>
                                            <div class="course-footer">
                                                <a href="<?php echo esc_url(home_url('/admin/session-management/session-details/?session_id=' . $session->id)); ?>"
                                                    class="course-btn">Voir les détails</a>
                                            </div>
                                        </div>
                                        <?php endforeach; else: ?>
                                        <p class="no-data">Aucun cours à venir.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="ongoing">
                            <div class="row">
                                <div class="col">
                                    <div class="courses">
                                        <?php 
                                    $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
                                    if (!empty($ongoing_sessions)): 
                                        foreach ($ongoing_sessions as $session): 
                                            $course_id = $session->course_id;
                                            $session_date = $session->session_date;
                                            $session_date = date('M d, Y', strtotime($session->session_date));
                                            $status = $session->status;
                                            $slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
                                            $slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

                                            $table_name = $wpdb->prefix . 'courses';
                                            $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $course_id));

                                            // Translation map
                                            $translations = array(
                                                'upcoming'  => 'À venir',
                                                'ongoing'   => 'En cours',
                                                'completed' => 'Terminé',
                                                'cancelled' => 'Annulé',
                                            );

                                            // Convert status to lowercase just in case
                                            $status_key = strtolower($status);

                                            // Translate
                                            $french_status = isset($translations[$status_key]) ? $translations[$status_key] : $status;
                                            
                                ?>
                                        <div class="course-card">
                                            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                                alt="Course Image" class="course-image">
                                            <span
                                                class="course-tag in-progress"><?php echo esc_html($french_status); ?></span>
                                            <h3 class="course-title">
                                                <?php echo esc_html($course->title); ?>
                                            </h3>
                                            <div class="course-info">
                                                <p class="date">
                                                    Date:
                                                    <?php echo esc_html($session_date);?>
                                                </p>
                                                <p class="date">
                                                    Temps 1:
                                                    <?php echo esc_html($slot_1);?>
                                                </p>
                                                <p class="date">
                                                    Temps 2:
                                                    <?php echo esc_html($slot_2);?>
                                                </p>
                                            </div>
                                            <div class="course-footer">
                                                <a href="<?php echo esc_url(home_url('/admin/session-management/session-details/?session_id=' . $session->id)); ?>"
                                                    class="course-btn">Voir les détails</a>
                                            </div>
                                        </div>
                                        <?php endforeach; else: ?>
                                        <p class="no-data">Pas de cours en cours.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="completed">
                            <div class="row">
                                <div class="col">
                                    <div class="courses">
                                        <?php 
                                    $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
                                    if (!empty($completed_sessions)): 
                                        foreach ($completed_sessions as $session): 
                                            $course_id = $session->course_id;
                                            $session_date = $session->session_date;
                                            $session_date = date('M d, Y', strtotime($session->session_date));
                                            $status = $session->status;
                                            $slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
                                            $slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

                                            $table_name = $wpdb->prefix . 'courses';
                                            $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $course_id));

                                            // Translation map
                                            $translations = array(
                                                'upcoming'  => 'À venir',
                                                'ongoing'   => 'En cours',
                                                'completed' => 'Terminé',
                                                'cancelled' => 'Annulé',
                                            );

                                            // Convert status to lowercase just in case
                                            $status_key = strtolower($status);

                                            // Translate
                                            $french_status = isset($translations[$status_key]) ? $translations[$status_key] : $status;
                                            
                                ?>
                                        <div class="course-card">
                                            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                                alt="Course Image" class="course-image">
                                            <span
                                                class="course-tag in-progress"><?php echo esc_html($french_status); ?></span>
                                            <h3 class="course-title">
                                                <?php echo esc_html($course->title); ?>
                                            </h3>
                                            <div class="course-info">
                                                <p class="date">
                                                    Date:
                                                    <?php echo esc_html($session_date);?>
                                                </p>
                                                <p class="date">
                                                    Temps 1:
                                                    <?php echo esc_html($slot_1);?>
                                                </p>
                                                <p class="date">
                                                    Temps 2:
                                                    <?php echo esc_html($slot_2);?>
                                                </p>
                                            </div>
                                            <div class="course-footer">
                                                <a href="<?php echo esc_url(home_url('/admin/session-management/session-details/?session_id=' . $session->id)); ?>"
                                                    class="course-btn">Voir les détails</a>
                                            </div>
                                        </div>
                                        <?php endforeach; else: ?>
                                        <p class="no-data">Aucun cours terminé.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="cancelled">
                            <div class="row">
                                <div class="col">
                                    <div class="courses">
                                        <?php 
                                    $default_image = get_template_directory_uri() . '/assets/image/image-placeholder.png';
                                    if (!empty($cancelled_sessions)): 
                                        foreach ($cancelled_sessions as $session): 
                                            $course_id = $session->course_id;
                                            $session_date = $session->session_date;
                                            $session_date = date('M d, Y', strtotime($session->session_date));
                                            $status = $session->status;
                                            $slot_1 = date('h:i A', strtotime($session->slot1_start_time)) . ' - ' . date('h:i A', strtotime($session->slot1_end_time));
                                            $slot_2 = date('h:i A', strtotime($session->slot2_start_time)) . ' - ' . date('h:i A', strtotime($session->slot2_end_time));

                                            $table_name = $wpdb->prefix . 'courses';
                                            $course = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $course_id));

                                            // Translation map
                                            $translations = array(
                                                'upcoming'  => 'À venir',
                                                'ongoing'   => 'En cours',
                                                'completed' => 'Terminé',
                                                'cancelled' => 'Annulé',
                                            );

                                            // Convert status to lowercase just in case
                                            $status_key = strtolower($status);

                                            // Translate
                                            $french_status = isset($translations[$status_key]) ? $translations[$status_key] : $status;
                                            
                                ?>
                                        <div class="course-card">
                                            <img src="<?php echo esc_url( $course->image ? $course->image : $default_image ); ?>"
                                                alt="Course Image" class="course-image">
                                            <span
                                                class="course-tag in-progress"><?php echo esc_html($french_status); ?></span>
                                            <h3 class="course-title">
                                                <?php echo esc_html($course->title); ?>
                                            </h3>
                                            <div class="course-info">
                                                <p class="date">
                                                    Date:
                                                    <?php echo esc_html($session_date);?>
                                                </p>
                                                <p class="date">
                                                    Temps 1:
                                                    <?php echo esc_html($slot_1);?>
                                                </p>
                                                <p class="date">
                                                    Temps 2:
                                                    <?php echo esc_html($slot_2);?>
                                                </p>
                                            </div>
                                            <div class="course-footer">
                                                <a href="<?php echo esc_url(home_url('/admin/session-management/session-details/?session_id=' . $session->id)); ?>"
                                                    class="course-btn">Voir les détails</a>
                                            </div>
                                        </div>
                                        <?php endforeach; else: ?>
                                        <p class="no-data">Aucun cours annulé.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="row list">
                <div class="col">
                    <!-- payments history -->
                    <div class="user-payments">
                        <h3 class="section-heading">Historique des paiements</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Commande</th>
                                    <th>Crédit</th>
                                    <th>Prix ​​total</th>
                                    <th>Statut</th>
                                    <th>Mode de paiement</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                </tr>
                            </thead>
                            <?php 
                                    if ($payments) {
                                        // Start the table body and prepare an array for rows
                                        $rows = [];
                                    
                                        // Loop through the fetched payments and prepare the rows
                                        foreach ($payments as $payment) {
                                            $rows[] = sprintf(
                                                '<tr>
                                                    <td>%s</td>
                                                    <td class="credit">%d</td>
                                                    <td class="payment">
                                                    <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i> %s
                                                    </td>
                                                    <td>%s</td>
                                                    <td>%s</td>
                                                    <td>%s</td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="%s" target="_blank" class="invoice"><i class="fas fa-receipt"></i></a>
                                                            <a href="%s" target="_blank" class="pdf"><i class="fas fa-file-pdf"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>',
                                                esc_html($payment->invoice_number),
                                                esc_html($payment->credit),
                                                esc_html($payment->amount),
                                                esc_html($payment->status),
                                                esc_html($payment->payment_method),
                                                esc_html(date('M d, Y', strtotime($payment->created_at))),
                                                esc_url(home_url('/admin/student-management/student-invoice/?id=' . $payment->id)),
                                                esc_url(home_url('/admin/student-management/student-invoice/pdf/?id=' . $payment->id))
                                            );
                                        }
                                    
                                        // Output all rows in one go
                                        echo '<tbody id="list">' . implode('', $rows) . '</tbody>';
                                    } else {
                                        echo '<tr><td colspan="7" class="no-data">Aucun paiement trouvé.</td></tr>';
                                    }
                                ?>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>