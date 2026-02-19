<?php

namespace AutomateWoo;

use AutomateWoo\Fields\Checkbox;
use AutomateWoo\Fields\Coupon as Coupon_Field;
use AutomateWoo\Fields\Number;
use AutomateWoo\Fields\Text;
use function sanitize_email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Action_Create_Coupon class.
 */
class Action_Create_Coupon extends Action {

	/**
	 * Load admin details for the action.
	 */
	public function load_admin_details() {
		$this->title       = __( 'Create Coupon', 'automatewoo' );
		$this->group       = __( 'Coupon', 'automatewoo' );
		$this->description = __( 'Generate a new coupon by cloning an existing WooCommerce coupon template.', 'automatewoo' );
	}

	/**
	 * Load the action fields displayed in the workflow editor.
	 */
	public function load_fields() {
		$template_coupon = new Coupon_Field();
		$template_coupon->set_name( 'template_coupon' );
		$template_coupon->set_title( __( 'Template Coupon', 'automatewoo' ) );
		$template_coupon->set_description( __( 'Select the coupon that should be cloned when this action runs.', 'automatewoo' ) );
		$template_coupon->set_required();
		$this->add_field( $template_coupon );

		$prefix = new Text();
		$prefix->set_name( 'prefix' );
		$prefix->set_title( __( 'Code Prefix', 'automatewoo' ) );
		$prefix->set_description( __( "Leave blank to use the default prefix from AutomateWoo (aw-). Enter a single space to remove the prefix.", 'automatewoo' ) );
		$this->add_field( $prefix );

		$usage_limit = new Number();
		$usage_limit->set_name( 'usage_limit' );
		$usage_limit->set_title( __( 'Usage Limit', 'automatewoo' ) );
		$usage_limit->set_description( __( "Maximum number of times the coupon can be used. Leave blank to use the template coupon's limit.", 'automatewoo' ) );
		$usage_limit->set_min( 0 );
		$this->add_field( $usage_limit );

		$expires = new Number();
		$expires->set_name( 'expires' );
		$expires->set_title( __( 'Expires After (days)', 'automatewoo' ) );
		$expires->set_description( __( "Set how many days the coupon remains valid. Leave blank to inherit the expiry from the template coupon.", 'automatewoo' ) );
		$expires->set_min( 0 );
		$this->add_field( $expires );

		$restrict_email = new Checkbox();
		$restrict_email->set_name( 'restrict_to_customer_email' );
		$restrict_email->set_title( __( 'Restrict to workflow email', 'automatewoo' ) );
		$restrict_email->set_description( __( 'Restrict the generated coupon to the customer, guest, or order email available in the workflow data.', 'automatewoo' ) );
		$this->add_field( $restrict_email );

		$description = new Text();
		$description->set_name( 'description' );
		$description->set_title( __( 'Description', 'automatewoo' ) );
		$description->set_description( __( 'Optional description for the generated coupon. Supports workflow variables.', 'automatewoo' ) );
		$description->set_variable_validation();
		$this->add_field( $description );
	}

	/**
	 * Run the action and create the coupon.
	 */
	public function run() {
		$template_code = $this->get_option( 'template_coupon' );

		if ( ! $template_code ) {
			$this->workflow->log_action_error( $this, __( 'No template coupon was selected.', 'automatewoo' ) );
			return;
		}

		$coupon_generator = new Coupon_Generator();
		$coupon_generator->set_template_coupon_code( html_entity_decode( $template_code ) );

		if ( ! $coupon_generator->get_template_coupon_id() ) {
			$this->workflow->log_action_error( $this, __( 'The selected template coupon could not be found.', 'automatewoo' ) );
			return;
		}

		$prefix = $this->get_option( 'prefix', true );
		if ( $prefix !== null && $prefix !== '' ) {
			$coupon_generator->set_prefix( $prefix );
		}

		$template_coupon = null;
		$usage_limit     = $this->get_option( 'usage_limit' );
		if ( '' !== $usage_limit && null !== $usage_limit ) {
			$coupon_generator->set_usage_limit( $usage_limit );
		} else {
			$template_coupon = new \WC_Coupon( $coupon_generator->get_template_coupon_id() );
			$coupon_generator->set_usage_limit( $template_coupon->get_usage_limit() );
		}

		$expires = $this->get_option( 'expires' );
		if ( '' !== $expires && null !== $expires ) {
			$coupon_generator->set_expires( $expires );
		}

		$description = $this->get_option( 'description', true );
		if ( $description ) {
			$coupon_generator->set_description( $description );
		}

		if ( $this->get_option( 'restrict_to_customer_email' ) ) {
			$email = $this->get_restriction_email();
			if ( $email ) {
				$coupon_generator->set_email_restriction( $email );
			} else {
				$this->workflow->log_action_note( $this, __( 'No email address was available to restrict the coupon.', 'automatewoo' ) );
			}
		}

		if ( $this->workflow->is_test_mode() ) {
			$coupon_generator->set_suffix( '[test]' );
			$coupon_generator->set_description( __( 'AutomateWoo Test Coupon', 'automatewoo' ) );
		}

		$coupon_generator->set_code( $coupon_generator->generate_code() );

		$coupon = $coupon_generator->generate_coupon();

		if ( ! $coupon ) {
			$this->workflow->log_action_error( $this, __( 'Unable to create a coupon from the selected template.', 'automatewoo' ) );
			return;
		}

		if ( $this->workflow->is_test_mode() ) {
			$coupon->update_meta_data( '_is_aw_test_coupon', true );
			$coupon->save();
		}

		$this->workflow->log_action_note(
			$this,
			sprintf(
				/* translators: %s: coupon code */
				__( 'Created coupon %s.', 'automatewoo' ),
				$coupon->get_code()
			)
		);

		/**
		 * Fires after a coupon has been created via the Create Coupon action.
		 *
		 * @since 6.0.0
		 *
		 * @param \WC_Coupon                 $coupon  The newly generated coupon object.
		 * @param Action_Create_Coupon $action  The action instance.
		 */
		do_action( 'automatewoo/action/create_coupon/created', $coupon, $this );
	}

	/**
	 * Get an email from the workflow data layer that can be used to restrict the coupon.
	 *
	 * @return string
	 */
	protected function get_restriction_email() {
		$data_layer = $this->workflow->data_layer();

		if ( ! $data_layer ) {
			return '';
		}

		if ( $customer = $data_layer->get_customer() ) {
			$email = $customer->get_email();
			if ( $email ) {
				return $email;
			}
		}

		if ( $guest = $data_layer->get_guest() ) {
			$email = sanitize_email( $guest->get_email() );
			if ( $email ) {
				return $email;
			}
		}

		if ( $order = $data_layer->get_order() ) {
			$email = sanitize_email( $order->get_billing_email() );
			if ( $email ) {
				return $email;
			}
		}

		if ( $user = $data_layer->get_user() ) {
			$email = sanitize_email( is_object( $user ) && isset( $user->user_email ) ? $user->user_email : '' );
			if ( $email ) {
				return $email;
			}
		}

		return '';
	}
}
