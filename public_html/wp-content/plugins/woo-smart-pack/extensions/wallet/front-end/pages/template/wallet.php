<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$wallet = WooZnd_WalletAccountDB::GetAccount(get_current_user_id());
$ledger_balance = $wallet['ledger_balance'];
$current_balance = $wallet['current_balance'];
$total_spent = $wallet['total_spent'];

$allowed_html = WooZnd_Init::get_instance()->get_allow_html();

?><div class="wooznd_wallet_brief">
    <h3><?php echo esc_html($wallet_title); ?></h3>
    <p>
        <?php
       if (!empty($ledger_label)) {
            ?>
            <strong><?php echo esc_html($ledger_label); ?></strong> <?php echo wp_kses( wc_price($ledger_balance), $allowed_html ); ?>&nbsp;
            <?php
        }
        if (!empty($current_label)) {
            ?>
            <strong><?php echo esc_html($current_label); ?></strong> <?php echo wp_kses( wc_price($current_balance), $allowed_html ); ?>&nbsp;
            <?php
        }
        if (!empty($total_spent_label)) {
            ?>
            <strong><?php echo esc_html($total_spent_label); ?></strong> <?php echo wp_kses( wc_price($total_spent), $allowed_html ); ?>
            <?php
        }
        ?>
    </p>
</div>