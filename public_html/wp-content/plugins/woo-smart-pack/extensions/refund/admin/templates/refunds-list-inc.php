<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$url_options = array();
// phpcs:disable WordPress.Security.NonceVerification.Recommended
if ( isset( $_GET[ 'status' ] ) ) {

    $url_options[ 'status' ] = sanitize_text_field( wp_unslash( $_GET[ 'status' ] ) );
}

if ( isset( $_GET[ 'search' ] ) ) {

    $url_options[ 'search' ] = sanitize_text_field( wp_unslash( $_GET[ 'search' ] ) );
}



$url_format = admin_url( 'admin.php?page=wznd-wallet-refunds&pg={{page}}' );

$default_url = admin_url( 'admin.php?page=wznd-wallet-refunds' );

foreach ( $url_options as $key => $value ) {
    $url_format .= ('&' . $key . '={{' . $key . '}}');
    $default_url .= ('&' . $key . '={{' . $key . '}}');
}
$status = -1;

if ( isset( $_GET[ 'status' ] ) ) {

    $status = sanitize_text_field( wp_unslash( $_GET[ 'status' ] ) );
}

$search = isset( $_GET[ 'search' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'search' ] ) ) : '';

$totals = WooZnd_RefundDB::GetRequestsCount( ($search != '') ? $search . '%' : '', $status );
$pagesize = 25;

$pg = isset( $_GET[ 'pg' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'pg' ] ) ) : 1;
// phpcs:enable WordPress.Security.NonceVerification.Recommended
if ( empty( $pg ) ) {

    $pg = 1;
}

$paging = new WooZnd_Paginator( $totals, $pagesize, $pg );

$rows = WooZnd_RefundDB::LoadRequests( ($search != '') ? $search . '%' : '', $status, $paging->offset(), $paging->limit() );

