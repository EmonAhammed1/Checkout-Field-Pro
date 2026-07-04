<?php
/**
 * The common utility functionalities for the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.1.0
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD_Utils_Field')):

class THWCFD_Utils_Field {

	static $SPECIAL_FIELD_TYPES = array('country', 'state', 'city');
	static $ARRAY_PROPS = array('class', 'input_class', 'label_class', 'title_class', 'subtitle_class', 'validate');
	static $BOOLEAN_PROPS = array('custom_field', 'order_meta', 'user_meta', 'price_field', 'checked', 'required', 'enabled', 'clear', 'show_in_email', 
							'show_in_email_customer', 'show_in_order', 'show_in_thank_you_page');
	static $DEFAULT_FIELD_PROPS = array(
		'type'        => array('name'=>'type', 'value'=>'text'),
		'label' 	  => array('name'=>'title', 'value'=>''),
		'description' => array('name'=>'description', 'value'=>''),
		'placeholder' => array('name'=>'placeholder', 'value'=>''),
		'order' 	  => array('name'=>'order', 'value'=>''),
		'priority'    => array('name'=>'priority', 'value'=>''),
		'autocomplete' => array('name'=>'autocomplete', 'value'=>''),
		'hidden'	   => array('name'=>'hidden', 'value'=>''),
		
		'class' 	  => array('name'=>'cssclass', 'value'=>array()),
		'label_class' => array('name'=>'title_class', 'value'=>array()),
		
		'custom' 	  => array('name'=>'custom_field', 'value'=>0),
		'value' 	  => array('name'=>'value', 'value'=>''),
		'default' 	  => array('name'=>'value', 'value'=>''),
		'validate'	  => array('name'=>'validate', 'value'=>array()),
		
		'required' 	  => array('name'=>'required', 'value'=>0),
		'clear' 	  => array('name'=>'clear', 'value'=>0),
		'enabled' 	  => array('name'=>'enabled', 'value'=>1),

		'country_field' => array('name'=>'country_field', 'value'=>''),

		'show_in_email' => array('name'=>'show_in_email', 'value'=>1),
		'show_in_email_customer' => array('name'=>'show_in_email_customer', 'value'=>1),
		'show_in_order' => array('name'=>'show_in_order', 'value'=>1),
		'show_in_thank_you_page' => array('name'=>'show_in_thank_you_page', 'value'=>1),
		'show_in_my_account_page' => array('name'=>'show_in_my_account_page', 'value'=>0),
		
	);
	static $FIELD_PROPS = array(
		'type' => array('name'=>'type', 'value'=>''),
		'name' => array('name'=>'name', 'value'=>''),
		'label' => array('name'=>'title', 'value'=>''),
		'description' => array('name'=>'description', 'value'=>''),
		'label_class' => array('name'=>'title_class', 'value'=>array(), 'value_type'=>'array'),
		'input_class' => array('name'=>'input_class', 'value'=>array(), 'value_type'=>'array'),
		'default'	  => array('name'=>'value', 'value'=>''),
		'validate'	  => array('name'=>'validate', 'value'=>array(), 'value_type'=>'array'),
		'autocomplete' => array('name'=>'autocomplete', 'value'=>''),
		'hidden'	   => array('name'=>'hidden', 'value'=>''),
		'input_mask'  => array('name'=>'input_mask', 'value'=>''),
	
		'placeholder' => array('name'=>'placeholder', 'value'=>''),
		'class' 	  => array('name'=>'cssclass', 'value'=>array(), 'value_type'=>'array'),
		
		'order_meta' => array('name'=>'order_meta', 'value'=>1),
		'user_meta'  => array('name'=>'user_meta', 'value'=>0),
		'disable_select2' => array('name'=>'disable_select2', 'value'=>0),

		
		'checked'  => array('name'=>'checked', 'value'=>1),
		'required' => array('name'=>'required', 'value'=>0),
		'clear'    => array('name'=>'clear', 'value'=>0),
		'enabled'  => array('name'=>'enabled', 'value'=>1),
		// 'enable_country_code' => array('name'=>'enable_country_code', 'value'=>0),
		
		'title' 	  => array('name'=>'title', 'value'=>''),
		'title_type'  => array('name'=>'title_type', 'value'=>''),
		'title_color' => array('name'=>'title_color', 'value'=>''),
		'title_class' => array('name'=>'title_class', 'value'=>array(), 'value_type'=>'array'),
		
		'subtitle' 		 => array('name'=>'subtitle', 'value'=>''),
		'subtitle_type'  => array('name'=>'subtitle_type', 'value'=>''),
		'subtitle_color' => array('name'=>'subtitle_color', 'value'=>''),
		'subtitle_class' => array('name'=>'subtitle_class', 'value'=>array(), 'value_type'=>'array'),
		
		'minlength' => array('name'=>'minlength', 'value'=>''),
		'maxlength' => array('name'=>'maxlength', 'value'=>''),
		
		'country_field' => array('name'=>'country_field', 'value'=>''),
		'country' => array('name'=>'country', 'value'=>''),
		
		'show_in_my_account_page' => array('name'=>'show_in_my_account_page', 'value'=>0),
	);

	public static function is_valid_field($field){
		if(isset($field) && $field instanceof WCFE_Checkout_Field){
			return true;
		} 
		return false;
	}
	
	public static function is_enabled($field){
		if($field->get_property('enabled')){
			return true;
		}
		return false;
	}

	public static function is_custom_field($field){
		return $field->custom_field;
	}

	public static function is_valid_enabled($field){
		if(self::is_valid_field($field) && self::is_enabled($field)){
			return true;
		}
		return false;
	}

	public static function is_custom_enabled($field){
		if(self::is_valid_field($field) && self::is_custom_field($field) && self::is_enabled($field)){
			return true;
		}
		return false;
	}
	
	public static function is_user_field($field){
		return $field->get_property('user_meta');
	}

	public static function is_custom_user_field($field){
		if(self::is_custom_enabled($field) && self::is_user_field($field)){
			return true;
		}
		return false;
	}

	public static function is_order_field($field){
		return $field->get_property('order_meta');
	}

	public static function is_html_field($type){
		$is_html = false;
		if($type === 'heading' || $type === 'label'){
			$is_html = true;
		}
		return $is_html;
	}

	public static function prepare_field($field, $name, $props){
		if(!empty($props) && is_array($props)){
			$field->set_property('id', $name);
			$field->set_property('name', $name);
			
			foreach(self::$DEFAULT_FIELD_PROPS as $pname => $property){
				$pvalue = isset($props[$pname]) ? $props[$pname] : $property['value'];
				$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
				
				$field->set_property($property['name'], $pvalue);
			}
			
			if(isset($props['options']) && is_array($props['options'])){
				$options_object = array();
				foreach($props['options'] as $option_key => $option_text){
					$option_object = array();
					$option_object['key'] = $option_key;
					$option_object['text'] = $option_text;
					
					$options_object[$option_key] = $option_object;
				}
				$field->set_property( 'options', $options_object );
			}else{
				$field->set_property( 'options', array() );
			}
			//$this->set_address_field( isset($field['is_address_field']) ? $field['is_address_field'] : array() ); TODO
		}
		return $field;
	}
	
	public static function prepare_field_from_posted_data($posted, $props){
		$type = isset($posted['i_type']) ? trim(stripslashes($posted['i_type'])) : '';
		$type = empty($type) ? trim(stripslashes($posted['i_original_type'])) : $type;
		$type = empty($type) ? trim(stripslashes($posted['i_otype'])) : $type;

		$fname = isset($posted['i_name']) ? trim(stripslashes($posted['i_name'])) : '';
		$fname = empty($fname) ? trim(stripslashes($posted['i_name_old'])) : $fname;

		$field = self::create_field($type); 

		
		foreach( $props as $pname => $property ){

			$iname  = 'i_'.$pname;
			
			$pvalue = '';
			if($property['type'] === 'checkbox'){
				$pvalue = isset($posted[$iname]) ? $posted[$iname] : 0;
			}else if(isset($posted[$iname])){
				if(is_array($posted[$iname])){
					$pvalue = implode(',', $posted[$iname]);
				}else{
					$pvalue = trim(stripslashes($posted[$iname]));
					$pvalue = wp_kses_post($pvalue);
				}
			}
			
			$field->set_property($pname, $pvalue);
		}
		
		if($type === 'select' || $type === 'multiselect' || $type === 'radio' || $type === 'checkboxgroup'){
			$options_json = isset($posted['i_options']) ? trim(stripslashes($posted['i_options'])) : '';
			$options_arr = self::prepare_options_array($options_json);

			$options_extra = apply_filters('thwcfe_field_options', array(), $field->get_property('name'));
			if(is_array($options_extra) && !empty($options_extra)){
				$options_arr = array_merge($options_arr, $options_extra);
				$options_json = self::prepare_options_json($options_arr);
			}
			
			$field->set_property('options_json', $options_json);
			$field->set_property('options', $options_arr);
		}elseif($type === 'number'){
			$default_value = $field->get_property('value');
			if($default_value && !is_numeric($default_value)){
				$field->set_property('value', '');
			}
		}
		
		$ftype = $field->get_property('type');
		if(!$ftype){
			$field->set_property('type', $type);
		}
		$new_fname = $field->get_property('name');
		if(!$new_fname){
			$field->set_property('name', $fname);
		}
		
		//$field->set_property('order', isset($posted['order']) ? trim(stripslashes($posted['order'])) : 0);
		//$field->set_property('custom_field', isset($posted['i_custom_field']) ? trim(stripslashes($posted['i_custom_field'])) : 0);
		
		$field->set_property('name_old', isset($posted['i_name_old']) ? trim(stripslashes($posted['i_name_old'])) : '');
		
		self::prepare_properties($field);
		return $field;
	}
	
	public static function prepare_properties($field){
		if(apply_filters("thwcfe_sanitize_field_names", true)){
			$name = urldecode( sanitize_title(wc_clean($field->get_property('name'))) );
		}else{
			$name = urldecode( wc_clean($field->get_property('name')) );
		}
		$type = $field->get_property('type');
		
		$field->set_property('name', $name);
		$field->set_property('id', $name);
				
		if($type === 'radio' || $type === 'select' || $type === 'multiselect'){
			foreach($field->get_property('options') as $option_key => $option){
				if(isset($option['price']) && is_numeric($option['price']) && $option['price'] != 0){
					$field->set_property('price_field', 1);
					break;
				}
			}
		}else{
			if((is_numeric($field->price) && $field->price != 0) || $field->price_type === 'custom'){
				$field->set_property('price_field', 1);
			}
		}
		
		if($type === 'label' || $type === 'heading'){
			$field->set_property('required', 0);
		}

		$field->set_property('property_set', self::get_property_set($field));
		
		return $field;
	}
	
	
	public static function get_property_set($field){
		if(self::is_valid_field($field)){
			$optionsObj = $field->get_property('options');
			$options = array();
			foreach($optionsObj as &$option){
				$options[$option['key']] = $option['text'];
			}
			
			$props_set = array();
			foreach(self::$FIELD_PROPS as $pname => $props){
				$fvalue = $field->get_property($props['name']);
				
				if(in_array($pname, self::$ARRAY_PROPS) && !empty($fvalue)){
					$fvalue = is_array($fvalue) ? $fvalue : THWCFD_Utils::convert_string_to_array($fvalue);
				}
				
				if(!in_array($pname, self::$BOOLEAN_PROPS)){
					$fvalue = empty($fvalue) ? $props['value'] : $fvalue;
				}
				
				if($pname === 'required'){
					$fvalue = $fvalue ? true : false;
				}
				
				$props_set[$pname] = $fvalue;
			}
			
			if($field->get_property('type') === 'checkbox'){
				$off_value = empty($props_set['on_value']) ? 0 : '';
				$off_value = apply_filters('thwcfe_checkbox_field_off_value', $off_value, $field->name);
				
				$props_set['on_value'] = $field->get_property('value');
				$props_set['off_value'] = $off_value;
				
				if($field->get_property('checked')){
					$props_set['default'] = !empty($props_set['on_value']) ? $props_set['on_value'] : 1;
				}else{
					$props_set['default'] = !empty($props_set['on_value']) ? '' : 0;
				}
			}
			
			$order = is_numeric($field->get_property('order')) ? ($field->get_property('order')+1)*10 : $field->get_property('order');
			
			$props_set['custom'] = self::is_custom_field($field);
			$props_set['priority'] = THWCFD_Utils::is_blank($order) ? $field->get_property('priority') : $order;
			$props_set['label'] = $props_set['label'];
			
			$props_set['options'] = $options;
			$props_set['options_object'] = $optionsObj;
			$props_set['has_non_ajax_rules'] = empty($rules_json) ? false : true;
			
			return $props_set;
		}else{
			return false;
		}
	}
			
	public static function get_option_array($field){
		$options_array = array();
		$options = $field->get_property('options');
		if($options && is_array($options)){
			foreach($options as $key => $option){
				$options_array[$option['key']] = $option['text'];
			}
		}
		return $options_array;
	}
	
	public static function prepare_options_array($options_json){
		$options_json = rawurldecode($options_json);
		$options_arr = json_decode($options_json, true);
		$options = array();
		
		if($options_arr){
			foreach($options_arr as $option){
				if(apply_filters('thwcfe_add_text_to_empty_key', true)){
					$option['key'] = empty($option['key']) ? $option['text'] : $option['key'];
				}
				$options[$option['key']] = $option;
			}
		}
		return $options;
	}

	public static function prepare_options_json($options){
		$options_json = json_encode($options);
		$options_json = rawurlencode($options_json);
		return $options_json;
	}
	
	public static function create_field($type, $name = false, $field_args = false){
		$field = false;
		
		if(isset($type)){
			if($type === 'text'){
				$field = new WCFE_Checkout_Field_InputText();
			}else if($type === 'hidden'){
				$field = new WCFE_Checkout_Field_Hidden();
			}else if($type === 'password'){
				$field = new WCFE_Checkout_Field_Password();
			}else if($type === 'textarea'){
				$field = new WCFE_Checkout_Field_Textarea();
			}else if($type === 'select'){
				$field = new WCFE_Checkout_Field_Select();
			}else if($type === 'multiselect'){
				$field = new WCFE_Checkout_Field_Multiselect();
			}else if($type === 'radio'){
				$field = new WCFE_Checkout_Field_Radio();
			}else if($type === 'checkbox'){
				$field = new WCFE_Checkout_Field_Checkbox();
			}else if($type === 'checkboxgroup'){
				$field = new WCFE_Checkout_Field_CheckboxGroup();
			}else if($type === 'datepicker'){
				$field = new WCFE_Checkout_Field_DatePicker();
			}else if($type === 'timepicker'){
				$field = new WCFE_Checkout_Field_TimePicker();
			}else if($type === 'file'){
				$field = new WCFE_Checkout_Field_File();
			}else if($type === 'heading'){
				$field = new WCFE_Checkout_Field_Heading();
			}else if($type === 'label'){
				$field = new WCFE_Checkout_Field_Label();
			}else if($type === 'country'){
				$field = new WCFE_Checkout_Field_Country();
			}else if($type === 'email'){
				$field = new WCFE_Checkout_Field_Email();
			}else if($type === 'state'){
				$field = new WCFE_Checkout_Field_State();
			}else if($type === 'city'){
				$field = new WCFE_Checkout_Field_City();
			}else if($type === 'tel'){
				$field = new WCFE_Checkout_Field_Tel();
			}else if($type === 'phone'){
				$field = new WCFE_Checkout_Field_Tel();
			}else if($type === 'number'){
            	$field = new WCFE_Checkout_Field_Number();
			}else if($type === 'url'){
				$field = new WCFE_Checkout_Field_URL();
			}else if($type === 'paragraph'){
				$field = new WCFE_Checkout_Field_Paragraph();
			}else if($type === 'heading'){
				$field = new WCFE_Checkout_Field_Heading();
			}
		}else{
			$field = new WCFE_Checkout_Field_InputText();
		}
		
		if($field && $name && $field_args){
			self::prepare_field($field, $name, $field_args);
		}
		return $field;
	}
				
	// public static function add_wpml_support($field){
	// 	WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Field Title - '.$field->name, $field->title );
	// 	WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Field Subtitle - '.$field->name, $field->subtitle );
	// 	WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Field Placeholder - '.$field->name, $field->placeholder );
	// 	WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Field Description - '.$field->name, $field->description );
		
	// 	$options = $field->get_property('options');
	// 	foreach($options as $option){
	// 		WCFE_Checkout_Fields_Utils::wcfe_wpml_register_string('Field Option - '.$field->name.' - '.$option['key'], $option['text'] );
	// 	}
	// }
}

endif;