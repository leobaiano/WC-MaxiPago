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
		$this->api = new WC_MaxiPago_API( $this );

		// Main actions
		add_action( 'woocommerce_api_wc_maxipago_gateway', array( $this, 'ipn_handler' ) );
		add_action( 'valid_maxipago_ipn_request', array( $this, 'update_order_status' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
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

		// Sets the order status to "waiting" (on-hold)
    	$order->update_status( 'on-hold', __( 'Waiting for confirmation of payment by maxiPago!', 'wc-maxipago' ) );

    	// Clear cart
    	WC()->cart->empty_cart();

		// Values for MaxiPago page
		$query_string = array (
					'hp_merchant_id'	=> $this->merchant_id,
					'hp_processor_id'	=> 1,
					'hp_method'			=> 'ccard',
					'hp_txntype'		=> 'sale',
					'hp_currency'		=> 'BRL',
					'hp_amount'			=> $order->get_total(),
					'hp_refnum'			=> $order_id,
					'hp_sig_itemid'		=> 'maxipago-' . $order_id,
					'hp_bname' 			=> $order->billing_first_name . ' ' . $order->billing_last_name,
					'hp_baddr'			=> $order->shipping_address_1,
					'hp_baddr2'			=> $order->shipping_address_2,
					'hp_bcity'			=> $order->shipping_city,
					'hp_bstate'			=> $order->shipping_state,
					'hp_bzip'			=> $order->shipping_postcode,
					'hp_bcountry'		=> $order->shipping_country,
					'hp_phone'			=> $order->billing_phone,
					'hp_email'			=> $order->billing_email,
					'hp_lang'			=> 'pt',
					'hp_cf_1'			=> $order_id,
					'hp_cf_2'			=> $order->order_key,
				);
		$url = $this->api->get_checkout_url();
		$url_maxipago = add_query_arg( $query_string, $url );

    	// Returns success and redirects the user to a thank you page
    	$use_shipping = isset( $_POST['ship_to_different_address'] ) ? true : false;
	    return array(
	        'result'    => 'success',
	        'redirect'	=> $url_maxipago,
	    );
	}

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page() {}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {}

	/**
	 * IPN handler.
	 */
	public function ipn_handler() {

	}

	/**
	 * Update order status.
	 *
	 * @param array $data MaxiPago post data.
	 */
	public function update_order_status( $data ) {

	}
}
