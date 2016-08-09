<?php
	/**
	 * WC MaxiPago Class
	 *
	 * @version 1.0.0
	 */

	if ( ! defined( 'ABSPATH' ) )
		exit;

	/**
	 * WooCommerce PagSeguro gateway.
	 */
	class WC_MaxiPago_Gateway extends WC_Payment_Gateway {
		/**
		 * Construct Method
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->id = 'maxipago';
			$this->icon = apply_filters( 'woocommerce_maxipago_icon', plugins_url( 'assets/images/icon-maxipago.png', plugin_dir_path( __FILE__ ) ) );
			$this->has_fields = false;
			$this->method_title = __( 'MaxiPago', 'wc-maxipago' );
			$this->method_description = __( 'Accept payments by credit card, bank debit or banking ticket using the MAxiPago.', 'wc-maxipago' );
			$this->order_button_text  = __( 'Proceed to payment', 'wc-maxipago' );
		}
	}
