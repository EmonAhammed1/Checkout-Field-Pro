<?php
/**
 * The common utility functionalities for the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.5.0
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD_Utils')):

class THWCFD_Utils {	
	const OPTION_KEY_ADVANCED_SETTINGS = 'thwcfd_advanced_settings';
	const OPTION_KEY_BILLING_FIELDS = 'wc_fields_billing';
	const OPTION_KEY_SHIPPING_FIELDS = 'wc_fields_shipping';
	const OPTION_KEY_ADDITIONAL_FIELDS = 'wc_fields_additional';

	public function __construct() {
		
	}

	public static function wcfd_capability() {
		$allowed = array('manage_woocommerce', 'manage_options');
		$capability = apply_filters('thwcfd_required_capability', 'manage_woocommerce');

		if(!in_array($capability, $allowed)){
			$capability = 'manage_woocommerce';
		}
		return $capability;
	}

	public static function is_address_field($name){
		$address_fields = array(
			'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city',
			'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_postcode', 'shipping_city',
		);

		if($name && in_array($name, $address_fields)){
			return true;
		}
		return false;
	}

	public static function is_default_field($name){
		$default_fields = array(
			'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city',
			'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_postcode', 'shipping_city',
			'order_comments'
		);

		if($name && in_array($name, $default_fields)){
			return true;
		}
		return false;
	}

	public static function is_default_field_name($field_name){
		$default_fields = array(
			'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 
			'billing_city', 'billing_state', 'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',
			'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 
			'shipping_city', 'shipping_state', 'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments'
		);

		if($name && in_array($name, $default_fields)){
			return true;
		}
		return false;
	}

	public static function is_reserved_field_name( $field_name ){
		$reserved_names = array(
			'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 
			'billing_city', 'billing_state', 'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',
			'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 
			'shipping_city', 'shipping_state', 'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments'
		);
		
		if($name && in_array($name, $reserved_names)){
			return true;
		}
		return false;
	}

	public static function is_valid_field($field){
		$return = false;
		if(is_array($field)){
			$return = true;
		}
		return $return;
	}

	public static function is_enabled($field){
		$enabled = false;
		if(is_array($field)){
			$enabled = isset($field['enabled']) && $field['enabled'] == false ? false : true;
		}
		return $enabled;
	}

	public static function is_custom_field($field){
		$return = false;
		if(isset($field['custom']) && $field['custom']){
			$return = true;
		}
		return $return;
	}

	public static function is_active_custom_field($field){
		$return = false;
		if(self::is_valid_field($field) && self::is_enabled($field) && self::is_custom_field($field)){
			$return = true;
		}
		return $return;
	}

	public static function is_wc_handle_custom_field($field){
		$name = isset($field['name']) ? $field['name'] : '';
		$special_fields = array();
		
		if(version_compare(THWCFD_Utils::get_wc_version(), '5.6.0', ">=")){
			$special_fields[] = 'shipping_phone';
		}

		$special_fields = apply_filters('thwcfd_wc_handle_custom_field', $special_fields);

		if($name && in_array($name, $special_fields)){
			return true;
		}
		return false;
	}	

	public static function update_fields($key, $fields){
		$result = update_option('wc_fields_' . $key, $fields, 'no');
		return $result;
	}

	public static function get_fields($key){
		$fields = get_option('wc_fields_'. $key, array());
		$fields = is_array($fields) ? array_filter($fields) : array();
		
		if(empty($fields) || sizeof($fields) == 0){
			if($key === 'billing' || $key === 'shipping'){
				$fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), $key . '_');

			} else if($key === 'additional'){
				$fields = array(
					'order_comments' => array(
						'type'        => 'textarea',
						'class'       => array('notes'),
						'label'       => __('Order Notes', 'woocommerce'), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'placeholder' => _x('Notes about your order, e.g. special notes for delivery.', 'placeholder', 'woocommerce') // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
					)
				);
			}
			$fields = self::prepare_default_fields($fields);
		}
		return $fields;
	}

	private static function prepare_default_fields($fields){
		foreach ($fields as $key => $value) {
			$fields[$key]['custom'] = 0;
			$fields[$key]['enabled'] = 1;
			$fields[$key]['show_in_email'] = 1;
			$fields[$key]['show_in_order'] = 1;
		}
		return $fields;
	}

	public static function get_checkout_fields($order=false){
		$fields = array();
		$needs_shipping = true;

		if($order){
			$needs_shipping = !wc_ship_to_billing_address_only() && $order->needs_shipping_address() ? true : false;
		}
		
		if($needs_shipping){
			$fields = array_merge(self::get_fields('billing'), self::get_fields('shipping'), self::get_fields('additional'));
		}else{
			$fields = array_merge(self::get_fields('billing'), self::get_fields('additional'));
		}

		return $fields;
	}

	public static function prepare_field_options($options){
		if(is_string($options)){
			$options = array_map('trim', explode('|', $options));
		}
		return is_array($options) ? $options : array();
	}

	public static function prepare_options_array($options_json, $type = 'radio'){
		$options_json = rawurldecode($options_json);
		$options_arr = json_decode($options_json, true);
		$options = array();
		
		if($options_arr){
			$i = 0;
			foreach($options_arr as $option){
				$okey = isset($option['key']) ? $option['key'] : '';
				$otext = isset($option['text']) ? $option['text'] : '';
				if($i == 0 && $type == 'select'){
					$okey = $okey ? $okey : '';
				}else{
					$okey = $okey ? $okey : sanitize_key($otext);
				}
				$i++;
				//if($okey || $otext){
					$options[$okey] = $otext;
				//}
			}
		}
		return $options;
	}

	public static function prepare_options_json($options){
		$options_json = '';
		if(is_array($options) && !empty($options)){
			$options_arr = array();

			foreach($options as $okey => $otext){
				//$okey = $okey ? $okey : $otext;

				//if($okey && $otext){
					array_push($options_arr, array("key" => $okey, "text" => $otext));
					//array_push($options_arr, array("key" => esc_attr($okey), "text" => esc_attr($otext)));
				//}
			}

			$options_json = json_encode($options_arr);
			$options_json = rawurlencode($options_json);
		}
		return $options_json;
	}

	public static function get_option_text($field, $value){
		$type = isset($field['type']) ? $field['type'] : false;

		if($type === 'select' || $type === 'radio'){
			$options = isset($field['options']) ? $field['options'] : array();

			if(isset($options[$value]) && !empty($options[$value])){
				$value = $options[$value];
				$value = THWCFD_Utils::translate_dynamic_text($value, 'option');
			}
		}elseif($type === 'checkboxgroup' || $type === 'multiselect'){
			$options = isset($field['options']) ? $field['options'] : array();

			$value_arr = explode(',', $value);
			//THWCFD_Utils
			if(is_array($value_arr)){
				$new_value = array();
				foreach($value_arr as $single_value){
					
					if(isset($options[$single_value]) && !empty($options[$single_value])){
						$new_value[] = THWCFD_Utils::translate_dynamic_text($options[$single_value], 'option') ;
					}else{
						$new_value[] = THWCFD_Utils::translate_dynamic_text($single_value, 'option') ;
					}
				}
				$value = implode(', ', $new_value);
			}elseif(isset($options[$value]) && !empty($options[$value])){
				$value = $options[$value];
			}
				
		}
		return $value;
	}

	public static function prepare_field_priority($fields, $order, $new=false){
		$priority = '';
		if(!$new){
			$priority = is_numeric($order) ? ($order+1)*10 : false;
		}

		if(!$priority){
			$max_priority = self::get_max_priority($fields);
			$priority = is_numeric($max_priority) ? $max_priority+10 : false;
		}
		return $priority;
	}

	private static function get_max_priority($fields){
		$max_priority = 0;
		if(is_array($fields)){
			foreach ($fields as $key => $value) {
				$priority = isset($value['priority']) ? $value['priority'] : false;
				$max_priority = is_numeric($priority) && $priority > $max_priority ? $priority : $max_priority;
			}
		}
		return $max_priority;
	}

	public static function sort_fields($fields){
		uasort($fields, 'wc_checkout_fields_uasort_comparison');
		return $fields;
	}

	public static function sort_fields_by_order($a, $b){
	    if(!isset($a['order']) || $a['order'] == $b['order']){
	        return 0;
	    }
	    return ($a['order'] < $b['order']) ? -1 : 1;
	}

	public static function get_order_id($order){
		$order_id = false;
		if(self::woo_version_check()){
			$order_id = $order->get_id();
		}else{
			$order_id = $order->id;
		}
		return $order_id;
	}

	public static function woo_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}

	public static function wcfd_version_check( $version = '1.3.6' ) {
		if(THWCFD_VERSION && version_compare( THWCFD_VERSION, $version, ">=" ) ) {
	  		return true;
		}
	  	return false;
	}

	public static function is_blank($value) {
		return empty($value) && !is_numeric($value);
	}

	/**************************************
	 ----- ADVANCED SETTINGS - START ------
	 **************************************/
	 public static function get_advanced_settings(){
		$settings = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);
		$settings = apply_filters('thwcfd_advanced_settings', $settings);
		return empty($settings) ? false : $settings;
	}
	
	public static function get_setting_value($settings, $key){
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return 'undefined';
	}
	
	public static function get_settings($key){
		$settings = self::get_advanced_settings();
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return 'undefined';
	}

	public static function setup_advanced_settings(){
		$settings = self::get_advanced_settings();
		if(!$settings){
			$settings = array();
			$instance = new THWCFD_Admin_Settings_Advanced();
			$setting_fields = $instance->get_advanced_settings_fields();
			foreach ($setting_fields as $name => $field) {
				$value = $field['value'];
				$settings[$name] = $value;
			}
			$instance->save_advanced_settings($settings);
		}
	}
	/**************************************
	 ----- ADVANCED SETTINGS - END --------
	 **************************************/

	/***********************************
	 ----- i18n functions - START ------
	 ***********************************/

	/**
	 * Legacy helper function.
	 *
	 * This method is no longer responsible for translation.
	 * It is kept for backward compatibility, as it may still be
	 * referenced by older code or third-party integrations.
	 *
	 * @param string $text Text to be escaped.
	 * @return string Escaped text.
	 */
	public static function t($text){

		/*if(!empty($text)){	
			$otext = $text;						
			$text = esc_html($text);	
			if($text === $otext){
				$text = __($text, 'woocommerce');
			}
		}
		return $text;*/
		return esc_html( $text);
	}

	/**
	 * Legacy echo helper function.
	 *
	 * This method no longer performs translation.
	 * It is retained for backward compatibility to avoid
	 * breaking existing usages that expect this static method.
	 *
	 * @param string $text Text to be escaped and echoed.
	 * @return void
	 */
	public static function et($text){
		// if(!empty($text)){	
		// 	$otext = $text;						
		// 	$text = __($text, 'woo-checkout-field-editor-pro');	
		// 	if($text === $otext){
		// 		$text = __($text, 'woocommerce');
		// 	}
		// }
		echo esc_html($text);
	}
	/***********************************
	 ----- i18n functions - END ------
	 ***********************************/

	public static function get_wc_version() {
		if(!class_exists('WooCommerce')){
		    return;
		}

		if(defined('WC_VERSION')) {
		    return WC_VERSION;
		}
		return;
	}

	public static function write_log ( $log )  {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}

	public static function get_allowed_html(){
		$allowed_html = array(
			'input' => array(
				'type' => array(),
				'id' => array(),
				'name' => array(),
				'value' => array(),
				'style' => array(),
				'checked' => array(),
				'class' => array(), 
			),
			'label' => array(
				'for' => array(),
				'style' => array(),
			),
			'textarea' => array(
				'name' => array(),
				'rows' => array(),
				'cols' => array(),
				'style' => array(), 
			),
			'select' => array(
				'name' => array(),
				'style' => array(),
				'class' => array(),
				'onchange' => array(),
				'multiple' => array(),
				'style' => array(),
        		'placeholder' => array(),
			),
			'option' => array(
				'value' => array(),
			),
			'th' => array(
				'colspan' => array(),
				'rowspan' => array(),
				'style' => array(),
				'class' => array(),
			),
			'tr' => array(
				'style' => array(),
				'class' => array(),
			),
			'td' => array(
				'colspan' => array(),
				'rowspan' => array(),
				'style' => array(),
				'class' => array(),
			),
			'h3' => array(),
			'p' => array(),
			'strong' => array(),
			'br' => array(),
		);
		return $allowed_html;
	}

	static function get_allowed_html_order_output() {
		$allowed_html = array(
			'input' => array(
				'type'    => array(),
				'id'      => array(),
				'name'    => array(),
				'value'   => array(),
				'style'   => array(),
				'checked' => array(),
				'class'   => array(),
				// No event handlers (onclick, onchange, etc.)
			),
			'label' => array(
				'for'   => array(),
				'style' => array(),
			),
			'textarea' => array(
				'name'  => array(),
				'rows'  => array(),
				'cols'  => array(),
				'style' => array(),
				// No event handlers
			),
			'select' => array(
				'name'        => array(),
				'style'       => array(),
				'class'       => array(),
				'multiple'    => array(),
				'placeholder' => array(),
				// onchange intentionally excluded — user-supplied values rendered here
			),
			'option' => array(
				'value' => array(),
			),
			'th' => array(
				'colspan' => array(),
				'rowspan' => array(),
				'style'   => array(),
				'class'   => array(),
			),
			'tr' => array(
				'style' => array(),
				'class' => array(),
			),
			'td' => array(
				'colspan' => array(),
				'rowspan' => array(),
				'style'   => array(),
				'class'   => array(),
			),
			'h3'     => array(),
			'p'      => array(),
			'strong' => array(),
			'br'     => array(),
		);
		return $allowed_html;
	}

	public static function convert_string_to_array($str, $separator = ','){
		if(!is_array($str)){
			$str = array_map('trim', explode($separator, $str));
		}
		return $str;
	}

	/**
	 * Translate dynamic/admin-entered strings with proper fallbacks.
	 *
	 * Translation order:
	 * 1. WPML (string translation)
	 * 2. Plugin gettext (backward compatibility)
	 * 3. WooCommerce core gettext
	 *
	 * @param string $text      Original text.
	 * @param string $text_type Type of text: label|placeholder|option.
	 * @return string Translated text.
	 */
	public static function translate_dynamic_text( $text, $text_type = 'label' ) {

		if ( empty( $text ) ) {
			return '';
		}

		// Build WPML string key (kept for backward compatibility)
		$key = 'Field label - ' . $text;
		if ( $text_type === 'placeholder' ) {
			$key = 'Field placeholder - ' . $text;
		} elseif ( $text_type === 'option' ) {
			$key = 'Field option text - ' . $text;
		}

		// WPML – preferred for dynamic/admin strings
		if ( has_filter( 'wpml_translate_single_string' ) ) {
			$translated = apply_filters(
				'wpml_translate_single_string',
				$text,
				'woo-checkout-field-editor-pro',
				$key
			);

			if ( $translated !== $text ) {
				return $translated;
			}
		}

		// Plugin gettext fallback (backward compatibility)
		// Supports existing .mo translations before WPML integration.
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		$plugin_translation = __( $text, 'woo-checkout-field-editor-pro' );

		if ( $plugin_translation !== $text ) {
			return $plugin_translation;
		}

		// WooCommerce core fallback
		// Used only when WooCommerce already provides a translation
		// in the current locale.
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.TextDomainMismatch
		return __( $text, 'woocommerce' );
	}
}

endif;
