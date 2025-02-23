<?php

/* Template Name: Parent | Credit Management */

// page title
global $pageTitle;
$pageTitle = 'Gestion De Crédit';

require_once(get_template_directory() . '/parent/templates/header.php');

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get current user ID
$current_user = get_current_user_id();

// Get table name
global $wpdb;
$parent_table = $wpdb->prefix . 'parents';
$credits_table = $wpdb->prefix . 'credits';

// Get the parent details by the current user
$parent = $wpdb->get_row($wpdb->prepare("SELECT * FROM $parent_table WHERE id = %d", $current_user));

// Get the parent's credit transactions by the current user
$credit_transactions = $wpdb->get_results($wpdb->prepare("SELECT * FROM $credits_table WHERE user_id = %d ORDER BY created_at DESC", $current_user));

?>

<div class="content-area">
    <div class="sidebar-container">
        <?php require_once(get_template_directory() . '/parent/templates/sidebar.php'); ?>
    </div>
    <div id="parentCredit" class="main-content">
        <div class="content-header">
            <h2 class="content-title">Gestion de crédit</h2>
            <div class="content-breadcrumb">
                <a href="<?php echo home_url('/parent/dashboard'); ?>" class="breadcrumb-link">Tableau de bord</a>
                <span class="separator">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <span class="active">Gestion de crédit</span>
            </div>
        </div>

        <div class="content-section statistics">
            <div class="section-body">

                <!-- Available Credit Count -->
                <a href="javascript:void()" class="statistic-box total-teacher">
                    <h4 class="statistic-title">
                        <i class="fas fa-chalkboard-teacher"></i> Crédit disponible
                    </h4>
                    <p class="statistic-value">
                        <?php echo esc_html($parent->credit); ?>
                    </p>
                </a>

                <a href="<?php echo home_url('/#buyCredit'); ?>" class="button buy-credit">
                    <i class="fas fa-shopping-bag"></i> Buy Credit
                </a>

            </div>
        </div>

        <div class="content-section">
            <div class="row list">
                <div class="col">
                    <!-- payments history -->
                    <div class="credit-history">
                        <h3 class="section-heading">Historique de crédit</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Crédit</th>
                                    <th>Transaction Type</th>
                                    <th>Transaction Reason</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <?php 
                                    if ($credit_transactions) {
                                        // Start the table body and prepare an array for rows
                                        $rows = [];
                                    
                                        // Loop through the fetched payments and prepare the rows
                                        foreach ($credit_transactions as $transaction) {
                                            $rows[] = sprintf(
                                                '<tr>
                                                    <td class="credit">%d</td>
                                                    <td>%s</td>
                                                    <td>%s</td>
                                                    <td>%s</td>
                                                </tr>',
                                                esc_html($transaction->credit),
                                                esc_html($transaction->transaction_type),
                                                esc_html($transaction->transaction_reason),
                                                esc_html(date('M d, Y', strtotime($transaction->created_at))),
                                            );
                                        }
                                    
                                        // Output all rows in one go
                                        echo '<tbody id="list">' . implode('', $rows) . '</tbody>';
                                    } else {
                                        echo '<tr><td colspan="4" class="no-data">Aucune transaction de crédit trouvée.</td></tr>';
                                    }
                                ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once(get_template_directory() . '/parent/templates/footer.php'); ?>