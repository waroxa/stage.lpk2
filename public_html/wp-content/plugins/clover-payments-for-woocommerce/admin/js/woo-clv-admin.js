/* global cloverAdminVars */

jQuery( function ( $ ) {
	'use strict';

	const $environmentSelect = $( '#woocommerce_clover_payments_environment' );
	const $sandboxFields = $( '.clvsdfields' ).closest( 'tr' );
	const $productionFields = $( '.clvfields' ).closest( 'tr' );
	const $paymentMethodDescription = $('h2.wc-admin-header + p');

	const $requiredText = $( '<span>', {
		'class': 'clover-required',
		'html': '<strong>*</strong>' + cloverAdminVars.textRequired
	} );

	$paymentMethodDescription
		.append( '<br>' )
		.append( ' ' + cloverAdminVars.textLearnMore )
		.append( '<br><br>' )
		.append( $requiredText );

	function toggleEnvironmentFields( environment ) {
		const isSandbox = ( environment === 'sandbox' );
		$sandboxFields.toggle( isSandbox );
		$productionFields.toggle( ! isSandbox );
	}

	$environmentSelect.on( 'change', function () {
		toggleEnvironmentFields( this.value );
	} ).trigger( 'change' );

	$( document ).on( 'click', '.clv-wc-payment-gateway-capture', function ( e ) {
		e.preventDefault();
		alert( 'capture' );
	} );
} );
