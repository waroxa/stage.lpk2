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
 */
function kidscare_get_quickcal_appointment_details_markup( array $appointments, $plain_text = false ) {
    if ( empty( $appointments ) ) {
        return '';
    }

    if ( $plain_text ) {
        $output  = PHP_EOL . __( 'Appointment details', 'kidscare-child' ) . PHP_EOL;
        $output .= str_repeat( '-', 40 ) . PHP_EOL;
        foreach ( $appointments as $appointment_details ) {
            if ( ! empty( $appointment_details['product_name'] ) ) {
                $output .= $appointment_details['product_name'] . PHP_EOL;
            }
            if ( ! empty( $appointment_details['timeslot'] ) ) {
                $output .= sprintf( '%s: %s' . PHP_EOL, __( 'Date and time', 'kidscare-child' ), $appointment_details['timeslot'] );
            }
            foreach ( $appointment_details['fields'] as $label => $value ) {
                $label = trim( (string) $label );
                $value = trim( (string) $value );
                if ( '' === $label && '' === $value ) { continue; }
                $output .= sprintf( '%s: %s' . PHP_EOL, $label, $value );
            }
            $output .= PHP_EOL;
        }
        return $output;
    }

    ob_start();
    ?>
    <div class="kidscare-email-appointments" style="margin-bottom: 40px;">
        <h3 style="color: #ce5a67; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;"><?php esc_html_e( 'Appointment details', 'kidscare-child' ); ?></h3>
        <?php foreach ( $appointments as $appointment_details ) : ?>
            <div style="margin-bottom: 20px; border-left: 4px solid #ce5a67; padding-left: 15px;">
                <?php if ( ! empty( $appointment_details['product_name'] ) ) : ?>
                    <p style="margin: 0 0 5px;"><strong><?php echo esc_html( $appointment_details['product_name'] ); ?></strong></p>
                <?php endif; ?>

                <?php if ( ! empty( $appointment_details['timeslot'] ) ) : ?>
                    <p style="margin: 0 0 5px;"><strong><?php esc_html_e( 'Date and time', 'kidscare-child' ); ?>:</strong> <?php echo esc_html( $appointment_details['timeslot'] ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $appointment_details['fields'] ) ) : ?>
                    <ul style="margin: 5px 0 0; padding: 0; list-style: none;">
                        <?php foreach ( $appointment_details['fields'] as $label => $value ) :
                            $label = trim( (string) $label );
                            $value = trim( (string) $value );
                            if ( '' === $label && '' === $value ) { continue; }
                            ?>
                            <li style="margin-bottom: 3px;"><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $value ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return trim( ob_get_clean() );
}

/**
 * Collect QuickCal appointment data for a WooCommerce order.
 */
function kidscare_get_quickcal_appointments_for_order( $order ) {
    if ( ! class_exists( 'QuickCal_WC_Appointment' ) ) {
        return array();
    }
    $appointments = array();
    $processed_app_ids = array();
    foreach ( $order->get_items() as $item ) {
        $appointment_id = (int) $item->get_meta( 'booked_wc_appointment_id' );
        if ( ! $appointment_id || in_array( $appointment_id, $processed_app_ids, true ) ) { continue; }
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
        } catch ( Exception $e ) {}
    }
    return $appointments;
}

/**
 * Inject appointment details into the email.
 */
add_action( 'woocommerce_email_after_order_table', 'kidscare_inject_appointment_details', 5, 4 );
function kidscare_inject_appointment_details( $order, $sent_to_admin, $plain_text, $email ) {
    $appointments = kidscare_get_quickcal_appointments_for_order( $order );
    if ( ! empty( $appointments ) ) {
        echo kidscare_get_quickcal_appointment_details_markup( $appointments, $plain_text );
    }
}

/**
 * Change "Processing" status to "Confirmé" (French) or "Success" (English) in order totals.
 */
add_filter( 'woocommerce_get_order_item_totals', 'kidscare_change_processing_status_text', 10, 3 );
function kidscare_change_processing_status_text( $total_rows, $order, $tax_display ) {
    if ( isset( $total_rows['order_status'] ) && $order->get_status() === 'processing' ) {
        $locale = get_locale();
        $total_rows['order_status']['value'] = ( $locale === 'fr_CA' || $locale === 'fr_FR' ) ? 'Confirmé' : 'Success';
    }
    return $total_rows;
}

/**
 * ------------------------------------------------------------------------------------------------
 * KIDS CARE EMAIL TESTER TOOL (v4 ULTIMATE SAFETY)
 * ------------------------------------------------------------------------------------------------
 */

add_action( 'admin_menu', 'kidscare_register_email_tester_page' );
function kidscare_register_email_tester_page() {
    add_submenu_page( 'tools.php', 'Kids Care Email Tester', 'Kids Care Email Tester', 'manage_options', 'kidscare-email-tester', 'kidscare_render_email_tester_page' );
}

function kidscare_render_email_tester_page() {
    if ( ! empty( $_POST['order_id'] ) && ! empty( $_POST['email_class'] ) && ! empty( $_POST['recipient'] ) ) {
        $order_id    = (int) $_POST['order_id'];
        $email_class = sanitize_text_field( $_POST['email_class'] );
        $recipient   = sanitize_email( $_POST['recipient'] );
        $include_customer = ! empty( $_POST['include_customer'] );

        // MASTER KILL SWITCH: If not including customer, force the recipient for this specific email class
        if ( ! $include_customer ) {
            add_filter( 'woocommerce_email_recipient_' . $email_class, function( $rec, $object ) use ( $recipient ) {
                return $recipient;
            }, 9999, 2 );
        }

        $emails = WC()->mailer()->get_emails();
        if ( isset( $emails[ $email_class ] ) ) {
            $email = $emails[ $email_class ];
            $email->recipient = $recipient;
            $email->trigger( $order_id );
            
            echo '<div class="updated"><p>Email "' . esc_html( $email->get_title() ) . '" sent to <strong>' . esc_html( $recipient ) . '</strong></p></div>';
            if ( ! $include_customer ) {
                echo '<div class="notice notice-error"><p><strong>SAFETY MODE:</strong> The customer email address was completely blocked for this test.</p></div>';
            }
        }
    }

    $orders = wc_get_orders( array( 'limit' => 20 ) );
    ?>
    <div class="wrap">
        <h1>Kids Care Email Tester <span style="font-size: 12px; color: #ce5a67;">v4 Ultimate</span></h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">Order</th>
                    <td>
                        <select name="order_id">
                            <?php foreach ( $orders as $order ) : ?>
                                <option value="<?php echo $order->get_id(); ?>">#<?php echo $order->get_id(); ?> - <?php echo $order->get_billing_first_name(); ?> <?php echo $order->get_billing_last_name(); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Email Type</th>
                    <td>
                        <select name="email_class">
                            <?php foreach ( WC()->mailer()->get_emails() as $id => $email ) { echo '<option value="' . esc_attr( $id ) . '">' . esc_html( $email->get_title() ) . '</option>'; } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Your Email (Tester)</th>
                    <td><input type="email" name="recipient" class="regular-text" required placeholder="Enter YOUR email here"></td>
                </tr>
                <tr>
                    <th scope="row">Safety</th>
                    <td>
                        <label><input type="checkbox" name="include_customer" value="1"> Also send to the actual customer (NOT RECOMMENDED)</label>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Send Test Email' ); ?>
        </form>
    </div>
    <?php
}

/**
 * ------------------------------------------------------------------------------------------------
 * PARENTAL WAIVER LOGIC (PRESERVED)
 * ------------------------------------------------------------------------------------------------
 */

function kidscare_generate_parental_authorizations_for_order( $order_id, $order = null ) {
    $wc_order = $order ? $order : wc_get_order( $order_id );
    if ( ! $wc_order || $wc_order->get_meta( '_kidscare_generated_parental_waivers' ) ) { return; }

    $grouped_waivers = array();
    foreach ( $wc_order->get_items() as $item ) {
        $child_fn = $item->get_meta( 'Prénom de l\'enfant' ) ?: $item->get_meta( 'Enfant - Prénom' );
        $child_ln = $item->get_meta( 'Nom de l\'enfant' ) ?: $item->get_meta( 'Enfant - Nom' );
        if ( ! $child_fn && ! $child_ln ) { continue; }

        $parent_fn = $item->get_meta( 'Prénom du parent' ) ?: $wc_order->get_billing_first_name();
        $parent_ln = $item->get_meta( 'Nom du parent' ) ?: $wc_order->get_billing_last_name();
        $parent_em = $item->get_meta( 'Courriel du parent' ) ?: $wc_order->get_billing_email();
        $parent_ph = $item->get_meta( 'Téléphone du parent' ) ?: $wc_order->get_billing_phone();
        $parent_key = md5( strtolower( $parent_fn . $parent_ln . $parent_em ) );

        if ( ! isset( $grouped_waivers[ $parent_key ] ) ) {
            $grouped_waivers[ $parent_key ] = array( 'parent_first_name' => $parent_fn, 'parent_last_name' => $parent_ln, 'parent_email' => $parent_em, 'parent_phone' => $parent_ph, 'minors' => array() );
        }
        $grouped_waivers[ $parent_key ]['minors'][] = array( 'first_name' => $child_fn, 'last_name' => $child_ln, 'birth_date' => $item->get_meta( 'Date de naissance de l\'enfant' ) ?: $item->get_meta( 'Enfant - Date de naissance' ) );
    }

    $created = array();
    foreach ( $grouped_waivers as $data ) {
        if ( empty( $data['minors'] ) ) { continue; }
        if ( function_exists( 'kidscare_save_parental_authorization_waiver' ) ) {
            $waiver_id = kidscare_save_parental_authorization_waiver( array( 'parent_first_name' => $data['parent_first_name'], 'parent_last_name' => $data['parent_last_name'], 'parent_email' => $data['parent_email'], 'parent_phone' => $data['parent_phone'], 'minors' => array_values( $data['minors'] ), 'consent_guardian' => true, 'adult_terms_accept' => true ) );
            if ( ! is_wp_error( $waiver_id ) ) { $created[] = (int) $waiver_id; update_post_meta( $waiver_id, '_kidscare_waiver_source_order_id', $wc_order->get_id() ); }
        }
    }
    if ( ! empty( $created ) ) { $wc_order->update_meta_data( '_kidscare_generated_parental_waivers', $created ); $wc_order->save(); }
}

add_action( 'woocommerce_order_status_processing', 'kidscare_generate_parental_authorizations_for_order', 10, 2 );
add_action( 'woocommerce_order_status_completed', 'kidscare_generate_parental_authorizations_for_order', 10, 2 );
add_action( 'woocommerce_payment_complete', 'kidscare_generate_parental_authorizations_for_order', 10 );

function kidscare_register_waiver_terms_admin_columns() {
    $pts = array();
    if ( post_type_exists( 'kidscare_waiver' ) ) { $pts[] = 'kidscare_waiver'; }
    if ( post_type_exists( KIDSCARE_PARENTAL_WAIVER_POST_TYPE ) ) { $pts[] = KIDSCARE_PARENTAL_WAIVER_POST_TYPE; }
    foreach ( $pts as $pt ) {
        add_filter( "manage_edit-{$pt}_columns", function( $cols ) { $cols['waiver_terms_accept'] = __( 'Décharge acceptée', 'kidscare-child' ); return $cols; } );
        add_action( "manage_{$pt}_posts_custom_column", function( $col, $pid ) { if ( 'waiver_terms_accept' === $col ) { echo (bool) get_post_meta( $pid, '_kidscare_waiver_terms_accept', true ) ? 'Oui' : 'Non'; } }, 10, 2 );
    }
}
add_action( 'init', 'kidscare_register_waiver_terms_admin_columns', 20 );