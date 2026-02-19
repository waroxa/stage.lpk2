<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

global $wp_query;
$is_endpoint = isset( $wp_query->query_vars[ 'wznd-my-wallet' ] );

$p_url = '';
if ( $is_endpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {

    $p_url = wc_get_endpoint_url( 'wznd-my-wallet' );
} else {
    $p_url = get_permalink( get_the_ID() );
}

$url_format = $p_url . '?pg={{page}}';
$default_url = $p_url;

$account_number = WooZnd_WalletAccountDB::GetAccountNumberById( get_current_user_id() );

$totals = WooZnd_WalletTransactionDB::GetTransactionsCount( -1, $account_number, '', '' );
$pagesize = isset( $page_size ) ? $page_size : 5;

$pg = isset( $_GET[ 'pg' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'pg' ] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( empty( $pg ) ) {

    $pg = 1;
}

$paging = new WooZnd_Paginator( $totals, $pagesize, $pg );


$rows = WooZnd_WalletTransactionDB::LoadTransactions( $account_number, -1, '', '', $paging->offset(), $paging->limit() );


