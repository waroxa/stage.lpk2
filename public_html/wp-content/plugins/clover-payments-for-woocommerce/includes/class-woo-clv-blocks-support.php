<?php
/**
 * WooCommerce Blocks payment method integration for Clover.
 *
 * This class extends AbstractPaymentMethodType to integrate
 * Clover Payments with the WooCommerce block-based checkout.
 *
 * @since 2.0.0
 * @package clover-payments-for-woocommerce
 * @extends AbstractPaymentMethodType
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;

	if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Clover Payments Blocks Support Class.
 *
 * @since 2.0.0
 */
final class WC_Clover_Blocks_Support extends AbstractPaymentMethodType {

	/**
	 * Flag to indicate if the gateway is in sandbox mode.
	 *
	 * @since 2.0.0
	 * @var bool|null
	 */
	protected ?bool $test_mode = null;

	/**
	 * Initializes the payment method type.
	 *
	 * Sets the payment method name, loads settings, and determines
	 * the environment (sandbox or production).
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function initialize(): void {
		$this->name      = 'clover_payments';
		$this->settings  = get_option( "woocommerce_{$this->name}_settings", [] );
		$environment     = parent::get_setting( WC_Clover_Settings_Keys::ENVIRONMENT );
		$this->test_mode = $environment === WC_Clover_Environments::SANDBOX;

		add_action( 'woocommerce_rest_checkout_process_payment_with_context', [ $this, 'process_payment' ], 10, 2 );
	}

	/**
	 * Retrieves a gateway setting.
	 *
	 * This helper function automatically fetches the correct setting (test_ or production)
	 * based on the current environment. It falls back to the base setting name if
	 * the prefixed version doesn't exist.
	 *
	 * @since 2.0.0
	 * @param string $name    The base name of the setting (e.g., 'merchant_id').
	 * @param mixed  $default Optional. The default value to return if the setting is not found.
	 * @return mixed The setting value.
	 */
	protected function get_setting( $name, $default = '' ) {
		$env_prefix = $this->test_mode ? 'test_' : '';
		$setting = parent::get_setting( $env_prefix . $name, null );
		$setting = $setting ?? parent::get_setting( $name, null );
		return $setting ?? $default;
	}

	/**
	 * Checks if the payment method is active and available.
	 *
	 * This delegates the availability check to the main gateway class,
	 * ensuring logic is consistent between classic and block checkouts.
	 *
	 * @since 2.0.0
	 * @return bool True if active, false otherwise.
	 */
	public function is_active(): bool {
		return WC_Clover_Payments::get_instance()->get_clover_gateway()->is_available();
	}

	/**
	 * Returns an array of script handles to enqueue for the payment method.
	 *
	 * Registers the Clover SDK, the main integration script, and the stylesheet.
	 * It uses the `index.asset.php` file for dependencies and versioning.
	 *
	 * @since 2.0.0
	 * @return string[] An array of script handles.
	 */
	public function get_payment_method_script_handles(): array {
		$script_path       = '/build/index.js';
		$style_path        = '/build/index.css';
		$script_asset_path = WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/build/index.asset.php';

		$script_url = WC_CLOVER_PAYMENTS_PLUGIN_URL . $script_path;
		$style_url  = WC_CLOVER_PAYMENTS_PLUGIN_URL . $style_path;

		$version      = WC_CLOVER_PAYMENTS_VERSION;
		$dependencies = [];

		if ( file_exists( $script_asset_path ) ) {
			$asset = require $script_asset_path;

			$version      = is_array( $asset ) && isset( $asset['version'] )
				? $asset['version']
				: $version;
			$dependencies = is_array( $asset ) && isset( $asset['dependencies'] )
				? $asset['dependencies']
				: $dependencies;
		}

		$sdk_url = $this->test_mode ?
			'https://checkout.sandbox.dev.clover.com/sdk.js' :
			'https://checkout.clover.com/sdk.js';

		// Register Clover's external SDK.
		wp_register_script( 'clover', $sdk_url, [], 2.0, true );

		// Register the main payment method script.
		wp_register_script(
			'wc-clover-payments-checkout-block-integration',
			$script_url,
			array_merge( array( 'clover' ), $dependencies ),
			$version,
			true
		);

		wp_enqueue_style(
			'wc-clover-payments-checkout-block-style',
			$style_url,
			[],
			$version
		);

		wp_set_script_translations(
			'wc-clover-payments-checkout-block-integration',
			'woo-clv-payments',
			WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/languages/'
		);

		return array( 'wc-clover-payments-checkout-block-integration' );
	}


	/**
	 * Returns an array of data for the payment method.
	 *
	 * This data is passed to the frontend script via `wc.wcSettings.getSetting()`.
	 * It includes public keys, localization data, and feature flags.
	 *
	 * @since 2.0.0
	 * @return array Payment method data.
	 */
	public function get_payment_method_data(): array {
		return array(
			'merchantId'        => $this->get_setting( WC_Clover_Settings_Keys::MERCHANT_ID ),
			'publicKey'         => $this->get_setting( WC_Clover_Settings_Keys::PUBLIC_KEY ),
			'locale'            => WC_Clover_Helper::get_clover_compatible_locale(),
			'title'             => $this->get_setting( WC_Clover_Settings_Keys::TITLE ),
			'cloverURL'         => $this->test_mode ?
				WC_Clover_API::SANDBOX_CLOVER_API :
				WC_Clover_API::PRODUCTION_CLOVER_API,
			'isApplePayEnabled' => $this->get_setting( WC_Clover_Settings_Keys::APPLE_PAY ) === 'yes',
			'paymentMethodId'   => $this->name,
			'gatewayId'         => WC_Clover_Payments::get_instance()->get_clover_gateway()->id,
		);
	}

	/**
	 * Validates the payment context and retrieves the Clover payment source.
	 *
	 * This function checks for a valid nonce and an existing payment source
	 * in the payment data. It throws an exception if validation fails.
	 *
	 * @since 2.3.0
	 * @param PaymentContext $context The payment context object from WooCommerce Blocks.
	 * @return string The cleaned Clover source ID.
	 * @throws Exception If the nonce is invalid, or the source is missing.
	 */
	private function get_source_from_context( PaymentContext $context ): string {
		$payment_data = $context->payment_data ?? [];
		$source = isset( $payment_data['clover_source'] ) ? wc_clean( $payment_data['clover_source'] ) : '';

		if ( empty( $source ) ) {
			WC_Clover_Logger::error( 'Clover Blocks Payment Processing: Missing Clover source.' );
			throw new Exception( 'unexpected' );
		}

		return $source;
	}

	/**
	 * Processes the payment using data from the context.
	 *
	 * This is the main payment processing function for the Blocks checkout.
	 * It handles validation, API calls to Clover, and updates the order
	 * and payment result based on the API response.
	 *
	 * @since 2.3.0
	 * @param PaymentContext $context The payment context object.
	 * @param PaymentResult  $result  The payment result object, which is modified by this method.
	 *
	 * @return void
	 */
	public function process_payment( PaymentContext $context, PaymentResult $result ) {
		try {
			if ( $context->payment_method !== $this->name ) {
				WC_Clover_Logger::error( 'Clover Blocks Payment Processing: Payment method mismatch.', array(
					'Expected' => $this->name,
					'Got'      => $context->payment_method,
				) );
				return;
			}

			$source    = $this->get_source_from_context( $context );
			$is_charge = $this->get_setting( WC_Clover_Settings_Keys::PAYMENT_ACTION ) === 'charge';

			$response = WC_Clover_API::create_charge( $context->order, $is_charge, $source );

			$data = $response[ WC_Clover_API::RESPONSE_KEY_DATA ];

			if ( isset( $data->error ) ) {
				if ( isset( $data->error->charge ) ) {
					$context->order->set_transaction_id( $data->error->charge );
				}
				if ( isset( $data->error->code ) ) {
					throw new Exception( $data->error->code );
				}
				throw new Exception( 'unexpected' );
			}

			if ( ! isset( $data->status ) ) {
				WC_Clover_Logger::error( 'Clover Blocks Payment Processing: Invalid response.' );
				throw new Exception( 'invalid_key' );
			}

			if ( $data->status === 'succeeded' ) {
				if ( $context->payment_data['clover_apple_pay'] ) {
					$context->order->set_payment_method_title( 'Apple Pay' );
				}

				$card_details = $data->source->brand . ' - ' . $data->source->last4;
//              TODO: Use add_meta_data() instead of add_post_meta() when we implement HPOS.
//				$context->order->add_meta_data( '_card_details', $card_details );
				add_post_meta( $context->order->get_id(), '_card_details', $card_details );

				if ( $data->captured ) {
					$context->order->payment_complete( $data->id );
				} else {
					$context->order->set_transaction_id( $data->id );
					$context->order->update_status(
						'on-hold',
						__( 'Awaiting offline payment.', 'woo-clv-payments' )
					);
				}

				if ( isset( WC()->cart ) ) {
					WC()->cart->empty_cart();
				}

				$result->set_redirect_url( $context->order->get_checkout_order_received_url() );
				$result->set_status( 'success' );

			} else {
				WC_Clover_Logger::error( 'Clover Blocks Payment Processing: Charge not succeeded.', array(
					'OrderID' => $context->order->get_id(),
					'Status'  => $data->status,
				) );
				throw new Exception( 'unexpected' );
			}

		} catch ( Exception $e ) {
			$error_messages = WOO_CLV_ERRORMAPPER::get_localized_messages();
			$payment_details = [
				'message' => $error_messages[$e->getMessage()],
			];

			$context->order->update_status( 'failed', $error_messages['missing'] );
			$result->set_status( 'failure' );
			$result->set_payment_details( $payment_details );
		}
	}
}
