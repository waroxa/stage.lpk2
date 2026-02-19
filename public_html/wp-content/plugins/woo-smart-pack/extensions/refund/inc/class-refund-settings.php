<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WooZnd_Refund_Settings')) {

    class WooZnd_Refund_Settings {

        public static function init() {
            if (is_admin()) {
                //add settings tab
                add_filter('woocommerce_settings_tabs_array', array(new self(), 'settings_tabs_array'), 50);
                //show settings tab
                add_action('woocommerce_settings_tabs_wooznd_refund', array(new self(), 'show_settings_tab'));
                //save settings tab
                add_action('woocommerce_update_options_wooznd_refund', array(new self(), 'update_settings_tab'));

            }
        }

        public static function settings_tabs_array($settings_tabs) {
            $settings_tabs['wooznd_refund'] = esc_html__('Refund', 'woo-smart-pack');
            return $settings_tabs;
        }

        public static function show_settings_tab() {
            woocommerce_admin_fields(self::get_settings());
        }

        public static function update_settings_tab() {
            woocommerce_update_options(self::get_settings());
        }

        private static function get_settings() {

            $settings = array(
                
                'wooznd_refund_section_title' => array(
                    'name' => esc_html__('Orders Refund Settings', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_refund_section_title'
                ),
                'wooznd_enable_refund' => array(
                    'title' => esc_html__('Enable/Disable', 'woo-smart-pack'),
                    'desc' => esc_html__('Enable Orders Refund', 'woo-smart-pack'),
                    'type' => 'checkbox',
                    'default' => 'yes',
                    'id' => 'wooznd_enable_refund'
                ),
                
                'wooznd_purchase_refund_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_purchase_refund_end'
                ),
                //Refun Mail Option
                'wooznd_refund_mail_title' => array(
                    'name' => esc_html__('Refund Notification', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_refund_mail_title'
                ),
                'wooznd_refun_mail_subject' => array(
                    'name' => esc_html__('Email subject', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'Order Refund',
                    'placeholder' => esc_html__('Subject', 'woo-smart-pack'),
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_refund_subject'
                ),
                'wooznd_refun_mail_message' => array(
                    'name' => esc_html__('Email message', 'woo-smart-pack'),
                    'type' => "textarea",
                    'default' => 'Hi [wznd_wallet_name], <br /> Your wallet has been credited with [wznd_trans_amount] for the refund of order [wznd_order_link], your new wallet balance is [wznd_wallet_current].',
                    'placeholder' => esc_html__('Message', 'woo-smart-pack'),
                    'css' => 'min-width:350px; min-height:200px;',
                    'id' => 'wooznd_refund_message'
                ),
                
                'wooznd_reward_section_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_reward_section_end'
                )
            );
            return $settings;
        }

    }

    WooZnd_Refund_Settings::init();
}

