<?php
/**
 * Core plugin functionality.
 *
 * @package TenUpPlugin
 */

namespace WbPo\Core;

use WbPo\Classes;
use \WP_Error;

if ( file_exists( WBPO_INC . 'utils/helpers.php' ) ) {
	require_once WBPO_INC . 'utils/helpers.php';
}

global $wbpo_init;
$wbpo_init = new \WbPo\Classes\WbPo_Init();
$wbpo_init::register_services();
/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};
	add_action( 'init', $n( 'i18n' ) );
	add_action( 'init', $n( 'init' ) );
	add_action( 'plugins_loaded', $n( 'wbpo_register_product_type' ) );
	add_action( 'wp_enqueue_scripts', $n( 'scripts' ) );
	add_action( 'wp_enqueue_scripts', $n( 'styles' ) );
	add_action( 'admin_enqueue_scripts', $n( 'admin_scripts' ) );
	add_action( 'admin_enqueue_scripts', $n( 'admin_styles' ) );

	// Editor styles. add_editor_style() doesn't work outside of a theme.
	add_filter( 'mce_css', $n( 'mce_css' ) );
	// Hook to allow async or defer on asset loading.
	add_filter( 'script_loader_tag', $n( 'script_loader_tag' ), 10, 2 );

	do_action( 'bpo_woo_loaded' );
}

/**
 * Registers the default textdomain.
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'bpo-woo' );
	load_textdomain( 'bpo-woo', WP_LANG_DIR . '/bpo-woo/bpo-woo-' . $locale . '.mo' );
	load_plugin_textdomain( 'bpo-woo', false, plugin_basename( WBPO_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @return void
 */
function init() {
	do_action( 'bpo_woo__init' );
}

/**
 * Activate the plugin
 *
 * @return void
 */
function activate() {
	// First load the init scripts in case any rewrite functionality is being loaded
	init();
	flush_rewrite_rules();
}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {

}


/**
 * The list of knows contexts for enqueuing scripts/styles.
 *
 * @return array
 */
function get_enqueue_contexts() {
	return [ 'admin', 'frontend', 'shared' ];
}

/**
 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $script Script file name (no .js extension)
 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
 *
 * @return string|WP_Error URL
 */
function script_url( $script, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in TenUpPlugin script loader.' );
	}

	return WBPO_URL . "dist/js/${script}.js";

}

/**
 * Generate an URL to a stylesheet, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $stylesheet Stylesheet file name (no .css extension)
 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
 *
 * @return string URL
 */
function style_url( $stylesheet, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in TenUpPlugin stylesheet loader.' );
	}

	return WBPO_URL . "dist/css/${stylesheet}.css";

}

/**
 * Enqueue scripts for front-end.
 *
 * @return void
 */
function scripts() {

	wp_enqueue_script(
		'wbpo-shared',
		script_url( 'shared', 'shared' ),
		[],
		WBPO_VERSION,
		true
	);

	wp_enqueue_script(
		'wbpo-frontend',
		script_url( 'frontend', 'frontend' ),
		[],
		WBPO_VERSION,
		true
	);

	wp_localize_script(
		'wbpo-frontend',
		'WbPo',
		[
			'alert_selection'          => esc_html__( 'Please select some product options before adding this grouped to the cart.', 'woo-bpo' ),
			'alert_empty'              => esc_html__( 'Please choose at least one product before adding this grouped to the cart.', 'woo-bpo' ),
			'select_options'           => esc_html__( 'Select options', 'woo-bpo' ),
			'add_to_cart'              => esc_html__( 'Add to cart', 'woo-bpo' ),
			'total_text'               => wbpo_get_setting( 'total_text', 'wbpo_labels', __( 'Total', 'woo-bpo' ) ),
			'change_image'             => wbpo_get_setting( 'variation_change_image', 'wbpo_setting', 'on' ),
			'change_price'             => wbpo_get_setting( 'variation_change_price', 'wbpo_setting', 'on' ),
			'price_selector'           => wbpo_get_setting( 'price_selector', 'wbpo_setting' ),
			'price_format'             => get_woocommerce_price_format(),
			'price_decimals'           => wc_get_price_decimals(),
			'price_thousand_separator' => wc_get_price_thousand_separator(),
			'price_decimal_separator'  => wc_get_price_decimal_separator(),
			'currency_symbol'          => get_woocommerce_currency_symbol(),
			'version'                      => WBPO_VERSION,
		]
	);

}

/**
 * Enqueue scripts for admin.
 *
 * @return void
 */
function admin_scripts() {

	wp_enqueue_script(
		'wbpo-shared',
		script_url( 'shared', 'shared' ),
		[],
		WBPO_VERSION,
		true
	);

	wp_enqueue_script(
		'wbpo-admin',
		script_url( 'admin', 'admin' ),
		[],
		WBPO_VERSION,
		true
	);

	wp_localize_script( 'wbpo-admin', 'WbPo', [ 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ] );
}

/**
 * Enqueue styles for front-end.
 *
 * @return void
 */
function styles() {

	wp_enqueue_style(
		'wbpo-shared',
		style_url( 'shared-style', 'shared' ),
		[],
		WBPO_VERSION
	);

	if ( is_admin() ) {
		wp_enqueue_style(
			'wbpo-admin',
			style_url( 'admin-style', 'admin' ),
			[],
			WBPO_VERSION
		);
	} else {
		wp_enqueue_style(
			'wbpo-frontend',
			style_url( 'style', 'frontend' ),
			[],
			WBPO_VERSION
		);
	}

}

/**
 * Enqueue styles for admin.
 *
 * @return void
 */
function admin_styles() {

	wp_enqueue_style(
		'wbpo-shared',
		style_url( 'shared-style', 'shared' ),
		[],
		WBPO_VERSION
	);

	wp_enqueue_style(
		'wbpo-admin',
		style_url( 'admin-style', 'admin' ),
		[],
		WBPO_VERSION
	);

}

/**
 * Enqueue editor styles. Filters the comma-delimited list of stylesheets to load in TinyMCE.
 *
 * @param string $stylesheets Comma-delimited list of stylesheets.
 * @return string
 */
function mce_css( $stylesheets ) {
	if ( ! empty( $stylesheets ) ) {
		$stylesheets .= ',';
	}

	return $stylesheets . WBPO_URL . ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ?
			'assets/css/frontend/editor-style.css' :
			'dist/css/editor-style.min.css' );
}

/**
 * Add async/defer attributes to enqueued scripts that have the specified script_execution flag.
 *
 * @link https://core.trac.wordpress.org/ticket/12009
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @return string
 */
function script_loader_tag( $tag, $handle ) {
	$script_execution = wp_scripts()->get_data( $handle, 'script_execution' );

	if ( ! $script_execution ) {
		return $tag;
	}

	if ( 'async' !== $script_execution && 'defer' !== $script_execution ) {
		return $tag; // _doing_it_wrong()?
	}

	// Abort adding async/defer for scripts that have this script as a dependency. _doing_it_wrong()?
	foreach ( wp_scripts()->registered as $script ) {
		if ( in_array( $handle, $script->deps, true ) ) {
			return $tag;
		}
	}

	// Add the attribute if it hasn't already been added.
	if ( ! preg_match( ":\s$script_execution(=|>|\s):", $tag ) ) {
		$tag = preg_replace( ':(?=></script>):', " $script_execution", $tag, 1 );
	}

	return $tag;
}

/**
 * Custom Product type.
 *
 * @since 1.0.0
 * @return void
 */
function wbpo_register_product_type() {
	require_once WBPO_INC . 'classes/class-wc-product-wbpo.php';
}
