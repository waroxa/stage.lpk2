<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include_once ('inc/class-refund-settings.php');

if ( WooZnd_Util::GetOption( 'enable_refund', 'yes' ) == 'yes' ) {

    include_once ('inc/refund-activate.php');
    include_once ('inc/class-refund-db.php');
    include_once ('inc/class-refund.php');


    add_action( 'woocommerce_init', 'WooZnd_Refund::Init' );

    //Admin Menu
    add_action( "admin_menu", 'wznd_refund_addmenu' );

    function wznd_refund_addmenu() {

        wooznd_refund_request_update();
        wooznd_refund_request_approve();
        wooznd_refund_request_reject();


        $capability = 'manage_woocommerce';
        $wallets_slug = 'wznd-wallet';

        $request_pending_count = WooZnd_RefundDB::GetRequestsCount( '', WOOZND_WALLET_REFUND_REQUEST_PENDING );
        $request_pending_text = ($request_pending_count > 0) ? ' <span class="awaiting-mod update-plugins count-1"><span class="processing-count">' . $request_pending_count . '</span></span>' : '';

        add_submenu_page( $wallets_slug, esc_html__( 'Refund Requests', 'woo-smart-pack' ), esc_html__( 'Refund Requests', 'woo-smart-pack' ) . $request_pending_text, $capability, $wallets_slug . '-refunds', 'wznd_refund_list' );
    }

    if ( !function_exists( 'wznd_refund_list' ) ) {

        function wznd_refund_list() {

            $wooznd_nonce_action = basename( __FILE__ );
            include ('admin/templates/refunds-list.php');
        }

    }

    if ( !function_exists( 'wooznd_refund_request_update' ) ) {

        function wooznd_refund_request_update() {

            if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] == "POST" && isset( $_POST[ 'action_name' ] ) && $_POST[ 'action_name' ] == 'update-refund' ) {

                $is_valid_nonce = ( isset( $_POST[ 'wznd_refund_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_refund_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

                if ( !$is_valid_nonce ) {
                    return;
                }

                $req_id = isset( $_POST[ 'refund_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'refund_id' ] ) ) : '';

                if ( empty( $req_id ) ) {

                    return;
                }

                if ( isset( $_POST[ 'delete' ] ) && sanitize_text_field( wp_unslash( $_POST[ 'delete' ] ) ) == 'Delete' ) {

                    WooZnd_RefundDB::DeleteRequest( $req_id );

                    return;
                }

                $order = wc_get_order( $req_id );

                if ( !$order ) {

                    return;
                }

                $status = isset( $_POST[ 'status' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'status' ] ) ) : '';

                if ( $status == WOOZND_WALLET_REFUND_REQUEST_APROVED ) {

                    $req_amount = isset( $_POST[ 'request_amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'request_amount' ] ) ) : '';

                    if ( empty( $req_amount ) ) {

                        return;
                    }

                    $reason = isset( $_POST[ 'reason' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'reason' ] ) ) : '';

                    $trans_id = WooZnd_RefundDB::ProcessRequest( $req_id, $req_amount, $reason, false );

                    if ( $trans_id > 0 ) {

                        wc_create_refund(
                                array( 'order_id' => $req_id,
                                    'amount' => $req_amount,
                                    'reason' => $reason,
                                    'date' => $order->get_date_modified()
                                ) );
                    }
                }

                if ( $status == WOOZND_WALLET_REFUND_REQUEST_REJECTED ) {

                    WooZnd_RefundDB::CancelRequest( $req_id );
                }
            }
        }

    }

    if ( !function_exists( 'wooznd_refund_request_approve' ) ) {

        function wooznd_refund_request_approve() {

            if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] == "POST" && isset( $_POST[ 'action_name' ] ) && $_POST[ 'action_name' ] == 'complete-refund' ) {

                $is_valid_nonce = ( isset( $_POST[ 'wznd_refund_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_refund_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

                if ( !$is_valid_nonce ) {

                    return;
                }

                $req_id = isset( $_POST[ 'refund_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'refund_id' ] ) ) : '';

                if ( empty( $req_id ) ) {

                    return;
                }

                $req_amount = isset( $_POST[ 'request_amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'request_amount' ] ) ) : '';

                if ( empty( $req_amount ) ) {

                    return;
                }

                $order = wc_get_order( $req_id );

                if ( !$order ) {

                    return;
                }

                $trans_id = WooZnd_RefundDB::ProcessRequest( $req_id, $req_amount, '' );

                if ( $trans_id > 0 ) {

                    wc_create_refund(
                            array( 'order_id' => $req_id,
                                'amount' => $req_amount,
                                'reason' => '',
                                'date' => $order->get_date_modified()
                            ) );
                }
            }
        }

    }

    if ( !function_exists( 'wooznd_refund_request_reject' ) ) {

        function wooznd_refund_request_reject() {

            if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] == "POST" && isset( $_POST[ 'action_name' ] ) && $_POST[ 'action_name' ] == 'cancel-refund' ) {

                $is_valid_nonce = ( isset( $_POST[ 'wznd_refund_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_refund_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

                if ( !$is_valid_nonce ) {

                    return;
                }

                $req_id = isset( $_POST[ 'refund_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'refund_id' ] ) ) : '';

                if ( empty( $req_id ) ) {

                    return;
                }

                WooZnd_RefundDB::CancelRequest( $req_id );
            }
        }

    }
}


