<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

add_filter('the_title', 'znd_endpoint_title');

function znd_endpoint_title($title) {
    global $wp_query;
    $is_endpoint = isset($wp_query->query_vars['wznd-my-wallet']);
    if ($is_endpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
        // New page title.
        $title = esc_html__('My Wallet', 'woo-smart-pack');
        remove_filter('the_title', 'endpoint_title');
    }
    return $title;
}

function znd_account_menu_items($items) {
    if (!WooZnd_WalletAccountDB::AccountExists(get_current_user_id())) {
        return $items;
    }
    $itms = array();
    foreach ($items as $key => $item) {
        if ($key == 'customer-logout') {
            $itms['wznd-my-wallet'] = esc_html__('My Wallet', 'woo-smart-pack');
        }
        $itms[$key] = $item;
    }
    return $itms;
}

add_filter('woocommerce_account_menu_items', 'znd_account_menu_items', 10, 1);

function znd_add_my_account_endpoint() {

    add_rewrite_endpoint('wznd-my-wallet', EP_PAGES);
}

add_action('woocommerce_init', 'znd_add_my_account_endpoint');

function znd_information_endpoint_content() {
    include 'pages/my-wallet.php';
}

add_action('woocommerce_account_wznd-my-wallet_endpoint', 'znd_information_endpoint_content');


