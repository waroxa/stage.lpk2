<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include_once ('wallet-upgrade.php');

if (!function_exists('wooznd_wallet_activate')) {

    function wooznd_wallet_activate() {

        wooznd_wallet_upgrade_database(); 
    }

}
