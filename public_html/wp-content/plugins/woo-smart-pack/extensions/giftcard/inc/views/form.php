<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$allowed_html = WooZnd_Init::get_instance()->get_allow_html();

// phpcs:disable WordPress.Security.NonceVerification.Missing
if ( $price_type == 'range' ) {
 
    $price_from = get_post_meta( $product->get_id(), '_wznd_giftcard_from_price', true );
    $price_to = get_post_meta( $product->get_id(), '_wznd_giftcard_to_price', true );
    
    ?>
    <div class="wznd_gift_card_input wznd_gift_card_price">
        <?php echo esc_html__( 'Enter Gift Price:', 'woo-smart-pack' ); ?> 
        <input type="number" name="gift_price" placeholder="<?php echo esc_attr__( 'Price', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( $price_from ); ?>" min="<?php echo esc_attr($price_from); ?>" max="<?php echo esc_attr( $price_to ); ?>" step="1" />
    </div>
    <?php
} else if ( $price_type == 'select' ) {
    ?>
    <div class="wznd_gift_card_input wznd_gift_card_price">
        <?php echo esc_html__( 'Enter Gift Price:', 'woo-smart-pack' ); ?> 
        <select name="gift_price">
            <?php
            $prices = str_getcsv( get_post_meta( $product->get_id(), '_wznd_giftcard_select_price', true ), '|' );
            if ( is_array( $prices ) ) {
                foreach ( $prices as $price ) {
                    ?>
            <option value="<?php echo esc_attr( $price ); ?>"><?php echo wp_kses( wc_price( $price ), $allowed_html ); ?></option>
                    <?php
                }
            }
            ?>
        </select>
    </div>
    <?php
} else if ( $price_type == 'user' ) {
    ?>
    <div class="wznd_gift_card_input wznd_gift_card_price">
        <p><?php echo esc_html__( 'Enter Gift Price:', 'woo-smart-pack' ); ?></p> <input type="text" name="gift_price" placeholder="<?php echo esc_attr__( 'Price', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( isset( $_POST[ 'gift_price' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'gift_price' ] ) ) : $product->get_price()  ); ?>" />
    </div>
    <?php
} else {
    ?>
<input type="hidden" name="gift_price" value="<?php echo esc_attr( isset( $_POST[ 'gift_price' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'gift_price' ] ) ) : $product->get_price()  ); ?>" />
    <?php
}

if ( empty( $delivary_mathod ) && $delivary_mathod == '' ) {
    ?>
    <div class="wznd_gift_card_input">
        <p><?php echo esc_html__( 'Delivery Method:', 'woo-smart-pack' ); ?></p>
    </div>
    <div class="wznd_gift_card_input wznd_gift_card_delivery">
        <select name="delivary_method">
            <option<?php echo (isset( $_POST[ 'delivary_method' ] ) && $_POST[ 'delivary_method' ] == WOOZND_GIFTCARD_DELIVERY_OFFLINE) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr(WOOZND_GIFTCARD_DELIVERY_OFFLINE); ?>"><?php echo esc_html__( 'Print & Send', 'woo-smart-pack' ); ?></option>
            <option<?php echo (isset( $_POST[ 'delivary_method' ] ) && $_POST[ 'delivary_method' ] == WOOZND_GIFTCARD_DELIVERY_SHIP) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr(WOOZND_GIFTCARD_DELIVERY_SHIP); ?>"><?php echo esc_html__( 'Shipping Address', 'woo-smart-pack' ); ?></option>
            <option<?php echo ((isset( $_POST[ 'delivary_method' ] ) && $_POST[ 'delivary_method' ] == WOOZND_GIFTCARD_DELIVERY_EMAIL) || (!isset( $_POST[ 'delivary_method' ] ) || sanitize_text_field( wp_unslash( $_POST[ 'delivary_method' ] ) ) == '')) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( WOOZND_GIFTCARD_DELIVERY_EMAIL ); ?>"><?php echo esc_html__( 'Email Address', 'woo-smart-pack' ); ?></option>
        </select>
    </div>
    <?php
} else {
    ?>
<input type="hidden" name="delivary_method" value="<?php echo esc_attr( isset( $_POST[ 'delivary_method' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'delivary_method' ] ) ) : WOOZND_GIFTCARD_DELIVERY_EMAIL  ); ?>" />
    <?php
}

if ( (empty( $delivary_mathod ) && $delivary_mathod == '') || $delivary_mathod == WOOZND_GIFTCARD_DELIVERY_EMAIL ) {
    ?>
    <div class="wznd_gift_card_input<?php echo esc_attr( $hide_email_fields ); ?>">
        <p><?php echo esc_html__( 'Send gift card to:', 'woo-smart-pack' ); ?></p>
    </div>
    <?php
    if ( empty( $show_receiver_name ) || $show_receiver_name == 'yes' ) {
        ?>
        <div class="wznd_gift_card_input<?php echo esc_attr( $hide_email_fields ); ?>">
            <input type="text" name="send_to_name" placeholder="<?php echo esc_html__( 'Recipient name', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( isset( $_POST[ 'send_to_name' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'send_to_name' ] ) ) : ''  ); ?>" />
        </div>
        <?php
    }
    if ( empty( $show_receiver_email ) || $show_receiver_email == 'yes' ) {
        ?>
        <div class="wznd_gift_card_input<?php echo esc_attr( $hide_email_fields ); ?>">            
            <input type="text" name="send_to_email" placeholder="<?php echo esc_html__( 'Recipient email', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( isset( $_POST[ 'send_to_email' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'send_to_email' ] ) ) : ''  ); ?>" />
        </div>
        <?php
    }

    if ( $show_sender_name == 'yes' ) {
        ?>
        <div class="wznd_gift_card_input<?php echo esc_attr( $hide_email_fields ); ?>">            
            <input type="text" name="sender_name" placeholder="<?php echo esc_html__( 'Your name (optional)', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( isset( $_POST[ 'send_to_email' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'send_to_email' ] ) ) : ''  ); ?>" />
        </div>
        <?php
    }
    if ( $show_sender_email == 'yes' ) {
        ?>
        <div class="wznd_gift_card_input<?php echo esc_attr( $hide_email_fields ); ?>">            
            <input type="text" name="sender_email" placeholder="<?php echo esc_html__( 'Your email (optional)', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( isset( $_POST[ 'send_to_email' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'send_to_email' ] ) ) : ''  ); ?>" />
        </div>
        <?php
    }
    if ( empty( $show_message ) || $show_message == 'yes' ) {
        
        $allowed_html = WooZnd_Init::get_instance()->get_allow_html();
        $msg = '';

        if ( isset( $_POST[ 'send_to_message' ] ) ) {

            $msg = sanitize_textarea_field( wp_unslash( $_POST[ 'send_to_message' ] ) );
        }
        ?>
        <div class="wznd_gift_card_input">
            <textarea  name="send_to_message" placeholder="<?php echo esc_html__( 'Gift card message', 'woo-smart-pack' ); ?>"><?php echo wp_kses( $msg, $allowed_html ); ?></textarea>
        </div>
        <?php
    }
    ?>
    <?php
}
$send_date = get_post_meta( $product->get_id(), '_wznd_giftcard_allow_send_date', true );
if ( $send_date == 'yes' ) {
    ?>
    <div class="wznd_gift_card_input">
        <input type="text" class="datepicker" name="send_date" placeholder="<?php echo esc_html__( 'Gift card Date', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( isset( $_POST[ 'send_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'send_date' ] ) ) : current_time( 'Y-m-d' )  ); ?>" />
    </div>
    <?php
} else {
    ?>
    <input type="hidden" name="send_date" value="<?php echo esc_attr( isset( $_POST[ 'send_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'send_date' ] ) ) : current_time( 'Y-m-d' )  ); ?>" />
    <?php
}

// phpcs:enable WordPress.Security.NonceVerification.Missing