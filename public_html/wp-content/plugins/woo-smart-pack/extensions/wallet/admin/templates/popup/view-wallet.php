<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?><form action="" method="post">
    <?php wp_nonce_field( $wooznd_nonce_action, 'wznd_wallet_nonce' ); ?>
    <input type="hidden" name="action_name" value="update">
    <input type="hidden" name="user_id" value="<?php echo esc_attr( $row[ 'id' ] ); ?>">
    <div class="text-block">
        <p><b><?php echo esc_html__( 'Open On:', 'woo-smart-pack' ); ?></b> <?php echo isset( $row[ 'open_date' ] ) ? esc_html( WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'open_date' ], get_option( 'date_format' ) ) ) : esc_html__( 'N/A', 'woo-smart-pack' ); ?>, <b><?php echo esc_html__( 'Last Access:', 'woo-smart-pack' ); ?></b> <?php echo isset( $row[ 'last_access' ] ) ? esc_html( WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'last_access' ], get_option( 'date_format' ) ) ) : esc_html__( 'N/A', 'woo-smart-pack' ); ?>, <b><?php echo esc_html__( 'Last Transaction:', 'woo-smart-pack' ); ?></b> <?php echo isset( $row[ 'last_transaction' ] ) ? esc_html( WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'last_transaction' ], get_option( 'date_format' ) ) ) : esc_html__( 'N/A', 'woo-smart-pack' ); ?>.</p>
    </div>
    <table class="woo-wide-form">
        <tr>
            <td>
                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'First Name', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input name="first_name" type="text" placeholder="<?php echo esc_attr__( 'First Name', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( $row[ 'first_name' ] ); ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Last Name', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input name="last_name" type="text" placeholder="<?php echo esc_attr__( 'Last Name', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( $row[ 'last_name' ] ); ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Email', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_attr( $row[ 'email' ] ); ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Account Number', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_attr( $row[ 'account_number' ] ); ?>">
                    </div>
                </div>

                <div class="input-box last">
                    <div class="label">
                        <span><?php echo esc_html__( 'Locked/Unlocked', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input select-box">
                        <select name="locked">                            
                            <option value="<?php echo esc_attr( WOOZND_WALLET_ACCOUNT_STATUS_UNLOCKED ); ?>"><?php echo esc_html__( 'Unlocked', 'woo-smart-pack' ); ?></option>
                            <option<?php echo ($row[ 'locked' ] == WOOZND_WALLET_ACCOUNT_STATUS_LOCKED) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( WOOZND_WALLET_ACCOUNT_STATUS_LOCKED ); ?>"><?php echo esc_html__( 'Locked', 'woo-smart-pack' ); ?></option>
                        </select>
                    </div>
                </div>
            </td>
            <td class="wide-second">
                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Ledger Balance', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input name="ledger_balance" type="number" min="0" step="0.05" placeholder="0.00" value="<?php echo esc_attr( $row[ 'ledger_balance' ] ); ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Current Balance', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input name="current_balance" type="number" min="0" step="0.05" placeholder="0.00" value="<?php echo esc_attr( $row[ 'current_balance' ] ); ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Total Spent', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input name="total_spent" type="number" min="0" step="0.05" placeholder="0.00" value="<?php echo esc_attr( $row[ 'total_spent' ] ); ?>">
                    </div>
                </div>

                <div class="input-box last">
                    <div class="label" style="vertical-align: top">
                        <span><?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-area">
                        <textarea name="remark" placeholder="<?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?>" style="height: 123px;"><?php echo esc_textarea( $row[ 'remark' ] ); ?></textarea>
                    </div>
                </div>
            </td>
        </tr>
    </table>



    <div class="actions-box woo-wide-form">
        <input class="button button-primary" value="<?php echo esc_html__( 'Update Wallet', 'woo-smart-pack' ); ?>" type="submit">
        <input class="button button-secondary popup-btn-close" value="<?php echo esc_html__( 'Cancel', 'woo-smart-pack' ); ?>" type="button">
        <div style="float: right;">
            <input class="button delete-theme" name="delete" value="<?php echo esc_html__( 'Delete', 'woo-smart-pack' ); ?>" type="submit">
        </div>
    </div>

</form>
