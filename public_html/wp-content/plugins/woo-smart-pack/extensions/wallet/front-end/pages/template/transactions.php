<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

require 'transactions_inc.php';

$allowed_html = WooZnd_Init::get_instance()->get_allow_html();

?><h3><?php echo esc_html( $transaction_title ); ?></h3>
<table class="woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
    <thead>
        <tr>
            <th><span class="nobr"><?php echo esc_html__( 'Receipt #', 'woo-smart-pack' ); ?></span></th>
            <th><span class="nobr"><?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?></span></th>
            <th><span class="nobr"><?php echo esc_html__( 'Status', 'woo-smart-pack' ); ?></span></th>
            <th><span class="nobr"><?php echo esc_html__( 'Credit', 'woo-smart-pack' ); ?></span></th>
            <th><span class="nobr"><?php echo esc_html__( 'Debit', 'woo-smart-pack' ); ?></span></th>
            <th style="text-align: right;"><?php echo esc_html__( 'Date', 'woo-smart-pack' ); ?></th>
        </tr>
    </thead>

    <tbody>
        <?php
        foreach ( $rows as $row ) {
            
            $trans_remark = isset( $row[ 'remark' ] ) ? $row[ 'remark' ] : '';

            $order = false;

            if ( isset( $row[ 'order_id' ] ) ) {
                
                $order = wc_get_order( $row[ 'order_id' ] );
            }
            
            if ( $order ) {
                
                $trans_remark = preg_replace( '/{{order_id}}/', $row[ 'order_id' ], $trans_remark );
                $trans_remark = preg_replace( '/{{order_url}}/', $order->get_view_order_url(), $trans_remark );
                $trans_remark .= ' <a href="' . $order->get_view_order_url() . '" target="_blank">' . esc_html__( 'View Order', 'woo-smart-pack' ) . '</a>';
            }
            ?>
            <tr class="order">
                <td>                
                    <?php echo esc_html( $row[ 'receipt' ] ? $row[ 'receipt' ] : ''  ); ?>
                </td>
                <td>
                    <?php echo wp_kses_post( $trans_remark ); ?>
                </td>
                <td>
                    <?php echo esc_html( WooZnd_Wallet_Util::TransactionStatusString( $row[ 'status' ], false ) ); ?>
                </td>
                <td>
                    <?php echo wp_kses( ($row[ 'credit' ] > 0) ? wc_price( $row[ 'credit' ] ) : '-', $allowed_html ); ?>
                </td>
                <td>
                    <?php echo wp_kses( ($row[ 'debit' ] > 0) ? wc_price( $row[ 'debit' ] ) : '-', $allowed_html ); ?>
                </td>
                <td style="text-align: right;">
                    <time datetime="<?php echo esc_attr( WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'issue_date' ], 'Y-m-d' ) ); ?>" title="<?php echo esc_attr( WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'issue_date' ], 'U' ) ); ?>"><?php echo esc_html( WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'issue_date' ], get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></time>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <th><span class="nobr"><?php echo esc_html__( 'Receipt #', 'woo-smart-pack' ); ?></span></th>
            <th><span class="nobr"><?php echo esc_html__( 'Remark', 'woo-smart-pack' ); ?></span></th>
            <th><span class="nobr"><?php echo esc_html__( 'Status', 'woo-smart-pack' ); ?></span></th>
            <th><span class="nobr"><?php echo esc_html__( 'Credit', 'woo-smart-pack' ); ?></span></th>
            <th><span class="nobr"><?php echo esc_html__( 'Debit', 'woo-smart-pack' ); ?></span></th>
            <th style="text-align: right;"><?php echo esc_html__( 'Date', 'woo-smart-pack' ); ?></th>
        </tr>
    </tfoot>
</table>

<div class="woocommerce-Pagination">
    <?php $paging->render_woo_links( $url_format, [], $default_url, '+', esc_html__( 'Previous', 'woo-smart-pack' ), esc_html__( 'Next', 'woo-smart-pack' ) ); ?>
</div>

