<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include 'wallet-list_inc.php';

?><div class="wrap woo_wallet">
    <h1 class="wp-heading-inline"><?php echo esc_html__( 'All Wallets', 'woo-smart-pack' ); ?></h1>
    <a href="#" class="page-title-action new-wallet"><?php echo esc_html__( 'Add Wallet', 'woo-smart-pack' ); ?></a>
    <div class="popup-hidden new-wallet-template" data-title="<?php echo esc_html__( 'Add New Wallet', 'woo-smart-pack' ); ?>">
        <?php include 'popup/new-wallet.php'; ?>
    </div>
    <hr class="wp-header-end">
    <h2 class="screen-reader-text"><?php echo esc_html__( 'Filter account list', 'woo-smart-pack' ); ?></h2>
    <ul class="subsubsub">
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet' ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_ACCOUNT_STATUS_NONE) ? 'current' : ''; ?>"><?php echo esc_html__( 'All', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_WalletAccountDB::GetAccountsCount( '%', WOOZND_WALLET_ACCOUNT_STATUS_NONE ) ); ?>)</span></a> |</li>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet&status=' . WOOZND_WALLET_ACCOUNT_STATUS_UNLOCKED ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_ACCOUNT_STATUS_UNLOCKED) ? 'current' : ''; ?>"><?php echo esc_html__( 'Unlocked', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_WalletAccountDB::GetAccountsCount( '%', WOOZND_WALLET_ACCOUNT_STATUS_UNLOCKED ) ); ?>)</span></a> |</li>
        <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wznd-wallet&status=' . WOOZND_WALLET_ACCOUNT_STATUS_LOCKED ) ); ?>" class="<?php echo ($status == WOOZND_WALLET_ACCOUNT_STATUS_LOCKED) ? 'current' : ''; ?>"><?php echo esc_html__( 'Locked', 'woo-smart-pack' ); ?> <span class="count">(<?php echo esc_html( WooZnd_WalletAccountDB::GetAccountsCount( '%', WOOZND_WALLET_ACCOUNT_STATUS_LOCKED ) ); ?>)</span></a></li>
    </ul>


    <form id="posts-filter" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="wznd-wallet" />
        <?php
        foreach ( $url_options as $key => $value ) {
            if ( !($key == 'search' || $key == 'status') ) {
                ?>
                <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                <?php
            }
        }
        ?>
        <p class="search-box">
            <input type="search" style="width:200px;" name="search" placeholder="<?php echo esc_html__( 'name, email or account no', 'woo-smart-pack' ); ?>" value="<?php echo esc_attr( $search ); ?>">
            <input type="submit" class="button" value="<?php echo esc_html__( 'Search', 'woo-smart-pack' ); ?>"></p>
    </form>
    <form id="posts-filter" method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="wznd-wallet" />
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
                <select name="orderby">
                    <?php $ord = $orderby; ?>
                    <option value="open_date"><?php echo esc_html__( 'Sort by Date', 'woo-smart-pack' ); ?></option>                    
                    <option <?php echo ($ord == 'name') ? 'selected="selected"' : ''; ?> value="name"><?php echo esc_html__( 'Sort by name', 'woo-smart-pack' ); ?></option>
                    <option <?php echo ($ord == 'account_number') ? 'selected="selected"' : ''; ?> value="account_number"><?php echo esc_html__( 'Sort by account no', 'woo-smart-pack' ); ?></option>
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

                <th style="width:100px;"><b><?php echo esc_html__( 'Acount #', 'woo-smart-pack' ); ?></b></th>
                <th style="width: 150px;"><b><?php echo esc_html__( 'Account Name', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Ledger Balance', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Current Balance', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Total Spent', 'woo-smart-pack' ); ?></b></th>  
                <th style="width: 130px;"><b><?php echo esc_html__( 'Last Activity', 'woo-smart-pack' ); ?></b></th>                
                <th style="width: 180px;"><b><?php echo esc_html__( 'Actions', 'woo-smart-pack' ); ?></b></th>
                <th style="width:19px;"><span class="dashicons dashicons-lock"></span></th>
            </tr>
        </thead>

        <tbody>
            <?php
            
            $allowed_html = WooZnd_Init::get_instance()->get_allow_html();
            
            foreach ( $rows as $row ) {
                ?>
                <tr>
                    <td><?php echo esc_html( $row[ 'account_number' ] ); ?></td>
                    <td><a class="view-wallet" href="#"><?php echo esc_html( $row[ 'first_name' ] . ' ' . $row[ 'last_name' ] ); ?></a></td>
                    <td><?php echo wp_kses( wc_price( $row[ 'ledger_balance' ] ), $allowed_html ); ?></td>
                    <td><?php echo wp_kses( wc_price( $row[ 'current_balance' ] ), $allowed_html ); ?></td>
                    <td><?php echo wp_kses( wc_price( $row[ 'total_spent' ] ), $allowed_html ); ?></td>
                    <td><?php echo esc_html( isset( $row[ 'last_access' ] ) ? WooZnd_Util::MySQLTimeStampToDataTime( $row[ 'last_access' ], get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) : esc_html__( 'N/A', 'woo-smart-pack' ) ); ?></td>
                    <td>
                        <?php if ( $row[ 'locked' ] == true ) {
                            ?>
                            <a class="button" disabled="disabled"><?php echo esc_html__( 'Credit', 'woo-smart-pack' ); ?></a>
                            <a class="button" disabled="disabled"><?php echo esc_html__( 'Debit', 'woo-smart-pack' ); ?></a>
                            <a class="button view-wallet"><?php echo esc_html__( 'View', 'woo-smart-pack' ); ?></a>                  
                            <?php
                        } else {
                            ?>
                            <a class="button credit-wallet"><?php echo esc_html__( 'Credit', 'woo-smart-pack' ); ?></a>
                            <a class="button debit-wallet"><?php echo esc_html__( 'Debit', 'woo-smart-pack' ); ?></a>
                            <a class="button view-wallet"><?php echo esc_html__( 'View', 'woo-smart-pack' ); ?></a>
                            <div class="popup-hidden credit-wallet-template" data-title="<?php echo esc_html__( 'Credit Wallet', 'woo-smart-pack' ); ?>">
                                <?php include 'popup/credit-wallet.php'; ?>
                            </div>
                            <div class="popup-hidden debit-wallet-template" data-title="<?php echo esc_html__( 'Debit Wallet', 'woo-smart-pack' ); ?>">
                                <?php include 'popup/debit-wallet.php'; ?>
                            </div>

                            <?php
                        }
                        ?> 
                        <div class="popup-hidden view-wallet-template" data-title="<?php echo esc_html__( 'View Wallet', 'woo-smart-pack' ); ?>">
                            <?php include 'popup/view-wallet.php'; ?>
                        </div>
                    </td>
                    <td><span class="dashicons dashicons-<?php echo ($row[ 'locked' ] == true) ? 'lock' : 'unlock'; ?>"></span></td>
                </tr>    
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th><b><?php echo esc_html__( 'Acount #', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Account Name', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Ledger Balance', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Current Balance', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Total Spent', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Last Activity', 'woo-smart-pack' ); ?></b></th>
                <th><b><?php echo esc_html__( 'Actions', 'woo-smart-pack' ); ?></b></th>
                <th><span class="dashicons dashicons-lock"></span></th>
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

