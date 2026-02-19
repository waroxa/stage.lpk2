<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include_once('inc/wallet-activate.php');
include_once('inc/email.php');
include_once('inc/class-util.php');
include_once('inc/class-account-db.php');
include_once('inc/class-transaction-db.php');
include_once('shortcodes.php');
include_once('inc/payment-gateway.php');
include_once('inc/class-wallet.php');
include_once('inc/partial-payment.php');
include_once('inc/wallet_settings.php');
include_once('front-end/pages.php');
include_once('widgets/widgets.php');

add_action( 'woocommerce_init', 'WooZnd_Wallet::Init' );
add_action( 'woocommerce_init', 'WooZnd_WalletPartialPayment::Init' );

//Admin Menu
add_action( "admin_menu", 'wznd_wallet_addmenu' );

if ( !function_exists( 'wznd_wallet_addmenu' ) ) {

    function wznd_wallet_addmenu() {

        wooznd_woowallet_credit_wallet();
        wooznd_woowallet_debit_wallet();
        wooznd_woowallet_update_wallet();
        wooznd_woowallet_new_wallet();
        wooznd_update_transaction();
        wooznd_complete_transaction();
        wooznd_cancel_transaction();

        $capability = 'manage_woocommerce';
        $wallets_slug = 'wznd-wallet';

        $trans_pending_count = WooZnd_WalletTransactionDB::GetTransactionsCount( WOOZND_WALLET_TRANSANCTION_STATUS_PENDING );
        $trans_pending_text = ($trans_pending_count > 0) ? ' <span class="awaiting-mod update-plugins count-1"><span class="processing-count">' . $trans_pending_count . '</span></span>' : '';

        add_menu_page( esc_html__( 'All Wallets', 'woo-smart-pack' ), esc_html__( 'Wallets', 'woo-smart-pack' ), $capability, $wallets_slug, 'wznd_wallet_all_accounts', 'dashicons-money' );
        add_submenu_page( $wallets_slug, esc_html__( 'All Wallets', 'woo-smart-pack' ), esc_html__( 'All Wallets', 'woo-smart-pack' ), $capability, $wallets_slug );
        add_submenu_page( $wallets_slug, esc_html__( 'Transactions', 'woo-smart-pack' ), esc_html__( 'Transactions', 'woo-smart-pack' ) . $trans_pending_text, $capability, $wallets_slug . '-trans', 'wznd_wallet_all_transactions' );
    }

}
if ( !function_exists( 'wznd_wallet_all_accounts' ) ) {

    function wznd_wallet_all_accounts() {

        $wooznd_nonce_action = basename( __FILE__ );

        include dirname( __FILE__ ) . '/admin/templates/wallet-list.php';
    }

}

if ( !function_exists( 'wznd_wallet_all_transactions' ) ) {

    function wznd_wallet_all_transactions() {

        $wooznd_nonce_action = basename( __FILE__ );

        include dirname( __FILE__ ) . '/admin/templates/transactions-list.php';
    }

}

// Wallet Functions
if ( !function_exists( 'wooznd_woowallet_credit_wallet' ) ) {

    function wooznd_woowallet_credit_wallet() {

        if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] == "POST" && isset( $_POST[ 'action_name' ] ) && $_POST[ 'action_name' ] == 'credit' ) {

            $is_valid_nonce = ( isset( $_POST[ 'wznd_wallet_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_wallet_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

            if ( !$is_valid_nonce ) {

                return;
            }
            
            $issued_by = wp_get_current_user()->user_login;

            $status = isset( $_POST[ 'status' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'status' ] ) ) : -1;

            $posted_user_id = isset( $_POST[ 'user_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'user_id' ] ) ) : '';
            

            if ( (( int ) $posted_user_id) <= 0 ) {

                return;
            }

            $posted_amount = isset( $_POST[ 'amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'amount' ] ) ) : 0;

            $posted_transtype = isset( $_POST[ 'transtype' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'transtype' ] ) ) : '';

            $posted_remark = isset( $_POST[ 'remark' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'remark' ] ) ) : '';

            $trans_id = WooZnd_WalletTransactionDB::CreditWallet( ( int ) $posted_user_id, ( float ) $posted_amount, ( int ) $posted_transtype, $issued_by, $posted_remark );

            if ( $trans_id > 0 ) {
              
                if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD ) {

                    WooZnd_WalletTransactionDB::TransactionOnHold( $trans_id, '', $posted_remark );
                }

                if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING ) {

                    WooZnd_WalletTransactionDB::TransactionProcessing( $trans_id, '', $posted_remark );
                }

                if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED ) {

                    WooZnd_WalletTransactionDB::TransactionComplete( $trans_id, $issued_by, $posted_remark );
                }

                do_action( 'wooznd_wallet_admin_transaction_' . WooZnd_Wallet_Util::TransactionTypeString( $posted_transtype ) . '_processed', $trans_id, $posted_user_id, $issued_by );
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__( 'Done!', 'woo-smart-pack' ); ?></p>
                </div>
                <?php
            } else {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html__( 'Error!', 'woo-smart-pack' ); ?></p>
                </div>
                <?php
            }
        }
    }

}

if ( !function_exists( 'wooznd_woowallet_debit_wallet' ) ) {

    function wooznd_woowallet_debit_wallet() {

        if ( !isset( $_POST[ 'action_name' ] ) ) {

            return;
        }

        if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] == "POST" && $_POST[ 'action_name' ] == 'debit' ) {

            $is_valid_nonce = ( isset( $_POST[ 'wznd_wallet_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_wallet_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

            if ( !$is_valid_nonce ) {
                return;
            }

            $issued_by = wp_get_current_user()->user_login;

            $status = isset( $_POST[ 'status' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'status' ] ) ) : -1;

            $posted_user_id = isset( $_POST[ 'user_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'user_id' ] ) ) : '';

            if ( (( int ) $posted_user_id) <= 0 ) {

                return;
            }

            $posted_amount = isset( $_POST[ 'amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'amount' ] ) ) : 0;

            $posted_transtype = isset( $_POST[ 'transtype' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'transtype' ] ) ) : '';

            $posted_remark = isset( $_POST[ 'remark' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'remark' ] ) ) : '';

            $trans_id = WooZnd_WalletTransactionDB::DebitWallet( $posted_user_id, $posted_amount, $posted_transtype, $issued_by, $posted_remark );

            if ( $trans_id > 0 ) {

                if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD ) {

                    WooZnd_WalletTransactionDB::TransactionOnHold( $trans_id, '', $posted_remark );
                }

                if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING ) {

                    WooZnd_WalletTransactionDB::TransactionProcessing( $trans_id, '', $posted_remark );
                }

                if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED ) {

                    WooZnd_WalletTransactionDB::TransactionComplete( $trans_id, $issued_by, $posted_remark );
                }

                do_action( 'wooznd_wallet_admin_transaction_' . WooZnd_Wallet_Util::TransactionTypeString( $posted_transtype ) . '_processed', $trans_id, $posted_user_id, $issued_by );
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__( 'Done!', 'woo-smart-pack' ); ?></p>
                </div>
                <?php
            } else {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html__( 'Error!', 'woo-smart-pack' ); ?></p>
                </div>
                <?php
            }
        }
    }

}

if ( !function_exists( 'wooznd_woowallet_update_wallet' ) ) {

    function wooznd_woowallet_update_wallet() {

        if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] == "POST" && isset( $_POST[ 'action_name' ] ) && $_POST[ 'action_name' ] == 'update' ) {

            $is_valid_nonce = ( isset( $_POST[ 'wznd_wallet_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_wallet_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

            if ( !$is_valid_nonce ) {

                return;
            }

            $user_id = isset( $_POST[ 'user_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'user_id' ] ) ) : '';

            if ( (( int ) $user_id) <= 0 ) {

                return;
            }

            if ( isset( $_POST[ 'delete' ] ) && $_POST[ 'delete' ] == 'Delete' ) {

                WooZnd_WalletAccountDB::DeleteAccount( $user_id );

                return;
            }


            $first_name = isset( $_POST[ 'first_name' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'first_name' ] ) ) : '';
            $last_name = isset( $_POST[ 'last_name' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'last_name' ] ) ) : '';

            $ledger = isset( $_POST[ 'ledger_balance' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ledger_balance' ] ) ) : '';

            if ( empty( $ledger ) ) {

                $ledger = 0;
            }


            $current = isset( $_POST[ 'current_balance' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'current_balance' ] ) ) : '';

            if ( empty( $current ) ) {

                $current = 0;
            }

            $spent = isset( $_POST[ 'total_spent' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'total_spent' ] ) ) : '';

            if ( empty( $spent ) ) {

                $spent = 0;
            }

            $locked = isset( $_POST[ 'locked' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'locked' ] ) ) : 0;

            $remark = isset( $_POST[ 'remark' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'remark' ] ) ) : '';

            if ( WooZnd_WalletAccountDB::UpdateWallet( $user_id, $first_name, $last_name, $ledger, $current, $spent, $locked, $remark ) == true ) {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__( 'Done!', 'woo-smart-pack' ); ?></p>
                </div>
                <?php
            } else {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html__( 'Error!', 'woo-smart-pack' ); ?></p>
                </div>
                <?php
            }
        }
    }

}

if ( !function_exists( 'wooznd_woowallet_new_wallet' ) ) {

    function wooznd_woowallet_new_wallet() {

        if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] == "POST" && isset( $_POST[ 'action_name' ] ) && $_POST[ 'action_name' ] == 'addnew' ) {

            $is_valid_nonce = ( isset( $_POST[ 'wznd_wallet_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_wallet_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

            if ( !$is_valid_nonce ) {

                return;
            }

            $user_id = isset( $_POST[ 'username' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'username' ] ) ) : '';

            if ( empty( $user_id ) ) {

                return;
            }

            $n_user = false;

            if ( is_email( $user_id ) ) {

                $n_user = get_user_by( 'email', $user_id );
            } else if ( !empty( $user_id ) ) {

                $n_user = get_user_by( 'login', $user_id );
            }

            if ( $n_user != false ) {

                if ( !WooZnd_WalletAccountDB::AccountExists( $n_user->ID ) ) {

                    $first_name = $n_user->first_name;
                    $last_name = $n_user->user_lastname;

                    $ledger_balance = isset( $_POST[ 'ledger_balance' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'ledger_balance' ] ) ) : 0;

                    if ( empty( $ledger_balance ) ) {

                        $ledger_balance = 0;
                    }

                    $current_balance = isset( $_POST[ 'current_balance' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'current_balance' ] ) ) : 0;

                    if ( empty( $current_balance ) ) {

                        $current_balance = 0;
                    }

                    $total_spent = isset( $_POST[ 'total_spent' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'total_spent' ] ) ) : 0;

                    if ( empty( $total_spent ) ) {

                        $total_spent = 0;
                    }

                    $locked = isset( $_POST[ 'locked' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'locked' ] ) ) : 0;

                    $remark = isset( $_POST[ 'remark' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'remark' ] ) ) : '';

                    $email = $n_user->user_email;

                    if ( WooZnd_WalletAccountDB::CreateAccount( $n_user->ID, $first_name, $last_name, $email, $remark ) == true ) {

                        WooZnd_WalletAccountDB::UpdateWallet( $n_user->ID, $first_name, $last_name, $ledger_balance, $current_balance, $total_spent, $locked, $remark );
                        ?>
                        <div class="notice notice-success is-dismissible">
                            <p><?php echo esc_html__( 'Done!', 'woo-smart-pack' ); ?></p>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="notice notice-error is-dismissible">
                            <p><?php echo esc_html__( 'error!', 'woo-smart-pack' ); ?></p>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php echo esc_html__( 'A wallet account for this user already exist!', 'woo-smart-pack' ); ?></p>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html__( 'I can NOT create a wallet account for unregistered users!', 'woo-smart-pack' ); ?></p>
                </div>
                <?php
            }
        }
    }

}

//Transactions Functions
if ( !function_exists( 'wooznd_update_transaction' ) ) {

    function wooznd_update_transaction() {

        if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] == "POST" && isset( $_POST[ 'action_name' ] ) && $_POST[ 'action_name' ] == 'update-transaction' ) {

            $is_valid_nonce = ( isset( $_POST[ 'wznd_wallet_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_wallet_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

            if ( !$is_valid_nonce ) {

                return;
            }

            $trans_id = !isset( $_POST[ 'trans_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'trans_id' ] ) ) : 0;


            if ( $trans_id <= 0 ) {

                return;
            }

            if ( isset( $_POST[ 'delete' ] ) && $_POST[ 'delete' ] == 'Delete' ) {

                WooZnd_WalletTransactionDB::DeleteTransaction( $trans_id );

                return;
            }

            $user_login = wp_get_current_user()->user_login;

            $account_id = isset( $_POST[ 'user_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'user_id' ] ) ) : 0;

            $status = isset( $_POST[ 'status' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'status' ] ) ) : WOOZND_WALLET_TRANSANCTION_STATUS_PENDING;

            $remark = isset( $_POST[ 'remark' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'remark' ] ) ) : '';

            $result = false;

            if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_PENDING ) {

                $result = WooZnd_WalletTransactionDB::TransactionPending( $trans_id, $remark );
            }

            if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING ) {

                $result = WooZnd_WalletTransactionDB::TransactionProcessing( $trans_id, $remark );
            }

            if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD ) {

                $result = WooZnd_WalletTransactionDB::TransactionOnHold( $trans_id, $remark );
            }

            if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED ) {

                $result = WooZnd_WalletTransactionDB::TransactionComplete( $trans_id, $user_login, $remark );
            }

            if ( $status == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {

                $result = WooZnd_WalletTransactionDB::TransactionCancel( $trans_id, $user_login, $remark );
            }
            $status_text = WooZnd_Wallet_Util::TransactionStatusString( $status, true );

            if ( $result == true ) {

                do_action( 'wooznd_wallet_admin_transaction_status_' . $status_text, $trans_id, $account_id, $user_login );
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__( 'Done!', 'woo-smart-pack' ); ?></p>
                </div>
                <?php
            }
        }
    }

}

if ( !function_exists( 'wooznd_complete_transaction' ) ) {

    function wooznd_complete_transaction() {

        if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] == "POST" && isset( $_POST[ 'action_name' ] ) && $_POST[ 'action_name' ] == 'complete-transaction' ) {

            $is_valid_nonce = ( isset( $_POST[ 'wznd_wallet_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_wallet_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

            if ( !$is_valid_nonce ) {

                return;
            }

            $user_login = wp_get_current_user()->user_login;

            $trans_id = isset( $_POST[ 'trans_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'trans_id' ] ) ) : 0;

            if ( $trans_id <= 0 ) {

                return;
            }

            $account_id = isset( $_POST[ 'user_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'user_id' ] ) ) : 0;


            if ( $account_id <= 0 ) {

                return;
            }

            if ( WooZnd_WalletTransactionDB::TransactionComplete( $trans_id, $user_login ) ) {

                do_action( 'wooznd_wallet_admin_transaction_status_completed', $trans_id, $account_id, $user_login );
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__( 'Done!', 'woo-smart-pack' ); ?></p>
                </div>
                <?php
            }
        }
    }

}

if ( !function_exists( 'wooznd_cancel_transaction' ) ) {

    function wooznd_cancel_transaction() {

        if ( isset( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] == "POST" && isset( $_POST[ 'action_name' ] ) && $_POST[ 'action_name' ] == 'cancel-transaction' ) {

            $is_valid_nonce = ( isset( $_POST[ 'wznd_wallet_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_wallet_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

            if ( !$is_valid_nonce ) {

                return;
            }

            $user_login = wp_get_current_user()->user_login;

            $trans_id = isset( $_POST[ 'trans_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'trans_id' ] ) ) : 0;


            $account_id = isset( $_POST[ 'user_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'user_id' ] ) ) : 0;


            if ( WooZnd_WalletTransactionDB::TransactionCancel( $trans_id, $user_login ) ) {

                do_action( 'wooznd_wallet_admin_transaction_status_cancelled', $trans_id, $account_id, $user_login );
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html__( 'Done!', 'woo-smart-pack' ); ?></p>
                </div>
                <?php
            }
        }
    }

}
