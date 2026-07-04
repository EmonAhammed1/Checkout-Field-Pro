<?php
/**
 * Checkout Field - Country
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_Country')):

class WCFE_Checkout_Field_Country extends WCFE_Checkout_Field{
	
	public function __construct() {
		$this->type = 'country';
	}	
}

endif;