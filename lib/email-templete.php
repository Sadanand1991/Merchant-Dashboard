<?php
 
define('ADMIN_EMAIL_TEMPLETE', 'admin_email_templete');

class admin_email_templete_page {
 
	function admin_email_templete_page() {
		add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
		add_action('admin_menu', array(&$this, 'on_admin_menu'));
                add_action('admin_post_save_jeweldb_admin_email_templete', array(&$this, 'on_save_changes'));
        }
	
	function on_screen_layout_columns($columns, $screen) {
		if ($screen == $this->pagehook) {
		$columns[$this->pagehook] = 2;
		}
		return $columns;
	}

	//extend the admin menu
	function on_admin_menu() {
		$this->pagehook = add_submenu_page('theme_settings', __('Email Templates',''), __('Email Templates',''), 'manage_options', ADMIN_EMAIL_TEMPLETE, array(&$this, 'on_show_page'),'',90 );
		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
	}

	//will be executed if wordpress core detects this page has to be rendered
	function on_load_page() {
		//ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		add_meta_box('coupon-event', 'Coupon Event', array(&$this, 'coupon_email_settings'), $this->pagehook, 'normal', 'core');
                add_meta_box('birthday-email', 'Birth Day Email', array(&$this, 'birthday_email_settings'), $this->pagehook, 'normal', 'core');
		add_meta_box('anniversary-email', 'Anniversary Email', array(&$this, 'anniversary_email_settings'), $this->pagehook, 'normal', 'core');
		add_meta_box('new-user-registration-sms', 'New User Registration SMS', array(&$this, 'new_user_registartion_sms_settings'), $this->pagehook, 'normal', 'core');
                add_meta_box('new-order-sms', 'New Order SMS', array(&$this, 'new_order_sms_settings'), $this->pagehook, 'normal', 'core');
                add_meta_box('completed-order-sms', 'Completed Order SMS', array(&$this, 'completed_order_sms_settings'), $this->pagehook, 'normal', 'core');
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
		<h2>Email Templetes</h2>
		<form action="admin-post.php" method="post">
		<?php wp_nonce_field('theme-settings-metaboxes'); ?>
		<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
		<input type="hidden" name="action" value="save_jeweldb_admin_email_templete" />
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
//                print_r($_POST['admin_email_templete']); exit;
		if ( !current_user_can('manage_options') )
		wp_die( __('Cheatin&#8217; uh?') );	
		//cross check the given referer
		check_admin_referer('theme-settings-metaboxes');
//                print_r($_POST['admin_email_templete']);
		//process here your on $_POST validation and / or option saving
		update_option('global_admin_email_templete',serialize($_POST['admin_email_templete']));
		wp_redirect($_POST['_wp_http_referer']);	
	}
      
        
        function coupon_email_settings(){          
            $global_admin_email_templete = unserialize(get_option('global_admin_email_templete'));
        ?>
            <div class='general_setting_sections'>
              <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Coupon Amount : 
              <input type='text' name="admin_email_templete[coupon_amount]" placeholder="Coupon Amount" value="<?php echo $global_admin_email_templete['coupon_amount']; ?>" style="width : 300px; padding: 8px 5px 1px;" /> </p>
              <div class='clear'></div>
            </div>
        <?php
        }
        
        function birthday_email_settings(){          
            $global_admin_email_templete = unserialize(get_option('global_admin_email_templete'));
        ?>
        <p class="description">This e-mail will be sent to a new user Birthday.</p>
        
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Subject : </p>
          <input type='text' name="admin_email_templete[birthday_subject]" placeholder="Subject" value="<?php echo $global_admin_email_templete['birthday_subject']; ?>" style="width : 300px; padding: 8px 5px 1px;" />
          <div class='clear'></div>
        </div>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Message : </p>
          <p class="description" style="">Available Variables: %user_name%,%blogname%, %siteurl%,  %user_email%, %first_name%, %last_name%, %coupon_code%, %coupon_amount% </p>
          <?php wp_editor($global_admin_email_templete['birthday_message'], 'admin_email_templete[birthday_message]', array('textarea_name' => 'admin_email_templete[birthday_message]', 'media_buttons' => false, 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8)); ?>
          <div class='clear'></div>
        </div>
        <?php
        }
        
        function anniversary_email_settings(){          
               $global_admin_email_templete = unserialize(get_option('global_admin_email_templete'));
        ?>
        <p class="description">This e-mail will be sent to a new user Anniversary.</p>
        
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Subject : </p>
          <input type='text' name="admin_email_templete[anniversary_subject]" placeholder="Subject" value="<?php echo $global_admin_email_templete['anniversary_subject']; ?>" style="width : 300px; padding: 8px 5px 1px;" />
          <div class='clear'></div>
        </div>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Message : </p>
          <p class="description" style="">Available Variables: %user_name%,%blogname%, %siteurl%,  %user_email%, %first_name%, %last_name%, %coupon_code%, %coupon_amount% </p>
          <?php wp_editor($global_admin_email_templete['anniversary_message'], 'admin_email_templete[anniversary_message]', array('textarea_name' => 'admin_email_templete[anniversary_message]', 'media_buttons' => false, 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8)); ?>
          <div class='clear'></div>
        </div>
        <?php
        }
        function new_user_registartion_sms_settings(){          
            $global_admin_email_templete = unserialize(get_option('global_admin_email_templete'));
        ?>
        <div class='general_setting_sections'>
            <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Message : </p>
            <p class="description" style="">Available Variables:%confirmation_link% </p>
            <?php wp_editor($global_admin_email_templete['new_user_sms'], 'admin_email_templete[new_user_sms]', array('textarea_name' => 'admin_email_templete[new_user_sms]', 'media_buttons' => false, 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8)); ?>
            <div class='clear'></div>
        </div>
        <?php
        }
        function new_order_sms_settings(){          
            $global_admin_email_templete = unserialize(get_option('global_admin_email_templete'));
        ?>
          <div class='general_setting_sections'>
            <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Message : </p>
            
            <?php wp_editor($global_admin_email_templete['new_order_sms'], 'admin_email_templete[new_order_sms]', array('textarea_name' => 'admin_email_templete[new_order_sms]', 'media_buttons' => false, 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8)); ?>
            <div class='clear'></div>
        </div>
        <?php
        }
        function completed_order_sms_settings(){          
            $global_admin_email_templete = unserialize(get_option('global_admin_email_templete'));
        ?>
           <div class='general_setting_sections'>
            <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Message : </p>
           
            <?php wp_editor($global_admin_email_templete['completed_order_sms'], 'admin_email_templete[completed_order_sms]', array('textarea_name' => 'admin_email_templete[completed_order_sms]', 'media_buttons' => false, 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8)); ?>
            <div class='clear'></div>
        </div>
        <?php
        }

}

$admin_email_templete_page = new admin_email_templete_page();
$GLOBALS['admin_email_templete'] = unserialize(get_option('global_admin_email_templete'));


