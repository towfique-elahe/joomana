<?php

// Payment System  --------------------------------

add_action('woocommerce_payment_complete', 'set_order_to_completed');

function set_order_to_completed($order_id) {
    if (!$order_id) return;

    // Get the order object
    $order = wc_get_order($order_id);

    // Check if the order status is not already completed
    if ($order->get_status() != 'completed') {
        // Update the order status to completed
        $order->update_status('completed', __('Order set to completed after successful payment', 'your-text-domain'));
    }
}