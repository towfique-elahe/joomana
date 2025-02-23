<?php

// Payment System --------------------------------

add_action('woocommerce_payment_complete', 'set_order_to_completed_and_log_payment');

// Function to handle successful payment
function set_order_to_completed_and_log_payment($order_id) {
    if (!$order_id) return;

    global $wpdb;

    // Define the payments table name
    $payments_table = $wpdb->prefix . 'payments';

    // Define the credits table name
    $credits_table = $wpdb->prefix . 'credits';

    // Get the order object
    $order = wc_get_order($order_id);

    // Ensure the order exists
    if (!$order) return;

    // Get the user ID from the order
    $user_id = $order->get_user_id();

    do {
        // Generate a more robust unique invoice number
        $invoice_number = 'JMI-' . uniqid() . '-' . bin2hex(random_bytes(4));
        // Ensure the invoice number is unique in the payments table
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $payments_table WHERE invoice_number = %s", $invoice_number));
    } while ($exists > 0);

    // Extract required data from the order
    $currency = $order->get_currency(); // Currency
    $amount = $order->get_total(); // Total amount
    $payment_method = $order->get_payment_method(); // Payment method

    // Initialize total credit
    $total_credit = 0;

    // Loop through order items to fetch product meta
    foreach ($order->get_items() as $item_id => $item) {
        // Get the product ID
        $product_id = $item->get_product_id();

        // Fetch the credit amount from product meta
        $credit_amount = get_post_meta($product_id, 'credit_amount', true);

        // Accumulate the credit amount
        $total_credit += floatval($credit_amount);
    }

    // Insert data into the payments table
    $wpdb->insert(
        $payments_table,
        [
            'invoice_number' => $invoice_number,
            'user_id' => $user_id,
            'credit' => $total_credit, // Total credit from all products in the order
            'currency' => $currency,
            'amount' => $amount,
            'payment_method' => $payment_method,
            'status' => 'Complété', // Set status to 'Complété'
            'created_at' => current_time('mysql'), // Current timestamp
        ],
        [
            '%s', // invoice_number
            '%d', // user_id
            '%f', // credit
            '%s', // currency
            '%f', // amount
            '%s', // payment_method
            '%s', // status
            '%s', // created_at
        ]
    );

    // Insert data into the credits table
    $wpdb->insert(
        $credits_table,
        [
            'user_id' => $user_id,
            'credit' => $total_credit, // Total credit from all products in the order
            'transaction_type' => 'Crédité', // Set transaction_type to 'Crédité'
            'transaction_reason' => 'Crédit acheté', // Set transaction_reason to 'Crédité'
            'created_at' => current_time('mysql'), // Current timestamp
        ],
        [
            '%d', // user_id
            '%f', // credit
            '%s', // transaction_type
            '%s', // transaction_reason
            '%s', // created_at
        ]
    );

    // Check if the order status is not already completed
    if ($order->get_status() != 'completed') {
        // Update the order status to completed
        $order->update_status('completed', __('Order set to completed after successful payment', 'your-text-domain'));
    }
}


// Payment restictions for users
add_action('woocommerce_checkout_process', 'restrict_payment_to_specific_roles');
add_action('woocommerce_after_checkout_validation', 'restrict_payment_to_specific_roles', 10, 2);

function restrict_payment_to_specific_roles() {
    // Get the current user
    $current_user = wp_get_current_user();

    // Define allowed roles
    $allowed_roles = ['student', 'parent'];

    // Check if the user has any of the allowed roles
    $has_allowed_role = array_intersect($allowed_roles, $current_user->roles);

    // If no allowed role is found, show the error notice
    if (empty($has_allowed_role)) {
        // Add an error notice, but check if the notice already exists first
        if (!wc_has_notice(__("Vous n'êtes pas autorisé à effectuer un paiement. Seuls les étudiants ou les parents peuvent procéder.", 'woocommerce'), 'error')) {
            wc_add_notice(__("Vous n'êtes pas autorisé à effectuer un paiement. Seuls les étudiants ou les parents peuvent procéder."), 'error');
        }
    }
}


// redirect the users to credit management after successful payment
add_action('woocommerce_thankyou', 'redirect_after_payment');

function redirect_after_payment($order_id) {
    if (!$order_id) return;

    // Get order object
    $order = wc_get_order($order_id);

    // Ensure the order exists
    if (!$order) return;

    // Get user ID
    $user_id = $order->get_user_id();
    if (!$user_id) return;

    // Get user data
    $user = get_userdata($user_id);
    if (!$user) return;

    // Define redirection URLs based on role
    $redirect_url = home_url('/');

    if (in_array('student', (array) $user->roles)) {
        $redirect_url = home_url('/student/credit-management');
    } elseif (in_array('parent', (array) $user->roles)) {
        $redirect_url = home_url('/parent/credit-management');
    }

    // Redirect
    wp_safe_redirect($redirect_url);
    exit;
}