<?php
	/**
	 * File for handling Apple Pay error messages.
	 *
	 * @package woo-clv-payments
	 * @since   2.3.0
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	/**
	 * Final class for handling Apple Pay errors.
	 *
	 * This is a static utility class that provides a centralized way to access
	 * specific, translatable error messages related to Apple Pay functionality.
	 *
	 * @since 2.3.0
	 */
	final class WC_Clover_Apple_Pay_Error {

		/**
		 * Stores the array of error messages.
		 *
		 * Lazily loaded by set_errors().
		 *
		 * @since 2.3.0
		 * @var   ?array
		 */
		private static ?array $errors = null;

		/**
		 * Error code for when an account is not eligible for Apple Pay.
		 *
		 * @since 2.3.0
		 */
		public const INELIGIBLE = 'ineligible';

		/**
		 * Error code for critical account issues preventing Apple Pay enablement.
		 *
		 * @since 2.3.0
		 */
		public const CRITICAL = 'critical';

		/**
		 * Error code for when the site's permalink structure is invalid.
		 *
		 * @since 2.3.0
		 */
		public const INVALID_PERMALINK = 'invalid_permalink';

		/**
		 * Default error code for when the service cannot be reached.
		 *
		 * @since 2.3.0
		 */
		public const DEFAULT = 'default';

		/**
		 * Private constructor to prevent instantiation.
		 *
		 * @since 2.3.0
		 */
		private function __construct() {
		}

		/**
		 * Initializes and sets the error messages array.
		 *
		 * The messages are translatable strings for different error scenarios.
		 *
		 * @since 2.3.0
		 *
		 * @return void
		 */
		private static function set_errors(): void {
			self::$errors = array(
				self::INELIGIBLE => wp_sprintf(
				/* translators: 1: Opening HTML element, 2. Closing HTML element */
					__( 'Apple Pay cannot be enabled. Please contact Clover Support at %1$swordpress@clover.com%2$s to review your account\'s eligibility for this feature.', 'woo-clv-payments' ),
					'<a href="mailto:wordpress@clover.com" >',
					'</a>'
				),
				self::CRITICAL   => wp_sprintf(
				/* translators: 1: Opening HTML element, 2. Closing HTML element */
					__( 'Apple Pay cannot be enabled. Please contact Clover Support at %1$swordpress@clover.com%2$s to review your account.', 'woo-clv-payments' ),
					'<a href="mailto:wordpress@clover.com" >',
					'</a>'
				),
				self::INVALID_PERMALINK => wp_sprintf(
				/* translators: 1: Opening HTML element, 2. Closing HTML element */
				__( 'Apple Pay cannot be enabled. Permalinks cannot be "Plain"; click %1$shere%2$s to change your Permalink structure.', 'woo-clv-payments' ),
				'<a href="' . admin_url( 'options-permalink.php' ) . '" >',
				'</a>'
				),
				self::DEFAULT    => __(
					'We were unable to connect to the Apple Pay service. Try again.',
					'woo-clv-payments'
				),
			);
		}

		/**
		 * Retrieves an error message by its code.
		 *
		 * @since 2.3.0
		 *
		 * @param string $error_code The key for the desired error message (e.g., self::INELIGIBLE).
		 * @return string The corresponding error message, or an empty string if the code is not found.
		 */
		public static function get_error( string $error_code ): string {
			if ( is_null( self::$errors ) ) {
				self::set_errors();
			}
			return self::$errors[ $error_code ] ?? '';
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 2.3.0
		 */
		private function __clone() {
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 2.3.0
		 *
		 * @throws \Exception When unserialization is attempted.
		 */
		public function __wakeup() {
			throw new \Exception( 'Cannot unserialize a singleton.' );
		}
	}
