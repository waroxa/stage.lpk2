<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WC_Clover_Payments {

	private static ?WC_Clover_Payments $instance = null;

	private ?WOO_CLV_ADMIN $clover_gateway = null;

	private function __construct() {
		$this->init();
	}

	private function init() {
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/constants/class-woo-clv-settings-keys.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/constants/class-woo-clv-environments.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/constants/class-woo-clv-response-keys.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/class-woo-clv-gateway.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/class-woo-clv-admin.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/class-woo-clv-logger.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/class-woo-clv-helper.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/class-woo-clv-api.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/class-woo-clv-errormapper.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/class-woo-clv-form-fields.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/apple-pay/class-woo-clv-apple-pay-api-factory.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/apple-pay/class-woo-clv-apple-pay-api.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/apple-pay/class-woo-clv-apple-pay-error.php';
		require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/apple-pay/class-woo-clv-apple-pay.php';


		if ( is_admin() ) {
			require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/class-woo-clv-admin-capture.php';
			add_action( 'wp_ajax_wc_clv_order_capture', array( 'WOO_CLV_ADMIN_CAPTURE', 'wc_clv_order_capture' ) );
			add_action( 'wp_ajax_nopriv_wc_clv_order_capture', array( 'WOO_CLV_ADMIN_CAPTURE', 'wc_clv_order_capture' ) );
			add_filter( 'bulk_actions-edit-shop_order', array( 'WOO_CLV_ADMIN_CAPTURE', 'bulk_actions_capture_option' ) );
			add_filter( 'handle_bulk_actions-edit-shop_order', array( 'WOO_CLV_ADMIN_CAPTURE', 'handle_bulk_actions_capture' ), 10, 3 );
			add_action( 'admin_notices', array( 'WOO_CLV_ADMIN_CAPTURE', 'notice_bulk_actions_capture' ) );
		}

		add_filter( 'woocommerce_payment_gateways', array( $this, 'woo_clv_add_gateway_class' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wpo_wcpdf_after_order_data', array( $this, 'wc_clv_payment_card_info_on_invoice_page' ), 10, 2 );
		add_action( 'before_woocommerce_pay', array( $this, 'wc_clv_order_pay_order_details' ) );
		add_action( 'woocommerce_blocks_loaded', array( $this, 'clover_woocommerce_block_support' ) );
	}

	public static function get_instance(): WC_Clover_Payments{
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_clover_gateway(): ?WOO_CLV_ADMIN {
		if ( ! is_null( $this->clover_gateway ) ) {
			return $this->clover_gateway;
		}
		$this->clover_gateway = new WOO_CLV_ADMIN();
		return $this->clover_gateway;
	}

	public function woo_clv_add_gateway_class( array $gateways ): array {
		if ( class_exists( 'WooCommerce' ) ) {
			$gateways[] = 'WOO_CLV_ADMIN';
		}
		return $gateways;
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woo-clv-payments', false, dirname( plugin_basename( WC_CLOVER_PAYMENTS_MAIN_FILE ) ) . '/languages/' );
	}

	public function wc_clv_payment_card_info_on_invoice_page ( $template_type, $order ): void {
		if ( $template_type == 'invoice' ) {
			// Get all the meta-data values we need to display payment details.
			$card_details = get_post_meta( $order->id, '_card_details', true );
			if ( ! empty( $card_details ) ) {
				?>
				<tr class="payment-method">
					<th></th>
					<td><?php echo $card_details; ?></td>
				</tr>
				<?php
			}
		}
	}

	public function wc_clv_order_pay_order_details(): void {
		// ONLY RUN IF PENDING ORDER EXISTS
		if ( isset( $_GET['pay_for_order'], $_GET['key'] ) ) {
			// GET ORDER ID FROM URL BASENAME
			$order_id = intval( basename( strtok( $_SERVER["REQUEST_URI"], '?' ) ) );
			$order = wc_get_order( $order_id );
			$order_number = $order->get_order_number();
			?>
			<p><?php printf( esc_html__( 'Your order #%s placed on %s ', 'woocommerce' ), esc_html( $order->get_order_number() ), wc_format_datetime( $order->get_date_created() ) ); ?></p>

			<?php
			// INCLUDE CUSTOMER ADDRESS TEMPLATE
			//wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
		}
	}

	public function clover_woocommerce_block_support(): void {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			require_once WC_CLOVER_PAYMENTS_PLUGIN_PATH . '/includes/class-woo-clv-blocks-support.php';
			add_action( 'woocommerce_blocks_payment_method_type_registration',
				function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
					$payment_method_registry->register( new  WC_Clover_Blocks_Support() );
				}
			);
		}
	}
}
