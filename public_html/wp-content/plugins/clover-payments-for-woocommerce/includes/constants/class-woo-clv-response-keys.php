<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	final class WC_Clover_Response_Keys {

		private function __construct() {}

		/**
		 * Key for accessing the data in a response.
		 *
		 * @since 2.3.0
		 * @var string
		 */
		public const DATA = 'data';

		/**
		 * Key for accessing the URL in a response.
		 *
		 * @since 2.3.0
		 * @var string
		 */
		public const URL = 'url';

		/**
		 * Key for accessing the status code in a response.
		 *
		 * @since 2.3.0
		 * @var string
		 */
		public const STATUS_CODE = 'status_code';
	}
