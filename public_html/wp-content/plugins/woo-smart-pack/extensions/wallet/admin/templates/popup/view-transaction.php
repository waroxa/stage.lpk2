<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?><form action="" method="post">
    <?php wp_nonce_field( $wooznd_nonce_action, 'wznd_wallet_nonce' ); ?>
    <input type="hidden" name="action_name" value="update-transaction">
    <input type="hidden" name="trans_id" value="<?php echo esc_attr( $row[ 'id' ] ); ?>" />
    <input type="hidden" name="user_id" value="<?php echo esc_attr( $row[ 'account_id' ] ); ?>" />
    <div class="text-block">
        <p><b><?php echo esc_html__( 'Issued On:', 'woo-smart-pack' ); ?></b> <?php echo esc_html( isset( $row[ 'issue_date' ] ) ? WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'issue_date' ], get_option( 'date_format' ) ) : esc_html__( 'N/A', 'woo-smart-pack' ) ); ?>, <b><?php echo esc_html__( 'Completed On:', 'woo-smart-pack' ); ?></b> <?php echo esc_html( isset( $row[ 'complete_date' ] ) ? WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'complete_date' ], get_option( 'date_format' ) ) : esc_html__( 'N/A', 'woo-smart-pack' ) ); ?>.</p>
    </div>
    <table class="woo-wide-form">
        <tr>
            <td>
                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Receipt Number', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_attr( $row[ 'receipt' ] ); ?>">
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
                        <span><?php echo esc_html__( 'Amount', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_attr( ($row[ 'credit' ] > 0) ? $row[ 'credit' ] : $row[ 'debit' ]  ); ?>">
                    </div>
                </div>

                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Transaction Type', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_attr( WooZnd_Wallet_Util::TransactionTypeString( $row[ 'transaction_type' ], false ) ); ?>">
                    </div>
                </div>

                <div class="input-box last">
                    <div class="label">
                        <span><?php echo esc_html__( 'Issued By', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_attr( $row[ 'issued_by' ] ); ?>">
                    </div>
                </div>


            </td>
            <td class="wide-second">
                <div class="input-box">
                    <div class="label">
                        <span><?php echo esc_html__( 'Completed By', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-box">
                        <input type="text" disabled="disabled" value="<?php echo esc_attr( isset( $row[ 'completed_by' ] ) ? $row[ 'completed_by' ] : 'N/A'  ); ?>">
                    </div>
                </div>
                <?php
                if ( $row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED || $row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {
                    ?>
                    <div class="input-box">
                        <div class="label">
                            <span><?php echo esc_html__( 'Status', 'woo-smart-pack' ); ?></span>
                        </div>
                        <div class="input text-box">
                            <input type="text" disabled="disabled" value="<?php echo esc_attr( WooZnd_Wallet_Util::TransactionStatusString( $row[ 'status' ] ), false ); ?>">
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
                                <option value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_STATUS_PENDING ); ?>"><?php echo esc_html__( 'Pending', 'woo-smart-pack' ); ?></option>
                                <option<?php echo ($row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD ); ?>"><?php echo esc_html__( 'On-Hold', 'woo-smart-pack' ); ?></option>
                                <option<?php echo ($row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING ); ?>"><?php echo esc_html__( 'Processing', 'woo-smart-pack' ); ?></option>
                                <option<?php echo ($row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED ); ?>"><?php echo esc_html__( 'Completed', 'woo-smart-pack' ); ?></option>
                                <option<?php echo ($row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED) ? ' selected="selected"' : ''; ?> value="<?php echo esc_attr( WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ); ?>"><?php echo esc_html__( 'Cancelled', 'woo-smart-pack' ); ?></option>
                            </select>
                        </div>
                    </div>
                    <?php
                }
                ?>

                <div class="input-box last">
                    <div class="label" style="vertical-align: top">
                        <span><?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?></span>
                    </div>
                    <div class="input text-area">
                        <?php
                        if ( $row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED || $row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {
                            ?>
                            <textarea disabled="disabled" style="height: 211px;"><?php echo esc_textarea( $row[ 'remark' ] ); ?></textarea>
                            <?php
                        } else {
                            ?>
                            <textarea name="remark" placeholder="<?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?>" style="height: 211px;"><?php echo esc_textarea( $row[ 'remark' ] ); ?></textarea>
                            <?php
                        }
                        ?>                        
                    </div>
                </div>
            </td>
        </tr>
    </table>



    <div class="actions-box woo-wide-form">
        <?php
        if ( $row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED || $row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {
            ?>
            <input class="button button-secondary popup-btn-close" value="<?php echo esc_html__( 'Close', 'woo-smart-pack' ); ?>" type="button">
            <?php
        } else {
            ?>
            <input class="button button-primary" value="<?php echo esc_html__( 'Update Transaction', 'woo-smart-pack' ); ?>" type="submit">
            <input class="button button-secondary popup-btn-close" value="<?php echo esc_html__( 'Cancel', 'woo-smart-pack' ); ?>" type="button">
            <?php
        }
        ?>   
        <div style="float: right;">
            <input class="button delete-theme" name="delete" value="<?php echo esc_html__( 'Delete', 'woo-smart-pack' ); ?>" type="submit">
        </div>
    </div>

</form>
