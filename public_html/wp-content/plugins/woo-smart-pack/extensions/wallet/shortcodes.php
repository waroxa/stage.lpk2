<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'wznd_mywallet', 'wooznd_my_wallet' );

function wooznd_my_wallet( $atts, $content = "" ) {
   
    if ( !is_user_logged_in() ) {
    
        return '';
    }
    
    if ( !WooZnd_WalletAccountDB::AccountExists( get_current_user_id() ) ) {
    
        return '';
    }
    
    ob_start();
    
    $wallet_title = isset( $atts[ 'title' ] ) ? $atts[ 'title' ] : '';
    $ledger_label = isset( $atts[ 'ledger_text' ] ) ? $atts[ 'ledger_text' ] : '';
    $current_label = isset( $atts[ 'current_text' ] ) ? $atts[ 'current_text' ] : '';
    $total_spent_label = isset( $atts[ 'total_spent_text' ] ) ? $atts[ 'total_spent_text' ] : '';
    
    include dirname( __FILE__ ) . '/front-end/pages/template/wallet.php';
    
    return ob_get_clean();
}

add_shortcode( 'wznd_deposit', 'wooznd_deposit' );

function wooznd_deposit( $atts, $content = "" ) {
    
    if ( !is_user_logged_in() ) {
    
        return '';
    }
    
    if ( !WooZnd_WalletAccountDB::AccountExists( get_current_user_id() ) ) {
    
        return '';
    }
    
    ob_start();
    
    $deposit_title = isset( $atts[ 'title' ] ) ? $atts[ 'title' ] : '';
    $placeholder = isset( $atts[ 'placeholder' ] ) ? $atts[ 'placeholder' ] : '';
    $button_text = isset( $atts[ 'button_text' ] ) ? $atts[ 'button_text' ] : '';

    include ('front-end/pages/template/deposit.php');
    
    return ob_get_clean();
}

add_shortcode( 'wznd_transactions', 'wooznd_transactions' );

function wooznd_transactions( $atts, $content = "" ) {
    
    if ( !is_user_logged_in() ) {
    
        return '';
    }
    
    if ( !WooZnd_WalletAccountDB::AccountExists( get_current_user_id() ) ) {
    
        return '';
    }
    
    ob_start();
    
    $transaction_title = isset( $atts[ 'title' ] ) ? $atts[ 'title' ] : '';
    $page_size = isset( $atts[ 'pagesize' ] ) ? $atts[ 'pagesize' ] : 5;
    
    include ('front-end/pages/template/transactions.php');
    
    return ob_get_clean();
}

add_shortcode( 'wznd_wallet_name', 'wooznd_wallet_name' );

function wooznd_wallet_name( $atts, $content = "" ) {
    
    global $wooznd_wallet;
    
    ob_start();
    
    if ( isset( $wooznd_wallet ) ) {
    
        $fullname = $wooznd_wallet[ 'first_name' ] . ' ' . $wooznd_wallet[ 'last_name' ];
        
        if ( trim( $fullname ) == '' ) {
        
            $fullname = get_user_by( 'id', $wooznd_wallet[ 'id' ] )->display_name;
        }
        
        echo esc_html( $fullname );
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_wallet_current', 'wooznd_wallet_current' );

function wooznd_wallet_current( $atts, $content = "" ) {
    
    global $wooznd_wallet;
    
    ob_start();
    
    if ( isset( $wooznd_wallet ) ) {
    
        echo wp_kses( wc_price( $wooznd_wallet[ 'current_balance' ] ), WooZnd_Init::get_instance()->get_allow_html() );
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_wallet_ledger', 'wooznd_wallet_ledger' );

function wooznd_wallet_ledger( $atts, $content = "" ) {
    
    global $wooznd_wallet;
    
    ob_start();
    
    if ( isset( $wooznd_wallet ) ) {
    
        echo wp_kses( wc_price( $wooznd_wallet[ 'ledger_balance' ] ), WooZnd_Init::get_instance()->get_allow_html() );
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_wallet_spent', 'wooznd_wallet_spent' );

function wooznd_wallet_spent( $atts, $content = "" ) {
    
    global $wooznd_wallet;
    
    ob_start();
    
    if ( isset( $wooznd_wallet ) ) {
    
        echo wp_kses( wc_price( $wooznd_wallet[ 'total_spent' ] ), WooZnd_Init::get_instance()->get_allow_html() );
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_wallet_number', 'wooznd_wallet_number' );

function wooznd_wallet_number( $atts, $content = "" ) {
    
    global $wooznd_wallet;
    
    ob_start();
    
    if ( isset( $wooznd_wallet ) ) {
    
        echo esc_html( $wooznd_wallet[ 'account_number' ] );
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_order_number', 'wooznd_order_number' );

function wooznd_order_number( $atts, $content = "" ) {
    
    global $wooznd_order;
    
    ob_start();
    
    if ( isset( $wooznd_order ) ) {
    
        echo '#' . esc_html( $wooznd_order->get_order_number() );
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_order_link', 'wooznd_order_link' );

function wooznd_order_link( $atts, $content = "" ) {
    
    global $wooznd_order;
    
    ob_start();
    
    if ( isset( $wooznd_order ) ) {
    
        echo '#<a href="' . esc_attr( $wooznd_order->get_view_order_url() ) . '">' . esc_html( $wooznd_order->get_order_number() ) . '</a>';
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_product_link', 'wooznd_product_link' );

function wooznd_product_link( $atts, $content = "" ) {

    global $product;

    ob_start();
    
    if ( isset( $product ) ) {
    
        echo '<a href="' . esc_attr($product->get_permalink()) . '">' . esc_html($product->get_title()) . '</a>';
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_trans_receipt', 'wooznd_trans_receipt' );

function wooznd_trans_receipt( $atts, $content = "" ) {
    
    global $wooznd_transaction;
    
    ob_start();
    
    if ( isset( $wooznd_transaction ) ) {
    
        echo esc_html( $wooznd_transaction[ 'receipt' ] );
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_trans_credit', 'wooznd_trans_credit' );

function wooznd_trans_credit( $atts, $content = "" ) {
    
    global $wooznd_transaction;
    
    ob_start();
    
    if ( isset( $wooznd_transaction ) ) {
    
        echo wp_kses( wc_price( $wooznd_transaction[ 'credit' ] ), WooZnd_Init::get_instance()->get_allow_html() );
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_trans_debit', 'wooznd_trans_debit' );

function wooznd_trans_debit( $atts, $content = "" ) {
    
    global $wooznd_transaction;
    
    ob_start();
    
    if ( isset( $wooznd_transaction ) ) {
    
        echo wp_kses( wc_price( $wooznd_transaction[ 'debit' ] ), WooZnd_Init::get_instance()->get_allow_html() );
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_trans_amount', 'wooznd_trans_credit_debit' );

function wooznd_trans_credit_debit( $atts, $content = "" ) {
    
    global $wooznd_transaction;
    
    ob_start();
    
    if ( isset( $wooznd_transaction ) ) {
    
        if ( $wooznd_transaction[ 'credit' ] > 0 ) {
        
            echo wp_kses( wc_price( $wooznd_transaction[ 'credit' ] ), WooZnd_Init::get_instance()->get_allow_html() );
        } else {
            
            echo wp_kses( wc_price( $wooznd_transaction[ 'debit' ] ), WooZnd_Init::get_instance()->get_allow_html() );
        }
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_trans_type', 'wooznd_trans_type' );

function wooznd_trans_type( $atts, $content = "" ) {
    
    global $wooznd_transaction;
    
    ob_start();
    
    if ( isset( $wooznd_transaction ) ) {
    
        echo esc_html(WooZnd_Wallet_Util::TransactionTypeString( $wooznd_transaction[ 'transaction_type' ], false ));
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_trans_status', 'wooznd_trans_status' );

function wooznd_trans_status( $atts, $content = "" ) {
    
    global $wooznd_transaction;
    
    ob_start();
    
    if ( isset( $wooznd_transaction ) ) {
    
        echo esc_html(WooZnd_Wallet_Util::TransactionStatusString( $wooznd_transaction[ 'status' ], false ));
    }
    
    return ob_get_clean();
}

add_shortcode( 'wznd_site_link', 'wooznd_site_link' );

function wooznd_site_link( $atts, $content = "" ) {
    
    ob_start();
    
    echo '<a href="' . esc_attr(get_bloginfo( 'url' )) . '">' . esc_html(get_bloginfo( 'name' )) . '</a>';
    
    return ob_get_clean();
}

function wooznd_page_globals() {
    
    global $wooznd_wallet;
    
    if ( isset( $wooznd_wallet ) ) {
    
        return;
    }

    if ( !is_user_logged_in() ) {
        
        return;
    }
    
    if ( !WooZnd_WalletAccountDB::AccountExists( get_current_user_id() ) ) {
    
        return;
    }
    
    $wooznd_wallet = WooZnd_WalletAccountDB::GetAccount( get_current_user_id() );
}

add_action( 'get_header', 'wooznd_page_globals' );