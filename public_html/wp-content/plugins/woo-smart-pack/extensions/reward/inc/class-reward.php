<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'WooZnd_Reward' ) ) {

    class WooZnd_Reward {

        public static function Init() {

            add_action( 'woocommerce_order_status_completed', array( new self(), 'RewardOrderCompleted' ) );
            add_action( 'woocommerce_order_status_processing', array( new self(), 'RewardOrderCompleted' ) );
            add_action( 'woocommerce_order_status_on-hold', array( new self(), 'RewardOrderCompleted' ) );


            //reward info
            add_action( 'woocommerce_single_product_summary', array( new self(), 'DisplayProductRewardInfo' ), 6 );
            add_filter( 'woocommerce_get_item_data', array( new self(), 'DisplayCartRewardInfo' ), 10, 2 );

            add_filter( 'woocommerce_display_item_meta', array( new self(), 'DisplayFronOrderRewardInfo' ), 10, 2 );

            add_action( 'woocommerce_after_order_itemmeta', array( new self(), 'DisplayAdminOrderRewardInfo' ), 10, 4 );

            add_filter( 'woocommerce_add_order_item_meta', array( new self(), 'AddRewardInfoOrderItemMeta' ), 10, 3 );
            add_filter( 'woocommerce_get_cart_item_from_session', array( new self(), 'AddRewardInfoCartItemMeta' ), 20, 3 );
            add_filter( 'woocommerce_hidden_order_itemmeta', array( new self(), 'HideOrderItemMetaFields' ) );

            add_filter( 'woocommerce_available_payment_gateways', array( new self(), 'FilterPaymentMethods' ), 1 );


            //Mail
            add_action( 'wooznd_reward_processed', array( new self(), 'OrderRewardProcessed' ), 10, 4 );
        }

        public static function HideOrderItemMetaFields( $fields ) {
          
            $fields[] = '_wznd_reward_product_id';
            
            return $fields;
        }

        public static function AddRewardInfoCartItemMeta( $item_data, $values, $key ) {
            
            if ( WooZnd_Util::GetOption( 'enable_purchase_reward', 'yes' ) == 'no' ) {
            
                return $item_data;
            }

            $product_id = $item_data[ 'product_id' ];

            $enable_reward = get_post_meta( $product_id, "_wznd_enable_reward", true );
          
            if ( $enable_reward != 'yes' ) {
            
                return $item_data;
            }
            
            $item_data[ '_wznd_reward_product_id' ] = isset( $values[ '_wznd_reward_product_id' ] ) ? $values[ '_wznd_reward_product_id' ] : $product_id;
            
            return $item_data;
        }

        public static function AddRewardInfoOrderItemMeta( $item_id, $values ) {
          
            if ( isset( $values[ '_wznd_reward_product_id' ] ) ) {
            
                wc_add_order_item_meta( $item_id, '_wznd_reward_product_id', $values[ '_wznd_reward_product_id' ] );
            }
        }

        public static function DisplayProductRewardInfo() {
            
            $product_id = get_the_ID();
            
            if ( WooZnd_Util::GetOption( 'enable_purchase_reward', 'yes' ) == 'no' ) {
            
                return;
            }

            $enable_reward = get_post_meta( $product_id, "_wznd_enable_reward", true );
            
            if ( $enable_reward != 'yes' ) {
            
                return;
            }
            
            $credit_info = get_post_meta( $product_id, "_wznd_reward_credit_info", true );
            
            if ( !empty( $credit_info ) ) {
                
                $allowed_html = WooZnd_Init::get_instance()->get_allow_html();
            
                echo '<div class="wooznd_reward_info">' . wp_kses( $credit_info, $allowed_html ) . '</div>';
            }
        }

        public static function DisplayCartRewardInfo( $item_data, $cart_item ) {

            $product_id = $cart_item[ 'product_id' ];

            if ( WooZnd_Util::GetOption( 'enable_purchase_reward', 'yes' ) == 'no' ) {

                return $item_data;
            }

            $enable_reward = get_post_meta( $product_id, "_wznd_enable_reward", true );

            if ( $enable_reward != 'yes' ) {

                return $item_data;
            }

            $credit_info = get_post_meta( $product_id, "_wznd_reward_credit_info", true );

            if ( !empty( $credit_info ) ) {

                $item_data[ '_wznd_reward_info' ][ 'key' ] = esc_html__( 'Info', 'woo-smart-pack' );
                $item_data[ '_wznd_reward_info' ][ 'value' ] = $credit_info;
            }


            return $item_data;
        }

        public static function DisplayFronOrderRewardInfo( $output, $class_meta ) {

            $meta_list = array();

            $product_id = $class_meta->get_product_id();

            if ( WooZnd_Util::GetOption( 'enable_purchase_reward', 'yes' ) == 'no' ) {

                return $output;
            }

            $enable_reward = get_post_meta( $product_id, "_wznd_enable_reward", true );

            if ( $enable_reward != 'yes' ) {

                return $output;
            }

            $credit_info = get_post_meta( $product_id, "_wznd_reward_credit_info", true );

            $formatted_meta = $class_meta->get_formatted_meta_data( '' );

            foreach ( $formatted_meta as $meta ) {

                if ( $meta->key == '_wznd_reward_product_id' ) {

                    $meta_list[] = '<dt class="variation-wznd_reward_info">' . esc_html__( 'Info', 'woo-smart-pack' ) . '</dt>';
                    $meta_list[] = '<dd class="variation-wznd_reward_info">' . $credit_info . '</dd>';
                }
            }


            if ( !empty( $credit_info ) ) {

                $meta_list[] = '<dt class="variation-wznd_reward_info">' . esc_html__( 'Info', 'woo-smart-pack' ) . '</dt>';
                $meta_list[] = '<dd class="variation-wznd_reward_info">Meda</dd>';
            }

            $output = '<dl class="variation">' . implode( '', $meta_list ) . '</dl>';

            return $output;
        }

        public static function DisplayAdminOrderRewardInfo( $item_id, $item, $product ) {

            if ( WooZnd_Util::GetOption( 'enable_purchase_reward', 'yes' ) == 'no' ) {

                return;
            }

            $product_id = 0;

            if ( !isset( $product ) ) {

                return;
            }

            if ( method_exists( $product, 'get_id' ) ) {

                $product_id = $product->get_id();
            } else if ( isset( $product->id ) ) {

                $product_id = $product->id;
            } else {

                return;
            }

            if ( $product_id <= 0 ) {
                return;
            }

            $enable_reward = get_post_meta( $product_id, "_wznd_enable_reward", true );

            if ( $enable_reward != 'yes' ) {

                return;
            }

            $credit_info = get_post_meta( $product_id, "_wznd_reward_credit_info", true );


            $all_meta_data = get_metadata( 'order_item', $item_id, "", "" );

            if ( !empty( $credit_info ) ) {
                ?>
                <div class="view">
                    <table class="display_meta" cellspacing="0">
                        <?php
                        foreach ( $all_meta_data as $data_meta_key => $value ) {
                            if ( $data_meta_key == '_wznd_reward_product_id' ) {
                                ?>
                                <tr>                                    
                                    <td colspan="2"><p><?php echo wp_kses_post( $credit_info ); ?></p></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </table>
                </div>
                <?php
            }
        }

        public static function RewardOrderCompleted( $order_id ) {

            if ( WooZnd_Util::GetOption( 'enable_purchase_reward', true ) == false ) {

                return;
            }

            $order = wc_get_order( $order_id );

            if ( !$order ) {

                return;
            }

            $wallet_rewarded = $order->get_meta( 'wallet_rewarded', true );

            if ( $wallet_rewarded == 'on' ) {

                return;
            }

            if ( WooZnd_Util::get_order_status_suppassed( $order->get_status(), WooZnd_Util::GetOption( 'make_deposit_on_order_status', 'completed' ) ) == false ) {

                return;
            }

            //Check Payment Method
            $allowed = false;
            $payment_methods = WooZnd_Util::GetOption( 'purchase_reward_payment_methods', '' );
            $order_payment_method = wc_get_payment_gateway_by_order( $order );

            if ( empty( $payment_methods ) ) {

                $allowed = true;
            } else {

                foreach ( $payment_methods as $method ) {

                    if ( $order_payment_method->id == $method ) {

                        $allowed = true;
                    }
                }
            }

            if ( $allowed == false ) {

                return;
            }



            $amount = 0;
            $user = $order->get_user();
            $user_id = $order->get_user_id();

            if ( count( $order->get_items() ) > 0 ) {

                foreach ( $order->get_items() as $item ) {

                    if ( $item[ 'type' ] == 'line_item' ) {

                        if ( WooZnd_Util::GetOption( 'enable_purchase_reward', 'yes' ) == 'yes' ) {

                            $product = $item->get_product();

                            $product_id = $product->get_id();

                            if ( isset( $item[ 'variation_id' ] ) && $item[ 'variation_id' ] > 0 ) {

                                $product_id = $item[ 'product_id' ];
                            }

                            $enable_reward = get_post_meta( $product_id, "_wznd_enable_reward", true );

                            if ( $enable_reward == 'yes' ) {

                                $r_amount = get_post_meta( $product_id, "_wznd_reward_credit_amount", true );
                                $reward_type = get_post_meta( $product_id, "_wznd_reward_credit_type", true );
                                $reward_remark = get_post_meta( $product_id, "_wznd_reward_credit_remark", true );

                                // Add reward amount percentage.
                                $reward_amount = $r_amount;
                                $product_price = $product->get_price();


                                if ( $reward_type == 'fixed-unit' ) {

                                    $reward_amount = $r_amount * $item[ 'qty' ];
                                }

                                if ( $reward_type == 'percent' ) {

                                    $reward_amount = ($r_amount / 100) * $product_price;
                                }

                                if ( $reward_type == 'percent-unit' ) {

                                    $reward_amount = (($r_amount / 100) * $product_price) * $item[ 'qty' ];
                                }

                                if ( empty( $reward_remark ) ) {

                                    $reward_remark = WooZnd_Util::GetOption( 'purchase_reward_remark', 'Purchase Reward' );
                                }

                                if ( $reward_amount > 0 ) {

                                    $trans_id = WooZnd_WalletTransactionDB::CreditWallet( $user_id, $reward_amount, WOOZND_WALLET_TRANSANCTION_CREDIT, $user->user_login, $reward_remark );

                                    if ( $trans_id > 0 ) {

                                        WooZnd_WalletTransactionDB::TransactionComplete( $trans_id, $user->user_login, $reward_remark );
                                        WooZnd_WalletTransactionDB::SetTransactionOrderId( $trans_id, $order_id );

                                        $order->update_meta_data( 'wallet_rewarded', 'on' );

                                        do_action( 'wooznd_reward_processed', $trans_id, $order_id, $$product_id, $user_id );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        public static function OrderRewardProcessed( $transaction_id, $order_id, $product_id, $account_id ) {

            global $wooznd_transaction, $order, $product, $wooznd_wallet;

            if ( WooZnd_Util::GetOption( 'enable_purchase_reward', true ) == false ) {

                return;
            }

            $wooznd_transaction = WooZnd_WalletTransactionDB::GetTransaction( $transaction_id );

            $order = wc_get_order( $order_id );

            if ( !$order ) {

                return;
            }

            $product = wc_get_product( $product_id );

            if ( !$product ) {

                return;
            }

            $wooznd_wallet = WooZnd_WalletAccountDB::GetAccount( $account_id );
            $subject = WooZnd_Util::GetOption( 'purchase_reward_subject', 'purchase_reward_subject' );
            $message = WooZnd_Util::GetOption( 'purchase_reward_message', 'Hi [wznd_wallet_name], <br /> Your wallet has been credited with [wznd_trans_credit] for purchasing [wznd_product_link], your new wallet balance is [wznd_wallet_current].' );

            WooZnd_Util::SendMail( $wooznd_wallet[ 'email' ], do_shortcode( $subject ), do_shortcode( $message ) );
        }

        public static function FilterPaymentMethods( $gateways ) {

            if ( WooZnd_Util::GetOption( 'enable_purchase_reward', 'yes' ) == 'no' ) {

                return $gateways;
            }

            $found = false;

            if ( isset( WC()->cart->cart_contents ) ) {

                foreach ( WC()->cart->cart_contents as $value ) {

                    if ( get_post_meta( $value[ 'product_id' ], "_wznd_enable_reward", true ) == 'yes' ) {

                        $found = true;
                    }
                }
            }

            if ( $found == false ) {

                return $gateways;
            }


            if ( is_admin() ) {

                return $gateways;
            }
            if ( WooZnd_Util::GetOption( 'purchase_reward_payment_method_filter', 'no' ) == 'no' ) {

                return $gateways;
            }

            $payment_methods = WooZnd_Util::GetOption( 'purchase_reward_payment_methods', '' );

            if ( empty( $payment_methods ) ) {

                return $gateways;
            }

            $methods = [];

            foreach ( $payment_methods as $method ) {

                if ( isset( $gateways[ $method ] ) ) {

                    $methods[ $method ] = $gateways[ $method ];
                }
            }

            return $methods;
        }

    }

}