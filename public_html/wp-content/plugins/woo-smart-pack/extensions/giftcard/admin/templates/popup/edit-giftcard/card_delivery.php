<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?><table class="woo-wide-form">
    <tr>
        <td>

            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Delivary Method', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input select-box">
                    <select name="delivery_method" class="wc-enhanced-select">                            
                        <option value="<?php echo esc_attr( WOOZND_GIFTCARD_DELIVERY_OFFLINE ); ?>"><?php echo esc_html__( 'Print & Send', 'woo-smart-pack' ); ?></option>
                        <option value="<?php echo esc_attr( WOOZND_GIFTCARD_DELIVERY_EMAIL ); ?>"<?php echo ($row[ 'delivery_method' ] == WOOZND_GIFTCARD_DELIVERY_EMAIL) ? ' selected="selected"' : ''; ?>><?php echo esc_html__( 'Email Address', 'woo-smart-pack' ); ?></option>
                    </select>
                </div>
            </div>

            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Sender name', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input type="text" name="sender_name" value="<?php echo esc_attr( $row[ 'from_name' ] ); ?>" placeholder="<?php echo esc_html__( 'Full name', 'woo-smart-pack' ); ?>">
                </div>
            </div>

            <div class="input-box last">
                <div class="label">
                    <span><?php echo esc_html__( 'Sender email', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input type="email" name="sender_email" value="<?php echo esc_attr( $row[ 'from_email' ] ); ?>" placeholder="<?php echo esc_html__( 'Email address', 'woo-smart-pack' ); ?>">
                </div>
            </div>

        </td>
        <td class="wide-second">
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Receiver name', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input type="text" name="receiver_name" value="<?php echo esc_attr( $row[ 'to_name' ] ); ?>" placeholder="<?php echo esc_html__( 'Full name', 'woo-smart-pack' ); ?>">
                </div>
            </div>

            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Receiver email', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input type="email" name="receiver_email" value="<?php echo esc_attr( $row[ 'to_email' ] ); ?>" placeholder="<?php echo esc_attr__( 'Email address', 'woo-smart-pack' ); ?>">
                </div>
            </div>

            <div class="input-box last">
                <div class="label">
                    <span><?php echo esc_html__( 'Status', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input select-box">
                    <select name="status" class="wc-enhanced-select">                            
                        <option value="<?php echo esc_attr( WOOZND_GIFTCARD_STATUS_PENDING ); ?>"><?php echo esc_html__( 'Pending', 'woo-smart-pack' ); ?></option>
                        <option value="<?php echo esc_attr( WOOZND_GIFTCARD_STATUS_SENT ); ?>"<?php echo ($row[ 'status' ] == WOOZND_GIFTCARD_STATUS_SENT) ? ' selected="selected"' : ''; ?>><?php echo esc_html__( 'Sent', 'woo-smart-pack' ); ?></option>
                        <option value="<?php echo esc_attr( WOOZND_GIFTCARD_STATUS_USED ); ?>"<?php echo ($row[ 'status' ] == WOOZND_GIFTCARD_STATUS_USED) ? ' selected="selected"' : ''; ?>><?php echo esc_html__( 'Used', 'woo-smart-pack' ); ?></option>
                        <option value="<?php echo esc_attr( WOOZND_GIFTCARD_STATUS_EXHAUSTED ); ?>"<?php echo ($row[ 'status' ] == WOOZND_GIFTCARD_STATUS_EXHAUSTED) ? ' selected="selected"' : ''; ?>><?php echo esc_html__( 'Exhausted', 'woo-smart-pack' ); ?></option>
                        <option value="<?php echo esc_attr( WOOZND_GIFTCARD_STATUS_REFUNDED ); ?>"<?php echo ($row[ 'status' ] == WOOZND_GIFTCARD_STATUS_REFUNDED) ? ' selected="selected"' : ''; ?>><?php echo esc_html__( 'Refunded', 'woo-smart-pack' ); ?></option>
                    </select>
                </div>
            </div>
        </td>
    </tr>
</table>
