<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?><form action="" method="post">
    <?php wp_nonce_field( $wooznd_nonce_action, 'wznd_giftcard_nonce' ); ?>
    <input type="hidden" name="action_name" value="editgiftcard">
    <input type="hidden" name="giftcard_id" value="<?php echo esc_attr( $row[ 'id' ] ); ?>"> 
    
    <div class="wsp-tabs">
        <ul class="wsp-tabs-head">
            <li class="wsp-active"><a id="m" href="#edit_general<?php echo esc_attr( $row[ 'id' ] ); ?>"><?php echo esc_html__( 'General', 'woo-smart-pack' ); ?></a></li>
            <li><a href="#edit_limits<?php echo esc_attr( $row[ 'id' ] ); ?>"><?php echo esc_html__( 'Limits & Usage Restriction', 'woo-smart-pack' ); ?></a></li>
            <li><a href="#edit_pdf_email<?php echo esc_attr( $row[ 'id' ] ); ?>"><?php echo esc_html__( 'PDF & Email Template', 'woo-smart-pack' ); ?></a></li> 
            <li><a href="#edit_delivary<?php echo esc_attr( $row[ 'id' ] ); ?>"><?php echo esc_html__( 'Card Delivery', 'woo-smart-pack' ); ?></a></li>            
        </ul>
        <div class="wsp-tabs-body">
            <div id="edit_general<?php echo esc_attr( $row[ 'id' ] ); ?>" class="wsp-tabs-body-content wsp-active">
                <?php include 'edit-giftcard/general.php'; ?>
            </div>
            <div id="edit_limits<?php echo esc_attr( $row[ 'id' ] ); ?>" class="wsp-tabs-body-content">
                <?php include 'edit-giftcard/limits_usage_restriction.php'; ?>
            </div>
            <div id="edit_pdf_email<?php echo esc_attr( $row[ 'id' ] ); ?>" class="wsp-tabs-body-content">
                <?php include 'edit-giftcard/pdf_email.php'; ?>
            </div>
            <div id="edit_delivary<?php echo esc_attr( $row[ 'id' ] ); ?>" class="wsp-tabs-body-content">
                <?php include 'edit-giftcard/card_delivery.php'; ?>
            </div>

        </div>
    </div>

    <div class="actions-box woo-wide-form">
        <input class="button button-primary" value="<?php echo esc_html__( 'Save', 'woo-smart-pack' ); ?>" type="submit">
        <input class="button button-secondary popup-btn-close" value="<?php echo esc_html__( 'Cancel', 'woo-smart-pack' ); ?>" type="button">
        <div style="float: right;">
            <input class="button delete-theme" name="delete" value="<?php echo esc_html__( 'Delete', 'woo-smart-pack' ); ?>" type="submit">
        </div>
    </div>

</form>
