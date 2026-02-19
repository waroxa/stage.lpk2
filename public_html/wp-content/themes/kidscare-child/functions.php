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

// The Events Calendar can trigger an infinite redirect loop when combined
// with certain plugins (e.g. translation plugins) because of WordPress'
// canonical redirect behaviour. Disable canonical redirects on single
// event pages to prevent "too many redirects" errors.
add_filter( 'redirect_canonical', 'kidscare_disable_event_canonical_redirect', 10, 2 );
/**
 * Disable canonical redirection on single Tribe events.
 *
 * @param string $redirect_url   The redirect URL WordPress would use.
 * @param string $requested_url  The originally requested URL.
 * @return false|string          False to stop redirect, or original URL.
 */
function kidscare_disable_event_canonical_redirect( $redirect_url, $requested_url ) {
    if ( is_singular( 'tribe_events' ) ) {
        return false;
    }

    return $redirect_url;
}

/**
 * Build the QuickCal appointment markup for WooCommerce emails.
 *
 * @param array $appointments Appointment data collected from QuickCal.
 * @param bool  $plain_text   Whether to return a plain text representation.
 *
 * @return string
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
                $output .= sprintf(
                    '%s: %s' . PHP_EOL,
                    __( 'Date and time', 'kidscare-child' ),
                    $appointment_details['timeslot']
                );
            }

            foreach ( $appointment_details['fields'] as $label => $value ) {
                $label = trim( (string) $label );
                $value = trim( (string) $value );

                if ( '' === $label && '' === $value ) {
                    continue;
                }

                $output .= sprintf( '%s: %s' . PHP_EOL, $label, $value );
            }

            $output .= PHP_EOL;
        }

        return $output;
    }

    ob_start();
    ?>
    <div class="kidscare-email-appointments">
        <h3><?php esc_html_e( 'Appointment details', 'kidscare-child' ); ?></h3>
        <?php foreach ( $appointments as $appointment_details ) : ?>
            <?php if ( ! empty( $appointment_details['product_name'] ) ) : ?>
                <p><strong><?php echo esc_html( $appointment_details['product_name'] ); ?></strong></p>
            <?php endif; ?>

            <?php if ( ! empty( $appointment_details['timeslot'] ) ) : ?>
                <p><strong><?php esc_html_e( 'Date and time', 'kidscare-child' ); ?>:</strong> <?php echo esc_html( $appointment_details['timeslot'] ); ?></p>
            <?php endif; ?>

            <?php if ( ! empty( $appointment_details['fields'] ) ) : ?>
                <ul>
                    <?php foreach ( $appointment_details['fields'] as $label => $value ) :
                        $label = trim( (string) $label );
                        $value = trim( (string) $value );

                        if ( '' === $label && '' === $value ) {
                            continue;
                        }
                        ?>
                        <li><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $value ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php
    return trim( ob_get_clean() );
}

add_action( 'woocommerce_email_after_order_table', 'kidscare_email_append_appointment_details', 15, 4 );
/**
 * Append QuickCal appointment details to WooCommerce emails.
 *
 * @param WC_Order        $order          The order object.
 * @param bool            $sent_to_admin  Whether the email is sent to admin.
 * @param bool            $plain_text     Whether the email is plain text.
 * @param WC_Email|string $email          Email object or ID.
 */
function kidscare_email_append_appointment_details( $order, $sent_to_admin, $plain_text, $email ) {
    if ( ! ( $email instanceof WC_Email ) ) {
        return;
    }

    if ( ! ( $order instanceof WC_Order ) ) {
        return;
    }

    kidscare_populate_quickcal_email_placeholders( $email, $order );
}

/**
 * Collect QuickCal appointment data for a WooCommerce order.
 *
 * @param WC_Order $order Order object.
 *
 * @return array
 */
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
        } catch ( Exception $e ) {
            continue;
        }

        if ( ! $appointment ) {
            continue;
        }

        $processed_app_ids[] = $appointment_id;

        $appointments[] = array(
            'product_name' => $item->get_name(),
            'timeslot'     => isset( $appointment->timeslot_text ) ? $appointment->timeslot_text : '',
            'fields'       => isset( $appointment->custom_fields ) && is_array( $appointment->custom_fields )
                ? $appointment->custom_fields
                : array(),
        );
    }

    return $appointments;
}

/**
 * Populate the QuickCal placeholders on the provided email instance.
 *
 * @param WC_Email $email Email instance currently being generated.
 * @param WC_Order $order Related order object.
 *
 * @return void
 */
function kidscare_populate_quickcal_email_placeholders( WC_Email $email, WC_Order $order ) {
    static $processed_emails = array();

    $email_id = function_exists( 'spl_object_id' ) ? spl_object_id( $email ) : spl_object_hash( $email );

    if ( isset( $processed_emails[ $email_id ] ) ) {
        return;
    }

    $processed_emails[ $email_id ] = true;

    if ( ! isset( $email->placeholders['{quickcal_appointments_html}'] ) ) {
        $email->placeholders['{quickcal_appointments_html}'] = '';
    }

    if ( ! isset( $email->placeholders['{quickcal_appointments_plain}'] ) ) {
        $email->placeholders['{quickcal_appointments_plain}'] = '';
    }

    $appointments = kidscare_get_quickcal_appointments_for_order( $order );

    if ( empty( $appointments ) ) {
        return;
    }

    $email->placeholders['{quickcal_appointments_html}']  = kidscare_get_quickcal_appointment_details_markup( $appointments, false );
    $email->placeholders['{quickcal_appointments_plain}'] = kidscare_get_quickcal_appointment_details_markup( $appointments, true );
}

/**
 * Retrieve the QuickCal email placeholder tokens.
 *
 * @return array
 */
function kidscare_get_quickcal_email_placeholder_tokens() {
    return array(
        '{quickcal_appointments_html}',
        '{quickcal_appointments_plain}',
    );
}

add_filter( 'woocommerce_email_format_string_find', 'kidscare_register_quickcal_email_placeholder_tokens', 10, 2 );
/**
 * Ensure QuickCal placeholders are available in WooCommerce email settings.
 *
 * @param array    $find  Placeholder tokens WooCommerce is aware of.
 * @param WC_Email $email Current email object.
 *
 * @return array
 */
function kidscare_register_quickcal_email_placeholder_tokens( $find, $email ) {
    if ( $email instanceof WC_Email && $email->object instanceof WC_Order ) {
        kidscare_populate_quickcal_email_placeholders( $email, $email->object );
    }

    foreach ( kidscare_get_quickcal_email_placeholder_tokens() as $token ) {
        if ( ! in_array( $token, $find, true ) ) {
            $find[] = $token;
        }
    }

    return $find;
}

add_filter( 'woocommerce_email_format_string_replace', 'kidscare_register_quickcal_email_placeholder_replacements', 10, 2 );
/**
 * Provide default replacement values for QuickCal placeholders.
 *
 * @param array    $replace Replacement values for known placeholders.
 * @param WC_Email $email   Current email object.
 *
 * @return array
 */
function kidscare_register_quickcal_email_placeholder_replacements( $replace, $email ) {
    foreach ( kidscare_get_quickcal_email_placeholder_tokens() as $token ) {
        if ( $email instanceof WC_Email && isset( $email->placeholders[ $token ] ) ) {
            continue;
        }

        $replace[] = '';
    }

    return $replace;
}


/**
 * Register a fallback post type for stored parental waivers if none exists.
 */
function kidscare_register_parental_authorization_post_type() {
    if ( post_type_exists( 'kidscare_waiver' ) || post_type_exists( KIDSCARE_PARENTAL_WAIVER_POST_TYPE ) ) {
        return;
    }

    $result = register_post_type(
        KIDSCARE_PARENTAL_WAIVER_POST_TYPE,
        array(
            'labels' => array(
                'name'          => __( 'Autorisations parentales', 'kidscare-child' ),
                'singular_name' => __( 'Autorisation parentale', 'kidscare-child' ),
            ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'supports'            => array( 'title', 'editor' ),
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'show_in_admin_bar'   => false,
            'show_in_nav_menus'   => false,
            'exclude_from_search' => true,
        )
    );

    if ( is_wp_error( $result ) ) {
        error_log( sprintf( 'Kidscare: unable to register parental waiver fallback post type: %s', $result->get_error_message() ) );

        return;
    }
}
add_action( 'init', 'kidscare_register_parental_authorization_post_type', 1 );

/**
 * Default field values for the parental authorization form.
 *
 * @return array
 */
function kidscare_get_parental_authorization_defaults() {
    return array(
        'parent_first_name'   => '',
        'parent_last_name'    => '',
        'parent_email'        => '',
        'parent_phone'        => '',
        'minors'              => array(
            array(
                'first_name' => '',
                'last_name'  => '',
                'birth_date' => '',
            ),
        ),
        'consent_guardian'    => '',
        'adult_terms_accept'  => '',
    );
}

/**
 * Handle parental authorization form submissions.
 *
 * @return array{0: array, 1: array, 2: bool}
 */
function kidscare_handle_parental_authorization_submission() {
    $defaults   = kidscare_get_parental_authorization_defaults();
    $form_data  = $defaults;
    $form_errors = array();
    $form_success = false;

    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
        return array( $form_data, $form_errors, $form_success );
    }

    if ( empty( $_POST['kidscare_parental_authorization_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kidscare_parental_authorization_nonce'] ) ), 'kidscare_parental_authorization' ) ) {
        $form_errors['general'] = __( 'Une erreur de validation est survenue. Veuillez réessayer.', 'kidscare-child' );

        return array( $form_data, $form_errors, $form_success );
    }

    $form_data['parent_first_name']  = isset( $_POST['parent_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_first_name'] ) ) : '';
    $form_data['parent_last_name']   = isset( $_POST['parent_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_last_name'] ) ) : '';
    $form_data['parent_email']       = isset( $_POST['parent_email'] ) ? sanitize_email( wp_unslash( $_POST['parent_email'] ) ) : '';
    $form_data['parent_phone']       = isset( $_POST['parent_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_phone'] ) ) : '';
    $raw_minors = array();

    if ( isset( $_POST['minors'] ) && is_array( $_POST['minors'] ) ) {
        $raw_minors = wp_unslash( $_POST['minors'] );
    }

    $sanitized_minors = array();
    $valid_minors     = array();

    foreach ( $raw_minors as $index => $minor ) {
        if ( ! is_array( $minor ) ) {
            continue;
        }

        $sanitized_minor = array(
            'first_name' => isset( $minor['first_name'] ) ? sanitize_text_field( $minor['first_name'] ) : '',
            'last_name'  => isset( $minor['last_name'] ) ? sanitize_text_field( $minor['last_name'] ) : '',
            'birth_date' => isset( $minor['birth_date'] ) ? sanitize_text_field( $minor['birth_date'] ) : '',
        );

        $sanitized_minors[ $index ] = $sanitized_minor;

        $minor_has_value = '' !== $sanitized_minor['first_name']
            || '' !== $sanitized_minor['last_name']
            || '' !== $sanitized_minor['birth_date'];

        if ( ! $minor_has_value ) {
            continue;
        }

        $minor_errors = array();

        if ( '' === $sanitized_minor['first_name'] ) {
            $minor_errors['first_name'] = __( 'Veuillez indiquer le prénom de l\'enfant.', 'kidscare-child' );
        }

        if ( '' === $sanitized_minor['last_name'] ) {
            $minor_errors['last_name'] = __( 'Veuillez indiquer le nom de famille de l\'enfant.', 'kidscare-child' );
        }

        if ( '' === $sanitized_minor['birth_date'] ) {
            $minor_errors['birth_date'] = __( 'Veuillez indiquer la date de naissance de l\'enfant.', 'kidscare-child' );
        }

        if ( ! empty( $minor_errors ) ) {
            $form_errors['minors'][ $index ] = $minor_errors;
            continue;
        }

        $valid_minors[] = $sanitized_minor;
    }

    if ( empty( $sanitized_minors ) ) {
        $sanitized_minors = $defaults['minors'];
    }

    $normalized_minors = array_values( $sanitized_minors );
    $minor_key_map     = array_flip( array_keys( $sanitized_minors ) );

    if ( isset( $form_errors['minors'] ) && is_array( $form_errors['minors'] ) ) {
        $normalized_errors = array();

        foreach ( $form_errors['minors'] as $minor_index => $minor_error ) {
            if ( 'general' === $minor_index ) {
                $normalized_errors['general'] = $minor_error;
                continue;
            }

            if ( isset( $minor_key_map[ $minor_index ] ) ) {
                $normalized_errors[ $minor_key_map[ $minor_index ] ] = $minor_error;
            }
        }

        $form_errors['minors'] = $normalized_errors;
    }

    $form_data['minors'] = $normalized_minors;
    $form_data['consent_guardian']   = isset( $_POST['consent_guardian'] ) ? '1' : '';
    $form_data['adult_terms_accept'] = isset( $_POST['adult_terms_accept'] ) ? '1' : '';

    if ( '' === $form_data['parent_first_name'] ) {
        $form_errors['parent_first_name'] = __( 'Veuillez indiquer votre prénom.', 'kidscare-child' );
    }

    if ( '' === $form_data['parent_last_name'] ) {
        $form_errors['parent_last_name'] = __( 'Veuillez indiquer votre nom de famille.', 'kidscare-child' );
    }

    if ( '' === $form_data['parent_email'] || ! is_email( $form_data['parent_email'] ) ) {
        $form_errors['parent_email'] = __( 'Veuillez fournir une adresse courriel valide.', 'kidscare-child' );
    }

    if ( empty( $valid_minors ) ) {
        $form_errors['minors']['general'] = __( 'Veuillez ajouter au moins un enfant.', 'kidscare-child' );
    }

    if ( '' === $form_data['consent_guardian'] ) {
        $form_errors['consent_guardian'] = __( 'Vous devez confirmer être le parent ou tuteur légal.', 'kidscare-child' );
    }

    if ( '' === $form_data['adult_terms_accept'] ) {
        $form_errors['adult_terms_accept'] = __( 'Vous devez accepter les termes de la décharge de responsabilité.', 'kidscare-child' );
    }

    if ( ! empty( $form_errors ) ) {
        return array( $form_data, $form_errors, $form_success );
    }

    $form_data_to_save            = $form_data;
    $form_data_to_save['minors']  = $valid_minors;

    $saved = kidscare_save_parental_authorization_waiver( $form_data_to_save );

    if ( is_wp_error( $saved ) ) {
        $form_errors['general'] = $saved->get_error_message();

        return array( $form_data, $form_errors, $form_success );
    }

    $form_success = true;
    $form_data    = $defaults;

    return array( $form_data, $form_errors, $form_success );
}

/**
 * Persist parental authorization data as a waiver post.
 *
 * @param array $form_data The sanitized form data.
 *
 * @return int|WP_Error
 */
function kidscare_save_parental_authorization_waiver( array $form_data ) {
    $post_type = 'kidscare_waiver';

    if ( ! post_type_exists( $post_type ) ) {
        kidscare_register_parental_authorization_post_type();

        if ( post_type_exists( KIDSCARE_PARENTAL_WAIVER_POST_TYPE ) ) {
            $post_type = KIDSCARE_PARENTAL_WAIVER_POST_TYPE;
        } else {
            return new WP_Error(
                'kidscare_waiver_post_type_missing',
                __( 'Une erreur est survenue lors de l\'enregistrement de l\'autorisation parentale. Veuillez contacter l\'administrateur du site.', 'kidscare-child' )
            );
        }
    }

    $normalized_minors = kidscare_normalize_parental_authorization_minors(
        isset( $form_data['minors'] ) ? $form_data['minors'] : array()
    );

    $minor_names = array();

    foreach ( $normalized_minors as $minor ) {
        $minor_names[] = trim( $minor['first_name'] . ' ' . $minor['last_name'] );
    }

    $primary_child = isset( $minor_names[0] ) ? $minor_names[0] : '';
    $title_names   = $minor_names;

    if ( count( $title_names ) > 1 ) {
        $last_name  = array_pop( $title_names );
        $primary_child = implode( ', ', $title_names ) . ' ' . __( 'et', 'kidscare-child' ) . ' ' . $last_name;
    }

    $title = $primary_child
        ? sprintf( __( 'Autorisation parentale pour %s', 'kidscare-child' ), $primary_child )
        : __( 'Autorisation parentale', 'kidscare-child' );

    $postarr = array(
        'post_type'   => $post_type,
        'post_status' => 'private',
        'post_title'  => $title,
        'post_content'=> kidscare_format_parental_authorization_content(
            array_merge(
                $form_data,
                array( 'minors' => $normalized_minors )
            )
        ),
    );

    $waiver_id = wp_insert_post( $postarr, true );

    if ( is_wp_error( $waiver_id ) ) {
        return $waiver_id;
    }

    $meta = array(
        '_kidscare_waiver_parent_first_name'  => $form_data['parent_first_name'],
        '_kidscare_waiver_parent_last_name'   => $form_data['parent_last_name'],
        '_kidscare_waiver_parent_email'       => $form_data['parent_email'],
        '_kidscare_waiver_parent_phone'       => $form_data['parent_phone'],
        '_kidscare_waiver_child_name'         => isset( $minor_names[0] ) ? $minor_names[0] : '',
        '_kidscare_waiver_minors'             => $normalized_minors,
        '_kidscare_waiver_guardian_consent'   => (bool) $form_data['consent_guardian'],
        '_kidscare_waiver_terms_accept'       => (bool) $form_data['adult_terms_accept'],
    );

    foreach ( $meta as $meta_key => $meta_value ) {
        update_post_meta( $waiver_id, $meta_key, $meta_value );
    }

    return $waiver_id;
}

/**
 * Normalize a parental authorization field label for easier comparisons.
 *
 * @param string $label Original label text.
 *
 * @return string
 */
function kidscare_normalize_parental_authorization_label( $label ) {
    $label = remove_accents( (string) $label );
    $label = strtolower( $label );
    $label = preg_replace( '/[^a-z0-9]+/', '_', $label );

    return trim( $label, '_' );
}

/**
 * Normalize a list of minors to ensure consistent structure.
 *
 * @param mixed $minors Raw minors data.
 *
 * @return array<int, array{first_name: string, last_name: string, birth_date: string}>
 */
function kidscare_normalize_parental_authorization_minors( $minors ) {
    if ( ! is_array( $minors ) ) {
        return array();
    }

    $normalized = array();

    foreach ( $minors as $minor ) {
        if ( ! is_array( $minor ) ) {
            continue;
        }

        $first_name = isset( $minor['first_name'] ) ? trim( (string) $minor['first_name'] ) : '';
        $last_name  = isset( $minor['last_name'] ) ? trim( (string) $minor['last_name'] ) : '';
        $birth_date = isset( $minor['birth_date'] ) ? trim( (string) $minor['birth_date'] ) : '';

        if ( '' === $first_name && '' === $last_name && '' === $birth_date ) {
            continue;
        }

        $normalized[] = array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'birth_date' => $birth_date,
        );
    }

    return array_values( $normalized );
}

/**
 * Determine whether a normalized label matches one of the provided patterns.
 *
 * @param string $normalized_label Normalized label generated by
 *                                 kidscare_normalize_parental_authorization_label().
 * @param array  $patterns         Substrings that should match.
 * @param array  $exclusions       Substrings that should not be present.
 *
 * @return bool
 */
function kidscare_parental_authorization_label_matches( $normalized_label, array $patterns, array $exclusions = array() ) {
    foreach ( $exclusions as $exclusion ) {
        if ( false !== strpos( $normalized_label, $exclusion ) ) {
            return false;
        }
    }

    foreach ( $patterns as $pattern ) {
        if ( '' === $pattern ) {
            continue;
        }

        if ( false !== strpos( $normalized_label, $pattern ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Extract parent and child information from a set of QuickCal custom fields.
 *
 * @param array $fields Custom field data returned by QuickCal.
 *
 * @return array{parent: array{first_name: string, last_name: string, email: string, phone: string}, child: array{first_name: string, last_name: string, birth_date: string}}
 */
function kidscare_extract_parental_authorization_from_fields( array $fields ) {
    $parent = array(
        'first_name' => '',
        'last_name'  => '',
        'email'      => '',
        'phone'      => '',
    );

    $child = array(
        'first_name' => '',
        'last_name'  => '',
        'birth_date' => '',
    );

    foreach ( $fields as $label => $value ) {
        if ( ! is_scalar( $value ) ) {
            continue;
        }

        $normalized_label = kidscare_normalize_parental_authorization_label( $label );
        $clean_value      = sanitize_text_field( (string) $value );

        if ( '' === $clean_value ) {
            continue;
        }

        if ( kidscare_parental_authorization_label_matches( $normalized_label, array( 'prenom_du_parent', 'parent_first_name', 'guardian_first_name' ) ) ) {
            $parent['first_name'] = $clean_value;
            continue;
        }

        if ( kidscare_parental_authorization_label_matches( $normalized_label, array( 'nom_de_famille_du_parent', 'parent_last_name', 'guardian_last_name' ) ) ) {
            $parent['last_name'] = $clean_value;
            continue;
        }

        if ( kidscare_parental_authorization_label_matches( $normalized_label, array( 'adresse_courriel', 'courriel', 'email', 'parent_email' ) ) ) {
            $parent['email'] = $clean_value;
            continue;
        }

        if ( kidscare_parental_authorization_label_matches( $normalized_label, array( 'numero_de_telephone', 'telephone', 'phone' ), array( 'enfant', 'child' ) ) ) {
            $parent['phone'] = $clean_value;
            continue;
        }

        if ( kidscare_parental_authorization_label_matches( $normalized_label, array( 'prenom_de_lenfant', 'prenom_de_l_enfant', 'prenom_enfant', 'child_first_name', 'prenom_du_participant' ) ) ) {
            $child['first_name'] = $clean_value;
            continue;
        }

        if ( kidscare_parental_authorization_label_matches( $normalized_label, array( 'nom_de_famille_de_lenfant', 'nom_de_famille_de_l_enfant', 'nom_de_lenfant', 'nom_de_l_enfant', 'nom_enfant', 'child_last_name', 'nom_de_famille_du_participant' ) ) ) {
            $child['last_name'] = $clean_value;
            continue;
        }

        if ( kidscare_parental_authorization_label_matches( $normalized_label, array( 'date_de_naissance', 'dob', 'date_of_birth', 'birthdate', 'child_birth_date' ) ) ) {
            $child['birth_date'] = $clean_value;
            continue;
        }
    }

    return array(
        'parent' => array_map( 'trim', $parent ),
        'child'  => array_map( 'trim', $child ),
    );
}

/**
 * Create parental authorization entries for an order's appointments.
 *
 * @param int      $order_id Order identifier.
 * @param WC_Order $order    Optional order instance passed by the action hook.
 */
function kidscare_generate_parental_authorizations_for_order( $order_id, $order = null ) {
    if ( $order instanceof WC_Order ) {
        $wc_order = $order;
    } else {
        $wc_order = wc_get_order( $order_id );
    }

    if ( ! $wc_order instanceof WC_Order ) {
        return;
    }

    $existing_meta = $wc_order->get_meta( '_kidscare_generated_parental_waivers', true );

    if ( ! empty( $existing_meta ) ) {
        return;
    }

    $appointments = kidscare_get_quickcal_appointments_for_order( $wc_order );

    if ( empty( $appointments ) ) {
        return;
    }

    $grouped_waivers = array();

    foreach ( $appointments as $appointment ) {
        if ( empty( $appointment['fields'] ) || ! is_array( $appointment['fields'] ) ) {
            continue;
        }

        $extracted = kidscare_extract_parental_authorization_from_fields( $appointment['fields'] );
        $parent    = $extracted['parent'];
        $child     = $extracted['child'];

        if ( empty( array_filter( $parent ) ) ) {
            continue;
        }

        $parent_key_parts = array();

        foreach ( array( 'first_name', 'last_name', 'email' ) as $parent_field ) {
            if ( ! empty( $parent[ $parent_field ] ) ) {
                $parent_key_parts[] = strtolower( $parent[ $parent_field ] );
            }
        }

        if ( ! empty( $parent['phone'] ) ) {
            $parent_key_parts[] = preg_replace( '/[^0-9]+/', '', $parent['phone'] );
        }

        if ( empty( $parent_key_parts ) ) {
            $parent_key_parts[] = md5( wp_json_encode( $appointment['fields'] ) );
        }

        $parent_key = md5( implode( '|', $parent_key_parts ) );

        if ( ! isset( $grouped_waivers[ $parent_key ] ) ) {
            $grouped_waivers[ $parent_key ] = array(
                'parent_first_name'  => $parent['first_name'],
                'parent_last_name'   => $parent['last_name'],
                'parent_email'       => $parent['email'],
                'parent_phone'       => $parent['phone'],
                'minors'             => array(),
            );
        } else {
            foreach ( array( 'first_name' => 'parent_first_name', 'last_name' => 'parent_last_name', 'email' => 'parent_email', 'phone' => 'parent_phone' ) as $source_key => $target_key ) {
                if ( empty( $grouped_waivers[ $parent_key ][ $target_key ] ) && ! empty( $parent[ $source_key ] ) ) {
                    $grouped_waivers[ $parent_key ][ $target_key ] = $parent[ $source_key ];
                }
            }
        }

        if ( empty( array_filter( $child ) ) ) {
            continue;
        }

        $is_duplicate_child = false;

        foreach ( $grouped_waivers[ $parent_key ]['minors'] as $existing_minor ) {
            $matches_first = isset( $existing_minor['first_name'], $child['first_name'] )
                ? 0 === strcasecmp( $existing_minor['first_name'], $child['first_name'] )
                : false;
            $matches_last  = isset( $existing_minor['last_name'], $child['last_name'] )
                ? 0 === strcasecmp( $existing_minor['last_name'], $child['last_name'] )
                : false;
            $matches_birth = isset( $existing_minor['birth_date'], $child['birth_date'] )
                ? 0 === strcasecmp( $existing_minor['birth_date'], $child['birth_date'] )
                : false;

            if ( $matches_first && $matches_last && ( ! $child['birth_date'] || $matches_birth ) ) {
                $is_duplicate_child = true;
                break;
            }
        }

        if ( $is_duplicate_child ) {
            continue;
        }

        $grouped_waivers[ $parent_key ]['minors'][] = array(
            'first_name' => $child['first_name'],
            'last_name'  => $child['last_name'],
            'birth_date' => $child['birth_date'],
        );
    }

    $created_waivers = array();

    foreach ( $grouped_waivers as $waiver_data ) {
        if ( empty( $waiver_data['minors'] ) ) {
            continue;
        }

        $form_data = kidscare_get_parental_authorization_defaults();

        $form_data['parent_first_name']  = $waiver_data['parent_first_name'];
        $form_data['parent_last_name']   = $waiver_data['parent_last_name'];
        $form_data['parent_email']       = $waiver_data['parent_email'];
        $form_data['parent_phone']       = $waiver_data['parent_phone'];
        $form_data['minors']             = array_values( $waiver_data['minors'] );
        $form_data['consent_guardian']   = true;
        $form_data['adult_terms_accept'] = true;

        $waiver_id = kidscare_save_parental_authorization_waiver( $form_data );

        if ( is_wp_error( $waiver_id ) ) {
            continue;
        }

        $created_waivers[] = (int) $waiver_id;
        update_post_meta( $waiver_id, '_kidscare_waiver_source_order_id', $wc_order->get_id() );
    }

    if ( empty( $created_waivers ) ) {
        return;
    }

    $wc_order->update_meta_data( '_kidscare_generated_parental_waivers', $created_waivers );
    $wc_order->save();
}

add_action( 'woocommerce_order_status_processing', 'kidscare_generate_parental_authorizations_for_order', 10, 2 );
add_action( 'woocommerce_order_status_completed', 'kidscare_generate_parental_authorizations_for_order', 10, 2 );
add_action( 'woocommerce_order_status_partial-payment', 'kidscare_generate_parental_authorizations_for_order', 10, 2 );
add_action( 'woocommerce_payment_complete', 'kidscare_generate_parental_authorizations_for_order', 10 );

/**
 * Format the waiver post content for internal reference.
 *
 * @param array $form_data Sanitized form values.
 *
 * @return string
 */
function kidscare_format_parental_authorization_content( array $form_data ) {
    $lines = array(
        sprintf( __( 'Parent ou tuteur: %1$s %2$s', 'kidscare-child' ), $form_data['parent_first_name'], $form_data['parent_last_name'] ),
        sprintf( __( 'Courriel: %s', 'kidscare-child' ), $form_data['parent_email'] ),
        sprintf( __( 'Téléphone: %s', 'kidscare-child' ), $form_data['parent_phone'] ),
        sprintf( __( 'Consentement du tuteur légal: %s', 'kidscare-child' ), $form_data['consent_guardian'] ? __( 'Oui', 'kidscare-child' ) : __( 'Non', 'kidscare-child' ) ),
        sprintf( __( 'Décharge acceptée: %s', 'kidscare-child' ), $form_data['adult_terms_accept'] ? __( 'Oui', 'kidscare-child' ) : __( 'Non', 'kidscare-child' ) ),
    );

    $minors = kidscare_normalize_parental_authorization_minors(
        isset( $form_data['minors'] ) ? $form_data['minors'] : array()
    );

    if ( ! empty( $minors ) ) {
        $lines[] = '';
        $lines[] = __( 'Enfant(s) autorisé(s):', 'kidscare-child' );

        foreach ( $minors as $index => $minor ) {
            $details = trim( $minor['first_name'] . ' ' . $minor['last_name'] );

            if ( $minor['birth_date'] ) {
                $details = $details
                    ? $details . ' — ' . sprintf( __( 'Date de naissance: %s', 'kidscare-child' ), $minor['birth_date'] )
                    : sprintf( __( 'Date de naissance: %s', 'kidscare-child' ), $minor['birth_date'] );
            }

            $lines[] = sprintf( __( 'Enfant %1$d: %2$s', 'kidscare-child' ), $index + 1, $details );
        }
    }

    return implode( PHP_EOL, array_filter( $lines ) );
}

/**
 * Register the waiver acceptance admin column when the post type exists.
 */
function kidscare_register_waiver_terms_admin_columns() {
    $post_types = array();

    if ( post_type_exists( 'kidscare_waiver' ) ) {
        $post_types[] = 'kidscare_waiver';
    }

    if ( post_type_exists( KIDSCARE_PARENTAL_WAIVER_POST_TYPE ) ) {
        $post_types[] = KIDSCARE_PARENTAL_WAIVER_POST_TYPE;
    }

    foreach ( $post_types as $post_type ) {
        add_filter( "manage_edit-{$post_type}_columns", 'kidscare_add_waiver_terms_column' );
        add_action( "manage_{$post_type}_posts_custom_column", 'kidscare_render_waiver_terms_column', 10, 2 );
    }
}
add_action( 'init', 'kidscare_register_waiver_terms_admin_columns', 20 );

/**
 * Add the waiver acceptance status column.
 *
 * @param array $columns List of columns.
 *
 * @return array
 */
function kidscare_add_waiver_terms_column( $columns ) {
    $columns['waiver_terms_accept'] = __( 'Décharge acceptée', 'kidscare-child' );

    return $columns;
}

/**
 * Render the waiver acceptance column value.
 *
 * @param string $column  Current column slug.
 * @param int    $post_id Current post ID.
 */
function kidscare_render_waiver_terms_column( $column, $post_id ) {
    if ( 'waiver_terms_accept' !== $column ) {
        return;
    }

    $accepted = (bool) get_post_meta( $post_id, '_kidscare_waiver_terms_accept', true );

    echo $accepted ? esc_html__( 'Oui', 'kidscare-child' ) : esc_html__( 'Non', 'kidscare-child' );
}
