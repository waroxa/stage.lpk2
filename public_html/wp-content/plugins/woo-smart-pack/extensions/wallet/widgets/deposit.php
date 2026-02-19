<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class WooZnd_Deposit_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
                'wooznd_deposit', esc_html__( 'Wallet Deposit', 'woo-smart-pack' ), array(
            'description' => esc_html__( 'Allows users to deposit funds into their wallet', 'woo-smart-pack' )
                )
        );
    }

    function form( $instance ) {

        $title = (isset( $instance[ 'title' ] )) ? $instance[ 'title' ] : '';
        $button_text = (isset( $instance[ 'button_text' ] )) ? $instance[ 'button_text' ] : '';
        $placeholder = (isset( $instance[ 'placeholder' ] )) ? $instance[ 'placeholder' ] : '';
        ?>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html__( 'Title', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>"><?php echo esc_html__( 'Button Text', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" value="<?php echo esc_attr( $button_text ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>"><?php echo esc_html__( 'Placeholder', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'placeholder' ) ); ?>" value="<?php echo esc_attr( $placeholder ); ?>">
        </p>

        <?php
    }

    function update( $new_instance, $old_instance ) {

        $title = (isset( $new_instance[ 'title' ] )) ? $new_instance[ 'title' ] : '';
        $button_text = (isset( $new_instance[ 'button_text' ] )) ? $new_instance[ 'button_text' ] : '';
        $placeholder = (isset( $new_instance[ 'placeholder' ] )) ? $new_instance[ 'placeholder' ] : '';

        $instance = $old_instance;
        $instance[ 'title' ] = wp_strip_all_tags( $title );
        $instance[ 'button_text' ] = wp_strip_all_tags( $button_text );
        $instance[ 'placeholder' ] = wp_strip_all_tags( $placeholder );
        return $instance;
    }

    function widget( $args, $instance ) {

        $title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : 'Wallet Deposit';
        $button_text = (isset( $instance[ 'button_text' ] )) ? $instance[ 'button_text' ] : 'Deposit';
        $placeholder = (isset( $instance[ 'placeholder' ] )) ? $instance[ 'placeholder' ] : 'Enter Amount';



        extract( $args );
        echo esc_html( $before_widget );
        echo esc_html( $before_title . (!empty( $title ) ? $title : 'My Wallet') . $after_title );
        echo do_shortcode( '[wznd_deposit placeholder="' . $placeholder . '" button_text="' . $button_text . '"]' );
        echo esc_html( $after_widget );
    }

}
