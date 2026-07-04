<?php
/**
 * Plugin Name: Checkout Field by Emon
 * Description: Dynamically manage WooCommerce checkout fields. Created by Emon.
 * Author:      Emon
 * Version:     1.0.6
 * Author URI:  #
 * Plugin URI:  #
 * License:     GPLv2 or later
 * Text Domain: woo-checkout-field-editor-pro
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 10.8
 */
 
if(!defined( 'ABSPATH' )) exit;

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce');
	}
}

if(is_woocommerce_active()) {
	define('THWCFD_VERSION', '1.0.6');
	!defined('THWCFD_BASE_NAME') && define('THWCFD_BASE_NAME', plugin_basename( __FILE__ ));
	!defined('THWCFD_PATH') && define('THWCFD_PATH', plugin_dir_path( __FILE__ ));
	!defined('THWCFD_URL') && define('THWCFD_URL', plugins_url( '/', __FILE__ ));
	!defined('THWCFD_ABSPATH' ) && define( 'THWCFD_ABSPATH', __DIR__ );

	#require THWCFD_PATH . 'classes/class-thwcfd.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-thwcfd.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-emon-updater.php';

	function run_thwcfd() {
		$plugin = new THWCFD();
	}
	run_thwcfd();

	// ─── GitHub Auto-Updater ─────────────────────────────────────────────────
	// Change the two values below to match your GitHub username and repository name.
	// When you publish a new Release on GitHub with a tag like "v1.0.3",
	// WordPress will automatically show an "Update Available" notice.
	if ( is_admin() ) {
		new Emon_Plugin_Updater(
			__FILE__,
			'EmonAhammed1',      // GitHub username
			'Checkout-Field-Pro' // GitHub repository name
		);
	}
	// ─────────────────────────────────────────────────────────────────────────
}

if( ! function_exists( 'activate_thwcfd' ) ) {
	function activate_thwcfd($network_wide) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-thwcfd-activator.php';
		THWCFD_Activator::activate($network_wide);
	}
}
register_activation_hook( __FILE__, 'activate_thwcfd' );

add_action( 'before_woocommerce_init', 'thwcfd_before_woocommerce_init_hpos' ) ;

function thwcfd_before_woocommerce_init_hpos() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}