<?php
/**
 * Plugin Name: Clover Payments for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/clover-payments-for-woocommerce/
 * Description: Accepting payments in WooCommerce using Clover eCommerce.
 * Version: 2.3.1
 * Requires Plugins: woocommerce
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Author: Clover eCommerce
 * Author URI: https://www.clover.com
 * License: BSD-3-Clause-Clear
 * License URI: https://directory.fsf.org/wiki/License:BSD-3-Clause-Clear
 * Text Domain: woo-clv-payments
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WC_CLOVER_PAYMENTS_VERSION', '2.3.1' );
define( 'WC_CLOVER_PAYMENTS_MAIN_FILE', __FILE__ );
define( 'WC_CLOVER_PAYMENTS_PLUGIN_URL', untrailingslashit( plugin_dir_url( WC_CLOVER_PAYMENTS_MAIN_FILE ) ) );
define( 'WC_CLOVER_PAYMENTS_PLUGIN_PATH', untrailingslashit( plugin_dir_path( WC_CLOVER_PAYMENTS_MAIN_FILE ) ) );

add_action( 'plugins_loaded', 'woocommerce_clover_payments_init' );

function woocommerce_clover_payments_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'woo_clv_no_wc_notice' );
		return;
	}

	require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/class-woo-clv-payments.php';
	WC_Clover_Payments::get_instance();
}

function woo_clv_no_wc_notice(): void {
	?>
	<div class="error"><p><strong>
				<?php
					echo esc_html__(
						'WooCommerce Clover Payment Gateway: We require WooCommerce to be installed and active. You can download it here',
						'woo-clv-payments'
					);
				?>
				<a href="https://woocommerce.com/" target="_blank">WooCommerce</a></strong></p></div>
	<?php
}
