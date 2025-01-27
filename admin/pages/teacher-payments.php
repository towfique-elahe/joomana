<?php

/* Template Name: Admin | Teacher Payments */

// page title
global $pageTitle;
$pageTitle = 'Paiements Prof';

require_once(get_template_directory() . '/admin/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$payments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}teacher_payments");

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/admin/templates/sidebar.php'); ?>
    </div>
    <div id="adminTeacherPayment" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Paiements Prof</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/admin/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Paiements Prof</span>
            </div>
        </div>

        <div class="content-section list">
            <div class="filter-container">
                <a href="<?php echo home_url('/admin/teacher-payments/make-payment/'); ?>" class="button add-button">
                    <i class="fas fa-plus-circle"></i>
                    Effectuer le paiement
                </a>

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
                        <th>Montant</th>
                        <th>Exigible</th>
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
                                if (in_array('teacher', $user_roles)) {
                                // If the user has the 'teacher' role
                            ?>
                            <a
                                href="<?php echo esc_url(home_url('/admin/teacher-management/teacher-details/?id=' . $user->id)); ?>">
                                <?php echo esc_html($user->first_name) . ' ' . esc_html($user->last_name); ?>
                            </a>
                            <?php
                                }
                            ?>
                        </td>
                        <td class="payment">
                            <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                            <?php echo esc_html($payment->deposit); ?>
                        </td>
                        <td class="payment">
                            <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                            <?php echo esc_html($payment->due); ?>
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
                                if (in_array('teacher', $user_roles)) {
                                // If the user has the 'teacher' role
                            ?>
                            <div class="action-buttons">
                                <a href="<?php echo esc_url(home_url('/admin/teacher-payments/invoice/?id=' . $payment->id)); ?>"
                                    target="_blank" class="invoice"><i class="fas fa-receipt"></i></a>
                                <a href="<?php echo esc_url(home_url('/admin/teacher-payments/invoice/pdf/?id=' . $payment->id)); ?>"
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