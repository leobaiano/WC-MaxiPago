<?php
	/**
	 * Plugin Name: WC MaxiPago
	 * Plugin URI:
	 * Description: This plugin adds MaxiPago the list of payment gateways WooCommerce
	 * Author: Leo Baiano
	 * Author URI: http://leobaiano.com.br
	 * Version: 1.0.0
	 * License: GPLv2 or later
	 * Text Domain: wc-maxipago
 	 * Domain Path: /languages/
	 */

	if ( ! defined( 'ABSPATH' ) )
		exit; // Exit if accessed directly.

	if ( ! class_exists( 'WC_MaxiPago' ) ) :
		/**
		 * WC MaxiPago
		 *
		 * @author   Leo Baiano <ljunior2005@gmail.com>
		 */
		class WC_MaxiPago {
			/**
			 * Instance of this class.
			 *
			 * @var object
			 */
			protected static $instance = null;

			/**
			 * Slug.
			 *
			 * @var string
			 */
			protected static $text_domain = 'wc-maxipago';

			/**
			 * Initialize the plugin
			 */
			private function __construct() {
				// Check if WooCommerce activated and if class WC_Payment_Gateway exists
				if ( class_exists( 'WC_Payment_Gateway' ) ) {
					// Load plugin text domain
					add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

					// Load styles and script
					add_action( 'wp_enqueue_scripts', array( $this, 'load_styles_and_scripts' ) );

					// Load Helpers
					add_action( 'init', array( $this, 'load_helper' ) );

					// Check if is admin
					if ( is_admin() ) {
						// Load styles and scripts in admin
						add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_styles_and_scripts' ) );
					}
				} else {
					// Check if is admin
					if ( is_admin() ) {
						// If WooCommerce not activated print notice
						add_action( 'admin_notices', array( $this, 'notice_woocommerce_missing' ) );
					}
				}
			}

			/**
			 * Return an instance of this class.
			 *
			 * @return object A single instance of this class.
			 */
			public static function get_instance() {
				// If the single instance hasn't been set, set it now.
				if ( null == self::$instance ) {
					self::$instance = new self;
				}

				return self::$instance;
			}

			/**
			 * Load the plugin text domain for translation.
			 *
			 * @return void
			 */
			public function load_plugin_textdomain() {
				load_plugin_textdomain( self::$text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}

			/**
			 * Load styles and scripts
			 *
			 */
			public function load_styles_and_scripts() {
				wp_enqueue_style( self::$text_domain . '_css_main', plugins_url( '/assets/css/main.css', __FILE__ ), array(), null, 'all' );
				$params = array(
							'ajax_url'	=> admin_url( 'admin-ajax.php' )
						);
				wp_enqueue_script( self::$text_domain . '_js_main', plugins_url( '/assets/js/main.js', __FILE__ ), array( 'jquery' ), null, true );
				wp_localize_script( self::$text_domain . '_js_main', 'data_baianada', $params );
			}

			/**
			 * Load styles and scripts
			 *
			 */
			public function load_admin_styles_and_scripts() {
				wp_enqueue_style( self::$text_domain . '_admin_css_main', plugins_url( '/assets/css/admin_main.css', __FILE__ ), array(), null, 'all' );
				$params = array(
							'ajax_url'	=> admin_url( 'admin-ajax.php' )
						);
				wp_enqueue_script( self::$text_domain . '_admin_js_main', plugins_url( '/assets/js/admin_main.js', __FILE__ ), array( 'jquery' ), null, true );
				wp_localize_script( self::$text_domain . '_admin_js_main', 'data_baianada', $params );
			}

			/**
			 * Load auxiliary and third classes are in the class directory
			 *
			 */
			public function load_helper() {
				$class_dir = plugin_dir_path( __FILE__ ) . "/helper/";
				foreach ( glob( $class_dir . "*.php" ) as $filename ){
					include $filename;
				}
			}

			/**
			 * Print notice - WooCommerce not activated
			 */
			public function notice_woocommerce_missing() {
				include 'views/html-notice-woocommerce-missing.php';
			}

		} // end class WC_MaxiPago();
		add_action( 'plugins_loaded', array( 'WC_MaxiPago', 'get_instance' ), 0 );
	endif;
