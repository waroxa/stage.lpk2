<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

//Admin Menu
add_action("admin_menu", 'wooznd_giftcard_addmenu');

function wooznd_giftcard_addmenu() {
   
    if (WooZnd_Util::GetOption('enable_giftcard', 'yes') == 'no') {
    
        return;
    }

    $capability = 'manage_woocommerce';
    $giftcard_slug = 'wznd-manage-giftcard';
    add_menu_page(esc_html__('Gift Cards', 'woo-smart-pack'), esc_html__('Gift Cards', 'woo-smart-pack'), $capability, $giftcard_slug, 'wooznd_giftcards_admin', 'dashicons-id', 58);
    add_submenu_page($giftcard_slug, esc_html__('Gift Cards Email & PDF', 'woo-smart-pack'), esc_html__('Add New Template', 'woo-smart-pack'), $capability, "post-new.php?post_type=wznd_giftcard");
    add_submenu_page($giftcard_slug, esc_html__('Gift Cards', 'woo-smart-pack'), esc_html__('Manage Gift Cards', 'woo-smart-pack'), $capability, $giftcard_slug);
}

if (!function_exists('wooznd_giftcards_admin')) {

    function wooznd_giftcards_admin() {
        include 'templates/giftcards-list.php';
    }

}