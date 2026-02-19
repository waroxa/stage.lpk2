<?php
	/**
	 * WC_Clover_Apple_Pay_API Class
	 *
	 * This file handles communication with the Clover API for Apple Pay domain management.
	 *
	 * @package woo-clover-payments
	 * @since   2.3.0
	 * @version 1.0.0
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	/**
	 * Handles communication with the Clover API for Apple Pay domain management.
	 *
	 * @since 2.3.0
	 */
	class WC_Clover_Apple_Pay_API {

		/**
		 * Production Clover API URL.
		 *
		 * @var string
		 */
		private const PRODUCTION_CLOVER_API = 'https://www.clover.com/';

		/**
		 * Sandbox Clover API URL.
		 *
		 * @var string
		 */
		private const SANDBOX_CLOVER_API = 'https://sandbox.dev.clover.com/';

		/**
		 * Clover Production Platform API URL.
		 *
		 * @var string
		 */
		private const PRODUCTION_PLATFORM_API = 'https://api.clover.com/';

		/**
		 * Clover Sandbox Platform API URL.
		 *
		 * @var string
		 */
		private const SANDBOX_PLATFORM_API = 'https://apisandbox.dev.clover.com/';


		/**
		 * HTTP GET Method constant.
		 *
		 * @since 2.3.0
		 * @var string
		 */
		private const HTTP_METHOD_GET = 'GET';

		/**
		 * HTTP POST Method constant.
		 *
		 * @since 2.3.0
		 * @var string
		 */
		private const HTTP_METHOD_POST = 'POST';

		/**
		 * HTTP PUT Method constant.
		 *
		 * @since 2.3.0
		 * @var string
		 */
		private const HTTP_METHOD_PUT = 'PUT';

		/**
		 * HTTP DELETE Method constant.
		 *
		 * @since 2.3.0
		 * @var string
		 */
		private const HTTP_METHOD_DELETE = 'DELETE';

		/**
		 * The number of retry attempts for API requests.
		 *
		 * @since 2.3.0
		 * @var int
		 */
		private const RETRY_ATTEMPTS = 5;

		/**
		 * The Clover Merchant ID.
		 *
		 * @since 2.3.0
		 * @var   string
		 */
		private string $merchant_id = '';

		/**
		 * The Clover Private API Key.
		 *
		 * @since 2.3.0
		 * @var   string
		 */
		private string $private_key = '';

		/**
		 * The base Clover API URL for the current environment.
		 *
		 * @since 2.3.0
		 * @var   string
		 */
		private string $clover_api_url;

		/**
		 * The base Clover Platform API URL for the current environment.
		 *
		 * @since 2.3.0
		 * @var   string
		 */
		private string $platform_api_url;

		/**
		 * WC_Clover_Apple_Pay_API constructor.
		 *
		 * @since 2.3.0
		 *
		 * @param string $environment The API environment ('production' or 'sandbox').
		 * @param string $merchant_id The Clover Merchant ID.
		 * @param string $private_key The Clover Private API Key.
		 */
		public function __construct( string $environment, string $merchant_id, string $private_key ) {
			$this->set_properties( $environment, $merchant_id, $private_key);
		}

		/**
		 * Sets the API URLs based on the provided environment.
		 *
		 * @since 2.3.0
		 *
		 * @param string $environment The API environment ('production' or 'sandbox').
		 */
		private function set_urls( string $environment ): void {
			if ( $environment === WC_Clover_Environments::PRODUCTION ) {
				$this->clover_api_url   = self::PRODUCTION_CLOVER_API;
				$this->platform_api_url = self::PRODUCTION_PLATFORM_API;

			} else {
				$this->clover_api_url   = self::SANDBOX_CLOVER_API;
				$this->platform_api_url = self::SANDBOX_PLATFORM_API;
			}
		}

		/**
		 * Sets the API properties for making requests.
		 *
		 * @since 2.3.0
		 *
		 * @param string $environment The API environment ('production' or 'sandbox').
		 * @param string $merchant_id The Clover Merchant ID.
		 * @param string $private_key The Clover Private API Key.
		 */
		public function set_properties( string $environment, string $merchant_id, string $private_key ): void {
			$this->set_urls( $environment );
			$this->merchant_id = $merchant_id;
			$this->private_key = $private_key;
		}

		/**
		 * Performs an HTTP request to the Clover API.
		 *
		 * Implements a retry mechanism with exponential backoff for 429 (Too Many Requests) responses.
		 *
		 * @since 2.3.0
		 *
		 * @param string $method  The HTTP method (e.g., 'GET', 'POST').
		 * @param string $url     The request URL.
		 * @param array  $headers Optional. Additional request headers.
		 * @param array  $data    Optional. Request body data for POST/PUT requests.
		 * @return array The sanitized API response.
		 */
		private function fetch( string $method, string $url, array $headers = array(), array $data = array() ): array {
			try {
				$headers['Accept']        = 'application/json';
				$headers['Authorization'] = 'Bearer ' . $this->private_key;
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

				for ( $attempts = 0; $attempts < self::RETRY_ATTEMPTS; ++$attempts ) {
					$response      = wp_safe_remote_request( $url, $args );
					$response_body = wp_remote_retrieve_body( $response );

					if ( wp_remote_retrieve_response_code( $response ) !== 429 ) {
						break;
					}

					WC_Clover_Helper::exponential_backoff( $attempts );
				}

				$sanitized_response = array(
					WC_Clover_Response_Keys::STATUS_CODE => wp_remote_retrieve_response_code( $response ),
					WC_Clover_Response_Keys::URL         => $url,
					WC_Clover_Response_Keys::DATA        => json_decode( $response_body ),
				);

				if ( is_wp_error( $response ) ) {
					$sanitized_response['error_message'] = $response->get_error_message();
				}

				return $sanitized_response;

			} catch ( Exception $e ) {
				WC_Clover_Logger::error( 'API Error: ' . $e->getMessage() );
				return array(
					WC_Clover_Response_Keys::STATUS_CODE => 0,
				);
			}
		}

		/**
		 * Registers a domain with Clover for Apple Pay.
		 *
		 * @since 2.3.0
		 *
		 * @param string $domain The domain name to register.
		 * @return array The API response.
		 */
		public function register_domain( string $domain ): array {
			$headers = array(
				'X-Clover-Merchant-Id' => $this->merchant_id,
			);

			$body = array(
				'domain' => $domain,
			);

			$url = $this->clover_api_url . 'payment-orchestration-service/v1/at2p/domain';

			WC_Clover_Logger::info(
				'Domain registration request.',
				array(
					'url' => $url,
					'headers' => $headers,
					'body' => $body
				)
			);

			$response = $this->fetch( self::HTTP_METHOD_POST, $url, $headers, $body );

			WC_Clover_Logger::info( 'Domain registration response.', $response );

			return $response;
		}

		/**
		 * Verifies a registered domain with Clover for Apple Pay.
		 *
		 * Retries the request up to 3 times with exponential backoff due to potential API delays.
		 *
		 * @since 2.3.0
		 *
		 * @param string $uuid The UUID of the domain to verify.
		 * @return array The API response.
		 */
		public function verify_domain( string $uuid ): array {
			$headers = array(
				'X-Clover-Merchant-Id' => $this->merchant_id,
			);

			$body = array(
				'uuid' => $uuid,
			);

			$url = $this->clover_api_url . 'payment-orchestration-service/v1/at2p/domain';

			WC_Clover_Logger::info(
				'Domain verification request.',
				array(
					'url' => $url,
					'headers' => $headers,
					'body' => $body
				)
			);

			// Successful domain verification calls were inconsistent when made immediately after registration. Introducing
			// a delay resolved the issue.
			for ( $attempts = 0; $attempts < self::RETRY_ATTEMPTS; $attempts++ ) {
				$response = $this->fetch( self::HTTP_METHOD_PUT, $url, $headers, $body );

				if ( ! isset( $response[ WC_Clover_Response_Keys::DATA ]->error ) ) {
					break;
				}

				WC_Clover_Helper::exponential_backoff( $attempts );
			}

			WC_Clover_Logger::info(
				'Domain verification response.', $response );

			return $response;
		}

		/**
		 * Deletes a registered domain from Clover.
		 *
		 * Retries the request up to 3 times with exponential backoff if the initial attempt fails.
		 *
		 * @since 2.3.0
		 *
		 * @param string $uuid The UUID of the domain to delete.
		 * @return array The API response.
		 */
		public function delete_domain( string $uuid ): array {
			$headers = array(
				'X-Clover-Merchant-Id' => $this->merchant_id,
			);

			$url = $this->clover_api_url . 'payment-orchestration-service/v1/at2p/domain/' . $uuid;

			WC_Clover_Logger::info( 'Domain deletion request.',
				array(
					'url' => $url,
					'headers' => $headers,
				)
			);

			$response = array();
			for ( $attempts = 0; $attempts < self::RETRY_ATTEMPTS; ++$attempts ) {
				$response = $this->fetch( self::HTTP_METHOD_DELETE, $url, $headers );

				if ( $response[ WC_Clover_Response_Keys::STATUS_CODE ] === 200 ) {
					break;
				}

				WC_Clover_Helper::exponential_backoff( $attempts );
			}

			WC_Clover_Logger::info( 'Domain deletion response.', $response );

			return $response;
		}

		/**
		 * Retrieves the list of registered domains from Clover.
		 *
		 * @since 2.3.0
		 *
		 * @return array The API response containing the list of domains.
		 */
		public function get_domains(): array {
			$headers = array(
				'X-Clover-Merchant-Id' => $this->merchant_id,
			);

			$url = $this->clover_api_url . 'payment-orchestration-service/v1/at2p/domain';

			WC_Clover_Logger::info( 'Domain list request.',
				array(
					'url' => $url,
				)
			);

			$response = $this->fetch( self::HTTP_METHOD_GET, $url, $headers );

			WC_Clover_Logger::info( 'Domain list response.', $response );

			return $response;
		}

		/**
		 * Retrieves e-commerce payment configurations for the merchant.
		 *
		 * @since 2.3.0
		 *
		 * @return array The API response containing payment configurations.
		 */
		public function get_payment_configs(): array {
			$url = $this->platform_api_url . 'v3/merchants/' . $this->merchant_id . '/ecomm_payment_configs';

			WC_Clover_Logger::info( 'Payment config request.',
				array(
					'url' => $url
				)
			);

			$response = self::fetch( self::HTTP_METHOD_GET, $url );

			WC_Clover_Logger::info( 'Payment config response.', $response );

			return $response;
		}
	}
