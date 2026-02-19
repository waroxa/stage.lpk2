<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'QRcode' ) ) {
    
    include_once 'phpqrcode/qrlib.php';
}

include_once 'barcode/barcode.php';

if ( !function_exists( 'wznd_qrcode_or_barcode' ) ) {

    function wznd_qrcode_or_barcode( $code ) {
        
        //Paths
        $brpath = WP_CONTENT_DIR . '/uploads/woo-smart-pack/barcodes/br_' . $code . '.png';
        $qrpath = WP_CONTENT_DIR . '/uploads/woo-smart-pack/qrcodes/qr_' . $code . '.png';

        //Create folders
        wznd_qrcode_or_barcode_folders();

        //Create Files
        QRcode::png( $code, $qrpath, "M", 4, 2 );

        //Create the barcode
        $img = wznd_code128BarCode( $code, 1 );
        
        //Start output buffer to capture the image
        //Output PNG image
        imagepng( $img, $brpath );
    }

}

if ( !function_exists( 'wznd_qrcode_or_barcode_folders' ) ) {

    function wznd_qrcode_or_barcode_folders() {
        try {
            wp_mkdir_p( WP_CONTENT_DIR . '/uploads/woo-smart-pack/barcodes' );
        } catch ( Exception $ex ) {
            
        }
        try {
            wp_mkdir_p( WP_CONTENT_DIR . '/uploads/woo-smart-pack/qrcodes' );
        } catch ( Exception $ex ) {
            
        }
        try {
            wp_mkdir_p( WP_CONTENT_DIR . '/uploads/woo-smart-pack/giftcards' );
        } catch ( Exception $ex ) {
            
        }
    }

}

