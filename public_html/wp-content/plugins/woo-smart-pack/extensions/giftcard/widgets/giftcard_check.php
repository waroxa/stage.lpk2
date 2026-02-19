<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class WooZnd_GiftCardChecker_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
                'wooznd_giftcard', esc_html__( 'Gift Card Checker', 'woo-smart-pack' ), array(
            'description' => esc_html__( 'Allows users to check their gift card balance', 'woo-smart-pack' )
                )
        );
    }

    public function form( $instance ) {

        $title = (isset( $instance[ 'title' ] )) ? $instance[ 'title' ] : '';
        $button_text = (isset( $instance[ 'button_text' ] )) ? $instance[ 'button_text' ] : '';
        $placeholder = (isset( $instance[ 'placeholder' ] )) ? $instance[ 'placeholder' ] : '';


        $amount_label = (isset( $instance[ 'amount_label' ] )) ? $instance[ 'amount_label' ] : '';
        $balance_label = (isset( $instance[ 'balance_label' ] )) ? $instance[ 'balance_label' ] : '';
        $sent_to_label = (isset( $instance[ 'sent_to_label' ] )) ? $instance[ 'sent_to_label' ] : '';
        $expiry_date_label = (isset( $instance[ 'expiry_date_label' ] )) ? $instance[ 'expiry_date_label' ] : '';
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
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'amount_label' ) ); ?>"><?php echo esc_html__( 'Amount Label', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'amount_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'amount_label' ) ); ?>" value="<?php echo esc_attr( $amount_label ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'balance_label' ) ); ?>"><?php echo esc_html__( 'Balance Label', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'balance_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'balance_label' ) ); ?>" value="<?php echo esc_attr( $balance_label ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'sent_to_label' ) ); ?>"><?php echo esc_html__( 'Sent To Label', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'sent_to_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sent_to_label' ) ); ?>" value="<?php echo esc_attr( $sent_to_label ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'expiry_date_label' ) ); ?>"><?php echo esc_html__( 'Expiry Date Label', 'woo-smart-pack' ); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'expiry_date_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'expiry_date_label' ) ); ?>" value="<?php echo esc_attr( $expiry_date_label ); ?>">
        </p>

        <?php
    }

    public function update( $new_instance, $old_instance ) {

        $title = (isset( $new_instance[ 'title' ] )) ? $new_instance[ 'title' ] : '';
        $button_text = (isset( $new_instance[ 'button_text' ] )) ? $new_instance[ 'button_text' ] : '';
        $placeholder = (isset( $new_instance[ 'placeholder' ] )) ? $new_instance[ 'placeholder' ] : '';

        $amount_label = (isset( $new_instance[ 'amount_label' ] )) ? $new_instance[ 'amount_label' ] : '';
        $balance_label = (isset( $new_instance[ 'balance_label' ] )) ? $new_instance[ 'balance_label' ] : '';
        $sent_to_label = (isset( $new_instance[ 'sent_to_label' ] )) ? $new_instance[ 'sent_to_label' ] : '';
        $expiry_date_label = (isset( $new_instance[ 'expiry_date_label' ] )) ? $new_instance[ 'expiry_date_label' ] : '';



        $instance = $old_instance;
        $instance[ 'title' ] = wp_strip_all_tags( $title );
        $instance[ 'button_text' ] = wp_strip_all_tags( $button_text );
        $instance[ 'placeholder' ] = wp_strip_all_tags( $placeholder );

        $instance[ 'amount_label' ] = wp_strip_all_tags( $amount_label );
        $instance[ 'balance_label' ] = wp_strip_all_tags( $balance_label );
        $instance[ 'sent_to_label' ] = wp_strip_all_tags( $sent_to_label );
        $instance[ 'expiry_date_label' ] = wp_strip_all_tags( $expiry_date_label );
    
        return $instance;
    }

    public function widget( $args, $instance ) {

        $title = (isset( $instance[ 'title' ] ) && !empty( $title )) ? esc_html( $instance[ 'title' ] ) : esc_html__( 'Gift Card Checker', 'woo-smart-pack' );
        $button_text = (isset( $instance[ 'button_text' ] )) ? esc_html( $instance[ 'button_text' ] ) : esc_html__( 'Check', 'woo-smart-pack' );
        $placeholder = (isset( $instance[ 'placeholder' ] )) ? esc_html( $instance[ 'placeholder' ] ) : esc_html__( 'Enter code', 'woo-smart-pack' );

        $amount_label = (isset( $instance[ 'amount_label' ] )) ? esc_html( $instance[ 'amount_label' ] ) : esc_html__( 'Amount:', 'woo-smart-pack' );
        $balance_label = (isset( $instance[ 'balance_label' ] )) ? esc_html( $instance[ 'balance_label' ] ) : esc_html__( 'Remaining balance:', 'woo-smart-pack' );
        $sent_to_label = (isset( $instance[ 'sent_to_label' ] )) ? esc_html( $instance[ 'sent_to_label' ] ) : esc_html__( 'Sent to:', 'woo-smart-pack' );
        $expiry_date_label = (isset( $instance[ 'expiry_date_label' ] )) ? esc_html( $instance[ 'expiry_date_label' ] ) : esc_html__( 'Expiry date:', 'woo-smart-pack' );


        extract( $args );

        echo wp_kses_post( $before_widget );

        echo wp_kses_post( $before_title . $title . $after_title );

        do_shortcode( '[wznd_giftcard_check inline="true" placeholder="' . $placeholder . '" button_text="' . $button_text . '" amount_label="' . $amount_label . '" balance_label="' . $balance_label . '" sent_to_label="' . $sent_to_label . '" expiry_date_label="' . $expiry_date_label . '"]' );

        echo wp_kses_post( $after_widget );
    }

}
