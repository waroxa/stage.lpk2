<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !defined( 'WOOZND_WALLET_ACCOUNT_STATUS_NONE' ) ) {

    define( 'WOOZND_WALLET_ACCOUNT_STATUS_NONE', 2 );
}

if ( !defined( 'WOOZND_WALLET_ACCOUNT_STATUS_LOCKED' ) ) {

    define( 'WOOZND_WALLET_ACCOUNT_STATUS_LOCKED', 1 );
}

if ( !defined( 'WOOZND_WALLET_ACCOUNT_STATUS_UNLOCKED' ) ) {

    define( 'WOOZND_WALLET_ACCOUNT_STATUS_UNLOCKED', 0 );
}

add_action( 'init', "WooZnd_WalletAccountDB::Init" );

if ( !class_exists( 'WooZnd_WalletAccountDB' ) ) {

    class WooZnd_WalletAccountDB {

        public static function Init() {
            if ( WooZnd_Util::GetOption( 'auto_create_new_wallet', 'yes' ) == 'yes' ) {
                self::CreateCurrentUser();
            }
            add_filter( 'update_user_metadata', array( new self(), 'UpdateMeta' ), 10, 5 );
        }

        public static function CreateCurrentUser() {
            $c_user = wp_get_current_user();
            $user_id = get_current_user_id();

            if ( $user_id > 0 && self::AccountExists( $user_id ) == false ) {
                $new_wallet_remark = WooZnd_Util::GetOption( 'new_wallet_remark', esc_html__( 'Account Created', 'woo-smart-pack' ) );
                self::CreateAccount( $user_id, $c_user->first_name, $c_user->last_name, $c_user->user_email, $new_wallet_remark );
            }
        }

        public static function CreateAccount( $id, $first_name, $last_name, $email, $remark ) {

            if ( $id <= 0 ) {
                return false;
            }
            global $wpdb;
            try {
                $account_number = WooZnd_Wallet_Util::AccNumGen();
                $current_balance = WooZnd_Util::Encrypt( 0 );
                $ledger_balance = WooZnd_Util::Encrypt( 0 );
                $total_spent = WooZnd_Util::Encrypt( 0 );

                $sql = "INSERT INTO {$wpdb->prefix}wooznd_wallet_accounts (id, account_number, first_name, last_name, email, current_balance, ledger_balance, total_spent, open_date, remark)"
                        . "VALUES (%d,%s,%s,%s,%s,%s,%s,%s,%s,%s)";

                $sql = $wpdb->prepare( $sql, $id, $account_number, $first_name, $last_name, $email, $current_balance, $ledger_balance, $total_spent, current_time( 'mysql' ), $remark ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                if ( $nofr > 0 ) {
                    do_action( 'wooznd_wallet_created', $id );
                    $initial_credit = WooZnd_Util::GetOption( 'new_wallet_freecredit', 0 );
                    $system_user = WooZnd_Util::GetOption( 'system_login', 'system_login' );
                    $free_credit_remark = WooZnd_Util::GetOption( 'new_wallet_freecredit_remark', 'free credit' );
                    $free_credit_status = WooZnd_Util::GetOption( 'new_wallet_freecredit_status', WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED );
                    if ( $initial_credit > 0 ) {
                        $tran_id = WooZnd_WalletTransactionDB::CreditWallet( $id, $initial_credit, WOOZND_WALLET_TRANSANCTION_CREDIT, $system_user, $free_credit_remark );
                        if ( $free_credit_status == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED ) {
                            WooZnd_WalletTransactionDB::TransactionComplete( $tran_id, $system_user, $free_credit_remark );
                        }
                        do_action( 'wooznd_wallet_rewarded', $id, $tran_id );
                    }
                    return true;
                } else {
                    
                    wc_get_logger()->debug( $wpdb->last_error, array( 'source' => 'wooznd-wallet' ) );
                    
                    return false;
                }
            } catch ( Exception $ex ) {

                wc_get_logger()->debug( $ex->getMessage(), array( 'source' => 'wooznd-wallet' ) );

                return false;
            }
        }

        public static function LoadAccounts( $search = "", $status, $skip = 0, $limit = 25, $orderby = "", $ordertype = 'DESC' ) {

            global $wpdb;
            $retults = array();

            try {

                $rows = array();
                $order = 'ORDER BY open_date ' . $ordertype;
                if ( $orderby == 'email' ) {
                    $order = 'ORDER BY email ' . $ordertype;
                }
                if ( $orderby == 'account_number' ) {
                    $order = 'ORDER BY account_number ' . $ordertype;
                }
                if ( $orderby == 'name' ) {
                    $order = 'ORDER BY first_name ' . $ordertype . ', last_name ' . $ordertype;
                }
                if ( $status == WOOZND_WALLET_ACCOUNT_STATUS_NONE ) {

                    $sql = "SELECT * FROM {$wpdb->prefix}wooznd_wallet_accounts WHERE (first_name LIKE %s) OR (last_name LIKE %s) OR (email LIKE %s) OR (account_number LIKE %s)" . $order . " LIMIT %d,%d";
                    $sql = $wpdb->prepare( $sql, $search, $search, $search, $search, $skip, $limit ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                } else {

                    $sql = "SELECT * FROM {$wpdb->prefix}wooznd_wallet_accounts WHERE (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR account_number LIKE %s) AND (locked=%d)" . $order . " LIMIT %d,%d";
                    $sql = $wpdb->prepare( $sql, $search, $search, $search, $search, $status, $skip, $limit ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                $rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                if ( is_array( $rows ) ) {
                    foreach ( $rows as $row ) {
                        $rw = $row;
                        $rw[ 'current_balance' ] = WooZnd_Util::Decrypt( $row[ 'current_balance' ] );
                        $rw[ 'ledger_balance' ] = WooZnd_Util::Decrypt( $row[ 'ledger_balance' ] );
                        $rw[ 'total_spent' ] = WooZnd_Util::Decrypt( $row[ 'total_spent' ] );
                        $retults[] = $rw;
                    }
                }
            } catch ( Exception $ex ) {
                wc_get_logger()->debug( $ex->getMessage(), array( 'source' => 'wooznd-wallet' ) );
                $retults = array();
            }
            return $retults;
        }

        public static function GetAccount( $id ) {
            if ( $id <= 0 ) {
                return array();
            }
            global $wpdb;
            try {

                $sql = "SELECT * FROM {$wpdb->prefix}wooznd_wallet_accounts "
                        . "WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $reslt = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $result = $reslt;
                $result[ 'current_balance' ] = WooZnd_Util::Decrypt( $reslt[ 'current_balance' ] );
                $result[ 'ledger_balance' ] = WooZnd_Util::Decrypt( $reslt[ 'ledger_balance' ] );
                $result[ 'total_spent' ] = WooZnd_Util::Decrypt( $reslt[ 'total_spent' ] );
                return $result;
            } catch ( Exception $ex ) {
                wc_get_logger()->debug( $ex->getMessage(), array( 'source' => 'wooznd-wallet' ) );
                return array();
            }
        }

        public static function DeleteAccount( $id ) {
            if ( $id <= 0 ) {
                return false;
            }
            WooZnd_WalletTransactionDB::DeleteWalletTransactions( $id );
            global $wpdb;
            try {
                $sql = "DELETE FROM {$wpdb->prefix}wooznd_wallet_accounts "
                        . "WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                return ($nofr > 0);
            } catch ( Exception $ex ) {
                wc_get_logger()->debug( $ex->getMessage(), array( 'source' => 'wooznd-wallet' ) );
                return false;
            }
        }

        public static function GetAccountByAccountNumber( $account_number ) {
            $id = self::GetAccountIdByNumber( $account_number );
            return self::GetAccount( $id );
        }

        public static function GetAccountByEmail( $email ) {
            $id = self::GetAccountIdByEmail( $email );
            return self::GetAccount( $id );
        }

        public static function GetAccountByLogin( $login ) {
            $id = self::GetAccountIdByLogin( $login );
            return self::GetAccount( $id );
        }

        public static function GetAccountsCount( $search = "", $status = WOOZND_WALLET_ACCOUNT_STATUS_NONE ) {
            global $wpdb;
            try {
                if ( $status == WOOZND_WALLET_ACCOUNT_STATUS_NONE ) {
                    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wooznd_wallet_accounts WHERE (first_name LIKE %s) OR (last_name LIKE %s) OR (email LIKE %s) OR (account_number LIKE %s)";
                    $sql = $wpdb->prepare( $sql, $search, $search, $search, $search ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                } else {

                    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wooznd_wallet_accounts WHERE (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR account_number LIKE %s) AND (locked=%d)";
                    $sql = $wpdb->prepare( $sql, $search, $search, $search, $search, $status ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }
                return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            } catch ( Exception $ex ) {
                wc_get_logger()->debug( $ex->getMessage(), array( 'source' => 'wooznd-wallet' ) );
                return 0;
            }
        }

        public static function AccountExists( $user_id ) {
            global $wpdb;
            try {
                $sql = "SELECT id FROM {$wpdb->prefix}wooznd_wallet_accounts "
                        . "WHERE (id=%d) LIMIT 1";
                $sql = $wpdb->prepare( $sql, $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                return ($wpdb->get_var( $sql ) > 0); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            } catch ( Exception $ex ) {
                wc_get_logger()->debug( $ex->getMessage(), array( 'source' => 'wooznd-wallet' ) );
                return false;
            }
        }

        public static function AccountNumberExists( $account_number ) {
            global $wpdb;
            try {
                $sql = "SELECT account_number FROM {$wpdb->prefix}wooznd_wallet_accounts "
                        . "WHERE (account_number=%s) LIMIT 1";
                $sql = $wpdb->prepare( $sql, $account_number ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                return ($wpdb->get_var( $sql ) > 0); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            } catch ( Exception $ex ) {
                wc_get_logger()->debug( $ex->getMessage(), array( 'source' => 'wooznd-wallet' ) );
                return false;
            }
        }

        public static function GetAccountNumberById( $user_id ) {
            global $wpdb;
            try {
                $sql = "SELECT account_number FROM {$wpdb->prefix}wooznd_wallet_accounts "
                        . "WHERE (id=%d) LIMIT 1";
                $sql = $wpdb->prepare( $sql, $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            } catch ( Exception $ex ) {
                wc_get_logger()->debug( $ex->getMessage(), array( 'source' => 'wooznd-wallet' ) );
                return 0;
            }
        }

        public static function GetAccountIdByNumber( $account_number ) {
            global $wpdb;
            try {
                $sql = "SELECT id FROM {$wpdb->prefix}wooznd_wallet_accounts "
                        . "WHERE (account_number=%s) LIMIT 1";
                $sql = $wpdb->prepare( $sql, $account_number ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            } catch ( Exception $ex ) {
                wc_get_logger()->debug( $ex->getMessage(), array( 'source' => 'wooznd-wallet' ) );
                return 0;
            }
        }

        public static function GetAccountIdByEmail( $email ) {
            global $wpdb;
            try {
                $sql = "SELECT id FROM {$wpdb->prefix}wooznd_wallet_accounts "
                        . "WHERE (email=%s) LIMIT 1";
                $sql = $wpdb->prepare( $sql, $email ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            } catch ( Exception $ex ) {
                return 0;
            }
        }

        public static function GetAccountIdByLogin( $login ) {
            try {
                return get_user_by( 'login', $login )->ID;
            } catch ( Exception $ex ) {
                return 0;
            }
        }

        public static function UpdateWallet( $id, $first_name, $last_name, $ledger_balance, $current_balance, $total_spent, $locked, $remark ) {
            global $wpdb;
            try {
                $ledger = WooZnd_Util::Encrypt( $ledger_balance );
                $current = WooZnd_Util::Encrypt( $current_balance );
                $spent = WooZnd_Util::Encrypt( $total_spent );
                $last_access = current_time( 'mysql' );

                $sql = "UPDATE {$wpdb->prefix}wooznd_wallet_accounts SET "
                        . "ledger_balance=%s, "
                        . "current_balance=%s, "
                        . "total_spent=%s, "
                        . "locked=%d, "
                        . "last_access=%s, "
                        . "remark=%s "
                        . "WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $ledger, $current, $spent, $locked, $last_access, $remark, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

                if ( $nofr > 0 ) {
                    update_user_meta( $id, 'first_name', $first_name );
                    update_user_meta( $id, 'last_name', $last_name );
                }

                return ($nofr > 0);
            } catch ( Exception $ex ) {
                return false;
            }
        }

        public static function UpdateMeta( $null, $object_id, $meta_key, $meta_value, $prev_value ) {
            if ( $meta_key == 'first_name' ) {
                self::UpdateFirstName( $object_id, $meta_value );
            }
            if ( $meta_key == 'last_name' ) {
                self::UpdateLastName( $object_id, $meta_value );
            }
            if ( $meta_key == 'user_email' ) {
                self::UpdateEmail( $object_id, $meta_value );
            }
        }

        public static function UpdateFirstName( $user_id, $value ) {
            global $wpdb;
            try {
                $sql = "UPDATE {$wpdb->prefix}wooznd_wallet_accounts SET "
                        . "first_name=%s "
                        . "WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $value, $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                return ($nofr > 0);
            } catch ( Exception $ex ) {
                return false;
            }
        }

        public static function UpdateLastName( $user_id, $value ) {
            global $wpdb;
            try {
                $sql = "UPDATE {$wpdb->prefix}wooznd_wallet_accounts SET "
                        . "last_name=%s "
                        . "WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $value, $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                return ($nofr > 0);
            } catch ( Exception $ex ) {
                return false;
            }
        }

        public static function UpdateEmail( $user_id, $value ) {
            global $wpdb;
            try {
                $sql = "UPDATE {$wpdb->prefix}wooznd_wallet_accounts SET "
                        . "email=%s "
                        . "WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $value, $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                return ($nofr > 0);
            } catch ( Exception $ex ) {
                return false;
            }
        }

        public static function OnExternalUpdate( $user_id ) {
           
            if ( !current_user_can( 'edit_user', $user_id ) ) {
            
                return false;
            }
            
            if ( isset( $_POST[ 'email' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            
                update_user_meta( $user_id, 'user_email', sanitize_email( wp_unslash( $_POST[ 'email' ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
            }
            
            if ( isset( $_POST[ 'account_email' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
              
                $email = sanitize_email( wp_unslash( $_POST[ 'account_email' ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
                
                update_user_meta( $user_id, 'user_email', $email );
            }
        }

    }

}

add_action( 'personal_options_update', "WooZnd_WalletAccountDB::OnExternalUpdate" );
add_action( 'edit_user_profile_update', "WooZnd_WalletAccountDB::OnExternalUpdate" );
add_action( 'woocommerce_save_account_details', "WooZnd_WalletAccountDB::OnExternalUpdate" );

