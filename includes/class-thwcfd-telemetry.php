<?php
/**
 * Telemetry and Analytics Tracker for Checkout Field by Emon
 *
 * Sends non-blocking background pings on activation, deactivation,
 * and daily heartbeat to log active installations and domain names.
 * Features an explicit opt-in banner compliant with WordPress.org guidelines.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class THWCFD_Telemetry {

	/** @var string Telemetry endpoint URL (User can edit this to match their Vercel URL) */
	private $api_url = 'https://checkout-field-analytics.vercel.app/api/ping';

	/** @var string Secret API Key for validating request authenticity on the server */
	private $secret_key = 'emon-secret-telemetry-key';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Hook into the daily cron heartbeat
		add_action( 'thwcfd_telemetry_heartbeat', array( $this, 'send_heartbeat' ) );

		if ( is_admin() ) {
			// Hook to show opt-in notice banner
			add_action( 'admin_notices', array( $this, 'display_optin_notice' ) );
			// Hook to process opt-in/skip choices
			add_action( 'admin_init', array( $this, 'handle_optin_choice' ) );
		}
	}

	/**
	 * Initialize telemetry hooks and schedules.
	 */
	public static function init() {
		$instance = new self();

		// Register activation and deactivation hooks
		register_activation_hook( THWCFD_BASE_NAME, array( $instance, 'activate' ) );
		register_deactivation_hook( THWCFD_BASE_NAME, array( $instance, 'deactivate' ) );
	}

	/**
	 * Fired on plugin activation.
	 */
	public function activate() {
		// Schedule daily heartbeat check if not already scheduled
		if ( ! wp_next_scheduled( 'thwcfd_telemetry_heartbeat' ) ) {
			wp_schedule_event( time(), 'daily', 'thwcfd_telemetry_heartbeat' );
		}
	}

	/**
	 * Fired on plugin deactivation.
	 */
	public function deactivate() {
		// Remove scheduled cron heartbeat
		wp_clear_scheduled_hook( 'thwcfd_telemetry_heartbeat' );

		// Only send deactivation ping if the user opted-in
		$optin = get_option( 'thwcfd_telemetry_optin' );
		if ( $optin === 'yes' ) {
			$this->send_ping( 'deactivate', true );
		}
	}

	/**
	 * Fired by daily cron event.
	 */
	public function send_heartbeat() {
		$this->send_ping( 'heartbeat' );
	}

	/**
	 * Display the opt-in admin notice banner.
	 */
	public function display_optin_notice() {
		// Only show to users who can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if they already made a choice
		$optin = get_option( 'thwcfd_telemetry_optin' );
		if ( $optin !== false ) {
			return;
		}

		$allow_url = wp_nonce_url( add_query_arg( 'thwcfd_telemetry_opt', 'allow' ), 'thwcfd_telemetry_opt_action' );
		$skip_url  = wp_nonce_url( add_query_arg( 'thwcfd_telemetry_opt', 'skip' ), 'thwcfd_telemetry_opt_action' );
		?>
		<div class="notice notice-info is-dismissible" style="border-left-color: #6366f1; padding: 18px 24px; border-radius: 8px; margin: 15px 2px 15px 0; background: #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
			<div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
				<div style="font-size: 28px; line-height: 1;">⚙️</div>
				<div style="flex: 1; min-width: 250px;">
					<h4 style="margin: 0 0 5px 0; color: #1e293b; font-size: 15px; font-weight: 700;">Help Us Improve Checkout Field by Emon!</h4>
					<p style="margin: 0; color: #64748b; font-size: 13px; line-height: 1.5;">
						Would you like to share non-sensitive, anonymous telemetry data (such as active theme, active plugins count, shop orders count, and custom fields count) to help us improve the plugin and squash bugs faster? No customer personal data is ever collected.
					</p>
				</div>
				<div style="display: flex; gap: 8px;">
					<a href="<?php echo esc_url( $allow_url ); ?>" class="button button-primary" style="background: #6366f1; border-color: #6366f1; color: #fff; font-weight: 600; padding: 4px 14px; height: auto; border-radius: 4px; box-shadow: none; text-shadow: none;">Allow & Continue</a>
					<a href="<?php echo esc_url( $skip_url ); ?>" class="button" style="border-color: #cbd5e1; color: #64748b; font-weight: 600; padding: 4px 14px; height: auto; border-radius: 4px;">Skip</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle admin choices.
	 */
	public function handle_optin_choice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_GET['thwcfd_telemetry_opt'] ) || ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'thwcfd_telemetry_opt_action' ) ) {
			return;
		}

		$choice = sanitize_text_field( $_GET['thwcfd_telemetry_opt'] );
		if ( $choice === 'allow' ) {
			update_option( 'thwcfd_telemetry_optin', 'yes' );
			// Send the initial activation ping immediately
			$this->send_ping( 'activate' );
		} elseif ( $choice === 'skip' ) {
			update_option( 'thwcfd_telemetry_optin', 'no' );
		}

		// Redirect back cleanly without query params
		wp_safe_redirect( remove_query_arg( array( 'thwcfd_telemetry_opt', '_wpnonce' ) ) );
		exit;
	}

	/**
	 * Sends the telemetry request to the remote server.
	 *
	 * @param string $action   The action being performed ('activate', 'deactivate', 'heartbeat').
	 * @param bool   $blocking Whether to block execution waiting for a response (defaults to false for speed).
	 */
	private function send_ping( $action, $blocking = false ) {
		global $wp_version;

		// Check if user has opted-in
		$optin = get_option( 'thwcfd_telemetry_optin' );
		if ( $optin !== 'yes' ) {
			return;
		}

		// Determine WooCommerce version and orders
		$wc_version = '';
		$orders_count = 0;
		if ( class_exists( 'WooCommerce' ) ) {
			$wc_version = WC()->version;
			$orders_count = (int) wc_orders_count( 'completed' ) + (int) wc_orders_count( 'processing' );
		}

		// Count custom fields
		$fields_billing = get_option('wc_fields_billing', array());
		$fields_shipping = get_option('wc_fields_shipping', array());
		$fields_additional = get_option('wc_fields_additional', array());
		$custom_fields_count = count((array)$fields_billing) + count((array)$fields_shipping) + count((array)$fields_additional);

		// Active plugins and theme
		$active_plugins_count = count( (array) get_option( 'active_plugins', array() ) );
		$active_theme = wp_get_theme()->get( 'Name' );

		// Prepare telemetry payload
		$payload = array(
			'domain'               => esc_url( home_url() ),
			'plugin_version'       => defined( 'THWCFD_VERSION' ) ? THWCFD_VERSION : '1.2.1',
			'wp_version'           => $wp_version,
			'wc_version'           => $wc_version,
			'php_version'          => PHP_VERSION,
			'site_title'           => get_bloginfo( 'name' ),
			'locale'               => get_locale(),
			'active_theme'         => $active_theme,
			'active_plugins_count' => $active_plugins_count,
			'orders_count'         => $orders_count,
			'custom_fields_count'  => $custom_fields_count,
			'action'               => $action,
			'secret_key'           => $this->secret_key,
			'timestamp'            => time()
		);

		// Send non-blocking remote post request to prevent any dashboard lag
		wp_remote_post( $this->api_url, array(
			'method'      => 'POST',
			'timeout'     => $blocking ? 10 : 3, // very short timeout for non-blocking
			'blocking'    => $blocking,         // false means fire-and-forget
			'redirection' => 5,
			'httpversion' => '1.0',
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
			'body'        => json_encode( $payload ),
			'cookies'     => array(),
			'sslverify'   => false, // avoid SSL handshake issues on some servers
		) );
	}
}
