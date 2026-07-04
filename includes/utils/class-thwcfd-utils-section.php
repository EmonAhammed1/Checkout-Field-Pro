<?php
/**
 * The checkout sections specific functionality for the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD_Utils_Section')):

class THWCFD_Utils_Section {

	static $BLOCK_SECTION_PROPS = array(

		'name' 		 => array('name'=>'name', 'label'=>'Name/ID', 'type'=>'text', 'required'=>1),
		'position' 	 => array('name'=>'position', 'label'=>'Display Position', 'type'=>'hidden', 'note' => 'position can be changed from the Block Editor', 'value' => 'set_from_block' ),
		'title' 	  => array('name'=>'title', 'label'=>'Title', 'type'=>'text', 'required'=>1 ),
		'subtitle'    => array('name'=>'subtitle', 'label'=>'Description', 'type'=>'text'),
		'show_title' => array('name'=>'show_title', 'label'=>'Show section title in checkout page.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>1),	

	);
	
	public static function is_valid_section($section){
		if(isset($section) && $section instanceof WCFE_Checkout_Section && !empty($section->name)){
			return true;
		} 
		return false;
	}
	
	public static function is_enabled($section){
		if(self::is_valid_section($section) && $section->get_property('enabled')){
			return true;
		}
		return false;
	}

	public static function is_valid_enabled_section($section){
		if(self::is_valid_section($section) && self::is_enabled($section)){
			return true;
		}
		return false;
	}
	
	public static function is_custom_section($section){
		return $section->custom_section;
	}

	public static function has_fields($section){
		if($section->get_property('fields')){
			return true;
		}
		return false;
	}

	public static function is_show_section_title($section, $context='emails'){
		$show = true;
		if(self::is_enabled($section)){
			if($context === 'admin_order'){

			}else if($context === 'customer_order'){

			}else if($context === 'emails'){

			}
		}else{
			$show = false;
		}
		$show = apply_filters('thwcfe_show_section_title_in_'.$context, $show, $section->name);
		return $show;
	}

	public static function get_property_set($section, $esc_attr=false, $checkout_type = 'classic'){
		if(self::is_valid_section($section)){
			$props_set = array();
			$selected_section_props = $checkout_type === 'block' ?  self::$BLOCK_SECTION_PROPS: self::$SECTION_PROPS;
			foreach($selected_section_props  as $pname => $props){
				$pvalue = $section->get_property($props['name']);
				
				if(isset($props['value_type']) && $props['value_type'] === 'array' && !empty($pvalue)){
					$pvalue = is_array($pvalue) ? $pvalue : explode(',', $pvalue);
				}
				
				if(isset($props['value_type']) && $props['value_type'] != 'boolean'){
					$pvalue = empty($pvalue) ? $props['value'] : $pvalue;
				}
				
				$pvalue = $esc_attr && is_string($pvalue) ? esc_attr($pvalue) : $pvalue;
				$props_set[$pname] = $pvalue;
			}
			
			$props_set['custom'] = self::is_custom_section($section);
			
			return $props_set;
		}else{
			return false;
		}
	}
	
	public static function get_property_json($section, $checkout_type = 'classic'){
		$props_json = '';
		$props_set = self::get_property_set($section, true, $checkout_type);
		
		if($props_set){
			//$props_json = htmlspecialchars(json_encode($props_set));
			$props_json = wp_json_encode($props_set);
		}
		return $props_json;
	}

	public static function get_fields($section, $cart_info = false, $exclude_disabled = false){
		$fields = false;
		if(self::is_valid_section($section)){
			$fields = $section->get_property('fields');
			$fields = self::filter_fields($fields, $cart_info, $exclude_disabled);
		}
		return is_array($fields) && !empty($fields) ? $fields : array();
	}

	public static function filter_fields($fields, $cart_info = false, $exclude_disabled = false){
		if(is_array($fields) && ($cart_info || $exclude_disabled)){
			foreach($fields as $name => $field){
				if(THWCFD_Utils_Field::is_valid_field($field)){
					$is_enabled = THWCFD_Utils_Field::is_enabled($field);

					if(($exclude_disabled && !$is_enabled) || !THWCFD_Utils_Field::show_field($field, $cart_info)){
						unset($fields[$name]);
					}
				} 
			}
		}
		return $fields;
	}

	public static function get_fieldset($section, $cart_info = false, $exclude_disabled = true){
		$fieldset = array();
		$fields = self::get_fields($section);

		foreach($fields as $name => $field){
			if(THWCFD_Utils_Field::is_valid_field($field)){
				$is_enabled = THWCFD_Utils_Field::is_enabled($field);

				if($exclude_disabled && !$is_enabled){
					continue;
				}

				$field_props = THWCFD_Utils_Field::get_property_set($field);
				$fieldset[$name] = $field_props;
			} 
		}
		return $fieldset;
	}

	public static function add_field($section, $field, $custom_field=1){
		if(self::is_valid_section($section) && THWCFD_Utils_Field::is_valid_field($field)){
			$new_order = 0;
			$order_array = wp_list_pluck($section->fields, 'order');
			if(!empty($order_array)){
				$new_order = max($order_array);
				if(!$new_order){
					$new_order = sizeof($section->fields);
				}
			}

			$field->set_property('order', $new_order + 1);
			$field->set_property('custom_field', $custom_field);
			$section->fields[$field->get_property('name')] = $field;
			return $section;
		}else{
			throw new Exception('Invalid Section or Field Object.');
		}
	}
	
	public static function update_field($section, $field){
		if(self::is_valid_section($section) && THWCFD_Utils_Field::is_valid_field($field)){
			$name = $field->get_property('name');
			$name_old = $field->get_property('name_old');
			$field_set = $section->fields;
			if(!empty($name) && is_array($field_set) && isset($field_set[$name_old])){
				
				$o_field = $field_set[$name_old];				
				$index = array_search($name_old, array_keys($field_set));
				$is_custom = ($name != $name_old) ? 1 : $o_field->get_property('custom_field');
				
				$field->set_property('order', $index);
				$field->set_property('custom_field', $is_custom);
				//$field_set[$name] = $field;
				//$section->fields[$name] = $field;
				
				if($name != $name_old){
					//unset($field_set[$name_old]);
					//$field_set = self::sort_field_set($field_set);

					$temp_field_set = array();
					foreach($field_set as $key => $ofield){
						if($key === $name_old){
							$temp_field_set[$name] = $field;
						}else{
							$temp_field_set[$key] = $ofield;
						}
					}
					$field_set = $temp_field_set;
				}else{
					$field_set[$name] = $field;
				}
				//$field_set = self::sort_field_set($field_set);
				$section->set_property('fields', $field_set);
			}
			return $section;
		}else{
			throw new Exception('Invalid Section or Field Object.');
		}
	}
	
	public static function prepare_section_from_posted_data($posted, $form = 'new', $checkout_type = 'classic'){
		$name     = isset($posted['i_name']) ? $posted['i_name'] : '';
		$position = isset($posted['i_position']) ? $posted['i_position'] : '';
		$title    = isset($posted['i_title']) ? $posted['i_title'] : '';

		if(!$name || !$title){
			return;
		}
		
		if($form === 'edit'){

			if($checkout_type === 'block'){
				$section = THWCFD_Utils_Block::get_block_checkout_section($name);
			}
			
		}else{
			$name = strtolower($name);
			$name = is_numeric($name) ? "s_".$name : $name;
				
			$section = new WCFE_Checkout_Section();
			$section->set_property('id', $name);
		}

		$selected_section_props =  $checkout_type === 'block' ? self::$BLOCK_SECTION_PROPS : self::$SECTION_PROPS;
		
		foreach( $selected_section_props as $pname => $property ){
			$iname  = 'i_'.$pname;
			//$pvalue = isset($posted[$iname]) ? $posted[$iname] : $property['value'];
			$pvalue = isset($posted[$iname]) ? $posted[$iname] : ($property['value'] ?? null);
			$pvalue = is_string($pvalue) ? trim(stripslashes($pvalue)) : $pvalue;

			if($pname === 'show_title'){
				$pvalue = isset($posted[$iname]) ? $posted[$iname] :  null;
				$pvalue = !empty($pvalue) && $pvalue === 'yes' ? 1 : 0;
			}
			$section->set_property($pname, $pvalue);
		}
		
		if($form != 'edit'){
			$name = urldecode( sanitize_title(wc_clean($name)) );
			$section->set_property('name', $name);
			$section->set_property('id', $name);
		}

		$section->set_property('custom_section', 1);
		return $section;
	}

	public static function sort_fields($section){
		if(is_array($section->fields)){
			uasort($section->fields, array(__CLASS__, 'sort_by_order'));
		}
		return $section;
	}
	
	public static function sort_by_order($a, $b){
	    if($a->get_property('order') == $b->get_property('order')){
	        return 0;
	    }
	    return ($a->get_property('order') < $b->get_property('order')) ? -1 : 1;
	}
	
}

endif;