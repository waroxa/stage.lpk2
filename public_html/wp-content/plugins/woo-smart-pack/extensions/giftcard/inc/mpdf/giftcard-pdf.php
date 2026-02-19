<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !function_exists( 'wznd_get_giftcard_pdf_obj' ) ) {

    function wznd_get_giftcard_pdf_obj() {

        if ( !class_exists( '\Mpdf\Mpdf' ) ) {

            require_once __DIR__ . '/vendor/autoload.php';
        }

        $config = array(
            'mode' => 'c',
            'format' => 'A4',
            'default_font_size' => 0,
            'default_font' => '',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'orientation' => 'P',
        );

        return new \Mpdf\Mpdf( $config );
    }

}

if ( !function_exists( 'wznd_create_giftcard_pdf' ) ) {

    function wznd_create_giftcard_pdf( $item_id, $template_id ) {

        $pst = get_post( $template_id );

        if ( !isset( $pst->ID ) ) {

            return;
        }

        $giftcard_path = WP_CONTENT_DIR . '/uploads/woo-smart-pack/giftcards/giftcard' . $item_id . '.pdf';

        $custom_css = get_post_meta( $template_id, '_wznd_giftcard_custom_css', true );

        $css_styles = '';

        if ( !empty( $custom_css ) ) {

            $css_styles = '<style>' . $custom_css . '</style>';
        }

        $html = $css_styles . do_shortcode( $pst->post_content );

        $mpdf = wznd_get_giftcard_pdf_obj();

        $mpdf->WriteHTML( $html );

        $mpdf->Output( $giftcard_path, 'F' );

        return $giftcard_path;
    }

}

