<?php
/**
 *
 * @link       https://themehigh.com
 * @since      2.1.0
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/block
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD_Block_Order_Data')):
 
class THWCFD_Block_Order_Data {

	const VIEW_ADMIN_ORDER    = 'admin_order';
	const VIEW_CUSTOMER_ORDER = 'customer_order';
	const VIEW_ORDER_EMAIL    = 'emails';

	public function init() {
		$this->order_meta_fields_admin();
		$this->order_meta_fields_customer();
		$this->order_meta_fields_email();		
	}
	
	public function order_meta_fields_admin(){
		add_action('woocommerce_admin_order_data_after_order_details', array($this, 'admin_order_data_after_order_details'), 20, 1);
	}

	public function order_meta_fields_customer(){
		add_action('woocommerce_order_details_after_order_table', array($this, 'display_custom_fields_in_order_details_page_customer'), 20, 1);
		// WC subscriptions - Display custom fields in my account page
		add_action('woocommerce_subscription_details_after_subscription_table', array($this, 'display_custom_fields_in_order_details_page_customer'), 20, 1);
	}

	public function order_meta_fields_email(){

		add_action('woocommerce_email_customer_details', array($this, 'woo_email_customer_details'), 15, 4);
		add_filter('woocommerce_email_customer_details_fields', array($this, 'woo_email_customer_details_fields'), 10, 3);
		add_action('woocommerce_email_order_meta', array($this, 'woo_email_order_meta'), 20, 4);
		$hp_email_order_meta_fields = apply_filters('thwcfe_email_order_meta_fields_priority', 10);
		add_filter('woocommerce_email_order_meta_fields', array($this, 'woo_email_order_meta_fields'), $hp_email_order_meta_fields, 3);
	}

	/**********************************************************
	 **** DISPLAY CUSTOM FIELDS IN CUSTOMERT ORDER - START ****
	 **********************************************************/
	public function display_custom_fields_in_order_details_page_customer($order){
		$args = array(
			'fname_prefix'   => '',
			'is_nl2br'       => apply_filters('thwcfe_nl2br_custom_field_value', true),
			'esc_attr_label' => apply_filters('thwcfe_esc_attr_custom_field_label_thankyou_page', false),
		);
		$sections = $this->get_order_meta_sections($order, self::VIEW_CUSTOMER_ORDER);
		$html = '';
		foreach($sections as $sname => $section_data){
			$fields = isset($section_data['fields']) ? $section_data['fields'] : false;
			
			if(is_array($fields) && !empty($fields)){
				$section = isset($section_data['section']) ? $section_data['section'] : false;
				$fields_html = $this->get_order_meta_fields_html($order, $fields, self::VIEW_CUSTOMER_ORDER);
				if($fields_html){
					$html .= $this->get_section_title_html($section, self::VIEW_CUSTOMER_ORDER);
					$html .= $fields_html;
				}
				
			}
		}

		if($html){
			do_action( 'thwcfe_order_details_before_custom_fields_table', $order ); 
			?>
			<table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields">
				<?php
					do_action( 'thwcfe_order_details_before_custom_fields', $order );
					//echo $html;
					echo wp_kses($html, THWCFD_Utils::get_allowed_html_order_output());
					do_action( 'thwcfe_order_details_after_custom_fields', $order ); 
				?>
			</table>
			<?php
			do_action( 'thwcfe_order_details_after_custom_fields_table', $order ); 
		}
	}
	/********************************************************
	 **** DISPLAY CUSTOM FIELDS IN CUSTOMERT ORDER - END ****
	 ********************************************************/

	 /******************************************************
	 **** DISPLAY CUSTOM FIELDS IN ADMIN ORDER - START ****
	 ******************************************************/

     public function admin_order_data_after_order_details($order){
		$html = '';
		$args = $this->prepare_args_admin_order_view();
		$sections = $this->get_order_meta_sections($order, self::VIEW_ADMIN_ORDER, $args);

		foreach($sections as $sname => $section_data){
			$fields = isset($section_data['fields']) ? $section_data['fields'] : false;

			if(is_array($fields) && !empty($fields)){
				$section = isset($section_data['section']) ? $section_data['section'] : false;
				$fields_html = $this->get_order_meta_fields_html($order, $fields, self::VIEW_ADMIN_ORDER, $args);

				if($fields_html){
					$html .= $this->get_section_title_html($section, self::VIEW_ADMIN_ORDER);
					$html .= $fields_html;
				}
			}
		}

		if($html){
			echo '<p style="clear: both; margin: 0 !important;"></p>';
			echo wp_kses($html, THWCFD_Utils::get_allowed_html_order_output());
		}
	}
	private function prepare_args_admin_order_view(){
		$args = array(
			'fname_prefix'   => '',
			'is_nl2br'       => apply_filters('thwcfe_nl2br_custom_field_value', true),
			'esc_attr_label' => apply_filters('thwcfe_esc_attr_custom_field_label_admin_order', false),
		);
		return $args;
	}
	/****************************************************
	 **** DISPLAY CUSTOM FIELDS IN ADMIN ORDER - END ****
	 ****************************************************/
	/*************************************************
	 **** DISPLAY CUSTOM FIELDS IN EMAILS - START ****
	 *************************************************/
    public function woo_email_customer_details($order, $sent_to_admin = false, $plain_text = false, $email = false){
		$settings     = THWCFD_Utils::get_advanced_settings();
		$position     = THWCFD_Utils::get_setting_value($settings, 'custom_fields_position_email');
		$html_enabled = THWCFD_Utils::get_setting_value($settings, 'enable_html_in_emails') === 'yes' ? true : false;

		if($position === 'woocommerce_email_customer_details_fields' && $html_enabled){
			$this->display_custom_fields_in_emails($order, $sent_to_admin, $plain_text, $email);
		}
	}

    public function woo_email_order_meta($order, $sent_to_admin = false, $plain_text = false, $email = false){
		$settings     = THWCFD_Utils::get_advanced_settings();
		$position     = THWCFD_Utils::get_setting_value($settings, 'custom_fields_position_email');
		$html_enabled = THWCFD_Utils::get_setting_value($settings, 'enable_html_in_emails') === 'yes' ? true : false;

		if($position != 'woocommerce_email_customer_details_fields' && $html_enabled){
			$this->display_custom_fields_in_emails($order, $sent_to_admin, $plain_text, $email);
		}
	}

	public function woo_email_customer_details_fields($ofields, $sent_to_admin = false, $order = false){
		$settings     = THWCFD_Utils::get_advanced_settings();
		$position     = THWCFD_Utils::get_setting_value($settings, 'custom_fields_position_email');
		$html_enabled = THWCFD_Utils::get_setting_value($settings, 'enable_html_in_emails') === 'yes' ? true : false;

		if($position === 'woocommerce_email_customer_details_fields' && !$html_enabled){
			$ofields = $this->prepare_order_meta_fields_for_email($ofields, $sent_to_admin, $order);
		}
		return $ofields;
	}

    public function woo_email_order_meta_fields($ofields, $sent_to_admin = false, $order = false){
		$settings     = THWCFD_Utils::get_advanced_settings();
		$position     = THWCFD_Utils::get_setting_value($settings, 'custom_fields_position_email');
		$html_enabled = THWCFD_Utils::get_setting_value($settings, 'enable_html_in_emails') === 'yes' ? true : false;

		if($position != 'woocommerce_email_customer_details_fields' && !$html_enabled){
			$ofields = $this->prepare_order_meta_fields_for_email($ofields, $sent_to_admin, $order);
		}
		return $ofields;
	}

    private function prepare_order_meta_fields_for_email($ofields, $sent_to_admin, $order){
		if(!is_array($ofields)){
			$ofields = array();
		}

		$custom_fields = array();
		$args          = $this->prepare_args_order_email($sent_to_admin);
		$sections      = $this->get_order_meta_sections($order, self::VIEW_ORDER_EMAIL, $args);
		$order_id      = THWCFD_Utils::get_order_id($order);

		foreach($sections as $sname => $section_data){
			$fields = isset($section_data['fields']) ? $section_data['fields'] : false;
			
			if(is_array($fields)){
				foreach($fields as $name => $field) {
					if(THWCFD_Utils_Block::is_wc_handle_custom_field($field)){
						continue;
					}				
					if($this->is_show_field($field, self::VIEW_ORDER_EMAIL, $args)){
						$type = $field->get_property('type');

						if(!THWCFD_Utils_Field::is_html_field($type)){
							$field_data = $this->prepare_single_field_data($order_id, $name, $field, self::VIEW_ORDER_EMAIL, $args);
							$custom_fields[$name] = $field_data;
						}
					}
				}
			}
		}

		return array_merge($ofields, $custom_fields);
	}
	private function display_custom_fields_in_emails($order, $sent_to_admin, $plain_text, $email){

		$html = '';
		$args = $this->prepare_args_order_email($sent_to_admin);
		$args['plain_text'] = $plain_text;
		$sections = $this->get_order_meta_sections($order, self::VIEW_ORDER_EMAIL, $args);
		foreach($sections as $sname => $section_data){
			$fields = isset($section_data['fields']) ? $section_data['fields'] : false;
			
			if(is_array($fields) && !empty($fields)){
				$section = isset($section_data['section']) ? $section_data['section'] : false;
				$fields_html = $this->get_order_meta_fields_html($order, $fields, self::VIEW_ORDER_EMAIL, $args);
				if($fields_html){
					$html .= $this->get_section_title_html($section, self::VIEW_ORDER_EMAIL);
					$html .= $fields_html;
				}
			}
		}
		if($html){
			//echo $html;
			echo wp_kses($html, THWCFD_Utils::get_allowed_html_order_output());
		}	
	}
    private function prepare_args_order_email($sent_to_admin){
		$args = array(
			'sent_to_admin'  => $sent_to_admin,
			'is_nl2br'       => apply_filters('thwcfe_nl2br_custom_field_value', true),
			'esc_attr_label' => apply_filters('thwcfe_esc_attr_custom_field_label_email', false),
		);
		return $args;
	}
	/***********************************************
	 **** DISPLAY CUSTOM FIELDS IN EMAILS - END ****
	***********************************************/

	/***********************************************
	 **** DISPLAY SECTION TITLE - START ****
	 ***********************************************/

	private function is_show_section_title($section, $context){
		$show = true;

		if($context === self::VIEW_ADMIN_ORDER){

		}elseif($context === self::VIEW_CUSTOMER_ORDER){

		}elseif($context === self::VIEW_ORDER_EMAIL){
			
		}

		return apply_filters('thwcfe_show_section_title_in_'.$context, $show, $section->name);
	}

	private function get_section_title_html($section, $context=false){
		$html = '';

		if($this->is_show_section_title($section, $context)){
			$title = $section->get_property('title');

			if($title){
				//$title    = __($title, 'woo-checkout-field-editor-pro');
				$title    = THWCFD_Utils::translate_dynamic_text($title, 'label');
				$subtitle = $section->get_property('subtitle');
				$subtitle = apply_filters('thwcfe_section_subtitle', $subtitle, $section->name, $context);
				//$subtitle = $subtitle ? __($subtitle,'woo-checkout-field-editor-pro') : '';

				if($context === self::VIEW_ADMIN_ORDER){
					$html = $this->get_section_title_html_admin_order($title, $subtitle);

				}elseif($context === self::VIEW_CUSTOMER_ORDER){
					$html = $this->get_section_title_html_customer_order($title, $subtitle);

				}elseif($context === self::VIEW_ORDER_EMAIL){
					$html = $this->get_section_title_html_order_emails($title, $subtitle);
				}
			}
		}
		
		return $html;
	}
    private function get_section_title_html_admin_order($title, $subtitle){
		if($subtitle){
			$title .= '<br/><span style="font-size:80%">'.$subtitle.'</span>';
		}

		$html = '<h3>'. $title .'</h3>';
		return $html;
	}

	private function get_section_title_html_customer_order($title, $subtitle){
		$html = '';
		if($subtitle){
			$title .= '<br/><span style="font-size:80%">'.$subtitle.'</span>';
		}

		if(is_account_page() && apply_filters('thwcfe_display_section_title_customer_order',true)){
			$html = '<tr><th colspan="2" class="thwcfe-section-title">'. $title .'</th></tr>';
		}else if(apply_filters('thwcfe_display_section_title_customer_order',true)){
			$html = '<tr><th colspan="2" class="thwcfe-section-title">'. $title .'</th></tr>';
		}
		return $html;
	}

	private function get_section_title_html_order_emails($title, $subtitle){
		if($subtitle){
			$title .= '<br/><span style="font-size:80%">'.$subtitle.'</span>';
		}

		$html = '<h3>'. $title .'</h3>';
		return $html;
	}
	/***********************************************
	**** DISPLAY SECTION TITLE - END ****
	***********************************************/

	/***********************************************
	**** DISPLAY SECTION FIELDS - START ****
	***********************************************/
	private function is_show_field($field, $context, $args=array()){
		$show = true;

		if($context === self::VIEW_ADMIN_ORDER){
			$show = $field->get_property('show_in_order');

		}elseif($context === self::VIEW_CUSTOMER_ORDER){
			$show = $field->get_property('show_in_thank_you_page');

		}elseif($context === self::VIEW_ORDER_EMAIL){

			$sent_to_admin = isset($args['sent_to_admin']) ? $args['sent_to_admin'] : false;
			if($sent_to_admin){
				$show = $field->get_property('show_in_email');					
			}else{
				$show = $field->get_property('show_in_email_customer');
			}
		}

		$show = apply_filters('thwcfe_show_field_order_data', $show, $field, $context, $args);

		return $show;
	}

    private function get_order_meta_fields_html($order, $fields, $context=false, $args=array()){
		$html = '';

		if(is_array($fields)){
			$order_id = THWCFD_Utils::get_order_id($order);
			
			$defaults = array(
				'fname_prefix'   => '',
				'is_nl2br'       => true,
				'esc_attr_label' => false,
			);
			$args = wp_parse_args( $args, $defaults );
			
			foreach($fields as $name => $field){
				if(THWCFD_Utils_Block::is_wc_handle_custom_field($field)){
					continue;
				}
				
				$field_data = $this->prepare_single_field_data($order_id, $name, $field, $context, $args);

                if($context === self::VIEW_ADMIN_ORDER){
                    $html .= $this->get_single_field_html_admin_order($field_data);

                }elseif($context === self::VIEW_CUSTOMER_ORDER){
                    $html .= $this->get_single_field_html_customer_order($field_data);

                }elseif($context === self::VIEW_ORDER_EMAIL){
                    $html .= $this->get_single_field_html_order_emails($field_data, $args);
                }
					
			}
		}
		return $html;
	}

    private function prepare_single_field_data($order_id, $name, $field, $context, $args){
		$order = wc_get_order( $order_id );
		if(!$order){
			return array();
		}
		$fname_prefix   = isset($args['fname_prefix']) ? $args['fname_prefix'] : '';
		$is_nl2br       = isset($args['is_nl2br']) ? $args['is_nl2br'] : true;
		$esc_attr_label = isset($args['esc_attr_label']) ? $args['esc_attr_label'] : false;

		$type = $field->get_property('type');

		$field_data = array();
		$field_data['name'] = $name;
		$field_data['type'] = $type;
					
		if($type === 'label' || $type === 'heading' || $type === 'paragraph'){
			$title    = $field->get_property('title') ? $field->get_property('title') : false;
			$subtitle = $field->get_property('subtitle') ? $field->get_property('subtitle') : false;

			if($title || $subtitle){
				if($esc_attr_label){
					$title    = $title ? THWCFD_Utils::translate_dynamic_text($title, 'label') : '';
					//$subtitle = $subtitle ? __($subtitle,'woo-checkout-field-editor-pro') : '';
				}else{
					$title    = $title ? THWCFD_Utils::translate_dynamic_text($title, 'label') : '';
					//$subtitle = $subtitle ? __($subtitle,'woo-checkout-field-editor-pro') : '';
				}

				$field_data['label'] = $title;
				$field_data['sublabel'] = $subtitle;
			}
		}else{
			// $value = get_post_meta( $order_id, $fname_prefix.$name, true );
			$value = $order->get_meta( $fname_prefix.$name, true );
			$value = apply_filters('thwcfe_order_meta_field_value', $value, $name, $type);

			if($type === 'checkbox'){
				$value = ($value === 'yes' || $value === '1') ? __('Yes', 'woo-checkout-field-editor-pro') : __('No', 'woo-checkout-field-editor-pro');
			}

			if(!empty($value) || (($value === '0') && apply_filters( 'thwcfe_accept_value_zero',false))){
				$title = $field->get_property('title') ? $field->get_property('title') : $name;
				$title = THWCFD_Utils::translate_dynamic_text( $title, 'label' );

				if($type === 'file'){
					$value = $this->get_field_display_value_file($name, $value, $context);

				}else{
					$value = THWCFD_Utils_Block::get_option_text_from_value($field, $value);
					$value = is_array($value) ? implode(",", $value) : $value;

					if($type === 'multiselect' || $type === 'checkboxgroup'){
						$value = $this->get_field_display_value_multi_option($name, $value, $context);
					}
					
					if($is_nl2br && $type === 'textarea'){
						$value = nl2br($value);

					}else{
						$value = esc_html($value);
					}
				}
				// if($type === 'checkboxgroup' || $type === 'radio'){
				// 	$value = html_entity_decode($value);
				// }
				
				$field_data['label'] = $title;
				//$field_data['sublabel'] = $subtitle;
				$field_data['value'] = $value;					
			}
		}
		return $field_data;
	}
    private function get_single_field_html_admin_order($field){
		$html = '';
		$type = isset($field['type']) ? $field['type'] : false;

		if($type === 'heading' || $type === 'label' || $type === 'paragraph'){
			$label    = isset($field['label']) ? $field['label'] : false;
			$sublabel = isset($field['sublabel']) ? $field['sublabel'] : false;

			if($sublabel){
				$label .= '<br/><span style="font-size:80%">'.$sublabel.'</span>';
			}
			if(!empty($label)){
				if($type === 'heading'){
					$html .= '<h3>'. $label .'</h3>';
				}else{
					$html .= '<p><strong>'. $label .'</strong></p>';
				}
			}
		}elseif($type){
			$label = isset($field['label']) ? $field['label'] : false;
			$value = isset($field['value']) ? $field['value'] : false;

			if(!empty($label) && (!empty($value) || (($value === '0') && apply_filters( 'thwcfe_accept_value_zero',false)))){
				$html .= '<p><strong>'. $label .':</strong> '. $value .'</p>';
			}
		}

		return $html;
	}

	private function get_single_field_html_customer_order($field){
		$html = '';
		$type = isset($field['type']) ? $field['type'] : false;

		if($type === 'heading' || $type === 'label' || $type === 'paragraph'){
			$label    = isset($field['label']) ? $field['label'] : false;
			$sublabel = isset($field['sublabel']) ? $field['sublabel'] : false;

			if($sublabel){
				$label .= '<br/><span style="font-size:80%">'.$sublabel.'</span>';
			}

			if(!empty($label)){
				if(is_account_page()){
					$html .= '<tr><th colspan="2" class="thwcfe-html-'.$type.'">'. $label .'</th></tr>';
				}else{
					$html .= '<tr><th colspan="2" class="thwcfe-html-'.$type.'">'. $label .'</th></tr>';
				}
			}
		}elseif($type){
			$label = isset($field['label']) ? $field['label'] : false;
			$value = isset($field['value']) ? $field['value'] : false;

			if(!empty($label) && (!empty($value) || (($value === '0') && apply_filters( 'thwcfe_accept_value_zero',false)))){
				if(apply_filters( 'thwcfe_view_order_customer_details_table_view', true )){
					$html .= '<tr><td>'. $label .':</td><td>'. wptexturize($value) .'</td></tr>';
				}else{
					$html .= '<br/><dt>'. $label .':</dt><dd>'. wptexturize($value) .'</dd>';
				}
			}
		}

		return $html;
	}

	private function get_single_field_html_order_emails($field, $args=array()){
		$html  = '';
		$title = '';
		$value = '';
		$label = '';
		$type  = isset($field['type']) ? $field['type'] : false;
		$plain_text = isset($args['plain_text']) ? $args['plain_text'] : false;

		if($type === 'heading' || $type === 'label' || $type === 'paragraph'){
			$label    = isset($field['label']) ? $field['label'] : false;
			$sublabel = isset($field['sublabel']) ? $field['sublabel'] : false;

			if($sublabel && !$plain_text){
				$label .= '<br/><span style="font-size:80%">'.$sublabel.'</span>';
			}

			if(!empty($label)){
				if($plain_text){
					$html = $label;
				}else{
					$html = '<p><strong>'.$label.'</strong></p>';
				}
			}
		}elseif($type){
			$label = isset($field['label']) ? $field['label'] : false;
			$value = isset($field['value']) ? $field['value'] : false;
			if($type === 'checkbox'){
				$value = ($value === 'yes' || $value === '1') ? __('Yes', 'woo-checkout-field-editor-pro') : __('No', 'woo-checkout-field-editor-pro');
			}

			if(!empty($label) && (!empty($value) || (($value === '0') && apply_filters( 'thwcfe_accept_value_zero',false)))){
				if($plain_text){
					$html = $label . ': ' . $value . "\n";
				}else{
					$html = '<p><strong>'. $label .':</strong> '. $value .'</p>';
				}
			}
		}

		$html = apply_filters('thwcfe_email_display_field_html', $html, $type, $label, $value);
		return $html;
	}
	/***********************************************
	 **** DISPLAY SECTION FIELDS - END ****
	***********************************************/

	 /***********************************************
	 **** PREPARE SECTIONS & FIELDS - START ****
	 ***********************************************/
	private function get_order_meta_sections($order, $context, $args=array()){

		$sections = THWCFD_Utils_Block::get_block_checkout_sections();
		if(is_array($sections)){
			$order_id     = $order->get_id();
			$sections = $this->prepare_order_meta_sections($order, $order_id, $sections, $context, $args);
		}
		return $sections;
	}

	private function prepare_order_meta_sections($order, $order_id, $sections, $context, $args=array()){
		$final_sections = array();
       
		if(is_array($sections)){
			
			foreach($sections as $sname => $section){
                $section_field_values = $order->get_meta( $sname );
                if(!empty($section_field_values )){
                    $section_data = $this->prepare_section_data($order, $section, $context, $section_field_values, $args);
                    $final_sections[$sname] = $section_data;
                }
			}
		}
		return $final_sections;
	}

	
	private function prepare_section_data($order, $section, $context, $section_field_values, $args=array()){
		$section_data = false;
		$fields = $this->get_order_meta_fields($order, $section, $context, $section_field_values, $args);

		if(!empty($fields)){
			$section_data = array(
				'section'  => $section,
				'fields'   => $fields,
			);
		}

		return $section_data;
	}

    private function get_order_meta_fields($order, $section, $context, $section_field_values, $args=array()){
		$order_meta_fields = array();

		if($section){
			$fields     = $section->fields;
            foreach($fields as $field_key => $field){
                if($this->is_show_field($field, $context, $args)){
                    if(!is_array($section_field_values) || !array_key_exists($field_key, $section_field_values)){
                        continue;
                    }
                    $field_value = $section_field_values[$field_key];
                    $field_value = apply_filters('thwcfe_order_meta_field_value', $field_value, $field_key, $order, $context);
                    $field->value = $field_value;
                    $order_meta_fields[$field_key] = $field;
                }
            }
		}
		return $order_meta_fields;
	}
	/***********************************************
	 **** PREPARE SECTIONS & FIELDS - END ****
	***********************************************/
}

endif;
