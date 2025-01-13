<?php
/**
 * Cart errors page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/cart-errors.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>

<p><?php esc_html_e( 'Il existe quelques problèmes avec le forfait que vous avez choisi. Veuillez revenir à la section des tarifs et résoudre ces problèmes avant de procéder au paiement.', 'woocommerce' ); ?>
</p>

<?php do_action( 'woocommerce_cart_has_errors' ); ?>