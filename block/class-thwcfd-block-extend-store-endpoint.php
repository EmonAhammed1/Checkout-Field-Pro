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
use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\StoreApi\Schemas\CartSchema;
use Automattic\WooCommerce\Blocks\StoreApi\Schemas\CheckoutSchema;

if(!class_exists('THWCFD_Block_Extend_Store_Endpoint')):
class THWCFD_Block_Extend_Store_Endpoint{
	/**
	 * Stores Rest Extending instance.
	 *
	 * @var ExtendRestApi
	 */
	private static $extend;

	/**
	 * Plugin Identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'thwcfe-additional-fields';

	/**
	 * Bootstraps the class and hooks required data.
	 *
	 */
	public static function init() {
		self::$extend = Automattic\WooCommerce\StoreApi\StoreApi::container()->get( Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema::class );
		self::extend_store();
	}

	/**
	 * Registers the actual data into each endpoint.
	 */
	public static function extend_store() {

		if ( is_callable( [ self::$extend, 'register_endpoint_data' ] ) ) {
			self::$extend->register_endpoint_data(
				[
					'endpoint'        => CheckoutSchema::IDENTIFIER,
					'namespace'       => self::IDENTIFIER,
					'schema_callback' => [self::class, 'extend_checkout_schema' ],
					'schema_type'     => ARRAY_A,
				]
			);
		}

	}
	/**
	 * Register schema into the Checkout endpoint.
	 *
	 * @return array Registered schema.
	 *
	 */
	public static function extend_checkout_schema() {
        
        $schema = [];
        // Get all section IDs
        $sections = THWCFD_Utils_Block::get_block_checkout_sections();
        $excluded_ids = ['address', 'order'];

        foreach ($sections as $section_id => $section) {

            if (in_array($section_id, $excluded_ids, true)) {
                continue;
            }

            $schema[$section_id] = [
				// Translators: %s is the section ID.
                'description' => sprintf(__('Additional checkout fields for section %s', 'woo-checkout-field-editor-pro'), $section_id),
                'type'        => ['object', 'null'],
                'context'     => ['view', 'edit'],
                'readonly'    => true,
                'arg_options' => [
					'validate_callback' => function( $value ) {
						return ( $value );
					},
                ],
            ];
        }
        return $schema;

    }
}
endif;
