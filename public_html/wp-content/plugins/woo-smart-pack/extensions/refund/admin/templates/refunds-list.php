<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include 'refunds-list-inc.php';

?><div class="wrap woo_wallet">
    <h1 class="wp-heading-inline"><?php echo esc_html__( 'Refund Requests', 'woo-smart-pack' ); ?></h1>

    <hr class="wp-header-end">
    <h2 class="screen-reader-text"><?php echo esc_html__( 'Filter account list', 'woo-smart-pack' ); ?></h2>
    <ul class="subsubsub">
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet-refunds' ) ); ?>" class="<?php echo (empty( $status ) || $status == -1) ? 'current' : ''; ?>"><?php echo esc_html__( 'All', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_RefundDB::GetRequestsCount() ); ?>)</span></a> |</li>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet-refunds&status=' . WOOZND_WALLET_REFUND_REQUEST_PENDING ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_REFUND_REQUEST_PENDING) ? 'current' : ''; ?>"><?php echo esc_html__( 'Pending', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_RefundDB::GetRequestsCount( '', WOOZND_WALLET_REFUND_REQUEST_PENDING ) ); ?>)</span></a>|</li>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet-refunds&status=' . WOOZND_WALLET_REFUND_REQUEST_APROVED ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_REFUND_REQUEST_APROVED) ? 'current' : ''; ?>"><?php echo esc_html__( 'Refunded', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_RefundDB::GetRequestsCount( '', WOOZND_WALLET_REFUND_REQUEST_APROVED ) ); ?>)</span></a></li>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet-refunds&status=' . WOOZND_WALLET_REFUND_REQUEST_REJECTED ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_REFUND_REQUEST_REJECTED) ? 'current' : ''; ?>"><?php echo esc_html__( 'Rejected', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_RefundDB::GetRequestsCount( '', WOOZND_WALLET_REFUND_REQUEST_REJECTED ) ); ?>)</span></a></li>
    </ul>
    <form id="posts-filter" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="wznd-wallet-refunds" />
        <?php
        foreach ( $url_options as $key => $value ) {
            if ( !($key == 'search') ) {
                ?>
                <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                <?php
            }
        }
        ?>
        <div class="tablenav top">
            <div class="alignleft actions"> 
                <input type="search" style="width:250px;" name="search" placeholder="<?php echo esc_html__( 'Order number', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( $search ); ?>">
                <input type="submit" class="button" value="<?php echo esc_html__( 'Search', 'woo-smart-pack' ); ?>">	
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
                <th style="width:130px;"><b><?php echo esc_html__( 'Order #', 'woo-smart-pack' ); ?></b></th>
                <th style="width:140px;"><b><?php echo esc_html__( 'Order Date', 'woo-smart-pack' ); ?></b></th>
                <th style="width:140px;"><b><?php echo esc_html__( 'Request Date', 'woo-smart-pack' ); ?></b></th>
                <th style="width:100px;"><b><?php echo esc_html__( 'Order Amount', 'woo-smart-pack' ); ?></b></th>
                <th style="width:100px;"><b><?php echo esc_html__( 'Req. Amount', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Reason', 'woo-smart-pack' ); ?></b></th>  
                <th style="width:195px;"><b><?php echo esc_html__( 'Actions', 'woo-smart-pack' ); ?></b></th>
            </tr>
        </thead>

        <tbody>
            <?php
            foreach ( $rows as $row ) {

                $order = wc_get_order( $row[ 'order_id' ] );

                $order_date = esc_html__( 'n/a', 'woo-smart-pack' );

                $order_total = 0;
                
                $order_total_refunded = 0;
                $order_number = 0;

                if ( $order ) {

                    $order_date = $order->get_date_created()->date( 'Y-m-d H:i:s' );
                    $order_total = $order->get_total();     
                    $order_total_refunded = $order->get_total_refunded();
                    $order_number = $order->get_order_number();
                    
                }
                
                $allowed_html = WooZnd_Init::get_instance()->get_allow_html();
                
                ?>
                <tr>
                    <td>#<?php echo esc_html( $row[ 'order_id' ] ); ?><a href="<?php echo esc_url( admin_url( 'post.php?post=' . absint( $row[ 'order_id' ] ) . '&action=edit' ) ); ?>" target="_blank"><?php echo esc_html__( ' - View Order', 'woo-smart-pack' ); ?></a></td>
                    <td><?php echo esc_html( WooZnd_Util::MySQLTimeStampToDataTime( $order_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td> 
                    <td><?php echo esc_html( WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'request_date' ], get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
                    <td><?php echo wp_kses( wc_price( $order_total ), $allowed_html ); ?></td>
                    <td><?php echo wp_kses( wc_price( $row[ 'request_amount' ] ), $allowed_html ); ?></td>
                    <td><?php echo esc_html( $row[ 'reason' ] ); ?></td>
                    <td align="right">
                        <?php
                        if ( $row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_PENDING ) {
                            ?>
                            <form action="" method="post" class="table-action-form">                                 
                                <?php wp_nonce_field( $wooznd_nonce_action, 'wznd_refund_nonce' ); ?>
                                <input type="hidden" name="action_name" value="cancel-refund" />
                                <input type="hidden" name="refund_id" value="<?php echo esc_attr( $row[ 'order_id' ] ); ?>" />                                
                                <button type="submit" class="button"><?php echo esc_html__( 'Reject', 'woo-smart-pack' ); ?></button>
                            </form>
                            <form action="" method="post" class="table-action-form">
                                <?php wp_nonce_field( $wooznd_nonce_action, 'wznd_refund_nonce' ); ?>
                                <input type="hidden" name="action_name" value="complete-refund" />
                                <input type="hidden" name="refund_id" value="<?php echo esc_attr( $row[ 'order_id' ] ); ?>" />
                                <input type="hidden" name="request_amount" value="<?php echo esc_attr( $row[ 'request_amount' ] - $order_total_refunded ); ?>" />
                                <button type="submit" class="button"><?php echo esc_html__( 'Refund', 'woo-smart-pack' ); ?></button>
                            </form>
                            <?php
                        } else {
                            ?>
                            <?php
                            if ( $row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_APROVED ) {
                                ?>
                                <a class="button disabled"><?php echo esc_html__( 'Refunded', 'woo-smart-pack' ); ?></a>
                                <?php
                            }
                            if ( $row[ 'status' ] == WOOZND_WALLET_REFUND_REQUEST_REJECTED ) {
                                ?>
                                <a class="button disabled"><?php echo esc_html__( 'Rejected', 'woo-smart-pack' ); ?></a>
                                <?php
                            }
                            ?>
                            <?php
                        }
                        ?>
                        <a class="button view-refund"><?php echo esc_html__( 'View', 'woo-smart-pack' ); ?></a>
                        <div class="popup-hidden view-refund-template" data-title="<?php echo esc_html__( 'Refund Details', 'woo-smart-pack' ); ?>">
                            <?php include 'popup/view-refund.php'; ?>
                        </div>
                    </td>
                </tr>    
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th><b><?php echo esc_html__( 'Order #', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Order Date', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Request Date', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Order Amount', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Req. Amount', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Reason', 'woo-smart-pack' ); ?></b></th>  
                <th><b><?php echo esc_html__( 'Actions', 'woo-smart-pack' ); ?></b></th>
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

