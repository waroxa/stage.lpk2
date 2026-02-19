<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include 'giftcards-list_inc.php';

?><div class="wrap woo_wallet">
    <h1 class="wp-heading-inline"><?php echo esc_html__( 'All Gift Cards', 'woo-smart-pack' ); ?></h1>
    <a href="#" class="page-title-action new-giftcard"><?php echo esc_html__( 'Add Gift Card', 'woo-smart-pack' ); ?></a>
    <div class="popup-hidden new-giftcard-template" data-title="<?php echo esc_html__( 'Add New Gift Card', 'woo-smart-pack' ); ?>">
        <?php include 'popup/new-giftcard.php'; ?>
    </div>
    <hr class="wp-header-end">
    <h2 class="screen-reader-text"><?php echo esc_html__( 'Filter gift card list', 'woo-smart-pack' ); ?></h2>
    <ul class="subsubsub">
        <li><a href="<?php echo esc_attr( admin_url( 'admin.php?page=wznd-manage-giftcard' ) ); ?>" class="<?php echo ($status == -1 && empty( $_GET[ 'exp' ] )) ? 'current' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>"><?php echo esc_html__( 'All', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_GiftCardDB::GetGiftCardsCount( '', -1 ) ); ?>)</span></a> |</li>
        <li><a href="<?php echo esc_attr( admin_url( 'admin.php?page=wznd-manage-giftcard&status=' . WOOZND_GIFTCARD_STATUS_PENDING ) ); ?>" class="<?php echo esc_attr(($status == WOOZND_GIFTCARD_STATUS_PENDING) ? 'current' : ''); ?>"><?php echo esc_html__( 'Pending', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_GiftCardDB::GetGiftCardsCount( '', WOOZND_GIFTCARD_STATUS_PENDING ) ); ?>)</span></a> |</li>
        <li><a href="<?php echo esc_attr( admin_url( 'admin.php?page=wznd-manage-giftcard&status=' . WOOZND_GIFTCARD_STATUS_SENT ) ); ?>" class="<?php echo esc_attr(($status == WOOZND_GIFTCARD_STATUS_SENT) ? 'current' : ''); ?>"><?php echo esc_html__( 'Sent', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_GiftCardDB::GetGiftCardsCount( '', WOOZND_GIFTCARD_STATUS_SENT ) ); ?>)</span></a></li>
        <li><a href="<?php echo esc_attr( admin_url( 'admin.php?page=wznd-manage-giftcard&status=' . WOOZND_GIFTCARD_STATUS_USED ) ); ?>" class="<?php echo esc_attr(($status == WOOZND_GIFTCARD_STATUS_USED) ? 'current' : ''); ?>"><?php echo esc_html__( 'Used', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_GiftCardDB::GetGiftCardsCount( '', WOOZND_GIFTCARD_STATUS_USED ) ); ?>)</span></a></li>
        <li><a href="<?php echo esc_attr( admin_url( 'admin.php?page=wznd-manage-giftcard&status=' . WOOZND_GIFTCARD_STATUS_EXHAUSTED ) ); ?>" class="<?php echo esc_attr(($status == WOOZND_GIFTCARD_STATUS_EXHAUSTED) ? 'current' : ''); ?>"><?php echo esc_html__( 'Exhausted', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_GiftCardDB::GetGiftCardsCount( '', WOOZND_GIFTCARD_STATUS_EXHAUSTED ) ); ?>)</span></a></li>
        <li><a href="<?php echo esc_attr( admin_url( 'admin.php?page=wznd-manage-giftcard&status=' . WOOZND_GIFTCARD_STATUS_REFUNDED ) ); ?>" class="<?php echo esc_attr(($status == WOOZND_GIFTCARD_STATUS_REFUNDED) ? 'current' : ''); ?>"><?php echo esc_html__( 'Refunded', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html(WooZnd_GiftCardDB::GetGiftCardsCount( '', WOOZND_GIFTCARD_STATUS_REFUNDED )); ?>)</span></a></li>
        <li>|</li>
        <li><a href="<?php echo esc_attr( admin_url( 'admin.php?page=wznd-manage-giftcard&exp=1' ) ); ?>" class="<?php echo ($status == -1 && isset($_GET[ 'exp' ]) && $_GET[ 'exp' ] == 1) ? 'current' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>"><?php echo esc_html__( 'Expired', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_GiftCardDB::GetExpiredGiftCardsCount( '', -1 ) ); ?>)</span></a></li>
    </ul>
    <form id="posts-filter" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="wznd-manage-giftcard" />
        <?php
        foreach ( $url_options as $key => $value ) {
            if ( !($key == 'search' ) ) {
                ?>
                <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                <?php
            }
        }
        ?>
        <p class="search-box">
            <input type="search" style="width:200px;" name="search" placeholder="<?php echo esc_html__( 'gift card coupon', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( $search ); ?>">
            <input type="submit" class="button" value="<?php echo esc_html__( 'Search', 'woo-smart-pack' ); ?>"></p>
    </form>
    <form id="posts-filter" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="wznd-manage-giftcard" />
        <?php
        foreach ( $url_options as $key => $value ) {
            if ( !($key == 'orderby' || $key == 'order') ) {
                ?>
                <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                <?php
            }
        }
        ?>
        <div class="tablenav top">
            <div class="alignleft actions"> 
                <select name="orderby" >
                    <?php $ord = $orderby; ?>
                    <option value="event"><?php echo esc_html__( 'Sort by event date', 'woo-smart-pack' ); ?></option>                    
                    <option <?php echo ($ord == 'expiry') ? 'selected="selected"' : ''; ?> value="expiry"><?php echo esc_html__( 'Sort by expiry date', 'woo-smart-pack' ); ?></option>
                    <option <?php echo ($ord == 'coupon') ? 'selected="selected"' : ''; ?> value="coupon"><?php echo esc_html__( 'Sort by coupon code', 'woo-smart-pack' ); ?></option>
                    <option <?php echo ($ord == 'amount') ? 'selected="selected"' : ''; ?> value="amount"><?php echo esc_html__( 'Sort by amount', 'woo-smart-pack' ); ?></option>
                </select>
                <select name="order">
                    <?php $ordt = $order; ?>
                    <option value="desc"><?php echo esc_html__( 'Descending', 'woo-smart-pack' ); ?></option>
                    <option <?php echo ($ordt == 'asc') ? 'selected="selected"' : ''; ?> value="asc"><?php echo esc_html__( 'Ascending', 'woo-smart-pack' ); ?></option>                    
                </select>
                <input type="submit" class="button" value="<?php echo esc_html__( 'Sort', 'woo-smart-pack' ); ?>">		
            </div>

            <div class="pages">
                <span class="displaying-num"><?php esc_html( $paging->render_result_count( esc_html__( '{{from}} to {{to}} of {{total}} items', 'woo-smart-pack' ) ) ); ?></span>
                <?php wp_kses_post( $paging->render_links( $url_format, 5, $url_options, $default_url, '+' ) ); ?>
            </div>
            <br class="clear">
        </div>
    </form>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width:116px;"><b><?php echo esc_html__( 'Coupon Code', 'woo-smart-pack' ); ?></b></th>
                <th style="width: 92px;"><b><?php echo esc_html__( 'Amount', 'woo-smart-pack' ); ?></b></th>
                <th style="width: 92px;"><b><?php echo esc_html__( 'Balance', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'To', 'woo-smart-pack' ); ?></b></th>
                <th style="width: 130px;"><b><?php echo esc_html__( 'Event Date', 'woo-smart-pack' ); ?></b></th>  
                <th style="width: 130px;"><b><?php echo esc_html__( 'Expiry Date', 'woo-smart-pack' ); ?></b></th>                
                <th style="width: 77px;"><b><?php echo esc_html__( 'Actions', 'woo-smart-pack' ); ?></b></th>
            </tr>
        </thead>

        <tbody>
            <?php
        
           $allowed_html = WooZnd_Init::get_instance()->get_allow_html();
            
            foreach ( $rows as $row ) {
                ?>
                <tr>
                    <td><b><?php echo esc_html( strtoupper( $row[ 'coupon' ] ) ); ?></b></td>                    
                    <td><?php echo wp_kses( wc_price( $row[ 'amount' ] ), $allowed_html ); ?></td>
                    <td><?php echo wp_kses( wc_price( $row[ 'coupon_amount' ] ), $allowed_html ); ?></td> 
                    <td><?php echo esc_html( wooznd_get_giftcard_formetted_name( $row )); ?></td>
                    <td><?php echo esc_html( isset( $row[ 'send_date' ] ) ? WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'send_date' ], get_option( 'date_format' ) ) : esc_html__( 'N/A', 'woo-smart-pack' )); ?></td>
                    <td><?php echo esc_html( isset( $row[ 'expiry_date' ] ) ? WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'expiry_date' ], get_option( 'date_format' ) ) : esc_html__( 'N/A', 'woo-smart-pack' )); ?></td>
                    <td>
                        <a class="button edit-giftcard"><?php echo esc_html__( 'View/Edit', 'woo-smart-pack' ); ?></a>
                        <div class="popup-hidden edit-giftcard-template" data-title="<?php echo esc_html__( 'View/Edit Gift Card', 'woo-smart-pack' ); ?>">
                            <?php include 'popup/edit-giftcard.php'; ?>
                        </div>
                    </td>

                </tr>    
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th><b><?php echo esc_html__( 'Coupon Code', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Amount', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Balance', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'To', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Event Date', 'woo-smart-pack' ); ?></b></th>  
                <th><b><?php echo esc_html__( 'Expiry Date', 'woo-smart-pack' ); ?></b></th>                
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