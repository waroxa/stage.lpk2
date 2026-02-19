import { decodeEntities } from "@wordpress/html-entities";
import { __, _x } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

/**
 * Renders the payment form description, overridden to add the test card numbers for easier sandbox testing.
 */
export const PaymentMethodDescription = ( props ) => {
	const {
		settings = {},
		edit = false,
	} = props;

	const descriptionText = decodeEntities(settings.description || '');
	const className = `sv-wc-payment-method-description wc-${settings.id}-description`;

	if (settings?.flags?.is_test_environment && ! edit) {
		return (
			<div style={{display: 'grid', marginBlockEnd: '1rem'}}>
				<p className={className}>{descriptionText}</p>
				<strong>
					{__('Test Mode Enabled', 'wc-plugin-framework').toUpperCase()}
				</strong>
				{settings?.flags?.has_subscription && settings?.flags?.has_tokens ? null : (
					<>
						<p>{createInterpolateElement(_x(
									'Use the <a>following card numbers</a> with card security code <span>123</span>.',
									'Credit card test numbers',
									'woocommerce-gateway-firstdata',
								), {
									a: <a href="https://docs.clover.com/docs/test-card-numbers" target="_blank" />,
									span: <span style={{fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Monaco, monospace'}} />
								}
							)}
						</p>
						<div style={{ display: 'grid', gridTemplateColumns: 'max-content min-content', gap: '8px', alignItems: 'center', paddingTop: '8px'}}>
							<div style={{backgroundColor: 'rgb(21 128 61)', display: 'flex', justifyContent: 'center', borderRadius: '16px', color: 'white', userSelect: 'none', fontWeight: 'semibold', fontSize: '0.75rem', padding: '0.125rem 0.625rem', transition: 'background-color 0.2s', outline: 'none', ring: '2', ringRing: 'ring', ringOffset: '2'}}>{__('Approved', 'woocommerce-gateway-firstdata')}</div>
							<span style={{fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace' }}>{settings.test_card_number_approval}</span>
							<div style={{backgroundColor: 'rgb(185 28 28)', display: 'flex', justifyContent: 'center', borderRadius: '16px', color: 'white', userSelect: 'none', fontWeight: 'semibold', fontSize: '0.75rem', padding: '0.125rem 0.625rem', transition: 'background-color 0.2s', outline: 'none', ring: '2', ringRing: 'ring', ringOffset: '2'}}> {__('Declined', 'woocommerce-gateway-firstdata')}</div>
							<span style={{fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace' }}>{settings.test_card_number_decline}</span>
						</div>
					</>
				)}
			</div>
		);
	}

	return <p className={className}>{descriptionText}</p>;
}
