<?php
/**
 * Plugin Name:       Beautiful Product Offers for Woocommerce
 * Plugin URI:
 * Description:       The first Woocommerce plugin that allows you to fully customize the appearance of your product offers and increase your Average Order Value. You can create pre-determined product bundles or allow your clients to build kits. Additionally you can customize your product title with HTML and shortcodes, you can upload trust badge and add custom HTML content above and under the Add To Cart Button.
 * Version:           1.0.4
 * Requires at least: 4.9
 * Requires PHP:      7.2
 * Author:            GnKapps
 * Author URI:        https://gnkapps.com/woocommerce-beautiful-product-offers
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woo-bpo
 * Domain Path:       /languages
 *
 * @package           WbPo
 */

// Useful global constants.
define( 'WBPO_VERSION', '1.0.4' );
define( 'WBPO_URL', plugin_dir_url( __FILE__ ) );
define( 'WBPO_PATH', plugin_dir_path( __FILE__ ) );
define( 'WBPO_INC', WBPO_PATH . 'includes/' );
define( 'WBPO_PRO_URL', 'https://gnkapps.com/woocommerce-beautiful-product-offers/pricing/' );

// Require Composer autoloader if it exists.
if ( file_exists( WBPO_PATH . 'vendor/autoload.php' ) ) {
	require_once WBPO_PATH . 'vendor/autoload.php';
}

// Include files.
require_once WBPO_INC . '/core.php';

// Activation/Deactivation.
register_activation_hook( __FILE__, '\WbPo\Core\activate' );
register_deactivation_hook( __FILE__, '\WbPo\Core\deactivate' );

// Bootstrap.
WbPo\Core\setup();
