<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include_once('mywallet.php');
include_once('deposit.php');

function wooznd_register_wallet_widgets() {
    register_widget('WooZnd_MyWallet_Widget');
    register_widget('WooZnd_Deposit_Widget');
}
add_action('widgets_init', 'wooznd_register_wallet_widgets');


