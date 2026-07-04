<?php
/**
 * Checkout Field - Radio
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Radio')):

class WCFE_Checkout_Field_Radio extends WCFE_Checkout_Field{
	public $options = array();
	
	public function __construct() {
		$this->type = 'radio';
	}	
}

endif;