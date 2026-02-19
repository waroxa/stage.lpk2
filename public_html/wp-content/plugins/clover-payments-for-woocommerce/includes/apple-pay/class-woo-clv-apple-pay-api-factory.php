<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Factory for creating WC_Clover_Apple_Pay_API instances.
 * @since 2.3.0
 */
class WC_Clover_Apple_Pay_API_Factory {

	private ?WC_Clover_Apple_Pay_API $api = null;

	/**
	 * Create a new WC_Clover_Apple_Pay_API instance.
	 *
	 * @since 2.3.0
	 *
	 * @param string $environment
	 * @param string $merchant_id
	 * @param string $private_key
	 * @return WC_Clover_Apple_Pay_API
	 */
	public function create( string $environment, string $merchant_id, string $private_key ): WC_Clover_Apple_Pay_API {
		if ( ! $this->api ) {
			$this->api = new WC_Clover_Apple_Pay_API( $environment, $merchant_id, $private_key );
		} else {
			$this->api->set_properties( $environment, $merchant_id, $private_key );
		}
		return $this->api;
	}
}
