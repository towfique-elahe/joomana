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

/**
 * Translating checkout page billing details fields content ot french.
 */
add_filter('woocommerce_checkout_fields', 'custom_woocommerce_billing_fields');

function custom_woocommerce_billing_fields($fields) {
    // Modify billing fields
    $fields['billing']['billing_first_name']['label'] = 'Prénom';
    $fields['billing']['billing_first_name']['placeholder'] = 'Entrez votre prénom';

    $fields['billing']['billing_last_name']['label'] = 'Nom';
    $fields['billing']['billing_last_name']['placeholder'] = 'Entrez votre nom';

    $fields['billing']['billing_company']['label'] = 'Nom de l\'entreprise (optionnel)';
    $fields['billing']['billing_company']['placeholder'] = 'Entrez le nom de votre entreprise';

    $fields['billing']['billing_country']['label'] = 'Pays';
    $fields['billing']['billing_country']['placeholder'] = 'Sélectionnez votre pays';

    $fields['billing']['billing_address_1']['label'] = 'Adresse';
    $fields['billing']['billing_address_1']['placeholder'] = 'Entrez votre adresse';

    $fields['billing']['billing_address_2']['label'] = 'Complément d\'adresse (optionnel)';
    $fields['billing']['billing_address_2']['placeholder'] = 'Appartement, bureau, etc.';

    $fields['billing']['billing_city']['label'] = 'Ville';
    $fields['billing']['billing_city']['placeholder'] = 'Entrez votre ville';

    $fields['billing']['billing_state']['label'] = 'État/Région';
    $fields['billing']['billing_state']['placeholder'] = 'Entrez votre région';

    $fields['billing']['billing_postcode']['label'] = 'Code Postal';
    $fields['billing']['billing_postcode']['placeholder'] = 'Entrez votre code postal';

    $fields['billing']['billing_phone']['label'] = 'Téléphone';
    $fields['billing']['billing_phone']['placeholder'] = 'Entrez votre numéro de téléphone';

    $fields['billing']['billing_email']['label'] = 'Adresse e-mail';
    $fields['billing']['billing_email']['placeholder'] = 'Entrez votre adresse e-mail';

    return $fields;
}