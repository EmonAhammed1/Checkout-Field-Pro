<?php
/**
 * GitHub Plugin Updater class.
 * Handles automatic updates directly from a GitHub repository.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Emon_Plugin_Updater {

	private $file;
	private $slug;
	private $username;
	private $repo;
	private $github_response;

	/**
	 * Constructor.
	 */
	public function __construct( $file, $username, $repo ) {
		$this->file     = $file;
		$this->slug     = plugin_basename( $file );
		$this->username = $username;
		$this->repo     = $repo;

		// Check for updates by hooking into WordPress transients
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 20, 3 );
	}

	/**
	 * Get the latest release info from the GitHub API.
	 */
	private function get_github_info() {
		if ( ! empty( $this->github_response ) ) {
			return $this->github_response;
		}

		$url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases/latest";
		
		$args = array(
			'headers' => array(
				'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' )
			)
		);

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		if ( ! empty( $data ) && ! isset( $data->message ) ) {
			$this->github_response = $data;
			return $data;
		}

		return false;
	}

	/**
	 * Inject update information into WordPress transient if a new version exists.
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$github_info = $this->get_github_info();
		if ( ! $github_info ) {
			return $transient;
		}

		$plugin_data = get_plugin_data( $this->file );
		$current_version = $plugin_data['Version'];
		$new_version = ltrim( $github_info->tag_name, 'v' );

		// Compare current plugin version with GitHub release tag
		if ( version_compare( $current_version, $new_version, '<' ) ) {
			$obj = new stdClass();
			$obj->slug        = dirname( $this->slug );
			$obj->plugin      = $this->slug;
			$obj->new_version = $new_version;
			$obj->url         = $github_info->html_url;
			$obj->package     = $github_info->zipball_url; // WordPress automatically downloads zipball from GitHub

			$transient->response[ $this->slug ] = $obj;
		}

		return $transient;
	}

	/**
	 * Inject information for the plugin details popup (changelog).
	 */
	public function plugin_popup( $result, $action, $args ) {
		if ( $action !== 'plugin_information' ) {
			return $result;
		}

		if ( ! isset( $args->slug ) || $args->slug !== dirname( $this->slug ) ) {
			return $result;
		}

		$github_info = $this->get_github_info();
		if ( ! $github_info ) {
			return $result;
		}

		$plugin_data = get_plugin_data( $this->file );

		$obj = new stdClass();
		$obj->name          = $plugin_data['Name'];
		$obj->slug          = dirname( $this->slug );
		$obj->plugin_name   = $plugin_data['Name'];
		$obj->version       = ltrim( $github_info->tag_name, 'v' );
		$obj->author        = $plugin_data['Author'];
		$obj->homepage      = $github_info->html_url;
		$obj->download_link = $github_info->zipball_url;
		
		// Use Markdown parsed body from GitHub release as the changelog tab content
		$changelog = isset( $github_info->body ) ? wp_kses_post( $github_info->body ) : 'New version released on GitHub.';
		
		$obj->sections = array(
			'description' => $plugin_data['Description'],
			'changelog'   => nl2br( $changelog )
		);

		return $obj;
	}
}
