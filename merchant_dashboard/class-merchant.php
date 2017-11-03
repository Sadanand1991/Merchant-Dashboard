<?php
/**
* Merchant Processing Class
*/

//require_once (MERCHANT_DASHBOARD__PLUGIN_DIR . 'merchant_dashboard/merchant-registration.php');
require_once (MERCHANT_DASHBOARD__PLUGIN_DIR . 'merchant_dashboard/view-my-shop.php');
require_once (MERCHANT_DASHBOARD__PLUGIN_DIR . 'merchant_dashboard/products.php');
require_once (MERCHANT_DASHBOARD__PLUGIN_DIR . 'merchant_dashboard/product-uploads.php');
require_once (MERCHANT_DASHBOARD__PLUGIN_DIR . 'merchant_dashboard/products-csv-uploads.php');
require_once (MERCHANT_DASHBOARD__PLUGIN_DIR . 'merchant_dashboard/manage-sets.php');
class Merchant {

	public function __construct(){

		add_action('merchant_account_menu', array($this, 'merchant_account_menu_hook'));

	}

	public function merchant_account_menu_hook() {
		$theme_settings = unserialize(get_option('global_theme_settings'));
		$merchantdashboardurl = get_permalink($theme_settings['merchantdashboard']);	
		$editprofile = '';
		if(isset($theme_settings['editprofile'])) {
			$editprofileid = $theme_settings['editprofile'];
			$editprofile = get_permalink($editprofileid);
		}
		if(is_user_logged_in()) {
			$merchant_user = wp_get_current_user();
			if($merchant_user->roles[0] == 'merchant') {
				$first_name = get_user_meta($merchant_user->ID, 'first_name', true);
				$last_name = get_user_meta($merchant_user->ID, 'last_name', true);
				
				echo '<div class="welcome">Welcome '.$first_name.' '.$last_name.'</div>';
				echo '<div class="dropdown">
				  <a class="accountmenu" data-toggle="dropdown" href="javascript:void(0);"><i class="fa fa-cog"></i></a>
				  <ul class="dropdown-menu md_dashboard_menu" role="menu" aria-labelledby="">
				  <li role="presentation"><a href="javascript:void(0);" class="navigate" data-menu-target="" tabindex="-1" role="menuitem">View My Shop</a></li>
				  <li role="presentation"><a href="javascript:void(0);" class="navigate" data-menu-target="upload-product" tabindex="-1" role="menuitem">Upload</a></li>
                                  <li role="presentation"><a href="javascript:void(0);" class="navigate" data-menu-target="manage-sets" tabindex="-1" role="menuitem">Manage Sets</a></li>
				  <li role="presentation"><a href="javascript:void(0);" class="navigate" data-menu-target="orders" tabindex="-1" role="menuitem">View Orders</a></li>
				  
				  <li role="presentation"><a href="../merchant-registration?merchant_id='.$merchant_user->ID.'" target="_blank" tabindex="-1" role="menuitem" title="Edit Profile">Edit Profile</a></li>
				  <li role="presentation"><a href="'.wp_logout_url( $merchantdashboardurl ).'" tabindex="-1" role="menuitem" title="Logout">Logout</a></li>
				  </ul>
				</div>';
				echo '<div class="clearfix"></div>';
			}
		}
	}


	public function merchant_login_window() {
            global $post;
		ob_start();
		?>
		<div class="login_modal_form_outer_wrap" id="login_window">
			<div class="" id="loginModal">
			  <div class="modal-dialog">
			    <div class="modal-content">
			    	<div class="modal-header">
			    		
			        </div>
			      <div class="modal-body">
			      	<p class="error_message has-error" id="error"><?php _e('Wrong username or password. Or You are not verified user ', 'Jewelbuff'); ?></p>
			      	<div class="login_form_wrapper">
			        	<div class="email_address">
			        		<span class="label col-sm-3"><label for="login-email">Email Id</label></span>
                                                <div  class="col-sm-9"><input type="email" name="login-email" id="login-email" placeholder="Email Or Username"/></div>
			        		<div class="clearfix"></div>
			        	</div>
			        	
						<div class="password">
                                                    <span class="label col-sm-3"><label for="login-password">Password</label></span>
                                                <div class="col-sm-9"><input type="password" name="login-password" id="login-password" placeholder="Password"/></div>
			        		<div class="clearfix"></div>
			        	</div>
			        	<div class="password_info">
			        		<div class="rememberme_checkbox col-sm-6">
			        			<input type="checkbox" name="remember_check" id="remember_check" value="true"><label for="remember_check">Remember me</label> 
			        		</div>
			        		<div class="forgot_passsword_link col-sm-6">
			        			<a href="<?php echo get_permalink($theme_settings['resetpassword']); ?>">Forgot Password?</a>
			        		</div>
			        		<div class="clearfix"></div>
			        	</div>
			        	<div class="login_but col-sm-10 col-sm-offset-1">
			        		<i class="fa fa-cog fa-spin login_loader" style="font-size:20px; display:none;"></i>
			        		<button type="button" class="btn btn-primary" id="login" action="mm_merchant_login">Login</button>
			        	</div>
			        	<?php wp_nonce_field( 'login-nonce', 'security' ); ?>
			        	<input type="hidden" value="<?php echo $post->ID; ?>" id="current_page_id" />
                                        <div class="login_mr col-sm-10 col-sm-offset-1">
                                            <span class="log-text">Not a user yet? Create Registration</span>
                                        <a href="<?php echo site_url(); ?>/merchant-registration" class="small-box-footer">Registration</a>
                                        </div>
			        	<div class="clearfix"></div>
			        </div>
			        
			      </div>
			    </div>
			  </div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	

}

$GLOBALS['MerchantObject'] = new Merchant();