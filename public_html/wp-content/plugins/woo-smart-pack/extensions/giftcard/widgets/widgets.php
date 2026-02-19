<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include_once('giftcard_check.php');

function wooznd_register_giftcard_widgets() {
    register_widget('WooZnd_GiftCardChecker_Widget');
}
add_action('widgets_init', 'wooznd_register_giftcard_widgets');


