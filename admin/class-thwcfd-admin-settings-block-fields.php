<?php
/**
 * Woo Checkout Field Editor Settings General
 *
 * @link       https://themehigh.com
 * @since      2.1.0
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/classes
 */

defined( 'ABSPATH' ) || exit;

if(!class_exists('THWCFD_Admin_Settings_Block_Fields')):

class THWCFD_Admin_Settings_Block_Fields extends THWCFD_Admin_Settings{

	protected static $_instance = null;
	private $section_form = null;
	private $field_form = null;
	private $field_form_props = array();

	protected $tabs = '';
	protected $sections = '';

	public function __construct() {
		parent::__construct();
		$this->page_id    = 'fields';
		$this->c_type     = 'block';
		$this->section_id = 'contact';
	}

	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function init(){
		$this->section_form = new THWCFD_Admin_Form_Block_Section();
		$this->field_form   = new THWCFD_Admin_Form_Block_Field();
		$this->field_form_props = $this->field_form->get_field_form_props();;
		$this->render_page();
	}

	public function render_page(){
		$this->output_tabs();
		$this->output_checkout_types();
		$this->output_sections();
		$this->output_content();
	}

	public function render_checkout_fields_heading_row(){
		?>
		<th class="sort"></th>
		<th class="check-column"><input type="checkbox" style="margin:0px 4px -1px -1px;" onclick="thwcfdSelectAllCheckoutFields(this)"/></th>
		<th class="name"><?php esc_html_e('Name', 'woo-checkout-field-editor-pro'); ?></th>
		<th class="id"><?php esc_html_e('Type', 'woo-checkout-field-editor-pro'); ?></th>
		<th><?php esc_html_e('Label', 'woo-checkout-field-editor-pro'); ?></th>
		<th><?php esc_html_e('Placeholder', 'woo-checkout-field-editor-pro'); ?></th>
		<th><?php esc_html_e('Validations', 'woo-checkout-field-editor-pro'); ?></th>
        <th class="status"><?php esc_html_e('Required', 'woo-checkout-field-editor-pro'); ?></th>
		<th class="status"><?php esc_html_e('Enabled', 'woo-checkout-field-editor-pro'); ?></th>	
        <th class="action"><?php esc_html_e('Edit', 'woo-checkout-field-editor-pro'); ?></th>	
        <?php
	}
	
	public function render_actions_row($section){
		?>
        <th colspan="6">
            <button type="button" class="button button-primary" onclick="thwcfdOpenNewFieldForm('<?php echo esc_js($section); ?>', 'block')">+ <?php esc_html_e( 'Add field', 'woo-checkout-field-editor-pro' ); ?></button>
            <button type="button" class="button" onclick="thwcfdRemoveSelectedFields()"><?php esc_html_e('Remove', 'woo-checkout-field-editor-pro'); ?></button>
            <button type="button" class="button" onclick="thwcfdEnableSelectedFields()"><?php esc_html_e('Enable', 'woo-checkout-field-editor-pro'); ?></button>
            <button type="button" class="button" onclick="thwcfdDisableSelectedFields()"><?php esc_html_e('Disable', 'woo-checkout-field-editor-pro'); ?></button>
        </th>
        <th colspan="4">
        	<input type="submit" name="save_fields" class="button-primary" value="<?php esc_attr_e( 'Save changes', 'woo-checkout-field-editor-pro' ) ?>" style="float:right" />
            <input type="submit" name="reset_fields" class="button" value="<?php esc_attr_e( 'Reset to default fields', 'woo-checkout-field-editor-pro' ) ?>" style="float:right; margin-right: 5px;" 
			onclick="return confirm('<?php esc_html_e('Are you sure you want to reset to the default fields? All your changes will be deleted, and all sections will be restored to their default fields.', 'woo-checkout-field-editor-pro' ); ?>')"/>
        </th>  
    	<?php 
	}
	private function truncate_str($string, $offset){
		if($string && strlen($string) > $offset){
			$string = trim(substr($string, 0, $offset)).'...';
		}
		
		return $string;
	}

	private function render_fields_table_heading(){
		?>
		<th class="sort"></th>
		<th class="check-column"><input type="checkbox" style="margin:0px 4px -1px -1px;" onclick="thwcfdSelectAllCheckoutFields(this)"/></th>
		<th class="name"><?php esc_html_e('Name', 'woo-checkout-field-editor-pro'); ?></th>
		<th class="type"><?php esc_html_e('Type', 'woo-checkout-field-editor-pro'); ?></th>
		<th class="label"><?php esc_html_e('Label', 'woo-checkout-field-editor-pro'); ?></th>
		<th class="placeholder"><?php esc_html_e('Placeholder', 'woo-checkout-field-editor-pro'); ?></th>
		<th class="validate"><?php esc_html_e('Validations', 'woo-checkout-field-editor-pro'); ?></th>
        <th class="status"><?php esc_html_e('Required', 'woo-checkout-field-editor-pro'); ?></th>
		<th class="status"><?php esc_html_e('Enabled', 'woo-checkout-field-editor-pro'); ?></th>	
        <th class="actions align-center"><?php esc_html_e('Actions', 'woo-checkout-field-editor-pro'); ?></th>	
        <?php
	}

	public function output_content() {

		$section_name = $this->get_current_section();
		$action = isset($_POST['f_action']) ? sanitize_text_field(wp_unslash($_POST['f_action'])) : false;

		if(isset($_POST['reset_fields']))
			$this->reset_to_default();

		$section = THWCFD_Utils_Block::get_block_checkout_section($section_name);

		if($action === 'new' || $action === 'copy')
			$this->save_or_update_field($section, $action);	
			
		if($action === 'edit')
			$this->save_or_update_field($section, $action);
		
		if(isset($_POST['save_fields']))
			$this->save_fields($section);

		$ignore_fields = apply_filters('thwcfd_ignore_fields', array());
		
		?>            
        <div class="wrap woocommerce"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>                
		    <form method="post" id="thwcfd_checkout_fields_form" action="">
            <table id="thwcfd_checkout_fields" class="wc_gateways widefat thpladmin_fields_table" cellspacing="0">
            	<thead>
                    <tr><?php $this->render_actions_row($section_name); ?></tr>
                    <tr><?php $this->render_fields_table_heading(); ?></tr>						
                </thead>
                <tfoot>
                    <tr><?php $this->render_fields_table_heading(); ?></tr>
                    <tr><?php $this->render_actions_row($section_name); ?></tr>
                </tfoot>
                <tbody class="ui-sortable">
                <?php 
				if(THWCFD_Utils_Section::is_valid_section($section) && THWCFD_Utils_Section::has_fields($section)){
					$i=0;										
					foreach( $section->fields as $field ) :	
						$name = $field->get_property('name');
						$type = $field->get_property('type');
						$is_enabled = $field->get_property('enabled') ? 1 : 0;
						$props_json = htmlspecialchars($this->get_property_set_json($name ,$field));
						$options_json = htmlspecialchars($field->get_property('options_json'));
					
						//$disabled_actions = $is_enabled ? in_array($type, THWCFE_Utils_Field::$SPECIAL_FIELD_TYPES) : 1;
						$disable_actions = in_array($name, $ignore_fields) ? true : false;
						$disable_edit = $disable_actions || !$is_enabled ? true : false;
						//$disable_copy = $disable_actions || in_array($type, THWCFD_Utils_Field::$SPECIAL_FIELD_TYPES) ? true : false;
						$disable_copy = false;
						$disabled_cb = $disable_actions ? 'disabled' : '';
						
						$non_editable_field = $name === 'email' ? ' not-editable' : '';
						$not_deletable_field = $name === 'country' ? ' not-deletable' : '';
					?>
						<tr class="row_<?php echo esc_attr($i); echo($is_enabled === 1 ? '' : ' thpladmin-disabled');  echo esc_attr($non_editable_field); echo esc_attr($not_deletable_field);?>">
							<td width="1%" class="sort ui-sortable-handle">
								<input type="hidden" name="f_name[<?php echo esc_attr($i); ?>]" class="f_name" value="<?php echo esc_attr($name); ?>" />
								<input type="hidden" name="f_order[<?php echo esc_attr($i); ?>]" class="f_order" value="<?php echo esc_attr($i); ?>" />
								<input type="hidden" name="f_deleted[<?php echo esc_attr($i); ?>]" class="f_deleted" value="0" />
								<input type="hidden" name="f_enabled[<?php echo esc_attr($i); ?>]" class="f_enabled" value="<?php echo esc_attr($is_enabled); ?>" />
								<input type="hidden" name="f_selected[<?php echo esc_attr($i); ?>]" class="f_selected" value="0" />
								
								<input type="hidden" name="f_props[<?php echo esc_attr($i); ?>]" class="f_props" value='<?php echo esc_attr($props_json); ?>' />
								<input type="hidden" name="f_options[<?php echo esc_attr($i); ?>]" class="f_options" value="<?php echo esc_attr($options_json); ?>" />
							</td>
							<td class="td_select"><input type="checkbox" name="select_field" <?php echo esc_attr($disabled_cb); ?> onchange="thwcfdCheckoutFieldSelected(this)"/></td>
							
							<?php
							$field_props_display = $this->field_form->get_field_form_props_display();

							foreach( $field_props_display as $pname => $property ){							
								$pvalue = $field->get_property($pname);
								$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
								
								if($property['type'] == 'checkbox'){
									$pvalue = $pvalue ? 1 : 0;
								}
								
								if(isset($property['status']) && $property['status'] == 1){
									$statusHtml = $pvalue == 1 ? '<span class="dashicons dashicons-yes tips" data-tip="'. esc_attr(__('Yes', 'woo-checkout-field-editor-pro')).'"></span>' : '-';
									?>
									<td class="td_<?php echo esc_attr($pname); ?> status"><?php echo wp_kses($statusHtml, array('span' => array('class' => true))); ?></td>
									<?php
								}else{
									$pvalue = esc_attr($pvalue);
									$pvalue = stripslashes($pvalue);
									$tooltip = '';

									$len = isset($property['len']) ? $property['len'] : false;

									if(is_numeric($len) && $len > 0){
										$tooltip = $pvalue;
										$pvalue = $this->truncate_str($pvalue, $len);
									}

									?>
									<td class="td_<?php echo esc_attr($pname); ?>">
										<label title="<?php echo esc_attr($tooltip); ?>"><?php echo esc_html($pvalue); ?></label>
									</td>
									<?php
								}
							}
							?>
							
							<td class="td_actions" align="center">
								<?php if($disable_edit){ ?>
									<span class="f_edit_btn dashicons dashicons-edit disabled"></span>
								<?php }else{ ?>
									<span class="f_edit_btn dashicons dashicons-edit tips" data-tip="<?php esc_attr_e('Edit Field', 'woo-checkout-field-editor-pro'); ?>"  
									onclick="thwcfdOpenEditFieldForm(this, <?php echo esc_js($i); ?>, 'block')"></span>
								<?php } ?>

								<?php if($disable_copy){ ?>
									<span class="f_copy_btn dashicons dashicons-admin-page disabled"></span>
								<?php }else{ ?>
									<span class="f_copy_btn dashicons dashicons-admin-page tips" data-tip="<?php esc_attr_e('Duplicate Field', 'woo-checkout-field-editor-pro'); ?>"  
									onclick="thwcfdOpenCopyFieldForm(this, <?php echo esc_js($i); ?>, 'block')"></span>
								<?php } ?>

							</td>
						</tr>						
	                <?php $i++; endforeach;
                }else{
					?>
					<tr>
						<td colspan="10" class="empty-msg-row">
							<?php esc_html_e('No checkout fields found. Click on Add Field button to create new fields.', 'woo-checkout-field-editor-pro'); ?>
						</td>
					</tr>
					<?php
				}
				
                ?>
                </tbody>
            </table> 
			<?php wp_nonce_field( 'thwcfd_section_block_fields', 'thwcfd_security_manage_block_fields' ); ?>
            </form>
            <?php
           	$this->section_form->output_section_forms();
        	$this->field_form->output_field_forms($section_name);
			?>
    	</div>
    <?php
	}

	private function get_property_set_json($name , $field){
		if(THWCFD_Utils_Field::is_valid_field($field)){
			$props_set = array();
			foreach( $this->field_form_props as $pname => $property ){
				$pvalue = $field->get_property($pname);
				$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
				$pvalue = esc_attr($pvalue);
				
				if($property['type'] == 'checkbox'){
					$pvalue = $pvalue ? 1 : 0;
				}
				$props_set[$pname] = $pvalue;
			}
						
			$props_set['custom'] = THWCFD_Utils_Field::is_custom_field($field) ? 1 : 0;
			$props_set['order'] = $field->get_property('order');
			$props_set['priority'] = $field->get_property('priority');
			$props_set['price_field'] = $field->get_property('price_field') ? 1 : 0;
			$props_set['rules_action'] = $field->get_property('rules_action');
			$props_set['rules_action_ajax'] = $field->get_property('rules_action_ajax');
						
			return wp_json_encode($props_set);
		}else{
		 	return '';
		}
	}

	private function save_or_update_field($section, $action) {
		$nonce = isset($_REQUEST['thwcfd_security_manage_block_field']) ? sanitize_text_field(wp_unslash($_REQUEST['thwcfd_security_manage_block_field'])) : false;
		$capability = THWCFD_Utils::wcfd_capability();
		if(!wp_verify_nonce($nonce, 'thwcfd_block_field_form') || !current_user_can($capability)){
			die();
		}

		try {
			$field = THWCFD_Utils_Field::prepare_field_from_posted_data($_POST, $this->field_form_props);
			$this->add_wpml_support($field);
			if(is_object($field) && isset($field->name) && !empty($field->name) && isset($field->type) && !empty($field->type)){
				if($action === 'edit'){
					$section = THWCFD_Utils_Section::update_field($section, $field);
				}else{
					$section = THWCFD_Utils_Section::add_field($section, $field);
				}

				$result = $this->update_block_section($section);
				
				if($result == true){
					$this->print_notices('Your changes were saved.', 'updated');
					do_action('thwcfe-checkout-fields-updated');
				}else{
					$this->print_notices('Your changes were not saved due to an error (or you made none!).', 'error');
				}
			}else{
				$this->print_notices('Your changes were not saved due to an error.', 'error');
			}
		} catch (Exception $e) {
			$this->print_notices('Your changes were not saved due to an error.', 'error');
		}
	}
	
	private function save_fields($section) {
		$nonse = isset($_REQUEST['thwcfd_security_manage_block_fields']) ? sanitize_text_field(wp_unslash($_REQUEST['thwcfd_security_manage_block_fields'])) : false;
		$capability = THWCFD_Utils::wcfd_capability();
		if(!wp_verify_nonce($nonse, 'thwcfd_section_block_fields') || !current_user_can($capability)){
			die();
		}
		try {
			if(THWCFD_Utils_Section::is_valid_section($section)){
				$f_names = !empty( $_POST['f_name'] ) ? $_POST['f_name'] : array();	
				$f_names = array_map('sanitize_key', $f_names);
				if(empty($f_names)){
					echo '<div class="error"><p> '. esc_html__('Your changes were not saved due to no fields found.', 'woo-checkout-field-editor-pro') .'</p></div>';
					return;
				}
				
				$f_order   = !empty( $_POST['f_order'] ) ? $_POST['f_order'] : array();	
				$f_order = array_map('absint', $f_order);
				$f_deleted = !empty( $_POST['f_deleted'] ) ? $_POST['f_deleted'] : array();
				$f_deleted = array_map('absint', $f_deleted);
				$f_enabled = !empty( $_POST['f_enabled'] ) ? $_POST['f_enabled'] : array();
				$f_enabled = array_map('absint', $f_enabled);		
				$sname = $section->get_property('name');
				$field_set = THWCFD_Utils_Section::get_fields($section);
				
				$max = max( array_map( 'absint', array_keys( $f_names ) ) );
				for($i = 0; $i <= $max; $i++) {
					$name = $f_names[$i];
					
					if(isset($field_set[$name])){
						if(isset($f_deleted[$i]) && $f_deleted[$i] == 1){
							unset($field_set[$name]);
							continue;
						}
						
						$field = $field_set[$name];
						$field->set_property('order', isset($f_order[$i]) ? trim(stripslashes($f_order[$i])) : 0);
						$field->set_property('enabled', isset($f_enabled[$i]) ? trim(stripslashes($f_enabled[$i])) : 0);
						
						$field_set[$name] = $field;
					}
				}
				$section->set_property('fields', $field_set);
				$section = THWCFD_Utils_Section::sort_fields($section);
				$result1 = $this->update_block_section($section);
				//$result2 = $this->update_options_name_title_map();
				
				if ($result1 == true) {
					$this->print_notices('Your changes were saved.', 'updated');
					do_action('thwcfe-checkout-fields-updated');
				} else {
					$this->print_notices('Your changes were not saved due to an error (or you made none!).', 'error');
				}
			}
		} catch (Exception $e) {
			$this->print_notices('Your changes were not saved due to an error.', 'error');
		}
	}
	public function reset_to_default() {

		$nonse = isset($_REQUEST['thwcfd_security_manage_block_fields']) ? sanitize_text_field(wp_unslash($_REQUEST['thwcfd_security_manage_block_fields'])) : false;
		$capability = THWCFD_Utils::wcfd_capability();
		if(!wp_verify_nonce($nonse, 'thwcfd_section_block_fields') || !current_user_can($capability)){
			die();
		}
		delete_option(THWCFD_Utils_Block::OPTION_KEY_BLOCK_SECTIONS);
		$this->print_notices(__('Checkout fields successfully reset', 'woo-checkout-field-editor-pro'), 'updated');
	}
	
	private function prepare_field_from_posted_data($posted){
		$field_props = $this->field_form_props;
		$field = array();
		
		foreach ($field_props as $pname => $prop) {
			$iname  = 'i_'.$pname;
			
			$pvalue = '';
			if($prop['type'] === 'checkbox'){
				$pvalue = isset($posted[$iname]) && $posted[$iname] ? 1 : 0;
			}else if(isset($posted[$iname])){
				//$pvalue = is_array($posted[$iname]) ? implode(',', $posted[$iname]) : trim(stripslashes($posted[$iname]));
				// $pvalue = is_array($posted[$iname]) ? $posted[$iname] : trim(stripslashes($posted[$iname]));

				if(($pname === 'type') || ($pname === 'name')){
					$pvalue = !empty($posted[$iname]) ? sanitize_key($posted[$iname]) : "";
				}else if(($pname === 'label')){
					//$pvalue = !empty($posted[$iname]) ? htmlentities(stripslashes($posted[$iname])) : "";
					$pvalue = !empty($posted[$iname]) ? wp_unslash(wp_filter_post_kses($posted[$iname])) : "";
				}else if(($pname === 'validate')){
					$pvalue = !empty($posted[$iname]) ? (array) $posted[$iname] : array();
					$pvalue = array_map( 'sanitize_key', $pvalue );
				}else if($pname === 'class'){
					//$pvalue = is_string($pvalue) ? array_map('trim', explode(',', $pvalue)) : $pvalue;
					$pvalue = !empty($posted[$iname]) ? $posted[$iname] : '';
					$pvalue = is_string($pvalue) ? preg_split('/(\s*,*\s*)*,+(\s*,*\s*)*/', $pvalue) : array();
					$pvalue = array_map('sanitize_key', $pvalue);
				}else{
					$pvalue = !empty($posted[$iname]) ? sanitize_text_field(wp_unslash($posted[$iname])) : "";
				}
			}

			$field[$pname] = $pvalue;
		}

		$type = isset($field['type']) ? $field['type'] : '';
		if(!$type){
			$type = isset($posted['i_otype']) ? sanitize_key($posted['i_otype']) : '';
			$field['type'] = $type;
		}

		$name = isset($field['name']) ? $field['name'] : '';
		if(!$name){
			$field['name'] = isset($posted['i_oname']) ? sanitize_key($posted['i_oname']) : '';
		}

		if($type === 'select' || $type === 'multiselect'){
			$field['validate'] = '';

		}else if($type === 'radio'){
			$field['validate'] = '';
			$field['placeholder'] = '';

		}else if($type === 'number'){
			$field['validate'] = array('number');

		}else if($type === 'checkbox'){
			if(isset($posted['i_default'])){
				$field['default'] = sanitize_text_field($posted['i_default']);
			}else{
				$field['default'] = '';
			}

		}

		if($type === 'select' || $type === 'radio' || $type === 'checkboxgroup' || $type === 'multiselect'){
			$options_json = isset($posted['i_options_json']) ? trim(stripslashes($posted['i_options_json'])) : '';
			$options_arr = THWCFD_Utils::prepare_options_array($options_json, $type);

			$keys = array_keys($options_arr);
			// $keys = array_map('sanitize_key', $keys);
			$keys = array_map('sanitize_text_field', $keys);

			$values = array_values($options_arr);
			$values = array_map('htmlspecialchars', $values);

			$options_arr = array_combine($keys, $values);

			$field['options'] = $options_arr;

			// // Sanitize default value same like option values
			// $default_value = isset($field['default']) ? $field['default'] : '';
			// if($default_value){
			// 	$field['default'] = sanitize_key($default_value);
			// }


		}else{
			$field['options'] = '';
		}

		$field['autocomplete'] = isset($posted['i_autocomplete']) ? sanitize_text_field($posted['i_autocomplete']) : '';
		$field['priority'] = isset($posted['i_priority']) ? absint($posted['i_priority']) : '';
		//$field['custom'] = isset($posted['i_custom']) ? $posted['i_custom'] : '';
		$field['custom'] = isset($posted['i_custom']) && $posted['i_custom'] ? 1 : 0;
		
		return $field;
	}

	public function update_block_section($section){
		   
		if(THWCFD_Utils_Section::is_valid_section($section)){
		   $sections = THWCFD_Utils_Block::get_block_checkout_sections();
		   $sections = is_array($sections) ? $sections : array();
		   $sections[$section->name] = $section;
		   $result1 = $this->save_block_sections($sections);
		   return $result1;
	   }
	   return false;
    }
	public function save_block_sections($sections){
		
		$result = update_option(THWCFD_Utils_Block::OPTION_KEY_BLOCK_SECTIONS , $sections, 'no');
		return $result;
	}

	
	/******* TABS & SECTIONS *******/
	/*******************************/
	public function get_current_tab(){
		return isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'fields';
	}

	public function get_current_c_type(){
		return isset( $_GET['c_type'] ) ? sanitize_key( $_GET['c_type'] ) : 'block';
	}
	
	public function get_current_section(){
		$tab = $this->get_current_tab();
		$c_type = $this->get_current_c_type();
		$section = '';
		if($tab === 'fields' && $c_type === 'block'){
			$section = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : 'contact';
		}
		return $section;
	}

	public function output_checkout_types(){
		$selected_checkout = $this->get_current_c_type();
		$c_url = $this->get_admin_url($this->page_id, 'classic');
		$b_url = $this->get_admin_url($this->page_id, 'block');
		$tt_content = esc_html("You're on the Classic Checkout Field Editor right now. If your store is not using Classic Checkout, fields you add here won’t appear on the checkout page. Unsure which checkout type your store is using?", 'woo-checkout-field-editor-pro');
		if ( !empty( $_GET['c_type'] ) && 'block' == sanitize_key($_GET['c_type'] )) {
			$tt_content = esc_html("You're on the Block Checkout Field Editor right now. If your store is not using Block Checkout, fields you add here won’t appear on the checkout page. Unsure which checkout type your store is using?", 'woo-checkout-field-editor-pro');
		}
		?>
		<div class="th-ct-wrap">
			<div class="th-ct-tabs">
				<a href="<?php echo esc_url($c_url); ?>" class="ct-tab <?php echo $selected_checkout === 'classic' ? 'active' : ''; ?>">Classic Checkout</a>
				<a href="<?php echo esc_url($b_url); ?>" class="ct-tab <?php echo $selected_checkout === 'block' ? 'active' : ''; ?>">Block Checkout</a>
			</div>
			<div class="ct-info" id="th_info_container">
				<img src="<?php echo esc_url(THWCFD_ASSETS_URL_ADMIN . 'images/info.svg'); ?>" alt="Block Checkout">
				<div class="ct-info-box" id="infoBox" style="display: none;">
					<p> <?php echo esc_html($tt_content); ?></p>
				</div>
			</div>
		</div>
		
		<?php
	}

	public function output_tabs(){
		$current_tab = $this->get_current_tab();

		if(empty($this->tabs)){
			return;
		}
		
		echo '<h2 class="thpladmin-tabs nav-tab-wrapper woo-nav-tab-wrapper">';
		foreach( $this->tabs as $id => $label ){
			$active = ( $current_tab == $id ) ? 'nav-tab-active' : '';
			//$label  = __($label, 'woo-checkout-field-editor-pro');
			echo '<a class="nav-tab '.esc_attr($active).'" href="'. esc_url($this->get_admin_url($id)) .'">'. esc_html($label).'</a>';
		}
		echo '</h2>';	
	}
	
	public function output_sections() {
		
		$result = false;
		$s_action = isset($_POST['s_action']) ? sanitize_text_field(wp_unslash($_POST['s_action'])) : false;

		if($s_action == 'edit'){
			$result = $this->edit_section();
		}
		//$this->sort_sections($sections);
		$current_section = $this->get_current_section();
		$sections = THWCFD_Utils_Block::get_block_checkout_sections();

		$array_keys = array_keys( $sections );
				
		echo '<ul class="thpladmin-sections">';
		$i=0;
		foreach( $sections as $name => $section ){
			

			if(!THWCFD_Utils_Section::is_valid_section($section)){
				continue;
			}
	
			if( $name !== 'address' && $name !== 'contact' && $name !== 'additional_info'){
				// section will add after available block area in Additional order info block
				continue;
			}
			$url = $this->get_admin_url($this->page_id, $this->c_type, sanitize_title($name));
			$props_json = htmlspecialchars(THWCFD_Utils_Section::get_property_json($section, 'block'));
			$title = wp_strip_all_tags($section->get_property('title'));
			
			echo '<li><a href="'. esc_url($url) .'" class="'. ($current_section == $name ? 'current' : '') .'">'. esc_html($title) .'</a></li>';
			if(THWCFD_Utils_Section::is_custom_section($section)){

				?>
                <li>
                	
                    <span class="s_edit_btn dashicons dashicons-edit tips" 
      					data-tip="<?php esc_attr_e('Edit Section', 'woo-checkout-field-editor-pro'); ?>" 
      					onclick="thwcfdOpenEditSectionForm(<?php echo esc_js($props_json); ?>, 'block')">
					</span>
                </li>
               
                <?php
			}
			echo '<li>';
			echo(end( $array_keys ) == $name ? '' : '<li style="margin-right: 5px;">|</li>');
			echo '</li>';

		}
		
		echo '</ul>';		
		
	}
	
	private function edit_section(){

		$nonse = isset($_REQUEST['thwcfd_security_manage_block_fields']) ? sanitize_text_field(wp_unslash($_REQUEST['thwcfd_security_manage_block_fields'])) : false;
		$capability = THWCFD_Utils::wcfd_capability();
		if(!wp_verify_nonce($nonse, 'thwcfd_block_section_form') || !current_user_can($capability)){
			die();
		}
		$result = false;
		$section  = THWCFD_Utils_Section::prepare_section_from_posted_data($_POST, 'edit', 'block');
		if($section){
			$name 	  = $section->get_property('name');
			$position = $section->get_property('position');
			$result = $this->update_block_section($section);
		}

		if($result == true){
			$this->print_notices('Section details updated successfully.', 'updated', true);
		}else{
			$this->print_notices('Section details not updated due to an error.', 'error', true);
		}	
	}
	
	public function get_admin_url($tab = false, $c_type = false, $section = false){
		$url = 'admin.php?page=checkout_form_designer';
		if($tab && !empty($tab)){
			$url .= '&tab='. $tab;
		}
		if($c_type && !empty($c_type)){
			$url .= '&c_type='. $c_type;
		}
		if($section && !empty($section)){
			$url .= '&section='. $section;
		}
		return admin_url($url);
	}

	private function add_wpml_support($field){

		if(isset($field->property_set) && $field->property_set){
			$field = $field->property_set;
			$context = 'woo-checkout-field-editor-pro';
			
			$label = isset($field['label']) ? $field['label'] : '';
			if($label){
				$name = 'Field label - ' . $label;
				do_action( 'wpml_register_single_string', 'woo-checkout-field-editor-pro', $name, $label );
			}

			$placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
			if($placeholder){
				$name = 'Field placeholder - ' . $placeholder;
				do_action( 'wpml_register_single_string', 'woo-checkout-field-editor-pro', $name, $placeholder );
			}

			$options = isset($field['options']) ? $field['options'] : '';
			if($options){
				if(is_array($options)){
					$index = 0;
					foreach($options as $option_value => $option_text){
						$name = 'Field option text - ' . $option_text;
						do_action( 'wpml_register_single_string', 'woo-checkout-field-editor-pro', $name, $option_text );
						$index++;
					}
				}
			}
		}
		
	}
}

endif;
