<?php

namespace Kestrel\WooCommerce\First_Data\Clover\Integrations;

defined( 'ABSPATH' ) or exit;

use Kestrel\WooCommerce\First_Data\Clover\Gateway\Credit_Card;
use Kestrel\WooCommerce\First_Data\Clover\Gateway\Payment_Form;
use SkyVerge\WooCommerce\PluginFramework\v5_15_2 as Framework;
use WC_First_Data as Clover;
use WC_Payment_Token;
use WC_Payment_Tokens;
use WC_Subscription;
use WC_Subscriptions_Cart;
use WCS_Payment_Tokens;

/**
 * Subscriptions integration with Clover.
 *
 * @since 5.2.3
 */
class Subscriptions {

	/** @var string user meta key to store a deleted token */
	private static string $clover_deleted_token_user_meta_key = '_clover_deleted_token';

	/**
	 * Subscriptions integration constructor.
	 *
	 * Clover only allows one token per customer, so we need to allow the removal of a token tied to a subscription to allow the customer to add a new one.
	 *
	 * @since 5.2.3
	 */
	public function __construct() {

		// disallow paying for a subscription with a new card if a token is already present
		add_filter( 'wc_first_data_clover_credit_card_payment_form_new_payment_method_input_html', [ $this, 'prevent_entering_new_card_with_existing_token' ], 10, 3 );

		// filter the Payment Methods list to allow the removal of a token tied to a subscription
		add_filter( 'wc_subscriptions_allow_subscription_token_deletion', [ $this, 'allow_subscription_tied_token_removal' ], 10, 2 );
		add_filter( 'woocommerce_payment_methods_list_item', [ $this, 'add_remove_subscription_tied_token_button' ], 10, 2 );
		add_action( 'woocommerce_before_account_payment_methods', [ $this, 'add_subscription_tied_token_removal_notice_container' ] );

		// offer to link a new payment method to orphaned subscriptions
		add_action( 'woocommerce_account_payment_methods_column_subscriptions', [ $this, 'add_link_new_payment_method_to_orphaned_subscriptions' ], 10, 2 );
		// add notice upon adding new payment method to remind of orphaned subscriptions
		add_action( 'wc_payment_gateway_first_data_clover_credit_card_payment_method_added', [ $this, 'add_notice_new_payment_method_with_orphaned_subscriptions' ], 10, 3 );

		// Handle the removal of a token tied to a subscription when confirmed via AJAX
		add_action( 'wp_ajax_clover_delete_subscription_tied_token', [ $this, 'remove_subscription_tied_token' ] );
		add_action( 'wp_ajax_nopriv_clover_delete_subscription_tied_token', [ $this, 'remove_subscription_tied_token' ] );

		// Allow subscriptions to change a payment method when the subscription is not active
		add_filter( 'woocommerce_can_subscription_be_updated_to_new-payment-method', [ $this, 'maybe_allow_payment_method_change' ], 15, 2 );
	}


	/**
	 * Prevents the customer from entering a new card in the payment form when a token is already present.
	 *
	 * @since 5.2.3
	 * @internal
	 *
	 * @param string|mixed $html
	 * @param Payment_Form|mixed $payment_form
	 * @return string|mixed
	 */
	public function prevent_entering_new_card_with_existing_token( $html, $payment_form ) {

		if ( ! $payment_form instanceof Payment_Form ) {
			return $html;
		}

		$gateway = $payment_form->get_gateway();

		// do not render option to add new card (which would be forced to be tokenized) if there's already one saved
		if ( $gateway instanceof Credit_Card && ( isset( $_GET['change_payment_method'] ) || ( class_exists( WC_Subscriptions_Cart::class ) && WC_Subscriptions_Cart::cart_contains_subscription() ) ) && ! empty( $payment_form->has_tokens() ) ) {
			return "\n" . $gateway->get_single_payment_method_limitation_notice();
		}

		return $html;
	}


	/**
	 * Replaces the button that would prevent a customer from removing a token tied to a subscription.
	 *
	 * @see WCS_My_Account_Payment_Methods::flag_subscription_payment_token_deletions()
	 *
	 * @since 5.2.3
	 * @internal
	 *
	 * @param bool|mixed $allow_removal
	 * @param WC_Payment_Token|mixed $payment_token
	 * @return bool|mixed
	 */
	public function allow_subscription_tied_token_removal( $allow_removal, $payment_token ) {

		// bail if something else is allowing removal of the token
		if ( $allow_removal || ! $payment_token instanceof WC_Payment_Token ) {
			return $allow_removal;
		}

		if ( Clover::CLOVER_CREDIT_CARD_GATEWAY_ID !== $payment_token->get_gateway_id() ) {
			return $allow_removal;
		}

		return true;
	}


	/**
	 * Replaces the button that would prevent a customer from removing a token tied to a subscription.
	 *
	 * @see WCS_My_Account_Payment_Methods::flag_subscription_payment_token_deletions()
	 *
	 * @since 5.2.3
	 * @internal
	 *
	 * @param array<string, mixed>|mixed $payment_token_data
	 * @param WC_Payment_Token|mixed $payment_token
	 * @return array<string, mixed>|mixed
	 */
	public function add_remove_subscription_tied_token_button( $payment_token_data, $payment_token ) {

		if ( ! is_array( $payment_token_data ) || ! $payment_token instanceof WC_Payment_Token ) {
			return $payment_token_data;
		}

		if ( Clover::CLOVER_CREDIT_CARD_GATEWAY_ID !== $payment_token->get_gateway_id() ) {
			return $payment_token_data;
		}

		if ( ! class_exists( WCS_Payment_Tokens::class ) || 0 === count( WCS_Payment_Tokens::get_subscriptions_from_token( $payment_token ) ) ) {
			return $payment_token_data;
		}

		// this handling is similar to what Subscriptions does to My Payment methods buttons basically, so we have a CSS class to play with
		$payment_token_data['actions']['clover_deletion_error']        = $payment_token_data['actions']['delete'];
		$payment_token_data['actions']['clover_deletion_error']['url'] = '#delete_subscription_tied_token?token_id=' . $payment_token->get_id();

		unset( $payment_token_data['actions']['delete'] );

		return $payment_token_data;
	}


	/**
	 * Adds a notice container to show a notice when a token tied to a subscription is attempted to be removed.
	 *
	 * The attributes within the notice container thus output will be used in JavaScript to toggle the notice and handle an AJAX request.
	 *
	 * @since 5.2.3
	 * @internal
	 *
	 * @return void
	 */
	public function add_subscription_tied_token_removal_notice_container() : void {

		// the notice is hidden on load, and only shown when a token delete request is made ?>
		<div id="clover_delete_token_warning"
			style="display: none;"
			data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'clover_delete_token' ) ); ?>"
			data-message="<?php esc_attr_e( 'Deleting this payment method will disable automated billing on your subscription(s) until you add a new payment method.', 'woocommerce-first-data' ); ?>"
			data-confirm="<?php esc_attr_e( 'Confirm delete', 'woocommerce-first-data' ); ?>">
			<?php wc_print_notice( '', 'error' ); ?>
		</div>
		<?php
	}


	/**
	 * Handles the removal of a token tied to a subscription when confirmed via AJAX.
	 *
	 * @since 5.2.3
	 * @internal
	 *
	 * @return void
	 */
	public function remove_subscription_tied_token() : void {

		wp_verify_nonce( sanitize_key( $_POST['security'] ?: '' ), 'clover_delete_token' );

		$token_id = sanitize_text_field( $_POST['token_id'] ?: '' );

		if ( ! $token_id ) {
			wp_send_json_error( 'Missing token ID' );
		}

		if ( ! class_exists( WCS_Payment_Tokens::class ) ) {
			wp_send_json_error( 'Subscriptions token handler not found' );
		}

		$token = WCS_Payment_Tokens::get( $token_id );

		if ( ! $token instanceof WC_Payment_Token ) {
			wp_send_json_error( 'Token not found' );
		}

		$result = $token->delete( true );

		if ( ! $result ) {
			wp_send_json_error( 'Failed to delete token' );
		}

		// stores the deleted token to keep track of it
		update_user_meta( $token->get_user_id( 'edit' ), static::$clover_deleted_token_user_meta_key, $token->get_token() );

		wp_send_json_success( 'Token successfully deleted' );
	}


	/**
	 * Adds a notice to remind the customer of orphaned subscriptions when adding a new payment method.
	 *
	 * @since 5.2.3
	 * @internal
	 *
	 * @param string|mixed $new_token
	 * @param int|mixed $customer_id
	 * @param Framework\SV_WC_Payment_Gateway_API_Create_Payment_Token_Response|mixed $response
	 * @return void
	 */
	public function add_notice_new_payment_method_with_orphaned_subscriptions( $new_token, $customer_id, $response ) : void {

		if ( ! $new_token || ! $customer_id || ! $response instanceof Framework\SV_WC_Payment_Gateway_API_Create_Payment_Token_Response || ! $response->transaction_approved() ) {
			return;
		}

		if ( get_current_user_id() !== $customer_id ) {
			return;
		}

		$old_token = get_user_meta( $customer_id, static::$clover_deleted_token_user_meta_key, true );

		if ( ! $old_token || $old_token === $new_token ) {
			return;
		}

		$orphaned_subscriptions = [];

		foreach ( $this->get_orphaned_subscriptions_for_user( $customer_id, $old_token ) as $subscription ) {
			$orphaned_subscriptions[ '#' . $subscription->get_order_number() ] = $subscription->get_change_payment_method_url();
		}

		if ( ! empty( $orphaned_subscriptions ) && ! is_admin() ) {

			$subscription_links = [];

			foreach ( $orphaned_subscriptions as $subscription_number => $subscription_link ) {
				$subscription_links[] = '<a href="' . esc_url( $subscription_link ) . ' " target="_blank" style="font-weight:bold;">' . esc_html( $subscription_number ) . '</a>';
			}

			Framework\SV_WC_Helper::wc_add_notice( sprintf(
				_n(
					'You have a subscription (%1$s) that is not linked to any payment method! %2$sLink your subscription to this payment method%3$s.',
					'You have subscriptions (%1$s) that are not linked to any payment method! %2$sLink your subscriptions to this payment method%3$s.',
					count( $orphaned_subscriptions ),
					'woocommerce-first-data'
				),
				Framework\SV_WC_Helper::list_array_items( $subscription_links ),
				'<a href="' . esc_url( current( $orphaned_subscriptions ) ) . '" target="_blank" style="font-weight:bold;">',
				'</a>'
			), 'notice' );
		}
	}


	/**
	 * Offers to link orphaned subscriptions to a new payment method in the Subscriptions column in the Payment Methods list.
	 *
	 * @since 5.2.3
	 * @internal
	 *
	 * @param array<string, mixed>|mixed $payment_data
	 * @return void
	 */
	public function add_link_new_payment_method_to_orphaned_subscriptions( $payment_data ) : void {

		if ( ! is_array( $payment_data ) || ! isset( $payment_data['method']['gateway'], $payment_data['token'] ) || Clover::CLOVER_CREDIT_CARD_GATEWAY_ID !== $payment_data['method']['gateway'] ) {
			return;
		}

		$user_id   = get_current_user_id();
		$token     = $this->get_payment_token( $user_id, $payment_data['token'] ?: null );
		$new_token = $token instanceof WC_Payment_Token ? $token->get_token( 'edit' ) : null;
		$old_token = get_user_meta( $user_id, static::$clover_deleted_token_user_meta_key, true );

		if ( ! $old_token || ! $new_token || $old_token === $new_token ) {
			return;
		}

		$orphaned_subs = [];

		foreach ( $this->get_orphaned_subscriptions_for_user( $user_id, $old_token ) as $subscription ) {
			if ( $old_token === $subscription->get_meta( '_wc_first_data_clover_credit_card_payment_token' ) ) {
				/* translators: Placeholder: %s - Subscription number */
				$orphaned_subs[] = '<a href="' . esc_url( $subscription->get_change_payment_method_url() ) . '">' . sprintf( esc_html__( 'Link to #%s', 'woocommerce-first-data' ), $subscription->get_order_number() ) . '</a>';
			}
		}

		if ( ! empty( $orphaned_subs ) ) {
			echo '<br>' . implode( '<br>', $orphaned_subs );
		}
	}


	/**
	 * Gets subscriptions for a user matching a deleted token.
	 *
	 * @since 5.2.3
	 *
	 * @param int $user_id
	 * @param string $deleted_token
	 * @return WC_Subscription[]
	 */
	private function get_orphaned_subscriptions_for_user( int $user_id, string $deleted_token ) : array {

		$subscriptions = wcs_get_users_subscriptions( $user_id );
		$orphaned_subs = [];

		foreach ( $subscriptions as $subscription ) {
			if ( $deleted_token === $subscription->get_meta( '_wc_first_data_clover_credit_card_payment_token' ) ) {
				$orphaned_subs[] = $subscription;
			}
		}

		return $orphaned_subs;
	}


	/**
	 * Gets a token object from a token string.
	 *
	 * @since 5.2.3
	 *
	 * @param int $user_id
	 * @param string|null $token
	 * @return WC_Payment_Token|null
	 */
	private function get_payment_token( int $user_id, ?string $token ) : ?WC_Payment_Token {

		if ( ! $token ) {
			return null;
		}

		$token_id = null;

		foreach ( WC_Payment_Tokens::get_customer_tokens( $user_id ) as $user_token ) {
			if ( $user_token->get_token() === $token ) {
				$token_id = $user_token->get_id();
				break;
			}
		}

		if ( ! $token_id ) {
			return null;
		}

		return WC_Payment_Tokens::get( $token_id ) ?: null;
	}

	/**
	 * Allows the change of a payment method for a subscription when the subscription is not active.
	 *
	 * @see WC_Subscription::can_be_updated_to()
	 *
	 * @since 5.2.6
	 * @internal
	 *
	 * @param bool $subscription_can_be_changed
	 * @param WC_Subscription $subscription
	 * @return bool
	 */
	public function maybe_allow_payment_method_change( $subscription_can_be_changed, $subscription ) {

		if ( $subscription->get_payment_method() === Clover::CLOVER_CREDIT_CARD_GATEWAY_ID ) {
			return true;
		}

		return $subscription_can_be_changed;
	}
}
