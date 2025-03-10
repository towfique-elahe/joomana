<?php

/* Template Name: Admin | Payments */

// page title
global $pageTitle;
$pageTitle = 'Paiements';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Query to fetch all payment
global $wpdb; // Access the global $wpdb object for database queries

// Query the custom 'payment' table
$payments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}payments");

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminPayments" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Paiements</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
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
                        <th>Nom</th>
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
                        <td class="name">
                            <?php
                                // Assuming $user_roles contains the roles of the current user
                                if (in_array('parent', $user_roles)) {
                                // If the user has the 'parent' role
                            ?>
                            <a
                                href="<?php echo esc_url(home_url('/admin/parent-management/parent-details/?id=' . $user->ID)); ?>">
                                <?php echo esc_html($user->first_name) . ' ' . esc_html($user->last_name); ?>
                            </a>
                            <?php
                                } elseif (in_array('student', $user_roles)) {
                                // If the user has the 'student' role
                            ?>
                            <a
                                href="<?php echo esc_url(home_url('/admin/student-management/student-details/?id=' . $user->ID)); ?>">
                                <?php echo esc_html($user->first_name) . ' ' . esc_html($user->last_name); ?>
                            </a>
                            <?php
                                }
                            ?>
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
                            <?php
                                // Assuming $user_roles contains the roles of the current user
                                if (in_array('parent', $user_roles)) {
                                // If the user has the 'parent' role
                            ?>
                            <div class="action-buttons">
                                <a href="<?php echo esc_url(home_url('/admin/payments/parent-invoice/?id=' . $payment->id)); ?>"
                                    target="_blank" class="invoice"><i class="fas fa-receipt"></i></a>
                                <a href="<?php echo esc_url(home_url('/admin/payments/parent-invoice/pdf/?id=' . $payment->id)); ?>"
                                    target="_blank" class="pdf"><i class="fas fa-file-pdf"></i></a>
                            </div>
                            <?php
                                } elseif (in_array('student', $user_roles)) {
                                // If the user has the 'student' role
                            ?>
                            <div class="action-buttons">
                                <a href="<?php echo esc_url(home_url('/admin/payments/student-invoice/?id=' . $payment->id)); ?>"
                                    target="_blank" class="invoice"><i class="fas fa-receipt"></i></a>
                                <a href="<?php echo esc_url(home_url('/admin/payments/student-invoice/pdf/?id=' . $payment->id)); ?>"
                                    target="_blank" class="pdf"><i class="fas fa-file-pdf"></i></a>
                            </div>
                            <?php
                                }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="7">Aucun paiement trouvé.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/admin/templates/footer.php'); ?>