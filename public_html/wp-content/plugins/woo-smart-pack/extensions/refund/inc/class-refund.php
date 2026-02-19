<?php

if (!defined('ABSPATH')) {
    exit;
}

if ( !class_exists( 'WooZnd_Refund' ) ) {

    class WooZnd_Refund {

        public static function Init() {
            add_filter( 'wooznd_wallet_process_refund', array( new self(), 'ProcessOrderRefund' ), 10, 3 );
            add_filter( 'wooznd_wallet_gateway_supports', array( new self(), 'GatewaySupports' ), 10, 1 );

            //Refund Meta
            add_action( 'woocommerce_order_item_add_action_buttons', array( new self(), 'OrderRefundMetaBox' ), 10, 1 );
            add_action( 'woocommerce_process_shop_order_meta', array( new self(), 'UpdatOrderRefundMeta' ), 40, 1 );

            //Refund Front-End
            add_filter( 'woocommerce_my_account_my_orders_actions', array( new self(), 'RecentOrdersActions' ), 10, 2 );
            add_action( 'init', array( new self(), 'AddRefundEndPoint' ) );
            add_action( 'template_redirect', array( new self(), 'AddRefundEndPointProcess' ) );

            //Mail
            add_action( 'wooznd_order_renfund_processed', array( new self(), 'OrderRefundProcessed' ), 10, 3 );
        }

        //Payment Gateway Refund
        public static function ProcessOrderRefund( $amount, $reason, $order_id ) {
            $trans_id = 0;
            if ( WooZnd_RefundDB::RequestExist( $order_id ) == true ) {
                $trans_id = WooZnd_RefundDB::ProcessRequest( $order_id, $amount, $reason, false );
            } else {
                $trans_id = WooZnd_RefundDB::RefundWallet( $order_id, $amount, $reason );
            }
            if ( $trans_id > 0 ) {
                return true;
            }
            return false;
        }

        public static function GatewaySupports( $supports ) {
            $supports[] = 'refunds';
            return $supports;
        }

        //Refunds MetaBox
        public static function OrderRefundMetaBox( $order ) {
            if ( WooZnd_Util::GetOption( 'enable_refund', 'yes' ) == 'no' ) {
                return;
            }
            $btn_prompt = esc_html__( 'Are you sure you wish to process this refund? this action cannot be undone.', 'woo-smart-pack' );
            $btn_text = preg_replace( '/{{price}}/', (wc_price( 0 ) ), esc_html__( 'Refund {{price}} via Wallet', 'woo-smart-pack' ) );
            echo '<div class="woo_wallet_hidden">';
            echo '<button type="submit" name="wallet_refund" value="yes" class="button button-primary wallet-refund" data-warnme="' . $btn_prompt . '"><span class="wc-order-refund-amount">' . $btn_text . '</span></button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</div>';
        }

        public static function UpdatOrderRefundMeta( $order_id ) {
            if ( WooZnd_Util::GetOption( 'enable_refund', 'yes' ) == 'no' ) {
                return;
            }
            if ( is_admin() ) {
                if ( isset( $_POST[ 'wallet_refund' ] ) && $_POST[ 'wallet_refund' ] == 'yes' ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
                    $amount = isset( $_POST[ 'refund_amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'refund_amount' ] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
                    $reason = isset( $_POST[ 'refund_reason' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'refund_reason' ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

                    $trans_id = 0;
                    if ( WooZnd_RefundDB::RequestExist( $order_id ) == true ) {
                        $trans_id = WooZnd_RefundDB::ProcessRequest( $order_id, $amount, $reason, false );
                    } else {
                        $trans_id = WooZnd_RefundDB::RefundWallet( $order_id, $amount, $reason );
                    }
                    if ( $trans_id > 0 ) {

                        $order = wc_get_order( $order_id );

                        if ( !$order ) {

                            return 0;
                        }

                        $refund_args = array(
                            'order_id' => $order_id,
                            'amount' => $amount,
                            'reason' => $reason,
                            'date' => $order->get_date_modified()
                        );

                        wc_create_refund( $refund_args );
                    }
                }
            }
        }

        //Refunds Front-End
        public static function RecentOrdersActions( $actions, $order ) {
           
            if ( WooZnd_Util::GetOption( 'enable_refund', 'yes' ) == 'no' ) {

                return $actions;
            }

            if ( !is_user_logged_in() ) {

                return $actions;
            }

            $order_id = $order->get_id();

            $url = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'wooznd_refund_order?rf_order_id=' . $order->get_order_number();
        
            $name = esc_html__( 'Refund', 'woo-smart-pack' );
            $url = wp_nonce_url( $url, basename( __FILE__ ), 'refnd' );

            $order_request = WooZnd_RefundDB::GetRequestById( $order_id );
            if ( isset( $order_request[ 'order_id' ] ) ) {
                if ( $order_request[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_APROVED ) {
                    $url = $order->get_view_order_url();
                    $name = esc_html__( 'Refund Completed', 'woo-smart-pack' );
                } else if ( $order_request[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_REJECTED ) {
                    $url = $order->get_view_order_url();
                    $name = esc_html__( 'Refund Rejected', 'woo-smart-pack' );
                } else if ( $order_request[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_PENDING ) {
                    $url = $order->get_view_order_url();
                    $name = esc_html__( 'Pending Refund', 'woo-smart-pack' );
                }
            } else if ( $order->get_status() == 'refunded' ) {
                $url = $order->get_view_order_url();
                $name = esc_html__( 'Refund Completed', 'woo-smart-pack' );
            } else if ( $order->get_status() == 'on-hold' ) {
                return $actions;
            } else if ( $order->get_status() == 'pending' ) {
                return $actions;
            }

            $actions[ 'wznd_refund_order' ] = array(
                'url' => $url,
                'name' => $name,
            );
            return $actions;
        }

        public static function AddRefundEndPoint() {
            add_rewrite_endpoint( 'wooznd_refund_order', EP_PAGES );
        }

        public static function AddRefundEndPointProcess() {

            $is_valid_nonce = ( isset( $_GET[ 'refnd' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET[ 'refnd' ] ) ), basename( __FILE__ ) ) ) ? true : false;

            if ( !$is_valid_nonce ) {

                return;
            }

            if ( WooZnd_Util::GetOption( 'enable_refund', 'yes' ) == 'no' ) {

                return;
            }

            if ( !is_user_logged_in() || !is_account_page() ) {

                return;
            }


            if ( !isset( $_GET[ 'rf_order_id' ] ) ) {

                return;
            }

            $order_id = sanitize_text_field( wp_unslash( $_GET[ 'rf_order_id' ] ) );

            if ( empty( $order_id ) ) {

                return;
            }

            $order = wc_get_order( $order_id );

            if ( $order ) {

                WooZnd_RefundDB::CreateRequest( $order_id, wp_get_current_user()->ID, $order->get_total() + $order->get_total_discount() );
            }

            wp_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/' . get_option( 'woocommerce_myaccount_orders_endpoint' ) );

            exit;
        }

        public static function OrderRefundProcessed( $transaction_id, $order_id, $account_id ) {

            global $wooznd_transaction, $order, $wooznd_wallet;

            $order = wc_get_order( $order_id );

            if ( !$order ) {

                return;
            }

            $wooznd_transaction = WooZnd_WalletTransactionDB::GetTransaction( $transaction_id );

            $wooznd_wallet = WooZnd_WalletAccountDB::GetAccount( $account_id );
            $subject = WooZnd_Util::GetOption( 'refun_mail_subject', esc_html__( 'Order Refund', 'woo-smart-pack' ) );
            $message = WooZnd_Util::GetOption( 'refun_mail_message', wp_kses_post( __( 'Hi [wznd_wallet_name], <br /> Your wallet has been credited with [wznd_trans_amount] for the refund of order [wznd_order_link], your new wallet balance is [wznd_wallet_current].', 'woo-smart-pack' ) ) );
            WooZnd_Util::SendMail( $wooznd_wallet[ 'email' ], do_shortcode( $subject ), do_shortcode( $message ) );
        }

        public static function RefundStatusString( $refund_status, $lower_case = true ) {
            switch ( $refund_status ) {
                case WOOZND_WALLET_REFUND_REQUEST_PENDING:
                    if ( $lower_case == true ) {
                        return 'pending';
                    }
                    return 'Pending';
                case WOOZND_WALLET_REFUND_REQUEST_APROVED:
                    if ( $lower_case == true ) {
                        return 'aproved';
                    }
                    return 'Aproved';
                case WOOZND_WALLET_REFUND_REQUEST_REJECTED:
                    if ( $lower_case == true ) {
                        return 'rejected';
                    }
                    return 'Rejected';
                default :
                    return '';
            }
        }

    }

}

