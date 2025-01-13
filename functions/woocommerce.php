<?php
/**
 * WooCommerce Support Functions
 */

/**
 * Redirect product pages directly to the checkout with a single product.
 */
function joomana_redirect_to_checkout_with_single_product() {
    if (is_cart() || is_checkout()) {
        return;
    }

    if (is_product()) {
        global $post;

        // Clear the cart to ensure no multiple products or quantities.
        WC()->cart->empty_cart();

        // Add the product to the cart with a quantity of 1.
        $product_id = $post->ID;
        WC()->cart->add_to_cart($product_id, 1);

        // Redirect to the checkout page.
        $checkout_url = wc_get_checkout_url();
        wp_safe_redirect($checkout_url);
        exit;
    }
}
add_action('template_redirect', 'joomana_redirect_to_checkout_with_single_product');

/**
 * Disable the cart page entirely.
 */
function joomana_disable_cart_page() {
    if (is_cart()) {
        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }
}
add_action('template_redirect', 'joomana_disable_cart_page');

/**
 * Always keep only the last product added to the cart in the checkout page.
 */
function joomana_keep_only_last_product_in_checkout() {
    if (is_checkout()) {
        $cart = WC()->cart->get_cart();
        $product_keys = array_keys($cart);

        if (!empty($product_keys)) {
            // Get the last product added to the cart.
            $last_product_key = end($product_keys);
            $last_product_id = $cart[$last_product_key]['product_id'];

            // Clear the cart and add only the last product back.
            WC()->cart->empty_cart();
            WC()->cart->add_to_cart($last_product_id, 1);
        }
    }
}
add_action('template_redirect', 'joomana_keep_only_last_product_in_checkout');