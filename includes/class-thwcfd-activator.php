<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD_Activator')):

class THWCFD_Activator {

	/**
	 * Copy older version settings if any.
	 *
	 * Use pro version settings if available, if no pro version settings found 
	 * check for free version settings and use it.
	 *
	 * - Check for premium version settings, if found do nothing. 
	 * - If no premium version settings found, then check for free version settings and copy it.
	 *
	 * @since    2.9.0
	 */
	public static function activate($network_wide) {
		self::store_plugin_since();
	}

    public static function store_plugin_since(){
       
		$thwcfd_since = get_option( 'thwcfd_since', false );
		if ($thwcfd_since === false) {
			set_transient('thwcfd_activation_redirect', true, 30);
		}
	}

}
endif;