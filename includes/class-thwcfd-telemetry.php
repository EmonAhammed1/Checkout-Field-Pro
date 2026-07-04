<?php
/**
 * Telemetry and Analytics Tracker for Checkout Field by Emon
 *
 * Sends non-blocking background pings on activation, deactivation,
 * and daily heartbeat to log active installations and domain names.
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
	}

	/**
	 * Initialize telemetry hooks and schedules.
	 */
	public static function init() {
		$instance = new self();

		// Register activation and deactivation hooks
		// Since this class is loaded, we can reference the main plugin file
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

		// Send activation ping immediately
		$this->send_ping( 'activate' );
	}

	/**
	 * Fired on plugin deactivation.
	 */
	public function deactivate() {
		// Remove scheduled cron heartbeat
		wp_clear_scheduled_hook( 'thwcfd_telemetry_heartbeat' );

		// Send deactivation ping immediately (this one is blocking so it completes before deactivation finishes)
		$this->send_ping( 'deactivate', true );
	}

	/**
	 * Fired by daily cron event.
	 */
	public function send_heartbeat() {
		$this->send_ping( 'heartbeat' );
	}

	/**
	 * Sends the telemetry request to the remote server.
	 *
	 * @param string $action   The action being performed ('activate', 'deactivate', 'heartbeat').
	 * @param bool   $blocking Whether to block execution waiting for a response (defaults to false for speed).
	 */
	private function send_ping( $action, $blocking = false ) {
		global $wp_version;

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
			'plugin_version'       => defined( 'THWCFD_VERSION' ) ? THWCFD_VERSION : '1.1.0',
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
