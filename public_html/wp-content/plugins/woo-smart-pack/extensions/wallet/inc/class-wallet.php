<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'WooZnd_Wallet' ) ) {

    class WooZnd_Wallet {

        public static function Init() {


            //Funds Deposit
            add_action( 'template_redirect', array( new self(), 'AddProductToCart' ) );
            add_filter( 'woocommerce_product_get_price', array( new self(), 'GetDepositPrice' ), 9999999, 2 );
            add_filter( 'woocommerce_product_variation_get_price', array( new self(), 'GetDepositPrice' ), 9999999, 2 );

            add_action( 'woocommerce_get_cart_item_from_session', array( new self(), 'GetDepositPriceSession' ), 99, 2 );
            add_filter( 'woocommerce_add_order_item_meta', array( new self(), 'AddDePositPriceToOrderItemMeta' ), 10, 3 );
            add_filter( 'woocommerce_hidden_order_itemmeta', array( new self(), 'HideOrderItemMetaFields' ) );

            add_action( 'woocommerce_order_status_completed', array( new self(), 'DepositOrderCompleted' ) );
            add_action( 'woocommerce_order_status_processing', array( new self(), 'DepositOrderCompleted' ) );
            add_action( 'woocommerce_order_status_on-hold', array( new self(), 'DepositOrderCompleted' ) );

            add_filter( 'woocommerce_available_payment_gateways', array( new self(), 'FilterPaymentMethods' ), 1 );

            add_filter( 'woocommerce_is_purchasable', array( new self(), 'IsPurchasable' ), 10, 2 );
        }

        //Funds Deposit
        public static function AddProductToCart() {

            if ( !isset( $_POST[ 'wznd_wallet_deposit' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

                return;
            }

            $deposit_amount = sanitize_text_field( wp_unslash( $_POST[ 'wznd_wallet_deposit' ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

            if ( empty( $deposit_amount ) ) {

                return;
            }

            $product_id = WooZnd_Util::GetOption( 'deposit_product_id', 0 );

            if ( $product_id <= 0 ) {

                return;
            }

            WC()->session->set( '_wznd_deposit_price', ( ( float ) $deposit_amount ) );
            WC()->cart->add_to_cart( $product_id );

            wp_safe_redirect( wc_get_cart_url() );

            exit();
        }

        public static function IsPurchasable( $is_pirchasable, $product ) {

            if ( !$product->exists() ) {

                return $is_pirchasable;
            }

            $deposit_product_id = WooZnd_Util::GetOption( 'deposit_product_id', 0 );

            if ( !$deposit_product_id ) {

                return $is_pirchasable;
            }

            $product_id = $product->get_id();

            if ( !$product_id ) {

                return $is_pirchasable;
            }

            if ( $product_id == $deposit_product_id ) {

                return true;
            }


            return $is_pirchasable;
        }

        public static function GetDepositPrice( $price, $product ) {

            if ( !self::is_cart_or_checkout() ) {

                return $price;
            }

            if ( !$product->exists() ) {

                return $price;
            }

            $deposit_product_id = WooZnd_Util::GetOption( 'deposit_product_id', 0 );

            if ( !$deposit_product_id ) {

                return $price;
            }

            $product_id = $product->get_id();

            if ( !$product_id ) {

                return $price;
            }

            if ( $product_id != $deposit_product_id ) {

                return $price;
            }

            if ( !WC()->cart ) {

                return $price;
            }

            if ( WC()->cart->is_empty() ) {

                return $price;
            }


            $deposit_price = WC()->session->get( '_wznd_deposit_price', false );

            if ( !$deposit_price ) {

                return $price;
            }

            if ( empty( $deposit_price ) ) {

                return $price;
            }

            return ( float ) $deposit_price;
        }

        public static function GetDepositPriceSession( $item, $values ) {

            $deposit_price = WC()->session->get( '_wznd_deposit_price', 0 );

            if ( $deposit_price > 0 && $item[ 'product_id' ] == WooZnd_Util::GetOption( 'deposit_product_id', 0 ) ) {

                $item[ '_wznd_deposit_price' ] = isset( $values[ '_wznd_deposit_price' ] ) ? $values[ '_wznd_deposit_price' ] : $deposit_price;
            }

            return $item;
        }

        public static function AddDePositPriceToOrderItemMeta( $item_id, $values ) {

            if ( isset( $values[ '_wznd_deposit_price' ] ) ) {

                wc_add_order_item_meta( $item_id, '_wznd_deposit_price', $values[ '_wznd_deposit_price' ] );
            }
        }

        public static function HideOrderItemMetaFields( $fields ) {

            $fields[] = '_wznd_deposit_price';

            return $fields;
        }

        public static function DepositOrderCompleted( $order_id ) {

            $order = wc_get_order( $order_id );

            if ( !$order ) {

                return;
            }

            $wallet_credited = $order->get_meta( 'wallet_credited', true );

            if ( $wallet_credited == 'on' ) {

                return;
            }

            if ( WooZnd_Util::get_order_status_suppassed( $order->get_status(), WooZnd_Util::GetOption( 'make_deposit_on_order_status', 'processing' ) ) == false ) {

                return;
            }

            $amount = 0;

            if ( count( $order->get_items() ) > 0 ) {

                foreach ( $order->get_items() as $item ) {

                    if ( !$item->get_type() == 'line_item' ) {

                        continue;
                    }

                    $deposit = $item->get_meta( '_wznd_deposit_price', true );

                    if ( !$deposit ) {

                        continue;
                    }

                    if ( empty( $deposit ) ) {

                        continue;
                    }

                    $qty = $item->get_quantity();

                    $amount += ((( float ) $deposit) * $qty);
                }
            }

            $user = $order->get_user();
            $user_id = $order->get_user_id();

            if ( $amount > 0 ) {

                $trans_id = WooZnd_WalletTransactionDB::CreditWallet( $user_id, $amount, WOOZND_WALLET_TRANSANCTION_DEPOSIT, $user->user_login, WooZnd_Util::GetOption( 'wallet_deposit_remark', esc_html__( 'Funds Deposit', 'woo-smart-pack' ) ) );

                if ( $trans_id > 0 ) {

                    WooZnd_WalletTransactionDB::TransactionComplete( $trans_id, $user->user_login, WooZnd_Util::GetOption( 'wallet_deposit_remark', esc_html__( 'Funds Deposit', 'woo-smart-pack' ) ) );
                    WooZnd_WalletTransactionDB::SetTransactionOrderId( $trans_id, $order_id );

                    do_action( 'wooznd_wallet_deposit_processed', $trans_id, $order_id, $user_id );

                    $order->update_meta_data( 'wallet_credited', 'on' );
                }
            }
        }

        public static function FilterPaymentMethods( $gateways ) {

            if ( is_admin() ) {

                return $gateways;
            }

            $found = false;

            if ( isset( WC()->cart->cart_contents ) ) {

                foreach ( WC()->cart->cart_contents as $value ) {

                    if ( $value[ 'product_id' ] == WooZnd_Util::GetOption( 'deposit_product_id', 0 ) ) {

                        $found = true;
                    }
                }
            }

            if ( $found == false ) {
               
                return $gateways;
            }
            

            $payment_methods = WooZnd_Util::GetOption( 'deposit_payment_methods', '' );

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

        private static function is_cart_or_checkout() {

            if ( is_cart() ) {

                return true;
            }

            if ( is_checkout() ) {

                return true;
            }

            return false;
        }

    }

}


