<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

//Wallet Pro
if (!class_exists('WooZnd_Wallet')) {

    include_once ('wallet/wallet.php');
}
//Refund Pro
if (!class_exists('WooZnd_Refund')) {

    include_once ('refund/refund.php');
}

//Reward Pro
if (!class_exists('WooZnd_Reward')) {
    
    include_once ('reward/reward.php');
}

//Gift cards Pro
if (!class_exists('WooZnd_GiftCard')) {
   
    include_once ('giftcard/giftcard.php');
}


if (!function_exists('wooznd_plugin_activate')) {

    function wooznd_plugin_activate() {
        
        if (function_exists('wooznd_wallet_activate')) {
            
            wooznd_wallet_activate();
        }
        
        if (function_exists('wooznd_refund_activate')) {
            
            wooznd_refund_activate();
        }
        
        if (function_exists('wooznd_reward_activate')) {
            
            wooznd_reward_activate();
        }
        
        if (function_exists('wooznd_giftcard_activate')) {
            
            wooznd_giftcard_activate();
        }
    }

}