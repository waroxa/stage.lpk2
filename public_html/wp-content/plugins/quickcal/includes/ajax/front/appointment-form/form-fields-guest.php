<?php

	$email_required = get_option('booked_require_guest_email_address',false);
	$name_requirements = get_option('booked_registration_name_requirements',array('require_name'));
	$name_requirements = ( isset($name_requirements[0]) ? $name_requirements[0] : false );

?>

<?php
/**
 * The default form displays an introductory block labelled "Your Information".
 * This copy isn't needed for our booking flow, so the block is intentionally
 * omitted to keep the form concise.
 */
?>

<?php if ( $name_requirements == 'require_surname' ): ?>
	<div class="field">
		<input value="" placeholder="<?php esc_html_e('First Name','booked'); ?>..." type="text" class="textfield" name="guest_name" />
		<input value="" placeholder="<?php esc_html_e('Last Name','booked'); ?>..." type="text" class="textfield" name="guest_surname" />
	</div>
<?php else: ?>
	<div class="field">
		<?php if ( $email_required ): ?>
			<input value="" placeholder="<?php esc_html_e('Name','booked'); ?>..." type="text" class="textfield" name="guest_name" />
			<input value="" placeholder="<?php esc_html_e('Email Address','booked'); ?>..." type="email" class="textfield" name="guest_email" />
		<?php else: ?>
			<input value="" placeholder="<?php esc_html_e('Name','booked'); ?>..." type="text" class="large textfield" name="guest_name" />
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ( $email_required && $name_requirements == 'require_surname' ): ?>
	<div class="field">
		<input value="" placeholder="<?php esc_html_e('Email Address','booked'); ?>..." type="email" class="large textfield" name="guest_email" />
	</div>
<?php endif; ?>
