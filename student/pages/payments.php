<?php

/* Template Name: Student | Payments */

// page title
global $pageTitle;
$pageTitle = 'Paiements';

require_once(get_template_directory() . '/student/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get current user ID
$current_user = get_current_user_id();

// Get the 'payment' table name
global $wpdb;
$payments_table = $wpdb->prefix . 'payments';

// Get the payments made by the current user
$payments = $wpdb->get_results("SELECT * FROM $payments_table WHERE user_id = $current_user ORDER BY created_at DESC");

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/student/templates/sidebar.php'); ?>
    </div>

    <div id="studentPayments" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Paiements</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/student/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Paiements</span>
            </div>
        </div>

        <div class="content-section list">
            <div class="filter-container">
                <div class="filter-bar">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Recherche De Paiement" onkeyup="filterUser()">
                    </div>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Numéro de facture</th>
                        <th>Crédit</th>
                        <th>Montant</th>
                        <th>Méthode</th>
                        <th>Date</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody id="list">
                    <?php if (!empty($payments)) : ?>
                    <?php 
                        foreach ($payments as $payment) : 
                            $user = get_user_by('id', $payment->user_id);
                            $user_roles = $user->roles;
                    ?>
                    <tr>
                        <td class="invoice-number">
                            <?php echo esc_html($payment->invoice_number); ?>
                        </td>
                        <td>
                            <?php echo esc_html($payment->credit); ?>
                        </td>
                        <td class="payment">
                            <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                            <?php echo esc_html($payment->amount); ?>
                        </td>
                        <td>
                            <?php echo esc_html($payment->payment_method); ?>
                        </td>
                        <td>
                            <?php echo esc_html(date('M d, Y', strtotime($payment->created_at))); ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo esc_url(home_url('/student/payments/invoice/?id=' . $payment->id)); ?>"
                                    target="_blank" class="invoice"><i class="fas fa-receipt"></i></a>
                                <a href="<?php echo esc_url(home_url('/student/payments/invoice/pdf/?id=' . $payment->id)); ?>"
                                    target="_blank" class="pdf"><i class="fas fa-file-pdf"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="6">Aucun paiement trouvé.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/student/templates/footer.php'); ?>