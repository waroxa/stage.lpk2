import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { PaymentMethodDescription } from "./components/payment-method-description";
import { CreditCard } from "./components/credit-card";
import { CreditCardSavedToken } from "./components/credit-card-saved-token";
import { PaymentMethodLabel } from "./components/payment-method-label";
import { settings } from "./utils";

/**
 * Payment method config object.
 */
const cloverCreditCard = {
	name: settings.name,
	label: <PaymentMethodLabel settings={settings} />,
	content: <CreditCard />,
	edit: <PaymentMethodDescription settings={settings} edit={true}/>,
	savedTokenComponent: <CreditCardSavedToken />,
	canMakePayment: () => true,
	icons: settings.icons,
	ariaLabel: decodeEntities( settings.title ),
	supports: {
		features: settings.supports,
		showSavedCards: settings.flags?.tokenization_enabled || false,
		showSaveOption:
			!settings?.flags?.tokenization_forced &&
			settings?.flags?.tokenization_enabled,
	},
};

registerPaymentMethod( cloverCreditCard );
