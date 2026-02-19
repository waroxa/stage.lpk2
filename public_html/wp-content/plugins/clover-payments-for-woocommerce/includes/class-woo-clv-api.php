<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WC_Clover_API {

	/**
	 * Indicates whether the environment is set to Sandbox.
	 *
	 * @var bool
	 */
	private static ?bool $is_test_mode = null;

	/**
	 * Used for authentication.
	 *
	 * @var string
	 */
	private static string $bearer_token = '';

	/**
	 * Merchant ID.
	 *
	 * @var string
	 */
	private static string $merchant_id = '';

	/**
	 * HTTP GET Method constant.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	const HTTP_METHOD_GET    = 'GET';

	/**
	 * HTTP POST Method constant.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	const HTTP_METHOD_POST   = 'POST';

	/**
	 * HTTP PUT Method constant.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	const HTTP_METHOD_PUT    = 'PUT';

	/**
	 * HTTP DELETE Method constant.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	const HTTP_METHOD_DELETE = 'DELETE';

	/**
	 * Key for accessing the data in a response.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	const RESPONSE_KEY_DATA = 'data';

	/**
	 * Key for accessing the URL in a response.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	const RESPONSE_KEY_URL = 'url';

	/**
	 * Key for accessing the status code in a response.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	const RESPONSE_KEY_STATUS_CODE = 'status_code';


	/**
	 * Clover Production Platform API base URL.
	 *
	 * @var string
	 */
	const PRODUCTION_PLATFORM_API = 'https://api.clover.com/';

	/**
	 * Clover Sandbox Platform API base URL.
	 *
	 * @var string
	 */
	const SANDBOX_PLATFORM_API    = 'https://apisandbox.dev.clover.com/';

	/**
	 * Clover Production Ecommerce Service API base URL.
	 *
	 * @var string
	 */
	const PRODUCTION_ECOMM_API    = 'https://scl.clover.com/';

	/**
	 * Clover Sandbox Ecommerce Service API base URL.
	 *
	 * @var string
	 */
	const SANDBOX_ECOMM_API       = 'https://scl-sandbox.dev.clover.com/';

	const PRODUCTION_CLOVER_API   = 'https://www.clover.com/';

	const SANDBOX_CLOVER_API      = 'https://sandbox.dev.clover.com/';


	/**
	 * Retrieves the test mode status.
	 *
	 * This method checks if the test mode status is set. If not, it retrieves the settings
	 * and determines if the environment is set to 'sandbox'.
	 *
	 * @since 2.2.0
	 *
	 * @return bool The test mode status.
	 */
	private static function get_is_test_mode(): bool {
		if ( is_null( self::$is_test_mode ) ) {
			$settings = WC_Clover_Helper::get_clover_settings();
			self::$is_test_mode = 'sandbox' === $settings['environment'];
		}
		return self::$is_test_mode;
	}

	/**
	 * Retrieves the bearer token.
	 *
	 * This method checks if the bearer token is set. If not, it retrieves the settings
	 * and determines the appropriate token based on the test mode status.
	 *
	 * @since 2.2.0
	 *
	 * @return string The bearer token.
	 */
	private static function get_bearer_token(): string {
		if ( empty( self::$bearer_token ) ) {
			$settings = WC_Clover_Helper::get_clover_settings();
			self::$bearer_token = self::get_is_test_mode() ? $settings['test_private_key'] : $settings['private_key'];
		}
		return self::$bearer_token;
	}

	/**
	 * Retrieves the merchant ID.
	 *
	 * This method checks if the merchant ID is set. If not, it retrieves the settings
	 * and determines the appropriate merchant ID based on the test mode status.
	 *
	 * @since 2.2.0
	 *
	 * @return string The merchant ID.
	 */
	private static function get_merchant_id(): string {
		if ( empty( self::$merchant_id ) ) {
			$settings = WC_Clover_Helper::get_clover_settings();
			self::$merchant_id = self::get_is_test_mode() ? $settings['test_merchant_id'] : $settings['merchant_id'];
		}
		return self::$merchant_id;
	}

	/**
	 * Sends an HTTP request to the specified URL with the given method, data, and headers.
	 *
	 * This method constructs the request arguments, including the method, timeout, headers, and body.
	 * It handles retries for rate-limited requests (HTTP 429) with exponential backoff.
	 * If the bearer token is not set, it throws an exception.
	 * Logs the request arguments, headers, and URL for debugging purposes.
	 * Returns the response status code and data, or an error message if the request fails.
	 *
	 * @param string $method The HTTP method to use for the request (e.g., 'GET', 'POST', 'PUT').
	 * @param string $url The URL to send the request to.
	 * @param array $data The data to include in the request body (default is an empty array).
	 * @param array $headers The headers to include in the request (default is an empty array).
	 * @return array The response from the request, including the status code and data.
	 */
	private static function fetch( string $method, string $url, array $data = array(), array $headers = array() ): array {
		try {
			$bearer_token = self::get_bearer_token();
			if ( empty( $bearer_token ) ) {
				throw new Exception( 'Private Key not found.' );
			}

			$headers['Accept']        = 'application/json';
			$headers['Authorization'] = 'Bearer ' . $bearer_token;
			$headers['User-Agent']    = 'WooCommerce Clover';

			if ( $method === self::HTTP_METHOD_POST || $method === self::HTTP_METHOD_PUT ) {
				$headers['Content-Type'] = 'application/json';
			}

			$args = array(
				'method'  => $method,
				'timeout' => 60,
				'headers' => $headers,
			);

			if ( ! empty( $data ) ) {
				$args['body'] = wp_json_encode( $data );
			}

			for ( $attempts = 0; $attempts < 10 ; $attempts++ ) {
				$response      = wp_safe_remote_request( $url, $args );

				if ( wp_remote_retrieve_response_code( $response ) !== 429 ) {
					break;
				}

				WC_Clover_Helper::exponential_backoff( $attempts );
			}

			$sanitized_response = array(
				self::RESPONSE_KEY_STATUS_CODE  => wp_remote_retrieve_response_code( $response ),
				self::RESPONSE_KEY_URL          => $url,
				self::RESPONSE_KEY_DATA         => json_decode( wp_remote_retrieve_body( $response ) ),
			);

			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}

			return $sanitized_response;

		} catch ( Exception $e ) {
			WC_Clover_Logger::error( 'API Error: ' . $e->getMessage() );
			return array(
				self::RESPONSE_KEY_STATUS_CODE => 0,
				self::RESPONSE_KEY_DATA        => (object) array(
					'error' => array(
						'code' => 'unexpected',
					)
				),
			);
		}
	}

	/**
	 * Creates a charge for a given order.
	 *
	 * This method initiates a charge request for a specified order. It constructs the necessary data
	 * for the charge, including the amount, currency, source token, capture flag, description, metadata,
	 * customer information, and tax amount. The request is sent to the Clover API endpoint for creating charges.
	 *
	 * @param   WC_Order  $order         The order object for which the charge is being created.
	 * @param   bool      $is_charge     Indicates whether the charge should be captured immediately.
	 * @param   string    $clover_token  The Clover token used as the source for the charge.
	 * @return array The response from the charge request.
	 */
	public static function create_charge( WC_Order $order, bool $is_charge, string $clover_token ): array {
	    $headers = array(
	        'x-forwarded-for' => WC_Geolocation::get_ip_address(),
	        'idempotency_key' => wp_generate_uuid4()
	    );

	    $data = array(
	        'amount'      => WC_Clover_Helper::get_clover_amount( $order->get_total() ),
	        'currency'    => strtolower( $order->get_currency() ),
	        'source'      => '--REDACTED--',
	        'capture'     => $is_charge,
	        'description' => $is_charge ? 'Authorize and Capture' : 'Authorize',
	        'metadata'    => array(
	            'shopping_cart' => self::get_versions()
	        ),
			'external_reference_id' => $order->get_order_number(),
	        'customer' => array(
	            'first_name' => '--REDACTED--',
	            'last_name'  => '--REDACTED--',
	            'email'      => $order->get_billing_email()
	        ),
	        'tax_amount'  => WC_Clover_Helper::get_clover_amount( $order->get_total_tax() ),
	        'skip_default_convenience_fee' => true
	    );

		$phone_number = $order->get_billing_phone();
		if ( ! empty( $phone_number ) ) {
			$data['customer']['phone'] = $phone_number;
		}

		WC_Clover_Logger::info( 'Charge request.', array(
			'Request' => $data
		) );

		$data['source']                 = $clover_token;
		$data['customer']['first_name'] = $order->get_billing_first_name();
		$data['customer']['last_name']  = $order->get_billing_last_name();

	    $endpoint = self::get_ecomm_api_url() . 'v1/charges';
	    $response = self::fetch( self::HTTP_METHOD_POST, $endpoint, $data, $headers );

		if ( isset( $response['data']->source->id ) ) {
			$response['data']->source->id = '--REDACTED--';
		}
		if ( isset( $response['data']->source->name ) ) {
			$response['data']->source->name = '--REDACTED--';
		}

		WC_Clover_Logger::info( 'Charge response.', array(
			'Response' => $response
		) );

		return $response;
	}

	/**
	 * Captures a charge for a given order.
	 *
	 * This method initiates a capture request for a specified order. It constructs the necessary data
	 * for the capture, including the amount, description, and metadata. The request is sent to the
	 * Clover API endpoint for capturing charges.
	 *
	 * @param WC_Order $order The order object for which the charge is being captured.
	 * @return array The response from the capture request.
	 */
	public static function capture_charge( WC_Order $order ): array {
	    $headers = array(
	        'x-forwarded-for' => WC_Geolocation::get_ip_address()
	    );

	    $amount = $order->get_total();
	    $data = array(
	        'amount'      => WC_Clover_Helper::get_clover_amount( $amount ),
	        'description' => 'capture_charge',
	        'metadata'    => array(
	            'shopping_cart' => self::get_versions()
	        )
	    );

		WC_Clover_Logger::info( 'Capture request.', array(
			'Request' => $data
		) );

	    $charge_id = $order->get_transaction_id();
	    $endpoint = self::get_ecomm_api_url() . 'v1/charges/' . $charge_id . '/capture';
	    return self::fetch( self::HTTP_METHOD_POST, $endpoint, $data, $headers );
	}

	/**
	 * Creates a refund for a given order.
	 *
	 * This method initiates a refund request for a specified order. It constructs the necessary data
	 * for the refund, including the charge ID, external reference ID, and metadata. If the refund is
	 * partial and the order has been paid, it includes the refund amount in the data.
	 *
	 * @param   WC_Order  $order       The order object for which the refund is being created.
	 * @param   float     $amount      The amount to be refunded.
	 * @param   bool      $is_partial  Indicates whether the refund is partial.
	 *
	 * @return array The response from the refund request.
	 */
	public static function create_refund( WC_Order $order, float $amount, bool $is_partial ): array {
		$data = array(
			'charge'                => $order->get_transaction_id(),
			'external_reference_id' => $order->get_id(),
			'metadata'              => array(
				'shopping_cart' => self::get_versions()
			),
		);

		if ( $is_partial && $order->get_date_paid() ) {
			$data['amount'] = WC_Clover_Helper::get_clover_amount( $amount );
		}

		WC_Clover_Logger::info( 'Refund request.', array(
			'Request' => $data
		) );

		$endpoint = self::get_ecomm_api_url() . 'v1/refunds';
		return self::fetch( self::HTTP_METHOD_POST, $endpoint, $data );
	}

	/**
	 * Retrieves the payment configurations for a given merchant.
	 *
	 * This method sends a GET request to the Clover API to fetch the e-commerce payment configurations
	 * for the specified merchant. It constructs the endpoint URL using the merchant ID and the platform API URL.
	 *
	 * @since 2.2.0
	 *
	 * @return array The response from the request, including the status code and data.
	 */
	public static function get_payment_configs(): array {
		$merchant_id = self::get_merchant_id();
	    $endpoint = self::get_platform_api_url() . 'v3/merchants/' . $merchant_id . '/ecomm_payment_configs';
	    return self::fetch( self::HTTP_METHOD_GET, $endpoint );
	}

	/**
	 * Retrieves the e-commerce API URL based on the environment.
	 *
	 * This method returns the sandbox e-commerce API URL if test mode is enabled,
	 * otherwise, it returns the production e-commerce API URL.
	 *
	 * @return string The e-commerce API URL.
	 */
	private static function get_ecomm_api_url(): string {
	    return self::get_is_test_mode() ? self::SANDBOX_ECOMM_API : self::PRODUCTION_ECOMM_API;
	}

	/**
	 * Retrieves the platform API URL based on the environment.
	 *
	 * This method returns the sandbox platform API URL if test mode is enabled,
	 * otherwise, it returns the production platform API URL.
	 *
	 * @return string The platform API URL.
	 */
	private static function get_platform_api_url(): string {
	    return self::get_is_test_mode() ? self::SANDBOX_PLATFORM_API : self::PRODUCTION_PLATFORM_API;
	}

	private static function get_clover_api_url(): string {
		return self::get_is_test_mode() ? self::SANDBOX_CLOVER_API : self::PRODUCTION_CLOVER_API;

	}

	/**
	 * Retrieves the version information.
	 *
	 * This method returns a string containing the versions of WordPress, WooCommerce,
	 * and the Clover Payments plugin.
	 *
	 * @return string The version information in the format 'WP {wp_version} | WC {wc_version} | Clover {clover_version}'.
	 */
	private static function get_versions(): string {
		global $wp_version;
		return 'WP ' . $wp_version . ' | WC ' . WC_VERSION . ' | Clover ' . WC_CLOVER_PAYMENTS_VERSION;
	}
}
