<?php

if (!defined('ABSPATH')) {
    exit;
}


if (!class_exists('WooZnd_Wallet_Settings')) {

    class WooZnd_Wallet_Settings {

        public static function init() {
            
            if (is_admin()) {
               
                //add settings tab
                add_filter('woocommerce_settings_tabs_array', array(new self(), 'settings_tabs_array'), 50);
                
                //show settings tab
                add_action('woocommerce_settings_tabs_wooznd_wallet', array(new self(), 'show_settings_tab'));
                
                //save settings tab
                add_action('woocommerce_update_options_wooznd_wallet', array(new self(), 'update_settings_tab'));

            }
        }

        public static function settings_tabs_array($settings_tabs) {
            
            $settings_tabs['wooznd_wallet'] = esc_html__('Wallet', 'woo-smart-pack');
            
            return $settings_tabs;
        }

        public static function show_settings_tab() {
            woocommerce_admin_fields(self::get_settings());
        }

        public static function update_settings_tab() {

            $deposit_product_id = '';

            if ( isset( $_POST[ 'wooznd_deposit_product_id' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

                $deposit_product_id = sanitize_text_field( wp_unslash( $_POST[ 'wooznd_deposit_product_id' ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
            }

            if ( !empty( $deposit_product_id ) ) {

                WooZnd_Util::UpdateOption( 'deposit_product_id', $deposit_product_id );
            } else {

                delete_option( 'wooznd_deposit_product_id' );
            }

            woocommerce_update_options(self::get_settings());
        }

        private static function get_settings() {
            $args = array(
                'role' => 'administrator',
                'orderby' => 'meta_key=first_name', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'order' => 'ASC',
                'fields' => array('id', 'user_login')
            );

            $admin_users_db = (new WP_User_Query($args))->get_results();
            $admin_users = array();

            foreach ($admin_users_db as $user) {
                $admin_users[$user->id] = $user->user_login;
            }
            $deposit_p = array();

            $prod = WooZnd_Util::GetOption('deposit_product_id', 0);
            if ($prod > 0) {
                $deposit_p = array(
                    $prod => WooZnd_Util::GetFormattedProductName($prod, false)
                );
            }

            $d_prod = array(
                'name' => esc_html__('Deposit product', 'woo-smart-pack'),
                'type' => "select",
                'class' => 'wc-product-search',
                'desc' => esc_html__('Select a product to use for funds deposit', 'woo-smart-pack'),
                'desc_tip' => true,
                'placeholder' => esc_html__('Search for a product&hellip;', 'woo-smart-pack'),
                'custom_attributes' => array(
                    'data-placeholder' => esc_html__('Search for a product&hellip;', 'woo-smart-pack'),
                    'data-selected' => WooZnd_Util::GetFormattedProductName(WooZnd_Util::GetOption('deposit_product_id', 0), false),
                ),
                'options' => $deposit_p,
                'id' => 'wooznd_deposit_product_id'
            );
                     
            $settings = array(
                //---------------
                // Wallet Settings
                //---------------
                'wooznd_wallet_section_title' => array(
                    'name' => esc_html__('Wallet Settings', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_wallet_section_title'
                ),
                'wooznd_auto_create_new_wallet' => array(
                    'title' => esc_html__('Auto create wallet', 'woo-smart-pack'),
                    'desc' => esc_html__('Automatically create new wallet for users', 'woo-smart-pack'),
                    'desc_tip' => true,
                    'type' => 'checkbox',
                    'default' => 'yes',
                    'id' => 'wooznd_auto_create_new_wallet'
                ),
                'wooznd_wallet_account_number_start' => array(
                    'title' => esc_html__('Account number start from', 'woo-smart-pack'),
                    'type' => "text",
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_wallet_account_number_start'
                ),
                'wooznd_new_wallet_remark' => array(
                    'title' => esc_html__('New wallet remark', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'New Account',
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_new_wallet_remark'
                ),
                'wooznd_transactions_receipt_format' => array(
                    'title' => esc_html__('Receipt number format', 'woo-smart-pack'),
                    'type' => "text",
                    'desc' => esc_html__('You can use the following variables: {{account_number}} and {{transaction_id}}', 'woo-smart-pack'),
                    'desc_tip' => true,
                    'default' => 'TRX{{account_number}}{{transaction_id}}',
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_transactions_receipt_format'
                ),
                'wooznd_basicsettings_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_basicsettings_end'
                ),
                //Funds Deposit
                'wooznd_funds_deposit_title' => array(
                    'name' => esc_html__('Funds Deposit', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_funds_deposit_title'
                ),
                'wooznd_deposit_product_id' => $d_prod,
                'wooznd_make_deposit_on_order_status' => array(
                    'title' => esc_html__('Complete deposit on order status change', 'woo-smart-pack'),
                    'desc' => esc_html__('Choose when to make complete deposit order', 'woo-smart-pack'),
                    'desc_tip' => true,
                    'type' => 'select',
                    'default' => 'processing',
                    'class' => 'wc-enhanced-select',
                    'css' => 'min-width:300px;',
                    'options' => array(
                        'on-hold' => esc_html__('On-Hold', 'woo-smart-pack'),
                        'processing' => esc_html__('Processing', 'woo-smart-pack'),
                        'completed' => esc_html__('Completed', 'woo-smart-pack'),
                    ),
                    'id' => 'wooznd_make_deposit_on_order_status'
                ),
                'wooznd_wallet_deposit_remark' => array(
                    'title' => esc_html__('Deposit remark', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'Funds Deposit',
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_wallet_deposit_remark'
                ),
                'wooznd_deposit_payment_methods' => array(
                    'title' => esc_html__('Payment methods', 'woo-smart-pack'),
                    'desc' => esc_html__('Choose which payment method can be use for funds deposit, leave this field blank to support all payment methods', 'woo-smart-pack'),
                    'desc_tip' => true,
                    'type' => 'multiselect',
                    'default' => '',
                    'class' => 'wc-enhanced-select',
                    'css' => 'min-width:300px;',
                    'options' => WooZnd_Util::GetPaymentMethodList(),
                    'custom_attributes' => array(
                        'data-placeholder' => esc_html__( 'Select payment methods&hellip;', 'woo-smart-pack' ),
                    ),
                    'id' => 'wooznd_deposit_payment_methods',
                ),
                'wooznd_funds_deposit_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_funds_deposit_end'
                ),
                //New Wallets Reward
                'wooznd_partial_payment_title' => array(
                    'name' => esc_html__('Partial Payment', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_partial_payment_title'
                ),
                'wooznd_enable_wallet_partial_payment' => array(
                    'title' => esc_html__('Enable/Disable', 'woo-smart-pack'),
                    'desc' => esc_html__('Enable partial payment', 'woo-smart-pack'),
                    'desc_tip' => true,
                    'type' => 'checkbox',
                    'default' => 'no',
                    'id' => 'wooznd_enable_wallet_partial_payment'
                ),
                'wooznd_wallet_partial_payment_text' => array(
                    'title' => esc_html__('Cart text', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'Wallet Funds',
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_wallet_partial_payment_text'
                ),
                'wooznd_wallet_partial_payment_min' => array(
                    'title' => esc_html__('Wallet funds threshold', 'woo-smart-pack'),
                    'type' => "number",
                    'default' => '0',
                    'desc' => esc_html__('Controls the minimum wallet balance for partial payment to apply on cart', 'woo-smart-pack'),
                    'desc_tip' => true,
                    'css' => 'min-width:350px;',
                    'custom_attributes' => array(
                        'min' => '0',
                        'step' => '1'
                    ),
                    'id' => 'wooznd_wallet_partial_payment_min'
                ),
                'wooznd_make_partial_payment_on_order_status' => array(
                    'title' => esc_html__('Complete payment on order status change', 'woo-smart-pack'),
                    'desc' => esc_html__('Choose when to make complete partial payment order', 'woo-smart-pack'),
                    'desc_tip' => true,
                    'type' => 'select',
                    'default' => 'on-hold',
                    'class' => 'wc-enhanced-select',
                    'css' => 'min-width:300px;',
                    'options' => array(
                        'completed' => esc_html__('Completed', 'woo-smart-pack'),
                        'processing' => esc_html__('Processing', 'woo-smart-pack'),
                        'on-hold' => esc_html__('On-Hold', 'woo-smart-pack'),
                    ),
                    'id' => 'wooznd_make_partial_payment_on_order_status'
                ),
                'wooznd_wallet_partial_payment_remark' => array(
                    'title' => esc_html__('Payment remark', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'Partial Payment',
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_wallet_partial_payment_remark'
                ),
                'wooznd_show_wallet_partial_payment_box' => array(
                    'title' => esc_html__('Show partial payment box', 'woo-smart-pack'),
                    'desc' => esc_html__('Allows users to choose when to apply partial payment on their cart.', 'woo-smart-pack'),
                    'desc_tip' => true,
                    'type' => 'checkbox',
                    'default' => 'no',
                    'id' => 'wooznd_show_wallet_partial_payment_box'
                ),
                'wooznd_wallet_partial_payment_box_title' => array(
                    'title' => esc_html__('Partial payment box title', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'Partial Payment',
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_wallet_partial_payment_box_title'
                ),
                'wooznd_wallet_partial_payment_box_desc' => array(
                    'title' => esc_html__('Partial payment box description', 'woo-smart-pack'),
                    'type' => "textarea",
                    'default' => 'Use the {{funds}} available funds in my wallet',
                    'css' => 'min-width:350px;',
                    'custom_attributes' => array(
                        'cols' => '40',
                        'rows' => '2'
                    ),
                    'id' => 'wooznd_wallet_partial_payment_box_desc'
                ),
                'wooznd_wallet_partial_payment_box_label' => array(
                    'title' => esc_html__('Partial payment box checkbox text', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'Use my wallet funds',
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_wallet_partial_payment_box_label'
                ),
                'wooznd_partial_payment_methods' => array(
                    'title' => esc_html__('Allowed payment methods', 'woo-smart-pack'),
                    'desc' => esc_html__('Choose which payment method can be use with partial payment, leave this field blank to support all payment methods', 'woo-smart-pack'),
                    'desc_tip' => true,
                    'type' => 'multiselect',
                    'default' => '',
                    'class' => 'wc-enhanced-select',
                    'css' => 'min-width:300px;',
                    'options' => WooZnd_Util::GetPaymentMethodList(),
                    'custom_attributes' => array(
                        'data-placeholder' => esc_html__( 'Select payment methods&hellip;', 'woo-smart-pack' ),
                    ),
                    'id' => 'wooznd_partial_payment_methods',
                ),
                'wooznd_partial_payment_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_partial_payment_end'
                ),
                //New Wallets Reward
                'wooznd_new_wallet_title' => array(
                    'name' => esc_html__('New Wallet Reward', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_new_wallet_title'
                ),
                'wooznd_new_wallet_freecredit' => array(
                    'name' => esc_html__('Reward amount', 'woo-smart-pack'),
                    'type' => "number",
                    'default' => '0',
                    'desc' => esc_html__('Amount to credit every new wallet', 'woo-smart-pack'),
                    'desc_tip' => true,
                    'placeholder' => esc_html__('Reward amount', 'woo-smart-pack'),
                    'custom_attributes' => array(
                        'min' => '0',
                    ),
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_new_wallet_freecredit'
                ),
                'wooznd_new_wallet_freecredit_remark' => array(
                    'title' => esc_html__('Reward remark', 'woo-smart-pack'),
                    'type' => "textarea",
                    'default' => 'free',
                    'css' => 'min-width:350px;',
                    'custom_attributes' => array(
                        'cols' => '40',
                        'rows' => '2'
                    ),
                    'id' => 'wooznd_new_wallet_freecredit_remark'
                ),
                'wooznd_new_wallet_freecredit_status' => array(
                    'title' => esc_html__('Reward transaction status', 'woo-smart-pack'),
                    'type' => 'select',
                    'default' => WOOZND_WALLET_TRANSANCTION_STATUS_PENDING,
                    'class' => 'wc-enhanced-select',
                    'css' => 'min-width:300px;',
                    'options' => array(
                        WOOZND_WALLET_TRANSANCTION_STATUS_PENDING => WooZnd_Wallet_Util::TransactionStatusString(WOOZND_WALLET_TRANSANCTION_STATUS_PENDING, false),
                        WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD => WooZnd_Wallet_Util::TransactionStatusString(WOOZND_WALLET_TRANSANCTION_STATUS_ONHOLD, false),
                        WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING => WooZnd_Wallet_Util::TransactionStatusString(WOOZND_WALLET_TRANSANCTION_STATUS_PROCESSING, false),
                        WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED => WooZnd_Wallet_Util::TransactionStatusString(WOOZND_WALLET_TRANSANCTION_STATUS_COMPLETED, false),
                    ),
                    'id' => 'wooznd_new_wallet_freecredit_status'
                ),
                'wooznd_new_wallet_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_new_wallet_end'
                ),
                //Security
                'wooznd_security_title' => array(
                    'name' => esc_html__('Wallets Data Security', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_security_title'
                ),
                'wooznd_system_id' => array(
                    'title' => esc_html__('System user', 'woo-smart-pack'),
                    'type' => 'select',
                    'default' => '1',
                    'class' => 'wc-enhanced-select',
                    'css' => 'min-width:300px;',
                    'options' => $admin_users,
                    'id' => 'wooznd_system_id'
                ),
                'wooznd_encryption_key' => array(
                    'title' => esc_html__('Encryption key', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => WooZnd_Util::GetOption('encryption_key', ''),
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_encryption_key'
                ),
                'wooznd_encryption_key_vi' => array(
                    'title' => esc_html__('Encryption key VI', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => WooZnd_Util::GetOption('encryption_key_vi', ''),
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_encryption_key_vi'
                ),
                'wooznd_security_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_security_end'
                ),
                //New Wallet Mail
                'wooznd_new_wallet_mail_title' => array(
                    'name' => esc_html__('New Wallet Notification', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_purchase_reward_mail_title'
                ),
                'wooznd_new_wallet_mail_subject' => array(
                    'name' => esc_html__('Email subject', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'Your new wallet has been created',
                    'placeholder' => esc_html__('Subject', 'woo-smart-pack'),
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_new_wallet_mail_subject'
                ),
                'wooznd_new_wallet_mail_message' => array(
                    'name' => esc_html__('Email message', 'woo-smart-pack'),
                    'type' => "textarea",
                    'default' => 'Hi [wznd_wallet_name], <br /> Your new wallet has been created, you can deposit any amount into your wallet and later use this funds to purchase product & services on our website.',
                    'placeholder' => esc_html__('Message', 'woo-smart-pack'),
                    'css' => 'min-width:350px; min-height:200px;',
                    'id' => 'wooznd_new_wallet_mail_message'
                ),
                'wooznd_new_wallet_mail_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_new_wallet_mail_end'
                ),
                //New Wallet Reward
                'wooznd_wallet_reward_title' => array(
                    'name' => esc_html__('New Wallet Reward Notification', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_wallet_reward_title'
                ),
                'wooznd_new_wallet_reward_mail_subject' => array(
                    'name' => esc_html__('Email subject', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'New Wallet Reward',
                    'placeholder' => esc_html__('Subject', 'woo-smart-pack'),
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_new_wallet_reward_mail_subject'
                ),
                'wooznd_new_wallet_reward_mail_message' => array(
                    'name' => esc_html__('Email message', 'woo-smart-pack'),
                    'type' => "textarea",
                    'default' => 'Hi [wznd_wallet_name], <br /> Your new wallet has been credited with [wznd_trans_credit] as part of our on going promo, your new wallet balance is [wznd_wallet_current].',
                    'placeholder' => esc_html__('Message', 'woo-smart-pack'),
                    'css' => 'min-width:350px; min-height:200px;',
                    'id' => 'wooznd_new_wallet_reward_mail_message'
                ),
                'wooznd_new_wallet_reward_mail_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_new_wallet_reward_mail_end'
                ),
                //Wallet Deposit
                'wooznd_wallet_deposit_title' => array(
                    'name' => esc_html__('Funds Deposit Notification', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_wallet_reward_title'
                ),
                'wooznd_wallet_deposit_mail_subject' => array(
                    'name' => esc_html__('Email subject', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'New Funds Deposit',
                    'placeholder' => esc_html__('Subject', 'woo-smart-pack'),
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_wallet_deposit_mail_subject'
                ),
                'wooznd_wallet_deposit_mail_message' => array(
                    'name' => esc_html__('Email message', 'woo-smart-pack'),
                    'type' => "textarea",
                    'default' => 'Hi [wznd_wallet_name], <br /> Your wallet has been credited with [wznd_trans_credit] funds deposit, your new wallet balance is [wznd_wallet_current].',
                    'placeholder' => esc_html__('Message', 'woo-smart-pack'),
                    'css' => 'min-width:350px; min-height:200px;',
                    'id' => 'wooznd_wallet_deposit_mail_message'
                ),
                'wooznd_wallet_deposit_mail_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_wallet_deposit_mail_end'
                ),
                //Wallet Transaction
                'wooznd_wallet_transaction_title' => array(
                    'name' => esc_html__('Transactions Notification', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_wallet_transaction_title'
                ),
                'wooznd_wallet_transactions_mail_subject' => array(
                    'name' => esc_html__('Email subject', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'New Transactions: [wznd_trans_receipt]',
                    'placeholder' => esc_html__('Subject', 'woo-smart-pack'),
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_wallet_transactions_mail_subject'
                ),
                'wooznd_wallet_transactions_mail_message' => array(
                    'name' => esc_html__('Email message', 'woo-smart-pack'),
                    'type' => "textarea",
                    'default' => 'Hi [wznd_wallet_name], <br /> A [wznd_trans_type] transaction ([wznd_trans_receipt]) has occured on your wallet, your new wallet balance is [wznd_wallet_current].',
                    'placeholder' => esc_html__('Message', 'woo-smart-pack'),
                    'css' => 'min-width:350px; min-height:200px;',
                    'id' => 'wooznd_wallet_transactions_mail_message'
                ),
                'wooznd_wallet_transactions_mail_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_wallet_transactions_mail_end'
                ),
                //Wallet Transaction Status
                'wooznd_wallet_transaction_status_title' => array(
                    'name' => esc_html__('Transactions Status Notification', 'woo-smart-pack'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'wooznd_wallet_transaction_status_title'
                ),
                'wooznd_wallet_transactions_status_mail_subject' => array(
                    'name' => esc_html__('Email subject', 'woo-smart-pack'),
                    'type' => "text",
                    'default' => 'Transactions [wznd_trans_receipt] status',
                    'placeholder' => esc_html__('Subject', 'woo-smart-pack'),
                    'css' => 'min-width:350px;',
                    'id' => 'wooznd_wallet_transactions_status_mail_subject'
                ),
                'wooznd_wallet_transactions_status_mail_message' => array(
                    'name' => esc_html__('Email message', 'woo-smart-pack'),
                    'type' => "textarea",
                    'default' => 'Hi [wznd_wallet_name], <br /> A [wznd_trans_type] transaction ([wznd_trans_receipt]) is now [wznd_trans_status], your new wallet balance is [wznd_wallet_current].',
                    'placeholder' => esc_html__('Message', 'woo-smart-pack'),
                    'css' => 'min-width:350px; min-height:200px;',
                    'id' => 'wooznd_wallet_transactions_status_mail_message'
                ),
                'wooznd_wallet_section_end' => array(
                    'type' => 'sectionend',
                    'id' => 'wooznd_wallet_section_end'
                )
            );
            return $settings;
        }

    }

    WooZnd_Wallet_Settings::init();
}

