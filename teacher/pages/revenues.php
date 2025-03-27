<?php

/* Template Name: Teacher | Revenues */

// page title
global $pageTitle;
$pageTitle = 'Revenus';

require_once(get_template_directory() . '/teacher/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// current user ID
$teacher_id = get_current_user_id();

global $wpdb;

$payments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}teacher_payments WHERE teacher_id = $teacher_id");

// Query to get total dues
$total_dues = (int) $wpdb->get_var($wpdb->prepare( 
    "SELECT SUM(amount) FROM {$wpdb->prefix}teacher_payments 
     WHERE teacher_id = %d AND status = %s",
    $teacher_id, 'due'
));

// Query to get total deposits
$total_deposits = (int) $wpdb->get_var($wpdb->prepare( 
    "SELECT SUM(amount) FROM {$wpdb->prefix}teacher_payments 
     WHERE teacher_id = %d AND status = %s",
    $teacher_id, 'completed'
));

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/teacher/templates/sidebar.php'); ?>
    </div>
    <div id="teacherRevenue" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Revenus</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/teacher/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Revenus</span>
            </div>
        </div>

        <div class="content-section statistics">
            <div class="section-body">

                <!-- Total due -->
                <a href="javascript:void()" class="statistic-box total-due">
                    <h4 class="statistic-title">
                        <i class="fas fa-exchange-alt"></i> Total à payer
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_dues); ?>
                        <span class="currecy"><i class="fas fa-euro-sign"></i></span>
                    </p>
                </a>

                <!-- Total deposits -->
                <a href="javascript:void()" class="statistic-box total-deposit">
                    <h4 class="statistic-title">
                        <i class="fas fa-exchange-alt"></i> Total terminé
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($total_deposits); ?>
                        <span class="currecy"><i class="fas fa-euro-sign"></i></span>
                    </p>
                </a>

            </div>
        </div>

        <div class="content-section list">
            <div class="filter-container">
                <a href="<?php echo home_url('/teacher/revenues/bank-details/'); ?>" class="button add-button">
                    <i class="fas fa-university"></i>
                    Informations Bancaires
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
                        <th>Paiement</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody id="list">
                    <?php if (!empty($payments)) : ?>
                    <?php 
                        foreach ($payments as $payment) : 
                            $user = get_user_by('id', $payment->teacher_id);
                            $user_roles = $user->roles;
                    ?>
                    <tr>
                        <td class="invoice-number">
                            <?php echo esc_html($payment->invoice_number); ?>
                        </td>
                        <td class="payment">
                            <i class="fas fa-euro-sign fa-xs" style="color: #fc7837;"></i>
                            <?php echo intval($payment->amount) == $payment->amount ? intval($payment->amount) : number_format($payment->amount, 2); ?>
                        </td>
                        <td>
                            <?php echo esc_html($payment->payment_method); ?>
                        </td>
                        <td>
                            <?php echo esc_html($payment->status === 'in progress' ? 'En attente' : ($payment->status === 'due' ? 'À payer' : ($payment->status === 'completed' ? 'Terminé' : $payment->status))); ?>
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
                                <a href="<?php echo esc_url(home_url('/teacher/revenues/invoice/?id=' . $payment->id)); ?>"
                                    target="_blank" class="invoice"><i class="fas fa-receipt"></i></a>
                                <a href="<?php echo esc_url(home_url('/teacher/revenues/invoice/pdf/?id=' . $payment->id)); ?>"
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

<?php require_once(get_template_directory() . '/teacher/templates/footer.php'); ?>