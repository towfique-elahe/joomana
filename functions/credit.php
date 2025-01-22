<?php

// Credit System  --------------------------------

// Add custom field in the product data section
function add_credit_amount_custom_field() {
    echo '<div class="options_group">';
    woocommerce_wp_text_input([
        'id'          => 'credit_amount',
        'label'       => __('Credit Amount', 'woocommerce'),
        'placeholder' => __('Enter credit amount', 'woocommerce'),
        'desc_tip'    => true,
        'description' => __('This defines the number of credits for this product.', 'woocommerce'),
        'type'        => 'number',
        'custom_attributes' => [
            'step' => 'any',
            'min'  => '0'
        ],
    ]);
    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'add_credit_amount_custom_field');

// Save the custom field value
function save_credit_amount_custom_field($post_id) {
    $credit_amount = isset($_POST['credit_amount']) ? sanitize_text_field($_POST['credit_amount']) : '';
    update_post_meta($post_id, 'credit_amount', $credit_amount);
}
add_action('woocommerce_process_product_meta', 'save_credit_amount_custom_field');

// Update credit balance on order completion
function update_user_credits_on_order_complete($order_id) {
    // Get the order object
    $order = wc_get_order($order_id);
    if (!$order) {
        error_log("Order not found for ID: $order_id");
        return;
    }

    // Get the user ID associated with the order
    $user_id = $order->get_user_id();

    // Get the user object
    $user = get_user_by('ID', $user_id);

    // Get the user roles
    $role = $user->roles;

    // Check if the role is either 'student' or 'parent'
    if (!in_array('student', $role) && !in_array('parent', $role)) {
        error_log("User ID: $user_id has an unsupported role: " . implode(', ', $role));
        return;
    }

    global $wpdb;
    $student_table = $wpdb->prefix . 'students';
    $parent_table = $wpdb->prefix . 'parents';

    // Get the purchased credit amount
    $total_credits = 0;
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $credit_amount = get_post_meta($product_id, 'credit_amount', true);

        if ($credit_amount) {
            $total_credits += (float) $credit_amount * $item->get_quantity();
        }
    }

    if (in_array('student', $user->roles)) {
        // Get the current credit balance
        $current_credit = (float) $wpdb->get_var(
            $wpdb->prepare("SELECT credit FROM {$student_table} WHERE id = %d", $user_id)
        );
        // Calculate the new credit balance
        $new_credit = $current_credit + $total_credits;

        // Update the credit balance
        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$student_table} SET credit = %d WHERE id = %d",
                $new_credit,
                $user_id
            )
        );
    } elseif (in_array('parent', $user->roles)) {
        // Get the current credit balance
        $current_credit = (float) $wpdb->get_var(
            $wpdb->prepare("SELECT credit FROM {$parent_table} WHERE id = %d", $user_id)
        );
        // Calculate the new credit balance
        $new_credit = $current_credit + $total_credits;

        // Update the credit balance
        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$parent_table} SET credit = %d WHERE id = %d",
                $new_credit,
                $user_id
            )
        );
    }
    else {
        error_log("User ID: $user_id has an unsupported role: $role");
    }
}
add_action('woocommerce_payment_complete', 'update_user_credits_on_order_complete');