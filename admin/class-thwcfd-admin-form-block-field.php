<?php
/**
 * The admin field forms functionalities.
 *
 * @link       https://themehigh.com
 * @since      2.1.0
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/admin
 */

if(!class_exists('THWCFD_Admin_Form_Block_Field')):

class THWCFD_Admin_Form_Block_Field extends THWCFD_Admin_Form{
	private $field_props = array();

	public function __construct() {
		$this->init_constants();
	}

	private function init_constants(){
		$this->field_props = $this->get_field_form_props();
	}

	private function get_field_types(){
		return array(
			'text' => 'Text', 
			'checkbox' => 'Checkbox',
			'select' => 'Select', 
			'radio' => 'Radio',
			'number' => 'Number',
			'url' => 'URL',
			'email' => 'Email',	
			'paragraph' => 'Paragraph',
			'heading' => 'Heading',
			'textarea' => 'Textarea',
		);
	}

	public function get_field_form_props(){

		$field_types = $this->get_field_types();
		$validations = array(
			'' => '',
			'email' => 'Email',
			'phone' => 'Phone',
			'url'   => 'Url',
		);
		$custom_validators = THWCFD_Utils::get_settings('custom_validators');
		if(is_array($custom_validators)){
			foreach( $custom_validators as $vname => $validator ) {
				$validations[$vname] = $validator['label'];
			}
		}
		
		$confirm_validators = THWCFD_Utils::get_settings('confirm_validators');
		if(is_array($confirm_validators)){
			foreach( $confirm_validators as $vname => $validator ) {
				$validations[$vname] = $validator['label'];
			}
		}
			
		$html_text_tags = array(
			'h1' => 'H1',
			'h2' => 'H2',
			'h3' => 'H3',
			'h4' => 'H4',
			'h5' => 'H5',
			'h6' => 'H6',
		);
		
		return array(
			'name' 		  => array('type'=>'text', 'name'=>'name', 'label'=>'Name', 'required'=>1, 'hint_text'=>'The field name is considered its unique identifier. Ensure it is not repeated across different sections.'),
			'type' 		  => array('type'=>'select', 'name'=>'type', 'label'=>'Field Type', 'required'=>1, 'options'=>$field_types, 'onchange'=>"thwcfdFieldTypeChangeListner(this, 'block')"),
			'value' 	  => array('type'=>'text', 'name'=>'value', 'label'=>'Default Value'),
			'placeholder' => array('type'=>'text', 'name'=>'placeholder', 'label'=>'Placeholder'),
			'validate'    => array('type'=>'select', 'name'=>'validate', 'label'=>'Validations', 'placeholder'=>'Select validations', 'options'=>$validations),
			'cssclass'    => array('type'=>'text', 'name'=>'cssclass', 'label'=>'Wrapper Class', 'placeholder'=>'Separate classes with comma', 'value'=>''),
			
			'order_meta' => array('type'=>'checkbox', 'name'=>'order_meta', 'label'=>'Order Meta Data', 'value'=>'yes', 'checked'=>1),
			'user_meta'  => array('type'=>'checkbox', 'name'=>'user_meta', 'label'=>'User Meta Data', 'value'=>'yes', 'checked'=>0),
			
			'checked'   => array('type'=>'checkbox', 'name'=>'checked', 'label'=>'Checked by default', 'value'=>'yes', 'checked'=>1),
			'required'  => array('type'=>'checkbox', 'name'=>'required', 'label'=>'Required', 'value'=>'yes', 'checked'=>0, 'status'=>1),
			'clear' 	=> array('type'=>'checkbox', 'name'=>'clear', 'label'=>'Clear Row', 'value'=>'yes', 'checked'=>0, 'status'=>1),
			'enabled'   => array('type'=>'checkbox', 'name'=>'enabled', 'label'=>'Enabled', 'value'=>'yes', 'checked'=>1, 'status'=>1),
			
			'show_in_email' => array('type'=>'checkbox', 'name'=>'show_in_email', 'label'=>'Display in Admin Emails', 'value'=>'yes', 'checked'=>1),
			'show_in_email_customer' => array('type'=>'checkbox', 'name'=>'show_in_email_customer', 'label'=>'Display in Customer Emails', 'value'=>'yes', 'checked'=>1),
			'show_in_order' => array('type'=>'checkbox', 'name'=>'show_in_order', 'label'=>'Display in Admin Order Details', 'value'=>'yes', 'checked'=>1),
			'show_in_thank_you_page' => array('type'=>'checkbox', 'name'=>'show_in_thank_you_page', 'label'=>'Display in Customer Order Details', 'value'=>'yes', 'checked'=>1),
			'show_in_my_account_page' => array('type'=>'checkbox', 'name'=>'show_in_my_account_page', 'label'=>'Display in My Account Page', 'value'=>'yes', 'checked'=>0),
			
			'title'          => array('type'=>'text', 'name'=>'title', 'label'=>'Label', 'required'=>0),
			'title_class'    => array('type'=>'text', 'name'=>'title_class', 'label'=>'Label Class', 'placeholder'=>'Separate classes with comma'),
			'title_type'     => array('type'=>'select', 'name'=>'title_type', 'label'=>'Title Type', 'value'=>'h3', 'options'=>$html_text_tags),
			
			'autocomplete' 	=> array('type'=>'text', 'name'=>'autocomplete', 'label'=>'Autocomplete'),
			'country_field' => array('type'=>'text', 'name'=>'country_field', 'label'=>'Country Field Name'),
			'country' 		=> array('type'=>'text', 'name'=>'country', 'label'=>'Country'),
						
		);
	}

	public function get_field_form_props_display(){
		return array(
			'name'  => array('name'=>'name', 'type'=>'text'),
			'type'  => array('name'=>'type', 'type'=>'select'),
			'title' => array('name'=>'title', 'type'=>'text', 'len'=>40),
			'placeholder' => array('name'=>'placeholder', 'type'=>'text', 'len'=>30),
			'validate' => array('name'=>'validate', 'type'=>'text'),
			'required' => array('name'=>'required', 'type'=>'checkbox', 'status'=>1),
			'enabled'  => array('name'=>'enabled', 'type'=>'checkbox', 'status'=>1),
		);
	}

	public function output_field_forms( $section = '' ){
		$this->output_field_form_pp($section);
		$this->output_form_fragments($section);
	}

	private function output_field_form_pp( $section = ''){
		?>
        <div id="thwcfd_field_form_pp" class="thpladmin-modal-mask thadmin-block-form <?php echo esc_attr($section)?>">
          <?php $this->output_popup_form_fields( $section ); ?>
          ?>
        </div>
        <?php
	}

	/*****************************************/
	/********** POPUP FORM WIZARD ************/
	/*****************************************/
	private function output_popup_form_fields( $section = '' ){
		?>
		<div class="thpladmin-modal">
			<div class="modal-container">
				<span class="modal-close" onclick="thwcfdCloseModal(this)">×</span>
				<div class="modal-content">
					<div class="modal-body">
						<div class="form-wizard wizard">
							<aside>
								<side-title class="wizard-title">Save Field</side-title>
								<ul class="pp_nav_links">
									<li class="text-primary active first pp-nav-link-basic" data-index="0">
										<i class="dashicons dashicons-admin-generic text-primary"></i>Basic Info
										<i class="i i-chevron-right dashicons dashicons-arrow-right-alt2"></i>
									</li>
									<?php
									if($section !== 'address'){ ?>
										<li class="text-primary pp-nav-link-price" data-index="1">
											<i class="dashicons dashicons-cart text-primary"></i> Display Styles
											<i class="i i-chevron-right dashicons dashicons-arrow-right-alt2"></i>
										</li>
										
									<?php } ?>
									
								</ul>
							</aside>
							<main class="form-container main-full">
								<form method="post" id="thwcfd_field_form" action="">
									<input type="hidden" name="f_action" value="" />
									<input type="hidden" name="i_name_old" value="" >
									<input type="hidden" name="i_order" value="" >
									<input type="hidden" name="i_priority" value="" >
			                        <input type="hidden" name="i_options" value="" >
									<input type="hidden" name="i_rules" value="" >
									<input type="hidden" name="i_rules_ajax" value="" >
									<input type="hidden" name="i_repeat_rules" value="" >
									<input type="hidden" name="i_country_field" value="" >
									<input type="hidden" name="i_country" value="" >
									<input type="hidden" name="i_autocomplete" value="" >
									<input type="hidden" name="i_rowid" value="" />
                    				<input type="hidden" name="i_original_type" value="" />
									<input type="hidden" name="i_otype" value="" />

									<div class="data-panel data_panel_0">
										<?php $this->render_form_tab_general_info($section); ?>
									</div>
									<div class="data-panel data_panel_1">
										<?php $this->render_form_tab_display_details(); ?>
									</div>
									<?php wp_nonce_field( 'thwcfd_block_field_form', 'thwcfd_security_manage_block_field' ); ?>
								</form>
							</main>
							<footer>
								<span class="Loader"></span>
								<div class="btn-toolbar">
									<button class="save-btn pull-right btn btn-primary" onclick="thwcfdSaveField(this)">
										<span>Save & Close</span>
									</button>
									<?php
									if($section !== 'address'){ ?>
										<button class="next-btn pull-right btn btn-primary-alt" onclick="thwcfdWizardNext(this)">
											<span>Next</span><i class="i i-plus"></i>
										</button>
										<button class="prev-btn pull-right btn btn-primary-alt" onclick="thwcfdWizardPrevious(this)">
											<span>Back</span><i class="i i-plus"></i>
										</button>
									<?php } ?>
								</div>
							</footer>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/*----- TAB - General Info -----*/
	private function render_form_tab_general_info( $section = '' ){
		$this->render_form_tab_main_title('Basic Details');

		?>
		<div style="display: inherit;" class="data-panel-content">
			<?php
			$this->render_form_fragment_general($section);
			?>
			<table class="thwcfd_field_form_tab_general_placeholder thwcfd_pp_table thwcfd-general-info"></table>
		</div>
		<?php
	}

	/*----- TAB - Display Details -----*/
	private function render_form_tab_display_details(){
		$this->render_form_tab_main_title('Display Settings');

		?>
		<div style="display: inherit;" class="data-panel-content mt-10">
			<table class="thwcfd_pp_table compact thwcfd-display-info">
				<?php
				$this->render_form_elm_row($this->field_props['cssclass']);
				//$this->render_form_elm_row($this->field_props['input_class']);
				//$this->render_form_elm_row($this->field_props['title_class']);

				$this->render_form_elm_row_cb($this->field_props['show_in_email']);
				$this->render_form_elm_row_cb($this->field_props['show_in_email_customer']);
				$this->render_form_elm_row_cb($this->field_props['show_in_order']);
				$this->render_form_elm_row_cb($this->field_props['show_in_thank_you_page']);
				?>
			</table>
		</div>
		<?php
	}

	/*----- TAB - Tooltip Info -----*/
	/*
	private function render_form_tab_tooltip_info(){
		$this->render_form_tab_main_title('Tooltip Details');

		?>
		<div style="display: inherit;" class="data-panel-content">
			<table class="thwcfe_pp_table thwcfe-tooltip-info">
				<?php
				$this->render_form_elm_row($this->field_props['tooltip']);
				$this->render_form_elm_row($this->field_props['tooltip_size']);
				$this->render_form_elm_row_cp($this->field_props['tooltip_color']);
				$this->render_form_elm_row_cp($this->field_props['tooltip_bg_color']);
				//$this->render_form_elm_row_cp($this->field_props['tooltip_border_color']);
				?>
			</table>
		</div>
		<?php
	}
	*/

	

	/*-------------------------------*/
	/*------ Form Field Groups ------*/
	/*-------------------------------*/
	private function render_form_fragment_general($section = ''){

		$address_field_types = array(
			'text' => 'Text', 
			'checkbox' => 'Checkbox',
			'select' => 'Select',
		);
		$ad_field_props_type =  array('type'=>'select', 'name'=>'type', 'label'=>'Field Type', 'required'=>1, 'options'=>$address_field_types, 'onchange'=>"thwcfdFieldTypeChangeListner(this, 'block')");
		$field_props_type = $section === 'address' ?  $ad_field_props_type : $this->field_props['type'];

		?>
		<div class="err_msgs"></div>
        <table class="thwcfd_pp_table">
        	<?php
			$this->render_form_elm_row($field_props_type);
			$this->render_form_elm_row($this->field_props['name']);
			?>
        </table>  
        <?php
	}

	private function output_form_fragments( $section = ''){

		$this->render_form_field_inputtext($section);
		$this->render_form_field_select($section );		
		$this->render_form_field_radio($section);
		$this->render_form_field_checkbox( $section );
		$this->render_form_field_number($section);
		$this->render_form_field_url($section);
		$this->render_form_field_email($section);
		$this->render_form_field_paragraph($section);
		$this->render_form_field_heading($section);
		$this->render_form_field_textarea($section);
	
		$this->render_form_field_default();
	
	}

	private function render_form_field_inputtext( $section = '' ){
		?>
        <table id="thwcfd_field_form_id_text" class="thwcfd_pp_table" style="display:none;">
        	<?php
			$this->render_form_elm_row($this->field_props['title']);
			if( $section != 'address' ){
				$this->render_form_elm_row($this->field_props['value']);
			}
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta'], array(), $section);
			$this->render_form_elm_row_cb($this->field_props['user_meta'], array(), $section);
			?>
        </table>
        <?php   
	}

	private function render_form_field_email( $section = ''){
		?>
        <table id="thwcfd_field_form_id_email" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['title']);
			// $this->render_form_elm_row($this->field_props['description']);
			if( $section != 'address' ){
				$this->render_form_elm_row($this->field_props['value']);
			}
			//$this->render_form_elm_row($this->field_props['placeholder']);
			// $this->render_form_elm_row($this->field_props['maxlength']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta']);
			$this->render_form_elm_row_cb($this->field_props['user_meta']);
			?>    
        </table>
        <?php   
	}
	
	private function render_form_field_select( $section = '' ){
		?>
        <table id="thwcfd_field_form_id_select" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['title']);
			//$this->render_form_elm_row($this->field_props['description']);
			if( $section != 'address' ){
				$this->render_form_elm_row($this->field_props['value']);
			}
			$this->render_form_elm_row($this->field_props['placeholder']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta'], array(), $section);
			$this->render_form_elm_row_cb($this->field_props['user_meta'], array(), $section);
			//$this->render_form_elm_row_cb($this->field_props['disable_select2']);
			
			$this->render_form_fragment_h_spacing();
			$this->render_form_fragment_options();
			?>
        </table>
        <?php   
	}
	
	private function render_form_field_radio( $section = '' ){
		?>
        <table id="thwcfd_field_form_id_radio" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['title']);
			//$this->render_form_elm_row($this->field_props['description']);
			if( $section != 'address' ){
				$this->render_form_elm_row($this->field_props['value']);
			}

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta']);
			$this->render_form_elm_row_cb($this->field_props['user_meta']);

			$this->render_form_fragment_h_spacing();
			$this->render_form_fragment_options();
			?>
        </table>
        <?php   
	}
	
	private function render_form_field_checkbox( $section = '' ){
		$value_props = $this->field_props['value'];
		$value_props['label'] = __('Value', 'woo-checkout-field-editor-pro');

		?>
        <table id="thwcfd_field_form_id_checkbox" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['title']);
			//$this->render_form_elm_row($this->field_props['description']);
			//$this->render_form_elm_row($value_props);
			if( $section != 'address' ){
				$this->render_form_elm_row_cb($this->field_props['checked']);
			}
			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta'], array(), $section);
			$this->render_form_elm_row_cb($this->field_props['user_meta'], array(), $section);
			?>  
        </table>
        <?php   
	}

	private function render_form_field_country(){
		?>
        <table id="thwcfd_field_form_id_country" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['title']);
			$this->render_form_elm_row($this->field_props['description']);
			$this->render_form_elm_row($this->field_props['value']);
			$this->render_form_elm_row($this->field_props['placeholder']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta']);
			$this->render_form_elm_row_cb($this->field_props['user_meta']);
			?>    
        </table>
        <?php   
	}

	private function render_form_field_state(){
		?>
        <table id="thwcfd_field_form_id_state" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['title']);
			$this->render_form_elm_row($this->field_props['description']);
			$this->render_form_elm_row($this->field_props['country_field']);
			$this->render_form_elm_row($this->field_props['value']);
			$this->render_form_elm_row($this->field_props['placeholder']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta']);
			$this->render_form_elm_row_cb($this->field_props['user_meta']);
			?>    
        </table>
        <?php   
	}

	private function render_form_field_number( $section = '' ){
		?>
        <table id="thwcfd_field_form_id_number" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['title']);
			$this->render_form_elm_row($this->field_props['value']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta']);
			$this->render_form_elm_row_cb($this->field_props['user_meta']);
			?>    
        </table>
        <?php   
	}

	private function render_form_field_url( $section = '' ){
		?>
		<table id="thwcfd_field_form_id_url" class="thwcfd_field_form_table" width="100%" style="display:none;">
			<?php
			$this->render_form_elm_row($this->field_props['title']);
			$this->render_form_elm_row($this->field_props['value']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta']);
			$this->render_form_elm_row_cb($this->field_props['user_meta']);
			?>    
		</table>
		<?php   
	}

	private function render_form_field_paragraph( $section = '' ){
		$label_props = $this->field_props['title'];
		$label_props['label'] = 'Content';
		?>
		<table id="thwcfd_field_form_id_paragraph" class="thwcfd_field_form_table" width="100%" style="display:none;">
			<?php
			$this->render_form_elm_row($label_props);
	
			$this->render_form_elm_row_cb($this->field_props['enabled']);
			?>    
		</table>
		<?php   
	}

	private function render_form_field_heading( $section = '' ){
		?>
		<table id="thwcfd_field_form_id_heading" class="thwcfd_field_form_table" width="100%" style="display:none;">
			<?php
			$this->render_form_elm_row($this->field_props['title']);
			$this->render_form_elm_row($this->field_props['title_type']);
	
			$this->render_form_elm_row_cb($this->field_props['enabled']);
			?>    
		</table>
		<?php   
	}

	private function render_form_field_textarea( $section = '' ){
		$value_props = $this->field_props['value'];
		$value_props['type'] = 'textarea';
		?>
		<table id="thwcfd_field_form_id_textarea" class="thwcfd_field_form_table" width="100%" style="display:none;">
			<?php
			$this->render_form_elm_row($this->field_props['title']);
			$this->render_form_elm_row($value_props);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta']);
			$this->render_form_elm_row_cb($this->field_props['user_meta']);
			?>    
		</table>
		<?php   
	}

	private function render_form_field_default(){
		?>
        <table id="thwcfd_field_form_id_default" class="thwcfd_field_form_table" width="100%" style="display:none;">
            <?php
			$this->render_form_elm_row($this->field_props['title']);
			//$this->render_form_elm_row($this->field_props['description']);
			$this->render_form_elm_row($this->field_props['value']);
			//$this->render_form_elm_row($this->field_props['placeholder']);
			//$this->render_form_elm_row($this->field_props['maxlength']);
			$this->render_form_elm_row($this->field_props['validate']);

			$this->render_form_elm_row_cb($this->field_props['required']);
			$this->render_form_elm_row_cb($this->field_props['enabled']);

			$this->render_form_elm_row_cb($this->field_props['order_meta']);
			$this->render_form_elm_row_cb($this->field_props['user_meta']);
			?>    
        </table>
        <?php   
	}



	private function render_form_fragment_options(){
		
		?>
		<tr>
			<td class="sub-title"><?php esc_html_e('Options', 'woo-checkout-field-editor-pro'); ?></td>
			<?php $this->render_form_fragment_tooltip(); ?>
			<td></td>
		</tr>
		<tr>
			<td colspan="3" class="p-0">
				<table border="0" cellpadding="0" cellspacing="0" class="thwcfd-option-list thpladmin-options-table"><tbody>
					<tr>
						<td class="key"><input type="text" name="i_options_key[]" placeholder="Option Value"></td>
						<td class="value"><input type="text" name="i_options_text[]" placeholder="Option Text"></td>
						<!-- <td class="price"><input type="text" name="i_options_price[]" placeholder="Price"></td>
						<td class="price-type">    
							<select name="i_options_price_type[]">
								<option selected="selected" value="">Fixed</option>
								<option value="percentage">Percentage of Cart Contents Total</option>
								<option value="percentage_subtotal">Percentage of Subtotal</option>
								<option value="percentage_subtotal_ex_tax">Percentage of Subtotal Ex Tax</option>
							</select>
						</td> -->
						<td class="action-cell">
							<a href="javascript:void(0)" onclick="thwcfdAddNewOptionRow(this)" class="btn btn-tiny btn-primary" title="Add new option">+</a><a href="javascript:void(0)" onclick="thwcfeRemoveOptionRow(this)" class="btn btn-tiny btn-danger" title="Remove option">x</a><span class="btn btn-tiny sort ui-sortable-handle"></span>
						</td>
					</tr>
				</tbody></table>            	
			</td>
		</tr>
        <?php
	}

}

endif;