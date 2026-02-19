<?php
/**
 * The Waitlists table schema.
 *
 * @since 6.2.0
 *
 * @package TEC\Tickets_Plu\Waitlist\Tables;
 */

namespace TEC\Tickets_Plus\Waitlist\Tables;

use TEC\Common\Integrations\Custom_Table_Abstract as Table;

/**
 * Waitlists table schema.
 *
 * The table is used to link a waitlist object with a ticket-able post.
 *
 * @since 6.2.0
 *
 * @package TEC\Tickets_Plu\Waitlist\Tables;
 */
class Waitlists extends Table {
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
	protected static $base_table_name = 'tec_waitlists';

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
	protected static $schema_slug = 'tec-tickets-plus-waitlist';

	/**
	 * The field that uniquely identifies a row in the table.
	 *
	 * @since 6.2.0
	 *
	 * @var string
	 */
	protected static $uid_column = 'waitlist_id';

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
			'post_id',
			'enabled',
			'conditional',
			'type',
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
				`{$uid_column}` bigint(20) NOT NULL AUTO_INCREMENT,
				`post_id` bigint(20) NOT NULL,
				`enabled` boolean NOT NULL DEFAULT 0,
				`conditional` tinyint(1) NOT NULL DEFAULT 0,
				`type` tinyint(1) NOT NULL DEFAULT 0,
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
		$this->check_and_add_index( $results, 'type', 'type' );
		return $this->check_and_add_index( $results, 'post_id', 'post_id' );
	}
}
