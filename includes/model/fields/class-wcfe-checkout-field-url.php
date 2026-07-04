<?php
/**
 * Checkout Field - URL
 *
 * @author    ThemeHiGH
 * @category  Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('WCFE_Checkout_Field_URL')):

class WCFE_Checkout_Field_URL extends WCFE_Checkout_Field{
    
    public function __construct() {
        $this->type = 'url';
    }	
}

endif;