<?php
/**
 * Admin classes init.
 *
 * @package WbPo
 */
namespace WbPo\Classes;

 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WbPo_Init' ) ) :
		/**
	 *  Init class to initiate the classes logic..
	 *
	 * @since 1.0.0
	 */
	final class WbPo_Init {

		public function __construct() {
		}

		/**
		 * Store all the classes inside an array.
		 *
		 * @since 1.0.0
		 * @return array Full list of classes.
		 */
		public static function get_services() {
			return [
				WbPo_Settings::class,
				WbPo_Admin::class,
			];
		}

		/**
		 * Loop through the classes, initialize them,
		 * and call the register() method if it exists
		 *
		 * @since 1.0.0
		 */
		public static function register_services() {
			foreach ( self::get_services() as $class ) {
				$service = self::instantiate( $class );
				if ( method_exists( $service, 'register' ) ) {
					$service->register();
				}
			}
		}

		/**
		 * Initialize the class.
		 *
		 * @since 1.0.0
		 * @param  class $class    class from the services array.
		 * @return class instance  new instance of the class.
		 */
		private static function instantiate( $class ) {
			$service = new $class();
			return $service;
		}

	} // end class.


endif;
