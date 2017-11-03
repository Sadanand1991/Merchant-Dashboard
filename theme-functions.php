<?php

/**
* Theme Custom Function Class
*/
 
class ThemeFunctions {
	public function __construct() {
         
        add_action( 'pages_header', array(&$this, 'pagesHeader') );		
         
        //Add Login Popup
//        add_action( 'wp_footer', array(&$this, 'loginmodalform') ); 

        //Confirm Registration Email
//        add_action( 'wp_footer', array(&$this, 'confirm_registration') );
       
        //Add user role as merchand
        add_action( 'after_setup_theme', array(&$this, 'MM_register_new_user_role') );

        //Hide admin bar
		add_action('after_setup_theme', array(&$this,'MM_remove_admin_bar'));

		//Hide Backend to the users other than admin
		add_action( 'init', array(&$this,'MM_hide_backend_for_users') );

		//Load Script Templates
		//add_action( 'wp_footer', array(&$this, 'load_script_templates') );

		//Add Query Vars
		add_filter('query_vars', array(&$this, 'add_query_var'));

		//Rewrite Rule for Mall CPT
		add_action('init', array(&$this, 'add_rewrite_rules_for_malls_cpt'));

		//Rewrite Rule for Malls Archive
		add_filter( 'generate_rewrite_rules', array(&$this, 'add_rewrite_rules_for_malls_archive'));

		//Rewrite Rule For Pages
		//add_filter( 'page_rewrite_rules', array($this, 'mm_pages_rewrite_rules') );

		//Permalink Structure Change For Malls CPT
		add_filter('post_type_link', array(&$this, 'malls_permalinks'), 10, 3);

		//Unique Post Slug filter for Mall Posts
		add_filter( 'wp_unique_post_slug', array(&$this, 'mall_unique_slug'), 10, 6 );
		
		//Load Deals Discounts Products By AJAX
		add_action('wp_ajax_get_deals_discount_products', array($this, 'get_deals_discount_products'));	
		add_action('wp_ajax_nopriv_get_deals_discount_products', array($this, 'get_deals_discount_products'));		
		
		//Load Fresh Stock Products By AJAX
		add_action('wp_ajax_get_fresh_stock_products', array($this, 'get_fresh_stock_products'));
		add_action('wp_ajax_nopriv_get_fresh_stock_products', array($this, 'get_fresh_stock_products'));

		add_action('wp_ajax_reset_password_login', array($this, 'reset_password_login'));
		add_action('wp_ajax_nopriv_reset_password_login', array($this, 'reset_password_login'));

	}
	

    //Set Header For Different Pages with pages_header hook
	public function pagesHeader() {
		$header = "";	
		if(is_home() || is_front_page()) {
			$header = $this->homePageHeader();
		} 

		else if(is_singular('shop') || is_singular('mall')) {
			$header = $this->shop_mall_PageHeader();
		}

		if(is_singular('product')) {
			$header = "";
		}
		echo $header;
	}	

	//Set Conditions For Rendering Product Preview Modal
	public function is_product_preview_modal() {
		if(is_home() || is_front_page() || is_singular('shop') || is_singular('mall')) {
			return true;
		}
		return false;
	}

	//Home Page Slider
	public function home_slider_handler() {
		ob_start();
		$args = array(
			'post_type' => 'home_slide',
			'post_status' => 'publish',
		);
		$slide_query = new WP_Query($args);

		if($slide_query->have_posts()) {
			?>
			<div class="home_carousel">
				<?php
				$i=0;
				while ( $slide_query->have_posts() ) { 
					$slide_query->the_post();
					$slide_link = get_post_meta($slide_query->post->ID, 'slide_link', true);
					if($slide_link=='') {
						$slide_link = 'javascript:void(0);';
					}
					$new_tab = get_post_meta($slide_query->post->ID, 'link_open_new_tab', true);
					$target = '';
					if($new_tab=='yes') {
						$target = 'target= "_blank"';
					}
					$thumb_id = get_post_thumbnail_id($slide_query->post->ID);
					$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full', true);
					$thumb_url = $thumb_url_array[0];

					if($i!=0) {
						$style = 'display: none;';
					}

					echo '<a href="'.$slide_link.'" '.$target.'  style="'.$style.'">';
					if($i==0) {
						//echo '<img class="owl-lazy" data-src="'.$thumb_url.'" src="'.$thumb_url.'" />';
						echo '<img src="'.$thumb_url.'" />';
					} else {
						echo '<img src="'.$thumb_url.'" />';
					}
					echo '</a>';
					$i++;
				}
				
				?>
			</div>
			<?php
		}
		wp_reset_postdata();		
		return ob_get_clean();
	}

	//Home Page Header
	public function homePageHeader() {
		ob_start();
		?>
		<!--- Header Search Start -->

	    <div class="header_site_search">
	    	<div class="page_header_search">
	        	<?php echo do_shortcode('[wpdreams_ajaxsearchpro id=1]'); ?>
	        	<!--<input type="text" value="" name="site_search" id="global_search" placeholder="Search Here" autocomplete="off"></input>-->
	        	<span class="search_button"></span>
	    	</div>
	    </div>  
	    <!--- Header Search End -->

	    <div class="or_label">OR</div>

	    <div class="scroll_down_label">
	        <span>SCROLL DOWN FOR MALLING</span>
	        <i class="fa fa-angle-down scroll_down_arrow"></i>
	    </div>	
		<?php
		return ob_get_clean();
	}

	//Shop Page Header
	public function shop_mall_PageHeader() {
		ob_start();
		$placeholder = 'Search Here';
		if(is_singular('mall')) {
			$placeholder = 'Search Here';
		} else if(is_singular('shop')) {
			$placeholder = 'Search Shop Here';
		}
		?>
		<div class="header_shop_mall_search header_site_search">
	    	<div class="page_header_search">
	        	<input type="text" value="" name="shop_search" placeholder="<?php echo $placeholder; ?>"></input>
	        	<span class="search_button"></span>
	    	</div>
	    </div>  
	   	<?php
		return ob_get_clean();
	}	

	//Confirm Registration
	public function confirm_registration() {
//		if(isset($_GET['activation_code'])) {
//			if(isset($_GET['id'])) {
//				$user_id = $_GET['id'];
//				$activation_code = get_user_meta($user_id, 'activation_key', true);
//				if( trim($activation_code) == trim($_GET['activation_code']) ) {
//					delete_user_meta( $user_id, 'activation_key');
//                                        delete_user_meta( $user_id, 'activation_flag');
//                                        delete_user_meta( $user_id, 'activation_exp_time');
//					ob_start();
//					?>
					<script type="text/javascript">
//						jQuery(document).ready(function(){
//							jQuery("#loginModal").modal("show");
//						});	
					</script>
					//<?php
//					echo ob_get_clean();
//				}
//			}
//		}
	}

	//Get Cities
	public function getCities($select_class = 'mm_select_city', $signupselect = false, $select_id = '', $name = '') {
		ob_start();
		$args = array(
			'post_type' => 'city',
			'post_status' => 'publish',
		);
		$city_query = new WP_Query($args);

		if($city_query->have_posts()) {
			?>
			<label class="mobile_select">
			<select name="<?php echo $name; ?>" class="<?php echo $select_class; ?>" id="<?php echo $select_id; ?>" data-placeholder="Select City" tabindex="1">
			<option value="">Select City</option>
			<?php
			while ( $city_query->have_posts() ) {
				$city_query->the_post();
				$city_id = $city_query->post->ID;
				$city_name = $city_query->post->post_title;
				$city = $city_query->post->post_name;
				$selected = "";

				if($signupselect==false) {
					// if($city == "pune") {
					// 	$selected = "selected=selected";
					// }

					if(isset($_SESSION['current_city'])) {
						if($city == $_SESSION['current_city']) {
							$selected = "selected=selected";
						}
					}
				}
				?>
				<option value="<?php echo $city; ?>" <?php echo $selected; ?>><?php echo $city_name; ?></option>
				<?php
			}
			?>
			</select>	
			</label>	
			<?php
		}
		wp_reset_postdata();
		return ob_get_clean();
	}

	//Get Malls 
	public function getMalls() {
		ob_start();
		?>
		<div class="section_heading"><?php _e('Checkout The Malls Around You','memalling') ?> </div>
		<div class="malls_loader mall_products_loader">
			<i class="fa fa-cog fa-spin"></i>
		</div>
		<div class="malls_listing"></div>	
		<div class="clearfix"></div>
		<a href="#" class="button_viewall view_all_malls btn btn-default">View All</a>
		<?php 
		return ob_get_clean();
	}


	//Mall 
	public function showMall($mall_image, $mall_distance, $mall_title, $mall_url) {
		ob_start();
		?>
		<div class="mall_display_wrapper col-sm-4 col-lg-4 col-md-4">
		<div class="mall_display_inner_wrapper">
			<div class="mall_image">
				<img src="<?php echo get_template_directory_uri()."/timthumb.php?src=".$mall_image."&h=210&w=455"; ?>" class="img-responsive" />
			</div>
			<?php
			if($mall_distance) { ?>	
			<div class="mall_distance">
				<?php echo number_format($mall_distance, 2, '.', '').' KM'; ?>
			</div>
			<?php } ?>
			<div class="mall_title">
				<a href="<?php echo $mall_url ?>"><?php echo $mall_title ?></a>	
			</div>
		</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function showMallWithDefaultImage($mall_image, $mall_distance, $mall_title, $mall_url, $default_image_flag) {
		ob_start();
		?>
		<div class="mall_display_wrapper col-sm-4 col-lg-4 col-md-4">
		<div class="mall_display_inner_wrapper">				
			<div class="mall_image" style="text-align: center;">
				<?php
				if( $default_image_flag == false ) {
				?>
					<img src="<?php echo get_template_directory_uri()."/timthumb.php?src=".$mall_image."&h=210&w=455"; ?>" class="img-responsive" />
				<?php
				} else {
				?>
					<img src="<?php echo $mall_image; ?>" class="img-responsive" />
				<?php
				}
				?>
			</div>
			<?php
			if($mall_distance) { ?>	
			<div class="mall_distance">
				<?php echo number_format($mall_distance, 2, '.', '').' KM'; ?>
			</div>
			<?php } ?>
			<div class="mall_title">
				<a href="<?php echo $mall_url ?>"><?php echo $mall_title ?></a>	
			</div>
		</div>
		</div>
		<?php
		return ob_get_clean();
	}
	


	//Render Login Modal Form
	public function loginmodalform() {  
		ob_start();
		global $post, $theme_settings;
		?>
		<div class="login_modal_form_outer_wrap" id="login_window">
			<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModal" aria-hidden="true">
			  <div class="modal-dialog">
			    <div class="modal-content">
			    	<div class="modal-header">
			    		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                                                <!--<img src="<?php // echo get_template_directory_uri(); ?>/assets/img/close-btn.png">-->
                                            </span><span class="sr-only"></span></button>
			         </div>
			      <div class="modal-body">
			      	<div class="alert alert-success email_confirmation_message" role="alert">
                  		Email Confirm Successfully !!! Now Login with your details.
                	</div>
			      	<p class="error_message has-error" id="error"><?php _e('Wrong username or password.', 'memalling'); ?></p>
			      	<!-- <h3 class="login-title">
			      			<input type="radio" name="login_type" value="user"><span class="option_label">User</span>
							<input type="radio" name="login_type" value="merchant"><span class="option_label">Merchant</span>
			      	</h3> -->
			        <div class="login_form_wrapper">
			        	<div class="email_address">
			        		<span class="label col-sm-3">Email Id</span>
			        		<div  class="col-sm-9"><input type="email" name="login-email" id="login-email"/></div>
			        		<div class="clearfix"></div>
			        	</div>
			        	
						<div class="password">
			        		<span class="label col-sm-3">Password</span>
			        		<div class="col-sm-9"><input type="password" name="login-password" id="login-password" /></div>
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
			        	<div class="login_but">
			        		<i class="fa fa-cog fa-spin login_loader" style="font-size:20px; display:none;"></i>
			        		<button type="button" class="btn btn-primary" id="login" action="mm_login">Login</button>
			        	</div>
			        	<?php wp_nonce_field( 'login-nonce', 'security' ); ?>
			        	<input type="hidden" value="<?php echo $post->ID; ?>" id="current_page_id" />
			        	<div class="clearfix"></div>

			        </div>
			        <div class="login_bottom_wrap">
				        <h5>Not a member yet?</h5>
				        <div class="signin_but">
				        		<a href="<?php echo get_permalink($theme_settings['signup']); ?>" class="btn btn-primary" title="Signup Now">Signup Now</a>
				        </div>
				        <div class="clearfix"></div>
				       <span class="otheroption" style="display: none;">OR</span>
				        <div class="signinmerchant_but" style="display: none;">
				        		<a href="<?php echo get_permalink($theme_settings['signupasmerchant']); ?>" class="btn btn-primary" title="Contact Us To Signup As Merchant">Contact Us To Signup As Merchant</a>
				        </div>
				        <div class="clearfix"></div>
			    	</div>
			      </div>
			    </div>
			  </div>
			</div>
	</div>
		<?php echo ob_get_clean(); 
	}


	//Reset Password Login
	public function reset_password_login() {		
		if(isset($_POST['arguments'])) {
			$reset_password = $_POST['arguments'][0];
			$flag = $_POST['arguments'][1];
			if ( wp_verify_nonce( $reset_password, 'reset_password' ) ) {
				$flag_array = explode("#", $flag);
				$id = $flag_array[0];
				$user = get_user_by( 'id', $id );				
				if( $user ) {
					$user_id = $id;
				    wp_set_current_user( $user_id );
				    wp_set_auth_cookie( $user_id );
				    //do_action( 'wp_login', $user->user_login );
				}
    	   }
		}
		die(0);
	}


	//Function to add merchant as a user role
	public function MM_register_new_user_role() {
		$merchant = get_role('merchant');
        if($merchant==null) {
            $merchant_role = add_role(
                'merchant',
                __( 'Merchant' ),
                array(
                    'read'         => true,  // true allows this capability
                    'edit_posts'   => false,
                    'delete_posts' => false, // Use false to explicitly deny
                )
            );
        }
	}

	//Function to hide admin bar

	public function MM_remove_admin_bar() {
		if (!current_user_can('administrator') && !is_admin()) {
		  show_admin_bar(false);
		}
	}

	//Function to Hide Backend to the users other than admin
	public function MM_hide_backend_for_users() {
		if ( is_user_logged_in() && ((is_admin() && ! current_user_can( 'administrator' )) && ! current_user_can( 'editor' )) &&
		! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		wp_redirect( home_url() );
		exit;
		}
	}

	//Load Script Templates in Footer
	// public function load_script_templates() {
 // 		echo file_get_contents(get_template_directory_uri().'/script_templates/templates.html');
 // 	}

 	//Add Query Var
	public function add_query_var($query_vars) {
		$query_vars[] = 'city';
		$query_vars[] = 'discount';
		$query_vars[] = 'pages';
		$query_vars[] = 'filterby';
		$query_vars[] = 'viewby';
		$query_vars[] = 'productcategory';
		//print_r($query_vars);
		return $query_vars;
	}
	
	//Change Permalinks for Mall CPT
 	public function malls_permalinks($permalink, $post, $leavename)
	{
	    $post_id = $post->ID;
	    if($post->post_type != 'mall' || empty($permalink) || in_array($post->post_status, array('draft', 'pending', 'auto-draft')))
		return $permalink;
		$city =  sanitize_title_with_dashes(get_post_meta($post_id, 'mall_city', true));
	    $permalink = str_replace('%city%', $city, $permalink);
	    return $permalink;
	}

	//Set Mall Name Slug Same with diffrent mall by location on wp_unique_post_slug hook
	public function mall_unique_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
	    if($post_type=="mall") {    
		    $slug = preg_replace('/[0-9]+/', '', $original_slug);
		    $slug = sanitize_title_with_dashes($slug);
		}
		return $slug;
	}

	//Rewrite Rules for Mall CPT
	public function add_rewrite_rules_for_malls_cpt()
	{
	    // Register custom rewrite rules
	    global $wp_rewrite;
	    $wp_rewrite->add_rewrite_tag('%mall%', '([^/]+)', 'mall=');
	    $wp_rewrite->add_rewrite_tag('%city%', '([^/]+)', 'city=');
	    $wp_rewrite->add_permastruct('mall', 'malls/%city%/%mall%');
	    flush_rewrite_rules();
	}

	//Rewrite Rules for Malls Archive
	public function add_rewrite_rules_for_malls_archive( $wp_rewrite )
	{
		$global_theme_settings = unserialize(get_option('global_theme_settings'));
		$dd_page_id = $global_theme_settings['dealsdiscounts'];		
		$fs_page_id = $global_theme_settings['freshstocks'];		
	    
	    $feed_rules = array(
        
	        'malls/?$'    =>  'index.php?post_type=mall',
	        'malls/page/([^/]+)/?$'    =>  'index.php?post_type=mall&page=$matches[1]',
	        'malls/([^/]+)/?$'    =>  'index.php?post_type=mall&city=$matches[1]',
	        'malls/([^/]+)/page/([^/]+)/?$'    =>  'index.php?post_type=mall&city=$matches[1]&page=$matches[2]',
		   		
		   	'malls/([^/]+)/([^/]+)/([^/]+)/?$'    =>  'index.php?post_type=mall&city=$matches[1]&mall=$matches[2]&filterby=$matches[3]',	
		   	'malls/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$'    =>  'index.php?post_type=mall&city=$matches[1]&mall=$matches[2]&filterby=$matches[3]&viewby=$matches[4]',	
		   	'malls/([^/]+)/([^/]+)/([^/]+)/([^/]+)/pages/([^/]+)/?$'    =>  'index.php?post_type=mall&city=$matches[1]&mall=$matches[2]&filterby=$matches[3]&viewby=$matches[4]&pages=$matches[5]',	
		   	'malls/([^/]+)/([^/]+)/([^/]+)/([^/]+)/productcategory/([^/]+)/?$'    =>  'index.php?post_type=mall&city=$matches[1]&mall=$matches[2]&filterby=$matches[3]&viewby=$matches[4]&productcategory=$matches[5]',		
		   	'malls/([^/]+)/([^/]+)/([^/]+)/([^/]+)/productcategory/([^/]+)/pages/([^/]+)/?$'    =>  'index.php?post_type=mall&city=$matches[1]&mall=$matches[2]&filterby=$matches[3]&viewby=$matches[4]&productcategory=$matches[5]&pages=$matches[6]',		

		    'products/([^/]+)/?$'    =>  'index.php?post_type=product&city=$matches[1]',
		    'products/([^/]+)/([^/]+)/?$'    =>  'index.php?post_type=product&city=$matches[1]&search=$matches[2]',
		    'products/([^/]+)/discount/([^/]+)/?$'    =>  'index.php?post_type=product&city=$matches[1]&discount=$matches[2]',
		    'products/([^/]+)/discount/([^/]+)/page/([^/]+)/?$'    =>  'index.php?post_type=product&city=$matches[1]&discount=$matches[2]&page=$matches[3]',

		    //'shops/?$'    =>  'index.php?post_type=shop',
		    //'shop/([^/]+)/discount/([^/]+)/?$'    =>  'index.php?post_type=shop&shop=$matches[1]&discount=$matches[2]',
		    //'shop/([^/]+)/discount/([^/]+)/page/([^/]+)/?$'    =>  'index.php?post_type=shop&shop=$matches[1]&discount=$matches[2]&pages=$matches[3]',

		    'shop/([^/]+)/products/pages/([^/]+)/?$'    =>  'index.php?post_type=shop&shop=$matches[1]&pages=$matches[2]',
		    'shop/([^/]+)/products/productcategory/([^/]+)/?$'    =>  'index.php?post_type=shop&shop=$matches[1]&productcategory=$matches[2]',
		    'shop/([^/]+)/products/productcategory/([^/]+)/pages/([^/]+)/?$'    =>  'index.php?post_type=shop&shop=$matches[1]&productcategory=$matches[2]&pages=$matches[3]',
			
			'deals-discounts/([^/]+)/?$'    =>  'index.php?page_id='.$dd_page_id.'&city=$matches[1]',
			'deals-discounts/([^/]+)/productcategory/([^/]+)/?$'    =>  'index.php?page_id='.$dd_page_id.'&city=$matches[1]&productcategory=$matches[2]',
			'deals-discounts/([^/]+)/pages/([^/]+)/?$'    =>  'index.php?page_id='.$dd_page_id.'&city=$matches[1]&pages=$matches[2]',
			'deals-discounts/([^/]+)/productcategory/([^/]+)/pages/([^/]+)/?$'    =>  'index.php?page_id='.$dd_page_id.'&city=$matches[1]&productcategory=$matches[2]&pages=$matches[3]',

			'fresh-stocks/([^/]+)/?$'    =>  'index.php?page_id='.$fs_page_id.'&city=$matches[1]',
			'fresh-stocks/([^/]+)/productcategory/([^/]+)/?$'    =>  'index.php?page_id='.$fs_page_id.'&city=$matches[1]&productcategory=$matches[2]',
			'fresh-stocks/([^/]+)/pages/([^/]+)/?$'    =>  'index.php?page_id='.$fs_page_id.'&city=$matches[1]&pages=$matches[2]',
			'fresh-stocks/([^/]+)/productcategory/([^/]+)/pages/([^/]+)/?$'    =>  'index.php?page_id='.$fs_page_id.'&city=$matches[1]&productcategory=$matches[2]&pages=$matches[3]',

			'product-category/([^/]+)/([^/]+)/?$'    =>  'index.php?taxonomy=product_cat&product_cat=$matches[1]&city=$matches[2]',
			'product-category/([^/]+)/([^/]+)/([^/]+)/?$'    =>  'index.php?taxonomy=product_cat&product_cat=$matches[2]&city=$matches[3]',
			'product-category/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$'    =>  'index.php?taxonomy=product_cat&product_cat=$matches[3]&city=$matches[4]',
			'product-category/([^/]+)/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$'    =>  'index.php?taxonomy=product_cat&product_cat=$matches[4]&city=$matches[5]',

			'brand/([^/]+)/([^/]+)/?$'    =>  'index.php?taxonomy=pa_brand&pa_brand=$matches[1]&city=$matches[2]',
		
	    );
	    $wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
	    return $wp_rewrite;
	}


	//CURL For Google Maps API
	public function curl_latlng_by_city($city) {
		$address = $city;
		$url = "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=India";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response);
		$latitude = $response_a->results[0]->geometry->location->lat;
		$longitude = $response_a->results[0]->geometry->location->lng;
		return array($latitude, $longitude);
	}

	//All Product Listing By Each Discount and City
	public function each_discount_all_products_by_city($discount, $city, $paged, $posts_per_page) {
		$product_args = array(
	        'post_type' => 'product',
	        'meta_key' => 'product_city',
	        'meta_value' => $city,
	        'meta_compare' => '==',
	        'post_status' => 'publish',
	        'tax_query' => array(
	            array(
	            'taxonomy' => 'product-discount',
	            'field' => 'term_id',
	            'terms' => $discount->term_id
	            )
	        ),
	        'posts_per_page' => $posts_per_page,
	        'paged' => $paged
	    );
        $product_query = new WP_Query( $product_args );
        
        if($product_query->have_posts()) {
	        echo '<div class="container view_all_products_wrapper">';
	        echo '<b class="product_sort_title">'.$discount->name.' off'.'</b>';
		    while ($product_query->have_posts()) : $product_query->the_post(); 
		        echo $this->each_product_view_html($product_query);
		    endwhile;
		    echo '</div>';
		}		
		echo '<div class="view_all_products_pagination">';
		wp_pagenavi( array('query' => $product_query) );
		wp_reset_postdata();
		$product_query = null;
		echo '</div>';
	}

	//All Product Listing By Each Discount and Shop
	public function each_discount_all_products_by_shop($discount, $shop, $paged, $posts_per_page) {
		$product_args = array(
	        'post_type' => 'product',
	        'meta_key' => 'shop',
	        'meta_value' => $shop,
	        'meta_compare' => '==',
	        'post_status' => 'publish',
	        'tax_query' => array(
	            array(
	            'taxonomy' => 'product-discount',
	            'field' => 'term_id',
	            'terms' => $discount->term_id
	            )
	        ),
	        'posts_per_page' => $posts_per_page,
	        'paged' => $paged
	    );
        $product_query = new WP_Query( $product_args );
        
        if($product_query->have_posts()) {
	        echo '<div class="container view_all_products_wrapper">';
	        echo '<b class="product_sort_title">'.$discount->name.' off'.'</b>';
		    while ($product_query->have_posts()) : $product_query->the_post(); 
		        echo $this->each_product_view_html($product_query);
		    endwhile;
		    echo '</div>';
		}		
		echo '<div class="view_all_products_pagination">';
		wp_pagenavi( array('query' => $product_query) );
		wp_reset_postdata();
		$product_query = null;
		echo '</div>';
	}
	
	// Product Listing By Discounts
	public function list_products_by_discount($id, $metakey) {
		$terms = get_terms('product-discount', 'hide_empty=0');
	  	if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
	    $discount_array = array();
	    foreach ($terms as $term) {
	        $discount_array[$term->slug] = array($term->term_id, $term->name);
	    }   
	    krsort($discount_array);
	    $count=1;

	    foreach($discount_array as $discount => $term) {
	        
	        $product_args = array(
	          'post_type' => 'product',
	          'meta_key' => $metakey,
	          'meta_value' => $id,
	          'meta_compare' => '==',
	          'post_status' => 'publish',
	          'tax_query' => array(
	              array(
	              'taxonomy' => 'product-discount',
	              'field' => 'term_id',
	              'terms' => $term[0])
	          ),
	          'posts_per_page' => 3,
	        );

	        $city = '';
	        $product_query = new WP_Query( $product_args );
	        $product_count = $product_query->found_posts;
	        if($product_query->have_posts()):
	            echo '<div class="products_row '. (++$count%2 ? "oddrow" : "evenrow") .'">'; 
	            echo '<div class="container">'; 
	            echo '<b class="product_sort_title">'.$term[1].' off'.'</b>';
	            while ($product_query->have_posts()) : $product_query->the_post(); 
	            	$city = get_post_meta($product_query->post->ID, 'product_city', true);
	            	echo $this->each_product_view_html($product_query);
	            endwhile; 
	            wp_reset_postdata();
				$product_query = null;
	            ?>
	        <div class="clearfix"></div>  
	        <?php 
	         if($product_count > 3){ 
		        if(is_singular('shop')) {
		        	global $post; 
		        	$shop = $post->post_name;	
		        	$view_product_archive_link = site_url().'/shop/'.$shop.'/discount/'.$discount;
		        } else {	
		        	$view_product_archive_link = site_url().'/products/'.$city.'/discount/'.$discount;
		        }
		        ?>
		        <a href="<?php echo $view_product_archive_link; ?>" class="button_viewall btn btn-default">View All</a>
		        <?php
	        }
	        echo '</div>';
	        echo '</div>';  
	        endif;
	        
	    }
	  	}
	}

	
	//Function to resize images 
	public function vt_resize( $attach_id = null, $img_url = null, $width, $height, $crop = false ) {
 
	// this is an attachment, so we have the ID
		if ( $attach_id ) {
 
		$image_src = wp_get_attachment_image_src( $attach_id, 'full' );
		$file_path = get_attached_file( $attach_id );
		 
		// this is not an attachment, let's use the image url
		} else if ( $img_url ) {
		 
		$file_path = parse_url( $img_url );
		$file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];
		 
		// Look for Multisite Path
		if(file_exists($file_path) === false){
		global $blog_id;
		$file_path = parse_url( $img_url );
		if (preg_match("/files/", $file_path['path'])) {
		$path = explode('/',$file_path['path']);
		foreach($path as $k=>$v){
		if($v == 'files'){
		$path[$k-1] = 'wp-content/blogs.dir/'.$blog_id;
		}
		}
		$path = implode('/',$path);
		}
		$file_path = $_SERVER['DOCUMENT_ROOT'].$path;
		}
		//$file_path = ltrim( $file_path['path'], '/' );
		//$file_path = rtrim( ABSPATH, '/' ).$file_path['path'];
		 
		$orig_size = getimagesize( $file_path );
		 
		$image_src[0] = $img_url;
		$image_src[1] = $orig_size[0];
		$image_src[2] = $orig_size[1];
		}
		 
		$file_info = pathinfo( $file_path );
		 
		// check if file exists
		$base_file = $file_info['dirname'].'/'.$file_info['filename'].'.'.$file_info['extension'];
		if ( !file_exists($base_file) )
		return;
		 
		$extension = '.'. $file_info['extension'];
		 
		// the image path without the extension
		$no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];
		 
		$cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;
		 
		// checking if the file size is larger than the target size
		// if it is smaller or the same size, stop right here and return
		if ( $image_src[1] > $width ) {
		 
		// the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
		if ( file_exists( $cropped_img_path ) ) {
		 
		$cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
		 
		$vt_image = array (
		'url' => $cropped_img_url,
		'width' => $width,
		'height' => $height
		);
		 
		return $vt_image;
		}
		 
		// $crop = false or no height set
		if ( $crop == false OR !$height ) {
		 
		// calculate the size proportionaly
		$proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
		$resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;
		 
		// checking if the file already exists
		if ( file_exists( $resized_img_path ) ) {
		 
		$resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );
		 
		$vt_image = array (
		'url' => $resized_img_url,
		'width' => $proportional_size[0],
		'height' => $proportional_size[1]
		);
		 
		return $vt_image;
		}
		}
		 
		// check if image width is smaller than set width
		$img_size = getimagesize( $file_path );
		if ( $img_size[0] <= $width ) $width = $img_size[0];
		 
		// Check if GD Library installed
		if (!function_exists ('imagecreatetruecolor')) {
		echo 'GD Library Error: imagecreatetruecolor does not exist - please contact your webhost and ask them to install the GD library';
		return;
		}
		 
		// no cache files - let's finally resize it
		$new_img_path = image_resize( $file_path, $width, $height, $crop ); 
		$new_img_size = getimagesize( $new_img_path );
		$new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );
		 
		// resized output
		$vt_image = array (
		'url' => $new_img,
		'width' => $new_img_size[0],
		'height' => $new_img_size[1]
		);
		 
		return $vt_image;
		}
		 
		// default output - without resizing
		$vt_image = array (
		'url' => $image_src[0],
		'width' => $width,
		'height' => $height
		);
		 
		return $vt_image;
		
	}


	//Get Deals and Discount Products
	public function get_deals_discount_products() {
		$city = $_GET['city'];
		$products_array = array();

		$products = "";
		$deal_products_count = 0;
		$dis_products_count = 0;

		$dealargs = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			"meta_key" => "product_deal_flag",
			"order" => "DESC",
                        "orderby" => 'DATE',
			'meta_query' => array(
				array(
		            'key' => 'city',
		            'value' => $city,
		            'compare'       => '==',
                    'type'          => 'CHAR'
		        )
		    ),
			'posts_per_page' => -1,
		);
		$deal_product_query = new WP_Query($dealargs);
		$deal_products_count = $deal_product_query->found_posts;
		
		if($deal_product_query->have_posts()) {
			while($deal_product_query->have_posts()) {
				$deal_product_query->the_post();
				$products_array[] = array($deal_product_query->post->ID, $deal_product_query->post->post_title);
			}
		}
		wp_reset_postdata();

		if($deal_product_query->post_count<6) {
			$dis_product_number = 6 - $deal_product_query->post_count;
			$dissargs = array(
				'post_type' => 'product',
				'post_status' => 'publish',
				"meta_key" => "_product_discount_number",
//				"orderby" => "meta_value",
                                "orderby" => "date",
				"order" => "DESC",
				'meta_query' => array(
					array(
			            'key' => 'city',
			            'value' => $city,
			            'compare'       => '==',
	                    'type'          => 'CHAR'
			        )
			    ),
				'posts_per_page' => $dis_product_number,
			);
			$dis_product_query = new WP_Query($dissargs);
			$dis_products_count = $dis_product_query->found_posts;
			
			if($dis_product_query->have_posts()) {
				while($dis_product_query->have_posts()) {
					$dis_product_query->the_post();
					$products_array[] = array($dis_product_query->post->ID, $dis_product_query->post->post_title);
				}
			}
			wp_reset_postdata();
		}

		//print_r($products_array);
		if(!empty($products_array)) {
			foreach($products_array as $product) {
				$product_id = $product[0];
				$product_title = $product[1];
				$mobile_class = 'col-xs-6';
		  		$products .= $this->each_product_view_html($product_id, 6, '', $mobile_class);
			}
		}

		$total_products = $deal_products_count + $dis_products_count;
		$view_all_button = '';
		if($total_products>6) {
			$global_theme_settings = unserialize(get_option('global_theme_settings'));
			$view_all_link = get_permalink($global_theme_settings['dealsdiscounts']).''.$city.'/';
			$view_all_button = '<div class="clearfix"></div><a href="'.$view_all_link.'" class="view_all_dis_deal_products">View All</a>';
		}
		

		echo json_encode(array('products'=>$products, 'view_all_button' => $view_all_button));
		die(0);
	}

	public function get_fresh_stock_products() {
		$city = $_GET['city'];
		$products_array = array();

		$products = "";

		$freshargs = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			"meta_key" => "no_deal_discount",
			"orderby" => "date",
			"order" => "DESC",
			'meta_query' => array(
				array(
		            'key' => 'city',
		            'value' => $city,
		            'compare'       => '==',
                    'type'          => 'CHAR'
		        )
		    ),
			'posts_per_page' => 6,
		);
		$fresh_product_query = new WP_Query($freshargs);
		
		if($fresh_product_query->have_posts()) {
			while($fresh_product_query->have_posts()) {
				$fresh_product_query->the_post();
				$products_array[] = array($fresh_product_query->post->ID, $fresh_product_query->post->post_title);
			}
		}
		wp_reset_postdata();

		//print_r($products_array);
		if(!empty($products_array)) {
			foreach($products_array as $product) {
				$product_id = $product[0];
				$product_title = $product[1];
				$mobile_class = 'col-xs-6';
		  		$products .= $this->each_product_view_html($product_id, 6, '', $mobile_class);
			}
		}

		$total_products = $fresh_product_query->found_posts;
		$view_all_button = '';
		if($total_products>6) {
			$global_theme_settings = unserialize(get_option('global_theme_settings'));
			$view_all_link = get_permalink($global_theme_settings['freshstocks']).''.$city.'/';
			$view_all_button = '<div class="clearfix"></div><a href="'.$view_all_link.'" class="view_all_fresh_products">View All</a>';
		}
		
		echo json_encode(array('products'=>$products, 'view_all_button' => $view_all_button));
		die(0);
	}

	//Product List View HTML
	public function each_product_view_html($product_id, $cols=6, $class="", $mobile_class="") {
		ob_start();
		$mall_id = get_post_meta($product_id,'mall', true);
		$shop_id = get_post_meta($product_id,'shop', true);
		$product_price = get_post_meta($product_id,'_regular_price', true);
	    $product_sale_price = get_post_meta($product_id,'_sale_price', true);
	    $product_discount = get_post_meta($product_id,'_product_discount', true);
	    $product_deal = get_post_meta($product_id,'product_deal', true);
	    $currency = 'Rs. ';
	    $terms = get_the_terms( $product_id, 'product-discount' );
	    
	    $saved_hightlight_attribute = get_option("product_highlight_attributes");
	    $product_cats = get_the_terms($product_id, 'product_cat');
	    $product_cat_class = ""; 
	    if(!empty($product_cats)) {
		    foreach($product_cats as $product_cat) {
		    	if($product_cat->parent==0) {
		    		$product_cat_class = $product_cat->slug;
		    	}
		    }
		}

	    $highlight_attribute = "";
	    if(!empty($product_cats)) {
	    	foreach($product_cats as $product_cat) {
	    		if($saved_hightlight_attribute[$product_cat->term_id]!='') {
	    			$highlight_attribute = $saved_hightlight_attribute[$product_cat->term_id];
	    		}
	       	}
	    }
	    	   	
	   	$product_attribute_object = get_the_terms($product_id, trim($highlight_attribute));
	   
	   	$product_attribute = '';
	   	$k=0;
	   	if(!empty($product_attribute_object)) {
		   	foreach($product_attribute_object as $attribute) {
		   		if($k==0) {
		   			$product_attribute = $attribute->name;
		   		}
		   		$k++;
		   	}
	   	}
	   	if($product_cat_class!="") {
	   		$class .= ' '.$product_cat_class;		
	    }
	    ?>
	    <div class="product_outer_wrapper <?php echo $mobile_class; ?> col-sm-<?php echo $cols." ".$class; ?>">
	        <div class="product_inner_wrapper" title="<?php echo get_the_title($product_id); ?>">
	            <?php if ( has_post_thumbnail() ) { 

	    	        $productimage = wp_get_attachment_url( get_post_thumbnail_id($product_id) ); 	    	        
	    	        $attach_id = get_post_thumbnail_id($product_id);
	    	        $width = '290';
	    	        $height = '315';
	    	        $image = $this->vt_resize( $attach_id, '', $width, $height, true );
	    	        ?>
	                <div class="product_image product_preview" data-toggle="modal" data-target="#productview<?php echo $product_id; ?>" data-product-id="<?php echo $product_id; ?>">
	                	<!-- <img src="<?php //echo $image[url]; ?>" width="<?php //echo $image[width]; ?>" height="<?php //echo $image[height]; ?>" class="img-responsive" /> -->
	                	<img src="<?php echo get_template_directory_uri()."/timthumb.php?src=".$productimage."&h=315&w=290&zc=2"; ?>" class="img-responsive" />
	                    <?php 
	                    if($product_deal!='') {
	                    	?>
	                    	<div class="product_deal_text"><?php echo $product_deal; ?></div>
	                    	<div class="product_deal">DEAL</div>
	                    	<?php
	                    }
	                    else if($product_discount !=''){
                                $percent_value = str_replace("%","",$product_discount);
                                ?>
	                        <div class="product_discount"><?php echo $percent_value.'%'; ?></div>
	                    <?php } 
	                    else{  } ?>
	                    <!-- <div class="product_view product_preview" data-toggle="modal" data-target="#productview<?php //echo $product_id; ?>" data-product-id="<?php //echo $product_id; ?>" >Preview</div> -->
	                    <div class="product_view product_view_loader" style="display:none; background: #ccc;">Loading...</div>
                        </div>
                    <?php } ?>
                    <div class="product_title">
                        <a href="<?php echo get_permalink($product_id); ?>">
                        <?php 
                        $product_title = get_the_title($product_id);
                        if ( strlen ($product_title) > 10 ) {
                            echo substr($product_title, 0, 20).'..'; 
                        } else {
                            echo get_the_title($product_id); 
                        }
                        ?>
                        </a>
                    </div>
                    <div class="product_price">
                        <?php 
                        if($product_sale_price!='') {
                            setlocale(LC_MONETARY, 'en_IN');
                            $amount = money_format('%!.0n', $product_price);
                            $sale_price = money_format('%!.0n', $product_sale_price);
                            ?>
                            <span class="discounted_price col-sm-6"><?php if($product_sale_price != ''){ echo $currency . $sale_price."/-" ;}?></span>
                            <span class="original_price col-sm-6"><?php if($product_price != ''){ echo $currency . $amount."/-"; }?></span>
                        <?php } else {
                            setlocale(LC_MONETARY, 'en_IN');
                            $amount = money_format('%!.0n', $product_price);
                            ?>
                            <span class="regular_product_price"><?php if($product_price != ''){ echo $currency . $amount."/-" ;}?></span>
                        <?php } ?>
                        <div class="clearfix"></div>
                    </div>
                    <div class="product_attribute_mall_info row">
                        <div class="product_attribute_mall col-sm-12">
                            <div class="product_mall col-sm-6">
                                <?php 
                                if($shop_id != ''){ ?>	
                                    <a class="shopmalllink" title="<?php echo get_the_title($shop_id); ?>" href="<?php echo get_permalink($shop_id); ?>"> <?php echo get_the_title($shop_id); ?> </a>
                                <?php } ?>
                            </div>
                            <div class="product_mall col-sm-6 mall_with_attribute">
                                <?php 
                                if($mall_id != ''){ ?>	
                                    <a class="shopmalllink" title="<?php echo get_the_title($mall_id); ?>" href="<?php echo get_permalink($mall_id); ?>"> <?php echo get_the_title($mall_id); ?></a>
                                <?php } ?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
	        </div>
	    </div>
		<?php
		return ob_get_clean();	
	}


}


$GLOBALS['MMGlobalFunctions'] = new ThemeFunctions();

