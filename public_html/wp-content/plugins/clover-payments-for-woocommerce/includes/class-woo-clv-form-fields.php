<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

Class WC_Clover_Form_Fields {

	public static function init(): void {
		$self = new self();
		add_filter( 'wc_clover_form_fields', array( $self, 'get_form_fields' ) );
	}

	public static function get_form_fields( array $initial_fields ): array {
		$new_fields = array(
			'enabled' => array(
				'title'       => __( 'Clover Payments', 'woo-clv-payments' ),
				'type'        => 'select',
				'options' => array(
					'yes' => __( 'Enabled', 'woo-clv-payments' ),
					'no'  => __( 'Disabled', 'woo-clv-payments' ),
				),
				'description' => __( 'Clover Payments is available in the United States and Canada.', 'woo-clv-payments' ),
				'default'     => 'no',
				'js_trigger'  => true,
			),
			'environment' => array(
				'title'       => __( 'Environment', 'woo-clv-payments' ),
				'type'        => 'select',
				'description' => __( 'Use \'Sandbox\' for testing with a test account. Use \'Production\' for live payments.', 'woo-clv-payments' ),
				'default'     => 'production',
				'options'     => array(
					'sandbox'    => __( 'Sandbox', 'woo-clv-payments' ),
					'production' => __( 'Production', 'woo-clv-payments' ),
				),
			),
			'test_merchant_id' => array(
				'title'       => __( 'Sandbox Merchant ID', 'woo-clv-payments' ) . '*',
				'type'        => 'text',
				'description' => wp_sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag. */
				__( '%1$sLearn how%2$s to obtain a Sandbox Merchant ID.', 'woo-clv-payments' ),
					'<a href="https://docs.clover.com/dev/docs/locating-merchant-id-1" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'class'       => 'clvsdfields',
			),
			'test_publishable_key' => array(
				'title'       => __( 'Sandbox Public Key', 'woo-clv-payments' ) . '*',
				'type'        => 'text',
				'description' => wp_sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag. */
				__( '%1$sLearn how%2$s to obtain a Sandbox Public Key.', 'woo-clv-payments' ),
					'<a href="https://docs.clover.com/dev/docs/setting-up-an-api-token" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'class'       => 'clvsdfields',
			),
			'test_private_key' => array(
				'title'       => __( 'Sandbox Private Key', 'woo-clv-payments' ) . '*',
				'type'        => 'password',
				'description' => wp_sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag. */
				__( '%1$sLearn how%2$s to obtain a Sandbox Private Key.', 'woo-clv-payments' ),
					'<a href="https://docs.clover.com/dev/docs/setting-up-an-api-token" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'class'       => 'clvsdfields',
			),
			'merchant_id' => array(
				'title'       => __( 'Merchant ID', 'woo-clv-payments' ) . '*',
				'type'        => 'text',
				'description' => wp_sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag. */
				__( '%1$sLearn how%2$s to obtain a Merchant ID.', 'woo-clv-payments' ),
					'<a href="https://docs.clover.com/dev/docs/locating-merchant-id-1" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'class'       => 'clvfields',
			),
			'publishable_key' => array(
				'title'       => __( 'Public Key', 'woo-clv-payments' ) . '*',
				'type'        => 'text',
				'description' => wp_sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag. */
				__( '%1$sLearn how%2$s to obtain a Public Key.', 'woo-clv-payments' ),
					'<a href="https://docs.clover.com/dev/docs/setting-up-an-api-token" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'class'       => 'clvfields',
			),
			'private_key' => array(
				'title'       => __( 'Private Key', 'woo-clv-payments' ) . '*',
				'type'        => 'password',
				'description' => wp_sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag. */
				__( '%1$sLearn how%2$s to obtain a Private Key.', 'woo-clv-payments' ),
					'<a href="https://docs.clover.com/dev/docs/setting-up-an-api-token" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'class'       => 'clvfields',
			),
			'payment_action' => array(
				'title'   => __( 'Payment Action', 'woo-clv-payments' ),
				'type'    => 'select',
				'default' => 'charge',
				'options' => array(
					'charge'    => __( 'Authorize and Capture', 'woo-clv-payments' ),
					'authorize' => __( 'Authorize', 'woo-clv-payments' ),
				),
			),
			'apple_pay' => array(
				'title'   => __( 'Apple Pay', 'woo-clv-payments' ),
				'label'   => __( 'Enable Apple Pay', 'woo-clv-payments' ),
				'type'    => 'select',
				'options' => array(
					'yes' => __( 'Enabled', 'woo-clv-payments' ),
					'no'  => __( 'Disabled', 'woo-clv-payments' ),
				),
				'default' => 'no',
			),
			'title' => array(
				'title'       => __( 'Payment Section title', 'woo-clv-payments' ),
				'type'        => 'text',
				'description' => __( 'Appears as the title of the payment form on the checkout page.', 'woo-clv-payments' ),
				'default'     => __( 'Credit / Debit Card', 'woo-clv-payments' )
			),
			'debug' => array(
				'title' => __( 'Logging', 'woo-clv-payments' ),
				'type' => 'select',
				'options' => array(
					'yes' => __( 'Enabled', 'woo-clv-payments' ),
					'no' => __( 'Disabled', 'woo-clv-payments' ),
				),
				'description' => wp_sprintf(
				/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag. */
				__( '%1$sView logs%2$s', 'woo-clv-payments' ),
					wp_sprintf(
						'<a href="%s" target="_blank" rel="noopener noreferrer">',
						admin_url( 'admin.php?page=wc-status&tab=logs' )
					),
					'</a>'
				),
				'default' => 'yes',
			),
		);

		return array_merge( $new_fields, $initial_fields );
	}
}

WC_Clover_Form_Fields::init();
