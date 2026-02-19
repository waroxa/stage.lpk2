<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$url_options = array();
if ( isset( $_GET[ 'status' ] ) ) {
    $url_options[ 'status' ] = sanitize_text_field( wp_unslash( $_GET[ 'status' ] ) );
}
if ( isset( $_GET[ 'search' ] ) ) {
    $url_options[ 'search' ] = sanitize_text_field( wp_unslash( $_GET[ 'search' ] ) );
}
if ( isset( $_GET[ 'from' ] ) ) {
    $url_options[ 'from' ] = sanitize_text_field( wp_unslash( $_GET[ 'from' ] ) );
}
if ( isset( $_GET[ 'to' ] ) ) {
    $url_options[ 'to' ] = sanitize_text_field( wp_unslash( $_GET[ 'to' ] ) );
}




$url_format = admin_url( 'admin.php?page=wznd-wallet-trans&pg={{page}}' );

$default_url = admin_url( 'admin.php?page=wznd-wallet-trans' );

foreach ( $url_options as $key => $value ) {
    $url_format .= ('&' . $key . '={{' . $key . '}}');
    $default_url .= ('&' . $key . '={{' . $key . '}}');
}
$status = -1;
if ( !empty( $_GET[ 'status' ] ) ) {
    $status = sanitize_text_field( wp_unslash( $_GET[ 'status' ] ) );
}
$search = !empty( $_GET[ 'search' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'search' ] ) ) : '';
$from = !empty( $_GET[ 'from' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'from' ] ) ) : '';
$to = !empty( $_GET[ 'to' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'to' ] ) ) : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended
$totals = $status < 0 ? WooZnd_WalletTransactionDB::GetTransactionsCount( -1, $search, $from, $to ) : WooZnd_WalletTransactionDB::GetTransactionsCount( $status, $search, $from, $to );
$pagesize = 25;

$pg = isset( $_GET[ 'pg' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'pg' ] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( empty( $pg ) ) {

    $pg = 1;
}

$paging = new WooZnd_Paginator( $totals, $pagesize, $pg );

$rows = WooZnd_WalletTransactionDB::LoadTransactions( $search, $status, $from, $to, $paging->offset(), $paging->limit() );
