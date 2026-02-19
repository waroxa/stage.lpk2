<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'woocommerce_init', 'wooznd_giftcard_upgrade_database', 1 );

function wooznd_giftcard_upgrade_database() {

    $giftcard_version = WooZnd_Util::GetOption( 'ws_giftcard_db', 99 );
    //Add create Database here.

    if ( $giftcard_version < 100 ) {
        wooznd_giftcard_upgrade_database100();
    }
    if ( $giftcard_version < 101 ) {
        wooznd_giftcard_upgrade_database101();
    }
}

function wooznd_giftcard_upgrade_database100() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$wpdb->prefix}wooznd_giftcard_items ("
            . "id bigint(20) UNSIGNED NOT NULL,"
            . "coupon_id bigint(20) UNSIGNED NOT NULL,"
            . "amount decimal(10,0) NOT NULL,"
            . "to_name varchar(256) NOT NULL,"
            . "to_email varchar(256) NOT NULL,"
            . "message varchar(512) NOT NULL,"
            . "from_name varchar(256) NOT NULL,"
            . "from_email varchar(256) NOT NULL,"
            . "giftcard_template_id bigint(20) UNSIGNED NOT NULL,"
            . "send_date timestamp NULL DEFAULT NULL,"
            . "expiry_date timestamp NULL DEFAULT NULL,"
            . "status tinyint(4) UNSIGNED NOT NULL,"
            . "PRIMARY KEY (id),"
            . "UNIQUE KEY coupon_id (coupon_id)"
            . ") $charset_collate;";

    WooZnd_Util::CreateTable( $sql );

    WooZnd_Util::UpdateOption( 'ws_giftcard_db', 100 );


    $image_path = WOOZND_ASSET_URL . 'images/fontawsome_magnet.png';
    $image_bg_path = WOOZND_ASSET_URL . 'images/unsplash_giftcard_happy_bg.png';

    ob_start();

    include 'giftcard-template-contents.php';

    $content = ob_get_clean();

    $page_definitions = array(
        'card-template-one' => array(
            'title' => esc_html__( 'Gift Card Template', 'woo-smart-pack' ),
            'content' => $content,
            'custom_css' => 'table{
padding:0px;
width:100%;
margin:0px;
border-spacing:0px;
border-collapse:collapse;
}
td{
margin:0px;
}'
        ),
    );

    foreach ( $page_definitions as $slug => $page ) {
        // Check that the page doesn't exist already
        $query = new WP_Query( array( 'post_type' => 'wznd_giftcard', 'name' => $slug ) );
        if ( !$query->have_posts() ) {
            // Add the page using the data from the array above

            $post_id = wp_insert_post(
                    array(
                        'post_content' => $page[ 'content' ],
                        'post_name' => $slug,
                        'post_title' => $page[ 'title' ],
                        'post_status' => 'publish',
                        'post_type' => 'wznd_giftcard',
                        'ping_status' => 'closed',
                        'comment_status' => 'closed',
                    )
            );
            if ( $post_id > 0 ) {
                update_post_meta( $post_id, '_wznd_giftcard_custom_css', $page[ 'custom_css' ] );
            }
        }
    }
}

function wooznd_giftcard_upgrade_database101() {
    
    global $wpdb;
    
    ob_start();
    
    try {
    
        $col_delivery_method = "ALTER TABLE {$wpdb->prefix}wooznd_giftcard_items ADD delivery_method tinyint(4) UNSIGNED NULL";
        $wpdb->query( $col_delivery_method ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        
        $col_email_template_id = "ALTER TABLE {$wpdb->prefix}wooznd_giftcard_items ADD email_template_id bigint(20) UNSIGNED DEFAULT NULL";
        $wpdb->query( $col_email_template_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        
        WooZnd_Util::UpdateOption( 'ws_giftcard_db', 101 );
    } catch ( Exception $ex ) {
        
    }

    ob_get_clean();
}
