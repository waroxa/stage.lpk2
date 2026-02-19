<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include_once ('inc/class-giftcard-settings.php');

if (WooZnd_Util::GetOption('enable_giftcard', 'yes') == 'yes') {
   
    include_once ('inc/giftcard-activate.php');
    include_once ('inc/mpdf/giftcard-pdf.php');
    include_once ('inc/qrcode-barcode/code_image.php');
    include_once ('inc/class-giftcard-db.php');

    include_once ('admin/giftcard-admin.php');
    include_once ('inc/giftcard-template.php');
    include_once ('inc/giftcard-product-metabox.php');
    include_once ('inc/giftcard-order-metabox.php');
    include_once ('inc/payment-gateway.php');
    include_once ('inc/class-giftcard.php');
    include_once ('shortcodes.php');
    include_once ('inc/email.php');
    include_once ('widgets/widgets.php');

    add_action('woocommerce_init', 'WooZnd_GiftCard::Init');
}
