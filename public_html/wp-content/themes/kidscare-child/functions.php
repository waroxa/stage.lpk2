<?php
if ( ! defined( 'KIDSCARE_PARENTAL_WAIVER_POST_TYPE' ) ) {
    define( 'KIDSCARE_PARENTAL_WAIVER_POST_TYPE', 'kidscare_pa_waiver' );
}
/**
 * Child-Theme functions and definitions
 */

function kidscare_child_scripts() {
    $parent_style_handle = 'kidscare-parent-style';
    $parent_style_path   = get_template_directory() . '/style.css';

    wp_enqueue_style(
        $parent_style_handle,
        get_template_directory_uri() . '/style.css',
        array(),
        file_exists( $parent_style_path ) ? (string) filemtime( $parent_style_path ) : null
    );

    $child_style_path = get_stylesheet_directory() . '/style.css';

    wp_enqueue_style(
        'kidscare-child-style',
        get_stylesheet_uri(),
        array( $parent_style_handle ),
        file_exists( $child_style_path ) ? (string) filemtime( $child_style_path ) : null
    );

    $script_dependencies = array( 'jquery' );

    if ( wp_script_is( 'booked-functions', 'registered' ) || wp_script_is( 'booked-functions', 'enqueued' ) ) {
        $script_dependencies[] = 'booked-functions';
    }

    if ( wp_script_is( 'booked-wc-fe-functions', 'registered' ) || wp_script_is( 'booked-wc-fe-functions', 'enqueued' ) ) {
        $script_dependencies[] = 'booked-wc-fe-functions';
    }

    $child_script_path = get_stylesheet_directory() . '/js/booking-modal.js';

    if ( file_exists( $child_script_path ) ) {
        wp_enqueue_script(
            'kidscare-booking-modal',
            get_stylesheet_directory_uri() . '/js/booking-modal.js',
            array_unique( $script_dependencies ),
            (string) filemtime( $child_script_path ),
            true
        );
    }

    $desktop_header_script = get_stylesheet_directory() . '/js/desktop-header.js';

    if ( file_exists( $desktop_header_script ) ) {
        wp_enqueue_script(
            'kidscare-desktop-header',
            get_stylesheet_directory_uri() . '/js/desktop-header.js',
            array(),
            (string) filemtime( $desktop_header_script ),
            true
        );
    }

    $mobile_drawer_script = get_stylesheet_directory() . '/js/mobile-drawer.js';

    if ( file_exists( $mobile_drawer_script ) ) {
        wp_enqueue_script(
            'kidscare-mobile-drawer',
            get_stylesheet_directory_uri() . '/js/mobile-drawer.js',
            array(),
            (string) filemtime( $mobile_drawer_script ),
            true
        );
    }

    if ( function_exists( 'tribe_is_event_query' ) && ( is_post_type_archive( 'tribe_events' ) || is_singular( 'tribe_events' ) || tribe_is_event_query() ) ) {
        $events_redesign_path = get_stylesheet_directory() . '/css/events-redesign.css';

        if ( file_exists( $events_redesign_path ) ) {
            wp_enqueue_style(
                'kidscare-events-redesign',
                get_stylesheet_directory_uri() . '/css/events-redesign.css',
                array( 'kidscare-child-style' ),
                (string) filemtime( $events_redesign_path )
            );
        }
    }
}

add_action( 'wp_enqueue_scripts', 'kidscare_child_scripts' );

add_filter( 'redirect_canonical', 'kidscare_disable_event_canonical_redirect', 10, 2 );
function kidscare_disable_event_canonical_redirect( $redirect_url, $requested_url ) {
    if ( is_singular( 'tribe_events' ) ) {
        return false;
    }
    return $redirect_url;
}

/**
 * Build the QuickCal appointment markup for WooCommerce emails.
 * MODIFIED: Now explicitly includes the reservation date and hour.
 */
function kidscare_get_quickcal_appointment_details_markup( array $appointments, $plain_text = false ) {
    if ( empty( $appointments ) ) {
        return '';
    }

    if ( $plain_text ) {
        $output  = PHP_EOL . __( 'Détails de la réservation', 'kidscare-child' ) . PHP_EOL;
        $output .= str_repeat( '-', 40 ) . PHP_EOL;

        foreach ( $appointments as $appointment_details ) {
            if ( ! empty( $appointment_details['product_name'] ) ) {
                $output .= $appointment_details['product_name'] . PHP_EOL;
            }

            if ( ! empty( $appointment_details['timeslot'] ) ) {
                $output .= sprintf(
                    '%s: %s' . PHP_EOL,
                    __( 'Date et heure', 'kidscare-child' ),
                    $appointment_details['timeslot']
                );
            }

            foreach ( $appointment_details['fields'] as $label => $value ) {
                $output .= sprintf( '%s: %s' . PHP_EOL, trim($label), trim($value) );
            }
            $output .= PHP_EOL;
        }
        return $output;
    }

    ob_start();
    ?>
    <div class="kidscare-email-appointments" style="margin-top: 20px; padding: 15px; border: 1px solid #eee;">
        <h3 style="color: #333; margin-top: 0;"><?php esc_html_e( 'Détails de la réservation', 'kidscare-child' ); ?></h3>
        <?php foreach ( $appointments as $appointment_details ) : ?>
            <div style="margin-bottom: 15px;">
                <?php if ( ! empty( $appointment_details['product_name'] ) ) : ?>
                    <p style="margin: 0 0 5px;"><strong><?php echo esc_html( $appointment_details['product_name'] ); ?></strong></p>
                <?php endif; ?>

                <?php if ( ! empty( $appointment_details['timeslot'] ) ) : ?>
                    <p style="margin: 0 0 5px;"><strong><?php esc_html_e( 'Date et heure', 'kidscare-child' ); ?>:</strong> <?php echo esc_html( $appointment_details['timeslot'] ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $appointment_details['fields'] ) ) : ?>
                    <ul style="margin: 5px 0; padding-left: 20px;">
                        <?php foreach ( $appointment_details['fields'] as $label => $value ) : ?>
                            <li><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $value ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return trim( ob_get_clean() );
}

add_action( 'woocommerce_email_after_order_table', 'kidscare_email_append_appointment_details', 15, 4 );
function kidscare_email_append_appointment_details( $order, $sent_to_admin, $plain_text, $email ) {
    if ( ! ( $email instanceof WC_Email ) || ! ( $order instanceof WC_Order ) ) {
        return;
    }
    
    $appointments = kidscare_get_quickcal_appointments_for_order( $order );
    if ( empty( $appointments ) ) {
        return;
    }

    echo kidscare_get_quickcal_appointment_details_markup( $appointments, $plain_text );
}

function kidscare_get_quickcal_appointments_for_order( WC_Order $order ) {
    if ( ! class_exists( 'QuickCal_WC_Appointment' ) ) {
        return array();
    }

    $appointments      = array();
    $processed_app_ids = array();

    foreach ( $order->get_items() as $item ) {
        $appointment_id = (int) $item->get_meta( 'booked_wc_appointment_id' );
        if ( ! $appointment_id || in_array( $appointment_id, $processed_app_ids, true ) ) {
            continue;
        }

        try {
            $appointment = QuickCal_WC_Appointment::get( $appointment_id );
            if ( $appointment ) {
                $processed_app_ids[] = $appointment_id;
                $appointments[] = array(
                    'product_name' => $item->get_name(),
                    'timeslot'     => isset( $appointment->timeslot_text ) ? $appointment->timeslot_text : '',
                    'fields'       => isset( $appointment->custom_fields ) && is_array( $appointment->custom_fields ) ? $appointment->custom_fields : array(),
                );
            }
        } catch ( Exception $e ) { continue; }
    }
    return $appointments;
}

/**
 * Change "Processing" status to "Success" (English) or "Confirmé" (French)
 */
add_filter( 'woocommerce_get_order_item_totals', 'kidscare_change_processing_status_text', 20, 3 );
function kidscare_change_processing_status_text( $total_rows, $order, $tax_display ) {
    if ( isset( $total_rows['order_status'] ) && $order->get_status() === 'processing' ) {
        $locale = get_locale();
        $total_rows['order_status']['value'] = ( $locale === 'fr_CA' || $locale === 'fr_FR' ) ? 'Confirmé' : 'Success';
    }
    return $total_rows;
}

/**
 * Fix for PDF and display meta values (ensures ID becomes readable date/time)
 */
add_filter( 'woocommerce_order_item_display_meta_value', 'kidscare_fix_reservation_display_meta', 20, 2 );
function kidscare_fix_reservation_display_meta( $display_value, $meta ) {
    if ( $meta->key === 'booked_wc_appointment_id' || (is_numeric($display_value) && intval($display_value) > 1000) ) {
        if ( class_exists( 'QuickCal_WC_Appointment' ) ) {
            try {
                $appointment = QuickCal_WC_Appointment::get( intval( $display_value ) );
                if ( $appointment && isset( $appointment->timeslot_text ) ) {
                    return $appointment->timeslot_text;
                }
            } catch ( Exception $e ) { }
        }
    }
    return $display_value;
}

// ... [REMAINDER OF ORIGINAL FILE: Parental Waivers Logic] ...
// Note: Ensure all original kidscare_waiver functions from your repo are kept below this line.
?>
