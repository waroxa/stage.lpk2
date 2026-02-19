<?php
/**
 * Gateway class
 *
 * @package woo-clover-payments
 */

if (! defined('ABSPATH') ) {
    exit; // Exit if accessed directly.
}

/**
 * Class WOO_CLV_GATEWAY
 */
class WOO_CLV_GATEWAY extends WC_Payment_Gateway_CC
{

    /**
     * *
     *
     * @param  type $order_id Order id.
     * @param  type $amount   Refund value.
     * @param  type $reason   Refund reason.
     * @return WP_Error|boolean
     * @throws Exception Handler.
     */
    public function process_refund( $order_id, $amount = null, $reason = '' ) {
        try {
            if (is_null($amount) ) {
                return new WP_Error(
                    'clover_error',
                    sprintf(
                        __('Please enter refund value.', 'woo-clv-payments')
                    )
                );
            }
            $order = wc_get_order($order_id);

            if (in_array($order->get_status(), array( 'refunded', 'failed' ), true) ) {
                return new WP_Error(
                    'clover_error',
                    sprintf(
                        __('Please select a valid order to refund.', 'woo-clv-payments')
                    )
                );
            }

            $ordertotal = $order->get_total();
            $ispartial  = $this->isPartial($ordertotal, $amount);

            if ($ispartial && ! $order->get_date_paid() ) {
                return new WP_Error(
                    'clover_error',
                    sprintf(
                        __('Unable to process partial void transaction.', 'woo-clv-payments')
                    )
                );
            }

			$response = WC_Clover_API::create_refund( $order, $amount, $ispartial );

			WC_Clover_Logger::info( 'Refund response.', array(
				'Response' => $response
			) );

            $processed_response = $this->handle_payments_response( $response );

            if ( $processed_response['message'] === 'succeeded' ) {
                $refund_message = $processed_response['message'];

                if ($order->get_date_paid() ) {
                    $refund_message = sprintf(
                    /* translators: %1$s: refund amount, %2$s: refund transaction ID, %3$s: refund status */
                    __( 'Refunded %1$s - Refund ID: %2$s - Status: %3$s', 'woo-clv-payments' ),
						$amount, $processed_response['TXN_ID'], $refund_message
                    );

                    $order->update_meta_data('_clover_refund_id', $processed_response['TXN_ID']);

                } else {
                    $refund_message = sprintf(
                    /* translators: %1$s: refund amount, %2$s: void transaction ID, %3$s: refund status */
                    __( 'Voided %1$s - Void ID: %2$s - Status: %3$s', 'woo-clv-payments' ),
						$amount, $processed_response['TXN_ID'], $refund_message
                    );

                    $order->update_meta_data('_clover_void_id', $processed_response['TXN_ID']);
                }

                $order->add_order_note($refund_message);
                return true;

            } else {
                $failure_message = WOO_CLV_ERRORMAPPER::get_localized_error_message( $processed_response );

                return new WP_Error(
                    'clover_error',
                    sprintf( 'Error:' . $failure_message )
                );
            }
        } catch ( Exception $e ) {
            throw new Exception($e->getMessage());
        }
    }

	/**
	 * Handles the response from the payment API.
	 *
	 * This method processes the response from the payment API and returns an array
	 * containing the transaction ID, reference number, and message. It handles different
	 * status codes and maps them to appropriate messages and error codes.
	 *
	 * @param array $response The response array from the payment API.
	 * @return array The processed response containing transaction details and status message.
	 */
	public function handle_payments_response( array $response ): array {
	    $processed_response = array();

	    if ( $response['status_code'] === 200 ) {
	        $processed_response['TXN_ID']  = $response['data']->id;
	        $processed_response['ref_num'] = $response['data']->ref_num ?? '';
	        $processed_response['message'] = $response['data']->status;

	    } elseif ( $response['status_code'] === 0 ) {
	        $processed_response['message']    = __('Unable to complete transaction.', 'woo-clv-payments');
	        $processed_response['error_code'] = 'unexpected';

	    } else {
	        if ( isset( $response['data']->error ) ) {
	            $processed_response['error_code'] = $response['data']->error->code ?? '';
	            $processed_response['message']    = $response['data']->error->message ?? '';

	        } else {
	            if ( $response['status_code'] === 400 ) {
	                $processed_response['message']    = $response['data']->message ?? 'Invalid Source';
	                $processed_response['error_code'] = 'invalid_details';

	            } elseif ( $response['status_code'] === 401 ) {
	                $processed_response['message']    = $response['data']->message ?? 'Unauthorized';
	                $processed_response['error_code'] = 'invalid_key';

	            } else {
	                $processed_response['message']    = __('Unable to complete transaction.', 'woo-clv-payments');
	                $processed_response['error_code'] = 'unexpected';
	            }
	        }
	    }
	    return $processed_response;
	}

    /**
     * Partial check.
     *
     * @param  type $ordertotal Total.
     * @param  type $refund     Value.
     * @return boolean
     */
    private function isPartial( $ordertotal, $refund )
    {
        if ($refund < $ordertotal ) {
            return true;
        }
        return false;
    }
}
