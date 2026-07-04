<?php
/**
 * Admin Base HTML.
 *
 * @package Themehigh\\admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
set_current_screen();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
	<head>
	<?php //wp_head(); ?>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php esc_html_e( 'Checkout field editor Landing page', 'woo-checkout-field-editor-pro' ); ?></title>
	</head>
	<body class="thwcfd-admin-page thwcfd-landing-page wp-core-ui">
		<div id="th_render_landing_page">
			<div class="th-landing-page-content">
				<div style="margin-bottom: 40px;">
					<div class = "th-icon-wrap">
						<div class="th-icon">
							<svg width="50" height="50" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9.89459 0H0.508202C0.441464 0 0.37538 0.013146 0.313722 0.0386855C0.252064 0.0642251 0.196039 0.101657 0.148848 0.148848C0.101657 0.196039 0.064224 0.252065 0.0386845 0.313723C0.0131449 0.375381 0 0.441464 0 0.508202V2.5017C-6.61617e-08 2.56841 0.0131492 2.63446 0.0386975 2.69607C0.0642459 2.75769 0.101692 2.81367 0.148892 2.8608C0.196092 2.90794 0.252121 2.9453 0.313775 2.97076C0.375429 2.99623 0.441497 3.00928 0.508202 3.00919H9.89459C10.0292 3.00919 10.1583 2.95572 10.2534 2.86055C10.3486 2.76538 10.4021 2.6363 10.4021 2.5017V0.508202C10.4022 0.441497 10.3891 0.375429 10.3636 0.313775C10.3382 0.252121 10.3008 0.196092 10.2537 0.148892C10.2066 0.101692 10.1506 0.064247 10.089 0.0386986C10.0273 0.0131503 9.96129 -6.61617e-08 9.89459 0Z" fill="#845DE2"/>
								<path d="M15.2318 4.67675H3.88198C3.74732 4.67675 3.61817 4.7302 3.52288 4.82535C3.42759 4.9205 3.37397 5.04958 3.37378 5.18424V7.18131C3.37397 7.31597 3.42759 7.44505 3.52288 7.54021C3.61817 7.63536 3.74732 7.6888 3.88198 7.6888H15.2318C15.2985 7.6888 15.3645 7.67567 15.4261 7.65017C15.4876 7.62467 15.5436 7.58729 15.5907 7.54016C15.6378 7.49304 15.6752 7.43709 15.7007 7.37552C15.7262 7.31395 15.7393 7.24796 15.7393 7.18131V5.18424C15.7393 5.04965 15.6859 4.92057 15.5907 4.82539C15.4955 4.73022 15.3664 4.67675 15.2318 4.67675Z" fill="#6E55FF"/>
								<path d="M15.2318 9.35279H12.595C12.4604 9.35298 12.3313 9.4066 12.2361 9.50189C12.141 9.59718 12.0875 9.72634 12.0875 9.861V11.8574C12.0874 11.9241 12.1005 11.9901 12.126 12.0518C12.1514 12.1134 12.1888 12.1695 12.2359 12.2167C12.283 12.2639 12.339 12.3013 12.4006 12.3269C12.4623 12.3524 12.5283 12.3656 12.595 12.3656H15.2318C15.3666 12.3656 15.4959 12.312 15.5912 12.2167C15.6865 12.1214 15.74 11.9921 15.74 11.8574V9.86385C15.7404 9.79687 15.7275 9.73048 15.7021 9.66849C15.6768 9.60651 15.6394 9.55014 15.5922 9.50265C15.5449 9.45516 15.4888 9.41747 15.427 9.39175C15.3651 9.36603 15.2988 9.35279 15.2318 9.35279Z" fill="#6E55FF"/>
								<path d="M9.89463 9.35279H7.2557C7.12085 9.35355 6.99181 9.4078 6.89693 9.50362C6.80204 9.59944 6.74907 9.729 6.74964 9.86385V11.8602C6.74964 11.995 6.80318 12.1243 6.89849 12.2196C6.99379 12.3149 7.12306 12.3684 7.25784 12.3684H9.89463C9.96134 12.3684 10.0274 12.3553 10.089 12.3297C10.1506 12.3042 10.2066 12.2667 10.2537 12.2195C10.3009 12.1723 10.3382 12.1163 10.3637 12.0546C10.3892 11.993 10.4022 11.9269 10.4021 11.8602V9.86385C10.4025 9.79693 10.3897 9.7306 10.3643 9.66866C10.339 9.60672 10.3017 9.55039 10.2545 9.5029C10.2074 9.45542 10.1513 9.41771 10.0896 9.39195C10.0278 9.36619 9.96155 9.35288 9.89463 9.35279Z" fill="#6E55FF"/>
								<path d="M15.2318 0H12.595C12.5283 -6.61617e-08 12.4623 0.0131503 12.4006 0.0386986C12.339 0.064247 12.283 0.101692 12.2359 0.148892C12.1888 0.196092 12.1514 0.252121 12.126 0.313775C12.1005 0.375429 12.0874 0.441497 12.0875 0.508202V2.5017C12.0875 2.56835 12.1007 2.63434 12.1262 2.69591C12.1517 2.75748 12.189 2.81342 12.2362 2.86055C12.2833 2.90767 12.3392 2.94506 12.4008 2.97056C12.4624 2.99606 12.5284 3.00919 12.595 3.00919H15.2318C15.2985 3.00928 15.3646 2.99623 15.4262 2.97076C15.4879 2.9453 15.5439 2.90794 15.5911 2.8608C15.6383 2.81367 15.6758 2.75769 15.7013 2.69607C15.7269 2.63446 15.74 2.56841 15.74 2.5017V0.508202C15.74 0.373418 15.6865 0.244155 15.5912 0.148848C15.4959 0.0535418 15.3666 0 15.2318 0Z" fill="#6E55FF"/>
							</svg>
						</div>
					</div>
				</div>
        		<h1 style="font-weight: 700;"><?php esc_html_e("Welcome to Checkout Field Editor!", 'woo-checkout-field-editor-pro' ); ?></h1>
				<p class ="th-desc-head" >
				<?php esc_html_e( "We’re excited to have you on board! Now, you can easily customize your WooCommerce checkout using both the Block Checkout Editor and the Classic Checkout Field Editor. Enjoy complete flexibility and control! " , 'woo-checkout-field-editor-pro' ); ?>
				</p>
				
				<a href="<?php echo esc_url(admin_url('admin.php?page=checkout_form_designer&tab=fields&c_type=classic')); ?>" class="th-get-started-btn">
					<?php esc_html_e("Get Started" , 'woo-checkout-field-editor-pro' ); ?>
				</a>

				<div class="th-read-more">
					<p> <?php esc_html_e("Learn more about both editors to make the most out of your checkout", 'woo-checkout-field-editor-pro' ); ?>  </p>
					<!-- <button>
					<?php //esc_html_e("Read More!", 'woo-checkout-field-editor-pro' ); ?>
					</button> -->
					<a href="https://www.themehigh.com/docs/classic-vs-block-checkout/" target="_blank" rel="noopener noreferrer">
						<button type="button">
							<?php esc_html_e("Read More!", 'woo-checkout-field-editor-pro' ); ?>
						</button>
					</a>
				</div>

				<div class="th-sub-content" style="">
					<p ><?php esc_html_e("We support both Classic and Block Checkout. Choose the one you're using", 'woo-checkout-field-editor-pro' ); ?></p>
					<h2><?php esc_html_e("Which Checkout Fields should you use?", 'woo-checkout-field-editor-pro' ); ?> </h2>

					<div class="th-diff-box-wrap">
						<div class="th-diff-box">
							<div class="th-diff-box-content">
								<svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
									<symbol id="bullet-icon" viewBox="0 0 11 10">
										<path d="M0.257421 4.67442C0.289999 4.68728 0.346924 4.70537 0.40042 4.73075C1.68878 5.34169 2.97782 5.95124 4.26378 6.56739C4.37831 6.62233 4.44347 6.60529 4.52783 6.51593C6.51266 4.40702 8.50024 2.3009 10.4875 0.19408C10.5317 0.147138 10.5739 0.0967189 10.6236 0.0567314C10.7145 -0.0162892 10.8119 -0.0194187 10.9072 0.0508202C10.9974 0.117582 11.0252 0.234763 10.9758 0.341512C10.958 0.380456 10.9329 0.416619 10.9096 0.452782C8.90934 3.56033 6.90908 6.66788 4.90847 9.77544C4.88755 9.80812 4.86766 9.84185 4.844 9.87245C4.72089 10.0331 4.56898 10.0435 4.4325 9.89505C4.13244 9.56785 3.83513 9.23786 3.53713 8.90892C2.39383 7.6474 1.24949 6.38658 0.108593 5.12297C0.0571549 5.06595 0.0122321 4.98632 0.00194445 4.91191C-0.0165733 4.77908 0.0993343 4.67164 0.257421 4.67442Z" fill="#FFC812"/>
									</symbol>
								</svg>
								<div>
									<h3><?php esc_html_e("Classic Checkout", 'woo-checkout-field-editor-pro' ); ?></h3>
									<ul>
										<li>
											<svg width="11" height="10">
                    							<use xlink:href="#bullet-icon"></use>
                							</svg>  
											<p><?php esc_html_e("Use this if you’re still using the traditional WooCommerce checkout page.", 'woo-checkout-field-editor-pro' ); ?></p>
										</li>
										<li>
											<svg width="11" height="10">
                    							<use xlink:href="#bullet-icon"></use>
                							</svg>
											<p><?php esc_html_e("Compatible with older checkout layouts.", 'woo-checkout-field-editor-pro' ); ?></p>
										</li>
										<li>
											<svg width="11" height="10">
                    							<use xlink:href="#bullet-icon"></use>
                							</svg>
											<p><?php esc_html_e("Best for users who prefer the classic, non-block experience.", 'woo-checkout-field-editor-pro' ); ?> </p>
										</li>
									</ul>
								</div>
								<img src="<?php echo esc_url(THWCFD_ASSETS_URL_ADMIN . 'images/checkout-img.png'); ?>" alt="Classic Checkout">
							</div>
							
						</div>
						
						<div class="th-diff-box">
							
							<div class="th-diff-box-content">
								<div>
									<h3 style="font-weight: bold;"><?php esc_html_e("Block Checkout", 'woo-checkout-field-editor-pro' ); ?></h3>
									<ul>
										<li>
											<svg width="11" height="10">
												<use xlink:href="#bullet-icon"></use>
											</svg> 
											<p> <?php esc_html_e("Use this if you’ve transitioned to WooCommerce’s new Block-based checkout.", 'woo-checkout-field-editor-pro' ); ?></p>
										</li>
										<li>
											<svg width="11" height="10">
												<use xlink:href="#bullet-icon"></use>
											</svg>
											<p> <?php esc_html_e( "Easier to customize with block elements.", 'woo-checkout-field-editor-pro'); ?> </p>	
										</li>
										<li>
											<svg width="11" height="10">
												<use xlink:href="#bullet-icon"></use>
											</svg>
											<p> <?php esc_html_e("Best for users looking for a more modern, flexible checkout design",'woo-checkout-field-editor-pro'); ?>.</p>
										</li>
									</ul>
								</div>
								<img src="<?php echo esc_url(THWCFD_ASSETS_URL_ADMIN . 'images/block-checkout.png'); ?>" alt="Block Checkout">
							</div>
							
						</div>	
					</div>
					<div class="th-btn-box">
						<a href="<?php echo esc_url(admin_url('admin.php?page=checkout_form_designer&tab=fields&c_type=classic')); ?>" class="th-choose-btn">
							<?php esc_html_e('Go with Classic Checkout Editor', 'woo-checkout-field-editor-pro') ?>
						</a>
						<a href="<?php echo esc_url(admin_url('admin.php?page=checkout_form_designer&tab=fields&c_type=block')); ?>" class="th-choose-btn">
							<?php esc_html_e('Go with Block Checkout Editor', 'woo-checkout-field-editor-pro') ?>
						</a>
					</div>
				</div>
				<a href="<?php echo esc_url(admin_url('admin.php?page=checkout_form_designer&tab=fields&c_type=classic')); ?>" class="th-get-started-btn">
					<?php esc_html_e("Let's Get started", 'woo-checkout-field-editor-pro' ); ?>
				</a>
			</div>
			
			
		</div>
	</body>
	<?php wp_footer(); ?>
</html>
<?php exit; ?>