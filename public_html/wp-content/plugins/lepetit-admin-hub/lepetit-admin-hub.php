<?php
/**
 * Plugin Name: Le Petit Kangaroo Admin Hub
 * Description: Provides a streamlined admin hub with login branding and quick access to WooCommerce orders, subscriptions, and wallet balances.
 * Version: 1.0.0
 * Author: OpenAI Assistant
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LPK_Admin_Hub {
    const MENU_SLUG    = 'lpk-admin-hub';
    const NONCE_ACTION = 'lpk_admin_hub_wallet_credit';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'login_enqueue_scripts', [ $this, 'customize_login_logo' ] );
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_head', [ $this, 'output_admin_styles' ] );
        add_action( 'admin_post_lpk_admin_hub_wallet_credit', [ $this, 'handle_wallet_credit' ] );
        add_action( 'admin_notices', [ $this, 'render_admin_notices' ] );
        add_shortcode( 'lpk_admin_hub', [ $this, 'render_shortcode' ] );
    }

    /**
     * Replace the default WordPress logo on the login screen.
     */
    public function customize_login_logo() {
        $logo_url = 'https://lepetitkangaroo.com/wp-content/uploads/2019/01/le-petit-kangaroo-files-1-01-1-scaled.png';
        ?>
        <style type="text/css">
            body.login div#login h1 a {
                background-image: url('<?php echo esc_url( $logo_url ); ?>');
                background-size: contain;
                width: 100%;
                height: 160px;
            }
        </style>
        <?php
    }

    /**
     * Register the Admin Hub menu item.
     */
    public function register_menu() {
        add_menu_page(
            __( 'Admin Hub', 'lepetit-admin-hub' ),
            __( 'Admin Hub', 'lepetit-admin-hub' ),
            'manage_woocommerce',
            self::MENU_SLUG,
            [ $this, 'render_page' ],
            'dashicons-admin-generic',
            3
        );
    }

    /**
     * Render custom admin styles when needed.
     */
    public function output_admin_styles() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || 'toplevel_page_' . self::MENU_SLUG !== $screen->id ) {
            return;
        }
        ?>
        <style>
            .lpk-admin-hub__wallet-form {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                align-items: center;
            }

            .lpk-admin-hub__wallet-form input[type="number"],
            .lpk-admin-hub__wallet-form input[type="text"] {
                max-width: 140px;
            }
        </style>
        <?php
    }

    /**
     * Render the Admin Hub page.
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'lepetit-admin-hub' ) );
        }

        $this->render_interface( 'admin' );
    }

    /**
     * Render the Admin Hub interface within content via shortcode.
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string
     */
    public function render_shortcode( $atts = [] ) {
        if ( ! is_user_logged_in() ) {
            $redirect = function_exists( 'get_queried_object_id' ) ? get_permalink( get_queried_object_id() ) : home_url();
            $login_url = wp_login_url( $redirect );

            return sprintf(
                '<p>%s</p>',
                wp_kses_post(
                    sprintf(
                        /* translators: %s: login URL */
                        __( 'You must <a href="%s">log in</a> to view the admin hub.', 'lepetit-admin-hub' ),
                        esc_url( $login_url )
                    )
                )
            );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return '<p>' . esc_html__( 'You do not have permission to view this content.', 'lepetit-admin-hub' ) . '</p>';
        }

        ob_start();

        $this->render_interface( 'frontend' );

        return ob_get_clean();
    }

    /**
     * Render the Admin Hub page.
     *
     * @param string $context Rendering context. Accepts "admin" or "frontend".
     */
    private function render_interface( $context = 'admin' ) {
        if ( 'frontend' === $context ) {
            $this->output_frontend_styles();
        }

        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'orders';
        $tabs       = [
            'orders'        => __( 'Orders', 'lepetit-admin-hub' ),
            'subscriptions' => __( 'Subscriptions', 'lepetit-admin-hub' ),
            'wallets'       => __( 'Wallets', 'lepetit-admin-hub' ),
        ];

        if ( ! array_key_exists( $active_tab, $tabs ) ) {
            $active_tab = 'orders';
        }
        ?>
        <div class="wrap lpk-admin-hub">
            <h1><?php esc_html_e( 'Admin Hub', 'lepetit-admin-hub' ); ?></h1>
            <h2 class="nav-tab-wrapper">
                <?php foreach ( $tabs as $key => $label ) : ?>
                    <?php $class = $key === $active_tab ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>
                    <a href="<?php echo esc_url( $this->get_tab_url( $key, $context ) ); ?>" class="<?php echo esc_attr( $class ); ?>">
                        <?php echo esc_html( $label ); ?>
                    </a>
                <?php endforeach; ?>
            </h2>
            <div class="lpk-admin-hub__content">
                <?php
                switch ( $active_tab ) {
                    case 'subscriptions':
                        $this->render_subscriptions_tab( $context );
                        break;
                    case 'wallets':
                        $this->render_wallets_tab( $context );
                        break;
                    case 'orders':
                    default:
                        $this->render_orders_tab( $context );
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render the orders tab.
     */
    private function render_orders_tab( $context ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
            echo '<p>' . esc_html__( 'WooCommerce is required to display orders.', 'lepetit-admin-hub' ) . '</p>';
            return;
        }

        $paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
        $per_page = 20;

        $order_query = wc_get_orders(
            [
                'limit'    => $per_page,
                'page'     => $paged,
                'orderby'  => 'date',
                'order'    => 'DESC',
                'paginate' => true,
            ]
        );

        $orders      = isset( $order_query->orders ) ? $order_query->orders : [];
        $total_pages = isset( $order_query->max_num_pages ) ? (int) $order_query->max_num_pages : 1;

        echo '<p>' . esc_html__( 'Recent WooCommerce orders.', 'lepetit-admin-hub' ) . '</p>';
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__( 'Order', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Date', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Status', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Customer', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Total', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Payment Method', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Actions', 'lepetit-admin-hub' ) . '</th>';
        echo '</tr></thead><tbody>';

        if ( $orders ) {
            foreach ( $orders as $order ) {
                $order_id    = $order->get_id();
                $edit_link   = admin_url( 'post.php?post=' . $order_id . '&action=edit' );
                $order_title = sprintf( '#%1$s', $order->get_order_number() );
                $date        = $order->get_date_created() ? $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) : '&mdash;';
                $status      = wc_get_order_status_name( $order->get_status() );
                $customer    = $order->get_formatted_billing_full_name();
                if ( empty( $customer ) ) {
                    $customer = $order->get_formatted_shipping_full_name();
                }
                $customer = $customer ? $customer : __( 'Guest', 'lepetit-admin-hub' );
                $total    = $order->get_formatted_order_total();
                $payment  = $order->get_payment_method_title();

                echo '<tr>';
                echo '<td><strong>' . esc_html( $order_title ) . '</strong></td>';
                echo '<td>' . wp_kses_post( $date ) . '</td>';
                echo '<td>' . esc_html( $status ) . '</td>';
                echo '<td>' . esc_html( $customer ) . '</td>';
                echo '<td>' . wp_kses_post( $total ) . '</td>';
                echo '<td>' . esc_html( $payment ) . '</td>';
                echo '<td><a class="button" href="' . esc_url( $edit_link ) . '">' . esc_html__( 'View', 'lepetit-admin-hub' ) . '</a></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="7">' . esc_html__( 'No orders found.', 'lepetit-admin-hub' ) . '</td></tr>';
        }

        echo '</tbody></table>';

        $this->render_pagination( $paged, $total_pages, 'orders', $context );
    }

    /**
     * Render the subscriptions tab.
     */
    private function render_subscriptions_tab( $context ) {
        if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Subscriptions_Admin' ) || ! method_exists( 'WC_Subscriptions_Admin', 'subscriptions_management_page' ) ) {
            echo '<p>' . esc_html__( 'WooCommerce Subscriptions is required to display subscriptions.', 'lepetit-admin-hub' ) . '</p>';
            return;
        }

        if ( ! class_exists( 'WP_List_Table' ) && file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        }

        $markup = $this->get_wc_subscriptions_table_markup();

        if ( ! empty( $markup ) ) {
            echo '<p>' . esc_html__( 'Manage WooCommerce subscriptions using the native table below.', 'lepetit-admin-hub' ) . '</p>';
            echo '<div class="lpk-admin-hub__subscriptions-table">';
            echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup generated by WooCommerce Subscriptions.
            echo '</div>';

            return;
        }

        echo '<p>' . esc_html__( 'Unable to load the WooCommerce Subscriptions table at this time. Displaying a simplified view below.', 'lepetit-admin-hub' ) . '</p>';

        $this->render_custom_subscriptions_table( $context );
    }

    /**
     * Retrieve the WooCommerce Subscriptions management markup and adapt it for the hub.
     *
     * @return string
     */
    private function get_wc_subscriptions_table_markup() {
        if ( ! class_exists( 'WC_Subscriptions_Admin' ) || ! method_exists( 'WC_Subscriptions_Admin', 'subscriptions_management_page' ) ) {
            return '';
        }

        $original_get     = $_GET;
        $original_request = $_REQUEST;

        $_GET     = array_merge( $original_get, [ 'page' => 'subscriptions' ] );
        $_REQUEST = array_merge( $original_request, [ 'page' => 'subscriptions' ] );

        ob_start();

        try {
            WC_Subscriptions_Admin::subscriptions_management_page();
        } catch ( \Throwable $error ) {
            ob_end_clean();
            $_GET     = $original_get;
            $_REQUEST = $original_request;

            $this->add_notice( sprintf( __( 'Subscriptions table error: %s', 'lepetit-admin-hub' ), $error->getMessage() ), 'error' );

            return '';
        }

        $output = ob_get_clean();

        $_GET     = $original_get;
        $_REQUEST = $original_request;

        return $this->adjust_subscription_management_markup( $output );
    }

    /**
     * Render a simplified subscriptions table when the native markup cannot be retrieved.
     *
     * @param string $context Rendering context.
     */
    private function render_custom_subscriptions_table( $context ) {
        if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_orders' ) ) {
            echo '<p>' . esc_html__( 'WooCommerce is required to display subscriptions.', 'lepetit-admin-hub' ) . '</p>';
            return;
        }

        if ( ! function_exists( 'wcs_get_subscription_statuses' ) ) {
            echo '<p>' . esc_html__( 'WooCommerce Subscriptions is required to display subscriptions.', 'lepetit-admin-hub' ) . '</p>';
            return;
        }

        if ( ! class_exists( 'WC_Subscription' ) ) {
            echo '<p>' . esc_html__( 'The subscription class could not be loaded.', 'lepetit-admin-hub' ) . '</p>';
            return;
        }

        $paged    = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
        $per_page = 20;

        $query = wc_get_orders(
            [
                'type'      => 'shop_subscription',
                'status'    => array_keys( wcs_get_subscription_statuses() ),
                'limit'     => $per_page,
                'page'      => $paged,
                'paginate'  => true,
                'orderby'   => 'date',
                'order'     => 'DESC',
                'return'    => 'objects',
            ]
        );

        $subscriptions = [];
        $total         = 0;
        $total_pages   = 1;

        if ( is_object( $query ) && isset( $query->orders ) ) {
            $subscriptions = $query->orders;
            $total         = isset( $query->total ) ? absint( $query->total ) : count( $subscriptions );
            $total_pages   = isset( $query->max_num_pages ) ? max( 1, absint( $query->max_num_pages ) ) : max( 1, (int) ceil( $total / $per_page ) );
        } elseif ( is_array( $query ) ) {
            $subscriptions = $query;
            $total         = count( $subscriptions );
            $total_pages   = max( 1, (int) ceil( $total / $per_page ) );
        }

        echo '<div class="lpk-admin-hub__subscriptions-table lpk-admin-hub__subscriptions-table--fallback">';

        if ( empty( $subscriptions ) ) {
            echo '<p>' . esc_html__( 'No subscriptions found.', 'lepetit-admin-hub' ) . '</p>';
            echo '</div>';
            return;
        }

        $columns = [
            'status'          => esc_html__( 'Status', 'lepetit-admin-hub' ),
            'subscription'    => esc_html__( 'Subscription', 'lepetit-admin-hub' ),
            'items'           => esc_html__( 'Items', 'lepetit-admin-hub' ),
            'start_date'      => esc_html__( 'Start Date', 'lepetit-admin-hub' ),
            'trial_end'       => esc_html__( 'Trial End', 'lepetit-admin-hub' ),
            'next_payment'    => esc_html__( 'Next Payment', 'lepetit-admin-hub' ),
            'last_payment'    => esc_html__( 'Last Payment', 'lepetit-admin-hub' ),
            'end_date'        => esc_html__( 'End Date', 'lepetit-admin-hub' ),
            'total'           => esc_html__( 'Total', 'lepetit-admin-hub' ),
            'recurring_total' => esc_html__( 'Recurring Total', 'lepetit-admin-hub' ),
            'actions'         => esc_html__( 'Actions', 'lepetit-admin-hub' ),
        ];

        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>';
        foreach ( $columns as $column_key => $label ) {
            $scope = in_array( $column_key, [ 'status', 'subscription' ], true ) ? 'scope="col"' : '';
            printf( '<th %1$s class="column-%2$s">%3$s</th>', $scope, esc_attr( $column_key ), esc_html( $label ) );
        }
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ( $subscriptions as $subscription ) {
            if ( ! $subscription instanceof WC_Subscription ) {
                $subscription = wcs_get_subscription( $subscription );
            }

            if ( ! $subscription instanceof WC_Subscription ) {
                continue;
            }

            echo '<tr>';
            echo '<td class="column-status">' . $this->get_subscription_status_badge( $subscription ) . '</td>';
            echo '<td class="column-subscription">' . $this->get_subscription_title_cell( $subscription ) . '</td>';
            echo '<td class="column-items">' . $this->get_subscription_items_summary( $subscription ) . '</td>';
            echo '<td class="column-start_date">' . $this->get_subscription_date_display( $subscription, 'start' ) . '</td>';
            echo '<td class="column-trial_end">' . $this->get_subscription_date_display( $subscription, 'trial_end' ) . '</td>';
            echo '<td class="column-next_payment">' . $this->get_subscription_date_display( $subscription, 'next_payment' ) . '</td>';
            echo '<td class="column-last_payment">' . $this->get_subscription_date_display( $subscription, 'last_payment' ) . '</td>';
            echo '<td class="column-end_date">' . $this->get_subscription_date_display( $subscription, 'end' ) . '</td>';
            echo '<td class="column-total">' . $this->get_subscription_total_display( $subscription ) . '</td>';
            echo '<td class="column-recurring_total">' . $this->get_subscription_recurring_total_display( $subscription ) . '</td>';
            echo '<td class="column-actions">' . $this->get_subscription_actions( $subscription ) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';

        $this->render_pagination( $paged, $total_pages, 'subscriptions', $context );
    }

    /**
     * Adjust the WooCommerce Subscriptions markup so that it posts back to the Admin Hub context.
     *
     * @param string $markup Raw markup from WooCommerce Subscriptions.
     *
     * @return string
     */
    private function adjust_subscription_management_markup( $markup ) {
        if ( empty( $markup ) || ! is_string( $markup ) ) {
            return '';
        }

        $page_slug = self::MENU_SLUG;

        $replacements = [
            'name="page" value="subscriptions"' => 'name="page" value="' . esc_attr( $page_slug ) . '"',
            "name='page' value='subscriptions'"   => 'name="page" value="' . esc_attr( $page_slug ) . '"',
        ];

        $markup = strtr( $markup, $replacements );

        $markup = preg_replace(
            '/(<input\s+type="hidden"\s+name="page"\s+value="' . preg_quote( $page_slug, '/' ) . '"\s*\/?>)/i',
            '$1' . sprintf( '<input type="hidden" name="tab" value="%s" />', esc_attr( 'subscriptions' ) ),
            $markup
        );

        $markup = str_replace( 'page=wc-orders--shop_subscription', 'page=' . $page_slug . '&amp;tab=subscriptions', $markup );

        $markup = preg_replace( '/^\s*<div class="wrap">\s*/i', '', $markup );
        $markup = preg_replace( '/\s*<\/div>\s*$/', '', $markup );

        return $markup;
    }

    /**
     * Generate a formatted status badge for a subscription.
     *
     * @param WC_Subscription $subscription Subscription instance.
     *
     * @return string
     */
    private function get_subscription_status_badge( $subscription ) {
        $status      = $subscription->get_status();
        $status_name = function_exists( 'wcs_get_subscription_status_name' ) ? wcs_get_subscription_status_name( $status ) : wc_get_order_status_name( $status );
        $classes     = [ 'lpk-admin-hub__status-badge', 'status-' . sanitize_html_class( $status ) ];

        return sprintf( '<span class="%1$s">%2$s</span>', esc_attr( implode( ' ', $classes ) ), esc_html( $status_name ) );
    }

    /**
     * Generate the subscription column markup.
     *
     * @param WC_Subscription $subscription Subscription instance.
     *
     * @return string
     */
    private function get_subscription_title_cell( $subscription ) {
        $subscription_number = '#' . $subscription->get_order_number();
        $edit_link           = get_edit_post_link( $subscription->get_id() );
        $customer_name       = $subscription->get_formatted_billing_full_name();

        $title = $edit_link ? '<a href="' . esc_url( $edit_link ) . '">' . esc_html( $subscription_number ) . '</a>' : esc_html( $subscription_number );

        if ( ! empty( $customer_name ) ) {
            $title .= '<br /><small>' . esc_html( $customer_name ) . '</small>';
        }

        return $title;
    }

    /**
     * Get a comma separated list of subscription items with quantities.
     *
     * @param WC_Subscription $subscription Subscription instance.
     *
     * @return string
     */
    private function get_subscription_items_summary( $subscription ) {
        $items = [];

        foreach ( $subscription->get_items() as $item ) {
            $name     = $item->get_name();
            $quantity = $item->get_quantity();
            $items[]  = trim( $quantity . ' × ' . $name );
        }

        if ( empty( $items ) ) {
            return '&ndash;';
        }

        return esc_html( implode( ', ', $items ) );
    }

    /**
     * Get a formatted subscription date for display.
     *
     * @param WC_Subscription $subscription Subscription instance.
     * @param string          $type         Date key.
     *
     * @return string
     */
    private function get_subscription_date_display( $subscription, $type ) {
        $date = method_exists( $subscription, 'get_date_to_display' ) ? $subscription->get_date_to_display( $type ) : $subscription->get_date( $type );

        if ( empty( $date ) ) {
            return '&ndash;';
        }

        return esc_html( $date );
    }

    /**
     * Format the initial total for a subscription.
     *
     * @param WC_Subscription $subscription Subscription instance.
     *
     * @return string
     */
    private function get_subscription_total_display( $subscription ) {
        $total = $subscription->get_formatted_order_total();

        return $total ? wp_kses_post( $total ) : '&ndash;';
    }

    /**
     * Format the recurring total string for a subscription.
     *
     * @param WC_Subscription $subscription Subscription instance.
     *
     * @return string
     */
    private function get_subscription_recurring_total_display( $subscription ) {
        if ( function_exists( 'wcs_price_string' ) ) {
            $details = [
                'currency'              => $subscription->get_currency(),
                'recurring_amount'      => (float) $subscription->get_total(),
                'subscription_period'   => $subscription->get_billing_period(),
                'subscription_interval' => $subscription->get_billing_interval(),
            ];

            $price_string = wcs_price_string( $details );

            if ( $subscription->get_sign_up_fee() > 0 ) {
                $price_string .= ' + ' . wc_price( $subscription->get_sign_up_fee(), [ 'currency' => $subscription->get_currency() ] );
            }

            return wp_kses_post( $price_string );
        }

        return $this->get_subscription_total_display( $subscription );
    }

    /**
     * Build the action links for a subscription row.
     *
     * @param WC_Subscription $subscription Subscription instance.
     *
     * @return string
     */
    private function get_subscription_actions( $subscription ) {
        $actions   = [];
        $edit_link = get_edit_post_link( $subscription->get_id() );

        if ( $edit_link ) {
            $actions[] = '<a href="' . esc_url( $edit_link ) . '" class="button button-small">' . esc_html__( 'Edit', 'lepetit-admin-hub' ) . '</a>';
        }

        $view_link = $subscription->get_view_order_url();

        if ( $view_link ) {
            $actions[] = '<a href="' . esc_url( $view_link ) . '" class="button button-small" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View', 'lepetit-admin-hub' ) . '</a>';
        }

        if ( empty( $actions ) ) {
            return '&ndash;';
        }

        return implode( ' ', $actions );
    }

    /**
     * Render the wallets tab.
     */
    private function render_wallets_tab( $context ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
            echo '<p>' . esc_html__( 'WooCommerce is required to display wallet balances.', 'lepetit-admin-hub' ) . '</p>';
            return;
        }

        if ( ! class_exists( 'WooZnd_WalletAccountDB' ) ) {
            echo '<p>' . esc_html__( 'The WooCommerce wallet extension is required to display wallet balances.', 'lepetit-admin-hub' ) . '</p>';
            return;
        }

        $paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
        $per_page = 20;
        $offset   = ( $paged - 1 ) * $per_page;

        $status_all = defined( 'WOOZND_WALLET_ACCOUNT_STATUS_NONE' ) ? WOOZND_WALLET_ACCOUNT_STATUS_NONE : 2;

        $accounts = WooZnd_WalletAccountDB::LoadAccounts( '%', $status_all, $offset, $per_page, 'name', 'ASC' );
        $total    = absint( WooZnd_WalletAccountDB::GetAccountsCount( '%', $status_all ) );

        $total_pages = $per_page > 0 ? max( 1, (int) ceil( $total / $per_page ) ) : 1;

        echo '<p>' . esc_html__( 'Wallet balances with quick credit controls.', 'lepetit-admin-hub' ) . '</p>';
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__( 'Account #', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'User', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Email', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Ledger Balance', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Current Balance', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Total Spent', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Last Activity', 'lepetit-admin-hub' ) . '</th>';
        echo '<th>' . esc_html__( 'Actions', 'lepetit-admin-hub' ) . '</th>';
        echo '</tr></thead><tbody>';

        if ( ! empty( $accounts ) ) {
            foreach ( $accounts as $account ) {
                $user_id = isset( $account['id'] ) ? absint( $account['id'] ) : 0;
                $user    = $user_id ? get_userdata( $user_id ) : false;

                $name_parts = [
                    isset( $account['first_name'] ) ? $account['first_name'] : '',
                    isset( $account['last_name'] ) ? $account['last_name'] : '',
                ];
                $display_name = trim( implode( ' ', array_filter( $name_parts ) ) );
                if ( '' === $display_name && $user ) {
                    $display_name = $user->display_name;
                }

                $email = isset( $account['email'] ) && '' !== $account['email'] ? $account['email'] : ( $user ? $user->user_email : '' );

                $ledger_balance  = isset( $account['ledger_balance'] ) ? floatval( $account['ledger_balance'] ) : 0.0;
                $current_balance = isset( $account['current_balance'] ) ? floatval( $account['current_balance'] ) : 0.0;
                $total_spent     = isset( $account['total_spent'] ) ? floatval( $account['total_spent'] ) : 0.0;

                $ledger_display  = function_exists( 'wc_price' ) ? wp_kses_post( wc_price( $ledger_balance ) ) : esc_html( number_format_i18n( $ledger_balance, 2 ) );
                $current_display = function_exists( 'wc_price' ) ? wp_kses_post( wc_price( $current_balance ) ) : esc_html( number_format_i18n( $current_balance, 2 ) );
                $spent_display   = function_exists( 'wc_price' ) ? wp_kses_post( wc_price( $total_spent ) ) : esc_html( number_format_i18n( $total_spent, 2 ) );

                $last_access = isset( $account['last_access'] ) ? $account['last_access'] : '';
                $last_activity = __( 'N/A', 'lepetit-admin-hub' );
                if ( $last_access && class_exists( 'WooZnd_Util' ) && method_exists( 'WooZnd_Util', 'MySQLTimeStampToDataTime' ) ) {
                    $formatted = WooZnd_Util::MySQLTimeStampToDataTime( $last_access, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), __( 'N/A', 'lepetit-admin-hub' ) );
                    $last_activity = $formatted ? $formatted : __( 'N/A', 'lepetit-admin-hub' );
                }

                $is_locked = ! empty( $account['locked'] );

                echo '<tr>';
                echo '<td>' . esc_html( isset( $account['account_number'] ) ? $account['account_number'] : '' ) . '</td>';
                echo '<td>' . esc_html( $display_name ) . '</td>';
                echo '<td>' . esc_html( $email ) . '</td>';
                echo '<td>' . $ledger_display . '</td>';
                echo '<td>' . $current_display . '</td>';
                echo '<td>' . $spent_display . '</td>';
                echo '<td>' . esc_html( $last_activity ) . '</td>';
                echo '<td>';
                if ( $is_locked ) {
                    echo '<span class="dashicons dashicons-lock" aria-hidden="true"></span> ' . esc_html__( 'Locked', 'lepetit-admin-hub' );
                } else {
                    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="lpk-admin-hub__wallet-form">';
                    wp_nonce_field( self::NONCE_ACTION );
                    echo '<input type="hidden" name="action" value="lpk_admin_hub_wallet_credit" />';
                    echo '<input type="hidden" name="user_id" value="' . esc_attr( $user_id ) . '" />';
                    echo '<label class="screen-reader-text" for="wallet-credit-' . esc_attr( $user_id ) . '">' . esc_html__( 'Amount to credit', 'lepetit-admin-hub' ) . '</label>';
                    echo '<input type="number" step="0.01" min="0" name="amount" id="wallet-credit-' . esc_attr( $user_id ) . '" placeholder="' . esc_attr__( 'Amount', 'lepetit-admin-hub' ) . '" required />';
                    echo '<input type="text" name="note" placeholder="' . esc_attr__( 'Note (optional)', 'lepetit-admin-hub' ) . '" />';
                    echo '<button type="submit" class="button button-secondary">' . esc_html__( 'Add Credit', 'lepetit-admin-hub' ) . '</button>';
                    echo '</form>';
                }
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="8">' . esc_html__( 'No wallets found.', 'lepetit-admin-hub' ) . '</td></tr>';
        }

        echo '</tbody></table>';

        $this->render_pagination( $paged, $total_pages, 'wallets', $context );
    }

    /**
     * Render simple pagination component.
     *
     * @param int    $paged       Current page.
     * @param int    $total_pages Total pages.
     * @param string $tab         Active tab key.
     */
    private function render_pagination( $paged, $total_pages, $tab, $context ) {
        if ( $total_pages <= 1 ) {
            return;
        }

        $base_url = $this->get_tab_url( $tab, $context );

        echo '<div class="tablenav"><div class="tablenav-pages">';
        echo paginate_links(
            [
                'base'      => add_query_arg( 'paged', '%#%', $base_url ),
                'format'    => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total'     => $total_pages,
                'current'   => $paged,
            ]
        );
        echo '</div></div>';
    }

    /**
     * Handle manual wallet credit submissions.
     */
    public function handle_wallet_credit() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'lepetit-admin-hub' ) );
        }

        check_admin_referer( self::NONCE_ACTION );

        $user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
        $amount  = isset( $_POST['amount'] ) ? wc_format_decimal( wp_unslash( $_POST['amount'] ) ) : 0;
        $note    = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : '';

        if ( $user_id <= 0 || $amount <= 0 ) {
            $this->add_notice( __( 'Please provide a valid amount to credit.', 'lepetit-admin-hub' ), 'error' );
            wp_safe_redirect( $this->get_wallet_tab_url() );
            exit;
        }

        if ( function_exists( 'woo_wallet' ) && method_exists( woo_wallet()->wallet, 'credit' ) ) {
            $transaction_data = [
                'currency' => get_woocommerce_currency(),
                'note'     => ! empty( $note ) ? $note : __( 'Manual credit via Admin Hub', 'lepetit-admin-hub' ),
            ];
            woo_wallet()->wallet->credit( $user_id, $amount, $transaction_data );
        } else {
            $current_balance = get_user_meta( $user_id, 'woo_wallet_balance', true );
            $current_balance = $current_balance ? floatval( $current_balance ) : 0;
            update_user_meta( $user_id, 'woo_wallet_balance', $current_balance + (float) $amount );
        }

        $this->add_notice( __( 'Wallet credited successfully.', 'lepetit-admin-hub' ), 'updated' );
        wp_safe_redirect( $this->get_wallet_tab_url() );
        exit;
    }

    /**
     * Store admin notices for later output.
     *
     * @param string $message Notice message.
     * @param string $type    Notice type.
     */
    private function add_notice( $message, $type = 'updated' ) {
        $notices   = get_transient( 'lpk_admin_hub_notices' );
        $notices   = is_array( $notices ) ? $notices : [];
        $notices[] = [
            'message' => $message,
            'type'    => $type,
        ];
        set_transient( 'lpk_admin_hub_notices', $notices, MINUTE_IN_SECONDS );
    }

    /**
     * Render queued admin notices.
     */
    public function render_admin_notices() {
        $notices = get_transient( 'lpk_admin_hub_notices' );
        if ( empty( $notices ) ) {
            return;
        }

        delete_transient( 'lpk_admin_hub_notices' );

        foreach ( $notices as $notice ) {
            $type    = isset( $notice['type'] ) ? $notice['type'] : 'updated';
            $message = isset( $notice['message'] ) ? $notice['message'] : '';

            if ( empty( $message ) ) {
                continue;
            }

            printf( '<div class="%1$s notice is-dismissible"><p>%2$s</p></div>', esc_attr( $type ), esc_html( $message ) );
        }
    }

    /**
     * Helper to get the wallet tab URL.
     *
     * @return string
     */
    private function get_wallet_tab_url() {
        return admin_url( 'admin.php?page=' . self::MENU_SLUG . '&tab=wallets' );
    }

    /**
     * Generate a URL for a specific tab based on context.
     *
     * @param string $tab     Tab slug.
     * @param string $context Rendering context.
     *
     * @return string
     */
    private function get_tab_url( $tab, $context ) {
        if ( 'frontend' === $context ) {
            $current_url = remove_query_arg( [ 'tab', 'paged' ] );
            if ( empty( $current_url ) ) {
                $current_url = function_exists( 'get_queried_object_id' ) ? get_permalink( get_queried_object_id() ) : home_url();
            }

            return add_query_arg( 'tab', $tab, $current_url );
        }

        return add_query_arg(
            [
                'page' => self::MENU_SLUG,
                'tab'  => $tab,
            ],
            admin_url( 'admin.php' )
        );
    }

    /**
     * Output lightweight styles when rendering on the front-end.
     */
    private function output_frontend_styles() {
        ?>
        <style>
            .lpk-admin-hub .nav-tab-wrapper {
                margin-bottom: 1rem;
                border-bottom: 1px solid #ccd0d4;
                padding-top: 0.5rem;
            }

            .lpk-admin-hub .nav-tab {
                display: inline-block;
                padding: 0.5rem 1rem;
                border: 1px solid #ccd0d4;
                border-bottom: none;
                background: #f6f7f7;
                color: inherit;
                text-decoration: none;
                margin-right: 4px;
                border-radius: 3px 3px 0 0;
            }

            .lpk-admin-hub .nav-tab.nav-tab-active {
                background: #fff;
                border-bottom: 1px solid #fff;
                font-weight: 600;
            }

            .lpk-admin-hub__content {
                background: #fff;
                padding: 1rem;
                border: 1px solid #ccd0d4;
            }

            .lpk-admin-hub table.widefat {
                width: 100%;
                border-collapse: collapse;
            }

            .lpk-admin-hub table.widefat th,
            .lpk-admin-hub table.widefat td {
                border: 1px solid #ccd0d4;
                padding: 8px;
                text-align: left;
            }

            .lpk-admin-hub .tablenav {
                margin-top: 1rem;
            }

            .lpk-admin-hub__subscriptions-table--fallback .button.button-small {
                margin-right: 4px;
            }

            .lpk-admin-hub__status-badge {
                display: inline-block;
                padding: 0.2em 0.6em;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 600;
                text-transform: capitalize;
                background: #f3f4f6;
                color: #1f2933;
            }

            .lpk-admin-hub__status-badge.status-active {
                background: #c6f6d5;
                color: #22543d;
            }

            .lpk-admin-hub__status-badge.status-on-hold,
            .lpk-admin-hub__status-badge.status-pending {
                background: #fefcbf;
                color: #744210;
            }

            .lpk-admin-hub__status-badge.status-cancelled,
            .lpk-admin-hub__status-badge.status-expired,
            .lpk-admin-hub__status-badge.status-pending-cancel {
                background: #fed7d7;
                color: #742a2a;
            }
        </style>
        <?php
    }
}

new LPK_Admin_Hub();
