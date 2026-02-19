<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wooznd_giftcard_before_sendcard', 'wznd_giftcard_before_sendcard', 10, 2 );

function wznd_giftcard_before_sendcard( $giftcard_id, $coupon_id ) {
    global $wooznd_giftcard;
    $wooznd_giftcard = WooZnd_GiftCardDB::GetGiftCard( $giftcard_id );
    $subject = WooZnd_Util::GetOption( 'giftcard_sendtofriend_subject', esc_html__( 'You have recieved a gift card from [wznd_fromname]', 'woo-smart-pack' ) );
    $message = '';

    if ( isset( $wooznd_giftcard[ 'email_template_id' ] ) && $wooznd_giftcard[ 'email_template_id' ] > 0 ) {
        $pst_content = get_post( $wooznd_giftcard[ 'email_template_id' ] )->post_content;
        $pst_style = get_post_meta( $wooznd_giftcard[ 'email_template_id' ], '_wznd_giftcard_custom_css', true );
        $message = (isset( $css_styles ) ? '<style>' . $pst_style . '</style>' : '') . $pst_content;
    }


    if ( empty( $message ) ) {
        $message = WooZnd_Util::GetOption( 'giftcard_sendtofriend_message', wp_kses_post( __( 'Hi [wznd_toname], <br /> You have recieved a gift card from [wznd_fromname] with message <strong>[wznd_message]</strong> <br /> your gift card code is [wznd_coupon] and the value of this gift card is [wznd_amount]. please redeem your gift card at  [wznd_site_link] before its expiry date on [wznd_expirydate].', 'woo-smart-pack' ) ) );
    }
    $attach = WooZnd_Util::GetOption( 'giftcard_sendtofriend_attach_pdf', 'yes' );

    $attachments = array();
    if ( $attach == 'yes' ) {
        $attachments = array( WP_CONTENT_DIR . '/uploads/woo-smart-pack/giftcards/giftcard' . $wooznd_giftcard[ 'id' ] . '.pdf' );
    }
    WooZnd_Util::SendMail( $wooznd_giftcard[ 'to_email' ], do_shortcode( $subject ), do_shortcode( $message ), $attachments );
}

add_action( 'wooznd_giftcard_refunded', 'wznd_giftcard_refunded', 10, 3 );

function wznd_giftcard_refunded( $transaction_id, $giftcard_id, $coupon_id ) {
    global $wooznd_giftcard, $wooznd_transaction;
    $wooznd_giftcard = WooZnd_GiftCardDB::GetGiftCard( $giftcard_id );
    $wooznd_transaction = WooZnd_WalletTransactionDB::GetTransaction( $transaction_id );

    $refund_mode = WooZnd_Util::GetOption( 'giftcard_refund_mode', 'auto' );

    $subject = WooZnd_Util::GetOption( 'giftcard_refund_subject', esc_html__( 'Your gift card balance has being refunded', 'woo-smart-pack' ) );
    $message = WooZnd_Util::GetOption( 'giftcard_refund_message', wp_kses_post( __( 'Hi [wznd_wallet_name], <br /> Your gift card <strong>[wznd_coupon]</strong> remaining balance <strong>[wznd_amount]</strong> has been refunded to you wallet <strong>[wznd_wallet_number]</strong>. You can use this money to purchase item at [wznd_site_link] any time.', 'woo-smart-pack' ) ) );

    $to = $wooznd_giftcard[ 'to_email' ];
    if ( $wooznd_giftcard[ 'status' ] != WOOZND_GIFTCARD_STATUS_PENDING && $refund_mode == 'auto' ) {
        $to = $wooznd_giftcard[ 'from_email' ];
    }
    WooZnd_Util::SendMail( $to, do_shortcode( $subject ), do_shortcode( $message ) );
}
