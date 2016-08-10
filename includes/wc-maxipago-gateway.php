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
		// Sett
		$this->id 					= 'maxipago';
		$this->icon 				= apply_filters( 'woocommerce_maxipago_icon', plugins_url( 'assets/images/icon-maxipago.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields 			= false;
		$this->method_title 		= __( 'MaxiPago', 'wc-maxipago' );
		$this->method_description 	= __( 'Accept payments by credit card, bank debit or banking ticket using the MaxiPago.', 'wc-maxipago' );
		$this->order_button_text  	= __( 'Proceed to payment', 'wc-maxipago' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->merchant_id 	= $this->get_option( 'merchant_id' );
		$this->secret_key   = $this->get_option( 'secret_key' );

		// Set the API.
		$this->api = new maxiPago();

		// Main actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'wc-maxipago' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable MaxiPago', 'wc-maxipago' ),
				'default' => 'yes',
			),
			'title' => array(
				'title'       => __( 'Title', 'wc-maxipago' ),
				'type'        => 'text',
				'description' => __( 'This is the title that you will see during the checkout in your store.', 'wc-maxipago' ),
				'desc_tip'    => true,
				'default'     => __( 'MaxiPago', 'wc-maxipago' ),
			),
			'description' => array(
				'title'       => __( 'Description', 'wc-maxipago' ),
				'type'        => 'textarea',
				'description' => __( 'This is the description that the user will see during checkout in your store.', 'wc-maxipago' ),
				'default'     => __( 'Pay via MaxiPago', 'wc-maxipago' ),
			),
			'merchant_id' => array(
				'title'       => __( 'MaxiPago Mechant ID', 'wc-maxipago' ),
				'type'        => 'text',
				'description' => __( 'Please enter your Merchant ID.', 'wc-maxipago' ),
				'desc_tip'    => true,
				'default'     => '',
			),
			'secret_key' => array(
				'title'       => __( 'MaxiPago Secret Key', 'wc-maxipago' ),
				'type'        => 'text',
				'description' => __( 'Please enter your Secret Key.', 'wc-maxipago' ),
				'desc_tip'    => true,
				'default'     => '',
			),
		);
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id Order ID.
	 *
	 * @return array
	 */

	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		$this->api->setCredentials( $this->merchant_id, $this->secret_key );
		$this->api->setLogger( ABSPATH . 'wp-content/plugins/WC-MaxiPago/logs', 'INFO' );
		$this->api->setDebug( true );
		$this->api->setEnvironment( 'TEST' );

		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status( 'on-hold', __( 'Awaiting payment', 'wc-maxipago' ) );

		// Reduce stock levels
		// $order->reduce_order_stock();

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}
}
