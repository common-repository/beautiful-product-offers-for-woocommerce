<?php
/**
 * Simple Group Product.
 *
 * @package WbPo
 */

 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WC_Product_WbPo' ) ) :

	/**
	 *  Simple Group Product Class.
	 *
	 * @since 1.0.0
	 */
	class WC_Product_WbPo extends WC_Product {

		/**
		 * Constructor call when object created.
		 */
		public function __construct( $product ) {
			$this->product_type = 'wbpo';
			parent::__construct( $product );

		}

		/**
		 * Check is variable product added in group.
		 *
		 * @since 1.0.0
		 * @return boolean
		 */
		public function has_variables():bool {
			if ( ! empty( $this->get_items() ) ) {
				$items = $this->get_items();
				foreach ( $items as $item ) {
					$item_product = wc_get_product( $item['id'] );

					if ( $item_product && $item_product->is_type( 'variable' ) ) {
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Get current group items
		 *
		 * @since 1.0.0
		 *
		 * @param boolean $only_ids is you want to get only ids.
		 * @return array
		 */
		public function get_items( bool $only_ids = false ):array {
			$product_id = $this->id;
			$products   = json_decode( $this->get_meta( 'wbpo_current_group', $product_id ), true );
			$items      = [];
			if ( json_last_error() === JSON_ERROR_NONE ) {

				foreach ( $products as $item ) {

					if ( $only_ids ) {

						$items[] = $item['id'];
					} else {
						$items[] = [
							'id'  => $item['id'],
							'qty' => $item['qty'],
						];
					}
				}
			}
			return $items;
		}

		/**
		 * Single Product Add to cart text.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function single_add_to_cart_text() {
	
			$btn_text =  $this->get_meta( 'wbpo_atc_text', $this->id );

			if ( empty( $btn_text ) ) {
				$btn_text = esc_html__( 'Add to cart', 'woo-bpo' );
			}

			return apply_filters( 'wbpo_product_single_add_to_cart_text', $btn_text, $this );
		}

		/**
		 * Archive loop add to cart text.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function add_to_cart_text() {
			if ( $this->is_purchasable() && $this->is_in_stock() ) {
				$btn_text = wbpo_get_setting( 'archive_purchasable_text', 'wbpo_labels' );
				if ( empty( $btn_text ) ) {
					$btn_text = esc_html__( 'Select options', 'woo-bpo' );
				}
			} else {
				$btn_text = wbpo_get_setting( 'archive_unpurchasable_text', 'wbpo_labels' );
				if ( empty( $btn_text ) ) {
					$btn_text = esc_html__( 'Read more', 'woo-bpo' );
				}
			}

			return apply_filters( 'wbpo_product_add_to_cart_text', $btn_text, $this );
		}
	} // end class.


endif;
