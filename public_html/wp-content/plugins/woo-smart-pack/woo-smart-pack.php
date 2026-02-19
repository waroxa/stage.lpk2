<?php

/*
 * Plugin Name: WooCommerce Smart Pack
 * Plugin URI: https://codecanyon.net/item/woocommerce-smart-pack-gift-card-wallet-refund-reward/20265145?ref=zendcrew
 * Description: WooCommerce Smart Pack is a woocommerce wallet, refund, reward and gift card plugin
 * Author: zendcrew
 * Author URI: https://codecanyon.net/user/zendcrew?ref=zendcrew
 * Text Domain: woo-smart-pack
 * Domain Path: /languages/
 * 
 * Requires at least: 4.0
 * Requires PHP: 5.6
 * WC requires at least: 3.0
 * 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Version: 1.5
 * Tested up to: 6.8
 * WC tested up to: 10.0
 * 
 * Requires Plugins: woocommerce
 * 
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !defined( 'WOOZND_MAIN_FILE' ) ) {

    define( 'WOOZND_MAIN_FILE', __FILE__ );
}

if ( !defined( 'WOOZND_ASSET_URL' ) ) {

    define( 'WOOZND_ASSET_URL', plugins_url( 'assets/', WOOZND_MAIN_FILE ) );
}


if ( !class_exists( 'WooZnd_Init' ) ) {

    class WooZnd_Init {

        private static $instance;

        public static function get_instance(): self {

            if ( !self::$instance ) {

                self::$instance = new self();
            }

            return self::$instance;
        }

        public function __construct() {

            add_action( 'plugins_loaded', array( $this, 'plugin_loaded' ), 1 );

            register_activation_hook( WOOZND_MAIN_FILE, array( $this, 'plugin_activate' ) );

            add_action( 'before_woocommerce_init', array( $this, 'before_woocommerce_init' ) );
            
            add_action( 'init', array( $this, 'load_textdomain' ) );
        }
        
        public function load_textdomain() {

            load_plugin_textdomain( 'woo-smart-pack', false, dirname( plugin_basename( WOOZND_MAIN_FILE ) ) . '/languages/' );
        }

        public function plugin_loaded() {

            if ( function_exists( 'WC' ) ) { // Check if WooCommerce is active
                
                $this->main();
            } else {

                add_action( 'admin_notices', array( $this, 'missing_notice' ) );
            }

        }

        public function plugin_activate() {

            if ( !function_exists( 'WC' ) ) { // Check if WooCommerce is active
                
                return;
            }

            include_once('inc/class-util.php');
            include_once('inc/paginator.php');
            include_once('extensions/extensions.php');

            wooznd_plugin_activate();
        }

        public function before_woocommerce_init() {

            // Adds support for HPOS
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {

                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WOOZND_MAIN_FILE, true );
            }
        }

        public function missing_notice() {

            echo '<div class="error"><p><strong>' . esc_html__( 'WooCommerce Smart Pack requires WooCommerce to be installed and activated.', 'woo-smart-pack' ) . '</strong></p></div>';
        }

        private function main() {

            include_once('inc/class-util.php');
            include_once('inc/paginator.php');
            include_once('extensions/extensions.php');

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
        }

        public function enqueue_admin_assets() {

            wp_enqueue_style( 'znd-admin-styles', WOOZND_ASSET_URL . 'css/styles.css', array(), '1.0', 'all' );
            wp_enqueue_style( 'znd-admin-popup-styles', WOOZND_ASSET_URL . 'css/popup.css', array(), '1.0', 'all' );
            wp_enqueue_style( 'jquery-ui', WOOZND_ASSET_URL . 'css/jquery-ui.min.css', array(), '1.0', 'all' );
            wp_enqueue_script( 'znd-admin-popup-script', WOOZND_ASSET_URL . 'js/jquery.popup.js', array( 'jquery', 'jquery-ui-datepicker' ), '1.0', true );
            wp_enqueue_script( 'znd-admin-custom-script', WOOZND_ASSET_URL . 'js/custom.js', array( 'znd-admin-popup-script' ), '1.0', true );
            wp_enqueue_style( 'select2' );
            wp_enqueue_style( 'wc-enhanced-select' );
        }

        public function enqueue_public_assets() {

            wp_enqueue_style( 'dashicons' );
            wp_enqueue_style( 'znd-frontend-styles', WOOZND_ASSET_URL . 'css/front-end.css', array(), '1.0', 'all' );
            wp_enqueue_style( 'jquery-ui', WOOZND_ASSET_URL . 'css/jquery-ui.min.css', array(), '1.0', 'all' );
            wp_enqueue_script( 'znd-front-custom-script', WOOZND_ASSET_URL . 'js/front-custom.js', array( 'jquery', 'jquery-ui-datepicker' ), '1.0', true );
        }

        public function get_allow_html() {

            $allowed_html = array(
                'br' => array(),
                'span' => array(
                    'id' => true,
                    'title' => true,
                    'class' => true,
                ),
                'a' => array(
                    'id' => true,
                    'href' => true,
                    'title' => true,
                    'class' => true,
                    'target' => true,
                ),
                'strong' => array(
                    'id' => true,
                    'title' => true,
                    'class' => true,
                ),
                'b' => array(
                    'id' => true,
                    'title' => true,
                    'class' => true,
                ),
                'i' => array(
                    'id' => true,
                    'title' => true,
                    'class' => true,
                ),
            );

            return $allowed_html;
        }

    }

    WooZnd_Init::get_instance();
}
