<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?><form action="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'wwl-my-wallet' ); ?>" method="post">
    <div class="wooznd_wallet_deposit">
        <h3><?php echo esc_html( $deposit_title ); ?></h3>
        <input type="text" name="wznd_wallet_deposit" placeholder="<?php echo esc_attr( $placeholder ); ?>" />        
        <button><?php echo esc_html( $button_text ); ?></button>
    </div>
</form>