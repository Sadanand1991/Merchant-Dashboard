<?php
 
define('CUSTOM_SETTING', 'custom_setting');

class custom_setting_page {
 
	function custom_setting_page() {
		add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
		add_action('admin_menu', array(&$this, 'on_admin_menu'));
                add_action('admin_post_save_jeweldb_custom_setting', array(&$this, 'on_save_changes'));
        }
	
	function on_screen_layout_columns($columns, $screen) {
		if ($screen == $this->pagehook) {
		$columns[$this->pagehook] = 2;
		}
		return $columns;
	}

	//extend the admin menu
	function on_admin_menu() {
		$this->pagehook = add_submenu_page('theme_settings',__('CUSTOM SETTING',''), __('Custom Setting',''), 'manage_options', CUSTOM_SETTING, array(&$this, 'on_show_page'),'',90 );
		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
	}

	//will be executed if wordpress core detects this page has to be rendered
	function on_load_page() {
		//ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		add_meta_box('Gold Price', 'Gold Price', array(&$this, 'gold_price_settings'), $this->pagehook, 'normal', 'core');
                add_meta_box('Commission', 'Commission', array(&$this, 'Commission_settings'), $this->pagehook, 'normal', 'core');
                add_meta_box('Silver Price', 'Silver Price', array(&$this, 'silver_price_settings'), $this->pagehook, 'normal', 'core');
                add_meta_box('Platinum Price', 'Platinum Price', array(&$this, 'platinum_price_settings'), $this->pagehook, 'normal', 'core');
                
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
		<h2>Custom Settings</h2>
		<form action="admin-post.php" method="post">
		<?php wp_nonce_field('theme-settings-metaboxes'); ?>
		<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
		<input type="hidden" name="action" value="save_jeweldb_custom_setting" />
		<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
                    <div id="side-info-column" class="inner-sidebar">
                    <?php do_meta_boxes($this->pagehook, 'side', $data); ?>
                    </div>
                    <div id="post-body" class="has-sidebar">
                        <div id="post-body-content" class="has-sidebar-content">
                            <?php do_meta_boxes($this->pagehook, 'normal', $data); ?>
                            <p>
                            <input type="submit" value="Save Changes" class="button-primary" name="Submit"/>
                            </p>
                            <?php do_meta_boxes($this->pagehook, 'additional', $data); ?>
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
//                print_r($_POST['custom_setting']); exit;
		if ( !current_user_can('manage_options') )
		wp_die( __('Cheatin&#8217; uh?') );	
		//cross check the given referer
		check_admin_referer('theme-settings-metaboxes');
//                print_r($_POST['custom_setting']);
		//process here your on $_POST validation and / or option saving
		update_option('global_custom_setting',serialize($_POST['custom_setting']));
		wp_redirect($_POST['_wp_http_referer']);	
	}
        
        function gold_price_settings(){          
            $global_custom_setting = unserialize(get_option('global_custom_setting'));
        ?>
            <div class='general_setting_sections'>
              <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Gold Price(gms.) : 
              <input type='text' name="custom_setting[gold_price]" placeholder="Eg. Gold price 3100 / gram" value="<?php echo $global_custom_setting['gold_price']; ?>" style="width : 300px; padding: 8px 5px 1px;" /> </p>
              <div class='clear'></div>
            </div>
             <div class='general_setting_sections'>
              <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">22(KARAT % FROM PURE GOLD) : 
              <input type='text' name="custom_setting[gold_c22]" placeholder="% FROM PURE GOLD" value="<?php echo $global_custom_setting['gold_c22']; ?>" style="width : 300px; padding: 8px 5px 1px;" /> </p>
              <div class='clear'></div>
            </div>
             <div class='general_setting_sections'>
              <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">18(KARAT % FROM PURE GOLD) : 
              <input type='text' name="custom_setting[gold_c18]" placeholder="% FROM PURE GOLD" value="<?php echo $global_custom_setting['gold_c18']; ?>" style="width : 300px; padding: 8px 5px 1px;" /> </p>
              <div class='clear'></div>
            </div>
             <div class='general_setting_sections'>
              <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">14(KARAT % FROM PURE GOLD) : 
              <input type='text' name="custom_setting[gold_c14]" placeholder="% FROM PURE GOLD" value="<?php echo $global_custom_setting['gold_c14']; ?>" style="width : 300px; padding: 8px 5px 1px;" /> </p>
              <div class='clear'></div>
            </div>
             <div class='general_setting_sections'>
              <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">12(KARAT % FROM PURE GOLD) : 
              <input type='text' name="custom_setting[gold_c12]" placeholder="% FROM PURE GOLD" value="<?php echo $global_custom_setting['gold_c12']; ?>" style="width : 300px; padding: 8px 5px 1px;" /> </p>
              <div class='clear'></div>
            </div>
            <div class='general_setting_sections'>
              <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">8(KARAT % FROM PURE GOLD) : 
              <input type='text' name="custom_setting[gold_c8]" placeholder="% FROM PURE GOLD" value="<?php echo $global_custom_setting['gold_c8']; ?>" style="width : 300px; padding: 8px 5px 1px;" /> </p>
              <div class='clear'></div>
            </div>
        <?php
        }
      function Commission_settings(){          
            $global_custom_setting = unserialize(get_option('global_custom_setting'));
        ?>
            <p>You can Change Your commission</p>    
            <div class='general_setting_sections'>
              <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Commission(%) : 
              <input type='text' name="custom_setting[commission_pers]" placeholder="% for all products" value="<?php echo $global_custom_setting['commission_pers']; ?>" style="width : 300px; padding: 8px 5px 1px;" /> </p>
              <div class='clear'></div>
            </div>
        <?php
        }
         function silver_price_settings(){          
            $global_custom_setting = unserialize(get_option('global_custom_setting'));
        ?>
               
            <div class='general_setting_sections'>
              <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Silver Price (gms.): 
              <input type='text' name="custom_setting[silver_price]" placeholder="Silver Price in gms" value="<?php echo $global_custom_setting['silver_price']; ?>" style="width : 300px; padding: 8px 5px 1px;" /> </p>
              <div class='clear'></div>
            </div>
        <?php
        }
         function platinum_price_settings(){          
            $global_custom_setting = unserialize(get_option('global_custom_setting'));
        ?>
             
            <div class='general_setting_sections'>
              <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Platinum Price (gms.): 
              <input type='text' name="custom_setting[platinum_price]" placeholder="platinum price in gms" value="<?php echo $global_custom_setting['platinum_price']; ?>" style="width : 300px; padding: 8px 5px 1px;" /> </p>
              <div class='clear'></div>
            </div>
        <?php
        }
}

$custom_setting_page = new custom_setting_page();
$GLOBALS['custom_setting'] = unserialize(get_option('global_custom_setting'));


