<?php

/* Template Name: Admin | Parent Details */

// page title
global $pageTitle;
$pageTitle = "Détails Des Parents";

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
$table_name = $wpdb->prefix . 'parents';
$payment_table = $wpdb->prefix . 'payments'; 

// Fetch the details of the parent using the ID
$parent = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
$payments = $wpdb->get_results($wpdb->prepare("SELECT * FROM $payment_table WHERE user_id = %d", $id));
$parentTotalPayment = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT SUM(amount) FROM $payment_table WHERE user_id = %d",
        $id
    )
);

if (!$parent) {
    // Handle case when the parent does not exist
    wp_die("Le parent demandé n'a pas pu être trouvé.");
}

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminStudentDetails" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Détails Des Parents</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <a href="<?php echo home_url('/admin/parent-management/'); ?>" class="breadcrumb-link">Gestion
                    Parents</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Détails Des Parents</span>
            </div>
        </div>

        <div class="content-section">
            <div class="row">
                <div class="col section user-profile">
                    <div class="profile-top">
                        <img src="<?php echo !empty($parent->image) ? esc_url($parent->image) : esc_url(get_stylesheet_directory_uri() . '/assets/image/user.png'); ?>"
                            alt="User Image" class="profile-image">

                        <h3 class="profile-name">
                            <?php echo esc_html($parent->first_name) . " " . esc_html($parent->last_name); ?></h3>
                        <p class="profile-username"><?php echo esc_html($wp_user->user_login); ?></p>
                    </div>
                    <div class="profile-details">
                        <div class="row detail-row">
                            <span class="col detail-label">Email:</span>
                            <span class="col detail-value"><?php echo esc_html($wp_user->user_email); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Téléphone:</span>
                            <span class="col detail-value"><?php echo esc_html($parent->phone); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Adresse:</span>
                            <span
                                class="col detail-value"><?php echo esc_html($parent->address) . ", " . esc_html($parent->city) . ", " . esc_html($parent->country) . "-" . esc_html($parent->zipcode); ?></span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Paiement total:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($parentTotalPayment); ?>
                            </span>
                        </div>
                        <div class="row detail-row">
                            <span class="col detail-label">Crédit disponible:</span>
                            <span class="col detail-value">
                                <?php echo esc_html($parent->credit); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col section user-courses">
                    <h3 class="section-heading">Informations sur l'enfant</h3>

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
                                                esc_url(home_url('/admin/parent-management/parent-invoice/?id=' . $payment->id)),
                                                esc_url(home_url('/admin/parent-management/parent-invoice/pdf/?id=' . $payment->id))
                                            );
                                        }
                                    
                                        // Output all rows in one go
                                        echo '<tbody id="list">' . implode('', $rows) . '</tbody>';
                                    } else {
                                        echo '<tr><td colspan="7" class="no-data">No payments found.</td></tr>';
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