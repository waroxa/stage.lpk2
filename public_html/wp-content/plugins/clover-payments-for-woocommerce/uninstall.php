<?php
	/**
	 * Clover Payments Uninstaller
	 *
	 * @package woo-clover-payments
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit;
	}


	final class Clover_Payments_Uninstaller {

		private const SETTINGS_KEY = 'woocommerce_clover_payments_settings';

		private const PROD_BASE_URL = 'https://www.clover.com/';

		private const SANDBOX_BASE_URL = 'https://sandbox.dev.clover.com/';

		private const DEREG_ATTEMPTS = 5;

		private array $settings;

		public function __construct() {
			$this->settings = get_option( self::SETTINGS_KEY, array() );
		}

		public function run(): void {
			$this->process_environment_deregistration( 'production' );
			$this->process_environment_deregistration( 'sandbox' );

			$this->disable_plugin();

			// Define WC_CLOVER_PAYMENTS_OPTION_RESET in wp-config.php to wipe Clover settings.
			// Define WC_REMOVE_ALL_DATA to wipe all WooCommerce settings (includes Clover settings).
			if ( defined( 'WC_CLOVER_PAYMENTS_OPTION_RESET') || defined( 'WC_REMOVE_ALL_DATA' ) ) {
				delete_option( self::SETTINGS_KEY );

			} else {
				$this->cleanup_sensitive_settings();
				$this->save_settings();
			}
		}

		private function process_environment_deregistration( string $env ): void {
			$prefix   = ( 'production' === $env ) ? '' : 'test_';
			$base_url = ( 'production' === $env ) ? self::PROD_BASE_URL : self::SANDBOX_BASE_URL;

			$merchant_id = $this->settings[ $prefix . 'apple_pay_merchant_id' ] ?? '';
			$private_key = $this->settings[ $prefix . 'apple_pay_private_key' ] ?? '';
			$domain_uuid = $this->settings[ $prefix . 'apple_pay_domain_uuid' ] ?? '';

			if ( $this->deregister_domain( $base_url, $merchant_id, $private_key, $domain_uuid ) ) {
				unset(
					$this->settings[ 'apple_pay_domain_name' ],
					$this->settings[ $prefix . 'apple_pay_merchant_id' ],
					$this->settings[ $prefix . 'apple_pay_private_key' ],
					$this->settings[ $prefix . 'apple_pay_domain_uuid' ],
				);
			}
		}

		private function deregister_domain( string $base_url, string $merchant_id, string $private_key, string $domain_uuid ): bool {
			if ( empty( $merchant_id ) || empty( $private_key ) || empty( $domain_uuid ) ) {
				return false;
			}

			$url  = $base_url . 'payment-orchestration-service/v1/at2p/domain/' . $domain_uuid;
			$args = array(
				'method'  => 'DELETE',
				'timeout' => 60,
				'headers' => array(
					'Accept'               => 'application/json',
					'Authorization'        => 'Bearer ' . $private_key,
					'User-Agent'           => 'Clover WooCommerce Uninstaller',
					'X-Clover-Merchant-Id' => $merchant_id,
				),
			);

			for ( $attempts = 0; $attempts < self::DEREG_ATTEMPTS; ++$attempts ) {
				$response      = wp_safe_remote_request( $url, $args );
				$response_code = wp_remote_retrieve_response_code( $response );

				if ( ! is_wp_error( $response ) && in_array( $response_code, array( 200, 204 ), true ) ) {
					return true;
				}

				usleep( ( 500 * ( 2 ** $attempts ) + wp_rand( 0, 500 ) ) * 1000 );
			}
			return false;
		}

		private function disable_plugin(): void {
			$this->settings['enabled'] = 'no';
			$this->settings['apple_pay'] = 'no';
		}

		private function cleanup_sensitive_settings(): void {
			unset( $this->settings['private_key'], $this->settings['test_private_key'] );
		}

		private function save_settings(): void {
			update_option( self::SETTINGS_KEY, $this->settings );
		}
	}

	$uninstaller = new Clover_Payments_Uninstaller();
	$uninstaller->run();
