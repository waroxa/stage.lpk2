<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?><table class="woo-wide-form">
    <tr>
        <td>
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Minimum spend', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input name="minimum_amount" type="number" value="<?php echo esc_attr( $row[ 'minimum_amount' ] ); ?>" min="0" step="0.05" placeholder="<?php echo esc_html__( 'No Minimum', 'woo-smart-pack' ) ?>">
                </div>
            </div>
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Maximum spend', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input name="maximum_amount" type="number" value="<?php echo esc_attr( $row[ 'maximum_amount' ] ); ?>" min="0" step="0.05" placeholder="<?php echo esc_html__( 'No Maximum', 'woo-smart-pack' ) ?>">
                </div>
            </div>

            <div class="input-box last">
                <div class="label">
                    <span><?php echo esc_html__( 'Exclude sale items', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input check-box">
                    <input id="exclude_sale_items" name="exclude_sale_items" value="yes"<?php echo ($row[ 'exclude_sale_items' ] == 'yes') ? ' checked="checked"' : ''; ?> type="checkbox" />
                    <label for="exclude_sale_items"><?php echo esc_html__( 'Check this box if gift cards should not apply to items on sale. Per-item gift card will only work if the item is not on sale. Per-cart gift cards will only work if there are no sale items in the cart.', 'woo-smart-pack' ); ?></label>
                </div>
            </div>
        </td>
        <td class="wide-second">
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Individual use only', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input check-box">
                    <input id="individual_use" name="individual_use" value="yes"<?php echo ($row[ 'individual_use' ] == 'yes') ? ' checked="checked"' : ''; ?> type="checkbox" />
                    <label for="individual_use"><?php echo esc_html__( 'Check this box if a gift card cannot be used in conjunction with other gift cards or coupons.', 'woo-smart-pack' ); ?></label>
                </div>
            </div>
            <div class="input-box">
                <div class="label">
                    <span><?php echo esc_html__( 'Usage limit per user', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input name="usage_limit_per_user" type="number" value="<?php echo esc_attr((!empty( $row[ 'usage_limit_per_user' ] )) ? $row[ 'usage_limit_per_user' ] : ''); ?>" min="0" step="1" placeholder="<?php echo esc_html__( 'Unlimited Usage', 'woo-smart-pack' ); ?>">
                </div>
            </div>

            <div class="input-box last">
                <div class="label">
                    <span><?php echo esc_html__( 'Usage limit per gift card', 'woo-smart-pack' ); ?></span>
                </div>
                <div class="input text-box">
                    <input name="usage_limit" type="number" value="<?php echo esc_attr((!empty( $row[ 'usage_limit' ] )) ? $row[ 'usage_limit' ] : ''); ?>" min="0" step="1" placeholder="<?php echo esc_html__( 'Unlimited Usage', 'woo-smart-pack' ); ?>">
                </div>
            </div>
        </td>
    </tr>
</table>
