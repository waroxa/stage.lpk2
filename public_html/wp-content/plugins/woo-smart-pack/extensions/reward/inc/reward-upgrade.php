<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('woocommerce_init', 'wooznd_reward_upgrade_database', 1);

function wooznd_reward_upgrade_database() {

    $rewards_version = WooZnd_Util::GetOption('ws_reward_db', 99);
    //Add create Database here.
}

function wooznd_reward_upgrade_database100() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$wpdb->prefix}wooznd_reward_accounts ("
            . "id bigint(20) UNSIGNED NOT NULL,"
            . "balance varchar(256) NOT NULL,"
            . "ledger varchar(256) NOT NULL,"
            . "locked tinyint(4) UNSIGNED NOT NULL,"
            . "PRIMARY KEY (id)"
            . ") $charset_collate;";

    WooZnd_Util::CreateTable($sql);

    $sql_t = "CREATE TABLE {$wpdb->prefix}wooznd_reward_transactions ("
            . "id bigint(20) UNSIGNED NOT NULL,"
            . "account_id bigint(20) UNSIGNED NOT NULL,"
            . "PRIMARY KEY (id)"
            . ") $charset_collate;";
    WooZnd_Util::CreateTable($sql_t);

    WooZnd_Util::UpdateOption('ws_reward_db', 100);
}
