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
                        <option value="<?php echo esc_attr( WOOZND_GIFTCARD_DELIVERY_EMAIL ); ?>"><?php echo esc_html__( 'Email Address', 'woo-smart-pack' ); ?></option>
                    </select>
                </div>
            </div>

            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Sender name', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input type="text" name="sender_name" placeholder="<?php echo esc_html__( 'Full name', 'woo-smart-pack' ); ?>">
                </div>
            </div>

            <div class="input-box last">
                <div class="label">
                    <span><?php echo esc_html__( 'Sender email', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input type="email" name="sender_email" placeholder="<?php echo esc_attr__( 'Email address', 'woo-smart-pack' ); ?>">
                </div>
            </div>

        </td>
        <td class="wide-second">
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Receiver name', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input type="text" name="receiver_name" placeholder="<?php echo esc_attr__( 'Full name', 'woo-smart-pack' ); ?>">
                </div>
            </div>

            <div class="input-box last">
                <div class="label">
                    <span><?php echo esc_html__( 'Receiver email', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input type="email" name="receiver_email" placeholder="<?php echo esc_attr__( 'Email address', 'woo-smart-pack' ); ?>">
                </div>
            </div>
        </td>
    </tr>
</table>
