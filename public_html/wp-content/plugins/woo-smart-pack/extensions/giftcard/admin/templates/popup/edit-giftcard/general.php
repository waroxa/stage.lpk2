<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?><table class="woo-wide-form">
    <tr>
        <td>
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Discount Type', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input select-box">
                    <select name="discount_type">                            
                        <option value="fixed_cart"><?php echo esc_html__( 'Cart Discount', 'woo-smart-pack' ); ?></option>
                        <option value="fixed_product"<?php echo ($row[ 'discount_type' ] == 'fixed_product') ? ' selected="selected"' : ''; ?>><?php echo esc_html__( 'Product Discount', 'woo-smart-pack' ); ?></option>
                    </select>
                </div>
            </div>            
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Coupon Code', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input name="coupon_code" value="<?php echo esc_attr( $row[ 'coupon' ] ); ?>" type="text" placeholder="<?php echo esc_attr__( 'Coupon code', 'woo-smart-pack' ); ?>">
                </div>
            </div>          
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Gift Card Amout', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input name="amount" type="number" value="<?php echo number_format( $row[ 'amount' ], 2 ); ?>" min="0" step="0.05" value="20.00" placeholder="0.00">
                </div>
            </div>
            <div class="input-box last">
                <div class="label">
                    <span><?php echo esc_html__( 'Gift Card Balance', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input name="coupon_amount" type="number" value="<?php echo number_format( $row[ 'coupon_amount' ], 2 ); ?>" min="0" step="0.05" value="20.00" placeholder="0.00">
                </div>
            </div>

        </td>
        <td class="wide-second">
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Apply gift cards before tax', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input check-box">
                    <input id="apply_before_tax" name="apply_before_tax" value="yes"<?php echo ($row[ 'apply_before_tax' ] == 'yes') ? ' checked="checked"' : ''; ?> type="checkbox" />
                    <label for="apply_before_tax"><?php echo esc_html__( 'Apply gift cards before tax', 'woo-smart-pack' ); ?></label>
                </div>
            </div>
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Allow free shipping', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input check-box">
                    <input id="free_shipping" name="free_shipping" value="yes"<?php echo ($row[ 'free_shipping' ] == 'yes') ? ' checked="checked"' : ''; ?> type="checkbox" />
                    <label for="free_shipping"><?php echo esc_html__( 'Check this box if the gift card grants free shipping. A free shipping method must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'woo-smart-pack' ); ?></label>
                </div>
            </div>
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Event Date', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input name="send_date" type="text" value="<?php echo esc_attr( WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'send_date' ], 'Y-m-d' ) ); ?>" class="pop_datepicker" value="<?php ?>" placeholder="<?php echo esc_attr__( 'Event Date', 'woo-smart-pack' ); ?>">
                </div>
            </div>

            <div class="input-box last">
                <div class="label">
                    <span><?php echo esc_html__( 'Expiry Date', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input name="expiry_date" type="text" value="<?php echo esc_attr( WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'expiry_date' ], 'Y-m-d' ) ); ?>" class="pop_datepicker" value="" placeholder="<?php echo esc_attr__( 'Expiry Date', 'woo-smart-pack' ); ?>">
                </div>
            </div>
        </td>
    </tr>
</table>
