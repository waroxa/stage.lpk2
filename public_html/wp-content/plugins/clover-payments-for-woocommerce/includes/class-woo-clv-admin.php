<?php

/**
 * Admin class
 *
 * @package woo-clover-payments
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Class WOO_CLV_ADMIN
 */
class WOO_CLV_ADMIN extends WOO_CLV_GATEWAY {

	public string $environment;

	public bool $test_mode;

	public string $private_key;

	public string $publishable_key;

	public string $merchant;

	public bool $logging;

	public bool $ischarge;

	private string $error_icon = '';

	/**
	 *  Constructor
	 */
	public function __construct() {
		$this->id = 'clover_payments';
		$this->icon = WC_CLOVER_PAYMENTS_PLUGIN_URL . '/assets/images/clover-logo-quatrefoil.svg';
		$this->has_fields = true;
		$this->method_title = __('Clover Payments', 'woo-clv-payments');
		$this->method_description = __( 'Clover simplifies the lives of small businesses with tailored, all-in-one payments, and business management systems that can be implemented quickly and grow with the business.', 'woo-clv-payments' );
		$this->supports = array(
			'products',
			'refunds'
		);
		$this->countries = array( 'US', 'CA', 'PR', 'VI', 'GU', 'MP', 'AS' );

		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();
		$this->title       = $this->get_option(WC_Clover_Settings_Keys::TITLE );
		$this->enabled     = $this->get_option(WC_Clover_Settings_Keys::ENABLED );
		$this->environment = $this->get_option(WC_Clover_Settings_Keys::ENVIRONMENT );
		$this->test_mode  = WC_Clover_Environments::SANDBOX === $this->environment;
		$this->private_key = $this->test_mode ? $this->get_option('test_private_key') : $this->get_option('private_key');
		$this->publishable_key  = $this->test_mode ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key');
		$this->merchant    = $this->test_mode ? $this->get_option('test_merchant_id') : $this->get_option('merchant_id');
		$this->logging     = ('yes' === $this->get_option('debug'));
		$this->ischarge    = ('charge' === $this->get_option('payment_action'));

		$this->update_option('capture', ('charge' === $this->get_option('payment_action')) ? 'yes' : 'no');

		WC_Clover_Logger::set_is_logging_enabled( $this->logging );

		// Calls sanitize_settings() to sanitize merchant entered plugin admin settings.
		add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings' ) );

		// This action hook saves the settings.
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

		// We need custom JavaScript to obtain a token.
		add_action('admin_enqueue_scripts', array($this, 'clover_admin_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
		add_action('woocommerce_order_item_add_action_buttons', array($this, 'add_capture_button'));
		//This action hook  lets us modify content of after order details for Admin
		add_action('woocommerce_admin_order_data_after_order_details', array($this, 'wc_clv_payment_card_info_on_order_details'), 10, 1);

		// This filter hook lets us modify content of order details table to add card brand and last4 digits
		// other option add action hook to add card details outside order details table
		add_filter('woocommerce_get_order_item_totals', array($this, 'add_card_details_to_account_order'), 10, 3);
		add_filter( 'woocommerce_gateway_icon', array( $this, 'hide_icon_on_classic_checkout' ), 10, 2 );

	}

	/**
	 * Updates Order detail table on 'My account/order-view' page
	 * adds card details to a new column.
	 */
	public function add_card_details_to_account_order($total_rows, $order) {
		// add card details to $total_rows to be displayed in order details table
		$card_details = $this->get_card_details($order);
		if (isset($card_details) && !empty($card_details)) {
			$total_rows['card_details'] = array(
				'label' => __('Card details:', 'woocommerce'),
				'value' => esc_html($card_details),
			);

			// 1. saving the values of items totals to be reordered
			$order_total = $total_rows['order_total'];

			// 2. remove items totals to be reordered
			unset($total_rows['order_total']);

			// 3 Reinsert removed items totals in the right order
			$total_rows['order_total'] = $order_total;
		}
		return $total_rows;

	}

	/**
	 * Display payment card fields in to Admin order details
	 */
	public function wc_clv_payment_card_info_on_order_details($order) {
		/*
		* get all the meta data values we need to display payment details
		*/
		$card_details = $this->get_card_details($order);
		if (isset($card_details) && !empty($card_details)) {
			?>
            <br class="clear"/>
            <h4>Payment Information </h4>
            <br class="clear"/>
			<?php
			echo $card_details;
			?>

			<?php
		}
	}

	/**
	 * Adds card details to the order's metadata.
	 *
	 * This method adds the card brand and last 4 digits of the card number to the order's metadata.
	 * If the card brand is 'MC', it is converted to 'MasterCard'.
	 *
	 * @param int   $order_id The ID of the order.
	 * @param array $response  The response data containing card details.
	 * @return void
	 */
	public function add_card_details( $order_id, $response ): void {
		if ( strcasecmp( $response['data']->source->brand, 'MC' ) == 0 ) {
			$brand = 'MasterCard';
		} else {
			$brand = $response['data']->source->brand;
		}

		$card_details = $brand . ' ending in ' . $response['data']->source->last4;

		add_post_meta( $order_id, '_brand', $response['data']->source->brand );
		add_post_meta( $order_id, '_last4', $response['data']->source->last4 );
		add_post_meta( $order_id, '_card_details', $card_details );
	}

	/**
	 * Retrieves card details from the order's metadata.
	 *
	 * This method retrieves the card details (brand and last 4 digits) from the order's metadata.
	 *
	 * @param WC_Order $order The order object.
	 * @return string The card details.
	 */
	public function get_card_details($order): string {
		return get_post_meta($order->get_id(), '_card_details', true);
	}

	/**
	 * Register and enqueue admin-specific scripts.
	 *
	 * This function registers the main admin JavaScript file, localizes
	 * translatable strings for use within the script, and then enqueues it
	 * on WordPress admin pages. The script version is set to the current time
	 * to prevent caching during development.
	 *
	 * @return void
	 */
	public function clover_admin_scripts(): void {
		wp_register_script(
			'clover_admin_js',
			plugins_url( '/admin/js/woo-clv-admin.js', WC_CLOVER_PAYMENTS_MAIN_FILE ),
			array( 'jquery' ),
			date('h:i:s'),
			true
		);

		wp_localize_script(
			'clover_admin_js',
			'cloverAdminVars',
			array(
				'textRequired'  => __( 'Required', 'woo-clv-payments' ),
				'textLearnMore' => wp_sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
				__( '%1$sLearn more%2$s about the integration.', 'woo-clv-payments' ),
					'<a href="https://docs.clover.com/dev/docs/woocommerce" target="_blank" rel="noopener noreferrer">',
					'</a>'
				)
			),
		);

		wp_enqueue_script( 'clover_admin_js' );
	}

	/**
	 * Configuration fields.
	 */
	public function init_form_fields(): void {
		/**
		 * Initializes the form fields for the Clover payment gateway.
		 *
		 * Supplies an empty array to be modified for creating the admin settings page.
		 *
		 * @since 2.2.0
		 */
		$this->form_fields = apply_filters( 'wc_clover_form_fields', array() );
	}


	/**
	 * Sanitizes plugin admin settings.
	 *
	 * Currently, this method capitalizes any lower case letter that was entered for the Production and Sandbox Merchant
	 * IDs. Can be expanded to modify other settings if need be.
	 *
	 * @since  2.2.0
	 * @param  array $settings Array of admin settings values.
	 * @return array Modified admin settings array.
	 */
	public function sanitize_settings( array $settings ): array {
		if ( isset( $settings['merchant_id'] ) ) {
			$settings['merchant_id'] = trim( strtoupper( $settings['merchant_id'] ) );
		}
		if ( isset( $settings['test_merchant_id'] ) ) {
			$settings['test_merchant_id'] = trim( strtoupper( $settings['test_merchant_id'] ) );
		}
		return $settings;
	}


	/**
	 * Validates the Merchant ID field.
	 *
	 * This method checks the validity of the Production Merchant ID. If the Merchant ID is invalid or missing,
	 * an error message is displayed in the admin settings and logged for debugging purposes.
	 *
	 * @since 2.2.0
	 *
	 * @param string $key   The key of the Merchant ID field in the settings array.
	 * @param string $value The value of the Merchant ID entered by the admin.
	 * @return string The validated Merchant ID or an empty string if validation fails.
	 */
	public function validate_merchant_id_field( string $key, string $value ): string {
		$environment_key = $this->plugin_id . $this->id . '_environment';
		$is_production   = isset( $_POST[ $environment_key ] ) && 'production' === $_POST[ $environment_key ];

		if ( ! $is_production ) {
			return $value;
		}

		if ( empty( $value ) ) {
			$message = __( 'Merchant ID is missing.', 'woo-clv-payments' );
			$log_msg = 'Empty Merchant ID was submitted.';
		} else if ( preg_match('/^[A-HJKMNP-TV-Z0-9]{13}$/i', $value ) ) {
			return $value;
    	} else {
			$message = __( 'Merchant ID is invalid.', 'woo-clv-payments' );
			$log_msg = 'Invalid Merchant ID was submitted.';
		}

		WC_Clover_Helper::add_error( $message );
		WC_Clover_Logger::error( $log_msg, array( 'merchant_id' => $value ) );

		return '';
	}

	/**
	 * Validates the Sandbox Merchant ID field.
	 *
	 * This method checks the validity of the Sandbox Merchant ID. If the Sandbox Merchant ID is invalid or missing,
	 * an error message is displayed in the admin settings and logged for debugging purposes.
	 *
	 * @since 2.2.0
	 *
	 * @param string $key   The key of the Sandbox Merchant ID field in the settings array.
	 * @param string $value The value of the Sandbox Merchant ID entered by the admin.
	 * @return string The validated Sandbox Merchant ID or an empty string if validation fails.
	 */
	public function validate_test_merchant_id_field( string $key, string $value ): string {
		$environment_key = $this->plugin_id . $this->id . '_environment';
		$test_mode	     = isset( $_POST[ $environment_key ] ) && 'sandbox' === $_POST[ $environment_key ];

		if ( ! $test_mode ) {
			return $value;
		}

		if ( empty( $value ) ) {
			$message = __( 'Sandbox Merchant ID is missing.', 'woo-clv-payments' );
			$log_msg = 'Empty Sandbox Merchant ID was submitted.';
		} else if ( preg_match('/^[A-HJKMNP-TV-Z0-9]{13}$/i', $value ) ) {
			return $value;
		} else {
			$message = __( 'Sandbox Merchant ID is invalid.', 'woo-clv-payments' );
			$log_msg = 'Invalid Sandbox Merchant ID was submitted.';
		}

		WC_Clover_Helper::add_error( $message );
		WC_Clover_Logger::error( $log_msg, array( 'sandbox_merchant_id' => $value ) );

		return '';
	}

	/**
	 * Outputs the HTML for the custom card form fields on the checkout page.
	 *
	 * @since 1.0.0
	 * @see WC_Payment_Gateway::payment_fields()
	 */
	public function payment_fields() {
		$this->load_error_icon();
		?>
		<fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" >

			<div id="clover-errors" role="alert"></div>

			<div id="card-elements-container">

				<div class="card-row-top">
					<div id="card-name" class="field"></div>
					<?php $this->render_error_container( 'card-name-errors' ); ?>
				</div>

				<div class="card-row-top">
					<div id="card-number" class="field"></div>
					<?php $this->render_error_container( 'card-number-errors' ); ?>
				</div>

				<div class="card-row-center">
					<div class="date-container">
						<div id="card-date" class="field"></div>
						<?php $this->render_error_container( 'card-date-errors' ); ?>
					</div>
					<div class="cvv-container">
						<div id="card-cvv" class="field"></div>
						<?php $this->render_error_container( 'card-cvv-errors' ); ?>
					</div>
				</div>

				<div class="card-row-bottom">
					<div class="width-container">
						<div class="margin-container">
							<div id="card-postal-code" class="field"></div>
							<?php $this->render_error_container( 'card-postal-code-errors' ); ?>
						</div>
					</div>
				</div>

			</div>
		</fieldset>
		<?php
	}

	/**
	 * Renders the HTML structure for a field's validation error message.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $id The unique HTML ID to assign to the error paragraph element.
	 */
	private function render_error_container( string $id ) {
		?>
		<div class="input-errors" role="alert">
			<p id="<?php echo esc_attr( $id ); ?>" class="validation-error">
				<?php echo $this->error_icon; ?>
				<span class="error-text" ></span>
			</p>
		</div>
		<?php
	}

	/**
	 * Loads the SVG error icon from the file system and caches it in a class property.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function load_error_icon(): void {
		$icon_path = WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/assets/icons/error-icon.svg';
		if ( file_exists( $icon_path ) ) {
			$this->error_icon = file_get_contents( $icon_path );
		}
	}

	/**
	 * *
	 *
	 * @param  type $test_mode Testmode.
	 * @return type
	 */
	private function getSurcharge(): array {
		$response = WC_Clover_API::get_payment_configs();
		return $this->parse_surcharge( $response );
	}

	private function parse_surcharge( array $response ): array {
		$surcharge = array();

		if ( $response['status_code'] === 200 ) { // 200 indicates success api response
			$surcharge['message'] = '';

			if ( isset( $response['data']->surcharging ) ) {
				$surcharging 			= $response['data']->surcharging;
				$surcharge['supported'] = $response['data']->surcharging->supported;

				if ( $surcharge['supported'] && isset( $surcharging->rate ) ) {
					$rate = ( $response['data']->surcharging->rate * 100 );
					$surcharge['message'] = 'Note: A surcharge of ' . $rate
					. '% may be applied to credit cards transactions';
				}
			}
		} elseif ( $response['status_code'] === 0 ) { // 0 indicates internal error
			$surcharge['message'] = 'Unable to display surcharge information at this moment ';

		} elseif ( $response['status_code'] === 401 ) {
			$surcharge['message'] = 'Merchant ID is invalid, so we are not able to display surcharge information';

		} else {
			$surcharge['message'] = 'Unable to display surcharge information at this moment';
		}
		return $surcharge;
	}

	/**
	 * Load frontend scripts.
	 *
	 * @return type
	 */
	public function payment_scripts(): void {
		if ( ! is_checkout() ) {
			return;
		}

		$sdk_url = $this->test_mode ? 'https://checkout.sandbox.dev.clover.com/sdk.js' : 'https://checkout.clover.com/sdk.js';
		wp_register_script(
			'clover',
			$sdk_url,
			array(),
			WC_CLOVER_PAYMENTS_VERSION,
			true
		);

		wp_register_style(
			'custom_styles',
			plugins_url('../public/css/woo-clv-custom.css', __FILE__),
			array(),
			WC_CLOVER_PAYMENTS_VERSION
		);

		wp_register_script(
			'custom_scripts',
			plugins_url('../public/js/woo-clv-custom.js',__FILE__),
			array( 'jquery', 'clover' ),
			WC_CLOVER_PAYMENTS_VERSION,
			true
		);

		wp_localize_script(
			'custom_scripts',
			'wc_clover_params',
			array(
				'publishableKey'    => $this->publishable_key,
				'locale'            => WC_Clover_Helper::get_clover_compatible_locale(),
				'merchant'          => $this->merchant,
				'checkoutNonce'     => wp_create_nonce( 'clover_process_checkout' ),
				'localizedMessages' => WOO_CLV_ERRORMAPPER::get_localized_messages()
			)
		);

		wp_enqueue_script('custom_scripts');
		wp_enqueue_style('custom_styles');
	}

	/**
	 * Process checkout.
	 *
	 * @param  type $order_id Order id.
	 * @return array
	 * @global type $woocommerce Woocommerce.
	 */
	public function process_payment( $order_id ) {
		try {
			global $woocommerce;
			$order = wc_get_order( $order_id );
			$success_link = $this->get_return_url( $order );

			// get clover token value, if empty notify's user that transaction could not be processed and
			// at admin's end shows the woocommerce order has been failed
			$clover_token = $this->get_token();
			if( empty( $clover_token ) ) {
				// log the information
				WC_Clover_Logger::warning( 'Transaction could not be processed: Clover Token does not exist.' );

				$order->update_status( 'failed' );
				$failure_message = __( 'Transaction could not be processed. Please try again.', 'woo-clv-payments' );
				wc_add_notice( $failure_message, 'error' );
				return array(
					'result' => 'failure',
					'message' => $failure_message,
					'error_code' => 'Unexpected',
				);
			}

			$response = WC_Clover_API::create_charge( $order, $this->ischarge, $clover_token);

			$processed_response = $this->handle_payments_response( $response );

			if ( $processed_response['message'] === 'succeeded' ) {
				$woocommerce->cart->empty_cart();

				// adding card details( card brand and last4 to order's metadata in post meta table
				$this->add_card_details( $order_id, $response );

				if ( $this->ischarge ) {
					$order->payment_complete( $processed_response['TXN_ID'] );
				} else {
					$order->set_transaction_id( $processed_response['TXN_ID'] );
					$order->update_status(
						'on-hold',
						__( 'Awaiting offline payment.', 'woo-clv-payments' )
					);
				}

				return array(
					'result' => 'success',
					'redirect' => $success_link,
				);
			} else {

				$failure_message = WOO_CLV_ERRORMAPPER::get_localized_error_message( $processed_response );
				$order->update_status('failed');
				wc_add_notice( $failure_message, 'error' );
				return array(
					'result' => 'failure',
					'message' => $failure_message,
					'error_code' => $processed_response['error_code'],
				);
			}

		} catch (Exception $e) {

			$order->update_status('failed');
			$failure_message = __('An error has occurred, please try again', 'woo-clv-payments');
			wc_add_notice( $failure_message, 'error');
			return array(
				'result' => 'failure',
				'exceptionMessage' => $e->getMessage(),
				'message' => $failure_message,
				'error_code' => 'Unexpected',
			);
		}
	}

	/**
	 * Admin Capture button.
	 *
	 * @param  type $order Order id.
	 * @return type
	 */
	public function add_capture_button( $order ) {
		if (!($order->payment_method === $this->id)) {
			return;
		}
		if (in_array($order->get_status(), array('cancelled', 'refunded', 'failed'), true)) {
			return;
		}
		if ($order->get_date_paid()) {
			return;
		}

		$order_id_nonce = wp_create_nonce('order-id-nonce');
		?>
        <button data-order_id_nonce="<?php echo esc_attr($order_id_nonce); ?>"
                data-order_id="<?php echo esc_attr($order->get_id()); ?>" type="button"
                class="button button-primary clv-wc-payment-gateway-capture"><?php esc_html_e('Capture Charge', 'woo-clv-payments'); ?></button>
		<?php
	}

	/**
	 * Capture action.   *
	 *
	 * @param  type $order        Order id.
	 * @param  type $bulk_capture Optional.
	 * @return type
	 */
	public function process_capture( $order, $bulk_capture = false ) {
		if ( ! ( $order->payment_method === $this->id ) ) {
			return array(
				'success' => false,
				'message' => __('Please select the correct order.', 'woo-clv-payments'),
				'code' => 400,
				'processed' => false,
			);
		}
		if ( in_array( $order->get_status(), array( 'cancelled', 'refunded', 'failed' ), true ) ) {
			return array(
				'success' => false,
				'message' => __('Unable to capture canceled, refunded, or failed orders.', 'woo-clv-payments'),
				'code' => 400,
				'processed' => false,
			);
		}
		if ( $order->get_date_paid() ) {
			return array(
				'success' => false,
				'message' => __('Already captured: unable to process again.', 'woo-clv-payments'),
				'code' => 400,
				'processed' => false,
			);
		}
		try {
			$response = WC_Clover_API::capture_charge( $order );

			WC_Clover_Logger::info( 'Capture response.', array(
				'Response' => $response
			) );

			$processed_response = $this->handle_payments_response( $response );

			if ( $processed_response['message'] === 'succeeded' ) {
				$amount = $order->get_total();
				$message = $processed_response['message'];

				$message = wp_sprintf(
				/* translators: %1$s: amount, %2$s: capture ID, %3$s: status */
				__('Captured %1$s - Capture ID: %2$s - Status: %3$s', 'woo-clv-payments'),
					$amount, $processed_response['TXN_ID'], $message
				);

				$order->update_meta_data( '_clover_capture_id', $processed_response['TXN_ID'] );
				$order->add_order_note( $message );
				$order->payment_complete( $processed_response['TXN_ID'] );

				return array(
					'success' => true,
					'code' => 200,
					'message' => $message,
					'processed' => true,
				);

			} else {
				$failure_message = WOO_CLV_ERRORMAPPER::get_localized_error_message( $processed_response );

				return array(
					'success' => false,
					'message' => $failure_message,
					'code' => $processed_response['error_code'],
					'processed' => true,
				);
			}
		} catch (Exception $e) {
			$order->update_status('failed');
			wc_add_notice(
				esc_html__('An error has occurred; please try again.', 'woo-clv-payments'),
				'error'
			);
			return array(
				'success' => false,
				'message' => $e->getMessage(),
				'code' => 500,
				'processed' => true,
			);
		}
	}

	/**
	 * checks for clover token value and returns the token to be used in getchargedata call.
	 *
	 * @return string
	 */
	private function get_token(): string {
		$clover_token = '';
		if ( WC_Clover_Helper::verify_checkout_nonce() ) {
			$clover_token = sanitize_text_field( $_POST['clover_token'] );
		}
		return $clover_token;
	}

	public function hide_icon_on_classic_checkout( $icon_html, $gateway_id ): string {
		if ( $this->id === $gateway_id ) {
			return '';
		}
		return $icon_html;
	}

	private function is_store_country_supported(): bool {
		if ( ! function_exists( 'WC' ) ) {
			return false;
		}
		$store_country = WC()->countries->get_base_country();
		if ( in_array( $store_country, $this->countries, true ) ) {
			return true;
		}
		WC_Clover_Logger::notice( 'Clover Payments is not available in ' . $store_country . '.' );
		return false;
	}

	public function is_available(): bool {
		if ( empty( $this->merchant ) ) {
			WC_Clover_Logger::error( 'Merchant ID is not set.' );
			return false;
		}
		if ( empty( $this->publishable_key ) ) {
			WC_Clover_Logger::error( 'Public Key is not set.' );
			return false;
		}
		if ( empty( $this->private_key) ) {
			WC_Clover_Logger::error( 'Private Key is not set.' );
			return false;
		}
		if ( ! $this->test_mode && ! is_ssl() ) {
			WC_Clover_Logger::error( 'Page is not using SSL.' );
			return false;
		}

		return parent::is_available() && $this->is_store_country_supported();
	}

}
