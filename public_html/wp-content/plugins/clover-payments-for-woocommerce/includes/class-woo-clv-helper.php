<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WC_Clover_Helper
 *
 * This class provides helper functions for the WooCommerce Clover payment gateway.
 * It includes methods to retrieve the Clover settings from the WordPress options table.
 *
 * @since 2.2.0
 */
class WC_Clover_Helper {

    /**
     * The option name for the WooCommerce Clover payments settings.
     */
    const OPTION = 'woocommerce_clover_payments_settings';

    /**
     * Retrieves the Clover settings.
     *
     * This method fetches the Clover settings from the WordPress options table.
     *
     * @since 2.2.0
     *
     * @return array The Clover settings.
     */
    public static function get_clover_settings(): array {
        return get_option( self::OPTION, array() );
    }

	/**
	 * Replaces WP underscored locale with a Clover compatible dash.
	 *
	 * @return string
	 */
	public static function get_locale(): string {
		$wp_locale = get_locale();
		return str_replace( '_', '-', $wp_locale );
	}

	/**
	 * Creates a nonce for the Clover checkout process.
	 *
	 * This method generates a WordPress nonce to secure the Clover checkout process.
	 * The nonce is used to verify the authenticity of the checkout request.
	 *
	 * @since 2.2.0
	 *
	 * @return string The generated nonce for the Clover checkout process.
	 */
	public static function create_checkout_nonce(): string {
		return wp_create_nonce( 'clover_process_checkout' );
	}

	/**
	 * Verifies the nonce for the Clover checkout process.
	 *
	 * This method checks if the nonce provided in the POST request is valid and matches
	 * the expected action for the Clover checkout process. It ensures the request is secure.
	 *
	 * @since 2.2.0
	 *
	 * @return bool True if the nonce is valid, false otherwise.
	 */
	public static function verify_checkout_nonce(): bool {
		return isset( $_POST[ 'clover_process_checkout_nonce' ] )
		       && wp_verify_nonce( wc_clean( $_POST[ 'clover_process_checkout_nonce' ] ), 'clover_process_checkout' );
	}

	public static function get_clover_amount( float $total ): int {
		return absint( wc_format_decimal( ( $total * 100 ), wc_get_price_decimals() ) );
	}

	/**
	 * Updates the Clover settings.
	 *
	 * This method updates the Clover settings in the WordPress options table.
	 *
	 * @since 2.3.0
	 *
	 * @param array $settings The Clover settings to update.
	 * @return bool Whether the update was successful.
	 */
	public static function update_clover_settings( array $settings ): bool {
		return update_option( self::OPTION, $settings );
	}

	/**
	 * Finds and returns the first element in the array for which the callback returns true.
	 * Note: array_find() was introduced as a native function in PHP 8.4. Remove this function when the minimum PHP
	 * version for the plug-in is 8.4.
	 *
	 * @since 2.3.0
	 *
	 * @param array    $array    The array to search.
	 * @param callable $callback A function that receives ($value, $key) and returns true for the desired element.
	 * @return mixed|null        The first matching element, or null if none found.
	 */
	public static function array_find( array $array, callable $callback ) {
		foreach ( $array as $key => $value ) {
			if ( $callback( $value, $key ) ) {
				return $value;
			}
		}
		return null;
	}

	/**
	 * Implements an exponential backoff with jitter to pause execution between retries.
	 *
	 * @since 2.3.0
	 *
	 * @param int $attempt The current retry attempt number.
	 */
	public static function exponential_backoff( int $attempt ): void {
		usleep( ( 500 * ( 2 ** $attempt ) + wp_rand( 0, 500 ) ) * 1000 );
	}


	/**
	 * Adds an error message to the Clover Payments admin settings page.
	 *
	 * @since 2.3.0
	 *
	 * @param string $message  The error message to display in the Clover Payments admin settings.
	 */
	public static function add_error( string $message ): void {
		static $already_added = false;
		if ( $already_added ) {
			return;
		}
		WC_Admin_Settings::add_error( $message );
		$already_added = true;
	}

	/**
	 * Checks if the specified WooCommerce page contains the checkout block.
	 *
	 * @param string|null $page  The slug of the WooCommerce page to check (e.g., 'checkout').
	 *                           Defaults to null.
	 *
	 * @return bool True if the page contains the checkout block, false otherwise.
	 * */
	public static function has_checkout_block( string $page = null ): bool {
		$checkout_page_id = wc_get_page_id( $page );
		return $checkout_page_id > -1 && has_block( 'woocommerce/checkout', $checkout_page_id );
	}

	/**
	 * Converts the WordPress locale format to a Clover-compatible format.
	 *
	 * @since 2.3.0
	 * @return string The Clover-compatible locale string.
	 */
	public static function get_clover_compatible_locale(): string {
		return str_replace( '_', '-', get_locale() );
	}
}
