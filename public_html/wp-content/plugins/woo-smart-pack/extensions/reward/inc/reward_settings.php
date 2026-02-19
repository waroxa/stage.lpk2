<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'WooZnd_Reward_Settings' ) ) {

    class WooZnd_Reward_Settings {

        public static function init() {
            if ( is_admin() ) {
                //add settings tab
                add_filter( 'woocommerce_settings_tabs_array', array( new self(), 'settings_tabs_array' ), 50 );
                //show settings tab
                add_action( 'woocommerce_settings_tabs_wooznd_reward', array( new self(), 'show_settings_tab' ) );
                //save settings tab
                add_action( 'woocommerce_update_options_wooznd_reward', array( new self(), 'update_settings_tab' ) );
            }
        }

        public static function settings_tabs_array( $settings_tabs ) {
            $settings_tabs[ 'wooznd_reward' ] = esc_html__( 'Reward', 'woo-smart-pack' );
            return $settings_tabs;
        }

        public static function show_settings_tab() {
            woocommerce_admin_fields( self::get_settings() );
        }

        public static function update_settings_tab() {
            woocommerce_update_options( self::get_settings() );
        }

        private static function get_settings() {


            $settings = array(
                'wooznd_reward_section_title' => array(
                    'name' => esc_html__( 'Purchase Reward Settings', 'woo-smart-pack' ),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_reward_section_title'
                ),
                'wooznd_enable_purchase_reward' => array(
                    'title' => esc_html__( 'Enable/Disable', 'woo-smart-pack' ),
                    'desc' => esc_html__( 'Enable Purchase Reward', 'woo-smart-pack' ),
                    'type' => 'checkbox',
                    'default' => 'yes',
                    'id' => 'wooznd_enable_purchase_reward'
                ),
                'wooznd_purchase_reward_remark' => array(
                    'title' => esc_html__( 'Reward  transactions remark', 'woo-smart-pack' ),
                    'type' => "textarea",
                    'default' => 'Purchase Reward',
                    'custom_attributes' => array(
                        'cols' => '40',
                        'rows' => '5'
                    ),
                    'id' => 'wooznd_purchase_reward_remark'
                ),
                'wooznd_purchase_reward_payment_methods' => array(
                    'title' => esc_html__( 'Allowed payment methods', 'woo-smart-pack' ),
                    'desc' => esc_html__( 'Choose which payment method can be use for purchase reward, leave this field blank to support all payment methods', 'woo-smart-pack' ),
                    'desc_tip' => true,
                    'type' => 'multiselect',
                    'default' => '',
                    'class' => 'wc-enhanced-select',
                    'css' => 'min-width:300px;',
                    'options' => WooZnd_Util::GetPaymentMethodList(),
                    'custom_attributes' => array(
                        'data-placeholder' => esc_html__( 'Select payment methods&hellip;', 'woo-smart-pack' ),
                    ),
                    'id' => 'wooznd_purchase_reward_payment_methods',
                ),
                'wooznd_purchase_reward_payment_method_filter' => array(
                    'title' => esc_html__( 'Payment methods filter', 'woo-smart-pack' ),
                    'desc' => esc_html__( 'Limit users to the above list of payment methods', 'woo-smart-pack' ),
                    'type' => 'checkbox',
                    'default' => 'no',
                    'id' => 'wooznd_purchase_reward_payment_method_filter'
                ),
                'wooznd_purchase_reward_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_purchase_reward_end'
                ),
                //Refun Mail Option
                'wooznd_purchase_reward_mail_title' => array(
                    'name' => esc_html__( 'Reward Notification', 'woo-smart-pack' ),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_purchase_reward_mail_title'
                ),
                'wooznd_purchase_reward_subject' => array(
                    'name' => esc_html__( 'Email subject', 'woo-smart-pack' ),
                    'type' => "text",
                    'default' => esc_html__( 'Purchase reward', 'woo-smart-pack' ),
                    'placeholder' => esc_html__( 'Subject', 'woo-smart-pack' ),
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_purchase_reward_subject'
                ),
                'wooznd_purchase_reward_message' => array(
                    'name' => esc_html__( 'Email message', 'woo-smart-pack' ),
                    'type' => "textarea",
                    'default' => wp_kses_post( __( 'Hi [wznd_wallet_name], <br /> Your wallet has been credited with [wznd_trans_credit] for purchasing [wznd_product_link], your new wallet balance is [wznd_wallet_current].', 'woo-smart-pack' ) ),
                    'placeholder' => esc_html__( 'Message', 'woo-smart-pack' ),
                    'css' => 'min-width:350px; min-height:200px;',
                    'id' => 'wooznd_purchase_reward_message'
                ),
                'wooznd_reward_section_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_reward_section_end'
                )
            );
            return $settings;
        }

    }

    WooZnd_Reward_Settings::init();
}

