<?php
/**
 * Plugin Name: LPK Events Templates
 * Description: Custom desktop event listing and detail templates powered by The Events Calendar and Event Tickets.
 * Version: 1.0.0
 * Author: LPK
 * Text Domain: lpk-events-templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LPK_Events_Templates {
	private static $instance = null;
	private $detail_page_base_url = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', [ $this, 'register_shortcodes' ] );
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	public function register_shortcodes() {
		add_shortcode( 'lpk_events_desktop_listing', [ $this, 'shortcode_events_desktop_listing' ] );
		add_shortcode( 'lpk_event_desktop_detail', [ $this, 'shortcode_event_desktop_detail' ] );
		add_shortcode( 'lpk_events_desktop_detail', [ $this, 'shortcode_event_desktop_detail' ] );
		add_shortcode( 'lpk_events_mobile_listing', [ $this, 'shortcode_events_mobile_listing' ] );
		add_shortcode( 'lpk_event_mobile_detail', [ $this, 'shortcode_event_mobile_detail' ] );
		add_shortcode( 'lpk_events_mobile_detail', [ $this, 'shortcode_event_mobile_detail' ] );
	}

	private function template_1_page_bootstrap_script() {
		return "<script>(function(){if(document.body){document.body.classList.add('lpk-template-1-active');}})();</script>";
	}



	private function settings_option_name() {
		return 'lpk_events_templates_settings';
	}

	private function get_settings() {
		$defaults = [
			'default_listing_limit' => 6,
			'default_event_id'      => 0,
			'featured_event_ids'    => [],
			'detail_page_id'        => 0,
			'map_url'               => 'https://share.google/NFz7TfIdr2vS7lwO3',
			'map_address'           => '869 Bd Saint-Jean-Baptiste local 100, Mercier, QC J6R 2K8',
			'map_image_id'          => 0,
			'ticket_form_shortcode' => '',
			'event_badges'          => [],
		];
		$settings = get_option( $this->settings_option_name(), [] );
		return wp_parse_args( is_array( $settings ) ? $settings : [], $defaults );
	}

	private function get_setting( $key, $default = null ) {
		$settings = $this->get_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	public function register_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=tribe_events',
			'LPK Events Templates',
			'LPK Templates',
			'manage_options',
			'lpk-events-templates',
			[ $this, 'render_admin_page' ]
		);
	}

	public function register_settings() {
		register_setting(
			'lpk_events_templates_settings_group',
			$this->settings_option_name(),
			[ $this, 'sanitize_settings' ]
		);
	}

	public function sanitize_settings( $input ) {
		$input = is_array( $input ) ? $input : [];
		$featured = isset( $input['featured_event_ids'] ) ? (array) $input['featured_event_ids'] : [];
		$featured = array_values( array_filter( array_map( 'absint', $featured ) ) );
		$event_badges = isset( $input['event_badges'] ) ? (array) $input['event_badges'] : [];
		$event_badges = array_reduce(
			array_keys( $event_badges ),
			function ( $carry, $event_id ) use ( $event_badges ) {
				$event_id = absint( $event_id );
				if ( ! $event_id ) {
					return $carry;
				}

				$badge = sanitize_text_field( (string) ( $event_badges[ $event_id ] ?? '' ) );
				if ( '' !== $badge ) {
					$carry[ $event_id ] = $badge;
				}

				return $carry;
			},
			[]
		);

		return [
			'default_listing_limit' => max( 1, absint( $input['default_listing_limit'] ?? 6 ) ),
			'default_event_id'      => absint( $input['default_event_id'] ?? 0 ),
			'featured_event_ids'    => $featured,
			'detail_page_id'        => absint( $input['detail_page_id'] ?? 0 ),
			'map_url'               => esc_url_raw( trim( (string) ( $input['map_url'] ?? 'https://share.google/NFz7TfIdr2vS7lwO3' ) ) ),
			'map_address'           => sanitize_text_field( (string) ( $input['map_address'] ?? '869 Bd Saint-Jean-Baptiste local 100, Mercier, QC J6R 2K8' ) ),
			'map_image_id'          => absint( $input['map_image_id'] ?? 0 ),
			'ticket_form_shortcode' => sanitize_text_field( (string) ( $input['ticket_form_shortcode'] ?? '' ) ),
			'event_badges'          => $event_badges,
		];
	}

	private function event_badge_label( $event_id ) {
		$event_id = absint( $event_id );
		$badges = $this->get_setting( 'event_badges', [] );
		if ( ! is_array( $badges ) ) {
			$badges = [];
		}

		if ( $event_id && ! empty( $badges[ $event_id ] ) ) {
			return (string) $badges[ $event_id ];
		}

		return (string) $this->tr( 'Événement spécial · Famille', 'Special event · Family' );
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'tribe_events_page_lpk-events-templates' !== $hook ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'lpk-events-templates-admin',
			plugin_dir_url( __FILE__ ) . 'assets/template-1-admin.js',
			[ 'jquery' ],
			'1.1.0',
			true
		);
	}

	private function all_pages_for_admin() {
		return get_posts(
			[
				'post_type'      => 'page',
				'posts_per_page' => 200,
				'post_status'    => 'publish',
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);
	}

	private function all_events_for_admin() {
		return get_posts(
			[
				'post_type'      => 'tribe_events',
				'posts_per_page' => 200,
				'post_status'    => 'publish',
				'meta_key'       => '_EventStartDate',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
			]
		);
	}

	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings = $this->get_settings();
		$events = $this->all_events_for_admin();
		$pages = $this->all_pages_for_admin();
		$map_image_id = absint( $settings['map_image_id'] );
		$map_image_url = $map_image_id ? wp_get_attachment_image_url( $map_image_id, 'medium_large' ) : '';
		?>
		<div class="wrap">
			<h1>LPK Events Templates</h1>
			<p>Configure which events appear in desktop template-1 and which event is used by default for detail pages.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'lpk_events_templates_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="lpk-default-listing-limit">Default listing limit</label></th>
						<td><input id="lpk-default-listing-limit" type="number" min="1" name="<?php echo esc_attr( $this->settings_option_name() ); ?>[default_listing_limit]" value="<?php echo esc_attr( (string) $settings['default_listing_limit'] ); ?>" class="small-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="lpk-default-event-id">Default detail event</label></th>
						<td>
							<select id="lpk-default-event-id" name="<?php echo esc_attr( $this->settings_option_name() ); ?>[default_event_id]">
								<option value="0">Auto (first upcoming ticketed event)</option>
								<?php foreach ( $events as $event ) : ?>
									<option value="<?php echo esc_attr( (string) $event->ID ); ?>" <?php selected( (int) $settings['default_event_id'], (int) $event->ID ); ?>><?php echo esc_html( $event->post_title . ' (#' . $event->ID . ')' ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lpk-detail-page-id">Detail shortcode page</label></th>
						<td>
							<select id="lpk-detail-page-id" name="<?php echo esc_attr( $this->settings_option_name() ); ?>[detail_page_id]">
								<option value="0">Auto (event native permalink)</option>
								<?php foreach ( $pages as $page ) : ?>
									<option value="<?php echo esc_attr( (string) $page->ID ); ?>" <?php selected( (int) $settings['detail_page_id'], (int) $page->ID ); ?>><?php echo esc_html( $page->post_title . ' (#' . $page->ID . ')' ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description">Select the page containing <code>[lpk_event_desktop_detail]</code>. Listing “Voir/View” buttons will pass the selected event ID to this page.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lpk-featured-events">Featured events in listing</label></th>
						<td>
							<select id="lpk-featured-events" name="<?php echo esc_attr( $this->settings_option_name() ); ?>[featured_event_ids][]" multiple="multiple" style="min-width:320px; min-height:240px;">
								<?php foreach ( $events as $event ) : ?>
									<option value="<?php echo esc_attr( (string) $event->ID ); ?>" <?php selected( in_array( (int) $event->ID, array_map( 'intval', (array) $settings['featured_event_ids'] ), true ) ); ?>><?php echo esc_html( $event->post_title . ' (#' . $event->ID . ')' ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description">If selected, listing shortcode shows these events in this exact order. Leave empty to auto-pull upcoming events.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Badge text per event</th>
						<td>
							<p class="description" style="margin-top:0;">Customize the hero badge shown on each mobile event detail page. Leave blank to use the default badge.</p>
							<div style="display:grid; gap:10px; max-width:860px;">
								<?php foreach ( $events as $event ) : ?>
									<?php $badge_value = isset( $settings['event_badges'][ $event->ID ] ) ? (string) $settings['event_badges'][ $event->ID ] : ''; ?>
									<label for="lpk-event-badge-<?php echo esc_attr( (string) $event->ID ); ?>" style="display:grid; gap:4px;">
										<span><?php echo esc_html( $event->post_title . ' (#' . $event->ID . ')' ); ?></span>
										<input id="lpk-event-badge-<?php echo esc_attr( (string) $event->ID ); ?>" type="text" class="regular-text" name="<?php echo esc_attr( $this->settings_option_name() ); ?>[event_badges][<?php echo esc_attr( (string) $event->ID ); ?>]" value="<?php echo esc_attr( $badge_value ); ?>" placeholder="<?php echo esc_attr( $this->tr( 'Événement spécial · Famille', 'Special event · Family' ) ); ?>" />
									</label>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lpk-map-address">Map address</label></th>
						<td>
							<input id="lpk-map-address" type="text" class="regular-text" name="<?php echo esc_attr( $this->settings_option_name() ); ?>[map_address]" value="<?php echo esc_attr( (string) $settings['map_address'] ); ?>" />
							<p class="description">Displayed in the “Lieu / Location” block.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lpk-map-url">Map link URL</label></th>
						<td>
							<input id="lpk-map-url" type="url" class="regular-text" name="<?php echo esc_attr( $this->settings_option_name() ); ?>[map_url]" value="<?php echo esc_attr( (string) $settings['map_url'] ); ?>" />
							<p class="description">Map card click target URL (opens in a new tab).</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Map placeholder image</th>
						<td>
							<input type="hidden" id="lpk-map-image-id" name="<?php echo esc_attr( $this->settings_option_name() ); ?>[map_image_id]" value="<?php echo esc_attr( (string) $map_image_id ); ?>" />
							<button type="button" class="button" id="lpk-map-image-upload">Select image</button>
							<button type="button" class="button" id="lpk-map-image-remove" <?php disabled( ! $map_image_id ); ?>>Remove image</button>
							<p class="description">Optional custom image shown in the map block on event detail.</p>
							<div id="lpk-map-image-preview" style="margin-top:12px; max-width:320px; <?php echo $map_image_url ? '' : 'display:none;'; ?>">
								<img src="<?php echo esc_url( $map_image_url ?: '' ); ?>" alt="Map preview" style="max-width:100%; height:auto; border:1px solid #ccd0d4; border-radius:6px;" />
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lpk-ticket-form-shortcode">Ticket Pro form shortcode</label></th>
						<td>
							<input id="lpk-ticket-form-shortcode" type="text" class="regular-text code" name="<?php echo esc_attr( $this->settings_option_name() ); ?>[ticket_form_shortcode]" value="<?php echo esc_attr( (string) $settings['ticket_form_shortcode'] ); ?>" placeholder="[tickets_pro_form id=&quot;123&quot;]" />
							<p class="description">Paste your Ticket Pro form shortcode. If empty, the template falls back to Event Tickets for this event.</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<hr />
			<h2>Ticketing quick access (same dashboard page)</h2>
			<p>Use these direct admin links to avoid menu redirects when managing ticket setup.</p>
			<p>
				<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=tribe_events&page=tickets-attendees' ) ); ?>">Attendees</a>
				<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=tribe_events&page=tickets-settings' ) ); ?>">Tickets Settings</a>
				<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=tribe_events' ) ); ?>">All Events</a>
			</p>
			<hr />
			<h2>How to add to a page (Elementor or classic editor)</h2>
			<ol>
				<li>Create/edit a page.</li>
				<li>In Elementor: add a <strong>Shortcode</strong> widget. In Classic/Gutenberg: add a Shortcode block.</li>
				<li>Paste one of these:</li>
			</ol>
			<pre>[lpk_events_desktop_listing]
	[lpk_event_desktop_detail]
	[lpk_events_mobile_listing]
	[lpk_event_mobile_detail]</pre>
			<p>You can also target a specific event manually: <code>[lpk_event_desktop_detail id="123"]</code>.</p>
			<p>To configure ticket types and prices for an event, edit the event itself in <strong>Events → All Events</strong> and use the <strong>Tickets</strong> panel (Event Tickets / Event Tickets Plus).</p>
			<hr />
			<h2>Direct event shortcodes (copy/paste)</h2>
			<table class="widefat striped" style="max-width:900px;">
				<thead><tr><th>Event</th><th>Shortcode</th></tr></thead>
				<tbody>
				<?php if ( ! empty( $events ) ) : ?>
					<?php foreach ( $events as $event ) : ?>
						<tr>
							<td><?php echo esc_html( $event->post_title . ' (#' . $event->ID . ')' ); ?></td>
							<td><code>[lpk_event_desktop_detail id="<?php echo esc_attr( (string) $event->ID ); ?>"]</code></td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td colspan="2">No events found.</td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	private function is_english() {
		global $TRP_LANGUAGE;

		if ( ! empty( $TRP_LANGUAGE ) ) {
			return 0 === strpos( strtolower( (string) $TRP_LANGUAGE ), 'en' );
		}

		$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
		if ( 0 === strpos( strtolower( (string) $locale ), 'en' ) ) {
			return true;
		}

		$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '';
		$path = strtolower( $path );

		if ( 0 === strpos( $path, '/en/' ) ) {
			return true;
		}

		if ( 0 === strpos( $path, '/fr/' ) ) {
			return false;
		}

		return false;
	}

	private function tr( $fr, $en ) {
		return $this->is_english() ? $en : $fr;
	}

	private function event_location_text( $event_id, $fallback_address ) {
		$location = '';
		if ( function_exists( 'tribe_get_full_address' ) ) {
			$location = (string) tribe_get_full_address( $event_id );
		}

		$location = wp_strip_all_tags( $location );
		$location = preg_replace( '/\s+/u', ' ', (string) $location );
		$location = trim( (string) $location );

		if ( '' !== $location ) {
			return $location;
		}

		$fallback_address = trim( (string) $fallback_address );
		return '' !== $fallback_address ? $fallback_address : $this->tr( 'Lieu communiqué après réservation', 'Location shared after booking' );
	}

	private function event_description_html( $event_id ) {
		$content = get_post_field( 'post_content', $event_id );

		if ( ! is_string( $content ) || '' === trim( wp_strip_all_tags( $content ) ) ) {
			return '';
		}

		$content = wp_kses_post( $content );

		return wpautop( $content );
	}

	private function enqueue_base_assets() {
		$base = plugin_dir_url( __FILE__ ) . 'assets/';
		wp_enqueue_style( 'lpk-events-template-common', $base . 'common.css', [], '1.0.0' );
		wp_enqueue_style( 'lpk-events-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Montserrat:wght@500;600;700;800&display=swap', [], null );
	}

	private function enqueue_template_1_assets() {
		$base = plugin_dir_url( __FILE__ ) . 'assets/';
		$path = plugin_dir_path( __FILE__ ) . 'assets/';
		$this->enqueue_base_assets();
		$css_version = file_exists( $path . 'template-1.css' ) ? (string) filemtime( $path . 'template-1.css' ) : '1.0.0';
		$js_version  = file_exists( $path . 'template-1.js' ) ? (string) filemtime( $path . 'template-1.js' ) : '1.1.0';
		wp_enqueue_style( 'lpk-events-template-1', $base . 'template-1.css', [ 'lpk-events-template-common' ], $css_version );
		wp_enqueue_script( 'lpk-events-template-1', $base . 'template-1.js', [], $js_version, true );
	}

	private function enqueue_template_3_assets() {
		$base = plugin_dir_url( __FILE__ ) . 'assets/';
		$path = plugin_dir_path( __FILE__ ) . 'assets/';
		$this->enqueue_base_assets();
		$css_version = file_exists( $path . 'template-3.css' ) ? (string) filemtime( $path . 'template-3.css' ) : '1.0.1';
		$js_version  = file_exists( $path . 'template-3.js' ) ? (string) filemtime( $path . 'template-3.js' ) : '1.0.1';
		wp_enqueue_style( 'lpk-events-template-3', $base . 'template-3.css', [ 'lpk-events-template-common' ], $css_version );
		wp_enqueue_script( 'lpk-events-template-3', $base . 'template-3.js', [], $js_version, true );
	}

	private function map_image_url() {
		$map_image_id = absint( $this->get_setting( 'map_image_id', 0 ) );
		if ( ! $map_image_id ) {
			return '';
		}

		$image_url = wp_get_attachment_image_url( $map_image_id, 'large' );
		if ( empty( $image_url ) ) {
			$image_url = wp_get_attachment_image_url( $map_image_id, 'full' );
		}
		if ( empty( $image_url ) ) {
			$image_url = wp_get_attachment_url( $map_image_id );
		}

		return is_string( $image_url ) ? $image_url : '';
	}


	private function featured_events_from_settings( $limit = 6 ) {
		$ids = array_values( array_filter( array_map( 'absint', (array) $this->get_setting( 'featured_event_ids', [] ) ) ) );
		if ( empty( $ids ) ) {
			return [];
		}

		return get_posts(
			[
				'post_type'      => 'tribe_events',
				'post__in'       => array_slice( $ids, 0, absint( $limit ) ),
				'orderby'        => 'post__in',
				'posts_per_page' => absint( $limit ),
				'post_status'    => 'publish',
			]
		);
	}

	private function upcoming_events( $limit = 6 ) {
		$limit = absint( $limit );
		$featured = $this->featured_events_from_settings( $limit );
		if ( count( $featured ) >= $limit ) {
			return $featured;
		}

		$featured_ids = array_values( array_filter( array_map( 'absint', wp_list_pluck( $featured, 'ID' ) ) ) );
		$remaining = max( 0, $limit - count( $featured ) );

		if ( 0 === $remaining ) {
			return $featured;
		}

		if ( function_exists( 'tribe_get_events' ) ) {
			$events = tribe_get_events(
				[
					'posts_per_page' => $remaining,
					'start_date'     => current_time( 'mysql' ),
					'orderby'        => 'event_date',
					'order'          => 'ASC',
					'post__not_in'   => $featured_ids,
				]
			);

			if ( ! empty( $events ) ) {
				return array_slice( array_merge( $featured, $events ), 0, $limit );
			}

			$past_events = tribe_get_events(
				[
					'posts_per_page' => $remaining,
					'orderby'        => 'event_date',
					'order'          => 'DESC',
					'post__not_in'   => $featured_ids,
				]
			);

			if ( ! empty( $past_events ) ) {
				return array_slice( array_merge( $featured, $past_events ), 0, $limit );
			}

			return array_slice( array_merge( $featured, $this->fallback_events_from_tickets( $remaining, $featured_ids ) ), 0, $limit );
		}

		$posts = get_posts(
			[
				'post_type'      => 'tribe_events',
				'posts_per_page' => absint( $remaining ),
				'post_status'    => 'publish',
				'meta_key'       => '_EventStartDate',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'post__not_in'   => $featured_ids,
			]
		);

		if ( ! empty( $posts ) ) {
			return array_slice( array_merge( $featured, $posts ), 0, $limit );
		}

		return array_slice( array_merge( $featured, $this->fallback_events_from_tickets( $remaining, $featured_ids ) ), 0, $limit );
	}



	private function ticketed_event_ids( $limit = 10 ) {
		global $wpdb;

		$limit = max( 1, absint( $limit ) );
		$meta_keys = [ '_tribe_wooticket_for_event', '_tribe_rsvp_for_event' ];
		$placeholders = implode( ',', array_fill( 0, count( $meta_keys ), '%s' ) );
		$sql = $wpdb->prepare(
			"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm WHERE pm.meta_key IN ($placeholders) AND pm.meta_value <> '' ORDER BY pm.meta_id DESC LIMIT %d",
			array_merge( $meta_keys, [ $limit ] )
		);
		$ids = $wpdb->get_col( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return array_values( array_filter( array_map( 'absint', (array) $ids ) ) );
	}

	private function fallback_events_from_tickets( $limit = 6, $exclude_ids = [] ) {
		$ids = $this->ticketed_event_ids( $limit * 3 );
		if ( empty( $ids ) ) {
			return [];
		}

		$exclude_ids = array_values( array_filter( array_map( 'absint', (array) $exclude_ids ) ) );
		if ( ! empty( $exclude_ids ) ) {
			$ids = array_values( array_diff( $ids, $exclude_ids ) );
		}

		if ( empty( $ids ) ) {
			return [];
		}

		$posts = get_posts(
			[
				'post_type'      => 'any',
				'post__in'       => $ids,
				'orderby'        => 'post__in',
				'posts_per_page' => absint( $limit ),
				'post_status'    => 'publish',
			]
		);
		return is_array( $posts ) ? $posts : [];
	}

	private function is_valid_event_post( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return false;
		}

		return 'publish' === get_post_status( $post_id ) && 'tribe_events' === get_post_type( $post_id );
	}

	private function detail_page_base_url() {
		if ( null !== $this->detail_page_base_url ) {
			return $this->detail_page_base_url;
		}

		$this->detail_page_base_url = '';
		$detail_page_id = absint( $this->get_setting( 'detail_page_id', 0 ) );
		if ( $detail_page_id && 'publish' === get_post_status( $detail_page_id ) && 'page' === get_post_type( $detail_page_id ) ) {
			$this->detail_page_base_url = (string) get_permalink( $detail_page_id );
		}

		return $this->detail_page_base_url;
	}

	private function detail_page_url_for_event( $event_id ) {
		$detail_page_base_url = $this->detail_page_base_url();
		if ( ! empty( $detail_page_base_url ) ) {
			return add_query_arg( 'lpk_event_id', absint( $event_id ), $detail_page_base_url );
		}

		return get_permalink( $event_id );
	}

	private function resolve_event_id( $requested_id = 0 ) {
		$event_id = absint( $requested_id );
		if ( $this->is_valid_event_post( $event_id ) ) {
			return $event_id;
		}

		if ( function_exists( 'is_singular' ) && is_singular( 'tribe_events' ) ) {
			$current = get_the_ID();
			if ( $this->is_valid_event_post( $current ) ) {
				return (int) $current;
			}
		}

		$default_event_id = absint( $this->get_setting( 'default_event_id', 0 ) );
		if ( $this->is_valid_event_post( $default_event_id ) ) {
			return $default_event_id;
		}

		$events = $this->upcoming_events( 1 );
		if ( ! empty( $events ) ) {
			return (int) $events[0]->ID;
		}

		$fallback = $this->fallback_events_from_tickets( 1 );
		if ( ! empty( $fallback ) ) {
			return (int) $fallback[0]->ID;
		}

		return 0;
	}
	private function event_tickets( $event_id ) {
		if ( ! class_exists( 'Tribe__Tickets__Tickets' ) ) {
			return [];
		}

		$tickets = Tribe__Tickets__Tickets::get_all_event_tickets( $event_id );
		return is_array( $tickets ) ? $tickets : [];
	}

	private function starting_price( $event_id ) {
		$tickets = $this->event_tickets( $event_id );
		$prices  = [];
		foreach ( $tickets as $ticket ) {
			$prices[] = (float) $ticket->price;
		}

		if ( ! empty( $prices ) ) {
			$price = min( $prices );
			return sprintf( $this->tr( 'À partir de %s', 'From %s' ), wp_strip_all_tags( $this->wc_price( $price ) ) );
		}

		if ( function_exists( 'tribe_get_cost' ) ) {
			$cost = tribe_get_cost( $event_id, true );
			if ( ! empty( $cost ) ) {
				return sprintf( $this->tr( 'À partir de %s', 'From %s' ), wp_strip_all_tags( $cost ) );
			}
		}

		return $this->tr( 'Tarif sur demande', 'Price on request' );
	}



	private function event_start_date( $event_id, $format = 'j M Y' ) {
		if ( function_exists( 'tribe_get_start_date' ) ) {
			return (string) tribe_get_start_date( $event_id, false, $format );
		}
		$raw = get_post_meta( $event_id, '_EventStartDate', true );
		if ( ! empty( $raw ) ) {
			$timestamp = strtotime( (string) $raw );
			if ( $timestamp ) {
				return wp_date( $format, $timestamp );
			}
		}
		return get_the_date( $format, $event_id );
	}

	private function event_start_time( $event_id, $format = 'H:i' ) {
		$from_meta = $this->event_meta_datetime( $event_id, '_EventStartDate', $format );
		if ( '' !== $from_meta ) {
			return $from_meta;
		}

		if ( function_exists( 'tribe_get_start_time' ) ) {
			return (string) tribe_get_start_time( $event_id, false, $format );
		}
		return '';
	}

	private function event_end_time( $event_id, $format = 'H:i' ) {
		$from_meta = $this->event_meta_datetime( $event_id, '_EventEndDate', $format );
		if ( '' !== $from_meta ) {
			return $from_meta;
		}

		if ( function_exists( 'tribe_get_end_time' ) ) {
			return (string) tribe_get_end_time( $event_id, false, $format );
		}
		return '';
	}

	private function event_meta_datetime( $event_id, $meta_key, $format ) {
		$raw = (string) get_post_meta( $event_id, $meta_key, true );
		if ( '' === $raw ) {
			return '';
		}

		try {
			$datetime = new DateTimeImmutable( $raw, wp_timezone() );
		} catch ( Exception $exception ) {
			return '';
		}

		return wp_date( $format, $datetime->getTimestamp(), $datetime->getTimezone() );
	}

	private function event_time_range( $event_id, $format = 'H:i' ) {
		$start = $this->event_start_time( $event_id, $format );
		$end = $this->event_end_time( $event_id, $format );
		if ( $start && $end ) {
			return $start . ' — ' . $end;
		}
		if ( $start ) {
			return $start;
		}
		if ( $end ) {
			return $end;
		}
		return $this->tr( 'Horaire à confirmer', 'Time to be confirmed' );
	}
	private function wc_price( $price ) {
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $price );
		}
		$symbol = function_exists( 'tribe_get_cost_format_symbol' ) ? tribe_get_cost_format_symbol() : '€';
		return $symbol . number_format_i18n( (float) $price, 0 );
	}

	private function past_events( $limit = 4 ) {
		$limit = absint( $limit );
		if ( function_exists( 'tribe_get_events' ) ) {
			return tribe_get_events(
				[
					'posts_per_page' => $limit,
					'end_date'       => current_time( 'mysql' ),
					'orderby'        => 'event_date',
					'order'          => 'DESC',
				]
			);
		}

		return get_posts(
			[
				'post_type'      => 'tribe_events',
				'posts_per_page' => $limit,
				'post_status'    => 'publish',
				'meta_key'       => '_EventStartDate',
				'orderby'        => 'meta_value',
				'order'          => 'DESC',
			]
		);
	}

	public function shortcode_events_desktop_listing( $atts = [] ) {
		$this->enqueue_template_1_assets();
		$atts = shortcode_atts( [ 'limit' => absint( $this->get_setting( 'default_listing_limit', 6 ) ) ], $atts, 'lpk_events_desktop_listing' );
		$events = $this->upcoming_events( (int) $atts['limit'] );
		$past_events = $this->past_events( 2 );

		ob_start();
		?>
		<?php echo $this->template_1_page_bootstrap_script(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="lpk-events-template lpk-template-1 lpk-desktop-only">
			<main class="page">
				<section class="topbar surface">
					<h1><?php echo esc_html( $this->tr( 'Événements', 'Events' ) ); ?></h1>
					<p><?php echo esc_html( $this->tr( 'Découvrez nos activités spéciales et ateliers.', 'Discover our special activities and workshops.' ) ); ?></p>
				</section>

				<section class="controls surface">
					<input class="input" type="search" placeholder="<?php echo esc_attr( $this->tr( 'Rechercher un événement, une thématique...', 'Search for an event, theme...' ) ); ?>" />
					<button class="chip" type="button"><?php echo esc_html( $this->tr( 'Date', 'Date' ) ); ?></button>
					<button class="chip" type="button"><?php echo esc_html( $this->tr( 'Âge', 'Age' ) ); ?></button>
					<button class="chip" type="button"><?php echo esc_html( $this->tr( 'Type', 'Type' ) ); ?></button>
					<button class="chip" type="button"><?php echo esc_html( $this->tr( 'Prix', 'Price' ) ); ?></button>
					<select class="select" aria-label="<?php echo esc_attr( $this->tr( 'Trier', 'Sort' ) ); ?>">
						<option><?php echo esc_html( $this->tr( 'À venir', 'Upcoming' ) ); ?></option>
						<option><?php echo esc_html( $this->tr( 'Ce mois-ci', 'This month' ) ); ?></option>
						<option><?php echo esc_html( $this->tr( 'Le week-end', 'Weekend' ) ); ?></option>
					</select>
				</section>

				<div class="section-title">
					<h2><?php echo esc_html( $this->tr( 'Prochains événements', 'Upcoming events' ) ); ?></h2>
					<span class="helper"><?php echo esc_html( $this->tr( 'Pensé pour conversion billets + réservations rapides', 'Designed for ticket conversion and fast reservations' ) ); ?></span>
				</div>

				<section class="card-grid">
					<?php foreach ( $events as $event ) : $event_id = $event->ID; $detail_url = $this->detail_page_url_for_event( $event_id ); ?>
						<article class="event-card surface">
							<div class="event-image" style="background-image: url('<?php echo esc_url( get_the_post_thumbnail_url( $event_id, 'large' ) ?: 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=1200&q=80' ); ?>')">
								<div class="date-badge"><strong><?php echo esc_html( $this->event_start_date( $event_id, 'd' ) ); ?></strong><span><?php echo esc_html( $this->event_start_date( $event_id, 'M' ) ); ?></span></div>
							</div>
							<div class="event-content">
								<h3><?php echo esc_html( get_the_title( $event_id ) ); ?></h3>
								<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $event_id ) ?: get_post_field( 'post_content', $event_id ) ), 16 ) ); ?></p>
								<div class="meta"><span><?php echo esc_html( $this->event_time_range( $event_id, 'H:i' ) ); ?></span><span><?php echo esc_html( $this->starting_price( $event_id ) ); ?></span></div>
							<div class="actions"><a class="btn btn-primary" href="<?php echo esc_url( $detail_url ); ?>"><?php echo esc_html( $this->tr( 'Réserver', 'Book' ) ); ?></a><a class="btn btn-secondary" href="<?php echo esc_url( $detail_url ); ?>"><?php echo esc_html( $this->tr( 'Voir', 'View' ) ); ?></a></div>
							</div>
						</article>
					<?php endforeach; ?>
					<?php if ( empty( $events ) ) : ?>
						<section class="empty-state surface">
							<h3><?php echo esc_html( $this->tr( 'Aucun événement trouvé', 'No events found' ) ); ?></h3>
							<p class="helper"><?php echo esc_html( $this->tr( 'Aucun événement n\'est disponible pour le moment.', 'No events are available at the moment.' ) ); ?></p>
						</section>
					<?php endif; ?>
				</section>

				<details class="past-events surface">
					<summary><?php echo esc_html( $this->tr( 'Événements passés', 'Past events' ) ); ?></summary>
					<div class="past-grid">
						<?php foreach ( $past_events as $past_event ) : ?>
							<div class="past-item"><span><?php echo esc_html( get_the_title( $past_event->ID ) ); ?></span><span><?php echo esc_html( $this->tr( 'Terminé', 'Finished' ) ); ?></span></div>
						<?php endforeach; ?>
					</div>
				</details>
			</main>
		</div>
		<?php
		return ob_get_clean();
	}

	public function shortcode_event_desktop_detail( $atts = [] ) {
		$this->enqueue_template_1_assets();
		$event_id_from_query = isset( $_GET['lpk_event_id'] ) ? absint( wp_unslash( $_GET['lpk_event_id'] ) ) : 0;
		$atts = shortcode_atts( [ 'id' => $event_id_from_query ?: get_the_ID() ], $atts, 'lpk_event_desktop_detail' );
		$event_id = $this->resolve_event_id( (int) $atts['id'] );
		if ( ! $event_id ) {
			return '<p>' . esc_html( $this->tr( 'Aucun événement avec billets trouvé.', 'No ticketed event found.' ) ) . '</p>';
		}
		$tickets = $this->event_tickets( $event_id );
		$map_address = (string) $this->get_setting( 'map_address', '869 Bd Saint-Jean-Baptiste local 100, Mercier, QC J6R 2K8' );
		$map_url = (string) $this->get_setting( 'map_url', 'https://share.google/NFz7TfIdr2vS7lwO3' );
		$map_image_url = $this->map_image_url();
		$location_text = $this->event_location_text( $event_id, $map_address );
		$ticket_form_shortcode = trim( (string) $this->get_setting( 'ticket_form_shortcode', '' ) );
		$ticket_form_html = '';

		if ( '' !== $ticket_form_shortcode ) {
			$ticket_form_html = do_shortcode( $ticket_form_shortcode );
		}

		if ( '' === trim( wp_strip_all_tags( (string) $ticket_form_html ) ) ) {
			$ticket_form_html = do_shortcode( sprintf( '[tribe_tickets post_id="%d"]', (int) $event_id ) );
		}

		$event_permalink = get_permalink( $event_id );
		$calendar_url = function_exists( 'tribe_get_gcal_link' ) ? tribe_get_gcal_link( $event_id ) : $event_permalink;

		ob_start();
		?>
		<?php echo $this->template_1_page_bootstrap_script(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="lpk-events-template lpk-template-1 lpk-desktop-only">
			<main class="page">
				<section class="hero" style="background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( $event_id, 'large' ) ?: 'https://images.unsplash.com/photo-1472162072942-cd5147eb3902?auto=format&fit=crop&w=1500&q=80' ); ?>')">
					<div class="hero-content">
						<span class="pill"><?php echo esc_html( $this->event_start_date( $event_id, 'l j F' ) . ' · ' . $this->event_time_range( $event_id, 'H:i' ) ); ?></span>
						<h1><?php echo esc_html( get_the_title( $event_id ) ); ?></h1>
					</div>
				</section>
				<section class="info-bar surface">
					<div class="info-item"><strong><?php echo esc_html( $this->tr( 'Date', 'Date' ) ); ?></strong><?php echo esc_html( $this->event_start_date( $event_id, 'j M Y' ) ); ?></div>
					<div class="info-item"><strong><?php echo esc_html( $this->tr( 'Heure', 'Time' ) ); ?></strong><?php echo esc_html( $this->event_time_range( $event_id, 'H:i' ) ); ?></div>
					<div class="info-item"><strong><?php echo esc_html( $this->tr( 'Durée', 'Duration' ) ); ?></strong><?php echo esc_html( $this->tr( 'Selon programme', 'As scheduled' ) ); ?></div>
					<div class="info-item"><strong><?php echo esc_html( $this->tr( 'Prix', 'Price' ) ); ?></strong><?php echo esc_html( $this->starting_price( $event_id ) ); ?></div>
					<div class="info-item"><strong><?php echo esc_html( $this->tr( 'Places limitées', 'Limited spots' ) ); ?></strong><?php echo esc_html( sprintf( $this->tr( '%d type(s) de billets', '%d ticket type(s)' ), count( $tickets ) ) ); ?></div>
				</section>
				<div class="detail-layout">
					<div style="display:grid; gap:14px;">
						<section class="section surface">
							<div class="actions"><a class="btn btn-primary" style="font-size:1rem; padding: 13px 20px;" href="#lpk-ticket-pro-form"><?php echo esc_html( $this->tr( 'Réserver maintenant', 'Book now' ) ); ?></a><a class="btn btn-secondary" href="<?php echo esc_url( $calendar_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $this->tr( 'Ajouter au calendrier', 'Add to calendar' ) ); ?></a><button class="btn btn-secondary" type="button" data-lpk-share-url="<?php echo esc_url( $event_permalink ); ?>" data-copied-label="<?php echo esc_attr( $this->tr( "Lien copié", "Link copied" ) ); ?>"><?php echo esc_html( $this->tr( 'Partager', 'Share' ) ); ?></button></div>
						</section>
						<section class="section surface"><h3>🧸 <?php echo esc_html( $this->tr( 'Description', 'Description' ) ); ?></h3><div class="lpk-description"><?php echo wp_kses_post( $this->event_description_html( $event_id ) ); ?></div></section>
						<section class="section surface"><h3>🎉 <?php echo esc_html( $this->tr( 'Au programme', 'Program' ) ); ?></h3><ul><?php foreach ( $tickets as $ticket ) : ?><li><?php echo esc_html( $ticket->name . ' — ' . wp_strip_all_tags( $this->wc_price( (float) $ticket->price ) ) ); ?></li><?php endforeach; ?></ul></section>
						<section class="section surface"><h3>🎁 <?php echo esc_html( $this->tr( 'Inclus', 'Included' ) ); ?></h3><p><?php echo esc_html( $this->tr( 'Animation, accompagnement et accès aux billets disponibles via Event Tickets.', 'Animation, guidance and access to available tickets via Event Tickets.' ) ); ?></p></section>
					</div>
					<aside style="display:grid; gap:14px; align-content:start;">
						<section class="section surface"><h3>👶 <?php echo esc_html( $this->tr( 'Pour qui', 'Who is it for' ) ); ?></h3><p><?php echo esc_html( $this->tr( 'Consultez la description de l’événement pour les détails d’âge.', 'Please check the event description for age details.' ) ); ?></p></section>
						<section class="section surface"><h3>📍 <?php echo esc_html( $this->tr( 'Lieu', 'Location' ) ); ?></h3><p><?php echo esc_html( $location_text ); ?></p><?php if ( ! empty( $map_url ) ) : ?><a class="map-placeholder<?php echo $map_image_url ? ' has-image' : ''; ?>" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer"<?php echo $map_image_url ? ' style="background-image:url(\'' . esc_url( $map_image_url ) . '\');"' : ''; ?>><?php echo esc_html( $this->tr( 'Ouvrir la carte', 'Open map' ) ); ?></a><?php else : ?><div class="map-placeholder<?php echo $map_image_url ? ' has-image' : ''; ?>"<?php echo $map_image_url ? ' style="background-image:url(\'' . esc_url( $map_image_url ) . '\');"' : ''; ?>><?php echo esc_html( $this->tr( 'Plan / Map placeholder', 'Plan / Map placeholder' ) ); ?></div><?php endif; ?></section>
					</aside>
				</div>
				<section id="lpk-ticket-pro-form" class="section surface">
					<h3>🎟️ <?php echo esc_html( $this->tr( 'Billets', 'Tickets' ) ); ?></h3>
					<?php echo $ticket_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</section>
			</main>
		</div>
		<?php
		return ob_get_clean();
	}

	public function shortcode_events_mobile_listing( $atts = [] ) {
		$this->enqueue_template_3_assets();
		$atts = shortcode_atts( [ 'limit' => absint( $this->get_setting( 'default_listing_limit', 6 ) ) ], $atts, 'lpk_events_mobile_listing' );
		$events = $this->upcoming_events( (int) $atts['limit'] );

		ob_start();
		?>
		<div class="lpk-events-template lpk-template-3 lpk-mobile-only" data-currency-code="<?php echo esc_attr( function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'CAD' ); ?>" data-currency-locale="<?php echo esc_attr( $this->is_english() ? 'en-CA' : 'fr-CA' ); ?>" data-checkout-url="<?php echo esc_url( function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ) ); ?>" data-add-to-cart-url="<?php echo esc_url( home_url( '/?wc-ajax=add_to_cart' ) ); ?>">
			<main class="page">
				<header class="mobile-top">
					<div class="search-row">
						<input class="search-input" type="search" placeholder="<?php echo esc_attr( $this->tr( 'Rechercher un événement', 'Search an event' ) ); ?>" aria-label="<?php echo esc_attr( $this->tr( 'Rechercher un événement', 'Search an event' ) ); ?>" />
					</div>
					<div class="filter-chips" role="tablist" aria-label="<?php echo esc_attr( $this->tr( 'Filtres événements', 'Event filters' ) ); ?>">
						<button class="chip is-active" type="button" data-filter="all"><?php echo esc_html( $this->tr( 'Tous', 'All' ) ); ?></button>
						<button class="chip" type="button" data-filter="aloha"><?php echo esc_html( $this->tr( 'Aloha & spéciaux', 'Aloha & specials' ) ); ?></button>
						<button class="chip" type="button" data-filter="workshops"><?php echo esc_html( $this->tr( 'Ateliers', 'Workshops' ) ); ?></button>
						<button class="chip" type="button" data-filter="birthdays"><?php echo esc_html( $this->tr( 'Anniversaires', 'Birthdays' ) ); ?></button>
					</div>
				</header>

				<section class="content" aria-label="<?php echo esc_attr( $this->tr( 'Événements à venir', 'Upcoming events' ) ); ?>">
					<?php foreach ( $events as $event ) : $event_id = $event->ID; ?>
						<?php
						$event_title = (string) get_the_title( $event_id );
						$filter_keys = [ 'all' ];
						$search_terms = [ sanitize_title( $event_title ) ];
						$event_terms = get_the_terms( $event_id, 'tribe_events_cat' );

						if ( ! is_wp_error( $event_terms ) && ! empty( $event_terms ) ) {
							foreach ( $event_terms as $event_term ) {
								$term_name = sanitize_title( (string) $event_term->name );
								$term_slug = sanitize_title( (string) $event_term->slug );
								$search_terms[] = $term_name;
								$search_terms[] = $term_slug;

								if ( false !== strpos( $term_name, 'aloha' ) || false !== strpos( $term_slug, 'aloha' ) || false !== strpos( $term_name, 'special' ) || false !== strpos( $term_slug, 'special' ) || false !== strpos( $term_name, 'sp-ciaux' ) || false !== strpos( $term_slug, 'sp-ciaux' ) ) {
									$filter_keys[] = 'aloha';
								}

								if ( false !== strpos( $term_name, 'atelier' ) || false !== strpos( $term_slug, 'atelier' ) || false !== strpos( $term_name, 'workshop' ) || false !== strpos( $term_slug, 'workshop' ) ) {
									$filter_keys[] = 'workshops';
								}

								if ( false !== strpos( $term_name, 'anniversaire' ) || false !== strpos( $term_slug, 'anniversaire' ) || false !== strpos( $term_name, 'birthday' ) || false !== strpos( $term_slug, 'birthday' ) ) {
									$filter_keys[] = 'birthdays';
								}
							}
						}

						$title_slug = sanitize_title( $event_title );
						if ( false !== strpos( $title_slug, 'aloha' ) || false !== strpos( $title_slug, 'special' ) || false !== strpos( $title_slug, 'sp-cial' ) ) {
							$filter_keys[] = 'aloha';
						}
						if ( false !== strpos( $title_slug, 'atelier' ) || false !== strpos( $title_slug, 'workshop' ) ) {
							$filter_keys[] = 'workshops';
						}
						if ( false !== strpos( $title_slug, 'anniversaire' ) || false !== strpos( $title_slug, 'birthday' ) ) {
							$filter_keys[] = 'birthdays';
						}

						$filter_keys = array_values( array_unique( $filter_keys ) );
						$search_blob = implode( ' ', array_filter( array_unique( $search_terms ) ) );
						?>
						<article class="card event-card" data-event-filters="<?php echo esc_attr( implode( ' ', $filter_keys ) ); ?>" data-event-search="<?php echo esc_attr( $search_blob ); ?>">
							<div class="thumb"><img src="<?php echo esc_url( get_the_post_thumbnail_url( $event_id, 'medium_large' ) ?: 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=500&q=80' ); ?>" alt="<?php echo esc_attr( get_the_title( $event_id ) ); ?>" /></div>
							<div>
								<div class="meta-line">
									<span class="date-pill"><?php echo esc_html( $this->event_start_date( $event_id, 'D d M' ) ); ?></span>
									<span class="status"><?php echo esc_html( $this->tr( 'Billets ouverts', 'Tickets open' ) ); ?></span>
								</div>
								<h2 class="event-title"><?php echo esc_html( get_the_title( $event_id ) ); ?></h2>
								<p class="event-info"><span><?php echo esc_html( $this->event_time_range( $event_id, 'H:i' ) ); ?></span> · <span><?php echo esc_html( $this->starting_price( $event_id ) ); ?></span></p>
								<a class="btn-primary" href="<?php echo esc_url( $this->detail_page_url_for_event( $event_id ) ); ?>"><?php echo esc_html( $this->tr( 'Réserver', 'Book' ) ); ?></a>
							</div>
						</article>
					<?php endforeach; ?>

					<?php if ( empty( $events ) ) : ?>
						<section class="card empty-state" aria-label="<?php echo esc_attr( $this->tr( 'État vide', 'Empty state' ) ); ?>">
							<h2><?php echo esc_html( $this->tr( 'Aucun événement dans ce filtre', 'No events in this filter' ) ); ?></h2>
							<p><?php echo esc_html( $this->tr( 'Essayez “Tous” pour voir les prochains rendez-vous.', 'Try “All” to see upcoming events.' ) ); ?></p>
						</section>
					<?php endif; ?>
				</section>
			</main>
		</div>
		<?php
		return ob_get_clean();
	}

	public function shortcode_event_mobile_detail( $atts = [] ) {
		$this->enqueue_template_3_assets();
		$event_id_from_query = isset( $_GET['lpk_event_id'] ) ? absint( wp_unslash( $_GET['lpk_event_id'] ) ) : 0;
		$atts = shortcode_atts( [ 'id' => $event_id_from_query ?: get_the_ID() ], $atts, 'lpk_event_mobile_detail' );
		$event_id = $this->resolve_event_id( $atts['id'] );
		if ( ! $event_id ) {
			return '<p>' . esc_html( $this->tr( 'Aucun événement disponible.', 'No event available.' ) ) . '</p>';
		}

		$tickets = $this->event_tickets( $event_id );
		$map_address = (string) $this->get_setting( 'map_address', '869 Bd Saint-Jean-Baptiste local 100, Mercier, QC J6R 2K8' );
		$map_url = (string) $this->get_setting( 'map_url', 'https://share.google/NFz7TfIdr2vS7lwO3' );
		$map_image_url = $this->map_image_url();
		$location_text = $this->event_location_text( $event_id, $map_address );
		$ticket_form_shortcode = trim( (string) $this->get_setting( 'ticket_form_shortcode', '' ) );
		$ticket_form_html = '';

		if ( '' !== $ticket_form_shortcode ) {
			$ticket_form_html = do_shortcode( $ticket_form_shortcode );
		}

		if ( '' === trim( wp_strip_all_tags( (string) $ticket_form_html ) ) ) {
			$ticket_form_html = do_shortcode( sprintf( '[tribe_tickets post_id="%d"]', (int) $event_id ) );
		}

		$event_permalink = get_permalink( $event_id );
		$calendar_url = function_exists( 'tribe_get_gcal_link' ) ? tribe_get_gcal_link( $event_id ) : $event_permalink;

		ob_start();
		?>
		<div class="lpk-events-template lpk-template-3 lpk-mobile-only" data-currency-code="<?php echo esc_attr( function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'CAD' ); ?>" data-currency-locale="<?php echo esc_attr( $this->is_english() ? 'en-CA' : 'fr-CA' ); ?>">
			<main class="page">
				<section class="hero card hero-edge">
					<img src="<?php echo esc_url( get_the_post_thumbnail_url( $event_id, 'large' ) ?: 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=1200&q=80' ); ?>" alt="<?php echo esc_attr( get_the_title( $event_id ) ); ?>" />
					<span class="hero-badge"><?php echo esc_html( $this->event_badge_label( $event_id ) ); ?></span>
				</section>
				<section class="detail-head card card-margin">
					<h1><?php echo esc_html( get_the_title( $event_id ) ); ?></h1>
					<div class="kpis">
						<span><?php echo esc_html( $this->event_start_date( $event_id, 'l j F' ) . ' · ' . $this->event_time_range( $event_id, 'H:i' ) ); ?></span>
						<span><?php echo esc_html( $location_text ); ?></span>
						<span><strong class="kpi-highlight"><?php echo esc_html( $this->starting_price( $event_id ) ); ?></strong> · <?php echo esc_html( $this->tr( 'Billets disponibles', 'Tickets available' ) ); ?></span>
					</div>
					<div class="head-actions">
						<a class="btn-light" href="<?php echo esc_url( $calendar_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $this->tr( 'Ajouter au calendrier', 'Add to calendar' ) ); ?></a>
						<button class="btn-primary" type="button" data-open-sheet><?php echo esc_html( $this->tr( 'Choisir mes billets', 'Choose my tickets' ) ); ?></button>
					</div>
				</section>

				<section class="sections">
					<article class="card section-card">
						<h3>🧸 <?php echo esc_html( $this->tr( 'Description', 'Description' ) ); ?></h3>
						<div class="desc-preview" data-description-preview><?php echo wp_kses_post( $this->event_description_html( $event_id ) ); ?></div>
						<button class="desc-toggle" type="button" data-description-toggle data-less-label="<?php echo esc_attr( $this->tr( 'Voir moins', 'Read less' ) ); ?>" hidden><?php echo esc_html( $this->tr( 'Voir plus', 'Read more' ) ); ?></button>
					</article>

					<?php if ( ! empty( $tickets ) ) : ?>
						<article class="card section-card">
							<h3>🎉 <?php echo esc_html( $this->tr( 'Au programme', 'Program' ) ); ?></h3>
							<ul>
								<?php foreach ( $tickets as $ticket ) : ?>
									<li><?php echo esc_html( $ticket->name . ' — ' . wp_strip_all_tags( $this->wc_price( (float) $ticket->price ) ) ); ?></li>
								<?php endforeach; ?>
							</ul>
						</article>
					<?php endif; ?>

					<article class="card section-card">
						<h3>📍 <?php echo esc_html( $this->tr( 'Lieu', 'Location' ) ); ?></h3>
						<p><?php echo esc_html( $location_text ); ?></p>
						<?php if ( ! empty( $map_url ) ) : ?>
							<a class="map-placeholder<?php echo $map_image_url ? ' has-image' : ''; ?>" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer"<?php echo $map_image_url ? ' style="background-image:url(\'' . esc_url( $map_image_url ) . '\');"' : ''; ?>><?php echo esc_html( $this->tr( 'Ouvrir la carte', 'Open map' ) ); ?></a>
						<?php else : ?>
							<div class="map-placeholder<?php echo $map_image_url ? ' has-image' : ''; ?>"<?php echo $map_image_url ? ' style="background-image:url(\'' . esc_url( $map_image_url ) . '\');"' : ''; ?>><?php echo esc_html( $this->tr( 'Plan / Map placeholder', 'Plan / Map placeholder' ) ); ?></div>
						<?php endif; ?>
					</article>
				</section>
			</main>

			<div class="sticky-bottom">
				<div class="price"><strong><?php echo esc_html( $this->starting_price( $event_id ) ); ?></strong><span><?php echo esc_html( $this->tr( 'prix de départ', 'starting price' ) ); ?></span></div>
				<button class="btn-primary" type="button" data-open-sheet><?php echo esc_html( $this->tr( 'Réserver', 'Book' ) ); ?></button>
			</div>

			<div class="sheet-overlay" data-close-sheet></div>
			<aside class="sheet" aria-label="<?php echo esc_attr( $this->tr( 'Sélection de billets', 'Ticket selection' ) ); ?>" aria-hidden="true">
				<div class="handle"></div>
				<h2 class="sheet-title"><?php echo esc_html( $this->tr( 'Sélection des billets', 'Ticket selection' ) ); ?></h2>
				<?php if ( ! empty( $tickets ) ) : ?>
					<?php foreach ( $tickets as $ticket ) : ?>
						<article class="ticket-row" data-ticket-id="<?php echo esc_attr( (string) $ticket->ID ); ?>" data-price="<?php echo esc_attr( (string) (float) $ticket->price ); ?>">
							<div>
								<div class="ticket-name"><?php echo esc_html( $ticket->name ); ?></div>
								<div class="ticket-price"><?php echo esc_html( wp_strip_all_tags( $this->wc_price( (float) $ticket->price ) ) ); ?></div>
							</div>
							<div class="qty">
								<button type="button" data-minus>-</button>
								<output>0</output>
								<button type="button" data-plus>+</button>
							</div>
						</article>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="ticket-empty"><?php echo esc_html( $this->tr( 'Billets indisponibles pour le moment.', 'Tickets are unavailable at the moment.' ) ); ?></div>
				<?php endif; ?>
				<div class="total">
					<span><?php echo esc_html( $this->tr( 'Total', 'Total' ) ); ?></span>
					<span data-total><?php echo esc_html( wp_strip_all_tags( $this->wc_price( 0 ) ) ); ?></span>
				</div>
				<div class="sheet-form"><?php echo $ticket_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</aside>
		</div>
		<?php
		return ob_get_clean();
	}


}

LPK_Events_Templates::instance();
