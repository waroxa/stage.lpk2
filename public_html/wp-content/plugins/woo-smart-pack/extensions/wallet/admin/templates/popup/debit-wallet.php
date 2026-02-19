<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?><form action="" method="post">
    <?php wp_nonce_field( $wooznd_nonce_action, 'wznd_wallet_nonce' ); ?>
    <input type="hidden" name="action_name" value="debit" />
    <input type="hidden" name="user_id" value="<?php echo esc_attr( $row[ 'id' ] ); ?>">
    <table class="woo-wide-form">
        <tr>
            <td>
                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Account Number', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input disabled="disabled" type="text" value="<?php echo esc_attr( $row[ 'account_number' ] ); ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Full Name', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input disabled="disabled" type="text" value="<?php echo esc_attr( $row[ 'first_name' ] . ' ' . $row[ 'last_name' ] ); ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Amount', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input name="amount" type="number" min="0" step="0.05" placeholder="0.00">
                    </div>
                </div>

                <div class="input-box last">
                    <div class="label">
                        <span><?php echo esc_html__( 'Status', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input select-box">
                        <select name="status">
                            <option value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_STATUS_PENDING ); ?>"><?php echo esc_html__( 'Pending', 'woo-smart-pack' ); ?></option>
                            <option value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD ); ?>"><?php echo esc_html__( 'On Hold', 'woo-smart-pack' ); ?></option>
                            <option value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING ); ?>"><?php echo esc_html__( 'Processing', 'woo-smart-pack' ); ?></option>
                            <option value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED ); ?>"><?php echo esc_html__( 'Completed', 'woo-smart-pack' ); ?></option>
                        </select>
                    </div>
                </div>
            </td>
            <td class="wide-second">
                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Type', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input select-box">
                        <select name="transtype">
                            <option value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_DEBIT ); ?>"><?php echo esc_html__( 'Debit', 'woo-smart-pack' ); ?></option>                
                            <option value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_WITHDRAWAL ); ?>"><?php echo esc_html__( 'Withdrawal', 'woo-smart-pack' ); ?></option>
                            <option value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_PAYMENT ); ?>"><?php echo esc_html__( 'Payment', 'woo-smart-pack' ); ?></option>
                            <option value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_BILL ); ?>"><?php echo esc_html__( 'Bill', 'woo-smart-pack' ); ?></option>                
                            <option value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_TRANSFER ); ?>"><?php echo esc_html__( 'Transfer', 'woo-smart-pack' ); ?></option>                
                        </select>
                    </div>
                </div>

                <div class="input-box last">
                    <div class="label" style="vertical-align: top">
                        <span><?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-area">
                        <textarea name="remark" placeholder="<?php echo esc_attr__( 'Remark', 'woo-smart-pack' ); ?>" style="height:200px;"></textarea>
                    </div>
                </div>

            </td>
        </tr>
    </table>


    <div class="actions-box woo-wide-form">
        <input class="button button-primary" value="<?php echo esc_html__( 'Add Funds', 'woo-smart-pack' ); ?>" type="submit">
        <input class="button button-secondary popup-btn-close" value="<?php echo esc_attr__( 'Cancel', 'woo-smart-pack' ); ?>" type="button">
    </div>

</form>
