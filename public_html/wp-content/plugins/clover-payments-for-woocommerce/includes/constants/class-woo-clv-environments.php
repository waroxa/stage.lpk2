<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	final class WC_Clover_Environments {

		private function __construct() {}

		public const PRODUCTION = 'production';

		public const SANDBOX    = 'sandbox';
	}
