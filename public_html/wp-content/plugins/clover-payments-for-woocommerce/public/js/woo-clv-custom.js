/* global wc_clover_params, Clover */

jQuery( function($) {
	"use strict";

	const wc_clover_form = {
		form: null,
		clover: null,
		elementCache: {},

		card: {
			name: null,
			number: null,
			date: null,
			cvv: null,
			postalCode: null,
		},

		validationErrors: {
			CARD_NAME: {
				render: false,
				error: '',
				focused: false,
			},
			CARD_NUMBER: {
				render: false,
				error: '',
				focused: false,
			},
			CARD_DATE: {
				render: false,
				error: '',
				focused: false,
			},
			CARD_CVV: {
				render: false,
				error: '',
				focused: false,
			},
			CARD_POSTAL_CODE: {
				render: false,
				error: '',
				focused: false,
			},
		},

		init() {
			const $form = $( 'form.woocommerce-checkout, form#order_review, form#add_payment_method' ).first();

			if ( ! $form.length ) {
				return;
			}

			this.form = $form;

			if ( $form.is( 'form.woocommerce-checkout' ) ) {
				$form.on( 'checkout_place_order_clover_payments', this.onSubmit.bind( this ) );
				$form.on( 'change', this.reset.bind( this ) );
			} else {
				$form.on( 'submit', this.onSubmit.bind( this ) );
			}

			$( document ).on( 'cloverError', this.onError.bind( this ) );
			$( document.body ).on( 'checkout_error', this.reset.bind( this ) );

			this.initClover();
			this.createElements();
		},

		initClover() {
			try {
				this.clover = new Clover(
					wc_clover_params.publishableKey,
					{
						locale:     wc_clover_params.locale,
						merchantId: wc_clover_params.merchant,
					}
				);
				return true;
			} catch ( e ) {
				console.error( 'Clover could not be instantiated.', e );
				return false;
			}
		},

		createElements() {
			const elements = this.clover.elements();

			const CC_STYLES = {
				html: {
					display: 'inline !important',
					overflow: 'hidden'
				},
				'.hydrated': {
					display: 'block',
					height: '46px',
					overflow: 'hidden',
				},
				input: {
					fontFamily: 'Graphik, Helvetica, sans-serif',
					color: '#000000',
					backgroundColor: '#FFFFFF',
					boxShadow: 'none',
					fontSize: '16px',
					height: '46px',
					width: '100%',
					padding: '11px 18px 11px 18px',
				},
				'.brand': {
					right: '8px',
					top: '8px'
				},
			};

			this.card.name       = elements.create( 'CARD_NAME', CC_STYLES )
			this.card.number     = elements.create( 'CARD_NUMBER', CC_STYLES );
			this.card.date       = elements.create( 'CARD_DATE', CC_STYLES );
			this.card.cvv        = elements.create( 'CARD_CVV', CC_STYLES );
			this.card.postalCode = elements.create( 'CARD_POSTAL_CODE', CC_STYLES );

			this.addEventListeners();

			if ( $( 'form.woocommerce-checkout' ).length ) {
				$( document.body ).on( 'updated_checkout', () => {
					if ( $( '#card-name' ).children().length ||
						$( '#card-number' ).children().length ||
						$( '#card-date' ).children().length ||
						$( '#card-cvv' ).children().length ||
						$( '#card-postal-code' ).children().length ) {
						return;
					}

					this.mountElements();
				} );
			} else if (  $( 'form.add_payment_method' ).length || $( 'form#order_review' ).length ) {
				this.mountElements();
			}
		},

		mountElements() {
			const elementsToMount = [
				{ type: 'CARD_NAME', id: '#card-name', cardProp: 'name' },
				{ type: 'CARD_NUMBER', id: '#card-number', cardProp: 'number' },
				{ type: 'CARD_DATE', id: '#card-date', cardProp: 'date' },
				{ type: 'CARD_CVV', id: '#card-cvv', cardProp: 'cvv' },
				{ type: 'CARD_POSTAL_CODE', id: '#card-postal-code', cardProp: 'postalCode' },
			];

			let allElementsFound = true;
			this.elementCache = {};

			elementsToMount.forEach( element => {
				const $wrapper = $( element.id );
				if ( ! $wrapper.length ) {
					console.warn( `Clover mount point ${ element.id } not found.` );
					allElementsFound = false;
					return;
				}

				this.card[element.cardProp].mount( element.id );

				const errorId = `${ element.id }-errors`;
				this.elementCache[element.type] = {
					$wrapper: $wrapper,
					$errorContainer: $( errorId ),
					$errorParent: $( errorId ).parent(),
					$errorText: $( `${ errorId } .error-text` ),
				};
			} );

			if ( ! allElementsFound ) {
				console.error( 'Not all Clover elements could be mounted.' );
			}
		},

		addEventListeners() {
			const elements = [
				this.card.name,
				this.card.number,
				this.card.date,
				this.card.cvv,
				this.card.postalCode
			];

			elements.forEach( ( element ) => {
				if ( element === null) {
					return;
				}

				const field = element.elementType;

				element.addEventListener( 'change', () => {
					this.validationErrors[field] = {
						...this.validationErrors[field],
						render: false
					};
					this.displayValidationErrors( field );
				} );

				element.addEventListener( 'blur', ( event ) => {
					this.validationErrors[field] = {
						...this.validationErrors[field],
						render: event[field].touched && !!event[field].error,
						error: event[field].error,
						focused: false
					};
					this.displayValidationErrors( field );
				} );

				element.addEventListener( 'focus', () => {
					this.validationErrors[field] = {
						...this.validationErrors[field],
						focused: true
					};
					this.displayValidationErrors( field )
				} );
			} );
		},

		displayValidationErrors( field ) {
			const cache = this.elementCache[field];
			if ( ! cache ) {
				return;
			}

			const { $wrapper, $errorParent, $errorText } = cache;
			const fieldValidation = this.validationErrors[field];

			if ( fieldValidation.focused ) {
				$wrapper.css( {
					"border-color": "#228800",
					"box-shadow": "0px 0px 3px 0px #228800"
				} );

				if ( ! fieldValidation.render ) {
					$errorParent.css( "display", "none" );
				}

			} else if ( fieldValidation.render ) {
				$wrapper.css( {
					"border-color" : "#e70000",
					"box-shadow" : "none"
				} );
				$errorParent.css( "display", "block" );
				$errorText.text( fieldValidation.error );

			} else {
				$wrapper.css( {
					"border-color" : "#727272",
					"box-shadow" : "none"
				} );
				$errorParent.css( "display", "none" );
			}
		},

		addHiddenInput( name, value, id = null ) {
			const $input = $( '<input>', {
				type: 'hidden',
				name: name,
				value: value
			} );

			if ( id ) {
				$input.attr( 'id', id );
			}

			this.form.append( $input );
		},

		onSubmit() {
			if ( ! this.isCloverChosen() ) {
				return true;
			}

			if ( this.hasToken() ) {
				return true;
			}

			this.block();

			this.clover.createToken()
				.then( ( response ) => {
					this.handleResponse( response );
				} )
				.catch( ( e ) => {
					console.error( 'Tokenization failed.', e );
					const message = wc_clover_params.localizedMessages['unexpected'];
					$( document.body ).trigger('cloverError', message );
				} );

			return false;
		},

		handleResponse( response ) {
			if ( response?.errors ) {
				for ( const key in response.errors ) {
					if ( response.errors[key] ) {
						$( document.body ).trigger( 'cloverError', response.errors[key] );
						return;
					}
				}
			}
			if ( ! response?.token ) {
				console.error( 'Token not found.', response );
				const message = wc_clover_params.localizedMessages['unexpected'];
				$( document.body ).trigger('cloverError', message );
				return;
			}

			this.reset();

			this.addHiddenInput( 'clover_token', response.token, 'clover-token' );
			this.addHiddenInput( 'clover_process_checkout_nonce', wc_clover_params.checkoutNonce, 'clover-process-checkout-nonce' );
			this.addHiddenInput( 'card_type', response.card.brand, 'card-type' );
			this.addHiddenInput( 'last4', response.card.last4, 'last4' );
			this.addHiddenInput( 'expiry_month', response.card.exp_month, 'expiry-month' );
			this.addHiddenInput( 'expiry_year', response.card.exp_year, 'expiry-year' );

			this.form.trigger( 'submit' );
		},

		onError( event, message ) {
			const selectedMethodElement = this.getSelectedPaymentElement().closest( '.payment_method_clover_payments' );
			const errorContainer = selectedMethodElement.find( '#clover-errors' );

			this.reset();

			$( errorContainer ).html( '<ul class="woocommerce_error woocommerce-error wc-clover-error"><li /></ul>' );
			$( errorContainer ).find( 'li' ).text( message );

			const $cloverError = $( '.wc-clover-error' );
			if ( $cloverError.length ) {
				$( 'html, body' ).animate( {
					scrollTop: ( $cloverError.offset().top - 200 )
				}, 200 );
			}

			this.unblock();
		},

		reset() {
			this.form.find( '#clover-token, #clover-process-checkout-nonce, #card-type, #last4, #expiry-month, #expiry-year' ).remove();
		},

		isCloverChosen() {
			return $( '#payment_method_clover_payments' ).is( ':checked' );
		},

		hasToken() {
			return this.form.find( '#clover-token' ).length > 0;
		},

		block() {
			if ( ! this.isMobile() ) {
				this.form.block( {
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				} );
			}
		},

		unblock() {
			this.form && this.form.unblock();
		},

		isMobile() {
			return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

		},

		getSelectedPaymentElement() {
			return $( '.payment_methods input[name="payment_method"]:checked' );
		},
	}
	wc_clover_form.init();
} );
