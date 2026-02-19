/**
 * The My Payment Methods handler.
 *
 * @since 4.7.3
 */
jQuery( function( $ ) {

	'use strict';

	window.WC_First_Data_My_Payment_Methods_Handler = window.SV_WC_Payment_Methods_Handler_v5_15_2;

	$( document.body ).trigger( 'wc_first_data_my_payment_methods_handler_loaded' );

	/**
	 * Displays an appropriate error message with a CTA when the delete token button is clicked for a token used by subscriptions.
	 *
	 * @since 5.2.3
	 */
	$( '.clover_deletion_error' ).on( 'click', function ( e ) {
		e.preventDefault();

		var token_id = parseInt( $( this ).attr( 'href' ).split( 'token_id=' )[1].split( '&' )[0], 10 ),
		    notice_container = $( '#clover_delete_token_warning' ),
		    notice = notice_container.find( 'li' ); // legacy notices

		if ( notice_container.find( '.wc-block-components-notice-banner' ).length > 0 ) {
			notice = notice_container.find( '.wc-block-components-notice-banner__content' ); // block-based notices
		}

		notice.html( notice_container.data( 'message' ) );
		notice.append( '<div style="text-align: right; margin: 10px 0 0;"><button id="clover_delete_token_confirm" class="btn btn-primary button button-primary wp-block-button__link wp-element-button">' + notice_container.data( 'confirm' ) + '</button>' );

		notice_container.slideDown();

		// if deletion is confirmed from the notice CTA, issue an AJAX request to delete the token
		$( '#clover_delete_token_confirm' ).on( 'click', function ( e ) {
			e.preventDefault();

			$.ajax( {
				type: 'POST',
				url: notice_container.data( 'ajax-url' ),
				data: {
					action: 'clover_delete_subscription_tied_token',
					token_id: token_id,
					security: notice_container.data( 'nonce' )
				},
				success: function ( response ) {

					if ( response.success ) {
						notice_container.slideUp();
						window.location.reload();
					}

					console.log( response );
				}
			} );
		} );
	} );

} );
