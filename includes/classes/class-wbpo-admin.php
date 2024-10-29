<?php
/**
 * Admin Backend Logic.
 *
 * @package WbPo
 */
namespace WbPo\Classes;

use \WP_Query;

 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WbPo_Admin' ) ) :
	/**
	 *  Admin Back end Logic.
	 *
	 * @since 1.0.0
	 */
	class WbPo_Admin {

		/**
		 * Constructor call when object created.
		 */
		public function __construct() {

			// Remove chose option text.
			add_filter( 'woocommerce_dropdown_variation_attribute_options_args', [ $this, 'wc_remove_options_text' ] );

			// Product type and meta hooks.
			add_filter( 'product_type_selector', [ $this, 'wbpo_product_types_selector' ] );
			add_filter( 'woocommerce_product_data_tabs', [ $this, 'wbpo_product_data_tabs' ] );
			add_action( 'woocommerce_product_data_panels', [ $this, 'wbpo_data_panels_callback' ] );
			add_action( 'woocommerce_process_product_meta_wbpo', [ $this, 'save_meta_fields' ] );

			// Product page front end hooks.
			add_action( 'woocommerce_wbpo_add_to_cart', [ $this, 'add_to_cart_form' ] );
			add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'wbpo_group_input' ] );
			// Query Search Hooks.
			if ( 'on' === wbpo_get_setting( 'exact_search', 'wbpo_search' ) ) {
				add_action( 'pre_get_posts', [ $this, 'search_exact_query' ], 99 );
			}

			if ( 'on' === wbpo_get_setting( 'sentence_search', 'wbpo_search' ) ) {
				add_action( 'pre_get_posts', [ $this, 'search_sentence_query' ], 99 );
			}

			if ( 'on' === wbpo_get_setting( 'search_by_sku', 'wbpo_search' ) ) {
				add_action( 'pre_get_posts', [ $this, 'search_sku_query' ], 99 );
			}

			// Product search ajax call back.
			add_action( 'wp_ajax_wbpo_search_products', [ $this, 'wbpo_search_products' ] );



			// Add to cart.
			add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], 10, 2 );
			add_action( 'woocommerce_add_to_cart', [ $this, 'add_to_cart' ], 10, 6 );
			add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'get_cart_item_from_session' ], 10, 2 ); // Fazool

			// Cart contents instead of woocommerce_before_calculate_totals, prevent price error on mini-cart. FAzool
			add_filter( 'woocommerce_get_cart_contents', [ $this, 'get_cart_contents' ], 10, 1 );

			// Dashboard posts.
			add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );

			// Plugin action link in plugin page.
			add_filter( 'plugin_action_links', [ $this, 'action_links' ], 10, 2 );

			add_action( 'admin_footer', [ $this, 'wbpo_admin_js' ] );
			add_filter( 'woocommerce_get_price_html', [ $this, 'get_price_html' ], 99, 2 );
		}



		/**
		 * Selector for simple product.
		 *
		 * @since 1.0.0
		 *
		 * @param array $types products type.
		 * @param array $types products type.
		 */
		public function wbpo_product_types_selector( $types ):array {

			$types = array_slice( $types, 0, 2, true ) + [ 'wbpo' => esc_html__( 'Woo BPO', 'woo-bpo' ) ] + array_slice( $types, 2, count( $types ) - 1, true );

			return $types;
		}

		/**
		 * Group Product data tabs.
		 *
		 * @since 1.0.0
		 * @param array $tabs product tabs.
		 * @return array $tabs product tabs.
		 */
		public function wbpo_product_data_tabs( $tabs ):array {

			$tabs['wbpo'] = [
				'label'  => esc_html__( 'Woo BPO', 'woo-bpo' ),
				'target' => 'wbpo_settings',
				'class'  => [ 'show_if_wbpo' ],
			];

			return $tabs;
		}

		/**
		 * Callback for panel data tabs.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function wbpo_data_panels_callback() {
			global $post;
			$post_id        = $post->ID;
			$product_object = wc_get_product( $post->ID );
			$admin_object   = $this;
			if ( file_exists( WBPO_INC . 'admin/meta-boxes/views/html-product-data-wbpo.php' ) ) {
				require_once WBPO_INC . 'admin/meta-boxes/views/html-product-data-wbpo.php';
			}

		}

		/**
		 * Search Product Ajax callback.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function wbpo_search_products() {
			$keyword     = isset( $_POST['search_input'] ) ? sanitize_text_field( wp_unslash( $_POST['search_input'] ) ) : '';
			$ids         = isset( $_POST['current_group'] ) ? sanitize_text_field( wp_unslash( $_POST['current_group'] ) ): '';
			$exclude_ids = array();
			$items       = $this->formatIds( $ids );

			$resp = '';
			// if ( is_array( $items ) && count( $items ) > 2 ) {
			// $resp = '<ul><span>Please use the Premium Version to add more than 3 products to the grouped & get the premium support. Click <a href="h" target="_blank">here</a> to buy!</span></ul>';
			// wp_send_json_error( [ 'data' => $resp ] );
			// }

			if ( 'on' === wbpo_get_setting( 'search_by_id', 'wbpo_search' ) && is_numeric( $keyword ) ) {
				// search by id.
				$query_args = [
					'p'         => absint( $keyword ),
					'post_type' => 'product',
				];
			} else {
				$query_args = [
					'is_wbpo'        => true,
					'post_type'      => 'product',
					'post_status'    => [ 'publish', 'private' ],
					's'              => $keyword,
					'posts_per_page' => wbpo_get_setting( 'limit', 'wbpo_search', 5 ),
					'tax_query'      => [
						[
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => array( 'wbpo' ),
							'operator' => 'NOT IN',
						],
					],
				];

				if ( 'on' !== wbpo_get_setting( 'same_product', 'wbpo_search' ) ) {
					if ( is_array( $items ) && count( $items ) > 0 ) {
						foreach ( $items as $item ) {
							$exclude_ids[] = absint( isset( $item['id'] ) ? $item['id'] : 0 );
						}
					}

					$query_args['post__not_in'] = $exclude_ids;
				}
			}

			$query = new WP_Query( $query_args );
			$res   = '<ul>';
			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {
					$query->the_post();
					$wbpo_product = wc_get_product( get_the_ID() );

					if ( ! $wbpo_product || $wbpo_product->is_type( 'wbpo' ) ) {
						continue;
					}

					$res .= $this->wbpo_product_data_li( $wbpo_product, 0, true );

					if ( $wbpo_product->is_type( 'variable' ) ) {
						// show all children.
						$children = $wbpo_product->get_children();

						if ( is_array( $children ) && count( $children ) > 0 ) {
							foreach ( $children as $child ) {
								$child_product = wc_get_product( $child );
								$res          .= $this->wbpo_product_data_li( $child_product, 0, true );
							}
						}
					}
				}

				wp_reset_postdata();
			} else {
				$res .= '<span class="no-found">' . esc_html__( 'No results found for:', 'woo-bpo' ) . ' ' . esc_html( $keyword ) . '</span>';
			}
			$res .= '</ul>';
			wp_send_json_success( [ 'data' => $res ] );
		}

		/**
		 * Get Search Item Html List.
		 *
		 * @since 1.0.0
		 *
		 * @param object        $product product object.
		 * @param string|object $item_data  default quantity.
		 * @param boolean       $search is it in search.
		 * @return string Html of the item.
		 */
		public function wbpo_product_data_li( $product, $item_data, bool $search = false ):string {
			$product_id = $product->get_id();

			ob_start();
			?>
			<li class="<?php echo ( ! $product->is_in_stock() ? 'out-of-stock' : '' ); ?>"  data-id="<?php echo esc_attr( $product_id ); ?>">
				<div class="main-content">
					<span class="move"></span>
					 <span class="qty" aria-label="<?php echo esc_html__( 'Default quantity', 'woo-bpo' ); ?> "	>
						<input type="number" value=" <?php echo esc_attr( $item_data['qty'] ) ?>" min="0" step="1"/>
					</span>
					<span class="data">
						<span class="name"> <?php echo esc_html( $product->get_name() ); ?></span> 
						<span class="info"> <?php echo wp_kses( $product->get_price_html(), wpbpo_allow_tags() ); ?></span> 
						<?php if ( $product->is_sold_individually() ) : ?> 
							<span class="info"><?php echo __( 'Sold Individually', 'woo-bpo' ); ?></span> 
						<?php endif; ?>
						<?php if ( ! $product->is_in_stock() ) : ?> 
							<span class="info"><?php echo __( 'Out of Stock', 'woo-bpo' ); ?></span> 
						<?php endif; ?>
					</span>
					<span class="type"><a href=" <?php echo esc_url( get_edit_post_link( $product_id ) ); ?> " target="_blank"> <?php echo esc_attr( $product->get_type() ); ?><br/>#<?php echo esc_attr( $product_id ); ?></a></span> 
					<span class="more-info close"><?php _e( 'Offer Details', 'wbpo' )?> </span>
					<?php
							if ( $search ) {
								echo  '<span class="add hint--left" aria-label="' . esc_html__( 'Add', 'woo-bpo' ) . '">+</span>';
							}	 else {
								echo '<span class="remove hint--left" aria-label="' . esc_html__( 'Remove', 'woo-bpo' ) . '">×</span>';
							}
					?>
				</div>
				<div class="hidden wbpo-more-detail">
					<?php
						woocommerce_wp_text_input(
							[
								'id'          => 'wbpo_tag_text',
								'value'       => $item_data['tag'] ?? '',
								'label'       => esc_html__( 'Price Tag Text', 'woo-bpo' ),
								'desc_tip'    => true,
								'description' => __( 'It will appear next to offer price', 'woo-bpo' ),
								'placeholder' => __( 'ex. Sale Price', 'woo-bpo' ),
							]
						);
						woocommerce_wp_text_input(
							[
								'id'          => 'wbpo_offer_text',
								'value'       => $item_data['offer'] ?? '',
								'label'       => __( 'Popup  Text', 'woo-bpo' ),
								'placeholder' => __( 'ex. Most Popular', 'woo-bpo' ),
							]
						);
						woocommerce_wp_text_input(
							[
								'id'          => 'wbpo_below_offer_text',
								'value'       => $item_data['below-offer'] ?? '',
								'label'       => esc_html__( 'Offer Incentive', 'woo-bpo' ),
								'placeholder' => esc_html__( 'ex. Save 40% ', 'woo-bpo' ),
								'desc_tip'    => true,
								'description' => __( 'It will appear below popup text', 'woo-bpo' ),
							]
						);
						woocommerce_wp_text_input(
							[
								'id'          => 'wbpo_above_variation_text',
								'value'       => $item_data['above-variation'] ?? '',
								'label'       => esc_html__( 'Select Options Text ', 'woo-bpo' ),
								'placeholder' => esc_html__( 'ex. Choose color ', 'woo-bpo' ),
								'desc_tip'    => true,
								'description' => __(
									'We suggest to use for variation product but you can input anything you want
								',
									'woo-bpo'
								),
							]
						);
					?>
				</div>
			</li>
			<?php
			return ob_get_clean();
		}


		/**
		 * Save Product fields meta data.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function save_meta_fields( $product_id ) {

			$keys = [
				'wbpo_current_group' => [
					'sanitize_call_back' => 'sanitize_text_field',
				],
				'wbpo_offer_position' => [
					'sanitize_call_back' => 'sanitize_text_field',
				],
				'wbpo_selection_type' => [
					'sanitize_call_back' => 'sanitize_text_field',
				],
				'wbpo_main_product_in_cart' => [
					'sanitize_call_back' => 'sanitize_text_field',
				],
				'wbpo_offer_design' => [
					'sanitize_call_back' => 'sanitize_text_field',
				],
				'wbpo_offer_layout' => [
					'sanitize_call_back' => 'sanitize_text_field',
				],
				'wbpo_qty_box' => [
					'sanitize_call_back' => 'sanitize_text_field',
				],
				'wbpo_price_text' => [
					'sanitize_call_back' => 'wp_kses_post',
				],
				'wbpo_atc_text' => [
					'sanitize_call_back' => 'wp_kses_post',
				],
				'wbpo_text_above' => [
					'sanitize_call_back' => 'wp_kses_post',
				],
				'wbpo_text_below' => [
					'sanitize_call_back' => 'wp_kses_post',
				],
				'wbpo_checklist' => [
					'sanitize_call_back' => 'wp_kses_post',
				],
				'wbpo_product_title' => [
					'sanitize_call_back' => 'wp_kses_post',
				],
			];

			foreach ( $keys as $key => $_data ) {
				if ( isset( $_POST[ $key ] ) ) {
					$_value = function_exists($_data['sanitize_call_back'] ) ? call_user_func( $_data['sanitize_call_back'], $_POST[ $key ] ) : sanitize_text_field( $_POST[ $key ] );
						update_post_meta( $product_id, $key, $_value ); // phpcs:ignore
				}
			}

			if ( ! isset( $_POST['wbpo_qty_box'] ) ) {
				update_post_meta( $product_id, 'wbpo_qty_box', '' ); // phpcs:ignore
			}

			if ( ! isset( $_POST['wbpo_main_product_in_cart'] ) ) {
				update_post_meta( $product_id, 'wbpo_main_product_in_cart', '' ); // phpcs:ignore
			}

		}

		/**
		 * Format raw json of group products.
		 *
		 * @since 1.0.0
		 *
		 * @param string  $data
		 * @param boolean $only_ids
		 * @return array
		 */
		public function formatIds( $data, bool $only_ids = false ) {

			$products = json_decode( wp_unslash( $data ), true );
			$items    = [];
			if ( json_last_error() === JSON_ERROR_NONE ) {

				foreach ( $products as $item ) {

					if ( $only_ids ) {

						$items[] = $item['id'];
					} else {
						$items[] = [
							'id'              => $item['id'],
							'qty'             => $item['qty'],
							'tag'             => $item['tag'] ?? '',
							'offer'           => $item['offer'] ?? '',
							'below-offer'     => $item['belowOffer'] ?? '',
							'above-variation' => $item['aboveVariation'] ?? '',
						];
					}
				}
			}

			return $items;

		}

		public function add_to_cart_form() {
			global $product;
			if ( $product->has_variables() ) {
				wp_enqueue_script( 'wc-add-to-cart-variation' );
			}
			$admin_object = $this;
			if ( file_exists( WBPO_INC . 'public/templates/html-add-to-cart.php' ) ) {
				require_once WBPO_INC . 'public/templates/html-add-to-cart.php';
			}

		}

		/**
		 * Show on Frontend of product list.
		 *
		 * @since 1.0.0
		 * @param object $product
		 * @return void
		 */
		public function get_group_products( $product = '' ) {
			if ( ! $product ) {
				global $product;

				if ( file_exists( WBPO_INC . 'public/templates/html-group-product.php' ) ) {
					require_once WBPO_INC . 'public/templates/html-group-product.php';
				}
			}
		}

		/**
		 * Query Match exact word query string.
		 *
		 * @since 1.0.0
		 * @param object $query
		 * @return void
		 */
		public function search_exact_query( $query ) {
			if ( $query->is_search && isset( $query->query['is_wbpo'] ) ) {
				$query->set( 'exact', true );
			}
		}

		/**
		 * Query Match exact  sentence query string.
		 *
		 * @since 1.0.0
		 * @param object $query
		 * @return void
		 */
		public function search_sentence_query( $query ) {
			if ( $query->is_search && isset( $query->query['is_wbpo'] ) ) {
				$query->set( 'sentence', true );
			}
		}

		/**
		 * Query Search with Product SKU string.
		 *
		 * @since 1.0.0
		 * @param object $query
		 * @return void
		 */
		public function search_sku_query( $query ) {
			if ( $query->is_search && isset( $query->query['is_wbpo'] ) ) {
				global $wpdb;
				$sku = $query->query['s'];
				$ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value = %s;", $sku ) );

				if ( ! $ids ) {
					return;
				}

				unset( $query->query['s'], $query->query_vars['s'] );
				$query->query['post__in'] = [];

				foreach ( $ids as $id ) {
					$post = get_post( $id );

					if ( 'product_variation' === $post->post_type ) {
						$query->query['post__in'][]      = $post->post_parent;
						$query->query_vars['post__in'][] = $post->post_parent;
					} else {
						$query->query_vars['post__in'][] = $post->ID;
					}
				}
			}
		}

		/**
		 * Add Group Product in tab.
		 *
		 * @param array $tabs
		 * @return array $tabs
		 */
		public function wbpo_product_tabs( $tabs ) {
			global $product;
			if ( $product->is_type( 'wbpo' ) ) {
				$tabs['wbpo'] = [
					'title'    => esc_html__( 'Woo BPO', 'woo-bpo' ),
					'priority' => 51,
					'callback' => [ $this, 'group_products_tab' ],
				];
			}

			return $tabs;
		}

		/**
		 * Group Product callback.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function group_products_tab() {
			$this->get_group_products();
		}

		/**
		 * Add input to collect products data.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function wbpo_group_input() {

			global $product;
			if ( $product->is_type( 'wbpo' ) ) {
				echo '<input name="wbpo_current_group" class="wbpo-current-group" type="hidden" value="' . esc_attr( get_post_meta( $product->get_id(), 'wbpo_current_group', true ) ) . '">';
			}
		}


		/**
		 * Add group in cart item data.
		 *
		 * @since 1.0.0
		 * @param array $cart_item_data current session cart item data.
		 * @param int   $product_id product item.
		 *
		 * @return array $current_item_data.
		 */
		public function add_cart_item_data( $cart_item_data, $product_id ) {
			$wbpo_product = wc_get_product( $product_id );

			if ( $wbpo_product && $wbpo_product->is_type( 'wbpo' ) && ( get_post_meta( $product_id, 'wbpo_current_group', true ) ) ) {
				// make sure that is grouped
				if ( isset( $_POST['wbpo_current_group'] ) ) {
					$current_group = wp_unslash( ( $_POST['wbpo_current_group'] ) ); // JSON string to remove unslash. We are not displaying it directly in the content.
				}

				$current_group = $this->formatIds( $current_group );
				if ( ! empty( $current_group ) ) {
					$cart_item_data['wbpo_current_group'] = $current_group;
				}
			}

			return $cart_item_data;
		}

		/**
		 * Add to cart group products.
		 *
		 * @since 1.0.0
		 * @param string $cart_item_key cart item key.
		 * @param int    $product_id product id.
		 * @param int    $quantity quantity.
		 * @param int    $variation_id variation id.
		 * @param int    $variation variation.
		 * @param array  $cart_item_data cart data.
		 *
		 * @return void
		 */
		public function add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

			if ( ! empty( $cart_item_data['wbpo_current_group'] ) ) {

				$items = $cart_item_data['wbpo_current_group'];
				foreach ( $items as $item ) {
					$item_id      = $item['id'];
					$item_qty     = $item['qty'] ?? 1;
					$item_product = wc_get_product( $item_id );

					if ( ! $item_product || ( $item_qty <= 0 ) ) {
						continue;
					}
					$item_variation_id = 0;
					$item_variation    = [];

					if ( $item_product instanceof WC_Product_Variation ) {
						// ensure we don't add a variation to the cart directly by variation ID.
						$item_variation_id = $item_id;
						$item_id           = $item_product->get_parent_id();
						$item_variation    = $item_product->get_variation_attributes();
					}

					// add to cart.
					$product_qty = $item_qty * $quantity;
					$item_key    = WC()->cart->add_to_cart( $item_id, $product_qty, $item_variation_id, $item_variation );

					if ( $item_key ) {
						WC()->cart->cart_contents[ $cart_item_key ]['wbpo_keys'][] = $item_key;
					}
				} // end foreach
				$product = wc_get_product( $product_id);

				// remove grouped.
				$add_main_product_in_cart = 'on' !== wbpo_get_setting( 'main_product_in_cart', 'wbpo_setting' ) ? false : true;
				if('yes' === $product->get_meta( 'wbpo_main_product_in_cart' )){
					$add_main_product_in_cart = true;
				}

				if ( ! $add_main_product_in_cart ) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}
		}

		/**
		 * Check and set current group in woo session.
		 *
		 * @since 1.0.0
		 * @param string $cart_item item key.
		 * @param string $item_session_values item session data.
		 *
		 * @return array $item_session_values item session data.
		 */
		public function get_cart_item_from_session( $cart_item, $item_session_values ) {
			if ( isset( $item_session_values['wbpo_current_group'] ) && ! empty( $item_session_values['wbpo_current_group'] ) ) {
				$cart_item['wbpo_current_group'] = $item_session_values['wbpo_current_group'];
			}

			return $cart_item;
		}

		/**
		 * Remove custom cart items before price calculation to prevent error.
		 *
		 * @since 1.0.0
		 * @param array $cart_contents cart items.
		 * @return array $cart_contents cart items.
		 */
		public function get_cart_contents( $cart_contents ) {
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				// if ( ! empty( $cart_item['wbpo_current_group'] ) ) {
					// $cart_item['data']->set_price( 0 ); @NOTE: Gerry Want's to add product with original price { @sharaz 26-10-21}
				// }

				if ( ! empty( $cart_item['wbpo_keys'] ) ) {
					$has_key = false;

					foreach ( $cart_item['wbpo_keys'] as $key ) {
						if ( isset( $cart_contents[ $key ] ) ) {
							$has_key = true;
						}
					}

					if ( ! $has_key ) {
						WC()->cart->remove_cart_item( $cart_item_key );
						unset( $cart_contents[ $cart_item_key ] );
					}
				}
			}

			return $cart_contents;
		}


		/**
		 * Add simple group status in product dashboard.
		 *
		 * @param array  $states status string.
		 * @param object $post post object.
		 * @return array $states status string.
		 */
		public function display_post_states( $states, $post ) {
			if ( 'product' == get_post_type( $post->ID ) ) {
				if ( ( $product = wc_get_product( $post->ID ) ) && $product->is_type( 'wbpo' ) ) {
					$count = 0;

					$current_group = get_post_meta( $post->ID, 'wbpo_current_group', true );
					if ( ! empty( $current_group ) ) {
						if ( $items = $this->formatIds( $current_group ) ) {
							$count = count( $items );
						}
					}

					$states[] = apply_filters( 'woosg_post_states', '<span class="wbpo-state">' . sprintf( esc_html__( 'Woo BPO (%s)', 'woo-bpo' ), $count ) . '</span>', $count, $product );
				}
			}

			return $states;
		}

		/**
		 * Modify plugin action link.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $links  actin links.
		 * @param string $file plugin path.
		 *
		 * @return array $links actin links.
		 */
		public function action_links( $links, $file ) {
			static $plugin;

			if ( ! isset( $plugin ) ) {
				$plugin = plugin_basename( __FILE__ );
			}

			if ( $plugin === $file ) {
				$settings         = '<a href="' . admin_url( 'admin.php?page=wbpo' ) . '">' . esc_html__( 'Settings', 'woo-bpo' ) . '</a>';
				$links['premium'] = '';
				array_unshift( $links, $settings );
			}

			return (array) $links;
		}

		/**
		 * Admin footer for  product post eidt.
		 *
		 * @return void
		 */
		public function wbpo_admin_js() {
			if ( 'product' != get_post_type() ) :
					return;
			endif;
			?>
			<script type='text/javascript'>
					jQuery( document ).ready( function() {
							jQuery( '.options_group.pricing' ).addClass( 'show_if_wbpo' ).show();
							jQuery( '.general_options' ).show();
					});
			</script>
			<?php
		}


		/**
		 * Filter the price html.
		 *
		 * @since 1.0.0
		 *
		 * @param string $price price html.
		 * @param object $product current product object.
		 * @return string $price or $custom_price
		 */
		public function get_price_html( $price, $product ) {

			if ( $product->is_type( 'wbpo' ) ) {
				$custom_price = get_post_meta( $product->get_id(), 'wbpo_price_text', true );
				if ( ! empty( $custom_price ) ) {
					return do_shortcode( stripslashes( $custom_price ) );
				}
			}

			return $price;
		}

		function wc_remove_options_text( $args ) {
			$args['show_option_none'] = '';
			return $args;
		}
	} // end class.

endif;
