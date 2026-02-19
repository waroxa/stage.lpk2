<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if (!WooZnd_WalletAccountDB::AccountExists(get_current_user_id())) {
    return;
}


$wallet_title = esc_html__('My Wallet', 'woo-smart-pack');
$ledger_label = esc_html__('Ledger Balance:', 'woo-smart-pack');
$current_label = esc_html__('Current Balance:', 'woo-smart-pack');
$total_spent_label = esc_html__('Total Spent:', 'woo-smart-pack');
include 'template/wallet.php';


$deposit_title = esc_html__('Wallet Deposit', 'woo-smart-pack');
$placeholder = esc_html__('Enter Amount', 'woo-smart-pack');
$button_text = esc_html__('Deposit', 'woo-smart-pack');
include 'template/deposit.php';


$transaction_title = esc_html__('Transactions', 'woo-smart-pack');
$page_size = 10;
include 'template/transactions.php';
