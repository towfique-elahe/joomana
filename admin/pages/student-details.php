<?php

/* Template Name: Admin | Student Details */

// page title
global $pageTitle;
$pageTitle = "Détails Sur L'étudiant";

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

if (!$student) {
    // Handle case when the student does not exist
    wp_die("L'étudiant demandé n'a pas pu être trouvé.");
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminStudentDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails Sur L'étudiant</h2>
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
                <span class="active">Détails Sur L'étudiant</span>
            </div>
        </div>

        <div class="content-section">
            <div class="row">
                <div class="col section user-profile">
                    <div class="profile-top">
                        <img src="<?php echo !empty($student->image) ? esc_url($student->image) : esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png'); ?>"
                            alt="User Image" class="profile-image">

                        <h3 class="profile-name">
                            <?php echo esc_html($student->first_name) . " " . esc_html($student->last_name); ?></h3>
                        <p class="profile-username"><?php echo esc_html($wp_user->user_login); ?></p>
                    </div>
                    <div class="profile-details">
                        <div class="row detail-row">
                            <span class="col detail-label">Email:</span>
                            <span class="col detail-value"><?php echo esc_html($wp_user->user_email); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Date de naissance:</span>
                            <span class="col detail-value"><?php echo esc_html($student->date_of_birth); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Genre:</span>
                            <span class="col detail-value"><?php echo esc_html($student->gender); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Grade:</span>
                            <span class="col detail-value"><?php echo esc_html($student->grade); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Niveau:</span>
                            <span class="col detail-value"><?php echo esc_html($student->level); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Paiement total:</span>
                            <span class="col detail-value">n/a</span>
                        </div>
                    </div>
                </div>

                <div class="col section user-courses">
                    <h3 class="section-heading">Cours Enregistrés</h3>
                    <!-- <div class="course-list">
                        <div class="course-item">
                            <div class="course-image">
                                <img src="<?php echo get_stylesheet_directory_uri(). '/assets/image/image-placeholder.png';?>"
                                    alt="Course Image">
                            </div>
                            <div class="course-details">
                                <h4 class="course-title">Mathématiques</h4>
                                <span class="course-student">Six</span>
                                <span class="course-level">Débutant</span>
                                <span class="course-payment">20.00</span>
                            </div>
                        </div>
                    </div> -->
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
                                                    <td><a href="%s" class="invoice"><i class="fas fa-receipt"></i></a></td>
                                                </tr>',
                                                esc_html($payment->invoice_number),
                                                esc_html($payment->credit),
                                                esc_html($payment->amount),
                                                esc_html($payment->status),
                                                esc_html($payment->payment_method),
                                                esc_html(date('M d, Y', strtotime($payment->created_at))),
                                                esc_url(home_url('/admin/student-management/student-invoice/?id=' . $payment->id))
                                            );
                                        }
                                    
                                        // Output all rows in one go
                                        echo '<tbody id="list">' . implode('', $rows) . '</tbody>';
                                    } else {
                                        echo '<tr><td colspan="5" class="no-data">No payments found.</td></tr>';
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