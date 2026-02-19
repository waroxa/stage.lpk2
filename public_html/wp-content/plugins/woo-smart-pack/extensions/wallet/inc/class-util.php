<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('WooZnd_Wallet_Util')) {

    class WooZnd_Wallet_Util {

        public static function AccNumGen() {
            $accno = WooZnd_Util::GetOption('wallet_account_number_start', '');
            if ($accno == '') {
                $accno = current_time('timestamp');
                WooZnd_Util::UpdateOption('wallet_account_number_start', $accno);
            }
            $res=$accno;
            $accno++;
            WooZnd_Util::UpdateOption('wallet_account_number_start', $accno);
            return $res;
        }

        public static function GenTransactionReceipt($account_id, $receipt_id) {
            $account_no = WooZnd_WalletAccountDB::GetAccountNumberById($account_id);

            $receipt = WooZnd_Util::GetOption('transactions_receipt_format', 'TRX{{account_number}}{{transaction_id}}');
            $receipt = preg_replace('/{{account_id}}/', $account_id, $receipt);
            $receipt = preg_replace('/{{account_number}}/', $account_no, $receipt);
            $receipt = preg_replace('/{{transaction_id}}/', $receipt_id, $receipt);
            return $receipt;
        }

        public static function TransactionTypeString($trans_type, $lower_case = true) {

            switch ($trans_type) {
                case WOOZND_WALLET_TRANSANCTION_NONE:
                    if ($lower_case == true) {
                        return 'none';
                    }
                    return 'None';
                case WOOZND_WALLET_TRANSANCTION_CREDIT:
                    if ($lower_case == true) {
                        return 'credit';
                    }
                    return 'Credit';
                case WOOZND_WALLET_TRANSANCTION_DEBIT:
                    if ($lower_case == true) {
                        return 'debit';
                    }
                    return 'Debit';
                case WOOZND_WALLET_TRANSANCTION_DEPOSIT:
                    if ($lower_case == true) {
                        return 'deposit';
                    }
                    return 'Deposit';
                case WOOZND_WALLET_TRANSANCTION_WITHDRAWAL:
                    if ($lower_case == true) {
                        return 'withdrawal';
                    }
                    return 'Withdrawal';
                case WOOZND_WALLET_TRANSANCTION_PAYMENT:
                    if ($lower_case == true) {
                        return 'payment';
                    }
                    return 'Payment';
                case WOOZND_WALLET_TRANSANCTION_BILL:
                    if ($lower_case == true) {
                        return 'bill';
                    }
                    return 'Bill';
                case WOOZND_WALLET_TRANSANCTION_REFUND:
                    if ($lower_case == true) {
                        return 'refund';
                    }
                    return 'Refund';
                case WOOZND_WALLET_TRANSANCTION_TRANSFER:
                    if ($lower_case == true) {
                        return 'transfer';
                    }
                    return 'Transfer';
                default :
                    return '';
            }
        }

        public static function TransactionStatusString($trans_status, $lower_case = true) {
            switch ($trans_status) {
                case WOOZND_WALLET_TRANSANCTION_STATUS_PENDING:
                    if ($lower_case == true) {
                        return 'pending';
                    }
                    return 'Pending';
                case WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD:
                    if ($lower_case == true) {
                        return 'onhold';
                    }
                    return 'On-Hold';
                case WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING:
                    if ($lower_case == true) {
                        return 'processing';
                    }
                    return 'Processing';
                case WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED:
                    if ($lower_case == true) {
                        return 'completed';
                    }
                    return 'Completed';
                case WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED:
                    if ($lower_case == true) {
                        return 'cancelled';
                    }
                    return 'Cancelled';
                default :
                    return '';
            }
        }

    }

}