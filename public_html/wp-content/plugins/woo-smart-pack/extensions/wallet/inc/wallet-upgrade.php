<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

add_action('woocommerce_init', 'wooznd_wallet_upgrade_database', 1);

function wooznd_wallet_upgrade_database() {

    $wallet_version = WooZnd_Util::GetOption('ws_wallet_db', 99);

    //Add create Database here.
    if ($wallet_version < 100) {

        wooznd_wallet_upgrade_database100();
    }
}

function wooznd_wallet_upgrade_database100() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $acc_sql = "CREATE TABLE {$wpdb->prefix}wooznd_wallet_accounts ("
            . "id bigint(20) UNSIGNED NOT NULL,"
            . "first_name varchar(256) DEFAULT NULL,"
            . "last_name varchar(256) DEFAULT NULL,"
            . "email varchar(256) DEFAULT NULL,"
            . "account_number varchar(30) NOT NULL,"
            . "current_balance varchar(256) NOT NULL,"
            . "ledger_balance varchar(256) NOT NULL,"
            . "total_spent varchar(256) NOT NULL,"
            . "open_date timestamp NULL DEFAULT NULL,"
            . "last_access timestamp NULL DEFAULT NULL,"
            . "last_transaction timestamp NULL DEFAULT NULL,"
            . "locked tinyint(1) NOT NULL DEFAULT 0,"
            . "security_key varchar(256) DEFAULT NULL,"
            . "security_tokon varchar(256) DEFAULT NULL,"
            . "tokon_expiry_date varchar(256) DEFAULT NULL,"
            . "remark varchar(512) NOT NULL,"
            . "PRIMARY KEY (id)"
            . ") $charset_collate;";

    WooZnd_Util::CreateTable($acc_sql);


    $trans_sql = "CREATE TABLE {$wpdb->prefix}wooznd_wallet_transactions ("
            . "id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,"
            . "account_id bigint(20) UNSIGNED NOT NULL,"
            . "receipt varchar(128) DEFAULT NULL,"
            . "order_id bigint(20) UNSIGNED DEFAULT NULL,"
            . "credit varchar(256) NOT NULL,"
            . "debit varchar(256) NOT NULL,"
            . "transaction_type tinyint(3) UNSIGNED NOT NULL,"
            . "status tinyint(3) UNSIGNED NOT NULL,"
            . "issue_date timestamp NULL DEFAULT NULL,"
            . "complete_date timestamp NULL DEFAULT NULL,"
            . "remark varchar(512) DEFAULT NULL,"
            . "issued_by varchar(256) NOT NULL,"
            . "completed_by varchar(256) DEFAULT NULL,"
            . "PRIMARY KEY (id)"
            . ") $charset_collate;";
    WooZnd_Util::CreateTable($trans_sql);
    wooznd_wallet_create_deposit_product();

    WooZnd_Util::UpdateOption('ws_wallet_db', 100);
}

function wooznd_wallet_create_deposit_product() {
    $query = new WP_Query(array('post_type' => 'product', 'name' => 'ws-funds-deposit01'));
    if (!$query->have_posts()) {
        // Add the page using the data from the array above
        
        $post_id = wp_insert_post(
                array(
                    'post_content' => '',
                    'post_name' => 'ws-funds-deposit01',
                    'post_title' => 'Funds Deposit',
                    'post_status' => 'publish',
                    'post_type' => 'product',
                    'ping_status' => 'closed',
                    'comment_status' => 'closed',
                )
        );
        if ($post_id > 0) {
            wp_set_object_terms($post_id, 'simple', 'product_type');
            update_post_meta($post_id, '_stock_status', 'instock');
            update_post_meta($post_id, 'total_sales', '0');
            update_post_meta($post_id, '_downloadable', 'no');
            update_post_meta($post_id, '_virtual', 'yes');
            update_post_meta($post_id, '_regular_price', '2');
            update_post_meta($post_id, '_sale_price', '1');
            update_post_meta($post_id, '_purchase_note', '');
            update_post_meta($post_id, '_featured', 'no');
            update_post_meta($post_id, '_weight', '');
            update_post_meta($post_id, '_length', '');
            update_post_meta($post_id, '_width', '');
            update_post_meta($post_id, '_height', '');
            update_post_meta($post_id, '_sku', '');
            update_post_meta($post_id, '_product_attributes', array());
            update_post_meta($post_id, '_sale_price_dates_from', '');
            update_post_meta($post_id, '_sale_price_dates_to', '');
            update_post_meta($post_id, '_price', '');
            update_post_meta($post_id, '_sold_individually', 'yes');
            update_post_meta($post_id, '_manage_stock', 'no');
            update_post_meta($post_id, '_backorders', 'no');
            update_post_meta($post_id, '_stock', '');
            WooZnd_Util::UpdateOption('deposit_product_id', $post_id);
        }
    }
}
