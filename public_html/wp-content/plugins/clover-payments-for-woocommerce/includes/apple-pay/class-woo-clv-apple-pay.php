<?php
/**
 * Clover Payments - Apple Pay Handler.
 *
 * This file contains the main class responsible for handling Apple Pay domain registration,
 * verification, and the necessary WordPress hooks for the WooCommerce Clover Payments plugin.
 *
 * @package woo-clv-payments
 * @since   2.3.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the Apple Pay domain lifecycle for WooCommerce Clover Payments.
 *
 * This class orchestrates the registration and verification of the site's domain for Apple Pay,
 * sets up rewrite rules for the domain association file, and hooks into WordPress
 * admin actions to respond to settings changes.
 *
 * @since 2.3.0
 */
class WC_Clover_Apple_Pay {

	/**
	 * The required filename for the Apple Pay domain association file.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	const DOMAIN_ASSOCIATION_FILE_NAME = 'apple-developer-merchantid-domain-association';

	/**
	 * The required directory for serving the domain association file.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	const DOMAIN_ASSOCIATION_FILE_DIR = '.well-known';

	/**
	 * The valid environments for Clover operations.
	 *
	 * @since 2.3.0
	 * @var string[]
	 */
	const ENVIRONMENTS = array(
		WC_Clover_Environments::SANDBOX,
		WC_Clover_Environments::PRODUCTION,
	);

	/**
	 * Holds the notice message to be displayed in the admin area.
	 *
	 * @since 2.3.0
	 * @var   string
	 */
	private string $notice = '';

	/**
	 * The current site's host name (domain).
	 *
	 * @since 2.3.0
	 * @var   string
	 */
	private string $domain_name;

	/**
	 * The factory for creating API client instances.
	 *
	 * @since 2.3.0
	 * @var   WC_Clover_Apple_Pay_API_Factory
	 */
	private WC_Clover_Apple_Pay_API_Factory $api_factory;


	/**
	 * Constructor for WC_Clover_Apple_Pay.
	 *
	 * @since 2.3.0
	 * @param WC_Clover_Apple_Pay_API_Factory $api_factory The factory for creating API clients.
	 */
	public function __construct( WC_Clover_Apple_Pay_API_Factory $api_factory ) {
		$this->api_factory = $api_factory;
		$this->domain_name = parse_url( get_site_url(), PHP_URL_HOST );
	}

	/**
	 * Registers WordPress hooks and filters for the plugin.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_domain_association_endpoint' ), 5 );
		add_filter( 'query_vars', array( $this, 'register_domain_association_query_var' ) );
		add_action( 'template_redirect', array( $this, 'serve_domain_association_file' ), 1 );
		add_action( 'admin_init', array( $this, 'reset_domain_registration_on_name_change' ) );
		add_action( 'admin_notices', array( $this, 'display_notice' ) );
		add_filter( 'woocommerce_settings_api_form_fields_clover_payments', array( $this, 'remove_apple_pay_for_classic_checkout' ) );
		add_action( 'add_option_woocommerce_clover_payments_settings', array( $this, 'register_domain_on_activation' ), 10, 2 );
		add_action( 'update_option_woocommerce_clover_payments_settings', array( $this, 'reconcile_domain_registrations' ), 10, 2 );
	}

	/**
	 * Registers the rewrite rule to serve the domain association file.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function register_domain_association_endpoint(): void {
		$regex    = '^' . preg_quote( self::DOMAIN_ASSOCIATION_FILE_DIR ) . '/' . preg_quote( self::DOMAIN_ASSOCIATION_FILE_NAME ) . '$';
		$redirect = add_query_arg( self::DOMAIN_ASSOCIATION_FILE_NAME, '1', 'index.php' );

		add_rewrite_rule( $regex, $redirect, 'top' );
	}

	/**
	 * Prepends a 'test_' prefix to a setting key if the environment is sandbox.
	 *
	 * @since 2.3.0
	 * @param string      $key         The original setting key.
	 * @param string|null $environment The environment ('sandbox' or 'production').
	 * @return string The potentially prefixed key.
	 * @throws InvalidArgumentException If the environment is not specified.
	 */
	private function maybe_prepend_test( string $key, string $environment = null ): string {
		if ( empty( $environment ) ) {
			throw new InvalidArgumentException( 'Environment must be specified.' );
		}

		$prefix = ( $environment === WC_Clover_Environments::SANDBOX ) ? 'test_' : '';
		return $prefix . $key;
	}

	/**
	 * Retrieves a subset of settings for a specific environment.
	 *
	 * @since 2.3.0
	 * @param string[] $setting_base_keys The setting keys to retrieve (without 'test_' prefix).
	 * @param string   $environment       The environment ('sandbox' or 'production').
	 * @param array    $settings          The full array of plugin settings.
	 * @return array The requested settings for the specified environment.
	 * @throws InvalidArgumentException If an invalid environment is specified.
	 */
	private function get_settings_for_env( array $setting_base_keys, string $environment, array $settings ): array {
		if ( ! in_array( $environment, self::ENVIRONMENTS, true ) ) {
			throw new InvalidArgumentException( 'Invalid environment specified: ' . $environment );
		}

		return array_reduce(
			$setting_base_keys,
			function ( array $requested_settings, string $setting_key ) use ( $environment, $settings ) {
				$final_key                          = $this->maybe_prepend_test( $setting_key, $environment );
				$requested_settings[ $setting_key ] = $settings[ $final_key ] ?? '';
				return $requested_settings;
			},
			array()
		);
	}

	/**
	 * Updates the main settings array with new values for a specific environment.
	 *
	 * @since 2.3.0
	 * @param array  $new_settings The new settings to apply (using base keys).
	 * @param string $environment  The environment ('sandbox' or 'production').
	 * @param array  $settings     The full array of plugin settings to modify.
	 * @return array The updated settings array.
	 * @throws InvalidArgumentException If an invalid environment is specified.
	 */
	private function set_settings_for_env( array $new_settings, string $environment, array $settings ): array {
		if ( ! in_array( $environment, self::ENVIRONMENTS, true ) ) {
			throw new InvalidArgumentException( 'Invalid environment specified: ' . $environment );
		}

		foreach ( $new_settings as $key => $value ) {
			$final_key = $this->maybe_prepend_test( $key, $environment );
			$settings[ $final_key ] = $value;
		}

		return $settings;
	}

	/**
	 * Saves the plugin settings to the database, preventing recursion.
	 *
	 * @since 2.3.0
	 * @param array $settings The settings array to update.
	 * @return void
	 */
	private function update_settings( array $settings ): void {
		remove_action( 'update_option_woocommerce_clover_payments_settings', array( $this, 'reconcile_domain_registrations' ) );
		WC_Clover_Helper::update_clover_settings( $settings );
		add_action( 'update_option_woocommerce_clover_payments_settings', array( $this, 'reconcile_domain_registrations' ), 10, 2 );
	}

	/**
	 * Checks if the Clover payment gateway is enabled.
	 *
	 * @since 2.3.0
	 * @param array $settings The plugin settings array.
	 * @return bool True if enabled, false otherwise.
	 */
	private function is_clover_enabled( array $settings ): bool {
		$is_clover_enabled = $settings[ WC_Clover_Settings_Keys::ENABLED ] ?? 'no';
		return $is_clover_enabled === 'yes';
	}

	/**
	 * Checks if Apple Pay is enabled in the settings.
	 *
	 * @since 2.3.0
	 * @param array $settings The plugin settings array.
	 * @return bool True if enabled, false otherwise.
	 */
	private function is_apple_pay_enabled( array $settings ): bool {
		$is_apple_pay_enabled = $settings[ WC_Clover_Settings_Keys::APPLE_PAY ] ?? 'no';
		return $is_apple_pay_enabled === 'yes';
	}

	/**
	 * Filters an array of settings to find any that are empty.
	 *
	 * @since 2.3.0
	 * @param array $settings The settings to check.
	 * @return array An array of settings that have empty values.
	 */
	private function get_invalid_settings( array $settings ): array {
		return array_filter( $settings, fn( $value ) => empty( $value ) );
	}

	/**
	 * Adds the query var for the domain association file endpoint.
	 *
	 * @since 2.3.0
	 * @param array $query_vars The existing query vars.
	 * @return array The modified query vars.
	 */
	public function register_domain_association_query_var( array $query_vars ): array {
		$query_vars[] = self::DOMAIN_ASSOCIATION_FILE_NAME;
		return $query_vars;
	}

	/**
	 * Serves the plain text domain association file when the endpoint is accessed.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function serve_domain_association_file(): void {
		if ( empty( get_query_var( self::DOMAIN_ASSOCIATION_FILE_NAME ) ) ) {
			return;
		}

		$path = WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/' . self::DOMAIN_ASSOCIATION_FILE_NAME;

		if ( ! is_readable( $path ) ) {
			WC_Clover_Logger::error(
				'Domain association file is missing or not readable.',
				array( 'path' => $path )
			);
			exit;
		}

		if ( headers_sent( $file, $line ) ) {
			WC_Clover_Logger::warning(
				'Could not set Apple Pay verification header. Output started at ' . esc_html( $file ) . ':' . esc_html( $line ),
				array(
					'file' => $file,
					'line' => $line,
				)
			);

		} else {
			header( 'Content-Type: text/plain; charset=utf-8' );
		}

		readfile( $path );
		exit;
	}

	/**
	 * Resets domain registration if the site's domain name changes.
	 *
	 * This method detects if the current domain differs from the one stored in settings,
	 * and if so, deletes the old domain registration and re-registers the new one.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function reset_domain_registration_on_name_change(): void {
		$current_settings = WC_Clover_Helper::get_clover_settings();

		$stored_domain_name = $current_settings[ WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_NAME ] ?? '';

		if ( empty( $stored_domain_name ) || $this->domain_name === $stored_domain_name ) {
			return;
		}

		WC_Clover_Logger::info(
			'Apple Pay - Domain name changed; resetting domain registration.',
			array(
				'previous_domain' => $stored_domain_name,
				'new_domain'      => $this->domain_name,
			)
		);

		$requested_settings = array(
			WC_Clover_Settings_Keys::APPLE_PAY_MERCHANT_ID,
			WC_Clover_Settings_Keys::APPLE_PAY_PRIVATE_KEY,
			WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID,
		);

		foreach ( self::ENVIRONMENTS as $environment ) {
			WC_Clover_Logger::info( 'Apple Pay - Deleting old ' . strtoupper( $environment ) . ' domain.' );

			$apple_pay_settings = $this->get_settings_for_env( $requested_settings, $environment, $current_settings );

			$invalid_settings = $this->get_invalid_settings( $apple_pay_settings );
			// If any of the Apple Pay settings are empty, skip, because no domain was previously registered.
			if ( $invalid_settings ) {
				continue;
			}

			$api = $this->api_factory->create(
				$environment,
				$apple_pay_settings[ WC_Clover_Settings_Keys::APPLE_PAY_MERCHANT_ID ],
				$apple_pay_settings[ WC_Clover_Settings_Keys::APPLE_PAY_PRIVATE_KEY ]
			);

			$current_settings = $this->delete_domain_for_environment( $environment, $api, $apple_pay_settings );

			$verified_domain_uuid = $this->register_and_verify_domain( $api );
			if ( $verified_domain_uuid ) {
				$apple_pay_settings[ WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID ] = $verified_domain_uuid;
				$this->process_successful_registration( $environment, $apple_pay_settings, $current_settings );
			}
		}
	}

	/**
	 * Deletes a registered domain for a given environment and clears its settings.
	 *
	 * @since 2.3.0
	 * @param string                  $environment The environment ('sandbox' or 'production').
	 * @param WC_Clover_Apple_Pay_API $api         The API client instance.
	 * @param array                   $settings    The current plugin settings.
	 * @return array The updated settings array.
	 */
	private function delete_domain_for_environment( string $environment, WC_Clover_Apple_Pay_API $api, array $settings ): array {
		try {
			$this->delete_domain( $api, $settings[ WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID ] );

			$updated_settings = array(
				WC_Clover_Settings_Keys::APPLE_PAY_MERCHANT_ID => '',
				WC_Clover_Settings_Keys::APPLE_PAY_PRIVATE_KEY => '',
				WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID => '',
			);

			return $this->set_settings_for_env( $updated_settings, $environment, $settings );

		} catch ( Exception $e ) {
			// TODO: Add some kind of notice tell merchan to contact support to correct failed domain deregistration.
			WC_Clover_Logger::error(
				'Apple Pay - Domain name change failure; ' . $e->getMessage(),
				array(
					WC_Clover_Settings_Keys::ENVIRONMENT => $environment,
				)
			);
		}
		return $settings;
	}

	/**
	 * Calls the API to delete a registered domain.
	 *
	 * @since 2.3.0
	 * @param WC_Clover_Apple_Pay_API $api         The API client instance.
	 * @param string                  $domain_uuid The UUID of the domain to delete.
	 * @return void
	 * @throws Exception If the domain UUID is empty or the API call fails.
	 */
	private function delete_domain( WC_Clover_Apple_Pay_API $api, string $domain_uuid ): void {
		try {
			if ( empty( $domain_uuid ) ) {
				throw new Exception( 'Domain UUID is empty.' );
			}

			$response = $api->delete_domain( $domain_uuid );
			if ( $this->is_successful_response( $response, false ) ) {
				return;
			}

			throw new Exception( 'API returned a non-successful response during domain deletion.' );

		} catch ( Exception $e ) {
			throw new Exception( 'Domain deletion failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Triggers domain registration when the plugin settings are first saved.
	 *
	 * @since 2.3.0
	 * @param string $option   The option name ('woocommerce_clover_payments_settings').
	 * @param array  $settings The new settings being saved.
	 * @return void
	 */
	public function register_domain_on_activation( string $option, array $settings ): void {
		$this->handle_domain_registration_on_settings_change( array(), $settings );
	}

	/**
	 * Reconciles domain registrations when settings are updated.
	 *
	 * First, it cleans up domains if merchant IDs have changed.
	 * Then, it proceeds with the normal registration/verification flow.
	 *
	 * @since 2.3.0
	 * @param array $previous_settings The settings before the update.
	 * @param array $current_settings  The new settings being saved.
	 * @return void
	 */
	public function reconcile_domain_registrations( array $previous_settings, array $current_settings ): void {
		foreach ( self::ENVIRONMENTS as $environment ) {
			$current_settings = $this->cleanup_domain_for_environment( $environment, $current_settings );
		}

		$this->handle_domain_registration_on_settings_change( $previous_settings, $current_settings );
	}

	/**
	 * Cleans up old domain registrations if the merchant ID has changed for an environment.
	 *
	 * @since 2.3.0
	 * @param string $environment The environment ('sandbox' or 'production').
	 * @param array  $settings    The current settings array.
	 * @return array The potentially modified settings array.
	 */
	private function cleanup_domain_for_environment( string $environment, array $settings ): array {
		$requested_settings = array(
			WC_Clover_Settings_Keys::MERCHANT_ID,
			WC_Clover_Settings_Keys::APPLE_PAY_MERCHANT_ID,
			WC_Clover_Settings_Keys::APPLE_PAY_PRIVATE_KEY,
			WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID,
		);

		$apple_pay_settings = $this->get_settings_for_env( $requested_settings, $environment, $settings );

		$apple_pay_merchant_id = $apple_pay_settings[ WC_Clover_Settings_Keys::APPLE_PAY_MERCHANT_ID ];
		$current_merchant_id   = $apple_pay_settings[ WC_Clover_Settings_Keys::MERCHANT_ID ];

		// If the merchant ID is unchanged, there's nothing to do.
		if ( empty( $apple_pay_merchant_id ) || $apple_pay_merchant_id === $current_merchant_id ) {
			WC_Clover_Logger::info(
				'Apple Pay - ' . strtoupper( $environment ) . ' Merchant ID has not changed. No domain cleanup needed.',
				array(
					'previous_merchant_id' => $apple_pay_merchant_id,
					'current_merchant_id'  => $current_merchant_id,
				)
			);
			return $settings;
		}

		$invalid_settings = $this->get_invalid_settings( $apple_pay_settings );
		if ( $invalid_settings ) {
			WC_Clover_Logger::critical(
				'Apple Pay - Invalid ' . strtoupper( $environment ) .' settings detected. Aborting domain cleanup.',
				$invalid_settings
			);
			return $settings;
		}

		WC_Clover_Logger::info(
			'Apple Pay - ' . strtoupper( $environment ) . ' Merchant ID changed. Deleting old domain.',
			array(
				'previous_merchant_id'               => $apple_pay_merchant_id,
				'current_merchant_id'                => $current_merchant_id,
			)
		);

		try {
			$api = $this->api_factory->create(
				$environment,
				$apple_pay_merchant_id,
				$apple_pay_settings[ WC_Clover_Settings_Keys::APPLE_PAY_PRIVATE_KEY ]
			);

			$this->delete_domain( $api, $apple_pay_settings[ WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID ] );

			// On successful deletion, clear the domain UUID from the current settings.
			$updated_settings = array(
				WC_Clover_Settings_Keys::APPLE_PAY_MERCHANT_ID => '',
				WC_Clover_Settings_Keys::APPLE_PAY_PRIVATE_KEY => '',
				WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID => '',
			);

			$settings = $this->set_settings_for_env( $updated_settings, $environment, $settings );

		} catch ( Exception $e ) {
			WC_Clover_Logger::error(
				'Apple Pay - Failed to clean up previous ' . strtoupper( $environment ) .' domain registration: ' . $e->getMessage()
			);
		}
		return $settings;
	}

	/**
	 * Handles domain registration logic when plugin settings are updated.
	 *
	 * @since 2.3.0
	 * @param array $previous_settings The old settings.
	 * @param array $current_settings  The new settings.
	 * @return void
	 */
	public function handle_domain_registration_on_settings_change( array $previous_settings, array $current_settings ): void {
		if ( ! $this->is_clover_enabled( $current_settings ) ) {
			WC_Clover_Logger::info( 'Apple Pay - Clover Payments is not enabled. Aborting domain registration.' );
			return;
		}

		if ( ! $this->is_apple_pay_enabled( $current_settings ) ) {
			WC_Clover_Logger::info( 'Apple Pay - Apple Pay is not enabled. Aborting domain registration.' );
			return;
		}

		$requested_settings = array(
			WC_Clover_Settings_Keys::MERCHANT_ID,
			WC_Clover_Settings_Keys::PRIVATE_KEY,
			WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID,
		);

		$environment        = $current_settings[ WC_Clover_Settings_Keys::ENVIRONMENT ] ?? '';
		$apple_pay_settings = $this->get_settings_for_env( $requested_settings, $environment, $current_settings );

		$domain_uuid = $apple_pay_settings[ WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID ];
		$merchant_id = $apple_pay_settings[ WC_Clover_Settings_Keys::MERCHANT_ID ];
		$private_key = $apple_pay_settings[ WC_Clover_Settings_Keys::PRIVATE_KEY ];

		if ( ! $this->should_register_domain( $previous_settings, $domain_uuid ) ) {
			WC_Clover_Logger::info(
				'Apple Pay - Plugin or Apple Pay was not previously disabled, or the domain UUID is not empty. No domain registration needed.',
				array(
					WC_Clover_Settings_Keys::ENVIRONMENT           => $environment,
					WC_Clover_Settings_Keys::ENABLED               => $previous_settings[ WC_Clover_Settings_Keys::ENABLED ] ?? '',
					WC_Clover_Settings_Keys::APPLE_PAY             => $merchant_id ?? '',
					WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID => $domain_uuid,
				)
			);
			return;
		}

		$invalid_settings = $this->get_invalid_settings( $apple_pay_settings );
		// Don't want an empty domain UUID to prevent registration.
		unset( $invalid_settings[ WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID ] );
		if ( $invalid_settings ) {
			WC_Clover_Logger::critical(
				'Apple Pay - Invalid ' . strtoupper( $environment ) . ' settings detected. Aborting domain registration.',
				$invalid_settings
			);
			$this->disable_apple_pay( $current_settings );
			return;
		}

		$api = $this->api_factory->create( $environment, $merchant_id, $private_key );

		if ( ! $this->is_merchant_eligible_for_apple_pay( $api ) ) {
			$this->disable_apple_pay( $current_settings );
			return;
		}

		if ( $this->is_domain_already_verified( $domain_uuid, $api, $current_settings ) ) {
			return;
		}

		$verified_domain_uuid = $this->register_and_verify_domain( $api );
		if ( empty( $verified_domain_uuid ) ) {
			$this->disable_apple_pay( $current_settings );
			return;
		}

		$apple_pay_settings[ WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID ] = $verified_domain_uuid;
		$this->process_successful_registration( $environment, $apple_pay_settings, $current_settings );
	}

	/**
	 * Determines if the domain registration process should proceed.
	 *
	 * Registration should happen if Apple Pay was previously disabled or if no domain UUID is currently stored.
	 *
	 * @since 2.3.0
	 * @param array  $settings    The previous settings array.
	 * @param string $domain_uuid The current domain UUID.
	 * @return bool True if registration should proceed, false otherwise.
	 */
	private function should_register_domain( array $settings, string $domain_uuid ): bool {
		$was_clover_enabled    = $this->is_clover_enabled( $settings );
		$was_apple_pay_enabled = $this->is_apple_pay_enabled( $settings );

		$was_previously_disabled = ! ( $was_clover_enabled && $was_apple_pay_enabled );

		// If Apple Pay or the plugin were previously disabled, or the domain UUID is empty, proceed with registration.
		// Domain UUID is only empty if a perviously registered domain was deregistered, or if no domain UUID was previously stored (i.e., new install).
		return $was_previously_disabled || empty( $domain_uuid );
	}

	/**
	 * Checks if the current merchant is eligible for Apple Pay via the API.
	 *
	 * @since 2.3.0
	 * @param WC_Clover_Apple_Pay_API $api The API client instance.
	 * @return bool True if eligible, false otherwise.
	 */
	public function is_merchant_eligible_for_apple_pay( WC_Clover_Apple_Pay_API $api ): bool {
		try {
			if ( $this->fetch_apple_pay_support_status( $api ) ) {
				WC_Clover_Logger::info( 'Apple Pay - Merchant eligibility confirmed.' );
				return true;
			}

			WC_Clover_Logger::info( 'Apple Pay - Merchant is ineligible for Apple Pay.' );

			$this->notice = WC_Clover_Apple_Pay_Error::get_error( WC_Clover_Apple_Pay_Error::INELIGIBLE );

		} catch ( Exception $e ) {
			WC_Clover_Logger::error( 'Apple Pay - Unable to confirm merchant eligibility. ' . $e->getMessage() );
			$this->notice = WC_Clover_Apple_Pay_Error::get_error( WC_Clover_Apple_Pay_Error::DEFAULT );
		}

		return false;
	}

	/**
	 * Fetches the Apple Pay support status from the payment configs endpoint.
	 *
	 * @since 2.3.0
	 * @param WC_Clover_Apple_Pay_API $api The API client instance.
	 * @return bool The value of the 'apple_pay.supported' property.
	 * @throws Exception If the API response is bad or the config is malformed.
	 */
	private function fetch_apple_pay_support_status( WC_Clover_Apple_Pay_API $api ): bool {
		$response = $api->get_payment_configs();

		if ( ! $this->is_successful_response( $response ) ) {
			throw new Exception(
				$response[ WC_Clover_Response_Keys::DATA ]->error->message ?? 'Bad response from API.'
			);
		}

		$config = $response[ WC_Clover_Response_Keys::DATA ] ?? null;

		if ( ! isset( $config->apple_pay->supported ) ) {
			throw new Exception( 'Invalid merchant e-comm config: The "apple_pay.supported" property is missing.' );
		}

		return (bool) $config->apple_pay->supported;
	}

	/**
	 * Checks if the domain is already registered and verified with the API.
	 *
	 * @since 2.3.0
	 * @param string                  $domain_uuid The domain UUID to check.
	 * @param WC_Clover_Apple_Pay_API $api         The API client instance.
	 * @param array                   $settings    The current plugin settings.
	 * @return bool True if the domain is already verified, false otherwise.
	 */
	private function is_domain_already_verified( string $domain_uuid, WC_Clover_Apple_Pay_API $api, array $settings ): bool {
		try {
			$domains = $this->get_domains( $api );
			$domain  = WC_Clover_Helper::array_find( $domains, fn( $domain ) => $domain_uuid === ( $domain->uuid ?? null ) );

			// Null if no UUID match is found. Should continue with registration.
			if ( is_null( $domain ) ) {
				return false;
			}

			if ( ( $domain->status ?? '' ) === 'VERIFIED' ) {
				WC_Clover_Logger::info( 'Apple Pay - Domain is already verified.' );
				return true;
			}

			WC_Clover_Logger::critical(
				'Apple Pay - Domain has a non-verified status.',
				array(
					'domain_status' => $domain->status ?? 'UNKNOWN',
				)
			);

			$this->notice = WC_Clover_Apple_Pay_Error::get_error( WC_Clover_Apple_Pay_Error::CRITICAL );

		} catch ( Exception $e ) {
			WC_Clover_Logger::error( 'Apple Pay - Failed to retrieve domains. ' . $e->getMessage() );
			$this->notice = WC_Clover_Apple_Pay_Error::get_error( WC_Clover_Apple_Pay_Error::DEFAULT );
		}
		// Return true to prevent further execution since we can't confirm the domain status.
		$this->disable_apple_pay( $settings );
		return true;
	}


	/**
	 * Fetches the list of registered domains from the API.
	 *
	 * @since 2.3.0
	 * @param WC_Clover_Apple_Pay_API $api The API client instance.
	 * @return array The list of domain objects.
	 * @throws Exception If the API returns a non-successful response.
	 */
	private function get_domains( WC_Clover_Apple_Pay_API $api ): array {
		$response = $api->get_domains();
		if ( $this->is_successful_response( $response ) ) {
			return $response[ WC_Clover_Response_Keys::DATA ];
		}

		throw new Exception(
			$response[ WC_Clover_Response_Keys::DATA ]->error->message ?? 'Bad response from API.'
		);
	}

	/**
	 * Executes the full domain registration and verification process.
	 *
	 * @since 2.3.0
	 * @param WC_Clover_Apple_Pay_API $api The API client instance.
	 * @return string The verified domain UUID, or an empty string on failure.
	 */
	private function register_and_verify_domain( WC_Clover_Apple_Pay_API $api ): string {
		if ( $this->is_permalink_structure_plain() ) {
			WC_Clover_Logger::error( 'Apple Pay - Cannot register domain with plain permalink structure.' );
			$this->notice = WC_Clover_Apple_Pay_Error::get_error(WC_Clover_Apple_Pay_Error::INVALID_PERMALINK );
			return '';
		}

		try {
			flush_rewrite_rules();

			$registered_domain_uuid = $this->register_domain( $api );
			$verified_domain_uuid   = $this->verify_domain( $registered_domain_uuid, $api );

			WC_Clover_Logger::info( 'Apple Pay - Domain successfully registered and verified!' );

			return $verified_domain_uuid;

		} catch ( Exception $e ) {
			WC_Clover_Logger::error( 'Apple Pay - ' . $e->getMessage() );
			$this->notice = WC_Clover_Apple_Pay_Error::get_error( WC_Clover_Apple_Pay_Error::DEFAULT );

			return '';
		}
	}

	/**
	 * Checks if the site's permalink structure is set to "Plain".
	 *
	 * @since 2.3.0
	 * @return bool True if the permalink structure is "Plain", false otherwise.
	 */
	private function is_permalink_structure_plain(): bool {
		$permalink_structure = get_option( 'permalink_structure' );
		return $permalink_structure === '';
	}

	/**
	 * Registers the site's domain with the API.
	 *
	 * @since 2.3.0
	 * @param WC_Clover_Apple_Pay_API $api The API client instance.
	 * @return string The UUID of the newly registered domain.
	 * @throws Exception If registration fails or the response is invalid.
	 */
	private function register_domain( WC_Clover_Apple_Pay_API $api ): string {
		try {
			$response = $api->register_domain( $this->domain_name );

			if ( ! $this->is_successful_response( $response ) ) {
				throw new Exception(
					$response[ WC_Clover_Response_Keys::DATA ]->error->message ?? 'Bad response from API.'
				);
			}

			$domain = $response[ WC_Clover_Response_Keys::DATA ];
			if ( ! isset( $domain->uuid ) ) {
				throw new Exception( 'UUID not found.' );
			}

			return $domain->uuid;

		} catch ( Exception $e ) {
			throw new Exception( 'Domain registration failed. ' . $e->getMessage() );
		}
	}

	/**
	 * Verifies a registered domain with the API. Attempts to roll back on failure.
	 *
	 * @since 2.3.0
	 * @param string                  $domain_uuid The UUID of the domain to verify.
	 * @param WC_Clover_Apple_Pay_API $api         The API client instance.
	 * @return string The verified domain's UUID.
	 * @throws Exception If verification fails. A critical failure includes a failed rollback.
	 */
	private function verify_domain( string $domain_uuid, WC_Clover_Apple_Pay_API $api ): string {
		try {
			$response = $api->verify_domain( $domain_uuid );
			if ( ! $this->is_successful_response( $response ) ) {
				throw new Exception(
					$response[ WC_Clover_Response_Keys::DATA ]->error->message ?? 'Bad response from API.'
				);
			}

			$domain = $response[ WC_Clover_Response_Keys::DATA ];
			if ( 'VERIFIED' !== ( $domain->status ?? '' ) ) {
				throw new Exception( 'Invalid domain status.' );
			}

			return $domain->uuid;

		} catch ( Exception $verification_exception ) {
			WC_Clover_Logger::error(
				'Domain verification failed, attempting rollback. Reason: ' . $verification_exception->getMessage()
			);
			try {
				$this->delete_domain( $api, $domain_uuid );

			} catch ( Exception $deletion_exception ) {
				throw new Exception(
					'CRITICAL: Domain verification failed and the subsequent rollback (delete) also failed. Original error: ' . $verification_exception->getMessage() . ' Rollback error: ' . $deletion_exception->getMessage(),
					0,
					$verification_exception
				);
			}
			throw new Exception( 'Domain verification failed and was rolled back.', 0, $verification_exception );
		}
	}

	/**
	 * Updates and saves settings after a successful domain registration.
	 *
	 * @since 2.3.0
	 * @param string $environment        The current environment ('sandbox' or 'production').
	 * @param array  $apple_pay_settings The subset of Apple Pay settings.
	 * @param array  $current_settings   The full array of current plugin settings.
	 * @return void
	 */
	private function process_successful_registration( string $environment, array $apple_pay_settings, array $current_settings ): void {
		$current_settings[ WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_NAME ] = $this->domain_name;

		$updated_settings = array(
			WC_Clover_Settings_Keys::APPLE_PAY_MERCHANT_ID => $apple_pay_settings[ WC_Clover_Settings_Keys::MERCHANT_ID ],
			WC_Clover_Settings_Keys::APPLE_PAY_PRIVATE_KEY => $apple_pay_settings[ WC_Clover_Settings_Keys::PRIVATE_KEY ],
			WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID => $apple_pay_settings[ WC_Clover_Settings_Keys::APPLE_PAY_DOMAIN_UUID ],
		);

		$current_settings = $this->set_settings_for_env( $updated_settings, $environment, $current_settings );

		$this->update_settings( $current_settings );
	}

	/**
	 * Disables the Apple Pay setting and updates the database.
	 *
	 * @since 2.3.0
	 * @param array $settings The settings array to modify.
	 * @return void
	 */
	private function disable_apple_pay( array $settings ): void {
		$settings[ WC_Clover_Settings_Keys::APPLE_PAY ] = 'no';
		$this->update_settings( $settings );
	}

	/**
	 * Checks if an API response is successful.
	 *
	 * @since 2.3.0
	 * @param array $response         The API response array.
	 * @param bool  $data_is_required Set to false for calls (e.g., DELETE) where a 200 OK with no data is a success.
	 * @return bool True if the response is successful.
	 */
	private function is_successful_response( array $response, bool $data_is_required = true ): bool {
		$is_status_ok = ( $response[ WC_Clover_Response_Keys::STATUS_CODE ] ?? 0 ) === 200;
		if ( ! $is_status_ok ) {
			return false;
		}

		if ( ! $data_is_required ) {
			return true;
		}

		$data = $response[ WC_Clover_Response_Keys::DATA ] ?? null;
		return ! isset( $data->error );
	}

	/**
	 * Removes the Apple Pay setting for classic checkout.
	 *
	 * @since 2.3.0
	 * @param array $settings The current settings array for the Clover payment gateway.
	 * @return array The modified settings array with the Apple Pay setting removed if the block-based checkout is not detected.
	 */
	public function remove_apple_pay_for_classic_checkout( array $settings ): array {
		$checkout_page_id = wc_get_page_id( 'checkout' );
		if ( $checkout_page_id === -1 || ! has_block( 'woocommerce/checkout', $checkout_page_id ) ) {
			unset( $settings[ WC_Clover_Settings_Keys::APPLE_PAY ] );
		}
		return $settings;
	}

	/**
	 * Displays an admin notice in the WordPress dashboard.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function display_notice(): void {
		if ( empty( $this->notice ) ) {
			return;
		}
		$class = esc_attr( 'notice notice-error is-dismissible' );
		printf( '<div class="%1$s" ><p><strong>%2$s</strong></p></div>', $class, $this->notice );
	}
}

$api_factory      = new WC_Clover_Apple_Pay_API_Factory();
$clover_apple_pay = new WC_Clover_Apple_Pay( $api_factory );
$clover_apple_pay->init();
