<?php
/**
 * Displays the simple group product tab in the product data meta box.
 *
 * @var object $product_object The item being displayed
 * @package WbPo\Public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$show_quantity_box    = $product->get_meta( 'wbpo_qty_box' );
 if( 'yes' !== $show_quantity_box ): ?>
 <style>
		.product-type-wbpo form.cart div.quantity {
			display: none !important;
		}
	</style>
<?php endif;?>

<div class="wbpo-form">
	<?php
	function wbpo_offers( $admin_object){
		do_action( 'wbpo_before_offer' );
		$admin_object->get_group_products();
		// wc_get_template( 'single-product/add-to-cart/simple.php' );
		do_action( 'wbpo_before_offer' );	
	}
	$wbpo_position = wbpo_get_setting( 'position', 'wbpo_setting', 'above_atc' );
	if ( $product_offer_position= $product->get_meta( 'wbpo_offer_position' ) ) {
		$wbpo_position = $product_offer_position;
	}

	if ( 'below_atc' === $wbpo_position ) {
		wc_get_template( 'single-product/add-to-cart/simple.php' );
	}

		wbpo_offers($admin_object);

		if ( 'above_atc' === $wbpo_position  ) {
			wc_get_template( 'single-product/add-to-cart/simple.php' );
		}
	do_action( 'wbpo_after_atc' );
	?>
</div>
