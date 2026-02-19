<?php
if ( !defined( 'ABSPATH' ) ) {
   
    exit;
}

add_filter( 'woocommerce_product_data_tabs', 'wooznd_reward_product_tabs' );

if ( !function_exists( 'wooznd_reward_product_tabs' ) ) {

    function wooznd_reward_product_tabs( $tabs ) {

        if ( WooZnd_Util::GetOption( 'enable_purchase_reward', 'yes' ) == 'no' ) {

            return $tabs;
        }

        $tabs[ 'wznd_reward' ] = array(
            'label' => esc_html__( 'Purchase Reward', 'woo-smart-pack' ),
            'target' => 'wznd_reward_options',
            'class' => array( 'show_if_simple', 'show_if_variable' ),
        );

        return $tabs;
    }

}

add_action( 'woocommerce_product_data_panels', 'wooznd_reward_options_product_tab_content', 99 );

if ( !function_exists( 'wooznd_reward_options_product_tab_content' ) ) {

    function wooznd_reward_options_product_tab_content() {

        global $post;

        if ( WooZnd_Util::GetOption( 'enable_purchase_reward', 'yes' ) == 'no' ) {

            return;
        }
        ?><div id='wznd_reward_options' class='panel woocommerce_options_panel'><div class="options_group show_if_simple show_if_variable"><?php
                wp_nonce_field( basename( __FILE__ ), 'wznd_reward_nonce' );
                global $woocommerce, $post;

                woocommerce_wp_checkbox( array(
                    'id' => '_wznd_enable_reward',
                    'label' => esc_html__( 'Enable Purchase Reward', 'woo-smart-pack' ),
                    'desc_tip' => true,
                    'description' => esc_html__( 'Allow customers wallet to be rewarded with credit.', 'woo-smart-pack' ),
                ) );

                woocommerce_wp_select(
                        array(
                            'id' => '_wznd_reward_credit_type',
                            'label' => esc_html__( 'Amount Type', 'woo-smart-pack' ),
                            'class' => 'wc-enhanced-select',
                            'style' => 'width:80%',
                            'options' => array(
                                'fixed' => esc_html__( 'Fixed Value', 'woo-smart-pack' ),
                                'fixed-unit' => esc_html__( 'Fixed Value (per unit)', 'woo-smart-pack' ),
                                'percent' => esc_html__( 'Percentage %', 'woo-smart-pack' ),
                                'percent-unit' => esc_html__( 'Percentage % ( per unit)', 'woo-smart-pack' )
                            ),
                            'desc_tip' => true,
                            'description' => esc_html__( 'Controls the reward amount type.', 'woo-smart-pack' )
                        )
                );

                woocommerce_wp_text_input(
                        array(
                            'id' => '_wznd_reward_credit_amount',
                            'label' => esc_html__( 'Reward Amount', 'woo-smart-pack' ),
                            'type' => 'number',
                            'placeholder' => esc_html__( 'Enter amount', 'woo-smart-pack' ),
                            'desc_tip' => 'true',
                            'default' => '0',
                            'custom_attributes' => array(
                                'min' => '0',
                                'step' => '0.05'
                            ),
                            'description' => esc_html__( 'Purchase reward amount for this product.', 'woo-smart-pack' )
                        )
                );

                woocommerce_wp_text_input(
                        array(
                            'id' => '_wznd_reward_credit_remark',
                            'label' => esc_html__( 'Reward Remark', 'woo-smart-pack' ),
                            'placeholder' => esc_html__( 'Purchase Reward', 'woo-smart-pack' ),
                            'desc_tip' => 'true',
                            'description' => esc_html__( 'Reward Transaction remark.', 'woo-smart-pack' )
                        )
                );

                woocommerce_wp_text_input(
                        array(
                            'id' => '_wznd_reward_credit_info',
                            'label' => esc_html__( 'Purchase Reward Info', 'woo-smart-pack' ),
                            'placeholder' => esc_html__( 'Enter Info', 'woo-smart-pack' ),
                            'desc_tip' => 'true',
                            'description' => esc_html__( 'Information about the purchase reward', 'woo-smart-pack' )
                        )
                );
                ?>
            </div>
        </div><?php
    }

}

add_action( 'woocommerce_process_product_meta', 'wooznd_save_reward_option_fields' );

if ( !function_exists( 'wooznd_save_reward_option_fields' ) ) {

    function wooznd_save_reward_option_fields( $post_id ) {
       
        if ( WooZnd_Util::GetOption( 'enable_purchase_reward', true ) == false ) {
        
            return;
        }

        $is_valid_nonce = ( isset( $_POST[ 'wznd_reward_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'wznd_reward_nonce' ] ) ), basename( __FILE__ ) ) ) ? true : false;
        
        if ( !$is_valid_nonce ) {
        
            return;
        }

        // Enable Customer Reward
        $credit_enable = isset( $_POST[ '_wznd_enable_reward' ] ) ? 'yes' : 'no';
        
        update_post_meta( $post_id, '_wznd_enable_reward', $credit_enable );

        
        // Reward Amount Type
        $amount_type_field = isset( $_POST[ '_wznd_reward_credit_type' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_wznd_reward_credit_type' ] ) ) : '';
        
        update_post_meta( $post_id, '_wznd_reward_credit_type', $amount_type_field );

        
        // Reward Amount
        $amount_field = isset( $_POST[ '_wznd_reward_credit_amount' ] ) ? sanitize_text_field( wp_unslash( $_POST[ '_wznd_reward_credit_amount' ] ) ) : '';
      
        if ( empty( $amount_field ) ) {
        
            update_post_meta( $post_id, '_wznd_reward_credit_amount', ( float ) $amount_field );
        } else {
            
            delete_post_meta( $post_id, '_wznd_reward_credit_amount' );
        }
        
        
        // Reward Remark
        $remark_field = isset( $_POST[ '_wznd_reward_credit_remark' ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ '_wznd_reward_credit_remark' ] ) ) : '';
        
        if ( !empty( $remark_field ) ) {
            
            update_post_meta( $post_id, '_wznd_reward_credit_remark', $remark_field );
        } else {
            
            delete_post_meta( $post_id, '_wznd_reward_credit_remark' );
        }

        
        // Reward Info
        $remark_info_field = isset( $_POST[ '_wznd_reward_credit_info' ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ '_wznd_reward_credit_info' ] ) ) : '';
      
        if ( !empty( $remark_info_field ) ) {
        
            update_post_meta( $post_id, '_wznd_reward_credit_info', $remark_info_field );
        } else {
            
            delete_post_meta( $post_id, '_wznd_reward_credit_info' );
        }
    }

}

