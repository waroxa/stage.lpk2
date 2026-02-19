import { useEffect } from "@wordpress/element";
import { settings } from "../utils";
import { __ } from "@wordpress/i18n";
import { decodeEntities } from '@wordpress/html-entities'

export function CreditCardSavedToken ({
	emitResponse,
	eventRegistration
}) {
	const onCheckoutFail = eventRegistration?.onCheckoutFail

	useEffect(() => {
		const unsubscribe = onCheckoutFail?.(async (checkoutFailResponse) => {
			const { paymentStatus, paymentDetails } = checkoutFailResponse?.processingResponse || {}

			if (
				paymentStatus === emitResponse?.responseTypes.FAIL &&
				paymentDetails?.result === emitResponse?.responseTypes.FAIL &&
				paymentDetails?.message
			) {
				return {
					type: emitResponse?.responseTypes.FAIL,
					// TODO: We can add more specific errors here based on paymentDetails?.result once we know who should map the message to customer friendly versions
					message: settings?.debug_mode === 'full' ||
						settings?.debug_mode === 'checkout' ? paymentDetails?.message : __('An error occurred, please try again or try an alternate form of payment.', 'woocommerce-gateway-firstdata'),
					messageContext:
					settings?.debug_mode === 'full' ||
					settings?.debug_mode === 'checkout'
							? emitResponse?.noticeContexts?.CHECKOUT
							: emitResponse?.noticeContexts?.PAYMENTS,
					retry: true,
				}
			}
		})
		return () => unsubscribe?.()
	}, [
		emitResponse?.noticeContexts?.CHECKOUT,
		emitResponse?.noticeContexts?.PAYMENTS,
		emitResponse?.responseTypes.FAIL,
		onCheckoutFail,
	])

	return decodeEntities('')
}
