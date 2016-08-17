<?php
/**
 * WooCommerce MAxiPago API class
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce PagSeguro API.
 */
class WC_MAxiPago_API {
	/**
	 * Gateway class.
	 *
	 * @var WC_PagSeguro_Gateway
	 */
	protected $gateway;

	/**
	 * Constructor.
	 *
	 * @param WC_PagSeguro_Gateway $gateway Payment Gateway instance.
	 */
	public function __construct( $gateway = null ) {
		$this->gateway = $gateway;
	}

	/**
	 * Get the checkout URL.
	 *
	 * @return string.
	 */
	public function get_checkout_url() {
		return 'https://secure.maxipago.net/hostpay/HostPay';
	}

	/**
	 * Process the IPN.
	 *
	 * @param  array $data IPN data.
	 *
	 * @return bool|SimpleXMLElement
	 */
	public function process_ipn_request( $data ) {
	}
}
