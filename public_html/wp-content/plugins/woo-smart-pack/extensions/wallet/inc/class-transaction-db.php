<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Transaction Types Constants

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_NONE' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_NONE', 0 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_CREDIT' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_CREDIT', 1 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_DEBIT' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_DEBIT', 2 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_DEPOSIT' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_DEPOSIT', 3 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_WITHDRAWAL' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_WITHDRAWAL', 4 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_PAYMENT' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_PAYMENT', 5 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_BILL' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_BILL', 6 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_REFUND' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_REFUND', 7 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_TRANSFER' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_TRANSFER', 8 );
}


// Transaction Status Constants

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_STATUS_PENDING' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_STATUS_PENDING', 0 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD', 1 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING', 2 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED', 3 );
}

if ( !defined( 'WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED' ) ) {

    define( 'WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED', 4 );
}

if ( !class_exists( 'WooZnd_WalletTransactionDB' ) ) {

    class WooZnd_WalletTransactionDB {

        public static function CreditWallet( $account_id, $amount, $trans_type, $issue_by, $remark = '' ) {
            
            $trans_id = self::create_transaction( $account_id, $amount, 0, $trans_type, $issue_by, $remark );
            
            if ( $trans_id > 0 ) {
            
                self::balance_account( $account_id, 0, $amount, 0, current_time( 'mysql' ) );
                
                return $trans_id;
            }
            return 0;
        }

        public static function DebitWallet( $account_id, $amount, $trans_type, $issue_by, $remark = '' ) {
            if ( self::can_debit_account( $account_id, $amount ) != true ) {
                return 0;
            }

            $trans_id = self::create_transaction( $account_id, 0, $amount, $trans_type, $issue_by, $remark );

            if ( $trans_id > 0 ) {
                self::balance_account( $account_id, 0 - $amount, 0, 0, current_time( 'mysql' ) );
                return $trans_id;
            }
            return 0;
        }

        public static function DeleteTransaction( $id ) {
            if ( $id <= 0 ) {
                return false;
            }
            global $wpdb;
            try {
                $sql = "DELETE FROM {$wpdb->prefix}wooznd_wallet_transactions "
                        . "WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                return ($nofr > 0);
            } catch ( Exception $ex ) {
                return false;
            }
        }

        public static function DeleteWalletTransactions( $account_id ) {
            if ( $account_id <= 0 ) {
                return false;
            }
            global $wpdb;
            try {
                $sql = "DELETE FROM {$wpdb->prefix}wooznd_wallet_transactions "
                        . "WHERE (account_id=%d)";
                $sql = $wpdb->prepare( $sql, $account_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                return ($nofr > 0);
            } catch ( Exception $ex ) {
                return false;
            }
        }

        public static function TransactionPending( $id, $remark = '' ) {
            $trans = self::GetTransaction( $id );
            if ( !isset( $trans[ 'id' ] ) ) {
                return false;
            }

            if ( $trans[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED || $trans[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {
                return false;
            }

            $remk = $remark;
            if ( $remk == '' ) {
                $remk = $trans[ 'remark' ];
            }

            $t_spent = 0;
            $t_current = 0;
            $t_ledger = 0;

            self::balance_account( $trans[ 'account_id' ], $t_current, $t_ledger, $t_spent, current_time( 'mysql' ), current_time( 'mysql' ) );

            self::update_transaction( $id, $trans[ 'credit' ], $trans[ 'debit' ], WOOZND_WALLET_TRANSANCTION_STATUS_PENDING, $remk );

            return true;
        }

        public static function TransactionProcessing( $id, $remark = '' ) {
            $trans = self::GetTransaction( $id );
            if ( !isset( $trans[ 'id' ] ) ) {
                return false;
            }

            if ( $trans[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED || $trans[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {
                return false;
            }

            $remk = $remark;
            if ( $remk == '' ) {
                $remk = $trans[ 'remark' ];
            }

            $t_spent = 0;
            $t_current = 0;
            $t_ledger = 0;

            self::balance_account( $trans[ 'account_id' ], $t_current, $t_ledger, $t_spent, current_time( 'mysql' ), current_time( 'mysql' ) );

            self::update_transaction( $id, $trans[ 'credit' ], $trans[ 'debit' ], WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING, $remk );

            return true;
        }

        public static function TransactionOnHold( $id, $remark = '' ) {
            $trans = self::GetTransaction( $id );

            if ( !isset( $trans[ 'id' ] ) ) {
                return false;
            }

            if ( $trans[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED || $trans[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {
                return false;
            }

            $remk = $remark;
            if ( $remk == '' ) {
                $remk = $trans[ 'remark' ];
            }

            $t_spent = 0;
            $t_current = 0;
            $t_ledger = 0;
            self::balance_account( $trans[ 'account_id' ], $t_current, $t_ledger, $t_spent, current_time( 'mysql' ), current_time( 'mysql' ) );

            self::update_transaction( $id, $trans[ 'credit' ], $trans[ 'debit' ], WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD, $remk );

            return true;
        }

        public static function TransactionComplete( $id, $completed_by, $remark = '' ) {
            $trans = self::GetTransaction( $id );

            if ( !isset( $trans[ 'id' ] ) ) {
                return false;
            }

            if ( $trans[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED || $trans[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {
                return false;
            }

            $remk = $remark;
            if ( $remk == '' ) {
                $remk = $trans[ 'remark' ];
            }
            $t_spent = 0;
            $t_current = 0;
            $t_ledger = 0;

            if ( $trans[ 'credit' ] > 0 ) {
                $t_current = $trans[ 'credit' ];
            } else if ( $trans[ 'debit' ] > 0 ) {
                $t_ledger = 0 - $trans[ 'debit' ];
                if ( $trans[ 'transaction_type' ] != WOOZND_WALLET_TRANSANCTION_DEBIT ) {
                    $t_spent = $trans[ 'debit' ];
                }
            }

            self::balance_account( $trans[ 'account_id' ], $t_current, $t_ledger, $t_spent, current_time( 'mysql' ), current_time( 'mysql' ) );

            self::update_transaction( $id, $trans[ 'credit' ], $trans[ 'debit' ], WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED, $completed_by, $remk, current_time( 'mysql' ) );

            return true;
        }

        public static function TransactionCancel( $id, $completed_by, $remark = '' ) {
            $trans = self::GetTransaction( $id );
            if ( !isset( $trans[ 'id' ] ) ) {
                return false;
            }

            if ( $trans[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED || $trans[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {
                return false;
            }

            $remk = $remark;
            if ( $remk == '' ) {
                $remk = $trans[ 'remark' ];
            }

            $t_spent = 0;
            $t_current = 0;
            $t_ledger = 0;

            if ( $trans[ 'credit' ] > 0 ) {
                $t_ledger = 0 - $trans[ 'credit' ];
            } else if ( $trans[ 'debit' ] > 0 ) {
                $t_current = $trans[ 'debit' ];
            }


            self::balance_account( $trans[ 'account_id' ], $t_current, $t_ledger, $t_spent, current_time( 'mysql' ), current_time( 'mysql' ) );

            self::update_transaction( $id, $trans[ 'credit' ], $trans[ 'debit' ], WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED, $completed_by, $remk, current_time( 'mysql' ) );

            return true;
        }

        public static function SetTransactionOrderId( $id, $order_id ) {
            if ( $id <= 0 ) {
                return false;
            }
            global $wpdb;
            try {
                $sql = "UPDATE {$wpdb->prefix}wooznd_wallet_transactions SET "
                        . "order_id=%d "
                        . "WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $order_id, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                return ($nofr > 0);
            } catch ( Exception $ex ) {
                return false;
            }
        }

        public static function LoadTransactions( $search = '', $status = -1, $from = '', $to = '', $startrow = 0, $limit = 25 ) {
            global $wpdb;
            $retults = array();

            try {

                $rows = array();

                $account_id = WooZnd_WalletAccountDB::GetAccountIdByNumber( $search );
                if ( empty( $account_id ) || $account_id <= 0 ) {
                    $account_id = WooZnd_WalletAccountDB::GetAccountIdByEmail( $search );
                }
                $filter = '';
                if ( !empty( $account_id ) && $account_id > 0 ) {
                    $q = ($filter == '') ? 'WHERE ' : ' ';
                    $q .= "(account_id = %d)";
                    $filter = $wpdb->prepare( $q, $account_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                if ( !($status < 0) ) {
                    $q = ($filter == '') ? 'WHERE ' : ' AND ';
                    $q .= "(status = %d)";
                    $filter .= $wpdb->prepare( $q, $status ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                if ( $from != '' ) {
                    $q = ($filter == '') ? 'WHERE (' : ' AND (';
                    $q .= "issue_date >= %s";
                    if ( $to != '' ) {
                        $q .= " AND issue_date <= %s";
                    }
                    $q .= ")";

                    $filter .= $wpdb->prepare( $q, $from . ' 00:00:00', $to . ' 23:59:59' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                $sql = "SELECT * FROM {$wpdb->prefix}wooznd_wallet_transactions " . $filter . " ORDER BY issue_date DESC LIMIT %d,%d";
                $sql = $wpdb->prepare( $sql, $startrow, $limit ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                if ( is_array( $rows ) ) {
                    foreach ( $rows as $row ) {
                        $rw = $row;
                        $rw[ 'account_number' ] = WooZnd_WalletAccountDB::GetAccountNumberById( $row[ 'account_id' ] );
                        $rw[ 'credit' ] = WooZnd_Util::Decrypt( $row[ 'credit' ] );
                        $rw[ 'debit' ] = WooZnd_Util::Decrypt( $row[ 'debit' ] );
                        $retults[] = $rw;
                    }
                }
            } catch ( Exception $ex ) {
                $retults = array();
            }
            return $retults;
        }

        public static function GetTransactionsCount( $status = -1, $search = '', $from = '', $to = '' ) {
            global $wpdb;
            try {


                $account_id = WooZnd_WalletAccountDB::GetAccountIdByNumber( $search );
                if ( empty( $account_id ) || $account_id <= 0 ) {
                    $account_id = WooZnd_WalletAccountDB::GetAccountIdByEmail( $search );
                }
                $filter = '';
                if ( !empty( $account_id ) && $account_id > 0 ) {
                    $q = ($filter == '') ? 'WHERE ' : ' ';
                    $q .= "(account_id = %d)";
                    $filter = $wpdb->prepare( $q, $account_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                if ( !($status < 0) ) {
                    $q = ($filter == '') ? 'WHERE ' : ' AND ';
                    $q .= "(status = %d)";
                    $filter .= $wpdb->prepare( $q, $status ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                if ( $from != '' ) {
                    $q = ($filter == '') ? 'WHERE (' : ' AND (';
                    $q .= "issue_date >= %s";
                    if ( $to != '' ) {
                        $q .= " AND issue_date <= %s";
                    }
                    $q .= ")";

                    $filter .= $wpdb->prepare( $q, $from . ' 00:00:00', $to . ' 23:59:59' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wooznd_wallet_transactions " . $filter;

                return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            } catch ( Exception $ex ) {
                return 0;
            }
        }

        public static function GetTransactionReceipt( $id ) {
            if ( $id <= 0 ) {
                return false;
            }
            global $wpdb;
            try {

                $sql = "SELECT receipt FROM {$wpdb->prefix}wooznd_wallet_transactions "
                        . "WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            } catch ( Exception $ex ) {
                return '';
            }
        }

        public static function GetTransaction( $id ) {
            if ( $id <= 0 ) {
                return false;
            }
            global $wpdb;
            try {

                $sql = "SELECT * FROM {$wpdb->prefix}wooznd_wallet_transactions "
                        . "WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $reslt = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $result = $reslt;
                $result[ 'credit' ] = WooZnd_Util::Decrypt( $result[ 'credit' ] );
                $result[ 'debit' ] = WooZnd_Util::Decrypt( $result[ 'debit' ] );
                return $result;
            } catch ( Exception $ex ) {
                return array();
            }
        }

        private static function create_transaction( $account_id, $credit, $debit, $trans_type, $issued_by, $remark = '' ) {
            if ( $account_id <= 0 ) {
                return false;
            }
            global $wpdb;
            try {
                $crypt_credit = WooZnd_Util::Encrypt( $credit );
                $crypt_debit = WooZnd_Util::Encrypt( $debit );
                $status = WOOZND_WALLET_TRANSANCTION_STATUS_PENDING;

                $sql = "INSERT INTO {$wpdb->prefix}wooznd_wallet_transactions (account_id, credit, debit, transaction_type, status, issue_date, issued_by, remark)"
                        . " VALUES (%d, %s, %s, %d, %d, %s, %s, %s)";

                $sql = $wpdb->prepare( $sql, $account_id, $crypt_credit, $crypt_debit, $trans_type, $status, current_time( 'mysql' ), $issued_by, $remark ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                if ( $nofr > 0 ) {
                    $trans_id = $wpdb->insert_id;


                    $receipt = WooZnd_Wallet_Util::GenTransactionReceipt( $account_id, $trans_id );

                    $sql = "UPDATE {$wpdb->prefix}wooznd_wallet_transactions SET "
                            . "receipt=%s "
                            . "WHERE (id=%d)";
                    $sql = $wpdb->prepare( $sql, $receipt, $trans_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

                    return $trans_id;
                } else {
                    return 0;
                }
            } catch ( Exception $ex ) {
                return 0;
            }
        }

        private static function update_transaction( $id, $credit, $debit, $status, $completed_by = '', $remark = '', $complete_date = '' ) {
            if ( $id <= 0 ) {
                return false;
            }
            global $wpdb;
            try {
                $crypt_credit = WooZnd_Util::Encrypt( $credit );
                $crypt_debit = WooZnd_Util::Encrypt( $debit );


                if ( $complete_date == '' ) {
                    $sql = "UPDATE {$wpdb->prefix}wooznd_wallet_transactions SET "
                            . "credit=%s, "
                            . "debit=%s, "
                            . "status=%d, "
                            . "remark=%s "
                            . "WHERE (id=%d)";
                    $sql = $wpdb->prepare( $sql, $crypt_credit, $crypt_debit, $status, $remark, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                } else {
                    $sql = "UPDATE {$wpdb->prefix}wooznd_wallet_transactions SET "
                            . "credit=%s, "
                            . "debit=%s, "
                            . "status=%d, "
                            . "complete_date=%s, "
                            . "completed_by=%s, "
                            . "remark=%s "
                            . "WHERE (id=%d)";
                    $sql = $wpdb->prepare( $sql, $crypt_credit, $crypt_debit, $status, $complete_date, $completed_by, $remark, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }


                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                return ($nofr > 0);
            } catch ( Exception $ex ) {
                return false;
            }
        }

        private static function can_debit_account( $id, $amount ) {
            if ( $id <= 0 ) {
                return false;
            }
            global $wpdb;
            try {

                $sql = "SELECT id, current_balance FROM {$wpdb->prefix}wooznd_wallet_accounts WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $row = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

                $current = doubleval( WooZnd_Util::Decrypt( $row[ 'current_balance' ] ) );
                return ($current >= $amount && $amount > 0);
            } catch ( Exception $ex ) {
                return false;
            }
        }

        private static function balance_account( $id, $current, $ledger, $spent, $last_access, $last_transaction = '' ) {
            if ( $id <= 0 ) {
                return false;
            }
            global $wpdb;
            try {

                $sql = "SELECT id, current_balance, ledger_balance, total_spent FROM {$wpdb->prefix}wooznd_wallet_accounts WHERE (id=%d)";
                $sql = $wpdb->prepare( $sql, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $row = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

                $old_current = doubleval( WooZnd_Util::Decrypt( $row[ 'current_balance' ] ) );
                $old_ledger = doubleval( WooZnd_Util::Decrypt( $row[ 'ledger_balance' ] ) );
                $old_total = doubleval( WooZnd_Util::Decrypt( $row[ 'total_spent' ] ) );

                $new_current = WooZnd_Util::Encrypt( $old_current + $current );
                $new_ledger = WooZnd_Util::Encrypt( $old_ledger + $ledger );
                $new_total = WooZnd_Util::Encrypt( $old_total + $spent );


                if ( $last_transaction == '' ) {
                    $sql = "UPDATE {$wpdb->prefix}wooznd_wallet_accounts SET "
                            . "current_balance=%s, "
                            . "ledger_balance=%s, "
                            . "total_spent=%s, "
                            . "last_access=%s "
                            . "WHERE (id=%d)";
                    $sql = $wpdb->prepare( $sql, $new_current, $new_ledger, $new_total, $last_access, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                } else {
                    $sql = "UPDATE {$wpdb->prefix}wooznd_wallet_accounts SET "
                            . "current_balance=%s, "
                            . "ledger_balance=%s, "
                            . "total_spent=%s, "
                            . "last_access=%s, "
                            . "last_transaction=%s "
                            . "WHERE (id=%d)";
                    $sql = $wpdb->prepare( $sql, $new_current, $new_ledger, $new_total, $last_access, $last_transaction, $id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }
                $nofr = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                return ($nofr > 0);
            } catch ( Exception $ex ) {
                return false;
            }
        }

    }

}
