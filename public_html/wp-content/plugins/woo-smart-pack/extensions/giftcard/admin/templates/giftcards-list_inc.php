<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$wooznd_nonce_action = basename(__FILE__);

wooznd_create_giftcard();
wooznd_update_giftcard();

$url_options = array();

// phpcs:disable WordPress.Security.NonceVerification.Recommended

if (isset($_GET['status'])) {

    $url_options['status'] = sanitize_text_field( wp_unslash( $_GET['status'] ) );
}

if (isset($_GET['search'])) {

    $url_options['search'] = sanitize_text_field( wp_unslash( $_GET['search'] ) );
}

if (isset($_GET['from'])) {

    $url_options['from'] = sanitize_text_field( wp_unslash( $_GET['from'] ) );
}

if (isset($_GET['to'])) {

    $url_options['to'] = sanitize_text_field( wp_unslash( $_GET['to'] ) );
}

if (isset($_GET['exp'])) {

    $url_options['exp'] = sanitize_text_field( wp_unslash( $_GET['exp'] ) );
}

if (isset($_GET['orderby'])) {

    $url_options['orderby'] = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
}

if (isset($_GET['order'])) {
  
    $url_options['order'] = sanitize_text_field( wp_unslash( $_GET['order'] ) );
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$url_format = admin_url('admin.php?page=wznd-manage-giftcard&pg={{page}}');

$default_url = admin_url('admin.php?page=wznd-manage-giftcard');

foreach ($url_options as $key => $value) {
    
    $url_format .= ('&' . $key . '={{' . $key . '}}');
    $default_url .= ('&' . $key . '={{' . $key . '}}');
}

$status = -1;

if (isset($_GET['status']) && $status != '') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    $status = sanitize_text_field( wp_unslash( $_GET['status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$search = isset( $_GET[ 'search' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'search' ] ) ) : '';
$orderby = isset( $_GET[ 'orderby' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ) ) : 'event';
$order = isset( $_GET[ 'order' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'order' ] ) ) : 'desc';
$srch = isset( $_GET[ 'search' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'search' ] ) ) . '%' : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended

if (isset($_GET['exp'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    $totals = WooZnd_GiftCardDB::GetExpiredGiftCardsCount($srch, $status);
} else {
    
    $totals = WooZnd_GiftCardDB::GetGiftCardsCount($srch, $status);
}


$pagesize = 25;
$pg = isset( $_GET[ 'pg' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'pg' ] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$paging = new WooZnd_Paginator( $totals, $pagesize, $pg );

if (isset($_GET['exp'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
 
    $rows = WooZnd_GiftCardDB::GetExpiredGiftCards($srch, $status, $paging->offset(), $paging->limit(), $orderby, $order);
} else {
    
    $rows = WooZnd_GiftCardDB::GetGiftCards($srch, $status, $paging->offset(), $paging->limit(), $orderby, $order);
}

function wooznd_create_giftcard() {
 
    global $wooznd_nonce_action;
    
    if (!isset($_POST['action_name'])) {
    
        return;
    }
    
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action_name']) && $_POST['action_name'] == 'addgiftcard') {

        $is_valid_nonce = ( isset($_POST['wznd_giftcard_nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wznd_giftcard_nonce'] ) ), basename(__FILE__)) ) ? true : false;
    
        if (!$is_valid_nonce) {
        
            return;
        }
        
        WooZnd_Util::UpdateOption('giftcard_buzy', 'yes');
        
        $attrs = WooZnd_GiftCardDB::GetAttributesFromSettings();
        $attrs['id'] = current_time('timestamp');
        $attrs['description'] = esc_html__('Gift Card Coupon', 'woo-smart-pack');

        $attrs['product_categories'] = array();
        $attrs['excluded_product_categories'] = array();


        $attrs[ 'discount_type' ] = isset( $_POST[ 'discount_type' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'discount_type' ] ) ) : '';
        $attrs[ 'coupon_code' ] = WooZnd_Util::GenRandomPattern( $attrs[ 'coupon_pattern' ] );
        $attrs[ 'amount' ] = isset( $_POST[ 'amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'amount' ] ) ) : 0;
        $attrs[ 'coupon_amount' ] = isset( $_POST[ 'coupon_amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'coupon_amount' ] ) ) : 0;
        
        $attrs[ 'apply_before_tax' ] = (isset( $_POST[ 'apply_before_tax' ] ) && 'yes' == sanitize_key( wp_unslash( $_POST[ 'apply_before_tax' ] ) ) ) ? 'yes' : 'no';
        $attrs[ 'free_shipping' ] = (isset( $_POST[ 'free_shipping' ] ) && 'yes' == sanitize_key( wp_unslash( $_POST[ 'free_shipping' ] ) ) ) ? 'yes' : 'no';
        
        $attrs[ 'send_date' ] = isset( $_POST[ 'send_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'send_date' ] ) ) : '';
        $attrs[ 'expiry_date' ] = isset( $_POST[ 'expiry_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'expiry_date' ] ) ) : '';
        $attrs[ 'minimum_amount' ] = isset( $_POST[ 'minimum_amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'minimum_amount' ] ) ) : '';
        $attrs[ 'maximum_amount' ] = isset( $_POST[ 'maximum_amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'maximum_amount' ] ) ) : '';
        
        $attrs[ 'exclude_sale_items' ] = (isset( $_POST[ 'exclude_sale_items' ] ) && 'yes' == sanitize_key( wp_unslash( $_POST[ 'exclude_sale_items' ] ) ) ) ? 'yes' : 'no';
        $attrs[ 'individual_use' ] = (isset( $_POST[ 'individual_use' ] ) && 'yes' == sanitize_key( wp_unslash( $_POST[ 'individual_use' ] ) ) ) ? 'yes' : 'no';
       
        $attrs[ 'usage_limit_per_user' ] = isset( $_POST[ 'usage_limit_per_user' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'usage_limit_per_user' ] ) ) : '';
        $attrs[ 'usage_limit' ] = isset( $_POST[ 'usage_limit' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'usage_limit' ] ) ) : '';
        $attrs[ 'pdf_template_id' ] = isset( $_POST[ 'pdf_template_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'pdf_template_id' ] ) ) : '';
        $attrs[ 'email_template_id' ] = isset( $_POST[ 'email_template_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'email_template_id' ] ) ) : '';
        $attrs[ 'message' ] = isset( $_POST[ 'message' ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ 'message' ] ) ) : '';
        $attrs[ 'delivery_method' ] = isset( $_POST[ 'delivery_method' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'delivery_method' ] ) ) : '';
        $attrs[ 'sender_name' ] = isset( $_POST[ 'sender_name' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'sender_name' ] ) ) : '';
        $attrs[ 'sender_email' ] = isset( $_POST[ 'sender_email' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'sender_email' ] ) ) : '';
        $attrs[ 'receiver_name' ] = isset( $_POST[ 'receiver_name' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'receiver_name' ] ) ) : '';
        $attrs[ 'receiver_email' ] = isset( $_POST[ 'receiver_email' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'receiver_email' ] ) ) : '';

        WooZnd_GiftCardDB::CreateGiftCard( $attrs );

        WooZnd_Util::UpdateOption( 'giftcard_buzy', 'no' );
    }
}

function wooznd_update_giftcard() {
   
    global $wooznd_nonce_action;
    
    if (!isset($_POST['action_name'])) {
    
        return;
    }
    
    if (isset( $_SERVER[ 'REQUEST_METHOD' ] ) && sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) == "POST" && isset( $_POST[ 'action_name' ] ) && sanitize_text_field( wp_unslash( $_POST['action_name'] ) ) == 'editgiftcard') {

        $is_valid_nonce = ( isset($_POST['wznd_giftcard_nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wznd_giftcard_nonce'] ) ), basename(__FILE__)) ) ? true : false;
    
        if (!$is_valid_nonce) {
        
            return;
        }


        WooZnd_Util::UpdateOption('giftcard_buzy', 'yes');
      
        if ( isset( $_POST[ 'delete' ] ) && $_POST[ 'delete' ] == 'Delete' ) {

            $giftcard_id = isset( $_POST[ 'giftcard_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'giftcard_id' ] ) ) : '';

            if ( !empty( $giftcard_id ) ) {

                WooZnd_GiftCardDB::DeleteGiftCard( $giftcard_id );
            }

            WooZnd_Util::UpdateOption( 'giftcard_buzy', 'no' );

            return;
        }

        $attrs = array();


        $attrs[ 'id' ] = isset( $_POST[ 'giftcard_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'giftcard_id' ] ) ) : 0;
        $attrs[ 'discount_type' ] = isset( $_POST[ 'discount_type' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'discount_type' ] ) ) : '';
        $attrs[ 'amount' ] =  isset( $_POST[ 'amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'amount' ] ) ) : 0;
        $attrs[ 'coupon_amount' ] = isset( $_POST[ 'coupon_amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'coupon_amount' ] ) ) : 0;
        $attrs[ 'coupon_code' ] = isset( $_POST[ 'coupon_code' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'coupon_code' ] ) ) : '';
        
        $attrs[ 'apply_before_tax' ] = (isset( $_POST[ 'apply_before_tax' ] ) && 'yes' == sanitize_key( wp_unslash( $_POST[ 'apply_before_tax' ] ) ) ) ? 'yes' : 'no';
        $attrs[ 'free_shipping' ] = (isset( $_POST[ 'free_shipping' ] ) && 'yes' == sanitize_key( wp_unslash( $_POST[ 'free_shipping' ] ) ) ) ? 'yes' : 'no';
        
        $attrs[ 'send_date' ] = isset( $_POST[ 'send_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'send_date' ] ) ) : '';
        $attrs[ 'expiry_date' ] = isset( $_POST[ 'expiry_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'expiry_date' ] ) ) : '';
        $attrs[ 'minimum_amount' ] = isset( $_POST[ 'minimum_amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'minimum_amount' ] ) ) : '';
        $attrs[ 'maximum_amount' ] = isset( $_POST[ 'maximum_amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'maximum_amount' ] ) ) : '';
        
        $attrs[ 'exclude_sale_items' ] = (isset( $_POST[ 'exclude_sale_items' ] ) && 'yes' == sanitize_key( wp_unslash( $_POST[ 'exclude_sale_items' ] ) ) ) ? 'yes' : 'no';
        $attrs[ 'individual_use' ] = (isset( $_POST[ 'individual_use' ] ) && 'yes' == sanitize_key( wp_unslash( $_POST[ 'individual_use' ] ) ) ) ? 'yes' : 'no';
        
        $attrs[ 'usage_limit_per_user' ] = isset( $_POST[ 'usage_limit_per_user' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'usage_limit_per_user' ] ) ) : '';
        $attrs[ 'usage_limit' ] = isset( $_POST[ 'usage_limit' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'usage_limit' ] ) ) : '';
        $attrs[ 'pdf_template_id' ] = isset( $_POST[ 'pdf_template_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'pdf_template_id' ] ) ) : '';
        $attrs[ 'email_template_id' ] = isset( $_POST[ 'email_template_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'email_template_id' ] ) ) : '';
        $attrs[ 'message' ] = isset( $_POST[ 'message' ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ 'message' ] ) ) : '';
        $attrs[ 'delivery_method' ] = isset( $_POST[ 'delivery_method' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'delivery_method' ] ) ) : '';
        $attrs[ 'sender_name' ] = isset( $_POST[ 'sender_name' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'sender_name' ] ) ) : '';
        $attrs[ 'sender_email' ] = isset( $_POST[ 'sender_email' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'sender_email' ] ) ) : '';
        $attrs[ 'receiver_name' ] = isset( $_POST[ 'receiver_name' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'receiver_name' ] ) ) : '';
        $attrs[ 'receiver_email' ] = isset( $_POST[ 'receiver_email' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'receiver_email' ] ) ) : '';
        $attrs[ 'status' ] = isset( $_POST[ 'status' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'status' ] ) ) : '';

        WooZnd_GiftCardDB::UpdateGiftCard( $attrs );

        WooZnd_Util::UpdateOption( 'giftcard_buzy', 'no' );
    }
}

function wooznd_get_giftcard_formetted_name($args) {
   
    if (!empty($args['to_name']) && !empty($args['to_email'])) {
    
        return $args['to_name'] . " (" . $args['to_email'] . ")";
    } else if (!empty($args['to_name'])) {
        
        return $args['to_name'];
    } else if (!empty($args['to_email'])) {
        
        return $args['to_email'];
    } else {
        
        return esc_html__('N/A', 'woo-smart-pack');
    }
}
