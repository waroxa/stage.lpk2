<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?><form action="" method="post">
    <?php wp_nonce_field( $wooznd_nonce_action, 'wznd_refund_nonce' ); ?>
    <input type="hidden" name="action_name" value="update-refund">
    <input type="hidden" name="refund_id" value="<?php echo esc_attr( $row[ 'order_id' ] ); ?>" />

    <div class="text-block">
        <p><b><?php echo esc_html__( 'Requested On:', 'woo-smart-pack' ); ?></b> <?php echo esc_html( isset( $row[ 'request_date' ] ) ? WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'request_date' ], get_option( 'date_format' ) ) : esc_html__( 'N/A', 'woo-smart-pack' ) ); ?>.</p>
    </div>
    <table class="woo-wide-form">
        <tr>
            <td>
                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Order Number', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_attr( '#' . $order_number ); ?>">
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

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Order Amount', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_attr( $order_total ); ?>">
                    </div>
                </div>
                <div class="input-box last">
                    <div class="label">
                        <span><?php echo esc_html__( 'Request Amount', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_attr( $row[ 'request_amount' ] ); ?>">
                    </div>
                </div>


            </td>
            <td class="wide-second">
                <?php
                if ( $row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_APROVED || $row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_REJECTED ) {
                    ?>
                    <div class="input-box">
                        <div class="label">
                            <span><?php echo esc_html__( 'Amount Refunded', 'woo-smart-pack' ); ?></span>
                        </div>
                        <div class="input text-box">
                            <input type="text" disabled="disabled" value="<?php echo esc_attr( $order_total_refunded ); ?>">
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="input-box">
                        <div class="label">
                            <span><?php echo esc_html__( 'Refundable Amount', 'woo-smart-pack' ); ?></span>
                        </div>
                        <div class="input text-box">
                            <input type="text" name="request_amount" value="<?php echo esc_attr( $row[ 'request_amount' ] - $order_total_refunded ); ?>">
                        </div>
                    </div>
                    <?php
                }
                ?>
                <?php
                if ( $row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_APROVED || $row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_REJECTED ) {
                    ?>
                    <div class="input-box">
                        <div class="label">
                            <span><?php echo esc_html__( 'Status', 'woo-smart-pack' ); ?></span>
                        </div>
                        <div class="input text-box">
                            <input type="text" disabled="disabled" value="<?php echo esc_attr( WooZnd_Refund::RefundStatusString( $row[ 'status' ], false ) ); ?>">
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="input-box">
                        <div class="label">
                            <span><?php echo esc_html__( 'Status', 'woo-smart-pack' ); ?></span>
                        </div>                    
                        <div class="input select-box">
                            <select name="status">                            
                                <option value="<?php echo esc_attr( WOOZND_WALLET_REFUND_REQUEST_PENDING ); ?>"><?php echo esc_html__( 'Pending', 'woo-smart-pack' ); ?></option>
                                <option<?php echo ($row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_APROVED) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( WOOZND_WALLET_REFUND_REQUEST_APROVED ); ?>"><?php echo esc_html__( 'Aproved', 'woo-smart-pack' ); ?></option>
                                <option<?php echo ($row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_REJECTED) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( WOOZND_WALLET_REFUND_REQUEST_REJECTED ); ?>"><?php echo esc_html__( 'Rejected', 'woo-smart-pack' ); ?></option>                                
                            </select>
                        </div>
                    </div>    
                    <?php
                }
                ?>


                <div class="input-box last">
                    <div class="label" style="vertical-align: top">
                        <span><?php echo esc_html__( 'Reason', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-area">
                        <textarea name="reason"<?php echo ($row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_APROVED || $row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_REJECTED) ? ' disabled="disabled"' : ''; ?> placeholder="<?php echo esc_html__( 'Reason', 'woo-smart-pack' ); ?>" style="height: 117px;"><?php echo esc_textarea( $row[ 'reason' ] ); ?></textarea>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div class="actions-box woo-wide-form">
        <?php
        if ( $row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_APROVED || $row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_REJECTED ) {
            ?>
            <input class="button button-secondary popup-btn-close" value="<?php echo esc_attr__( 'Close', 'woo-smart-pack' ); ?>" type="button">                 
            <?php
        } else {
            ?>
            <input class="button button-primary" value="<?php echo esc_attr__( 'Update', 'woo-smart-pack' ); ?>" type="submit">
            <input class="button button-secondary popup-btn-close" value="<?php echo esc_attr__( 'Cancel', 'woo-smart-pack' ); ?>" type="button">                 
            <?php
        }
        ?>    
        <div style="float: right;">
            <input class="button delete-theme" name="delete" value="<?php echo esc_html__( 'Delete', 'woo-smart-pack' ); ?>" type="submit">
        </div>
    </div>

</form>
