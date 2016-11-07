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
	 * @version 1.0.0
	 */
	class WC_MaxiPago {
		/**
		 * Instance of this class.
		 *
		 * @var object
		 *
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/**
		 * Slug.
		 *
		 * @var string
		 *
		 * @since 1.0.0
		 */
		protected static $text_domain = 'wc-maxipago';

		/**
		 * Initialize the plugin
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			// Check if WooCommerce activated and if class WC_Payment_Gateway exists
			if ( class_exists( 'WC_Payment_Gateway' ) ) {
				// Include files
				$this->includes();

				// Load plugin text domain
				add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

				// Load styles and script
				add_action( 'wp_enqueue_scripts', array( $this, 'load_styles_and_scripts' ) );

				// Load Helpers
				add_action( 'init', array( $this, 'load_helper' ) );

				// Add the gateway to WooCommerce
				add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

				// Prevents the store cancel orders that have not yet been paid
				add_filter( 'woocommerce_cancel_unpaid_order', array( $this, 'stop_cancel_unpaid_orders' ), 10, 2 );

				// Endpoint return maxipago
				add_action('woocommerce_api_'.strtolower( get_class( $this) ), array( $this, 'retorno_maxipago' ) );

				// Check if is admin
				if ( is_admin() ) {
					// Load styles and scripts in admin
					add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_styles_and_scripts' ) );

					// Action links
					add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
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
		 *
		 * @since 1.0.0
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
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( self::$text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Load styles and scripts
		 *
		 * @since 1.0.0
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
		 * @since 1.0.0
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
		 * @since 1.0.0
		 *
		 */
		public function load_helper() {
			$class_dir = plugin_dir_path( __FILE__ ) . "/helper/";
			foreach ( glob( $class_dir . "*.php" ) as $filename ){
				include $filename;
			}
		}

		/**
		 * Includes.
		 *
		 * @since 1.0.0
		 *
		 */
		private function includes() {
			// include_once 'includes/lib-maxi-pago/maxipago/Autoload.php';
			// include_once 'includes/lib-maxi-pago/maxiPago.php';
			include_once 'includes/wc-maxipago-gateway.php';
			include_once 'includes/wc-maxipago-api.php';
		}

		/**
		 * Print notice - WooCommerce not activated
		 *
		 * @since 1.0.0
		 *
		 */
		public function notice_woocommerce_missing() {
			include 'views/html-notice-woocommerce-missing.php';
		}

		/**
		 * Add the gateway to WooCommerce.
		 *
		 * @param array $methods WooCommerce payment methods.
		 *
		 * @since 1.0.0
		 *
		 * @return array Payment methods with MAxiPAgo.
		 */
		public function add_gateway( $methods ) {
			$methods[] = 'WC_MaxiPago_Gateway';

			return $methods;
		}

		/**
		 * Stop cancel unpaid MaxiPago orders.
		 *
		 * @param  bool     $cancel Check if need cancel the order.
		 * @param  WC_Order $order  Order object.
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		public function stop_cancel_unpaid_orders( $cancel, $order ) {
			if ( 'maxipago' === $order->payment_method ) {
				return false;
			}

			return $cancel;
		}

		/**
		 * Action links.
		 *
		 * @param array $links Action links.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public function plugin_action_links( $links ) {
			$plugin_links = array();

			$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_maxipago_gateway' ) ) . '">' . __( 'Settings', 'wc-maxipago' ) . '</a>';

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Retorno payment
		 */
		public function retorno_maxipago() {

			$content = '<h1>Conteúdo</h1>';
			$post_maxipago = $_POST;
			foreach ( $post_maxipago as $key => $value ) {
				$content .= $key . ': ' . $value . '<br />';
			}

			$to = 'ljunior2005@gmail.com';
			$subject = 'Retorno maxipago';

			wp_mail( $to, $subject, $content );
		}

	} // end class WC_MaxiPago();
	add_action( 'plugins_loaded', array( 'WC_MaxiPago', 'get_instance' ), 0 );
endif;
