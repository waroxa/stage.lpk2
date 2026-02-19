<?php

if (!defined('ABSPATH')) {
    exit;
}

include_once ('refund-upgrade.php');

if (!function_exists('wooznd_refund_activate')) {

    function wooznd_refund_activate() {

        wooznd_refund_upgrade_database();
    }

}
