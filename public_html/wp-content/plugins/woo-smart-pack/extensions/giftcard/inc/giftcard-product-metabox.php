<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'product_type_options', 'wooznd_gift_card_product_option' );

if ( !function_exists( 'wooznd_gift_card_product_option' ) ) {

    function wooznd_gift_card_product_option( $product_type_options ) {

        $product_type_options[ 'wznd_enable_giftcard' ] = array(
            'id' => '_wznd_enable_giftcard',
            'wrapper_class' => 'show_if_simple show_if_variable',
            'label' => esc_html__( 'Gift Card', 'woo-smart-pack' ),
            'description' => esc_html__( 'Turn this product into gift product.', 'woo-smart-pack' ),
            'default' => 'no'
        );

        return $product_type_options;
    }

}


add_filter( 'woocommerce_product_data_tabs', 'wooznd_giftcard_product_tabs' );

if ( !function_exists( 'wooznd_giftcard_product_tabs' ) ) {

    function wooznd_giftcard_product_tabs( $tabs ) {

        $tabs[ 'wznd_giftcard' ] = array(
            'label' => esc_html__( 'Gift Card', 'woo-smart-pack' ),
            'target' => 'wznd_giftcard_options',
            'class' => array( 'show_if_simple', 'show_if_variable' ),
        );

        return $tabs;
    }

}



add_action( 'woocommerce_product_data_panels', 'wooznd_giftcard_options_product_tab_content', 99 );

if ( !function_exists( 'wooznd_giftcard_options_product_tab_content' ) ) {

    function wooznd_giftcard_options_product_tab_content() {

        global $post;

        $allowed_html = WooZnd_Init::get_instance()->get_allow_html();
        ?><div id='wznd_giftcard_options' class='panel woocommerce_options_panel'>
            <div class='options_group'><?php
                wp_nonce_field( basename( __FILE__ ), 'wznd_giftcard_nonce' );
                woocommerce_wp_select(
                        array(
                            'id' => '_wznd_giftcard_discount_type',
                            'label' => esc_html__( 'Discount Type', 'woo-smart-pack' ),
                            'class' => 'wc-enhanced-select',
                            'style' => 'width:60%',
                            'options' => array(
                                '' => esc_html__( 'Default', 'woo-smart-pack' ),
                                'fixed_cart' => esc_html__( 'Cart Discount', 'woo-smart-pack' ),
                                'fixed_product' => esc_html__( 'Product Discount', 'woo-smart-pack' ),
                            ),
                            'desc_tip' => true,
                            'description' => esc_html__( 'Controls gift card discount type.', 'woo-smart-pack' )
                        )
                );
                ?>
            </div><div class='options_group'><?php
                wp_nonce_field( basename( __FILE__ ), 'wznd_giftcard_nonce' );
                woocommerce_wp_select(
                        array(
                            'id' => '_wznd_giftcard_price_type',
                            'label' => esc_html__( 'Pricing Type', 'woo-smart-pack' ),
                            'class' => 'wc-enhanced-select',
                            'style' => 'width:60%',
                            'options' => array(
                                'default' => esc_html__( 'Sales or Regular Price', 'woo-smart-pack' ),
                                'user' => esc_html__( 'User Price', 'woo-smart-pack' ),
                                'range' => esc_html__( 'Price Range', 'woo-smart-pack' ),
                                'select' => esc_html__( 'Price Selection', 'woo-smart-pack' )
                            ),
                            'desc_tip' => true,
                            'description' => esc_html__( 'Controls gift card pricing type.', 'woo-smart-pack' )
                        )
                );
                ?>
            </div>
            <div class='options_group show_if_price_range'>
                <?php
                woocommerce_wp_text_input( array(
                    'id' => '_wznd_giftcard_from_price',
                    'label' => esc_html__( 'Price Minimun', 'woo-smart-pack' ),
                    'style' => 'width:60%',
                    'desc_tip' => true,
                    'description' => esc_html__( 'Controls minimun price range for price range.', 'woo-smart-pack' ),
                    'type' => 'number',
                    'data_type' => 'price',
                    'placeholder' => '0.00',
                    'custom_attributes' => array(
                        'min' => '0',
                        'step' => '0.01',
                    )
                ) );
                woocommerce_wp_text_input( array(
                    'id' => '_wznd_giftcard_to_price',
                    'label' => esc_html__( 'Price Maximun', 'woo-smart-pack' ),
                    'style' => 'width:60%',
                    'desc_tip' => true,
                    'description' => esc_html__( 'Controls maximun price range for price range.', 'woo-smart-pack' ),
                    'type' => 'number',
                    'data_type' => 'price',
                    'placeholder' => '0.00',
                    'custom_attributes' => array(
                        'min' => '0',
                        'step' => '0.01',
                    )
                ) );
                ?>
            </div>
            <div class='options_group show_if_price_select'>
                <?php
                woocommerce_wp_textarea_input(
                        array(
                            'id' => '_wznd_giftcard_select_price',
                            'label' => esc_html__( 'Price Select Options', 'woo-smart-pack' ),
                            'style' => 'width:60%',
                            'placeholder' => '5|10|20',
                            'desc_tip' => true,
                            'description' => esc_html__( 'Controls price selection options.', 'woo-smart-pack' )
                        )
                );
                ?>
            </div>
            <div class="options_group">
                <?php
                woocommerce_wp_text_input( array(
                    'id' => '_wznd_giftcard_expiry_days',
                    'label' => esc_html__( 'Gift card validity (Days)', 'woo-smart-pack' ),
                    'style' => 'width:60%',
                    'default' => '7',
                    'desc_tip' => true,
                    'description' => esc_html__( 'Gift card validity period in days.', 'woo-smart-pack' ),
                    'type' => 'number',
                    'placeholder' => '07',
                    'custom_attributes' => array(
                        'min' => '1',
                        'step' => '1',
                    )
                ) );
                ?>
            </div>
            <div class='options_group'>
                <p class="form-field"><label><?php echo esc_html__( 'Products', 'woo-smart-pack' ); ?></label>
                    <select class="wc-product-search" multiple="multiple" style="width: 60%;" name="_wznd_giftcard_product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woo-smart-pack' ); ?>" data-action="woocommerce_json_search_products_and_variations">
                        <?php
                        $product_ids = get_post_meta( $post->ID, '_wznd_giftcard_product_ids', true );

                        if ( !is_array( $product_ids ) ) {

                            $product_ids = array();
                        }

                        foreach ( $product_ids as $product_id ) {

                            $product = wc_get_product( $product_id );

                            if ( is_object( $product ) ) {

                                echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                            }
                        }
                        ?>
                    </select> <?php echo wp_kses( wc_help_tip( esc_html__( 'Products which need to be in the cart to use this gift card or, for "Product Discounts", which products are discounted.', 'woo-smart-pack' ) ), $allowed_html ); ?></p>
                <?php ?>
                <p class="form-field"><label><?php echo esc_html__( 'Exclude Products', 'woo-smart-pack' ); ?></label>
                    <select class="wc-product-search" multiple="multiple" style="width: 60%;" name="_wznd_giftcard_exclude_product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woo-smart-pack' ); ?>" data-action="woocommerce_json_search_products_and_variations">
                        <?php
                        $product_ids = get_post_meta( $post->ID, '_wznd_giftcard_exclude_product_ids', true );

                        if ( !is_array( $product_ids ) ) {

                            $product_ids = array();
                        }

                        foreach ( $product_ids as $product_id ) {

                            $product = wc_get_product( $product_id );

                            if ( is_object( $product ) ) {

                                echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                            }
                        }
                        ?>
                    </select> <?php echo wp_kses( wc_help_tip( esc_html__( 'Products which must not be in the cart to use this gift card or, for "Product Discounts", which products are not discounted.', 'woo-smart-pack' ) ), $allowed_html ); ?></p>
            </div>
            <div class='options_group'>
                <p class="form-field"><label for="_wznd_giftcard_product_categoties"><?php echo esc_html__( 'Product categories', 'woo-smart-pack' ); ?></label>
                    <select id="product_categories" name="_wznd_giftcard_product_categoties[]" style="width: 60%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Any category', 'woo-smart-pack' ); ?>">
                        <?php
                        $category_ids = ( array ) get_post_meta( $post->ID, '_wznd_giftcard_product_categoties', true );

                        $category_args = array(
                            'taxonomy' => 'product_cat',
                            'orderby' => 'name',
                            'hide_empty' => false
                        );

                        $categories = get_terms( $category_args );

                        if ( $categories ) {

                            foreach ( $categories as $cat ) {

                                echo '<option value="' . esc_attr( $cat->term_id ) . '"' . selected( in_array( $cat->term_id, $category_ids ), true, false ) . '>' . esc_html( $cat->name ) . '</option>';
                            }
                        }
                        ?>
                    </select> <?php echo wp_kses( wc_help_tip( esc_html__( 'A product must be in this category for the gift card to remain valid or, for "Product Discounts", products in these categories will be discounted.', 'woo-smart-pack' ) ), $allowed_html ); ?>
                </p>
                <p class="form-field"><label for="_wznd_giftcard_exclude_categoties"><?php echo esc_html__( 'Exclude categories', 'woo-smart-pack' ); ?></label>
                    <select id="exclude_product_categories" name="_wznd_giftcard_exclude_categoties[]" style="width: 60%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'No categories', 'woo-smart-pack' ); ?>">
                        <?php
                        $category_ids = ( array ) get_post_meta( $post->ID, '_wznd_giftcard_exclude_categoties', true );

                        $category_args = array(
                            'taxonomy' => 'product_cat',
                            'orderby' => 'name',
                            'hide_empty' => false
                        );

                        $categories = get_terms( $category_args );

                        if ( $categories ) {

                            foreach ( $categories as $cat ) {

                                echo '<option value="' . esc_attr( $cat->term_id ) . '"' . selected( in_array( $cat->term_id, $category_ids ), true, false ) . '>' . esc_html( $cat->name ) . '</option>';
                            }
                        }
                        ?>
                    </select> <?php echo wp_kses( wc_help_tip( esc_html__( 'Product must not be in this category for the gift card to remain valid or, for "Product Discounts", products in these categories will not be discounted.', 'woo-smart-pack' ) ), $allowed_html ); ?>
                </p>
            </div>
            <div class='options_group'>
                <?php
                woocommerce_wp_checkbox(
                        array(
                            'id' => '_wznd_giftcard_allow_send_date',
                            'label' => esc_html__( 'Show Schedule Date', 'woo-smart-pack' ),
                            'description' => esc_html__( 'Allow Buyers to specify when to send their gift cards.', 'woo-smart-pack' )
                        ) );
                woocommerce_wp_text_input( array(
                    'id' => '_wznd_giftcard_coupon_pattern',
                    'label' => esc_html__( 'Gift Card Code Pattern', 'woo-smart-pack' ),
                    'style' => 'width:60%',
                    'data_type' => 'text',
                    'desc_tip' => true,
                    'description' => esc_html__( 'Code pattern to use when creating new gift card coupon, (e.g [A10], [Aa10], [a10], [C10], [Cc10], [c10], [N10])', 'woo-smart-pack' ),
                    'placeholder' => esc_html__( 'Use Default', 'woo-smart-pack' )
                ) );
                ?>
            </div>
            <div class='options_group'>
                <?php
                woocommerce_wp_select(
                        array(
                            'id' => '_wznd_giftcard_email_template',
                            'label' => esc_html__( 'Gift Card Email Template', 'woo-smart-pack' ),
                            'class' => 'wc-enhanced-select',
                            'style' => 'width:60%',
                            'options' => WooZnd_Util::GetPostTypeOption( 'wznd_giftcard', 'publish', array( '' => esc_html__( 'Default', 'woo-smart-pack' ) ) ),
                            'desc_tip' => true,
                            'description' => esc_html__( 'Gift card template to use when sending mail.', 'woo-smart-pack' )
                        )
                );

                woocommerce_wp_select(
                        array(
                            'id' => '_wznd_giftcard_template',
                            'label' => esc_html__( 'Gift Card PDF Template', 'woo-smart-pack' ),
                            'class' => 'wc-enhanced-select',
                            'style' => 'width:60%',
                            'options' => WooZnd_Util::GetPostTypeOption( 'wznd_giftcard' ),
                            'desc_tip' => true,
                            'description' => esc_html__( 'Gift card template to use when sending mail.', 'woo-smart-pack' )
                        )
                );
                ?>
            </div>
            <div class='options_group'><?php
                woocommerce_wp_select(
                        array(
                            'id' => '_wznd_giftcard_delivery',
                            'label' => esc_html__( 'Delivery Method', 'woo-smart-pack' ),
                            'class' => 'wc-enhanced-select',
                            'style' => 'width:60%',
                            'options' => [
                                '' => esc_html__( 'Select', 'woo-smart-pack' ),
                                WOOZND_GIFTCARD_DELIVERY_OFFLINE => esc_html__( 'Print & Send', 'woo-smart-pack' ),
                                WOOZND_GIFTCARD_DELIVERY_SHIP => esc_html__( 'Shipping', 'woo-smart-pack' ),
                                WOOZND_GIFTCARD_DELIVERY_EMAIL => esc_html__( 'Email Address', 'woo-smart-pack' )
                            ],
                            'desc_tip' => true,
                            'description' => esc_html__( 'Delivery method to use after creating a gift card.', 'woo-smart-pack' )
                        )
                );
                woocommerce_wp_checkbox( array(
                    'id' => '_wznd_giftcard_show_sender_name',
                    'label' => esc_html__( 'Buyer name field', 'woo-smart-pack' ),
                    'desc_tip' => true,
                    'description' => esc_html__( 'Allows buyers to specify their name', 'woo-smart-pack' ),
                ) );
                woocommerce_wp_checkbox( array(
                    'id' => '_wznd_giftcard_show_sender_email',
                    'label' => esc_html__( 'Buyer email field', 'woo-smart-pack' ),
                    'desc_tip' => true,
                    'description' => esc_html__( 'Allows buyers to specify their email', 'woo-smart-pack' ),
                ) );

                woocommerce_wp_checkbox( array(
                    'id' => '_wznd_giftcard_show_receiver_name',
                    'label' => esc_html__( 'Receiver name field', 'woo-smart-pack' ),
                    'desc_tip' => true,
                    'default' => 'yes',
                    'description' => esc_html__( "Allows buyers to specify receiver's email", "woo-smart-pack" ),
                ) );

                woocommerce_wp_checkbox( array(
                    'id' => '_wznd_giftcard_show_receiver_email',
                    'label' => esc_html__( 'Receiver email field', 'woo-smart-pack' ),
                    'desc_tip' => true,
                    'description' => esc_html__( "Allows buyers to specify receiver's email", "woo-smart-pack" ),
                ) );
                woocommerce_wp_checkbox( array(
                    'id' => '_wznd_giftcard_show_message',
                    'label' => esc_html__( 'Message field', 'woo-smart-pack' ),
                    'desc_tip' => true,
                    'description' => esc_html__( "Allows buyers to add gift card message", "woo-smart-pack" ),
                ) );
                ?></div>
        </div><?php
    }

}

add_action( 'woocommerce_process_product_meta', 'wooznd_save_giftcard_option_fields' );

if ( !function_exists( 'wooznd_save_giftcard_option_fields' ) ) {

    function wooznd_save_giftcard_option_fields( $post_id ) {

        $is_valid_nonce = ( isset( $_POST[ 'wznd_giftcard_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_giftcard_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;

        if ( !$is_valid_nonce ) {

            return;
        }

        $enable_gift_card = isset( $_POST[ '_wznd_enable_giftcard' ] ) ? 'yes' : 'no';

        update_post_meta( $post_id, '_wznd_enable_giftcard', $enable_gift_card );


        if ( isset( $_POST[ '_wznd_giftcard_discount_type' ] ) ) {

            update_post_meta( $post_id, '_wznd_giftcard_discount_type', sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_discount_type' ] ) ) );
        } else {

            delete_post_meta( $post_id, '_wznd_giftcard_discount_type' );
        }

        if ( isset( $_POST[ '_wznd_giftcard_price_type' ] ) ) {

            update_post_meta( $post_id, '_wznd_giftcard_price_type', sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_price_type' ] ) ) );
        } else {

            delete_post_meta( $post_id, '_wznd_giftcard_price_type' );
        }




        $wznd_giftcard_from_price = (isset( $_POST[ '_wznd_giftcard_from_price' ] ) && !empty( sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_from_price' ] ) ) ) ) ? sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_from_price' ] ) ) : 0;
        update_post_meta( $post_id, '_wznd_giftcard_from_price', $wznd_giftcard_from_price );

        $wznd_giftcard_to_price = (isset( $_POST[ '_wznd_giftcard_to_price' ] ) && !empty( sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_to_price' ] ) ) ) ) ? sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_to_price' ] ) ) : 9999999999;
        update_post_meta( $post_id, '_wznd_giftcard_to_price', $wznd_giftcard_to_price );

        $wznd_giftcard_select_price = (isset( $_POST[ '_wznd_giftcard_select_price' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_select_price' ] ) ) : '';
        update_post_meta( $post_id, '_wznd_giftcard_select_price', $wznd_giftcard_select_price );

        $wznd_giftcard_expiry_days = (isset( $_POST[ '_wznd_giftcard_expiry_days' ] ) && !empty( sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_expiry_days' ] ) ) ) ) ? sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_expiry_days' ] ) ) : 7;
        $wznd_giftcard_expiry_days = ($wznd_giftcard_expiry_days == 0 || $wznd_giftcard_expiry_days == '') ? 7 : $wznd_giftcard_expiry_days;

        update_post_meta( $post_id, '_wznd_giftcard_expiry_days', $wznd_giftcard_expiry_days );

        if ( isset( $_POST[ '_wznd_giftcard_product_ids' ] ) ) {

            update_post_meta( $post_id, '_wznd_giftcard_product_ids', wooznd_sanitize_giftcard_product_meta_data_ids( $_POST[ '_wznd_giftcard_product_ids' ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        } else {
            delete_post_meta( $post_id, '_wznd_giftcard_product_ids' );
        }


        if ( isset( $_POST[ '_wznd_giftcard_exclude_product_ids' ] ) ) {
            update_post_meta( $post_id, '_wznd_giftcard_exclude_product_ids', wooznd_sanitize_giftcard_product_meta_data_ids( $_POST[ '_wznd_giftcard_exclude_product_ids' ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        } else {
            delete_post_meta( $post_id, '_wznd_giftcard_exclude_product_ids' );
        }

        if ( isset( $_POST[ '_wznd_giftcard_product_categoties' ] ) ) {
            update_post_meta( $post_id, '_wznd_giftcard_product_categoties', wooznd_sanitize_giftcard_product_meta_data_ids( $_POST[ '_wznd_giftcard_product_categoties' ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        } else {
            delete_post_meta( $post_id, '_wznd_giftcard_product_categoties' );
        }

        if ( isset( $_POST[ '_wznd_giftcard_exclude_categoties' ] ) ) {
            update_post_meta( $post_id, '_wznd_giftcard_exclude_categoties', wooznd_sanitize_giftcard_product_meta_data_ids( $_POST[ '_wznd_giftcard_exclude_categoties' ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        } else {
            delete_post_meta( $post_id, '_wznd_giftcard_exclude_categoties' );
        }

        $show_gift_card_date = isset( $_POST[ '_wznd_giftcard_allow_send_date' ] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_wznd_giftcard_allow_send_date', $show_gift_card_date );

        if ( isset( $_POST[ '_wznd_giftcard_template' ] ) ) {
            update_post_meta( $post_id, '_wznd_giftcard_template', sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_template' ] ) ) );
        } else {
            delete_post_meta( $post_id, '_wznd_giftcard_template' );
        }

        if ( isset( $_POST[ '_wznd_giftcard_email_template' ] ) ) {
            update_post_meta( $post_id, '_wznd_giftcard_email_template', sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_email_template' ] ) ) );
        } else {
            delete_post_meta( $post_id, '_wznd_giftcard_email_template' );
        }

        if ( isset( $_POST[ '_wznd_giftcard_coupon_pattern' ] ) ) {
            update_post_meta( $post_id, '_wznd_giftcard_coupon_pattern', sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_coupon_pattern' ] ) ) );
        } else {
            delete_post_meta( $post_id, '_wznd_giftcard_coupon_pattern' );
        }


        if ( isset( $_POST[ '_wznd_giftcard_delivery' ] ) ) {
            update_post_meta( $post_id, '_wznd_giftcard_delivery', sanitize_text_field( wp_unslash( $_POST[ '_wznd_giftcard_delivery' ] ) ) );
        } else {
            delete_post_meta( $post_id, '_wznd_giftcard_delivery' );
        }

        $giftcard_show_sender_name = isset( $_POST[ '_wznd_giftcard_show_sender_name' ] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_wznd_giftcard_show_sender_name', $giftcard_show_sender_name );

        $giftcard_show_sender_email = isset( $_POST[ '_wznd_giftcard_show_sender_email' ] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_wznd_giftcard_show_sender_email', $giftcard_show_sender_email );

        $giftcard_show_receiver_name = isset( $_POST[ '_wznd_giftcard_show_receiver_name' ] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_wznd_giftcard_show_receiver_name', $giftcard_show_receiver_name );

        $giftcard_show_receiver_email = isset( $_POST[ '_wznd_giftcard_show_receiver_email' ] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_wznd_giftcard_show_receiver_email', $giftcard_show_receiver_email );

        $giftcard_show_message = isset( $_POST[ '_wznd_giftcard_show_message' ] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_wznd_giftcard_show_message', $giftcard_show_message );
    }

}

if ( !function_exists( 'wooznd_sanitize_giftcard_product_meta_data_ids' ) ) {

    function wooznd_sanitize_giftcard_product_meta_data_ids( $posted_ids ) {

        $ids = array();

        if ( !is_array( $posted_ids ) ) {

            return $ids;
        }

        foreach ( $posted_ids as $id_key => $id_value ) {

            $key = sanitize_key( wp_unslash( $id_key ) );

            $ids[ $key ] = sanitize_key( wp_unslash( $id_value ) );
        }

        return $ids;
    }

}
