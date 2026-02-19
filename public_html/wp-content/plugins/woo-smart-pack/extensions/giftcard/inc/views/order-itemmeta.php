<?php

if ( !defined( 'ABSPATH' ) ) {
  
    exit;
}

?><div class="view">
    <table class="display_meta" cellspacing="0">                        
        <?php
        if ( !empty( $coupon_code ) ) {
            ?>

            <tr>
                <?php
                if ( WooZnd_Util::GetOption( 'giftcard_codechart_type', 'br' ) == 'br' ) {
                    ?>

                    <td colspan="2">
                        <div class="wznd_barcode">
                            <img src="<?php echo esc_attr( WP_CONTENT_URL . '/uploads/woo-smart-pack/barcodes/br_' . $coupon_code . '.png' ); ?>" alt="" />
                            <br />
                            <?php echo esc_html( strtoupper( $coupon_code ) ); ?>
                        </div>
                    </td>
                    <?php
                } else {
                    ?>

                    <td colspan="2">
                        <div class="wznd_qrcode">
                            <img src="<?php echo esc_url( WP_CONTENT_URL . '/uploads/woo-smart-pack/qrcodes/qr_' . $coupon_code . '.png' ); ?>" alt="" />
                            <br />
                            <?php echo esc_html( strtoupper( $coupon_code ) ); ?>
                        </div>
                    </td>
                    <?php
                }
                ?>                
            </tr>
            <tr>
                <th><?php echo esc_html__( 'Coupon:', 'woo-smart-pack' ); ?></th>
                <td><?php echo esc_html( strtoupper( $coupon_code ) ); ?></td>
            </tr> 
            <?php
        }


        $allow_html = WooZnd_Init::get_instance()->get_allow_html();

        foreach ( $all_meta_data as $data_meta_key => $value ) {
           
            if ( $data_meta_key == '_wznd_delivery_method' ) {
            
                $delivary_method = esc_html__( 'Email Address', 'woo-smart-pack' );
                
                if ( $value == WOOZND_GIFTCARD_DELIVERY_OFFLINE ) {
                
                    $delivary_method = esc_html__( 'Print & Send', 'woo-smart-pack' );
                }
                
                if ( $value == WOOZND_GIFTCARD_DELIVERY_SHIP ) {
                
                    $delivary_method = esc_html__( 'Shipping Address', 'woo-smart-pack' );
                }
                ?>
                <tr>
                    <th><?php echo esc_html__( 'Delivery Method:', 'woo-smart-pack' ); ?></th>
                    <td><?php echo esc_html( $delivary_method ); ?></td>
                </tr>
                <?php
            }
            if ( $data_meta_key == '_wznd_send_to_name' ) {
                ?>
                <tr>
                    <th style="min-width: 95px;"><?php echo esc_html__( 'Recipient name:', 'woo-smart-pack' ); ?></th>
                    <td><p><?php echo wp_kses( $value, $allow_html ); ?></p></td>
                </tr>
                <?php
            }
            if ( $data_meta_key == '_wznd_send_to_email' ) {
                ?>
                <tr>
                    <th><?php echo esc_html__( 'Recipient email:', 'woo-smart-pack' ); ?></th>
                    <td><?php echo wp_kses( $value, $allow_html ); ?></td>
                </tr>
                <?php
            }
            if ( $data_meta_key == '_wznd_send_to_message' ) {
                ?>
                <tr>
                    <th><?php echo esc_html__( 'Message:', 'woo-smart-pack' ); ?></th>
                    <td><?php echo wp_kses( $value, $allow_html ); ?></td>
                </tr>
                <?php
            }
            if ( $data_meta_key == '_wznd_send_date' ) {
                ?>
                <tr>
                    <th><?php echo esc_html__( 'Send Date:', 'woo-smart-pack' ); ?></th>
                    <td><?php echo wp_kses( $value, $allow_html ); ?></td>
                </tr>
                <?php
            }
            if ( $data_meta_key == '_wznd_sender_name' ) {
                ?>
                <tr>
                    <th><?php echo esc_html__( 'Sender name:', 'woo-smart-pack' ); ?></th>
                    <td><?php echo wp_kses( $value, $allow_html ); ?></td>
                </tr>
                <?php
            }

            if ( $data_meta_key == '_wznd_sender_email' ) {
                ?>
                <tr>
                    <th><?php echo esc_html__( 'Sender email:', 'woo-smart-pack' ); ?></th>
                    <td><?php echo wp_kses( $value, $allow_html ); ?></td>
                </tr>
                <?php
            }
        }
        if ( !empty( $coupon_code ) ) {
            ?>
            <tr>
                <td colspan="2"><a href="<?php echo esc_url( WP_CONTENT_URL . '/uploads/woo-smart-pack/giftcards/giftcard' . $item_id . '.pdf' ); ?>" target="_blank"><?php echo esc_html__( 'Download Gift Card', 'woo-smart-pack' ); ?></a></td>
            </tr>
            <?php
        }
        ?>
    </table>
</div>