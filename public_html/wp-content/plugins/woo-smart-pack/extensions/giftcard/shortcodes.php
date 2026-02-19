<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

function wznd_giftcard_coupon( $atts, $content = "" ) {

    global $wooznd_giftcard;

    if ( !isset( $wooznd_giftcard ) ) {

        return '';
    }

    ob_start();

    echo esc_html( strtoupper( $wooznd_giftcard[ 'coupon' ] ) );

    return ob_get_clean();
}

function wznd_giftcard_message( $atts, $content = "" ) {

    global $wooznd_giftcard;

    if ( !isset( $wooznd_giftcard ) ) {

        return;
    }

    $allowed_html = WooZnd_Init::get_instance()->get_allow_html();
    
    ob_start();

    echo wp_kses( $wooznd_giftcard[ 'message' ], $allowed_html );

    return ob_get_clean();
}

function wznd_giftcard_amount( $atts, $content = "" ) {

    global $wooznd_giftcard;

    if ( !isset( $wooznd_giftcard ) ) {

        return;
    }
    
    $allowed_html = WooZnd_Init::get_instance()->get_allow_html();

    ob_start();

    echo wp_kses( wc_price( $wooznd_giftcard[ 'amount' ] ), $allowed_html );

    return ob_get_clean();
}

function wznd_giftcard_expirydate( $atts, $content = "" ) {

    global $wooznd_giftcard;

    if ( !isset( $wooznd_giftcard ) ) {

        return;
    }

    ob_start();

    echo esc_html( WooZnd_Util::MySQLTimeStampToDataTime( $wooznd_giftcard[ 'expiry_date' ], get_option( 'date_format' ) ) );

    return ob_get_clean();
}

function wznd_giftcard_toname( $atts, $content = "" ) {

    global $wooznd_giftcard;

    if ( !isset( $wooznd_giftcard ) ) {

        return;
    }

    ob_start();

    echo esc_html( $wooznd_giftcard[ 'to_name' ] );

    return ob_get_clean();
}

function wznd_giftcard_toemail( $atts, $content = "" ) {

    global $wooznd_giftcard;

    if ( !isset( $wooznd_giftcard ) ) {

        return;
    }

    ob_start();

    echo esc_html( $wooznd_giftcard[ 'to_email' ] );

    return ob_get_clean();
}

function wznd_giftcard_fromname( $atts, $content = "" ) {

    global $wooznd_giftcard;

    if ( !isset( $wooznd_giftcard ) ) {

        return;
    }

    ob_start();

    $fromname = $wooznd_giftcard[ 'from_name' ];

    if ( trim( $fromname ) == '' ) {

        ob_start();

        $fromname = get_user_by( 'email', $wooznd_giftcard[ 'from_email' ] )->display_name;

        ob_clean();
    }

    echo esc_html( $fromname );

    return ob_get_clean();
}

function wznd_giftcard_fromemail( $atts, $content = "" ) {

    global $wooznd_giftcard;

    if ( !isset( $wooznd_giftcard ) ) {

        return;
    }

    ob_start();

    echo esc_html( $wooznd_giftcard[ 'from_email' ] );

    return ob_get_clean();
}

function wznd_giftcard_qrcode( $atts, $content = "" ) {

    global $wooznd_giftcard;

    if ( !isset( $wooznd_giftcard ) ) {

        return;
    }

    ob_start();

    $alt = isset( $atts[ 'alt' ] ) ? $atts[ 'alt' ] : '';

    $url = WP_CONTENT_URL . '/uploads/woo-smart-pack/qrcodes/qr_' . $wooznd_giftcard[ 'coupon' ] . '.png';

    ?>
    <img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" />
    <?php

    return ob_get_clean();
}

function wznd_giftcard_barcode( $atts, $content = "" ) {

    global $wooznd_giftcard;

    if ( !isset( $wooznd_giftcard ) ) {

        return;
    }

    ob_start();

    $alt = isset( $atts[ 'alt' ] ) ? $atts[ 'alt' ] : '';

    $url = WP_CONTENT_URL . '/uploads/woo-smart-pack/barcodes/br_' . $wooznd_giftcard[ 'coupon' ] . '.png';

    ?>
    <img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" />
    <?php

    return ob_get_clean();
}

function wznd_giftcard_check_view( $atts, $content = "" ) {

    wp_nonce_field( basename( __FILE__ ), 'wznd_giftcard_ee_nonce' );

    $inline_view = (isset( $atts[ 'inline' ] ) && $atts[ 'inline' ] == 'true');
    $title_text = isset( $atts[ 'title' ] ) ? $atts[ 'title' ] : '';
    $placeholder_text = isset( $atts[ 'placeholder' ] ) ? $atts[ 'placeholder' ] : esc_html__( 'Enter code', 'woo-smart-pack' );
    $button_text = isset( $atts[ 'button_text' ] ) ? $atts[ 'button_text' ] : esc_html__( 'Check', 'woo-smart-pack' );

    $amount_label = isset( $atts[ 'amount_text' ] ) ? $atts[ 'amount_text' ] : esc_html__( 'Amount:', 'woo-smart-pack' );
    $balance_label = isset( $atts[ 'balance_label' ] ) ? $atts[ 'balance_label' ] : esc_html__( 'Remaining balance:', 'woo-smart-pack' );
    $sent_to_label = isset( $atts[ 'sent_to_label' ] ) ? $atts[ 'sent_to_label' ] : esc_html__( 'Sent to:', 'woo-smart-pack' );
    $expiry_date_label = isset( $atts[ 'expiry_date_label' ] ) ? $atts[ 'expiry_date_label' ] : esc_html__( 'Expiry date:', 'woo-smart-pack' );
    
    $allowed_html = WooZnd_Init::get_instance()->get_allow_html();
    
    ?>
    <form action="" method="post"> 
        <input type="hidden" name="wznd_check_action" value="yes" />  
        <p class="wooznd_wallet_deposit">
            <?php
            if ( !empty( $title_text ) ) {
                ?>
            <h3><?php echo esc_html( $title_text ); ?></h3>
            <?php
        }
        ?>        
            <input type="text" name="giftcard_coupon" value="<?php echo isset( $_POST[ 'giftcard_coupon' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST[ 'giftcard_coupon' ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>" placeholder="<?php echo esc_attr( $placeholder_text ); ?>" />        
        <button><?php echo esc_html( $button_text ); ?></button>
    </p>
    <?php
    
    if ( $_SERVER[ 'REQUEST_METHOD' ] == "POST" && isset( $_POST[ 'wznd_check_action' ] ) && $_POST[ 'wznd_check_action' ] == 'yes' ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.NonceVerification.Missing

        $giftcard = WooZnd_GiftCardDB::GetGiftCardByCode( sanitize_text_field( wp_unslash( $_POST[ 'giftcard_coupon' ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if ( isset( $giftcard[ 'id' ] ) ) {
    
            if ( $inline_view == true ) {
            
                ?>
                <p>
                    <strong><?php echo esc_html( $amount_label ); ?></strong>
                    <span><?php echo wp_kses( isset( $giftcard[ 'amount' ] ) ? wc_price( $giftcard[ 'amount' ] ) : esc_html__( 'N/A', 'woo-smart-pack' ), $allowed_html ); ?></span>
                    <strong><?php echo esc_html( $balance_label ); ?></strong>
                    <span> <?php echo wp_kses( isset( $giftcard[ 'coupon_amount' ] ) ? wc_price( $giftcard[ 'coupon_amount' ] ) : esc_html__( 'N/A', 'woo-smart-pack' ), $allowed_html ); ?></span>
                    <strong><?php echo esc_html( $sent_to_label ); ?></strong>
                    <span><?php echo isset( $giftcard[ 'to_name' ] ) ? esc_html( $giftcard[ 'to_name' ] ) : esc_html__( 'N/A', 'woo-smart-pack' ); ?>
                        <?php echo isset( $giftcard[ 'to_name' ] ) ? esc_html( '(' . $giftcard[ 'to_email' ] . ')' ) : ''; ?></span>
                    <strong><?php echo esc_html( $expiry_date_label ); ?></strong>
                    <span><?php echo esc_html( isset( $giftcard[ 'to_name' ] ) ? WooZnd_Util::MySQLTimeStampToDataTime( $giftcard[ 'expiry_date' ], get_option( 'date_format' ) ) : esc_html__( 'N/A', 'woo-smart-pack' ) ); ?></span>
                </p>
                <?php
            } else {
                ?>
                <table class="woocommerce-table woocommerce-table--customer-details shop_table customer_details">
                    <tr>
                        <th style="width: 250px;"><?php echo esc_html( $amount_label ); ?></th>
                        <td><?php echo wp_kses( isset( $giftcard[ 'amount' ] ) ? wc_price( $giftcard[ 'amount' ] ) : esc_html__( 'N/A', 'woo-smart-pack' ), $allowed_html ); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html( $balance_label ); ?></th>
                        <td><?php echo wp_kses( isset( $giftcard[ 'coupon_amount' ] ) ? wc_price( $giftcard[ 'coupon_amount' ] ) : esc_html__( 'N/A', 'woo-smart-pack' ), $allowed_html ); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html( $sent_to_label ); ?></th>
                        <td>
                            <?php echo isset( $giftcard[ 'to_name' ] ) ? esc_html( $giftcard[ 'to_name' ] ) : esc_html__( 'N/A', 'woo-smart-pack' ); ?>
                            <?php echo isset( $giftcard[ 'to_name' ] ) ? esc_html( '(' . $giftcard[ 'to_email' ] . ')' ) : ''; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html( $expiry_date_label ); ?></th>
                        <td><?php echo esc_html( isset( $giftcard[ 'to_name' ] ) ? WooZnd_Util::MySQLTimeStampToDataTime( $giftcard[ 'expiry_date' ], get_option( 'date_format' ) ) : esc_html__( 'N/A', 'woo-smart-pack' ) ); ?></td>
                    </tr>
                </table>
                <?php
            }
        } else {
            
            echo '<p>';
            echo esc_html__( 'Invalid gift card code.', 'woo-smart-pack' );
            echo '</p>';
        }
    }
    ?>        
    </form>    
    <?php
}

add_shortcode( 'wznd_coupon', 'wznd_giftcard_coupon' );
add_shortcode( 'wznd_message', 'wznd_giftcard_message' );
add_shortcode( 'wznd_amount', 'wznd_giftcard_amount' );
add_shortcode( 'wznd_expirydate', 'wznd_giftcard_expirydate' );
add_shortcode( 'wznd_toname', 'wznd_giftcard_toname' );
add_shortcode( 'wznd_toemail', 'wznd_giftcard_toemail' );
add_shortcode( 'wznd_fromname', 'wznd_giftcard_fromname' );
add_shortcode( 'wznd_fromemail', 'wznd_giftcard_fromemail' );

add_shortcode( 'wznd_qrcode', 'wznd_giftcard_qrcode' );
add_shortcode( 'wznd_barcode', 'wznd_giftcard_barcode' );
add_shortcode( 'wznd_giftcard_check', 'wznd_giftcard_check_view' );
