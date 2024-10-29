<?php
/**
 * Admin plugin functionality.
 *
 * @package WbPo
 */

namespace WbPo\Classes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WbPo_Settings' ) ) :
	/**
	 * Admin class for admin functionality.
	 *
	 * @since 1.0.0
	 */
	class WbPo_Settings {

		/**
		 * Setting api object.
		 *
		 * @since 1.0.0
		 * @access public
		 * @var object
		 */
		public $setting_api;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			// $this->includes();
			$this->setting_api = new WbPo_Setting_API();
			add_action( 'admin_init', [ $this, 'admin_init' ] );
			add_action( 'admin_menu', [ $this, 'admin_menu' ] );
			add_action( 'wbpo_form_top_wbpo_customize', [ $this, 'pro_notice' ] );
		}

		/**
		 * Include assets.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function includes() {

		}

		/**
		 * Add menu to the plugin.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function admin_menu() {

			add_menu_page(
				__( 'Woo BPO ', 'woo-bpo' ),
				__( 'Woo BPO ', 'woo-bpo' ),
				'manage_options',
				'wbpo',
				[ $this->setting_api, 'plugin_page' ],
				'dashicons-screenoptions',
				25 
			);

			add_submenu_page (
				'wbpo',
				__( 'Beautiful Product offer ', 'woo-bpo' ),
				__( 'Settings', 'woo-bpo' ),
				'manage_options',
				'wbpo',
				[ $this->setting_api, 'plugin_page' ],
			);
			do_action( 'wbpo_sub_menu_pages' );
		}

		/**
		 * Section and setting api.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function admin_init() {

			// Set setting sections.
			$this->setting_api->set_sections( $this->get_sections() );
			// Set setting fields.
			$this->setting_api->set_fields( $this->get_fields() );
			// Setting api init.
			$this->setting_api->admin_init();

		}


		/**
		 * Get setting section.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function get_sections():array {
			$styling_tab_title = '';
			if ( wbpo_is_pro() ) {
				$styling_tab_title = __( 'Styling', 'woo-bpo' );
			} else {
				$styling_tab_title  = sprintf( '%1$s <span class="wbpo-pro-text">(%2$s)</span>', __( 'Styling', 'woo-bpo' ), 'PRO' );
			}

			$sections = [
				[
					'id'    => 'wbpo_setting',
					'title' => __( 'Settings', 'woo-bpo' ),
				],
				[
					'id'    => 'wbpo_labels',
					'title' => __( 'Archive Labels', 'woo-bpo' ),
				],
				[
					'id'    => 'wbpo_search',
					'title' => __( 'Search', 'woo-bpo' ),
				],
				[
					'id'    => 'wbpo_customize',
					'title' => $styling_tab_title,
				]
			];
			return apply_filters( 'wbpo_setting_sections', $sections );
		}




		/**
		 * Setting fields.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function get_fields():array {

			$wbpo_trust_label = '';

			if ( wbpo_is_pro() ) {
				$wbpo_trust_label = __( 'Upload Trust Badge', 'woo-bpo' );
			} else {
				$wbpo_trust_label  = sprintf( '%1$s %2$s ', __( 'Upload Trust Badge', 'woo-bpo' ), wbpo_is_pro_text() );
			}

			$setting_fields = [
				'wbpo_setting'   => [
					[
						'id'      => 'position',
						'type'    => 'select',
						'name'    => __( 'Offer Position', 'woo-bpo' ),
						'desc'    => __( 'Under ATC suggested use case: Upsells', 'woo-bpo' ),
						'default' => 'above_atc',
						'options' => [
							'above_atc' => __( 'Above Add to Cart Button', 'woo-bpo' ),
							'below_atc' => __( 'Under Add to Cart Button', 'woo-bpo' ),
						],
					],
					[
						'id'      => 'selection_type',
						'type'    => 'select',
						'name'    => __( 'Offer Type', 'woo-bpo' ),
						'default' => 'checkbox',
						'options' => [	
							// 'qty'      => __( 'Quantity', 'woo-bpo' ), //TODO Remove totally functionally code {SS 16-10-21 }
							'checkbox' => __( 'Multiple Selections', 'woo-bpo' ),
							'radio'    => __( 'Single Selection', 'woo-bpo' ),
						],
					],
					[
						'id'      => 'product_item_click',
						'type'    => 'select',
						'name'    => __( 'Product Title On-Click Action', 'woo-bpo' ),
						'default' => 'new_tab',
						'options' => [
							'no_action' => __( 'No Action', 'woo-bpo' ),
							'new_tab'   => __( 'Open product in new Tab', 'woo-bpo' ),
							'same_tab'  => __( 'Open product in same Tab', 'woo-bpo' ),
						],
					],
					[
						'id'      => 'show_thumbnail',
						'name'    => __( 'Show Thumbnail', 'woo-bpo' ),
						'type'    => 'checkbox',
						'desc'    => __( 'Thumbnail of Pre-determined product in Offer.', 'woo-bpo' ),
						'default' => 'on',
					],
					[
						'id'      => 'variation_change_image',
						'name'    => __( 'Variation Change Image', 'woo-bpo' ),
						'type'    => 'checkbox',
						'default' => 'on',
					],
					[
						'id'      => 'show_desc',
						'name'    => __( 'Show Short Description', 'woo-bpo' ),
						'type'    => 'checkbox',
						'default' => 'off',
					],
					[
						'id'      => 'show_price',
						'name'    => __( 'Show Price', 'woo-bpo' ),
						'type'    => 'checkbox',
						'default' => 'on',
					],
					// [
					// 	'id'      => 'show_qty_modify',
					// 	'name'    => __( 'Show Quantity Box', 'woo-bpo' ),
					// 	'desc'    => __( '<b>Note: OnlyWork with Quantity type selection </b>Whether you want to show the quantity modification controls.', 'woo-bpo' ),
					// 	'type'    => 'checkbox',
					// 	'default' => 'on',
					// ],
					[
						'id'      => 'variation_change_price',
						'name'    => __( 'Price Change', 'woo-bpo' ),
						'desc'    => __( 'When variation or quantity update change main product price.', 'woo-bpo' ),
						'type'    => 'checkbox',
						'default' => 'on',
					],
					[
						'id'          => 'price_selector',
						'name'        => __( 'Custom Price Selector', 'woo-bpo' ),
						'value'       => '',
						'placeholder' => 'selector > .price',
						'desc'        => __( 'Woo BPO lets you replace the content of the price section. This works for themes that have the default woocommerce ".price" selector. If your theme selector is different, please enter it above.', 'woo-bpo' ),
						'type'        => 'text',
					],
					[
						'id'   => 'main_product_in_cart',
						'name' => __( 'Add Main Product in Cart', 'woo-bpo' ),
						'desc'        => __( 'Suggested use cases: Product Kits, Pick and Mix Bundles, Bundle Sells and Upsells.', 'woo-bpo' ),
						'type' => 'checkbox',
					],
					[
						'id'         => 'trust_badge',
						'name'       => $wbpo_trust_label,
						'type'       => 'file',
						'default'    => '',
						'options'    => [
							'button_label'  => 'Choose Image'
						]
					],
				],
				'wbpo_labels'    => [
					// [
					// 	'id'          => 'total_text',
					// 	'name'        => __( 'Total Text', 'woo-bpo' ),
					// 	'value'       => '',
					// 	'placeholder' => 'Total',
					// 	'type'        => 'text',
					// ],
					[
						'id'          => 'archive_purchasable_text',
						'name'        => __( 'Archive label for purchaseable Offer', 'woo-bpo' ),
						'value'       => '',
						'placeholder' => 'Select Option',
						'type'        => 'text',
					],
					[
						'id'          => 'archive_unpurchasable_text',
						'name'        => __( 'Archive label for not purchaseable Offer', 'woo-bpo' ),
						'value'       => '',
						'placeholder' => 'Read More',
						'type'        => 'text',
					],
				],
				'wbpo_search'    => [
					[
						'id'          => 'limit',
						'name'        => __( 'Search limit', 'woo-bpo' ),
						'value'       => '',
						'placeholder' => '5',
						'type'        => 'number',
						'min'         => 1,
					],
					[
						'id'   => 'search_by_id',
						'name' => __( 'Search by ID', 'woo-bpo' ),
						'desc' => __( 'Only when you add numeric value.', 'woo-bpo' ),
						'type' => 'checkbox',
					],
					[
						'id'   => 'search_by_sku',
						'name' => __( 'Search by SKU', 'woo-bpo' ),
						'type' => 'checkbox',
					],
					[
						'id'   => 'same_product',
						'name' => __( 'Add Same Product', 'woo-bpo' ),
						'desc' => __( 'Add already exist product in list.', 'woo-bpo' ),
						'type' => 'checkbox',
					],
					[
						'id'   => 'exact_search',
						'name' => __( 'Exact Search', 'woo-bpo' ),
						'desc' => __( 'exactly word match with product title or content?', 'woo-bpo' ),
						'type' => 'checkbox',
					],
					[
						'id'   => 'sentence_search',
						'name' => __( 'Sentence Search', 'woo-bpo' ),
						'desc' => __( 'Do a full phrase search not words?', 'woo-bpo' ),
						'type' => 'checkbox',
					],

				],
				'wbpo_customize' => [
					[
						'id'      => 'design',
						'type'    => 'select',
						'name'    => __( 'Offer Design', 'woo-bpo' ),
						'default' => 'simple',
						'options' => [
							'simple' => __( 'Basic', 'woo-bpo' ),
							'classic rounded'    => __( 'Rounded', 'woo-bpo' ),
							'classic rectangle'    => __( 'Rectangle', 'woo-bpo' ),
						],
					],
					[
						'id'      => 'layout',
						'type'    => 'select',
						'name'    => __( 'Offer Layout', 'woo-bpo' ),
						'default' => 'layout-horizontal',
						'options' => [
							'layout-vertical'   => __( 'Vertical', 'woo-bpo' ),
							'layout-horizontal' => __( 'Horizontal', 'woo-bpo' ),
						],
					],
					[
						'id'      => 'font_weight',
						'type'    => 'select',
						'name'    => __( 'Font Weight', 'woo-bpo' ),
						'default' => '400',
						'options' => [
							'400'    => __( 'Regular', 'woo-bpo' ),
							'600'    => __( 'Medium', 'woo-bpo' ),
							'700'    => __( 'Bold', 'woo-bpo' ),
						],
					],
					[
						'id'      => 'border_color',
						'type'    => 'color',
						'name'    => __( 'Border Color', 'woo-bpo' ),
					],
					[
						'id'      => 'box_shadow_color',
						'type'    => 'color',
						'name'    => __( 'Box Shadow Color', 'woo-bpo' ),
					],
					[
						'id'      => 'background_color',
						'type'    => 'color',
						'name'    => __( 'Offer Background Color', 'woo-bpo' ),
					],
					[
						'id'      => 'product_check_bg_color',
						'type'    => 'color',
						'name'    => __( 'Offer Hover | Active Background Color', 'woo-bpo' ),
					],
					// [
					// 	'id'      => 'product_hover_color',
					// 	'type'    => 'color',
					// 	'name'    => __( 'Product List Hover Background Color', 'woo-bpo' ),
					// ],
					[
						'id'      => 'text_color',
						'type'    => 'color',
						'name'    => __( 'Short Description & Offer details Text Color', 'woo-bpo' ),
					],
					[
						'id'      => 'text_hover_color',
						'type'    => 'color',
						'name'    => __( 'Short Description & Offer details Text Hover | Active Color', 'woo-bpo' ),
					],
					[
						'id'      => 'title_color',
						'type'    => 'color',
						'name'    => __( 'Product Title Color', 'woo-bpo' ),
					],
					[
						'id'      => 'title_hover_color',
						'type'    => 'color',
						'name'    => __( 'Product Title Hover | Active Color', 'woo-bpo' ),
					],
					/* [
						'id'      => 'total_color',
						'type'    => 'color',
						'name'    => __( 'Total Text Color', 'woo-bpo' ),
					], */
					[
						'id'      => 'price_color',
						'type'    => 'color',
						'name'    => __( 'Regular Price Color', 'woo-bpo' ),
					],
					[
						'id'      => 'price_hover_color',
						'type'    => 'color',
						'name'    => __( 'Regular Price Hover | Active Color', 'woo-bpo' ),
					],
					[
						'id'      => 'sale_price_color',
						'type'    => 'color',
						'name'    => __( 'Sale Price Color', 'woo-bpo' ),
					],
					[
						'id'      => 'sale_price_hover_color',
						'type'    => 'color',
						'name'    => __( 'Sale Price Hover | Active Color', 'woo-bpo' ),
					],
					[
						'id'      => 'var_select_color',
						'type'    => 'color',
						'name'    => __( 'Variation Select Color', 'woo-bpo' ),
					],
					[
						'id'      => 'tag_color',
						'type'    => 'color',
						'name'    => __( 'Tag Text Color', 'woo-bpo' ),
					],
					[
						'id'      => 'tag_background_color',
						'type'    => 'color',
						'name'    => __( 'Tag Background Color', 'woo-bpo' ),
					],
					[
						'id'      => 'offer_color',
						'type'    => 'color',
						'name'    => __( 'Popup Text Color', 'woo-bpo' ),
					],
					[
						'id'      => 'atc_color',
						'type'    => 'color',
						'name'    => __( 'Add To Cart Text Color', 'woo-bpo' ),
					],
					[
						'id'      => 'atc_hover_color',
						'type'    => 'color',
						'name'    => __( 'Add To Cart Hover Text Color', 'woo-bpo' ),
					],
					[
						'id'      => 'atc_background_color',
						'type'    => 'color',
						'name'    => __( 'Add To Cart Background Color', 'woo-bpo' ),
					],
					[
						'id'      => 'atc_hover_background_color',
						'type'    => 'color',
						'name'    => __( 'Add To Cart Hover Background Color', 'woo-bpo' ),
					]
				],
			];

			return apply_filters( 'wbpo_setting_fields', $setting_fields );
		}

		/**
		 * Pro Notice in plugin settings.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function pro_notice(){
			if( wbpo_is_pro() ){
				return ;
			}
			echo "<div class='wbpo-pro-notice'><div class='wbpo-pro-notice-inner'>Changes applied here will not show up on your live shop. <br/>Please <b>Upgrade to Pro </b> to unlock all styling features. <a target='blank' href='" . WBPO_PRO_URL . "' class='wbpo-pro-btn'> Upgrade</a></div></div>";
		}

	}//end class

endif;
// global $wbpo_settings;
// $wbpo_settings = new \WbPo_Settings();
