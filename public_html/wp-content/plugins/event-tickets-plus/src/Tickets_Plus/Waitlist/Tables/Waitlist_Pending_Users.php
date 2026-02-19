<?php
/**
 * The Waitlist_Pending_Users table schema.
 *
 * @since 6.2.0
 *
 * @package TEC\Tickets_Plu\Waitlist\Tables;
 */

namespace TEC\Tickets_Plus\Waitlist\Tables;

use TEC\Common\Integrations\Custom_Table_Abstract as Table;

/**
 * Waitlist_Pending_Users table schema.
 *
 * The table is used as the temporary storage for waitlist subscribers.
 *
 * Whenever a user is notified, we will delete them from here.
 *
 * @since 6.2.0
 *
 * @package TEC\Tickets_Plu\Waitlist\Tables;
 */
class Waitlist_Pending_Users extends Table {
	/**
	 * The schema version.
	 *
	 * @since 6.2.0
	 *
	 * @var string
	 */
	const SCHEMA_VERSION = '0.0.2-dev';

	/**
	 * The base table name, without the table prefix.
	 *
	 * @since 6.2.0
	 *
	 * @var string
	 */
	protected static $base_table_name = 'tec_waitlist_pending_users';

	/**
	 * The table group.
	 *
	 * @since 6.2.0
	 *
	 * @var string
	 */
	protected static $group = 'tec_tickets_plus_waitlist';

	/**
	 * The slug used to identify the custom table.
	 *
	 * @since 6.2.0
	 *
	 * @var string
	 */
	protected static $schema_slug = 'tec-tickets-plus-waitlist-pending-users';

	/**
	 * The field that uniquely identifies a row in the table.
	 *
	 * @since 6.2.0
	 *
	 * @var string
	 */
	protected static $uid_column = 'waitlist_user_id';

	/**
	 * An array of all the columns in the table.
	 *
	 * @since 6.2.0
	 *
	 * @var string[]
	 */
	public static function get_columns(): array {
		return [
			static::$uid_column,
			'waitlist_id',
			'wp_user_id',
			'fullname',
			'email',
			'created',
		];
	}

	/**
	 * Returns the table creation SQL in the format supported
	 * by the `dbDelta` function.
	 *
	 * @since 6.2.0
	 *
	 * @return string The table creation SQL, in the format supported
	 *                by the `dbDelta` function.
	 */
	protected function get_definition() {
		global $wpdb;
		$table_name      = self::table_name( true );
		$charset_collate = $wpdb->get_charset_collate();
		$uid_column      = self::uid_column();

		return "
			CREATE TABLE `{$table_name}` (
				`{$uid_column}` bigint(20) NOT NULL,
				`waitlist_id` bigint(20) NOT NULL,
				`wp_user_id` bigint(20) NULL,
				`fullname` varchar(255) NULL,
				`email` varchar(255) NULL,
				`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`{$uid_column}`)
			) {$charset_collate};
		";
	}

	/**
	 * Add indexes after table creation.
	 *
	 * @since 6.2.0
	 *
	 * @param array<string,string> $results A map of results in the format
	 *                                      returned by the `dbDelta` function.
	 *
	 * @return array<string,string> A map of results in the format returned by
	 *                              the `dbDelta` function.
	 */
	protected function after_update( array $results ) {
		return $this->check_and_add_index( $results, 'waitlist_id', 'waitlist_id' );
	}
}
