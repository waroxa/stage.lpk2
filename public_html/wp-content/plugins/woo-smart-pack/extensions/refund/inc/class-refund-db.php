<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !defined( 'WOOZND_WALLET_REFUND_REQUEST_NONE' ) ) {

    define( 'WOOZND_WALLET_REFUND_REQUEST_NONE', -1 );
}

if ( !defined( 'WOOZND_WALLET_REFUND_REQUEST_PENDING' ) ) {

    define( 'WOOZND_WALLET_REFUND_REQUEST_PENDING', 0 );
}

if ( !defined( 'WOOZND_WALLET_REFUND_REQUEST_APROVED' ) ) {

    define( 'WOOZND_WALLET_REFUND_REQUEST_APROVED', 1 );
}

if ( !defined( 'WOOZND_WALLET_REFUND_REQUEST_REJECTED' ) ) {

    define( 'WOOZND_WALLET_REFUND_REQUEST_REJECTED', 2 );
}

if ( !class_exists( 'WooZnd_RefundDB' ) ) {

    class WooZnd_RefundDB {

        public static function CreateRequest( $id, $account_id, $request_amount, $status = WOOZND_WALLET_REFUND_REQUEST_PENDING, $reason = '' ) {

            if ( $id <= 0 || $account_id <= 0 || $request_amount <= 0 ) {

                return false;
            }

            global $wpdb;

            try {

                $amount = WooZnd_Util::Encrypt( $request_amount );

                $sql = "INSERT INTO {$wpdb->prefix}wooznd_refund_requests (order_id, account_id, reason, request_amount, request_date, status)"
                        . " VALUES (%d, %d, %s, %s, %s, %d)";

                $sql = $wpdb->prepare( $sql, $id, $account_id, $reason, $amount, current_time( 'mysql' ), $status ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

                return ($nofr > 0);
            }
            catch ( Exception $ex ) {

                return false;
            }
        }

        public static function LoadRequests( $search = '', $status = WOOZND_WALLET_REFUND_REQUEST_NONE, $startrow = 0, $limit = 25 ) {

            global $wpdb;

            $retults = array();

            try {

                $rows = array();

                $filter = '';

                if ( $search != '' ) {

                    $q = ($filter == '') ? 'WHERE ' : ' ';
                    $q .= "(order_id = %d)";

                    $filter = $wpdb->prepare( $q, $search ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                if ( $status >= WOOZND_WALLET_REFUND_REQUEST_PENDING ) {

                    $q = ($filter == '') ? 'WHERE ' : 'AND ';
                    $q .= "(status = %d)";

                    $filter .= $wpdb->prepare( $q, $status ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                $sql = "SELECT * FROM {$wpdb->prefix}wooznd_refund_requests " . $filter . " ORDER BY request_date DESC LIMIT %d,%d";
                $sql = $wpdb->prepare( $sql, $startrow, $limit ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

                if ( is_array( $rows ) ) {

                    foreach ( $rows as $row ) {

                        $rw = $row;

                        $rw[ 'account_number' ] = WooZnd_WalletAccountDB::GetAccountNumberById( $row[ 'account_id' ] );
                        $rw[ 'request_amount' ] = WooZnd_Util::Decrypt( $row[ 'request_amount' ] );

                        $retults[] = $rw;
                    }
                }
            }
            catch ( Exception $ex ) {

                $retults = array();
            }

            return $retults;
        }

        public static function DeleteRequest( $id ) {

            if ( $id <= 0 ) {

                return false;
            }

            global $wpdb;

            try {

                $sql = "DELETE FROM {$wpdb->prefix}wooznd_refund_requests "
                        . "WHERE (order_id=%d)";
                $sql = $wpdb->prepare( $sql, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

                return ($nofr > 0);
            }
            catch ( Exception $ex ) {

                return false;
            }
        }

        public static function GetRequestsCount( $search = '', $status = WOOZND_WALLET_REFUND_REQUEST_NONE ) {

            global $wpdb;

            try {

                $rows = array();

                $filter = '';

                if ( $search != '' ) {

                    $q = ($filter == '') ? 'WHERE ' : ' ';
                    $q .= "(order_id = %d)";

                    $filter = $wpdb->prepare( $q, $search ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                if ( $status >= WOOZND_WALLET_REFUND_REQUEST_PENDING ) {

                    $q = ($filter == '') ? 'WHERE ' : 'AND ';
                    $q .= "(status = %d)";

                    $filter .= $wpdb->prepare( $q, $status ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wooznd_refund_requests " . $filter;

                return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            }
            catch ( Exception $ex ) {

                $retults = 0;
            }
        }

        public static function GetRequestById( $id ) {

            if ( empty( $id ) ) {

                return false;
            }

            if ( $id <= 0 ) {

                return false;
            }

            global $wpdb;

            try {

                $sql = "SELECT * FROM {$wpdb->prefix}wooznd_refund_requests "
                        . "WHERE (order_id=%d)";
                $sql = $wpdb->prepare( $sql, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $reslt = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

                if ( !$reslt ) {

                    return array();
                }

                $result = $reslt;
                $result[ 'account_number' ] = WooZnd_WalletAccountDB::GetAccountNumberById( $reslt[ 'account_id' ] );
                $result[ 'request_amount' ] = WooZnd_Util::Decrypt( $reslt[ 'request_amount' ] );

                return $result;
            }
            catch ( Exception $ex ) {

                return array();
            }
        }

        public static function RequestExist( $id ) {

            global $wpdb;

            try {

                $sql = "SELECT order_id FROM {$wpdb->prefix}wooznd_refund_requests "
                        . "WHERE (order_id=%d) LIMIT 1";

                $sql = $wpdb->prepare( $sql, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                return ($wpdb->get_var( $sql ) > 0); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            }
            catch ( Exception $ex ) {

                return false;
            }
        }

        public static function ProcessRequest( $id, $amount, $reason = '', $update_amount = true ) {

            $reas = $reason;

            $req = self::GetRequestById( $id );

            if ( $reas == '' ) {

                $reas = $req[ 'reason' ];
            }

            $req_amount = $req[ 'request_amount' ];

            if ( $update_amount == true ) {

                $req_amount = $amount;
            }

            if ( self::update_request( $id, $req_amount, WOOZND_WALLET_REFUND_REQUEST_APROVED, $reas ) == true ) {

                return self::RefundWallet( $id, $amount, $reason );
            }

            return 0;
        }

        public static function CancelRequest( $id ) {

            $req = self::GetRequestById( $id );
            $reason = $req[ 'reason' ];
            $amount = $req[ 'request_amount' ];

            if ( self::update_request( $id, $amount, WOOZND_WALLET_REFUND_REQUEST_REJECTED, $reason ) == true ) {

                return true;
            }

            return false;
        }

        public static function RefundWallet( $order_id, $amount, $reason = '' ) {

            $order = wc_get_order( $order_id );

            if ( !$order ) {

                return 0;
            }

            $user_id = $order->get_user_id();
            $order_total = $order->get_total();

            $refunded_amount = 0;

            foreach ( $order->get_refunds() as $refund ) {

                $refunded_amount += $refund->get_amount();
            }

            if ( $refunded_amount >= $order_total ) {
               
                return 0;
            }

            $usr = wp_get_current_user();
            $trans_id = WooZnd_WalletTransactionDB::CreditWallet( $user_id, $amount, WOOZND_WALLET_TRANSANCTION_REFUND, $usr->user_login, $reason );
            
            if ( $trans_id > 0 ) {
            
                WooZnd_WalletTransactionDB::TransactionComplete( $trans_id, $usr->user_login, $reason );
                
                do_action( 'wooznd_order_renfund_processed', $trans_id, $order_id, $user_id );
            }
            
            return $trans_id;
        }

        private static function update_request( $id, $amount, $status, $reason = '' ) {
           
            if ( $id <= 0 ) {
            
                return false;
            }
            
            global $wpdb;
            
            try {
            
                $request_amount = WooZnd_Util::Encrypt( $amount );

                $sql = "UPDATE {$wpdb->prefix}wooznd_refund_requests SET "
                        . "request_amount=%s, "
                        . "status=%d, "
                        . "reason=%s "
                        . "WHERE (order_id=%d)";
              
                $sql = $wpdb->prepare( $sql, $request_amount, $status, $reason, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
               
                return ($nofr > 0);
            }
            catch ( Exception $ex ) {
                
                return false;
            }
        }

    }

}

