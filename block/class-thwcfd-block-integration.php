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

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks
 */
if(!class_exists('THWCFD_Block_Integration')):

class THWCFD_Block_Integration implements IntegrationInterface {

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {

		return 'thwcfe-block-integration';
	}
	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {

		$this->register_contact_info_block_frontend_scripts();
		$this->register_additional_sections_editor_scripts();
		$this->register_contact_info_block_editor_scripts();
		$this->register_additional_sections_frontend_scripts();

		add_action('wp_enqueue_scripts', array($this, 'register_styles'));
		add_action('admin_enqueue_scripts', array($this, 'register_styles'));
	}

	public function register_styles(){

		if (function_exists('has_block') && has_block('woocommerce/checkout')) {
			$this->register_contact_info_block_frontend_styles();
			$this->register_contact_info_block_editor_styles();
			$this->register_additional_sections_frontend_styles();
		}
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {

		//$script_handles = ['thwcfe-block-integration', 'thwcfe-contact-info-section-frontend', 'thwcfe-additional-sections-frontend'];
		$script_handles = ['thwcfe-additional-sections-frontend', 'thwcfe-contact-info-section-frontend'];
        return $script_handles;
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		$script_edit_handles = ['thwcfe-additional-sections-editor', 'thwcfe-contact-info-section-editor'];	
		return $script_edit_handles;
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {

		$all_sections = THWCFD_Utils_Block::get_block_checkout_sections();
		$default_keys = ['contact', 'address', 'order', 'additional_info'];
		$additional_sections = array_diff_key($all_sections, array_flip($default_keys));
		$cfe_free_sections = array_intersect_key($all_sections, array_flip($default_keys));
		if ( apply_filters( 'thwcfd_decode_block_field_entities', false ) ) {
			$this->decode_block_field_entities( $additional_sections );
			$this->decode_block_field_entities( $cfe_free_sections );
		}
		$data = [
			'additionalSections' => $additional_sections,
			'allSections' => $cfe_free_sections,
		];
		return $data;
	}

	private function decode_block_field_entities( &$sections ) {
		if ( ! is_array( $sections ) ) {
			return;
		}
		foreach ( $sections as $section ) {
			if ( ! isset( $section->fields ) || ! is_array( $section->fields ) ) {
				continue;
			}
			foreach ( $section->fields as $field ) {
				if ( ! is_object( $field ) ) {
					continue;
				}
				if ( isset( $field->title ) && is_string( $field->title ) ) {
					$field->title = wp_specialchars_decode( $field->title, ENT_QUOTES );
				}
				if ( isset( $field->property_set['default'] ) && is_string( $field->property_set['default'] ) ) {
					$field->property_set['default'] = wp_specialchars_decode( $field->property_set['default'], ENT_QUOTES );
				}
			}
		}
	}

     /**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return THWCFD_VERSION;
	}

	/**
     * Returns an array of style handles to enqueue in the frontend context.
     *
     * @return string[]
     */
	public function register_contact_info_block_editor_styles(){

		$style_path = '/assets/dist/style-contact-info-section.css';
		$style_url = plugins_url( $style_path, __FILE__ );
		$absolute_path = plugin_dir_path(__FILE__) . '/assets/dist/style-contact-info-section.css';
		wp_enqueue_style(
			'thwcfe-contact-info-section-editor',
			$style_url,
			[],
			$this->get_file_version( $absolute_path )
		);
	}

	public function register_contact_info_block_editor_scripts() {

		$script_path       = '/assets/dist/contact-info-section.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = dirname( __FILE__ ) . '/assets/dist/contact-info-section.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_asset_path ),
			];

		wp_register_script(
			'thwcfe-contact-info-section-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'thwcfe-contact-info-section-editor',
			'woo-checkout-field-editor-pro',
			dirname( __FILE__ ) . '/languages'
		);
	}

	
	public function register_contact_info_block_frontend_styles(){
		$style_path = '/assets/dist/style-contact-info-section-frontend.css';
		$style_url = plugins_url( $style_path, __FILE__ );
		$absolute_path = plugin_dir_path(__FILE__) . '/assets/dist/style-contact-info-section-frontend.css';
		wp_enqueue_style(
			'thwcfe-contact-info-section-frontend',
			$style_url,
			[],
			$this->get_file_version( $absolute_path )
		);
	}

	public function register_contact_info_block_frontend_scripts() {

		$script_path       = '/assets/dist/contact-info-section-frontend.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = dirname( __FILE__ ) . '/assets/dist/contact-info-section-frontend.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_asset_path ),
			];

		wp_register_script(
			'thwcfe-contact-info-section-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'thwcfe-contact-info-section-frontend',
			'woo-checkout-field-editor-pro',
			dirname( __FILE__ ) . '/languages'
		);
	}

	public function register_additional_sections_editor_scripts(){

		$script_path       = '/assets/dist/additional-sections.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = dirname( __FILE__ ) . '/assets/dist/additional-sections.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_asset_path ),
			];

		wp_register_script(
			'thwcfe-additional-sections-editor',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'thwcfe-additional-sections-editor',
			'woo-checkout-field-editor-pro',
			dirname( __FILE__ ) . '/languages'
		);
	}

	public function register_additional_sections_editor_styles(){

		$style_path = '/assets/dist/style-additional-sections.css';
		$style_url = plugins_url( $style_path, __FILE__ );
		$absolute_path = plugin_dir_path(__FILE__) . '/assets/dist/style-additional-sections.css';
		wp_enqueue_style(
			'thwcfe-additional-sections-editor',
			$style_url,
			[],
			$this->get_file_version( $absolute_path )
		);
	}
	public function register_additional_sections_frontend_scripts(){

		$script_path       = '/assets/dist/additional-sections-frontend.js';
		$script_url        = plugins_url( $script_path, __FILE__ );
		$script_asset_path = dirname( __FILE__ ) . '/assets/dist/additional-sections-frontend.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_asset_path ),
			];

		wp_register_script(
			'thwcfe-additional-sections-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'thwcfe-additional-sections-frontend',
			'woo-checkout-field-editor-pro',
			dirname( __FILE__ ) . '/languages'
		);
	}

	public function register_additional_sections_frontend_styles(){
		$style_path = '/assets/dist/style-additional-sections-frontend.css';
		$style_url = plugins_url( $style_path, __FILE__ );
		$absolute_path = plugin_dir_path(__FILE__) . '/assets/dist/style-additional-sections-frontend.css';
		wp_enqueue_style(
			'thwcfe-additional-sections-frontend',
			$style_url,
			[],
			$this->get_file_version( $absolute_path )
		);
	}

    /*private function register_main_integration() {

		$script_path = '/assets/dist/index.js';
		$style_path  = '/assets/dist/style-index.css';

		$script_url = plugins_url( $script_path, __FILE__ );
		$style_url  = plugins_url( $style_path, __FILE__ );

		$script_asset_path = dirname( __FILE__ ) . '/assets/dist/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => ['thwcfe-additional-sections-frontend'],
				'version'      => $this->get_file_version( $script_path ),
			];
			$script_asset      =  [
				'dependencies' => ['thwcfe-additional-sections-frontend'],
				'version'      => $this->get_file_version( $script_path ),
			];

		wp_enqueue_style(
			'thwdtp-block-integration',
			$style_url,
			[],
			$this->get_file_version( $style_path )
		);


		wp_register_script(
			'thwcfe-block-integration',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		// wp_set_script_translations(
		// 	'thwcfe-block-integration',
		// 	' order-delivery-date-and-time',
		// 	dirname( __FILE__ ) . '/languages'
		// );
	}*/
}
endif;
