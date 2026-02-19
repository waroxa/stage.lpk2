<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include 'transactions-list_inc.php';

?><div class="wrap woo_wallet">
    <h1 class="wp-heading-inline"><?php echo esc_html__( 'All Transactions', 'woo-smart-pack' ); ?></h1>

    <hr class="wp-header-end">
    <h2 class="screen-reader-text"><?php echo esc_html__( 'Filter account list', 'woo-smart-pack' ); ?></h2>
    <ul class="subsubsub">
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet-trans' ) ); ?>" class="<?php echo!empty( $status ) ? 'current' : ''; ?>"><?php echo esc_html__( 'All', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_WalletTransactionDB::GetTransactionsCount() ); ?>)</span></a> |</li>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet-trans&status=' . WOOZND_WALLET_TRANSANCTION_STATUS_PENDING ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_TRANSANCTION_STATUS_PENDING) ? 'current' : ''; ?>"><?php echo esc_html__( 'Pending', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_WalletTransactionDB::GetTransactionsCount( WOOZND_WALLET_TRANSANCTION_STATUS_PENDING ) ); ?>)</span></a> |</li>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet-trans&status=' . WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING) ? 'current' : ''; ?>"><?php echo esc_html__( 'Processing', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_WalletTransactionDB::GetTransactionsCount( WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING ) ); ?>)</span></a></li>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet-trans&status=' . WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD) ? 'current' : ''; ?>"><?php echo esc_html__( 'On-Hold', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_WalletTransactionDB::GetTransactionsCount( WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD ) ); ?>)</span></a></li>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet-trans&status=' . WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED) ? 'current' : ''; ?>"><?php echo esc_html__( 'Cancelled', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_WalletTransactionDB::GetTransactionsCount( WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) ); ?>)</span></a></li>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet-trans&status=' . WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED) ? 'current' : ''; ?>"><?php echo esc_html__( 'Completed', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_WalletTransactionDB::GetTransactionsCount( WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED ) ); ?>)</span></a></li>
    </ul>

    <form id="posts-filter" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="wznd-wallet-trans" />
        <?php
        foreach ( $url_options as $key => $value ) {
            if ( !($key == 'search') ) {
                ?>
                <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                <?php
            }
        }
        ?>
        <p class="search-box">
            <input type="search" style="width:150px;" name="search" placeholder="<?php echo esc_html__( 'email or account no', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( $search ); ?>">
            <input type="submit" class="button" value="<?php echo esc_html__( 'Search', 'woo-smart-pack' ); ?>"></p>
    </form>
    <form id="posts-filter" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="wznd-wallet-trans" />
        <?php
        foreach ( $url_options as $key => $value ) {
            if ( !($key == 'from' || $key == 'to') ) {
                ?>
                <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                <?php
            }
        }
        ?>
        <div class="tablenav top">
            <div class="alignleft actions"> 
                <input type="text" class="datepicker" style="width:150px;" name="from" placeholder="<?php echo esc_html__( 'From', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( $from ); ?>">
                <input type="text" class="datepicker" style="width:150px;" name="to" placeholder="<?php echo esc_html__( 'To', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( $to ); ?>">
                <input type="submit" class="button" value="<?php echo esc_html__( 'Find', 'woo-smart-pack' ); ?>">		
            </div>

            <div class="pages">
                <span class="displaying-num"><?php $paging->render_result_count( esc_html__( '{{from}} to {{to}} of {{total}} items', 'woo-smart-pack' ) ); ?></span>
                <?php $paging->render_links( $url_format, 5, $url_options, $default_url, '+' ); ?>
            </div>
            <br class="clear">
        </div>
    </form>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><b><?php echo esc_html__( 'Receipt #', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Account #', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?></b></th>
                <th style="width:130px;"><b><?php echo esc_html__( 'Issue Date', 'woo-smart-pack' ); ?></b></th>
                <th style="width:90px;"><b><?php echo esc_html__( 'Credit', 'woo-smart-pack' ); ?></b></th>
                <th style="width:90px;"><b><?php echo esc_html__( 'Debit', 'woo-smart-pack' ); ?></b></th>                 
                <th style="width:211px;"><b><?php echo esc_html__( 'Actions', 'woo-smart-pack' ); ?></b></th>
            </tr>
        </thead>

        <tbody>
            <?php
            
            $allow_html = WooZnd_Init::get_instance()->get_allow_html();
            
            foreach ( $rows as $row ) {
                $trans_remark = isset( $row[ 'remark' ] ) ? $row[ 'remark' ] : '';
                if ( isset( $row[ 'order_id' ] ) ) {
                    $trans_remark = preg_replace( '/{{order_id}}/', $row[ 'order_id' ], $trans_remark );
                    $trans_remark = preg_replace( '/{{order_url}}/', admin_url( 'post.php?post=' . absint( $row[ 'order_id' ] ) . '&action=edit' ), $trans_remark );
                    $trans_remark .= ' <a href="' . admin_url( 'post.php?post=' . absint( $row[ 'order_id' ] ) . '&action=edit' ) . '" target="_blank">' . esc_html__( 'View Order', 'woo-smart-pack' ) . '</a>';
                }
                ?>
                <tr>
                    <td><?php echo esc_html( $row[ 'receipt' ] ); ?></td>
                    <td><?php echo esc_html( $row[ 'account_number' ] ); ?></td>
                    <td><?php echo wp_kses( $trans_remark, $allow_html ); ?></td> 
                    <td><?php echo esc_html( WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'issue_date' ], get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
                    <td><?php echo wp_kses( ($row[ 'credit' ] > 0) ? wc_price( $row[ 'credit' ] ) : '-', $allow_html ); ?></td>
                    <td><?php echo wp_kses( ($row[ 'debit' ] > 0) ? wc_price( $row[ 'debit' ] ) : '-', $allow_html ); ?></td>
                    <td align="right">
                        <?php
                        if ( $row[ 'status' ] != WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED && $row[ 'status' ] != WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {
                            ?>
                            <form action="" method="post" class="table-action-form">
                                <?php wp_nonce_field( $wooznd_nonce_action, 'wznd_wallet_nonce' ); ?>
                                <input type="hidden" name="action_name" value="cancel-transaction" />
                                <input type="hidden" name="trans_id" value="<?php echo esc_attr( $row[ 'id' ] ); ?>" />
                                <input type="hidden" name="user_id" value="<?php echo esc_attr( $row[ 'account_id' ] ); ?>" />
                                <button type="submit" class="button"><?php echo esc_html__( 'Cancel', 'woo-smart-pack' ); ?></button>
                            </form>
                            <form action="" method="post" class="table-action-form">
                                <?php wp_nonce_field( $wooznd_nonce_action, 'wznd_wallet_nonce' ); ?>
                                <input type="hidden" name="action_name" value="complete-transaction" />
                                <input type="hidden" name="trans_id" value="<?php echo esc_attr( $row[ 'id' ] ); ?>" />
                                <input type="hidden" name="user_id" value="<?php echo esc_attr( $row[ 'account_id' ] ); ?>" />
                                <button type="submit" class="button"><?php echo esc_html__( 'Complete', 'woo-smart-pack' ); ?></button>
                            </form>
                            <?php
                        } else {
                            ?>
                            <?php
                            if ( $row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED ) {
                                ?>
                                <a class="button disabled"><?php echo esc_html__( 'Completed', 'woo-smart-pack' ); ?></a>
                                <?php
                            }
                            if ( $row[ 'status' ] == WOOZND_WALLET_TRANSANCTION_STATUS_CANCELLED ) {
                                ?>
                                <a class="button disabled"><?php echo esc_html__( 'Cancelled', 'woo-smart-pack' ); ?></a>
                                <?php
                            }
                            ?>
                            <?php
                        }
                        ?>   
                        <a class="button view-transaction"><?php echo esc_html__( 'View', 'woo-smart-pack' ); ?></a>
                        <div class="popup-hidden view-transaction-template" data-title="<?php echo esc_html__( 'Transaction Details', 'woo-smart-pack' ); ?>">
                            <?php include 'popup/view-transaction.php'; ?>
                        </div>
                    </td>
                </tr>    
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th><b><?php echo esc_html__( 'Receipt #', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Account #', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?></b></th>
                <th style="width:130px;"><b><?php echo esc_html__( 'Issue Date', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Credit', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Debit', 'woo-smart-pack' ); ?></b></th>
                <th style="width:211px;"><b><?php echo esc_html__( 'Actions', 'woo-smart-pack' ); ?></b></th>
            </tr>
        </tfoot>
    </table>
    <div class="tablenav bottom">


        <div class="pages">
            <span class="displaying-num"><?php $paging->render_result_count( esc_html__( '{{from}} to {{to}} of {{total}} items', 'woo-smart-pack' ) ); ?></span>
            <?php $paging->render_links( $url_format, 5, $url_options, $default_url, '+' ); ?>
        </div>
        <br class="clear">

    </div>
</div>

