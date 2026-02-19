<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?><form action="" method="post">
    <?php wp_nonce_field( $wooznd_nonce_action, 'wznd_wallet_nonce' ); ?>
    <input type="hidden" name="action_name" value="addnew">  
    <div class="text-block">
        <p><b><?php echo esc_html__( 'Note:', 'woo-smart-pack' ); ?></b> <?php echo esc_html__( 'You can create wallet for registered users only.', 'woo-smart-pack' ); ?></p>
    </div>
    <table class="woo-wide-form">
        <tr>
            <td>
                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'User ID', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input name="username" type="text" placeholder="<?php echo esc_html__( 'User name or email address', 'woo-smart-pack' ); ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Account Number', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_html__( 'Auto generate', 'woo-smart-pack' ); ?>">
                    </div>
                </div>
                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Ledger Balance', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input name="ledger_balance" type="number" min="0" step="0.05" placeholder="0.00">
                    </div>
                </div>

                <div class="input-box last">
                    <div class="label">
                        <span><?php echo esc_html__( 'Current Balance', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input name="current_balance" type="number" min="0" step="0.05" placeholder="0.00">
                    </div>
                </div>

            </td>
            <td class="wide-second">
                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Total Spent', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input name="total_spent" type="number" min="0" step="0.05" placeholder="0.00">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Locked/Unlocked', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input select-box">
                        <select name="locked">                            
                            <option value="<?php echo esc_attr( WOOZND_WALLET_ACCOUNT_STATUS_UNLOCKED ); ?>"><?php echo esc_html__( 'Unlocked', 'woo-smart-pack' ); ?></option>
                            <option value="<?php echo esc_attr( WOOZND_WALLET_ACCOUNT_STATUS_LOCKED ); ?>"><?php echo esc_html__( 'Locked', 'woo-smart-pack' ); ?></option>
                        </select>
                    </div>
                </div>

                <div class="input-box last">
                    <div class="label" style="vertical-align: top">
                        <span><?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-area">
                        <textarea name="remark" placeholder="<?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?>" style="height: 123px;"></textarea>
                    </div>
                </div>
            </td>
        </tr>
    </table>



    <div class="actions-box woo-wide-form">
        <input class="button button-primary" value="<?php echo esc_html__( 'Create Wallet', 'woo-smart-pack' ); ?>" type="submit">
        <input class="button button-secondary popup-btn-close" value="<?php echo esc_html__( 'Cancel', 'woo-smart-pack' ); ?>" type="button">
    </div>

</form>
