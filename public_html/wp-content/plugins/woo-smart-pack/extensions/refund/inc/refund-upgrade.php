<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_init', 'wooznd_refund_upgrade_database', 1);

function wooznd_refund_upgrade_database() {

    $giftcard_version = WooZnd_Util::GetOption('ws_refund_db', 99);
    //Add create Database here.

    if ($giftcard_version < 100) {
        wooznd_refund_upgrade_database100();
    }
}

function wooznd_refund_upgrade_database100() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$wpdb->prefix}wooznd_refund_requests ("
            . "order_id bigint(20) UNSIGNED NOT NULL,"
            . "account_id bigint(20) UNSIGNED NOT NULL,"
            . "reason varchar(256) NOT NULL,"
            . "request_amount varchar(256) NOT NULL,"
            . "request_date timestamp NULL DEFAULT NULL,"
            . "status tinyint(3) UNSIGNED NOT NULL,"
            . "PRIMARY KEY (order_id)"
            . ") $charset_collate;";

    WooZnd_Util::CreateTable($sql);
    WooZnd_Util::UpdateOption('ws_refund_db', 100);
}
