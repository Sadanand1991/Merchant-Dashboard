<?php
 
define('THEME_SETTINGS', 'theme_settings');

class theme_settings_page {
 
	function theme_settings_page() {
		add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
		add_action('admin_menu', array(&$this, 'on_admin_menu'));
		add_action('admin_post_save_jeweldb_theme_settings', array(&$this, 'on_save_changes'));
	}
	
	function on_screen_layout_columns($columns, $screen) {
		if ($screen == $this->pagehook) {
		$columns[$this->pagehook] = 2;
		}
		return $columns;
	}

	//extend the admin menu
	function on_admin_menu() {
		$this->pagehook = add_menu_page(__('Theme Settings',''), __('Theme Settings',''), 'manage_options', THEME_SETTINGS, array(&$this, 'on_show_page'),'',90 );
		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
	}

	//will be executed if wordpress core detects this page has to be rendered
	function on_load_page() {
		//ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		 
		//add several metaboxes now, all metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
		add_meta_box('general-settings', 'General Settings', array(&$this, 'general_settings_meta_box'), $this->pagehook, 'normal', 'core');
		
//		add_meta_box('social-settings', 'Social Links Settings', array(&$this, 'social_settings_meta_box'), $this->pagehook, 'normal', 'core');
                
//                add_meta_box('product-bag-settings', 'Product Bag setting', array(&$this, 'set_custom_pages_meta_box'), $this->pagehook, 'normal', 'core');
		//add_meta_box('sms-gateway-settings', 'SMS Gateway Settings', array(&$this, 'sms_gateway_settings_meta_box'), $this->pagehook, 'normal', 'core');
		
	}
	//executed to show the plugins complete admin page
	function on_show_page() {
		//we need the global screen column value to beable to have a sidebar in WordPress 2.8
		global $screen_layout_columns;
		//define some data can be given to each metabox during rendering
		$data = array();
		?>
		<div id="theme-settings-metaboxes" class="wrap">
		<?php screen_icon('options-general'); ?>
		<h2>Theme Settings</h2>
		<form action="admin-post.php" method="post">
		<?php wp_nonce_field('theme-settings-metaboxes'); ?>
		<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
		<input type="hidden" name="action" value="save_jeweldb_theme_settings" />
		<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
		<div id="side-info-column" class="inner-sidebar">
		<?php do_meta_boxes($this->pagehook, 'side', $data); ?>
		</div>
		<div id="post-body" class="has-sidebar">
		<div id="post-body-content" class="has-sidebar-content">
		<?php do_meta_boxes($this->pagehook, 'normal', $data); ?>
				<?php do_meta_boxes($this->pagehook, 'additional', $data); ?>
		<p>
		<input type="submit" value="Save Changes" class="button-primary" name="Submit"/>
		</p>
		</div>
		</div>
		<br class="clear"/>
		</div>
		</form>
		</div>
                <script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			// close postboxes that should be closed
			jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			// postboxes setup
			postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			jQuery("select").chosen({no_results_text: "Oops, nothing found!"});
		});
		//]]>
		</script>
		
		<?php
	}
	 
	//executed if the post arrives initiated by pressing the submit button of form
	function on_save_changes() {
		//user permission check
		if ( !current_user_can('manage_options') )
		wp_die( __('Cheatin&#8217; uh?') );	
		//cross check the given referer
		check_admin_referer('theme-settings-metaboxes');
		//process here your on $_POST validation and / or option saving
		update_option('global_theme_settings',serialize($_POST['theme_settings']));
		wp_redirect($_POST['_wp_http_referer']);	
	}
	
        function general_settings_meta_box($data) {
		$global_theme_settings = unserialize(get_option('global_theme_settings'));
		?>

		<div class='general_setting_sections'>
			<p>Home  Logo : </p>
			<div class='site_logo upload_image'>
				<?php 
				$style = "style='display:none; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				if($global_theme_settings['site_logo']!="") { 
				$style = "style='display:block; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				} ?>
				<img src='<?php echo $global_theme_settings['site_logo']; ?>'  <?php echo $style; ?> title="Click here for Remove Image" class="mm_admin_remove_ts_image" />
			</div>
			<input type='hidden' name="theme_settings[site_logo]" value='<?php echo $global_theme_settings['site_logo']; ?>' />
			<div class="button button-primary button-large upload_image_button upload_image mm_admin_image_uploader" data-title="Site Logo Uploader" data-button-text="Set Site Logo">Upload Site Logo</div>
			<div class='clear'></div>
		</div>
                <div class='general_setting_sections'>
			<p>Header Logo : </p>
			<div class='site_logo upload_image'>
				<?php 
				$style = "style='display:none; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				if($global_theme_settings['header_logo']!="") { 
				$style = "style='display:block; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				} ?>
				<img src='<?php echo $global_theme_settings['header_logo']; ?>'  <?php echo $style; ?> title="Click here for Remove Image" class="mm_admin_remove_ts_image" />
			</div>
			<input type='hidden' name="theme_settings[header_logo]" value='<?php echo $global_theme_settings['header_logo']; ?>' />
			<div class="button button-primary button-large upload_image_button upload_image mm_admin_image_uploader" data-title="Header Logo Uploader" data-button-text="Set Header Logo">Upload Header Logo</div>
			<div class='clear'></div>
		</div>
<!--                <div class='general_setting_sections'>
			<p>Made To Order  Logo : </p>
			<div class='site_logo upload_image'>
				<?php 
//				$style = "style='display:none; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
//				if($global_theme_settings['header_logo']!="") { 
//				$style = "style='display:block; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
//				} ?>
				<img src='<?php // echo $global_theme_settings['made_to_order_logo']; ?>'  <?php // echo $style; ?> title="Click here for Remove Image" class="mm_admin_remove_ts_image" />
			</div>
			<input type='hidden' name="theme_settings[made_to_order_logo]" value='<?php // echo $global_theme_settings['made_to_order_logo']; ?>' />
			<div class="button button-primary button-large upload_image_button upload_image mm_admin_image_uploader" data-title="Header Logo Uploader" data-button-text="Set Header Logo">Upload Made To Order Logo</div>
			<div class='clear'></div>
		</div>-->

		

		<!-- <div class='general_setting_sections'>
			<p>Site Favicon (16px x 16px) : </p>
			<div class='site_favicon upload_image'>
				<?php 
				// $style = "style='display:none;'";
				// if($global_theme_settings['site_favicon']!="") { 
				// $style = "style='display:block;'";	
				//} ?>
				<img src='<?php //echo $global_theme_settings['site_favicon']; ?>'  <?php //echo $style; ?> />
			</div>
			<input type='hidden' name="theme_settings[site_favicon]" value='<?php //echo $global_theme_settings['site_favicon']; ?>' />
			<div class="button button-primary button-large upload_image_button upload_image mm_admin_image_uploader">Upload Site Favicon</div>
			<div class='clear'></div>
		</div> -->
	
		<?php
	}
	function social_settings_meta_box($data) {
		$global_theme_settings = unserialize(get_option('global_theme_settings'));	
		?>
		<p>(Use http:// in URL) </p>
		<div class='general_setting_sections'>
			<p>Facebook Link : </p>
			<input type='text' name="theme_settings[facebook_link]" value='<?php echo $global_theme_settings['facebook_link']; ?>' style='width:80%;' />
			
                        <div class='clear'></div>
                </div>
                <div class='general_setting_sections'>
			<p>Facebook Icon : </p>
			<div class='site_logo upload_image'>
				<?php 
				$style = "style='display:none; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				if($global_theme_settings['facbook_icon']!="") { 
				$style = "style='display:block; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				} ?>
				<img src='<?php echo $global_theme_settings['facbook_icon']; ?>'  <?php echo $style; ?> title="Click here for Remove Image" class="mm_admin_remove_ts_image" />
			</div>
			<input type='hidden' name="theme_settings[facbook_icon]" value='<?php echo $global_theme_settings['facbook_icon']; ?>' />
			<div class="button button-primary button-large upload_image_button upload_image mm_admin_image_uploader" data-title="Facebook Icon Uploader" data-button-text="Set Facebook Icon">Upload Facebook Icon</div>
			<div class='clear'></div>
		</div>
			<div class='general_setting_sections'>
			<p>Pinterest Link : </p>
			<input type='text' name="theme_settings[twitter_link]" value='<?php echo $global_theme_settings['twitter_link']; ?>' style='width:80%;'/>
			<div class='clear'></div>
			</div>
                 <div class='general_setting_sections'>
			<p>Pinterest Icon : </p>
			<div class='site_logo upload_image'>
				<?php 
				$style = "style='display:none; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				if($global_theme_settings['twitter_icon']!="") { 
				$style = "style='display:block; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				} ?>
				<img src='<?php echo $global_theme_settings['twitter_icon']; ?>'  <?php echo $style; ?> title="Click here for Remove Image" class="mm_admin_remove_ts_image" />
			</div>
			<input type='hidden' name="theme_settings[twitter_icon]" value='<?php echo $global_theme_settings['twitter_icon']; ?>' />
			<div class="button button-primary button-large upload_image_button upload_image mm_admin_image_uploader" data-title="Tweeter Icon Uploader" data-button-text="Set Tweeter Icon">Upload Pinterest Icon</div>
			<div class='clear'></div>
		</div>
			
			<div class='general_setting_sections'>
			<p>Instagram Link : </p>
			<input type='text' name="theme_settings[google_plus_link]" value='<?php echo $global_theme_settings['google_plus_link']; ?>' style='width:80%;'/>
			<div class='clear'></div>
			</div>
                 <div class='general_setting_sections'>
			<p>Instagram Icon : </p>
			<div class='site_logo upload_image'>
				<?php 
				$style = "style='display:none; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				if($global_theme_settings['insta_icon']!="") { 
				$style = "style='display:block; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				} ?>
				<img src='<?php echo $global_theme_settings['insta_icon']; ?>'  <?php echo $style; ?> title="Click here for Remove Image" class="mm_admin_remove_ts_image" />
			</div>
			<input type='hidden' name="theme_settings[insta_icon]" value='<?php echo $global_theme_settings['insta_icon']; ?>' />
			<div class="button button-primary button-large upload_image_button upload_image mm_admin_image_uploader" data-title="Instagram Icon Uploader" data-button-text="Set Instagram Icon">Upload Instagram Icon</div>
			<div class='clear'></div>
		</div>
<!--                <div class='general_setting_sections'>
			<p>Linked in Link : </p>
			<input type='text' name="theme_settings[linkedin_link]" value='<?php // echo $global_theme_settings['linkedin_link']; ?>' style='width:80%;'/>
			<div class='clear'></div>
			</div>
                 <div class='general_setting_sections'>
			<p>Linked in Icon : </p>
			<div class='site_logo upload_image'>
				<?php 
//				$style = "style='display:none; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
//				if($global_theme_settings['linkedin_icon']!="") { 
//				$style = "style='display:block; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
//				} ?>
				<img src='<?php // echo $global_theme_settings['linkedin_icon']; ?>'  <?php // echo $style; ?> title="Click here for Remove Image" class="mm_admin_remove_ts_image" />
			</div>
			<input type='hidden' name="theme_settings[linkedin_icon]" value='<?php // echo $global_theme_settings['linkedin_icon']; ?>' />
			<div class="button button-primary button-large upload_image_button upload_image mm_admin_image_uploader" data-title="Linked in Icon Uploader" data-button-text="Set Linked in Icon">Upload Linked in Icon</div>
			<div class='clear'></div>
		</div>-->
                <div class='general_setting_sections'>
			<p>Option Link : </p>
			<input type='text' name="theme_settings[option_link]" value='<?php echo $global_theme_settings['option_link']; ?>' style='width:80%;' />
			
                        <div class='clear'></div>
                </div>
                <div class='general_setting_sections'>
			<p>Option Icon : </p>
			<div class='site_logo upload_image'>
				<?php 
				$style = "style='display:none; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				if($global_theme_settings['option_icon']!="") { 
				$style = "style='display:block; border:1px solid #ccc; padding:10px; max-width:100%; margin-bottom:5px; cursor: pointer;'";
				} ?>
				<img src='<?php echo $global_theme_settings['option_icon']; ?>'  <?php echo $style; ?> title="Click here for Remove Image" class="mm_admin_remove_ts_image" />
			</div>
			<input type='hidden' name="theme_settings[option_icon]" value='<?php echo $global_theme_settings['option_icon']; ?>' />
			<div class="button button-primary button-large upload_image_button upload_image mm_admin_image_uploader" data-title="Option Icon Uploader" data-button-text="Set Option Icon">Upload Option Icon</div>
			<div class='clear'></div>
		</div>
		
		<?php
	}
        
        function set_custom_pages_meta_box($data) {
		$global_theme_settings = unserialize(get_option('global_theme_settings'));	
		?>
			<div class='general_setting_sections'>
			<p>Select Product for view in bag : </p>
			<?php $args = array('post_type'=>'product', 'posts_per_page'=> -1, 'post_status' => 'publish');
		$pages = new WP_Query($args);
		if($pages->have_posts()) {
		?>
		<select name="theme_settings[product_bag]" style="display: block!important;">
				<option value=''></option>
				<?php 
				while($pages->have_posts()) {
				$pages->the_post();	
				$selected = "";
                                $postid = $global_theme_settings[product_bag];
				if($postid==$pages->post->ID) {
					$selected = "selected=selected";
				}
				?>	
				<option value='<?php echo $pages->post->ID ?>' <?php echo $selected; ?>><?php echo $pages->post->post_title; ?></option>
				<?php
				}
				?>
		</select>	
		<?php
		}
		wp_reset_postdata(); ?>
			<div class='clear'></div>
			</div>
                <div class='general_setting_sections'>
                    <p>Headline For Product in bag : </p>
                    <input type='text' name="theme_settings[headline_for_product]" value='<?php echo $global_theme_settings['headline_for_product']; ?>' style='width:80%;'/>
                    <div class='clear'></div>
			</div>
		<?php
	}

}

$theme_settings_page = new theme_settings_page();
$GLOBALS['theme_settings'] = unserialize(get_option('global_theme_settings'));


