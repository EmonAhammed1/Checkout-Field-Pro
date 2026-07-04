<?php
/**
 * The file that defines the core plugin class.
 *
 * @link       https://github.com/EmonAhammed1/Checkout-Field-Pro
 * @since      1.5.0
 *
 * @package    woo-checkout-field-editor-pro
 * @subpackage woo-checkout-field-editor-pro/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFD')):

class THWCFD {
	protected $plugin_name;
	protected $version;
	const TEXT_DOMAIN = 'woo-checkout-field-editor-pro';

	public function __construct() {
		if(defined( 'THWCFD_VERSION')){
			$this->version = THWCFD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo-checkout-field-editor-pro';
        $this->load_dependencies();
		add_action('init', array($this, 'init'));
        $this->define_blocks();
	}

    public function init(){
		$this->define_constants();
		//$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		if(!function_exists('is_plugin_active')){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thwcfd-autoloader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-thwcfd-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-thwcfd-public-checkout.php';

		// require_once THWCFD_PATH . 'classes/class-thwcfd-utils.php';
		// require_once THWCFD_PATH . 'classes/class-thwcfd-settings.php';
		// require_once THWCFD_PATH . 'classes/class-thwcfd-settings-general.php';
		// require_once THWCFD_PATH . 'classes/class-thwcfd-settings-advanced.php';
		// require_once THWCFD_PATH . 'classes/class-thwcfd-checkout.php';
	}

    /************ Note: Manual loading of the plugin text domain is intentionally omitted.
     * Since WordPress 4.6+, translations for plugins hosted on WordPress.org are loaded automatically using Just-In-Time (JIT) loading.
     * This plugin requires WordPress 4.9 or higher, so no explicit call to load_plugin_textdomain is needed here.
    */

	/*private function set_locale() {
        // WordPress 4.6+ JIT loading of textdomains. This for backward compatibility logging purpose only.
        $domain = self::TEXT_DOMAIN;
        if ( is_textdomain_loaded( $domain ) ) {
            return;
        }
        // Determine locale safely (WP 5.0+)
        $locale = function_exists( 'determine_locale' )
            ? determine_locale()
            : get_locale();

        $mofile = WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo';
        if ( file_exists( $mofile ) ) {
            load_textdomain( $domain, $mofile );
            return;
        }
        load_plugin_textdomain(
            $domain,
            false,
            dirname( THWCFD_BASE_NAME ) . '/languages/'
        );
        //plugins_loaded hook not required as load_plugin_textdomain called in init method
		//add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));  
	}*/

	/* plugins_loaded hook not required as load_plugin_textdomain called in init method
    public function load_plugin_textdomain(){
		$locale = apply_filters('plugin_locale', get_locale(), self::TEXT_DOMAIN);
	
		load_textdomain(self::TEXT_DOMAIN, WP_LANG_DIR.'/woo-checkout-field-editor-pro/'.self::TEXT_DOMAIN.'-'.$locale.'.mo');
		load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(THWCFD_BASE_NAME) . '/languages/');
	}*/

	private function define_admin_hooks() {
		$plugin_admin = new THWCFD_Admin( $this->get_plugin_name(), $this->get_version() );

		add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles_and_scripts'));
		add_action('admin_menu', array($plugin_admin, 'admin_menu'));
		add_action('admin_head', array($plugin_admin,'review_banner_custom_css'));
		add_filter('woocommerce_screen_ids', array($plugin_admin, 'add_screen_id'));
		add_filter('plugin_action_links_'.THWCFD_BASE_NAME, array($plugin_admin, 'plugin_action_links'));
		add_action( 'admin_init', array( $plugin_admin, 'wcfd_notice_actions' ), 20 );
		add_action( 'admin_notices', array($plugin_admin, 'output_review_request_link'));
		add_filter('plugin_row_meta', array($plugin_admin, 'plugin_row_meta'), 10, 2);
        add_action( 'admin_head', array($plugin_admin, 'remove_welcome_page_menu'));
		

		$general_settings = new THWCFD_Admin_Settings_General();
		add_action('init', array($general_settings, 'define_admin_hooks'),20);
	}

	private function define_public_hooks() {
		//if(!is_admin() || (defined( 'DOING_AJAX' ) && DOING_AJAX)){
			$plugin_checkout = new THWCFD_Public_Checkout( $this->get_plugin_name(), $this->get_version() );
			add_action('wp_enqueue_scripts', array($plugin_checkout, 'enqueue_styles_and_scripts'));
			add_action('init', array($plugin_checkout, 'define_public_hooks'),20);
		//}
	}

    private function define_blocks(){

        $plugin_block_checkout = new THWCFD_Block();
        $plugin_block_checkout->init();
    }

	private function define_constants(){
		!defined('THWCFD_ASSETS_URL_ADMIN') && define('THWCFD_ASSETS_URL_ADMIN', THWCFD_URL . 'admin/assets/');
		!defined('THWCFD_ASSETS_URL_PUBLIC') && define('THWCFD_ASSETS_URL_PUBLIC', THWCFD_URL . 'public/assets/');
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function thwcfd_deactivation_form(){
		$is_snooze_time = get_user_meta( get_current_user_id(), 'thwcfd_deactivation_snooze', true );
        $now = time();

        if($is_snooze_time && ($now < $is_snooze_time)){
            return;
        }

        $deactivation_reasons = $this->get_deactivation_reasons();
        ?>
        <div id="thwcfd_deactivation_form" class="thpladmin-modal-mask">
            <div class="thpladmin-modal">
                <div class="modal-container">
                    <!-- <span class="modal-close" onclick="thwcfdfCloseModal(this)">×</span> -->
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="model-header">
                                <img class="th-logo" src="<?php echo esc_url(THWCFD_URL .'admin/assets/images/min-logo.svg'); ?>" alt="emon-logo">
                                <span><?php esc_html_e('Quick Feedback', 'woo-checkout-field-editor-pro'); ?></span>
                            </div>

                            <!-- <div class="get-support-version-b">
                                <p>We are sad to see you go. We would be happy to fix things for you. Please raise a ticket to get help</p>
                                <a class="thwcfd-link thwcfd-right-link thwcfd-active" target="_blank" href="https://github.com/EmonAhammed1/Checkout-Field-Pro/issues"><?php esc_html_e('Get Support', 'woo-checkout-field-editor-pro'); ?></a>
                            </div> -->

                            <main class="form-container main-full">
                                <p class="thwcfd-title-text"><?php esc_html_e('If you have a moment, please let us know why you want to deactivate this plugin', 'woo-checkout-field-editor-pro'); ?></p>
                                <ul class="deactivation-reason" data-nonce="<?php echo esc_attr( wp_create_nonce('thwcfd_deactivate_nonce')); ?>">
                                    <?php 
                                    if($deactivation_reasons){
                                        foreach($deactivation_reasons as $key => $reason){
                                            $reason_type = isset($reason['reason_type']) ? $reason['reason_type'] : '';
                                            $reason_placeholder = isset($reason['reason_placeholder']) ? $reason['reason_placeholder'] : '';
                                            ?>
                                            <li data-type="<?php echo esc_attr($reason_type); ?>" data-placeholder="<?php echo esc_attr($reason_placeholder); ?> ">
                                                <label>
                                                    <input type="radio" name="selected-reason" value="<?php echo esc_attr($key); ?>">
                                                    <span><?php echo esc_html($reason['radio_label']); ?></span>
                                                </label>
                                            </li>
                                            <?php
                                        }
                                    }
                                    ?>
                                </ul>
                                <p class="thwcfd-privacy-cnt"><?php esc_html_e('This form is only for getting your valuable feedback. We do not collect your personal data. To know more read our ', 'woo-checkout-field-editor-pro'); ?> <a class="thwcfd-privacy-link" target="_blank" href="<?php echo esc_url('https://github.com/EmonAhammed1/Checkout-Field-Pro/privacy-policy/');?>"><?php esc_html_e('Privacy Policy', 'woo-checkout-field-editor-pro'); ?></a></p>
                            </main>
                            <footer class="modal-footer">
                                <div class="thwcfd-left">
                                    <a class="thwcfd-link thwcfd-left-link thwcfd-deactivate" href="#"><?php esc_html_e('Skip & Deactivate', 'woo-checkout-field-editor-pro'); ?></a>
                                </div>
                                <div class="thwcfd-right">
                                    
                                    <a class="thwcfd-link thwcfd-right-link thwcfd-active" target="_blank" href="https://github.com/EmonAhammed1/Checkout-Field-Pro/issues"><?php esc_html_e('Get Support', 'woo-checkout-field-editor-pro'); ?></a>

                                    <a class="thwcfd-link thwcfd-right-link thwcfd-active thwcfd-submit-deactivate" href="#"><?php esc_html_e('Submit and Deactivate', 'woo-checkout-field-editor-pro'); ?></a>
                                    <a class="thwcfd-link thwcfd-right-link thwcfd-close" href="#"><?php esc_html_e('Cancel', 'woo-checkout-field-editor-pro'); ?></a>
                                </div>
                            </footer>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style type="text/css">
            .th-logo{
                margin-right: 10px;
            }
            .thpladmin-modal-mask{
                position: fixed;
                background-color: rgba(17,30,60,0.6);
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                overflow: scroll;
                transition: opacity 250ms ease-in-out;
            }
            .thpladmin-modal-mask{
                display: none;
            }
            .thpladmin-modal .modal-container{
                position: absolute;
                background: #fff;
                border-radius: 2px;
                overflow: hidden;
                left: 50%;
                top: 50%;
                transform: translate(-50%,-50%);
                width: 50%;
                max-width: 960px;
                /*min-height: 560px;*/
                /*height: 80vh;*/
                /*max-height: 640px;*/
                animation: appear-down 250ms ease-in-out;
                border-radius: 15px;
            }
            .model-header {
                padding: 21px;
            }
            .thpladmin-modal .model-header span {
                font-size: 18px;
                font-weight: bold;
            }
            .thpladmin-modal .model-header {
                padding: 21px;
                background: #ECECEC;
            }
            .thpladmin-modal .form-container {
                margin-left: 23px;
                clear: both;
            }
            .thpladmin-modal .deactivation-reason input {
                margin-right: 13px;
            }
            .thpladmin-modal .thwcfd-privacy-cnt {
                color: #919191;
                font-size: 12px;
                margin-bottom: 31px;
                margin-top: 18px;
                max-width: 75%;
            }
            .thpladmin-modal .deactivation-reason li {
                margin-bottom: 17px;
            }
            .thpladmin-modal .modal-footer {
                padding: 20px;
                border-top: 1px solid #E7E7E7;
                float: left;
                width: 100%;
                box-sizing: border-box;
            }
            .thwcfd-left {
                float: left;
            }
            .thwcfd-right {
                float: right;
            }
            .thwcfd-link {
                line-height: 31px;
                font-size: 12px;
            }
            .thwcfd-left-link {
                font-style: italic;
            }
            .thwcfd-right-link {
                padding: 0px 20px;
                border: 1px solid;
                display: inline-block;
                text-decoration: none;
                border-radius: 5px;
            }
            .thwcfd-right-link.thwcfd-active {
                background: #0773AC;
                color: #fff;
            }
            .thwcfd-title-text {
                color: #2F2F2F;
                font-weight: 500;
                font-size: 15px;
            }
            .reason-input {
                margin-left: 31px;
                margin-top: 11px;
                width: 70%;
            }
            .reason-input input {
                width: 100%;
                height: 40px;
            }
            .reason-input textarea {
                width: 100%;
                min-height: 80px;
            }
            input.th-snooze-checkbox {
                width: 15px;
                height: 15px;
            }
            input.th-snooze-checkbox:checked:before {
                width: 1.2rem;
                height: 1.2rem;
            }
            .th-snooze-select {
                margin-left: 20px;
                width: 172px;
            }

            /* Version B */
            .get-support-version-b {
                width: 100%;
                padding-left: 23px;
                clear: both;
                float: left;
                box-sizing: border-box;
                background: #0673ab;
                color: #fff;
                margin-bottom: 20px;
            }
            .get-support-version-b p {
                font-size: 12px;
                line-height: 17px;
                width: 70%;
                display: inline-block;
                margin: 0px;
                padding: 15px 0px;
            }
            .get-support-version-b .thwcfd-right-link {
                background-image: url(<?php echo esc_url(THWCFD_URL .'admin/assets/css/get_support_icon.svg'); ?>);
                background-repeat: no-repeat;
                background-position: 11px 10px;
                padding-left: 31px;
                color: #0773AC;
                background-color: #fff;
                float: right;
                margin-top: 17px;
                margin-right: 20px;
            }
            .thwcfd-privacy-link {
                font-style: italic;
            }
            .wcfe-review-link {
                margin-top: 7px;
                margin-left: 31px;
                font-size: 16px;
            }
            span.wcfe-rating-link {
                color: #ffb900;
            }
            .thwcfd-review-and-deactivate {
                text-decoration: none;
            }
        </style>

        <script type="text/javascript">
            (function($){
                var popup = $("#thwcfd_deactivation_form");
                var deactivation_link = '';
                $('.thwcfd-deactivate-link').on('click', function(e){
                    e.preventDefault();
                    deactivation_link = $(this).attr('href');
                    popup.css("display", "block");
                    popup.find('a.thwcfd-deactivate').attr('href', deactivation_link);
                });

                popup.on('click', 'input[type="radio"]', function () {
                    var parent = $(this).parents('li:first');
                    popup.find('.reason-input').remove();

                    var type = parent.data('type');
                    var placeholder = parent.data('placeholder');

                    var reason_input = '';
                    if('text' == type){
                        reason_input += '<div class="reason-input">';
                        reason_input += '<input type="text" placeholder="'+ placeholder +'">';
                        reason_input += '</div>';
                    }else if('textarea' == type){
                        reason_input += '<div class="reason-input">';
                        reason_input += '<textarea row="5" placeholder="'+ placeholder +'">';
                        reason_input += '</textarea>';
                        reason_input += '</div>';
                    }else if('checkbox' == type){
                        reason_input += '<div class="reason-input ">';
                        reason_input += '<input type="checkbox" id="th-snooze" name="th-snooze" class="th-snooze-checkbox">';
                        reason_input += '<label for="th-snooze">Snooze this panel while troubleshooting</label>';
                        reason_input += '<select name="th-snooze-time" class="th-snooze-select" disabled>';
                        reason_input += '<option value="<?php echo esc_attr(HOUR_IN_SECONDS) ?>">1 Hour</option>';
                        reason_input += '<option value="<?php echo esc_attr(12*HOUR_IN_SECONDS) ?>">12 Hour</option>';
                        reason_input += '<option value="<?php echo esc_attr(DAY_IN_SECONDS) ?>">24 Hour</option>';
                        reason_input += '<option value="<?php echo esc_attr(WEEK_IN_SECONDS) ?>">1 Week</option>';
                        reason_input += '<option value="<?php echo esc_attr(MONTH_IN_SECONDS) ?>">1 Month</option>';
                        reason_input += '</select>';
                        reason_input += '</div>';
                    }else if('reviewlink' == type){
                        reason_input += '<div class="reason-input wcfe-review-link">';
                        /*
                        reason_input += '<?php esc_html_e('Deactivate and ', 'woo-checkout-field-editor-pro');?>'
                        reason_input += '<a href="#" target="_blank" class="thwcfd-review-and-deactivate">';
                        reason_input += '<?php esc_html_e('leave a review', 'woo-checkout-field-editor-pro'); ?>';
                        reason_input += '<span class="wcfe-rating-link"> &#9733;&#9733;&#9733;&#9733;&#9733; </span>';
                        reason_input += '</a>';
                        */
                        reason_input += '<input type="hidden" value="<?php esc_html_e('Upgraded', 'woo-checkout-field-editor-pro');?>">';
                        reason_input += '</div>';
                    }

                    if(reason_input !== ''){
                        parent.append($(reason_input));
                    }
                });

                popup.on('click', '.thwcfd-close', function () {
                    popup.css("display", "none");
                });

                /*
                popup.on('click', '.thwcfd-review-and-deactivate', function () {
                    e.preventDefault();
                    window.open("https://wordpress.org/support/plugin/woo-checkout-field-editor-pro/reviews/?rate=5#new-post");
                    console.log(deactivation_link);
                    window.location.href = deactivation_link;
                });
                */

                popup.on('click', '.thwcfd-submit-deactivate', function (e) {
                    e.preventDefault();
                    var button = $(this);
                    if (button.hasClass('disabled')) {
                        return;
                    }
                    var radio = $('.deactivation-reason input[type="radio"]:checked');
                    var parent_li = radio.parents('li:first');
                    var parent_ul = radio.parents('ul:first');
                    var input = parent_li.find('textarea, input[type="text"], input[type="hidden"]');
                    var wcfe_deacive_nonce = parent_ul.data('nonce');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'thwcfd_deactivation_reason',
                            reason: (0 === radio.length) ? 'none' : radio.val(),
                            comments: (0 !== input.length) ? input.val().trim() : '',
                            security: wcfe_deacive_nonce,
                        },
                        beforeSend: function () {
                            button.addClass('disabled');
                            button.text('Processing...');
                        },
                        complete: function () {
                            window.location.href = deactivation_link;
                        }
                    });
                });

                popup.on('click', '#th-snooze', function () {
                    if($(this).is(':checked')){
                        popup.find('.th-snooze-select').prop("disabled", false);
                    }else{
                        popup.find('.th-snooze-select').prop("disabled", true);
                    }
                });

            }(jQuery))
        </script>

        <?php 
    }
    private function get_deactivation_reasons(){
        return array(
        	'upgraded_to_pro' => array(
				'radio_val'          => 'upgraded_to_pro',
				'radio_label'        => __('Upgraded to premium.', 'woo-checkout-field-editor-pro'),
				'reason_type'        => 'reviewlink',
				'reason_placeholder' => '',
			),

            'feature_missing'=> array(
                'radio_val'          => 'feature_missing',
                'radio_label'        => __('A specific feature is missing', 'woo-checkout-field-editor-pro'),
                'reason_type'        => 'text',
                'reason_placeholder' => __('Type in the feature', 'woo-checkout-field-editor-pro'),
            ),

            'error_or_not_working'=> array(
                'radio_val'          => 'error_or_not_working',
                'radio_label'        => __('Found an error in the plugin/ Plugin was not working', 'woo-checkout-field-editor-pro'),
                'reason_type'        => 'text',
                'reason_placeholder' => __('Specify the issue', 'woo-checkout-field-editor-pro'),
            ),

            'hard_to_use' => array(
                'radio_val'          => 'hard_to_use',
                'radio_label'        => __('It was hard to use', 'woo-checkout-field-editor-pro'),
                'reason_type'        => 'text',
                'reason_placeholder' => __('How can we improve your experience?', 'woo-checkout-field-editor-pro'),
            ),

            'found_better_plugin' => array(
                'radio_val'          => 'found_better_plugin',
                'radio_label'        => __('I found a better Plugin', 'woo-checkout-field-editor-pro'),
                'reason_type'        => 'text',
                'reason_placeholder' => __('Could you please mention the plugin?', 'woo-checkout-field-editor-pro'),
            ),

            // 'not_working_as_expected'=> array(
            //  'radio_val'          => 'not_working_as_expected',
            //  'radio_label'        => __('The plugin didn’t work as expected', 'woo-checkout-field-editor-pro'),
            //  'reason_type'        => 'text',
            //  'reason_placeholder' => __('Specify the issue', 'woo-checkout-field-editor-pro'),
            // ),

            'temporary' => array(
                'radio_val'          => 'temporary',
                'radio_label'        => __('It’s a temporary deactivation - I’m troubleshooting an issue', 'woo-checkout-field-editor-pro'),
                'reason_type'        => 'checkbox',
                'reason_placeholder' => __('Could you please mention the plugin?', 'woo-checkout-field-editor-pro'),
            ),

            'other' => array(
                'radio_val'          => 'other',
                'radio_label'        => __('Not mentioned here', 'woo-checkout-field-editor-pro'),
                'reason_type'        => 'textarea',
                'reason_placeholder' => __('Kindly tell us your reason, so that we can improve', 'woo-checkout-field-editor-pro'),
            ),
        );
    }

    public function thwcfd_deactivation_reason(){
        wp_send_json_success();
        exit();
    }

}

endif;