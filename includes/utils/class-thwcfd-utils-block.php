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

if(!class_exists('THWCFD_Utils_Block')):

class THWCFD_Utils_Block {


	const OPTION_KEY_BLOCK_SECTIONS     = 'thwcfe_block_sections';
	private $core_fields;
	private $fields_locations;

	public static function get_block_sections(){
		self::get_default_block_sections();
	}
	// phpcs:disable WordPress.WP.I18n.TextDomainMismatch
	public static function get_default_block_fields(){

		$core_fields         = array(
			'contact' => array(
				'email'      => [
					'name'           => 'email',
					'label'          => __( 'Email address', 'woocommerce' ),
					'optionalLabel'  => __(
						'Email address (optional)',
						'woocommerce'
					),
					'required'       => true,
					'hidden'         => false,
					'autocomplete'   => 'email',
					'autocapitalize' => 'none',
					'index'          => 0,
					'type'           => 'email'
				],
			),
			'address'  =>  array(
				
				'country'    => [
					'name'         => 'country',
					'label'         => __( 'Country/Region', 'woocommerce' ),
					'optionalLabel' => __(
						'Country/Region (optional)',
						'woocommerce'
					),
					'required'      => true,
					'hidden'        => false,
					'autocomplete'  => 'country',
					'index'         => 1,
					'type'          => 'country',
				],
				
				'first_name' => [
					'label'          => __( 'First name', 'woocommerce' ),
					'optionalLabel'  => __(
						'First name (optional)',
						'woocommerce'
					),
					'required'       => true,
					'hidden'         => false,
					'autocomplete'   => 'given-name',
					'autocapitalize' => 'sentences',
					'index'          => 10,
				],
				'last_name'  => [
					'label'          => __( 'Last name', 'woocommerce' ),
					'optionalLabel'  => __(
						'Last name (optional)',
						'woocommerce'
					),
					'required'       => true,
					'hidden'         => false,
					'autocomplete'   => 'family-name',
					'autocapitalize' => 'sentences',
					'index'          => 20,
				],
				
				'company'    => [
					'label'          => __( 'Company', 'woocommerce' ),
					'optionalLabel'  => __(
						'Company (optional)',
						'woocommerce'
					),
					'required'       => false,
					'hidden'         => false,
					'autocomplete'   => 'organization',
					'autocapitalize' => 'sentences',
					'index'          => 30,
				],
				'address_1'  => [
					'label'          => __( 'Address', 'woocommerce' ),
					'optionalLabel'  => __(
						'Address (optional)',
						'woocommerce'
					),
					'required'       => true,
					'hidden'         => false,
					'autocomplete'   => 'address-line1',
					'autocapitalize' => 'sentences',
					'index'          => 40,
				],
				'address_2'  => [
					'label'          => __( 'Apartment, suite, etc.', 'woocommerce' ),
					'optionalLabel'  => __(
						'Apartment, suite, etc. (optional)',
						'woocommerce'
					),
					'required'       => false,
					'hidden'         => false,
					'autocomplete'   => 'address-line2',
					'autocapitalize' => 'sentences',
					'index'          => 50,
				],
				'city'       => [
					'label'          => __( 'City', 'woocommerce' ),
					'optionalLabel'  => __(
						'City (optional)',
						'woocommerce'
					),
					'required'       => true,
					'hidden'         => false,
					'autocomplete'   => 'address-level2',
					'autocapitalize' => 'sentences',
					'index'          => 70,
				],
				'state'      => [
					'label'          => __( 'State/County', 'woocommerce' ),
					'optionalLabel'  => __(
						'State/County (optional)',
						'woocommerce'
					),
					'required'       => true,
					'hidden'         => false,
					'autocomplete'   => 'address-level1',
					'autocapitalize' => 'sentences',
					'index'          => 80,
					'type'           => 'state'
				],
				'phone'      => [
					'label'          => __( 'Phone', 'woocommerce' ),
					'optionalLabel'  => __(
						'Phone (optional)',
						'woocommerce'
					),
					'required'       => false,
					'hidden'         => false,
					'type'           => 'tel',
					'autocomplete'   => 'tel',
					'autocapitalize' => 'characters',
					'index'          => 80,
				],
				'postcode'   => [
					'label'          => __( 'Postal code', 'woocommerce' ),
					'optionalLabel'  => __(
						'Postal code (optional)',
						'woocommerce'
					),
					'required'       => true,
					'hidden'         => false,
					'autocomplete'   => 'postal-code',
					'autocapitalize' => 'characters',
					'index'          => 90,
				],
			),
			'order'    => array(
				
			),
			'additional_info'    => array(
				
			),
		);

			// $fields_locations = [
			// 	// omit email from shipping and billing fields.
			// 	'address' => array_merge( \array_diff_key( array_keys( $core_fields ), array( 'email' ) ) ),
			// 	'contact' => array( 'email' ),
			// 	'order'   => [],
			// ];

		return apply_filters( 'thwcfd_default_block_checkout_fields', $core_fields );
	}
	public static function get_default_block_section_fields($section_name){
		$core_fields = self::get_default_block_fields();
		if(isset($section_name) && !empty($section_name)){
			if(isset($core_fields[$section_name])){
				return $core_fields[$section_name];
			}
		}
		return false;
	}

	public static function get_block_checkout_sections(){
		$sections = get_option(self::OPTION_KEY_BLOCK_SECTIONS);
		return empty($sections) ? self::get_default_block_sections() : $sections;
	}

    public static function get_block_checkout_section($section){
        $sections = self::get_block_checkout_sections();
        if(isset($section) && !empty($section)){
            if(is_array($sections) && isset($sections[$section])){
                return $sections[$section];
            }
        }
    }

	public static function get_default_block_sections(){

		$checkout_fields = self::get_default_block_fields();
		$default_sections = array( 'contact' => 'Contact Information', 'address' => 'Address',  'order' => 'Additional order information', 'additional_info' => 'Additional Information' );
		$default_sections = apply_filters('thwcfe_default_checkout_sections', $default_sections);

		$sections = array();
		$order = -3;
		foreach($checkout_fields as $fieldset => $fields){
			//$fieldset = $fieldset && $fieldset === 'order' ? 'additional' : $fieldset;
			$title = isset($default_sections[$fieldset]) ? $default_sections[$fieldset] : '';

			$section = new WCFE_Checkout_Section();
			$section->set_property('id', $fieldset);
			$section->set_property('name', $fieldset);
			$section->set_property('order', $order);
			$section->set_property('title', $title);
			$fieldset === 'additional_info' ? $section->set_property('custom_section', 1) : $section->set_property('custom_section', 0);
			$section->set_property('fields', self::prepare_default_fields($fields));

			$sections[$fieldset] = $section;
			$order++;
		}
		
		return $sections;
	}

	public static function get_block_field_set(){
		
	}
	// phpcs:disable WordPress.WP.I18n.TextDomainMismatch
	public static function prepare_default_fields($fields){
		$field_objects = array();
			$default_fields_id = array(
				'first_name' => array(
					'label' => __( 'First name', 'woocommerce' ),
				),
				'last_name'  => array(
					'label' => __( 'Last name', 'woocommerce' ),
				),
				'company'    => array(
					'label' => __( 'Company name', 'woocommerce' ),
				),
				'country'    => array(
					'label' => __( 'Country / Region', 'woocommerce' ),
				),
				'address_1'  => array(
					'label'       => __( 'Street address', 'woocommerce' ),
					'placeholder' => __( 'House number and street name', 'woocommerce' ),
				),
				'address_2'  => array(
					'label'       => __( 'Apartment, suite, unit, etc.', 'woocommerce' ),
					'placeholder' => __( 'Apartment, suite, unit, etc. (optional)', 'woocommerce' ),
				),
				'city'       => array(
					'label' => __( 'Town / City', 'woocommerce' ),
				),
				'state'      => array(
					'label' => __( 'State / County', 'woocommerce' ),
				),
				'postcode'   => array(
					'label' => __( 'Postcode / ZIP', 'woocommerce' ),
				),
				'email'      => array(
					'label' => __( 'Email address', 'woocommerce' ),
				),
			);

		if(is_array($fields)){
			foreach($fields as $name => $field){
				if(!empty($name) && !empty($field) && is_array($field)){
					$field['type'] = isset($field['type']) ? $field['type'] : 'text';
					$field['order'] = isset($field['index']) ? $field['index'] : 0;
					$field_object = THWCFD_Utils_Field::create_field($field['type'], $name, $field); 

					if(array_key_exists($name, $default_fields_id) && is_object($field_object)){
						$field_object->title = $default_fields_id[$name]['label'];
						if($field_object->placeholder != '' && isset($default_fields_id[$name]['placeholder'])){
							$field_object->placeholder = $default_fields_id[$name]['placeholder'];
						}
					}
					if(($name === 'billing_state' || $name === 'shipping_state') && isset($field['country'])){
						$field_object->set_property('country', '');
					}

					if($field_object){
						$field_objects[$name] = $field_object;
					}
				}
			}
		}
		return $field_objects;
	}
	// phpcs:disable WordPress.WP.I18n.TextDomainMismatch
	public static function get_core_fields() {
		
		return [
			'email'      => [
				'label'          => __( 'Email address', 'woocommerce' ),
				'optionalLabel'  => __(
					'Email address (optional)',
					'woocommerce'
				),
				'required'       => true,
				'hidden'         => false,
				'autocomplete'   => 'email',
				'autocapitalize' => 'none',
				'type'           => 'email',
				'index'          => 0,
			],
			'country'    => [
				'label'         => __( 'Country/Region', 'woocommerce' ),
				'optionalLabel' => __(
					'Country/Region (optional)',
					'woocommerce'
				),
				'required'      => true,
				'hidden'        => false,
				'autocomplete'  => 'country',
				'index'         => 1,
			],
			'first_name' => [
				'label'          => __( 'First name', 'woocommerce' ),
				'optionalLabel'  => __(
					'First name (optional)',
					'woocommerce'
				),
				'required'       => true,
				'hidden'         => false,
				'autocomplete'   => 'given-name',
				'autocapitalize' => 'sentences',
				'index'          => 10,
			],
			'last_name'  => [
				'label'          => __( 'Last name', 'woocommerce' ),
				'optionalLabel'  => __(
					'Last name (optional)',
					'woocommerce'
				),
				'required'       => true,
				'hidden'         => false,
				'autocomplete'   => 'family-name',
				'autocapitalize' => 'sentences',
				'index'          => 20,
			],
			'company'    => [
				'label'          => __( 'Company', 'woocommerce' ),
				'optionalLabel'  => __(
					'Company (optional)',
					'woocommerce'
				),
				'required'       => false,
				'hidden'         => false,
				'autocomplete'   => 'organization',
				'autocapitalize' => 'sentences',
				'index'          => 30,
			],
			'address_1'  => [
				'label'          => __( 'Address', 'woocommerce' ),
				'optionalLabel'  => __(
					'Address (optional)',
					'woocommerce'
				),
				'required'       => true,
				'hidden'         => false,
				'autocomplete'   => 'address-line1',
				'autocapitalize' => 'sentences',
				'index'          => 40,
			],
			'address_2'  => [
				'label'          => __( 'Apartment, suite, etc.', 'woocommerce' ),
				'optionalLabel'  => __(
					'Apartment, suite, etc. (optional)',
					'woocommerce'
				),
				'required'       => false,
				'hidden'         => false,
				'autocomplete'   => 'address-line2',
				'autocapitalize' => 'sentences',
				'index'          => 50,
			],
			'city'       => [
				'label'          => __( 'City', 'woocommerce' ),
				'optionalLabel'  => __(
					'City (optional)',
					'woocommerce'
				),
				'required'       => true,
				'hidden'         => false,
				'autocomplete'   => 'address-level2',
				'autocapitalize' => 'sentences',
				'index'          => 70,
			],
			'state'      => [
				'label'          => __( 'State/County', 'woocommerce' ),
				'optionalLabel'  => __(
					'State/County (optional)',
					'woocommerce'
				),
				'required'       => true,
				'hidden'         => false,
				'autocomplete'   => 'address-level1',
				'autocapitalize' => 'sentences',
				'index'          => 80,
			],
			'postcode'   => [
				'label'          => __( 'Postal code', 'woocommerce' ),
				'optionalLabel'  => __(
					'Postal code (optional)',
					'woocommerce'
				),
				'required'       => true,
				'hidden'         => false,
				'autocomplete'   => 'postal-code',
				'autocapitalize' => 'characters',
				'index'          => 90,
			],
			'phone'      => [
				'label'          => __( 'Phone', 'woocommerce' ),
				'optionalLabel'  => __(
					'Phone (optional)',
					'woocommerce'
				),
				'required'       => false,
				'hidden'         => false,
				'type'           => 'tel',
				'autocomplete'   => 'tel',
				'autocapitalize' => 'characters',
				'index'          => 100,
			],
		];
		
	}

	public static function is_wc_handle_custom_field($field){

		if (is_object($field)) {
			$field = json_decode(json_encode($field), true);
		}

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

	public static function get_option_text_from_value($field, $value){
		if(THWCFD_Utils_Field::is_valid_field($field) && apply_filters('thwcfe_display_option_text_instead_of_option_value', true)){
			$type = $field->get_property('type');
			if($type === 'select' || $type === 'radio'){
				$options = $field->get_property('options');
				if(is_array($options) && isset($options[$value]) && $options[$value]['text']){
					//$value = $options[$value]['text'];
					$value = $options[$value]['text'];
				}
			}else if($type === 'multiselect' || $type === 'checkboxgroup'){
				$options = $field->get_property('options');
				if(is_array($options)){
					$value = is_array($value) ? $value : array_map('trim', explode(',', $value));
					if(is_array($value)){
						foreach($value as $key => $option_value){
							if(isset($options[$option_value]) && $options[$option_value]['text']){
								//$value[$key] = $options[$option_value]['text'];
								$value[$key] = $options[$option_value]['text'];
							}
						}
					}
				}
			}
		}
		return $value;
	}


	
}

endif;

