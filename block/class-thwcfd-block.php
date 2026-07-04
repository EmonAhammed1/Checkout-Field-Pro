<?php
/**
 * The file that defines the core plugin class.
 *
 * @link       https://themehigh.com
 * @since      2.1.0
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/block
 */
if(!defined('WPINC')){	die; }

use Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields;
use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;

if(!class_exists('THWCFD_Block')):

class THWCFD_Block {

    public $field_sets = array();

    public function init(){
        add_action('woocommerce_init', array($this, 'load_address_blocks'));
        add_action('woocommerce_blocks_loaded', array($this, 'define_block_hooks'));

        $order_data = new THWCFD_Block_Order_Data();
        $order_data->init();
    }

    /**************************************************
	******** Address Section Functionality - START ******
	***************************************************/
    public function load_address_blocks(){
        
        if(version_compare(THWCFD_Utils::get_wc_version(), '8.8.0', "<")){
            return;
        }   
        $this->register_additional_address_fields();
        //add_filter('woocommerce_shared_settings', array($this, 'update_default_fields_data'), 999);
        add_action('woocommerce_blocks_checkout_block_registration', array($this, 'update_default_fields_data_with_block'), 999);
        add_action('woocommerce_validate_additional_field', array($this, 'validate_additional_field'), 10, 3);
        add_filter('woocommerce_get_country_locale', array($this, 'update_address_fields_data'), 999);
        if($this->has_block_checkout()){
            add_filter('woocommerce_default_address_fields', array($this, 'update_default_fields_data'), 999);
        }  
    }

    private function has_block_checkout() {
        $checkout_page_id = wc_get_page_id( 'checkout' );
        $has_block_checkout = $checkout_page_id && has_block( 'woocommerce/checkout', $checkout_page_id );
        return $has_block_checkout || apply_filters( 'thwcfe_woocommerce_blocks_has_block_checkout', false );
	}

    public function register_additional_address_fields(){

        if (!function_exists('woocommerce_register_additional_checkout_field')) {
            return;
        }

        $fieldset = $this->get_section_field_set('address');
        $default_address_fields = THWCFD_Utils_Block::get_default_block_section_fields('address');
        if (!is_array($fieldset) || !is_array($default_address_fields)) {
            return;
        }
        $remove_optional = apply_filters('thwcfe_remove_optional_label', false);
        $additional_fields = array_diff_key($fieldset, $default_address_fields);
        foreach ($additional_fields as $field_data) {
			
            // WooCommerce < 9.8.0 does not support required checkboxes
            if((version_compare(THWCFD_Utils::get_wc_version(), '9.8.0', "<")) && $field_data['type'] === 'checkbox') {
                $field_data['required'] = false;
            }
            
            if (isset($field_data['label'])) {
                //$field_data['label'] = __($field_data['label'], 'woo-checkout-field-editor-pro');
                $field_data['label'] = THWCFD_Utils::translate_dynamic_text($field_data['label'], 'label');
            }
			woocommerce_register_additional_checkout_field(
				array(
					'id'          => 'thwcfe-block/'.$field_data['name'],
					'label'       => $field_data['label'],
                    'optionalLabel' =>  $remove_optional ? $field_data['label'] : sprintf(
                        /* translators: %s Field label. */
                        __( '%s (optional)', 'woocommerce' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
                        $field_data['label']
				    ),
					'placeholder' => $field_data['placeholder'],
					'location'    => 'address',
					'type'        => $field_data['type'],
					'required'    => $field_data['required'],
					'index'      => $field_data['priority'],
					'options'     =>  isset($field_data['options']) ? $this->get_field_options($field_data['options_object']) 
						: array()
					,
				)

			);

		} 
    }

    public function get_field_options($options){
		$field_options = array();
		foreach ($options as $option) {
            $option['text'] = THWCFD_Utils::translate_dynamic_text($option['text'], 'option');
			$field_options[] = array(
				'label' => $option['text'],
				'value' => $option['key'],
			);
		}
		return $field_options;
	}

    public function update_address_fields_data($locale){
 
        if(! function_exists('has_block') || ! has_block( 'woocommerce/checkout' )) {
            return $locale;
        }
        $change_default_address_fields = apply_filters('thwcfe_change_default_block_address_fields', true);
        if (!$change_default_address_fields) {
            return $locale;
        }

        $field_set = $this->get_section_field_set('address');
        $address_field_keys = array('address_1', 'postcode', 'city', 'state');
        $address_fields = array_intersect_key($field_set, array_flip($address_field_keys));
 
        foreach ($locale as $key => $value) {
            $this->update_locale_field($locale, $key, 'address_1', $address_fields);
            $this->update_locale_field($locale, $key, 'postcode', $address_fields);
            $this->update_locale_field($locale, $key, 'city', $address_fields);
            $this->update_locale_field($locale, $key, 'state', $address_fields);
        }
  
        return $locale;
    }
 
    private function update_locale_field(&$locale, $key, $field_name, $address_fields) {

        if (isset($address_fields[$field_name])) {
            $locale[$key][$field_name] = [
                'required' => $address_fields[$field_name]['required'] ?? true,
                'hidden'   => false,
            ];
        } else {
            $locale[$key][$field_name] = [
                'required' => false,
                'hidden'   => true,
            ];
        }
    }

    public function update_default_fields_data_with_block() {

		if (!class_exists('Automattic\WooCommerce\Blocks\Package') || !class_exists('Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry') || !class_exists('Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields')) {
            //error_log('WooCommerce Blocks classes not found. Please ensure WooCommerce Blocks is installed and activated.');
            return;
        }
        $change_default_address_fields = apply_filters('thwcfe_change_default_block_address_fields', true);
        if (!$change_default_address_fields) {
            return;
        }
        $checkout_fields     = Package::container()->get( CheckoutFields::class );
		$asset_data_registry = Package::container()->get(AssetDataRegistry::class);
        $default_address_fields = THWCFD_Utils_Block::get_core_fields();
        $field_set = $this->get_section_field_set('address');
        $remove_optional = apply_filters('thwcfe_remove_optional_label', false);

        foreach( $default_address_fields as $key => &$field){
            if($key === 'email'){
                continue;
            }
            if (isset($field_set[$key])) {
                //$field['index'] = $field_set[$key]['priority'] ?? $field['index'];
                $field['index'] = ! empty( $field_set[$key]['priority'] )? $field_set[$key]['priority'] : $field['index'];
               
                if(apply_filters('thwcfe_block_address_field_dynamic_label', true)){
                    $field['label'] = $field_set[$key]['label']?? $field['label'];
                
                    if($remove_optional){
                        $field['optionalLabel'] = $field_set[$key]['label']?? $field['optionalLabel'];
                    }else{
                        //$field['optionalLabel'] = $field_set[$key]['label']? $field_set[$key]['label'].' (optional)' : $field['optionalLabel'];
                        $field['optionalLabel'] = !empty($field_set[$key]['label']) ? sprintf(
                            /* translators: %s Field label. */
                            __( '%s (optional)', 'woocommerce' ), //phpcs:disable WordPress.WP.I18n.TextDomainMismatch
                            $field_set[$key]['label']
                        ) : $field['optionalLabel'];
                    }
                }
                $field['required'] = $field_set[$key]['required'] ?? $field['required'];
                
            } else {
                $field['hidden'] = true;
            }

            if(isset($field['label'])){
               // $field['label'] = __($field['label'], 'woo-checkout-field-editor-pro');
                $field['label'] = THWCFD_Utils::translate_dynamic_text($field['label'], 'label');
            }
        } 
        
        unset($field);
        $asset_data_registry->add( 'defaultFields', array_merge($default_address_fields, $checkout_fields->get_additional_fields() ) );

    }

    public function update_default_fields_data($fields){

        $change_default_address_fields = apply_filters('thwcfe_change_default_block_address_fields', true);
        if (!$change_default_address_fields) {
            return;
        }

        $field_set = $this->get_section_field_set('address');
        foreach( $fields as $key => &$field){
            if($key === 'email'){
                continue;
            }
            if (isset($field_set[$key])) {
                $field['index'] = $field_set[$key]['priority'] ?? $field['index'];
                $field['label'] = $field_set[$key]['label']?? $field['label'];
                $field['required'] = $field_set[$key]['required'] ?? $field['required'];
            } else {
                $field['hidden'] = true;
                $field['required'] = false;
            }

            if(isset($field['label'])){
                //$field['label'] = __($field['label'], 'woo-checkout-field-editor-pro');
                $field['label'] = THWCFD_Utils::translate_dynamic_text($field['label'], 'label');
            }
        }
        unset($field);
        return $fields;
    }

    public function validate_additional_field($errors, $field_key, $field_value) {
        
        $field_set = $this->get_section_field_set('address');
        $key_parts = explode('thwcfe-block/', $field_key);
        $actual_field_key = isset($key_parts[1]) ? $key_parts[1] : null;
    
        if (empty($actual_field_key) || !isset($field_set[$actual_field_key])) {
            return $errors;
        }

        $field_properties = $field_set[$actual_field_key];

        if (empty($field_properties['validate'])) {
            return $errors;
        }

        foreach ((array)$field_properties['validate'] as $rule) {
            switch ($rule) {
                case 'email':
                    if (!empty($field_value) && !is_email($field_value)) {
                        $errors->add(
                            'invalid_email_field',
                            sprintf(
                                /* translators: %s is the title of the field being validated. */
                                __('The provided %s is not a valid email address.', 'woo-checkout-field-editor-pro'),
                                esc_html($field_properties['title'] ?? 'value')
                            )
                        );
                    }
                    break;
    
                case 'phone':
                    if (!empty($field_value) && !\WC_Validation::is_phone($field_value)) {
                        
                        $errors->add(
                            'invalid_phone_field',
                            sprintf(
                                // translators: %s is the title of the field being validated.
                                __('The provided %s is not a valid phone number.', 'woo-checkout-field-editor-pro'),
                                esc_html($field_properties['title'] ?? 'value')
                            )
                        );
                    }
                    break;
    
                case 'postcode':
                    if (!empty($field_value) && !\WC_Validation::is_postcode($field_value)) {
                        
                        $errors->add(
                            'invalid_postcode',
                            sprintf(
                                // translators: %s is the title of the field being validated.
                                __('The provided %s is not a valid postcode.', 'woo-checkout-field-editor-pro'),
                                esc_html($field_properties['title'] ?? 'value')
                            )
                        );
                    }
                    break;
    
                default:
                    break;
            }
        }
        return $errors;
    }
    

    public function get_section_field_set($section_name){

        $section = THWCFD_Utils_Block::get_block_checkout_section($section_name);
      	$fieldset = THWCFD_Utils_Section::get_fieldset($section);
		return $fieldset;
    }

    /**************************************************
	******** Address Section Functionality - END ******
	***************************************************/

    /**************************************************
	******** Additional Sections- START ******
	***************************************************/

    public function define_block_hooks(){
       
        if(version_compare(THWCFD_Utils::get_wc_version(), '8.8.0', "<")){
            return;
        } 

        add_action('woocommerce_blocks_checkout_block_registration' , array($this, 'register_block_integration'));
        THWCFD_Block_Extend_Store_Endpoint::init();
        add_action('woocommerce_store_api_checkout_update_order_from_request', array($this, 'store_api_checkout_update_order_from_request'),10,2);
    }

    public function register_block_integration($integration_registry){
		$integration_registry->register( new THWCFD_Block_Integration() );
	}

    public function store_api_checkout_update_order_from_request( \WC_Order $order, \WP_REST_Request $request ){

		$request_data  = $request['extensions']['thwcfe-additional-fields'] ?? array();
        
        if (empty($request_data)) {
            return;
        }
        
        $sections = THWCFD_Utils_Block::get_block_checkout_sections();
        if (empty($sections)) {
            return;
        }

        $order_meta_updates = array();
        $user_meta_updates = array();
		foreach ($request_data as $section_key => $section_fields) {
            if (!isset($sections[$section_key]) || empty($section_fields)) {
                continue;
            }
            $section = $sections[$section_key];
            
            
            if (empty($section->fields)) {
                continue;
            }
            $order_meta_fields = array();
            $user_meta_fields = array();
            foreach($section_fields as $field_key => $field_value){
                if (!isset($section->fields[$field_key])) {
                    continue;
                }
                $field = $section->fields[$field_key];
                $field_type = $field->property_set['type'] ?  $field->property_set['type'] : 'text';

                if (is_array($field_value)) {
                    $field_value = array_map('sanitize_text_field', $field_value);
                    $field_value = implode(', ', $field_value);
                } else {
                    // Sanitize based on field type
                    if ($field_type === 'textarea') {
                        $field_value = sanitize_textarea_field($field_value);
                    } else {
                        $field_value = sanitize_text_field($field_value);
                    }
                }
                // if(is_array($field_value)){
                //     $field_value = implode(', ', $field_value);
                // }
                if (($field->property_set['order_meta'])) {
                    $order_meta_updates[$field_key] = $field_value;
                    $order_meta_fields[$field_key] = $field_value;
                }
                if (($field->property_set['user_meta'])) {
                    $user_meta_updates[$field_key] = $field_value;
                    $user_meta_fields[$field_key] = $field_value;
                }
                
            }
            $order_meta_updates[$section_key] = $order_meta_fields;
            $user_meta_updates[$section_key] = $user_meta_fields;
		}
        if (!empty($order_meta_updates)) {
            foreach ($order_meta_updates as $key => $value) {
                $order->update_meta_data($key, $value);
            }
            $order->save();
        }
        if (!empty($user_meta_updates)) {
            $user_id = $order->get_user_id();
            foreach ($user_meta_updates as $key => $value) {
                update_user_meta($user_id, $key, $value );
            }
        }
	}
   


}
endif;