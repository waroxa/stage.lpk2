<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'plugins_loaded', 'wooznd_init_wallet_gateway_class' );

function wooznd_init_wallet_gateway_class() {

    class WooZnd_Wallet_Gateway extends WC_Payment_Gateway {

        private $_wallet;
        private $_user; //need to fix this, some admin users may not be able to create order with this payment method for users.
        private $_remove_failed_orders;

        public function __construct() {
            $this->id = 'wooznd_wallet';
            $this->method_title = esc_html__( 'Wallet Payments', 'woo-smart-pack' );
            $this->method_description = esc_html__( 'Allows payment using funds in the wallet database.', 'woo-smart-pack' );
            $this->has_fields = true;
            $this->_remove_failed_orders = ($this->get_option( 'remove_failed_order' ) == 'yes');
            $this->supports = apply_filters( 'wooznd_wallet_gateway_supports', array(
                'products'
                    ) );

            if ( !is_user_logged_in() ) {
                $this->enabled = 'no';
                return;
            }


            $this->_wallet = WooZnd_WalletAccountDB::GetAccount( get_current_user_id() );
            $this->_user = wp_get_current_user();



            if ( !is_admin() && is_checkout() ) {
                if ( !isset( $this->_wallet[ 'id' ] ) ) {
                    $this->enabled = 'no';
                    return;
                }
            }

            $this->init_form_fields();
            $this->init_settings();


            $this->title = preg_replace( '/{{funds}}/', wc_price( $this->_wallet[ 'current_balance' ] ), $this->get_option( 'title' ) );

            if ( isset( $this->_wallet[ 'locked' ] ) && $this->_wallet[ 'locked' ] == true ) {
                $this->description = $this->get_option( 'lock_message' );
            } else {
                $this->description = $this->get_option( 'description' );
            }


            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        /**
         * Initialise Gateway Settings Form Fields
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => esc_html__( 'Enable/Disable', 'woo-smart-pack' ),
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Enable Wallet Payment', 'woo-smart-pack' ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => esc_html__( 'Title', 'woo-smart-pack' ),
                    'type' => 'text',
                    'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'woo-smart-pack' ),
                    'default' => esc_html__( 'My Wallet', 'woo-smart-pack' ),
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => esc_html__( 'Description', 'woo-smart-pack' ),
                    'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'woo-smart-pack' ),
                    'type' => 'textarea',
                    'default' => esc_html__( 'Make payment using {{funds}} available funds in your wallet', 'woo-smart-pack' ),
                    'desc_tip' => true,
                ),
                'lock_message' => array(
                    'title' => esc_html__( 'Lock Text', 'woo-smart-pack' ),
                    'type' => 'textarea',
                    'description' => esc_html__( 'This controls the lock message which the user sees when their wallet is locked.', 'woo-smart-pack' ),
                    'default' => esc_html__( 'Your wallet account is currently locked please contact our customer care.', 'woo-smart-pack' ),
                    'desc_tip' => true,
                ),
                'low_message' => array(
                    'title' => esc_html__( 'Insufficient Funds Text', 'woo-smart-pack' ),
                    'type' => 'textarea',
                    'description' => esc_html__( 'This controls the insufficient funds message which the user sees when someone tries to puchase item without having enough money in their wallet.', 'woo-smart-pack' ),
                    'default' => esc_html__( 'Insufficient funds.', 'woo-smart-pack' ),
                    'desc_tip' => true,
                ),
                'remark' => array(
                    'title' => esc_html__( 'Transaction Remarks', 'woo-smart-pack' ),
                    'type' => 'text',
                    'description' => esc_html__( 'This controls transaction remarks made by the gateway.', 'woo-smart-pack' ),
                    'default' => esc_html__( 'Order Payment', 'woo-smart-pack' ),
                    'desc_tip' => true,
                ),
                'remove_failed_order' => array(
                    'title' => esc_html__( 'Remove Pending Orders', 'woo-smart-pack' ),
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Auto Remove Pending Payment Orders', 'woo-smart-pack' ),
                    'default' => 'no'
                ),
            );
        }

        public function payment_fields() {

            $allow_html = WooZnd_Init::get_instance()->get_allow_html();
            ?>
            <div class="woo_wallet_panel<?php echo ($this->_wallet[ 'locked' ] == true) ? ' locked' : '' ?>">
                <?php
                if ( $this->_wallet[ 'locked' ] == true ) {
                    ?>
                    <i class="dashicons dashicons-lock"></i>
                    <?php
                }
                ?>
                <div class="woo_wallet_message">
                    <?php
                    echo wp_kses( preg_replace( '/{{funds}}/', wc_price( $this->_wallet[ 'current_balance' ] ), $this->description ), $allow_html );
                    ?>
                </div>          
            </div>
            <?php
        }

        public function process_payment( $order_id ) {
            
            $order = wc_get_order( $order_id );

            if ( !$order ) {

                return;
            }

            $order_user = $order->get_user();
            $user_login = $order_user->user_login;
            $user_id = $order->get_user_id();

            $total = $order->get_total();
            $wallet_balance = $this->_wallet[ 'current_balance' ];

            if ( $this->_wallet[ 'locked' ] == true ) {
                if ( $this->_remove_failed_orders == true ) {
                    wp_delete_post( $order_id, true );
                }
                wc_add_notice( $this->get_option( 'lock_message' ), 'error' );
                return;
            }

            $znd_found = false;
            foreach ( WC()->cart->get_fees() as $fee ) {
                if ( $fee->tax_class == 'znd_wallet_partial_payment' ) {
                    $znd_found = true;
                }
            }

            if ( $znd_found == true ) {
                wc_add_notice( $this->get_option( 'low_message' ), 'error' );
                return;
            }


            if ( $wallet_balance >= $total ) {

                $trans_id = WooZnd_WalletTransactionDB::DebitWallet( $user_id, $total, WOOZND_WALLET_TRANSANCTION_PAYMENT, $user_login, $this->get_option( 'remark' ) );
                if ( $trans_id > 0 ) {
                    $compp = WooZnd_WalletTransactionDB::TransactionComplete( $trans_id, WooZnd_Util::GetOption( 'system_login', 'system_login' ), $this->get_option( 'remark' ) );
                    WooZnd_WalletTransactionDB::SetTransactionOrderId( $trans_id, $order_id );

                    if ( $compp > 0 ) {
                        $order->payment_complete( WooZnd_WalletTransactionDB::GetTransactionReceipt( $trans_id ) );
                    }
                } else if ( $total == 0 ) {
                    $order->payment_complete();
                }


                // Remove cart
                WC()->cart->empty_cart();

                // Return thankyou redirect
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url( $order )
                );
            } else {
                if ( $this->_remove_failed_orders == true ) {
                    wp_delete_post( $order_id, true );
                }
                wc_add_notice( $this->get_option( 'low_message' ), 'error' );
                return;
            }
        }

        public function process_refund( $order_id, $amount = null, $reason = '' ) {
            return (apply_filters( 'wooznd_wallet_process_refund', $amount, $reason, $order_id ) > 0);
        }

    }

    //Payment Gateway
    function wznd_add_wallet_gateway( $gateways ) {
        $gateways[] = 'WooZnd_Wallet_Gateway';
        return $gateways;
    }

    add_filter( 'woocommerce_payment_gateways', 'wznd_add_wallet_gateway' );
}
