<?php

if (!defined('ABSPATH')) {
    exit;
}

include_once('reward-upgrade.php');

if (!function_exists('wooznd_reward_activate')) {
    
    function wooznd_reward_activate() {

        wooznd_reward_upgrade_database();
    }
}