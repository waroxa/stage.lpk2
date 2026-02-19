<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class WooZnd_MyWallet_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
                'wooznd_mywallet', esc_html__( 'My Wallet', 'woo-smart-pack' ), array(
            'description' => esc_html__( 'Displays users wallet informations', 'woo-smart-pack' )
                )
        );
    }

    function form( $instance ) {

        $title = (isset( $instance[ 'title' ] )) ? $instance[ 'title' ] : '';
        $ledger_text = (isset( $instance[ 'ledger_text' ] )) ? $instance[ 'ledger_text' ] : '';
        $current_text = (isset( $instance[ 'current_text' ] )) ? $instance[ 'current_text' ] : '';
        $total_spent_text = (isset( $instance[ 'total_spent_text' ] )) ? $instance[ 'total_spent_text' ] : '';
        ?>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html__( 'Title', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'ledger_text' ) ); ?>"><?php echo esc_html__( 'Ledger Text', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'ledger_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ledger_text' ) ); ?>" value="<?php echo esc_attr( $ledger_text ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'current_text' ) ); ?>"><?php echo esc_html__( 'Current Text', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'current_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'current_text' ) ); ?>" value="<?php echo esc_attr( $current_text ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'total_spent_text' ) ); ?>"><?php echo esc_html__( 'Total Spent Text', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'total_spent_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'total_spent_text' ) ); ?>" value="<?php echo esc_attr( $total_spent_text ); ?>">
        </p>

        <?php
    }

    function update( $new_instance, $old_instance ) {

        $title = (isset( $new_instance[ 'title' ] )) ? $new_instance[ 'title' ] : '';
        $ledger_text = (isset( $new_instance[ 'ledger_text' ] )) ? $new_instance[ 'ledger_text' ] : '';
        $current_text = (isset( $new_instance[ 'current_text' ] )) ? $new_instance[ 'current_text' ] : '';
        $total_spent_text = (isset( $new_instance[ 'total_spent_text' ] )) ? $new_instance[ 'total_spent_text' ] : '';

        $instance = $old_instance;
        $instance[ 'title' ] = wp_strip_all_tags( $title );
        $instance[ 'ledger_text' ] = wp_strip_all_tags( $ledger_text );
        $instance[ 'current_text' ] = wp_strip_all_tags( $current_text );
        $instance[ 'total_spent_text' ] = wp_strip_all_tags( $total_spent_text );
        return $instance;
    }

    function widget( $args, $instance ) {
        $title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
        $ledger_text = (isset( $instance[ 'ledger_text' ] )) ? $instance[ 'ledger_text' ] : '';
        $current_text = (isset( $instance[ 'current_text' ] )) ? $instance[ 'current_text' ] : '';
        $total_spent_text = (isset( $instance[ 'total_spent_text' ] )) ? $instance[ 'total_spent_text' ] : '';



        extract( $args );
        
        echo esc_html( $before_widget );
        echo esc_html( $before_title . (!empty( $title ) ? $title : 'My Wallet') . $after_title );
        echo do_shortcode( '[wznd_mywallet ledger_text="' . $ledger_text . '" current_text="' . (!empty( $current_text ) ? $current_text : 'Balance:') . '" total_spent_text="' . $total_spent_text . '"]' );
        echo esc_html( $after_widget );
    }

}
