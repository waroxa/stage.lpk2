<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WC_Clover_Settings_Keys {

	private function __construct() {}

	public const ENABLED                    = 'enabled';

	public const TITLE                      = 'title';

	public const ENVIRONMENT                = 'environment';

	public const MERCHANT_ID                = 'merchant_id';

	public const PRIVATE_KEY                = 'private_key';

	public const PUBLIC_KEY                 = 'publishable_key';

	public const TEST_MERCHANT_ID           = 'test_merchant_id';

	public const TEST_PRIVATE_KEY           = 'test_private_key';

	public const TEST_PUBLIC_KEY            = 'test_publishable_key';

	public const APPLE_PAY                  = 'apple_pay';

	public const DEBUG_MODE                 = 'debug_mode';

	public const PAYMENT_ACTION             = 'payment_action';

	public const APPLE_PAY_DOMAIN_NAME      = 'apple_pay_domain_name';

	public const APPLE_PAY_MERCHANT_ID      = 'apple_pay_merchant_id';

	public const TEST_APPLE_PAY_MERCHANT_ID = 'test_apple_pay_merchant_id';

	public const APPLE_PAY_PRIVATE_KEY      = 'apple_pay_private_key';

	public const TEST_APPLE_PAY_PRIVATE_KEY = 'test_apple_pay_private_key';

	public const APPLE_PAY_DOMAIN_UUID      = 'apple_pay_domain_uuid';

	public const TEST_APPLE_PAY_DOMAIN_UUID = 'test_apple_pay_domain_uuid';
}
