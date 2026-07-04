
<?php
/**
 * The admin section forms functionalities.
 *
 * @link       https://themehigh.com
 * @since      2.1.0
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD_Admin_Form_Block_Section')):

class THWCFD_Admin_Form_Block_Section extends THWCFD_Admin_Form{
	private $section_props = array();

	public function __construct() {
		$this->section_props = $this->get_section_form_props();
	}

	public function get_section_form_props(){
		
		//$html_text_tags = $this->get_html_text_tags();

		$suffix_types = array(
			'number' => 'Number',
			'alphabet' => 'Alphabet',
			'none' => 'None',
		);

		$suffix_types_1 = array(
			'number' => 'Number',
			'alphabet' => 'Alphabet',
		);
		
		return array(
		
			'name' 		 => array('name'=>'name', 'label'=>'Name/ID', 'type'=>'text', 'required'=>1),
			'position' 	 => array('name'=>'position', 'label'=>'Display Position', 'type'=>'hidden', 'note' => 'position can be changed from the Block Editor', 'value' => 'set_from_block' ),
			'title' 	  => array('name'=>'title', 'label'=>'Title', 'type'=>'text', 'required'=>1 ),
			'subtitle'    => array('name'=>'subtitle', 'label'=>'Description', 'type'=>'text'),
			'show_title' => array('name'=>'show_title', 'label'=>'Show section title in checkout page.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>1),
			
		);
	}

	public function output_section_forms(){
		?>
        <div id="thwcfd_section_form_pp" class="thpladmin-modal-mask thadmin-block-form">
          <?php $this->output_popup_form_section(); ?>
        </div>
        <?php
	}

	/*****************************************/
	/********** POPUP FORM WIZARD ************/
	/*****************************************/

	private function output_popup_form_section(){
		?>
		<div class="thpladmin-modal">
			<div class="modal-container">
				<span class="modal-close" onclick="thwcfdCloseModal(this)">×</span>
				<div class="modal-content">
					<div class="modal-body">
						<div class="form-wizard wizard">
							<aside>
								<side-title class="wizard-title">Save Section</side-title>
								<ul class="pp_nav_links">
									<li class="text-primary active first" data-index="0">
										<i class="dashicons dashicons-admin-generic text-primary"></i>Basic Info
										<i class="i i-chevron-right dashicons dashicons-arrow-right-alt2"></i>
									</li>
								</ul>
							</aside>
							<main class="form-container main-full">
								<form method="post" id="thwcfd_section_form" action="">
									<input type="hidden" name="s_action" value="" />
									<input type="hidden" name="s_name" value="" />
						
									<div class="data-panel data_panel_0">
										<?php $this->render_form_tab_general_info(); ?>
									</div>
									<?php wp_nonce_field('thwcfd_block_section_form', 'thwcfd_security_manage_block_fields' ); ?>
								</form>
							</main>
							<footer>
								<span class="Loader"></span>
								<div class="btn-toolbar">
									<button class="save-btn pull-right btn btn-primary" onclick="thwcfdSaveSection(this)">
										<span>Save & Close</span>
									</button>
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
	private function render_form_tab_general_info(){
		$this->render_form_tab_main_title('Basic Details');

		?>
		<div style="display: inherit;" class="data-panel-content">
			<div class="err_msgs"></div>
			<table class="thwcfd_pp_table">
				<?php
				$this->render_form_elm_row($this->section_props['name']);
				$this->render_form_elm_row($this->section_props['title']);
				$this->render_form_elm_row($this->section_props['subtitle']);
				$this->render_form_elm_row($this->section_props['position']);
				$this->render_form_elm_row_cb($this->section_props['show_title']);
			
				?>
			</table>
		</div>
		<?php
	}
	
}

endif;