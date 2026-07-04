<?php
/**
 * GitHub Auto-Updater for Checkout Field by Emon
 *
 * Checks the GitHub Releases API and notifies WordPress
 * when a newer version is available, allowing one-click updates.
 *
 * Usage (in main plugin file):
 *   new Emon_Plugin_Updater( __FILE__, 'EmonAhammed1', 'Checkout-Field-Pro' );
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'debugPrint' ) ) {
	function debugPrint( $message ) {
		if ( WP_DEBUG === true ) {
			error_log( '[Emon Debug] ' . print_r( $message, true ) );
		}
	}
}

if ( ! class_exists( 'Emon_Plugin_Updater' ) ) :

class Emon_Plugin_Updater {

	/** @var string Absolute path to the main plugin file */
	private $plugin_file;

	/** @var string plugin/plugin.php */
	private $plugin_slug;

	/** @var string GitHub username */
	private $github_user;

	/** @var string GitHub repository name */
	private $github_repo;

	/** @var object|null Cached GitHub API response */
	private $github_data = null;

	/** @var string Transient key for caching */
	private $transient_key;

	/**
	 * Constructor — registers WordPress hooks.
	 *
	 * @param string $plugin_file  Absolute path to main plugin file (__FILE__).
	 * @param string $github_user  GitHub username.
	 * @param string $github_repo  GitHub repository name.
	 */
	public function __construct( $plugin_file, $github_user, $github_repo ) {
		$this->plugin_file   = $plugin_file;
		$this->plugin_slug   = plugin_basename( $plugin_file );
		$this->github_user   = $github_user;
		$this->github_repo   = $github_repo;
		$this->transient_key = 'emon_gh_update_' . md5( $this->plugin_slug );

		// Inject update data into WordPress update transient
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );

		// Supply plugin info for the "View details" popup
		add_filter( 'plugins_api', array( $this, 'plugin_info_popup' ), 20, 3 );

		// Clean up the downloaded package folder name so WordPress can activate it
		add_filter( 'upgrader_source_selection', array( $this, 'fix_source_dir' ), 10, 4 );

		// Delete our transient after a successful update so next check is fresh
		add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ), 10, 2 );

		// Force update check if requested
		if ( is_admin() && isset( $_GET['force-check'] ) && $_GET['force-check'] == 1 ) {
			add_action( 'admin_init', array( $this, 'force_wp_update_check' ) );
		}
	}

	// -------------------------------------------------------------------------
	// 1. FETCH LATEST RELEASE FROM GITHUB
	// -------------------------------------------------------------------------

	/**
	 * Fetch latest release data from GitHub API.
	 * Result is cached in a transient for 6 hours to avoid hammering the API.
	 *
	 * @return object|false  stdClass with release data, or false on failure.
	 */
	private function get_github_release() {
		if ( $this->github_data !== null ) {
			return $this->github_data;
		}

		// Clear cache if WordPress is forcing a check
		if ( isset( $_GET['force-check'] ) && $_GET['force-check'] == 1 ) {
			delete_transient( $this->transient_key );
		}

		// Try transient cache first
		$cached = get_transient( $this->transient_key );
		if ( $cached !== false ) {
			// If on Plugins page or our Settings page, check if cache is older than 5 minutes (300 seconds)
			$current_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
			$is_admin_screen = strpos( $current_uri, 'plugins.php' ) !== false || strpos( $current_uri, 'checkout_form_designer' ) !== false;
			$fetched_at = isset( $cached->emon_fetched_at ) ? $cached->emon_fetched_at : 0;

			if ( $is_admin_screen && ( time() - $fetched_at > 300 ) ) {
				delete_transient( $this->transient_key );
				debugPrint( '[Emon Updater] Admin screen cache bypass: cache is older than 5 minutes.' );
			} else {
				$this->github_data = $cached;
				return $this->github_data;
			}
		}

		$api_url  = sprintf(
			'https://api.github.com/repos/%s/%s/releases/latest',
			$this->github_user,
			$this->github_repo
		);

		$response = wp_remote_get( $api_url, array(
			'timeout'    => 15,
			'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			'headers'    => array(
				'Accept' => 'application/vnd.github.v3+json',
			),
		) );

		debugPrint( '[Emon Updater] GitHub API URL: ' . $api_url );

		if ( is_wp_error( $response ) ) {
			debugPrint( '[Emon Updater] GitHub API error: ' . $response->get_error_message() );
			return false;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		$body      = wp_remote_retrieve_body( $response );

		debugPrint( '[Emon Updater] GitHub API HTTP ' . $http_code );

		if ( $http_code !== 200 ) {
			debugPrint( '[Emon Updater] GitHub API response: ' . $body );
			return false;
		}

		$release = json_decode( $body );

		if ( empty( $release ) || ! isset( $release->tag_name ) ) {
			debugPrint( '[Emon Updater] Invalid GitHub release response.' );
			return false;
		}

		debugPrint( '[Emon Updater] Latest release: ' . $release->tag_name );

		// Set custom fetch timestamp
		$release->emon_fetched_at = time();

		// Cache for 6 hours
		set_transient( $this->transient_key, $release, 6 * HOUR_IN_SECONDS );
		$this->github_data = $release;

		return $release;
	}

	/**
	 * Extract a clean semantic version number from a tag (strips leading 'v').
	 *
	 * @param string $tag  e.g. "v1.0.3" or "1.0.3"
	 * @return string       e.g. "1.0.3"
	 */
	private function tag_to_version( $tag ) {
		return ltrim( trim( $tag ), 'vV' );
	}

	// -------------------------------------------------------------------------
	// 2. INJECT UPDATE INTO WORDPRESS TRANSIENT
	// -------------------------------------------------------------------------

	/**
	 * Hooked to `pre_set_site_transient_update_plugins`.
	 * Adds our plugin to the list of available updates when a newer version
	 * exists on GitHub.
	 *
	 * @param  object $transient  WordPress update transient.
	 * @return object
	 */
	public function check_for_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_github_release();
		if ( ! $release ) {
			return $transient;
		}

		$remote_version    = $this->tag_to_version( $release->tag_name );
		$installed_version = $transient->checked[ $this->plugin_slug ] ?? '';

		if ( empty( $installed_version ) ) {
			$plugin_data       = get_plugin_data( $this->plugin_file );
			$installed_version = $plugin_data['Version'];
		}

		debugPrint( sprintf(
			'[Emon Updater] Installed: %s | GitHub: %s',
			$installed_version,
			$remote_version
		) );

		if ( version_compare( $installed_version, $remote_version, '<' ) ) {
			// Prefer a direct zip asset attached to the release; fall back to zipball
			$package_url = $this->get_download_url( $release );

			$update_obj              = new stdClass();
			$update_obj->id          = $this->plugin_slug;
			$update_obj->slug        = dirname( $this->plugin_slug );
			$update_obj->plugin      = $this->plugin_slug;
			$update_obj->new_version = $remote_version;
			$update_obj->url         = $release->html_url;
			$update_obj->package     = $package_url;
			$update_obj->tested      = get_bloginfo( 'version' );
			$update_obj->requires    = '5.0';
			$update_obj->icons       = array();
			$update_obj->banners     = array();
			$update_obj->upgrade_notice = isset( $release->body ) ? wp_strip_all_tags( $release->body ) : '';

			$transient->response[ $this->plugin_slug ] = $update_obj;

			debugPrint( '[Emon Updater] Update available! Injected into transient.' );
		} else {
			debugPrint( '[Emon Updater] Plugin is up to date.' );
			// Mark as checked so WP shows "checked" status
			$no_update              = new stdClass();
			$no_update->id          = $this->plugin_slug;
			$no_update->slug        = dirname( $this->plugin_slug );
			$no_update->plugin      = $this->plugin_slug;
			$no_update->new_version = $remote_version;
			$no_update->url         = $release->html_url;
			$no_update->package     = '';
			$transient->no_update[ $this->plugin_slug ] = $no_update;
		}

		return $transient;
	}

	/**
	 * Get the best download URL for a GitHub release.
	 * Prefers a .zip asset attached to the release; falls back to the zipball.
	 *
	 * @param  object $release  GitHub release object.
	 * @return string
	 */
	private function get_download_url( $release ) {
		// Check if there is a zip asset directly attached to the release
		if ( ! empty( $release->assets ) && is_array( $release->assets ) ) {
			foreach ( $release->assets as $asset ) {
				if ( isset( $asset->browser_download_url )
				     && substr( $asset->name, -4 ) === '.zip' ) {
					return $asset->browser_download_url;
				}
			}
		}

		// Fall back to GitHub's auto-generated source zipball
		return sprintf(
			'https://api.github.com/repos/%s/%s/zipball/%s',
			$this->github_user,
			$this->github_repo,
			$release->tag_name
		);
	}

	// -------------------------------------------------------------------------
	// 3. PLUGIN INFO POPUP ("View details")
	// -------------------------------------------------------------------------

	/**
	 * Hooked to `plugins_api`.
	 * Fills the "View details" popup with data from the GitHub release.
	 *
	 * @param  false|object $result  Default value.
	 * @param  string       $action  API action.
	 * @param  object       $args    Request arguments.
	 * @return false|object
	 */
	public function plugin_info_popup( $result, $action, $args ) {
		if ( $action !== 'plugin_information' ) {
			return $result;
		}

		if ( ! isset( $args->slug ) || $args->slug !== dirname( $this->plugin_slug ) ) {
			return $result;
		}

		$release = $this->get_github_release();
		if ( ! $release ) {
			return $result;
		}

		$plugin_data = get_plugin_data( $this->plugin_file );
		$changelog   = isset( $release->body ) && ! empty( $release->body )
			? '<pre>' . esc_html( $release->body ) . '</pre>'
			: '<p>See <a href="' . esc_url( $release->html_url ) . '" target="_blank">GitHub release page</a> for details.</p>';

		$info                    = new stdClass();
		$info->name              = $plugin_data['Name'];
		$info->slug              = dirname( $this->plugin_slug );
		$info->version           = $this->tag_to_version( $release->tag_name );
		$info->author            = $plugin_data['Author'];
		$info->homepage          = $release->html_url;
		$info->download_link     = $this->get_download_url( $release );
		$info->requires          = '5.0';
		$info->tested            = get_bloginfo( 'version' );
		$info->last_updated      = isset( $release->published_at ) ? $release->published_at : '';
		$info->sections          = array(
			'description' => $plugin_data['Description'],
			'changelog'   => $changelog,
		);

		return $info;
	}

	// -------------------------------------------------------------------------
	// 4. FIX FOLDER NAME AFTER DOWNLOAD
	// -------------------------------------------------------------------------

	/**
	 * Hooked to `upgrader_source_selection`.
	 * GitHub zip files extract to a folder like "EmonAhammed1-Checkout-Field-Pro-abc1234/".
	 * WordPress needs the folder to match the plugin slug.
	 * This renames the folder to match.
	 *
	 * @param  string      $source        Extracted source path.
	 * @param  string      $remote_source Remote package path.
	 * @param  WP_Upgrader $upgrader      Upgrader instance.
	 * @param  array       $hook_extra    Extra hook data.
	 * @return string|WP_Error  Corrected source path.
	 */
	public function fix_source_dir( $source, $remote_source, $upgrader, $hook_extra = array() ) {
		global $wp_filesystem;

		// Only act when updating THIS plugin
		if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_slug ) {
			return $source;
		}

		// Target folder name = the directory part of the plugin slug (e.g. "checkout-field-by-emon")
		$target_dir  = trailingslashit( dirname( $source ) ) . dirname( $this->plugin_slug ) . '/';

		if ( $source === $target_dir ) {
			return $source; // already correct
		}

		if ( $wp_filesystem->move( $source, $target_dir ) ) {
			debugPrint( '[Emon Updater] Renamed source dir to: ' . $target_dir );
			return $target_dir;
		}

		return new WP_Error(
			'emon_updater_rename_failed',
			sprintf( 'Could not rename plugin directory from %s to %s', $source, $target_dir )
		);
	}

	// -------------------------------------------------------------------------
	// 5. CACHE MANAGEMENT
	// -------------------------------------------------------------------------

	/**
	 * Hooked to `upgrader_process_complete`.
	 * Clears our cached release data so the next check fetches fresh data.
	 *
	 * @param WP_Upgrader $upgrader   Upgrader instance.
	 * @param array       $hook_extra Extra data.
	 */
	public function clear_cache( $upgrader, $hook_extra ) {
		if ( isset( $hook_extra['plugins'] )
		     && in_array( $this->plugin_slug, (array) $hook_extra['plugins'], true ) ) {
			delete_transient( $this->transient_key );
			$this->github_data = null;
			debugPrint( '[Emon Updater] Cleared update cache after successful update.' );
		}
	}

	/**
	 * Force WordPress to clear plugin update transients.
	 */
	public function force_wp_update_check() {
		delete_site_transient( 'update_plugins' );
		wp_clean_plugins_cache();
	}
}

endif; // class_exists
