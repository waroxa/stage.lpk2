<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'WooZnd_Util' ) ) {

    class WooZnd_Util {

        public static function get_order_status_suppassed( $order_status, $compare ) {
            $ord_status_level = array(
                'pending' => 1,
                'on-hold' => 2,
                'processing' => 3,
                'completed' => 4
            );

            $o_st = 0;

            if ( isset( $ord_status_level[ $order_status ] ) ) {
                $o_st = $ord_status_level[ $order_status ];
            }
            $comp = $ord_status_level[ $compare ];
            return($o_st >= $comp);
        }

        public static function GenRandomPattern( $pattern ) {
            $code = '';
            foreach ( self::GetCodePattern( $pattern ) as $value ) {
                if ( isset( $value[ 'allowed' ] ) ) {
                    $code .= self::GenRandom( $value[ 'length' ], $value[ 'allowed' ] );
                }
                if ( isset( $value[ 'text' ] ) ) {
                    $code .= $value[ 'text' ];
                }
            }
            return $code;
        }

        public static function GenRandom( $length = 10, $allowedchars = '' ) {
            $result = '';
            $cnt = 0;
            $allowed_chars = str_split( !empty( $allowedchars ) ? $allowedchars : '0123456789abcdefghijklmnopqrstwxyzABCDEFGHIJKLMNOPQRSTWXYZ-_+=!#@~.,', 1 );

            while ( $cnt < $length ) {
                $index = wp_rand( 0, (count( $allowed_chars ) - 1 ) );
                $result = $result . $allowed_chars[ $index ];
                $cnt++;
            }
            return $result;
        }

        public static function GetCodePattern( $pattern ) {
            //[N5],[A3],[C3],[Aa4],[Cc3],[a4],[c3]
            $result = array();
            $splts = str_split( $pattern, 1 );
            $brk_open = false;
            $p_text = '';

            foreach ( $splts as $splt ) {
                if ( $splt == ']' && $brk_open == true ) {
                    $t_splts = str_split( $p_text );
                    if ( count( $t_splts ) > 0 ) {
                        $s_tp = '';
                        $s_ln = '';
                        foreach ( $t_splts as $t_splt ) {
                            if ( $t_splt == 'A' || $t_splt == 'a' || $t_splt == 'C' || $t_splt == 'c' || $t_splt == 'N' ) {
                                $s_tp .= $t_splt;
                            } else {
                                $s_ln .= $t_splt;
                            }
                        }
                        if ( $s_tp == 'N' ) {
                            $s_tp = '0123456789';
                        }
                        if ( $s_tp == 'A' ) {
                            $s_tp = 'ABCDEFGHIJKLMNOPQRSTWXYZ';
                        }
                        if ( $s_tp == 'a' ) {
                            $s_tp = 'abcdefghijklmnopqrstwxyz';
                        }
                        if ( $s_tp == 'Aa' ) {
                            $s_tp = 'abcdefghijklmnopqrstwxyzABCDEFGHIJKLMNOPQRSTWXYZ';
                        }
                        if ( $s_tp == 'C' ) {
                            $s_tp = '0123456789ABCDEFGHIJKLMNOPQRSTWXYZ';
                        }
                        if ( $s_tp == 'c' ) {
                            $s_tp = '0123456789abcdefghijklmnopqrstwxyz';
                        }
                        if ( $s_tp == 'Cc' ) {
                            $s_tp = '0123456789abcdefghijklmnopqrstwxyzABCDEFGHIJKLMNOPQRSTWXYZ';
                        }
                        $result[] = array(
                            'allowed' => $s_tp,
                            'length' => absint( $s_ln )
                        );
                    }


                    $p_text = '';
                    $brk_open = false;
                } else if ( $splt == '[' && $brk_open == false ) {
                    if ( strlen( $p_text ) > 0 ) {
                        $result[] = array(
                            'text' => $p_text,
                            'length' => strlen( $p_text )
                        );
                        $p_text = '';
                    }
                    $brk_open = true;
                } else {
                    $p_text .= $splt;
                }
            }
            if ( strlen( $p_text ) > 0 ) {
                $result[] = array(
                    'text' => $p_text,
                    'length' => strlen( $p_text )
                );
            }
            return $result;
        }

        public static function GetCurrentTimeOffset( $days, $format, $current_time = '' ) {

            $inc = $days * DAY_IN_SECONDS;

            $c_time = 0;

            if ( '' == $current_time ) {

                $c_time = date_format( DateTime::createFromFormat( "U", current_time( 'U' ) ), 'U' );
            } else {

                $c_time = date_format( DateTime::createFromFormat( "Y-m-d", $current_time ), 'U' );
            }

            $stamp = DateTime::createFromFormat( "U", $c_time + $inc );

            return date_format( $stamp, $format );
        }

        public static function MySQLTimeStampToDataTime( $timestamp, $format, $defualt = "N/A" ) {

            if ( '0000-00-00 00:00:00' == $timestamp ) {

                return $defualt;
            }

            if ( $timestamp ) {

                $new_datetime = DateTime::createFromFormat( "Y-m-d H:i:s", $timestamp );

                return $new_datetime->format( $format );
            }

            return $defualt;
        }

        public static function GetFormattedProductName( $ids, $multible = true ) {

            if ( $ids == '' || $ids == 0 ) {

                return '';
            }

            if ( $multible == false ) {

                $product = wc_get_product( $ids );

                if ( is_object( $product ) ) {

                    return $product->get_formatted_name();
                }
            }

            $json_ids = array();

            if ( is_array( $ids ) ) {

                $product_ids = $ids;
            } else {

                $product_ids = array_filter( array_map( 'absint', explode( ',', $ids ) ) );
            }

            foreach ( $product_ids as $pro_id ) {

                $product = wc_get_product( $pro_id );

                if ( is_object( $product ) ) {

                    $json_ids[ $pro_id ] = wp_kses_post( $product->get_formatted_name() );
                }
            }

            return json_encode( $json_ids );
        }

        public static function GetProductCategoryOption() {

            $terms_args = array(
                'taxonomy' => 'product_cat',
                'orderby' => 'name',
                'hide_empty' => false
            );

            $categories = get_terms( $terms_args );

            $result = array();

            if ( $categories ) {

                foreach ( $categories as $cat ) {

                    $result[ $cat->term_id ] = $cat->name;
                }
            }
            return $result;
        }

        public static function GetPostTypeOption( $post_type = 'post', $status = 'publish', $include = array() ) {

            global $wpdb;

            $result = $include;

            $posts = array();

            if ( $post_type == 'post' ) {

                $posts = get_posts();
            } else if ( $post_type == 'page' ) {

                $posts = get_pages();
            } else {

                $sql = "SELECT ID, post_title FROM $wpdb->posts WHERE (post_type=%s) AND (post_status=%s) ORDER BY post_title ASC ";

                $sql = $wpdb->prepare( $sql, $post_type, $status ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

                $posts = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            }

            if ( $posts ) {

                foreach ( $posts as $p ) {

                    $result[ $p->ID ] = $p->post_title;
                }
            }

            return $result;
        }

        public static function GetPaymentMethodList() {

            $result = array();

            try {

                foreach ( WC()->payment_gateways->get_available_payment_gateways() as $key => $method ) {

                    $result[ $key ] = $method->title;
                }
            }
            catch ( Exception $ex ) {
                
            }

            return $result;
        }

        public static function Encrypt( $data ) {

            $key = self::GetOption( 'encryption_key', '' );
            $iv = self::GetOption( 'encryption_key_vi', '' );
            $method = 'AES128';

            $c_d = openssl_encrypt( $data, $method, $key, OPENSSL_RAW_DATA, $iv );
            $c_data = base64_encode( $c_d );

            if ( $c_data === false ) {

                return '';
            } else {

                return $c_data;
            }
        }

        public static function Decrypt( $data ) {

            $key = self::GetOption( 'encryption_key', '' );
            $iv = self::GetOption( 'encryption_key_vi', '' );

            $method = 'AES128';
            $c_d = base64_decode( $data );
            $c_data = openssl_decrypt( $c_d, $method, $key, OPENSSL_RAW_DATA, $iv );

            if ( $c_data === false ) {

                return '';
            } else {

                return $c_data;
            }
        }

        public static function UpdateOption( $option, $value, $autoload = null ) {

            return update_option( 'wooznd_' . $option, $value, $autoload );
        }

        public static function DeleteOption( $option ) {

            return delete_option( 'wooznd_' . $option );
        }

        public static function GetOption( $option, $default ) {

            switch ( $option ) {

                case 'encryption_key':

                    $en_key = get_option( 'wooznd_encryption_key', '' );

                    if ( empty( $en_key ) ) {

                        $en_key = self::GenRandom( 16, '0123456789ABCDEFGHIJKLMNOPUQRSTWXYZ' );

                        self::UpdateOption( 'encryption_key', $en_key );
                    }

                    return $en_key;
                case 'encryption_key_vi':

                    $en_key = get_option( 'wooznd_encryption_key_vi', '' );

                    if ( empty( $en_key ) ) {

                        $en_key = self::GenRandom( 16, '0123456789ABCDEFGHIJKLMNOPUQRSTWXYZ' );

                        self::UpdateOption( 'encryption_key_vi', $en_key );
                    }

                    return $en_key;
                case'system_login':

                    $user_id = get_option( 'wooznd_system_id', 0 );

                    if ( $user_id <= 0 ) {

                        return $default;
                    }

                    $user = get_user_by( 'id', $user_id );

                    if ( !isset( $user->user_login ) ) {

                        return $default;
                    }

                    return $user->user_login;
                default:

                    return get_option( 'wooznd_' . $option, $default );
            }
        }

        public static function SendMail( $to = '', $subject = '', $message = '', $attachments = array(), $from = '' ) {

            $headers = array( 'Content-Type: text/html; charset=UTF-8' );

            if ( $from == '' ) {

                $headers[] = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>';
            } else {

                $headers[] = 'From: ' . $from;
            }

            wp_mail( $to, $subject, $message, $headers, $attachments );
        }

        public static function CreateTable( $sql ) {

            try {

                require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

                ob_start();
                dbDelta( $sql );
                ob_get_clean();

                return true;
            }
            catch ( Exception $e ) {

                return false;
            }
        }

    }

}

