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

do_action( 'wbpo_before_wrap', $product );
$product_id           = $product->get_id();
$order                = 1;
$selection_type       = wbpo_get_setting( 'selection_type', 'wbpo_setting', 'checkbox' );
$group_items          = $this->formatIds( $product->get_meta( 'wbpo_current_group' ) );
$product_click_action = wbpo_get_setting( 'product_item_click', 'wbpo_setting', 'new_tab' );
$price_visibility     = wbpo_get_setting( 'show_price', 'wbpo_setting', 'on' );
$desc_visibility      = wbpo_get_setting( 'show_desc', 'wbpo_setting', 'off' );
$qty_btn_visibility   = wbpo_get_setting( 'show_qty_modify', 'wbpo_setting' );
$wrap_before_text     = apply_filters( 'wbpo_before_text', $product->get_meta( 'wbpo_text_above' ), $product_id );
$wrap_after_text      = apply_filters( 'wbpo_before_text', $product->get_meta( 'wbpo_text_below' ), $product_id );
$design_class         = apply_filters( 'wbpo_style_class', 'simple', $product );

if ( $product->get_meta( 'wbpo_selection_type' ) ) {
	$selection_type = $product->get_meta( 'wbpo_selection_type' );
}

if ( ! empty( $wrap_before_text ) ) { ?>
	<div class="wbpo-before-text wbpo-wrap-text">
		<?php echo wp_kses( do_shortcode( stripslashes( $wrap_before_text ) ), wpbpo_allow_tags() ); ?>
	</div>
	<?php
}
do_action( 'wbpo_before_product_list', $product );
?>
<div class="wbpo-products front-wbpo-list <?php esc_attr_e( $design_class ); ?>" data-variables="<?php echo esc_attr( $product->has_variables() ? 'yes' : 'no' ); ?>">
<?php
do_action( 'wbpo_before_products', $product );
foreach ( $group_items as $group_item ) :

	$wbpo_product = wc_get_product( $group_item['id'] );
	if ( ! $wbpo_product ) {
		continue;
	}

	$item_price = wc_get_price_to_display( $wbpo_product );
	$item_qty   = $group_item['qty'];
	$item_id    = $wbpo_product->is_type( 'variable' ) ? 0 : $group_item['id'];
	$item_class = 'wbpo-product';

	if ( $wbpo_product->is_purchasable() && $wbpo_product->is_in_stock() ) {
		$min = apply_filters( 'woocommerce_quantity_input_min', 0, $wbpo_product );
		$max = apply_filters( 'woocommerce_quantity_input_max', $wbpo_product->get_max_purchase_quantity(), $wbpo_product );

		if ( $max < 0 ) {
			$max = 1000;
		}

		if ( $item_qty < $min ) {
			$item_qty = $min;
		}

		if ( ( $max > 0 ) && ( $item_qty > $max ) ) {
			$item_qty = $max;
		}

		if ( $item_qty && ( 'checkbox' === $selection_type ) ) {
			// $item_qty = 1; // Will fix it need to add class checked in wrapper dynamically
			$item_qty = 0;
		}

		if ( 'radio' === $selection_type ) {
			$item_qty = 0;
		}
	} else {
		$item_class .= ' wbpo-unpurchasable';
		$item_price  = 0;
		$item_qty    = 0;
		$item_id     = -1;
	}

	?>

	<div class="<?php echo esc_attr( $item_class ); ?>" data-id="<?php echo esc_attr( $item_id ); ?>" data-price="<?php echo esc_attr( $item_price ); ?>" data-qty="<?php echo esc_attr( $item_qty ); ?>" data-order="<?php echo esc_attr( $order ); ?>" data-selection-type="<?php echo esc_attr( $selection_type ); ?>" >
		<?php
			do_action( 'wbpo_before_product', $wbpo_product, $product, $order );
		if ( 'checkbox' === $selection_type ) {
			?>
				<div class="wbpo-choose">
					<label>
						<?php if ( $wbpo_product->is_purchasable() && $wbpo_product->is_in_stock() ) : ?>
						<input class="wbpo-checkbox" type="checkbox">
						<?php endif; ?>
						<span class="checkmark"></span>
					</label>
				</div>
			<?php
		}
		if ( 'radio' === $selection_type ) {
			?>
				<div class="wbpo-choose">
					<label>
						<?php if ( $wbpo_product->is_purchasable() && $wbpo_product->is_in_stock() ) : ?>
							<input class="wbpo-radio" type="radio" name="wbpo" >
							<span class="checkmark"></span>
						<?php endif ?>
				</label>
				</div>
			<?php
		}
		if ( 'on' === wbpo_get_setting( 'show_thumbnail', 'wbpo_setting', 'on' ) ) :
			?>
				<div class="wbpo-thumb-wrapper">
					<?php
					do_action( 'wbpo_before_product_thumb', $wbpo_product, $product );

					if ( 'no_action' !== $product_click_action ) :
						?>
					<a class="wbpo-product-link <?php echo 'popup' === $product_click_action ? 'wbpo-popup' : ''; ?>" data-id="<?php esc_attr_e( $group_item['id'] ); ?>" href="<?php echo esc_attr( get_permalink( $group_item['id'] ) ); ?>" <?php echo 'new_tab' === $product_click_action ? 'target="_blank"' : ''; ?>>
						<?php
					endif; // start link anhcor.
						$image_id  = $wbpo_product->get_image_id();
						$image_url = wp_get_attachment_image_url( $image_id, 'full' );
					?>
						<div class="wbpo-thumb" data-thumb='<?php echo esc_url( $image_url ); ?>'>
								<?php
								echo wp_kses(
									apply_filters( 'wbpo_product_thumbnail', $wbpo_product->get_image(), $wbpo_product ),
									[
										'img' => [
											'class' => [],
											'src'   => [],
											'id'    => [],
										],
									]
								);
								?>
						</div>
						<?php if ( 'no_action' !== $product_click_action ) : ?>
							</a>
							<?php
								endif; // End link if.
						do_action( 'wbpo_after_product_thumb', $wbpo_product, $product );
						?>
				</div><!-- /wbpo-thumb-wrapper -->
<?php	endif; // Show Thumbnail. ?>
		<div class="wbpo-product-content">
			<?php do_action( 'wbpo_before_product_name', $wbpo_product, $product ); ?>
			<div class="wbpo-title">
				<?php if ( 'no_action' !== $product_click_action ) : ?>
					<a class="wbpo-product-link <?php echo 'popup' === $product_click_action ? 'wbpo-popup' : ''; ?>" data-id="<?php esc_attr_e( $group_item['id'] ); ?>" href="<?php echo esc_attr( get_permalink( $group_item['id'] ) ); ?>" <?php echo 'new_tab' === $product_click_action ? 'target="_blank"' : ''; ?>>
					<?php
							endif; // start link anhcor.
					$product_name = apply_filters( 'spgb_product_name', $wbpo_product->get_name(), $wbpo_product );
				if ( $wbpo_product->is_in_stock() ) {
						echo esc_html( $product_name );
				} else {
						echo '<s>' . esc_html( $wbpo_product->get_name() ) . '</s>';
				}
				?>
				<?php if ( 'no_action' !== $product_click_action ) : ?>
					</a>
					<?php
							endif; // start link anhcor.
				?>
			</div><!--wbpo-title-->
			<?php do_action( 'wbpo_after_product_name', $wbpo_product, $product ); ?>
		
			<?php
				$price_visibility = apply_filters( 'wbpo_price_visibility', $price_visibility, $wbpo_product, $product );
				?>
					<div class="wbpo-price-wrapper">
						<?php if ( 'on' === $price_visibility ) {
							do_action( 'wbpo_before_product_price', $wbpo_product, $product ); ?>
							<div class="wbpo-price" data-price-html='<?php echo wp_kses( $wbpo_product->get_price_html(), wpbpo_allow_tags() ); ?>'>
								<?php echo wp_kses( $wbpo_product->get_price_html(), wpbpo_allow_tags() ) ?>
							</div>
						<?php } //End price visibility if.?>
						<?php if ( $group_item['tag'] ) : ?>
							<div class="wbpo-tag"><?php esc_html_e( $group_item['tag'] ); ?></div>
						<?php endif; 
						if ( $group_item['offer'] ) : ?>
							<div class="wbpo-offer"><?php esc_html_e( $group_item['offer'] ); ?></div>
						<?php 
						endif;
						if ( $group_item['below-offer'] ) : ?>
							<div class="wbpo-below-offer"><?php esc_html_e( $group_item['below-offer'] ); ?></div>
						<?php endif; ?>
						<?php do_action( 'wbpo_after_product_price', $wbpo_product, $product ); ?>
					</div><!-- wbpo-price-wrapper -->
				<?php
			$desc_visibility = apply_filters( 'wbpo_desc_visibility', $desc_visibility, $wbpo_product, $product );
			if ( 'on' === $desc_visibility ) {
				?>
				<div class="wbpo-description">
					<?php echo apply_filters( 'wbpo_product_description', $wbpo_product->get_short_description(), $wbpo_product, $product ); ?>
				</div><!-- wbpo-description -->
				<?php
			} //End visibility for description.

			if ( $group_item['above-variation'] ) :
				?>
				<div class="wbpo-above-variation"><?php esc_html_e( $group_item['above-variation'] ); ?></div>
				<?php
			endif;
			if ( $wbpo_product->is_type( 'variable' ) ) {
				$attributes           = $wbpo_product->get_variation_attributes();
				$available_variations = $wbpo_product->get_available_variations();
				$variations_json      = wp_json_encode( $available_variations );
				$variations_attr      = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
				if ( is_array( $attributes ) && ( count( $attributes ) > 0 ) ) {
					do_action( 'wbpo_before_product_variations', $wbpo_product, $product );
					?>
					<form class="variations_form" data-product_id="<?php esc_attr_e( $wbpo_product->get_id() ); ?>" data-product_variations="<?php esc_attr_e( $variations_attr ); ?>">
						<div class="variations">
						<?php foreach ( $attributes as $attribute_name => $options ) { ?>
							<div class="variation">
								<div class="label">
									<?php echo esc_html( wc_attribute_label( $attribute_name ) ); ?>
								</div>
								<div class="select">
									<?php
										$attr              = 'attribute_' . sanitize_title( $attribute_name );
										$selected          = isset( $_REQUEST[ $attr ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ $attr ] ) ) ) : $wbpo_product->get_variation_default_attribute( $attribute_name );
										$chose_option_text = apply_filters( 'wbpo_variation_select_lable', esc_html__( 'Choose', 'woo-bpo' ), $wbpo_product, $product );
										wc_dropdown_variation_attribute_options(
											[
												'options'  => $options,
												'attribute' => $attribute_name,
												'product'  => $wbpo_product,
												'selected' => $selected,
												'show_option_none' => $chose_option_text . ' ' . wc_attribute_label( $attribute_name ),
											]
										);
									?>
								</div>
							</div>
						<?php }//End variating forloop. ?>
						<div class="reset"><a class="reset_variations" href="#">' <?php esc_html__( 'Clear', 'woo-bpo' ); ?></a></div>
						</div>
					</form>
					<?php
					if ( 'on' === $desc_visibility ) {
						?>
							<div class="wbpo-variation-description"></div>
						<?php
					}//End variation desc.
					do_action( 'wbpo_after_product_variations', $wbpo_product, $product );
					echo '<div class="wbpo-availability"></div>';
				}//End checking attribute array.
			} else {
				?>
					<div class="wbpo-availability"><?php echo wc_get_stock_html( $wbpo_product ); ?></div>
				<?php
			}//End variation select if.
			?>
		</div><!--wbpo-product-content-->
		<?php
		if ( 'qty' === $selection_type ) :
			;
			if ( $wbpo_product->is_purchasable() && $wbpo_product->is_in_stock() ) :
				;

				$qty_args = [
					'input_value' => $item_qty,
					'min_value'   => $min,
					'max_value'   => $max,
					'input_name'  => 'wbpo_qty_' . $order,
				];

				if ( $wbpo_product->is_sold_individually() ) {
					$qty_args['max_value'] = 1;
				}
				?>
					<div class="wbpo-qty <?php echo 'off' === $qty_btn_visibility ? 'hide-qty-btn' : ''; ?>" data-min="<?php esc_attr_e( $min ); ?>" data-max="<?php esc_attr( $max ); ?>">
						<?php do_action( 'wbpo_before_product_qty', $wbpo_product, $product ); ?>
						<span class="wbpo-qty-mod wbpo-qty-minus">-</span>
						<?php echo woocommerce_quantity_input( $qty_args, $wbpo_product ); ?>
						<span class="wbpo-qty-mod wbpo-qty-plus">+</span>
						<?php do_action( 'wbpo_after_product_qty', $wbpo_product, $product ); ?>
					</div><!--wbpo-qty-->

				<?php
						endif;// Check Product in stock.
					endif;// End Quantity selector.
			do_action( 'wbpo_after_product', $wbpo_product, $product, $order );
		?>
	</div><!--wbpo product item-->
	<?php
	$order++;
endforeach; // product items foreach.
do_action( 'wbpo_after_product_list', $product );
?>
	<div class="wbpo-total"></div>
	<?php if ( ! empty( $wrap_after_text ) ) { ?>
	<div class="wbpo-after-text wbpo-wrap-text">
		<?php echo wp_kses( do_shortcode( stripslashes( $wrap_after_text ) ), wpbpo_allow_tags() ); ?>
	</div>
	<?php } ?>
</div> <!--wbpo-products-->
