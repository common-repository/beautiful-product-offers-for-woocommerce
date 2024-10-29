<?php
/**
 * Displays the simple group product tab in the product data meta box.
 *
 * @var object $product_object The item being displayed
 * @package wbpo\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id='wbpo_settings' class='panel woocommerce_options_panel hidden'>
	<div class="options_group">

	<?php
			woocommerce_wp_text_input(
				[
					'id'          => 'wbpo_search_input',
					'value'       => '',
					'label'       => esc_html__( 'Add Product', 'woo-bpo' ),
					'placeholder' => esc_html__( 'Search product here', 'woo-bpo' ),
					'desc_tip'    => true,
					'description' => __( 'Add product(s) to offer. See search options in plugin settings', 'woo-bpo' ),
				]
			);
			woocommerce_wp_hidden_input(
				[
					'id'    => 'wbpo_current_group',
					'value' => $product_object->get_meta( 'wbpo_current_group' ),
				]
			);

			?>
		<div class="form-field  group-products">
			<label for="wbpo_search_input"> <?php echo __( 'Product(s) in Offer', 'woo-bpo' ); ?></label>
			<div id="wbpo-results" class="wbpo-search-products hidden">
			</div>
			<div id="wbpo_selected" class="wbpo-search-products">
				<ul id="sortlist">
						<?php
							$items = $admin_object->formatIds( $product_object->get_meta( 'wbpo_current_group' ) );
						if ( ! empty( $items ) ) {
							if ( $items ) {
								foreach ( $items as $item ) {
									$item_product = wc_get_product( $item['id'] );
									// var_dump( $item_product );
									// die;
									if ( ! $item_product || $item_product->is_type( 'wbpo' ) ) {
										continue;
									}
									echo $admin_object->wbpo_product_data_li( $item_product, $item );
								}
							}
						}
						?>

					</ul>
			</div>
		</div>
		<?php

		//TODO: Refactor the free and pro title login for settings.
		if( wbpo_is_pro() ){
			$wbpo_checklist_label = __( 'Custom Content Under Add-to-Cart', 'woo-bpo' );
			$wbpo_product_title   = __( 'Replace Product Title', 'woo-bpo' );
			$wbpo_design_title    = __( 'Offer Design', 'woo-bpo' );
			$wbpo_offer_title     = __( 'Offer layout', 'woo-bpo' );
		} else {
			$wbpo_checklist_label = sprintf( '%1$s %2$s', __( 'Custom Content Under Add-to-Cart', 'woo-bpo' ), wbpo_is_pro_text() );
			$wbpo_product_title = sprintf( '%1$s %2$s', __( 'Replace Product Title', 'woo-bpo' ), wbpo_is_pro_text() );
			$wbpo_design_title = sprintf( '%1$s %2$s', __( 'Offer Design', 'woo-bpo' ), wbpo_is_pro_text() );
			$wbpo_offer_title = sprintf( '%1$s %2$s', __( 'Offer Layout', 'woo-bpo' ), wbpo_is_pro_text() );
		}

		woocommerce_wp_select(
			[
				'id'          => 'wbpo_offer_position',
				'value'       => $product_object->get_meta( 'wbpo_offer_position' ),
				'label'       => __( 'Offer Position' , 'woo-bpo'),
				'options'     => [
					''   => __( 'Default', 'woo-bpo' ),
					'above_atc' => __( 'Above Add to Cart Button', 'woo-bpo' ),
					'below_atc' => __( 'Under Add to Cart Button', 'woo-bpo' ),
				],
			]
		);

		woocommerce_wp_select(
			[
				'id'          => 'wbpo_selection_type',
				'value'       => $product_object->get_meta( 'wbpo_selection_type' ),
				'label'       => __( 'Offer Selection Type', 'woo-bpo' ),
				'options'     => [
					''         => __( 'Default', 'woo-bpo' ),
					// 'qty'      => __( 'Quantity', 'woo-bpo' ), //Todo Remove totaly funcationalty code {SS 16-10-21 }
					'checkbox' => __( 'Multiple Selections', 'woo-bpo' ),
					'radio'    => __( 'Single Selection', 'woo-bpo' ),
				],
			]
		);

		woocommerce_wp_checkbox(
			[
				'id'          => 'wbpo_main_product_in_cart',
				'value'       => $product_object->get_meta( 'wbpo_main_product_in_cart' ),
				'label'       => esc_html__( 'Add Main Product in Cart', 'woo-bpo' ),
				'desc_tip'    => true,
				'description' => __( 'Suggested use cases: Product Kits, Pick and Mix Bundles, Bundle Sells and Upsells.', 'woo-bpo' ),
			]
		);

		woocommerce_wp_select(
			[
				'id'          => 'wbpo_offer_design',
				'value'       => $product_object->get_meta( 'wbpo_offer_design' ),
				'label'       => $wbpo_design_title,
				'options'     => [
					''   => __( 'Default', 'woo-bpo' ),
					'simple' => __( 'Basic', 'woo-bpo' ),
					'classic rounded'    => __( 'Rounded', 'woo-bpo' ),
					'classic rectangle'    => __( 'Rectangle', 'woo-bpo')
				],
			]
		);

		woocommerce_wp_select(
			[
				'id'          => 'wbpo_offer_layout',
				'value'       => $product_object->get_meta( 'wbpo_offer_layout' ),
				'label'       => $wbpo_offer_title,
				'options'     => [
					''   => __( 'Default', 'woo-bpo' ),
					'layout-vertical'   => __( 'Vertical', 'woo-bpo' ),
					'layout-horizontal' => __( 'Horizontal', 'woo-bpo' ),
				],
			]
		);

		woocommerce_wp_checkbox(
			[
				'id'          => 'wbpo_qty_box',
				'value'       => $product_object->get_meta( 'wbpo_qty_box' ),
				'label'       => esc_html__( 'Show Quantity Box', 'woo-bpo' ),
				'desc_tip'    => true,
				'description' => __( 'If you want to show custom quantity selector.', 'woo-bpo' ),
			]
		);
		woocommerce_wp_text_input(
			[
				'id'          => 'wbpo_price_text',
				'value'       => $product_object->get_meta( 'wbpo_price_text' ),
				'label'       => esc_html__( 'Replace Price Text', 'woo-bpo' ),
				'description' => esc_html__( 'Anything you add here will replace price from “General” tab - support HTML and shortcodes', 'woo-bpo' ),
				'desc_tip'    => true,
				// 'placeholder' => __( 'It will appear next to offer price.', 'woo-bpo' ),
			]
		);
		woocommerce_wp_text_input(
			[
				'id'          => 'wbpo_atc_text',
				'value'       => $product_object->get_meta( 'wbpo_atc_text' ),
				'label'       => esc_html__( 'Replace Add To Cart Text', 'woo-bpo' ),
				'description' => esc_html__( 'Change the default Add To Cart Text', 'woo-bpo' ),
				'desc_tip'    => true,
				'placeholder' => __( 'ex: add to bag', 'woo-bpo' ),
			]
		);
		woocommerce_wp_textarea_input(
			[
				'id'    => 'wbpo_text_above',
				'value' => $product_object->get_meta( 'wbpo_text_above' ),
				'label' => esc_html__( 'Text above offer', 'woo-bpo' ),
				'placeholder' => __( 'ex. Choose your Offer', 'woo-bpo')
			]
		);
			woocommerce_wp_textarea_input(
				[
					'id'    => 'wbpo_text_below',
					'value' => $product_object->get_meta( 'wbpo_text_below' ),
					'label' => esc_html__( 'Custom content above the Add-to-Cart', 'woo-bpo' ),
					'placeholder' => __( 'ex. Offer Ends Thursday, 12:00 PM (support HTML and shortcodes)', 'woo-bpo' )
				]
			);
			woocommerce_wp_textarea_input(
				[
					'id'    => 'wbpo_checklist',
					'value' => $product_object->get_meta( 'wbpo_checklist' ),
					'label' => $wbpo_checklist_label,
					'description' => esc_html__( 'Goes under the Add-to-Cart Button - support HTML and shortcodes', 'woo-bpo' ),
					'desc_tip'    => true,
					'placeholder' => __( "Free Home Delivery - 24/7 Support - 1 Month replace warranty", 'woo-bpo' )
				]
			);
			woocommerce_wp_textarea_input(
				[
					'id'    => 'wbpo_product_title',
					'value' => $product_object->get_meta( 'wbpo_product_title' ),
					'label' => $wbpo_product_title,
					'description' => esc_html__( 'Support HTML and Shortcodes', 'woo-bpo' ),
					'desc_tip'    => true,
					
				]
			);
			?>
			
		<?php do_action( 'spgp_options_meta' ); ?>
	</div>
</div>
