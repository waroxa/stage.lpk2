<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include_once ('giftcard-upgrade.php');

if ( !function_exists( 'wooznd_giftcard_activate' ) ) {

    function wooznd_giftcard_activate() {

        wooznd_giftcard_upgrade_database();
    }

}
