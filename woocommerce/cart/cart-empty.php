<?php
/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="wooEmptyCart" class="row woo-empty-cart">
	<div class="cta-col">
		<h2 class="woo-empty-cart-heading">
			<?php esc_html_e( 'Votre panier est actuellement vide.', 'woocommerce' ); ?>
		</h2>

		<div class="woo-empty-cart-action">
			<a href="<?php echo esc_url( home_url( '#buyCredit' ) ); ?>" class="woo-empty-cart-action-button">Choisissez
				un forfait de crédit</a>
		</div>
	</div>
	<div class="img-col">
		<img src="<?php echo get_template_directory_uri(). '/assets/image/empty-cart.svg';?>" alt="Empty Cart"
			class="woo-empty-cart-image">
	</div>
</div>
</div>