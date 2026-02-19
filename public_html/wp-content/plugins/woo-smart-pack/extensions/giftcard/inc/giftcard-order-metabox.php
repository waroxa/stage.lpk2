<?php
if ( !defined( 'ABSPATH' ) ) {

    exit;
}

add_action( 'add_meta_boxes', 'wooznd_add_giftcard_resend_metabox' );

if ( !function_exists( 'wooznd_add_giftcard_resend_metabox' ) ) {

    function wooznd_add_giftcard_resend_metabox() {

        add_meta_box( 'wznd_giftcard_css', esc_html__( 'Gift Card Receivers', 'woo-smart-pack' ), 'wooznd_giftcard_resend', 'woocommerce_page_wc-orders' );
    }

}

if ( !function_exists( 'wooznd_giftcard_resend' ) ) {

    function wooznd_giftcard_resend( $order ) {

        wp_nonce_field( basename( __FILE__ ), 'wznd_giftcard_nonce' );

        if ( !$order ) {

            return;
        }

        if ( count( $order->get_items() ) > 0 ) {

            $giftcards = [];

            foreach ( $order->get_items() as $item ) {

                $item_id = '';

                if ( $item[ 'type' ] == 'line_item' ) {

                    foreach ( $item[ 'item_meta' ] as $key => $value ) {

                        if ( $key == '_wznd_item_id' ) {

                            $item_id = $value;
                        }
                    }

                    if ( !empty( $item_id ) ) {

                        $gftcrd = WooZnd_GiftCardDB::GetGiftCard( $item_id );

                        if ( isset( $gftcrd[ 'id' ] ) ) {

                            $giftcards[] = $gftcrd;
                        }
                    }
                }
            }

            $cnt = 0;

            foreach ( $giftcards as $giftcard ) {

                $cnt++;
                ?>
                <p> 
                    <input type="text" name="giftcard_resend[<?php echo esc_attr( $item_id ); ?>][toname]" value="<?php echo esc_attr( $giftcard[ 'to_name' ] ); ?>" placeholder="<?php echo esc_attr__( 'Recipient name:', 'woo-smart-pack' ); ?>" />
                    <input type="email" name="giftcard_resend[<?php echo esc_attr( $item_id ); ?>][toemail]" value="<?php echo esc_attr( $giftcard[ 'to_email' ] ); ?>" placeholder="<?php echo esc_attr__( 'Recipient email:', 'woo-smart-pack' ); ?>" />
                    <input type="text" value="<?php echo esc_attr( strtoupper( WooZnd_GiftCardDB::GetCouponCodeByGiftCardId( $item_id ) ) ); ?>" disabled="disabled" />
                </p>        
                <?php
            }

            if ( $cnt > 0 ) {
                ?>
                <p> 
                    <input type="submit" value="<?php echo esc_attr__( 'Resend Gift Cards', 'woo-smart-pack' ); ?>" name="_wznd_giftcard_resend" class="button button-primary" />
                </p>
                <?php
            } else {
                ?>
                <p>
                    <?php echo esc_html__( 'No gift card found in this order.', 'woo-smart-pack' ); ?>
                </p>
                <?php
            }
        }
    }

}

add_action( 'save_post', 'wooznd_giftcard_resend_meta_save' );


if ( !function_exists( 'wooznd_giftcard_resend_meta_save' ) ) {

    function wooznd_giftcard_resend_meta_save( $post_id ) {

        // Checks save status
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );

        $is_valid_nonce = ( isset( $_POST[ 'wznd_giftcard_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_giftcard_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

        // Exits script depending on save status
        if ( $is_autosave || $is_revision || !$is_valid_nonce ) {

            return;
        }

        // Checks for input and sanitizes/saves if needed
        if ( isset( $_POST[ '_wznd_giftcard_resend' ] ) ) {

            $resend_gifts = isset( $_POST[ 'giftcard_resend' ] ) ? wooznd_sanitize_giftcard_order_meta_posted_data( $_POST[ 'giftcard_resend' ] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            WooZnd_Util::UpdateOption( 'giftcard_buzy', 'yes' );

            foreach ( $resend_gifts as $key => $gift ) {

                WooZnd_GiftCardDB::UpdateReceiver( $key, $gift[ 'toname' ], $gift[ 'toemail' ] );
            }

            WooZnd_Util::UpdateOption( 'giftcard_buzy', 'no' );
        }
    }

}

if ( !function_exists( 'wooznd_get_giftcard_order_meta_posted_data' ) ) {

    function wooznd_sanitize_giftcard_order_meta_posted_data( $posted_data ) {

        $data = array();

        if ( !is_array( $posted_data ) ) {

            return $data;
        }

        foreach ( $posted_data as $data_key => $data_value ) {

            $key = sanitize_key( wp_unslash( $data_key ) );

            $data[ $key ][ 'toname' ] = sanitize_text_field( wp_unslash( $data_value[ 'toname' ] ) );
            $data[ $key ][ 'toemail' ] = sanitize_email( wp_unslash( $data_value[ 'toemail' ] ) );
        }

        return $data;
    }

}


