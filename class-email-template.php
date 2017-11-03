<?php
/**
 * @class 		EmailTemplates
 * @version		1.0
 * @package		
 * @category             Class
 */
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class EmailTemplates {

  protected static $_instance = null;

  /**
   * EmailTemplates Constructor.
   * 
   * @access public
   * @return EmailTemplates
   */
  public function __construct() {
      add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
  }

  public function load_settings_page() {
    //add_action('phpmailer_init', array(&$this, 'send_smtp_email'));
    add_filter('screen_layout_columns', array(&$this, 'on_screen_layout_columns'), 10, 2);
    add_action('admin_menu', array(&$this, 'on_admin_menu'));
    add_action('admin_post_save_MM_email_template_settings', array(&$this, 'on_save_changes'));

  }

  /**
   * Main EmailTemplates Instance
   *
   * Ensures only one instance of EmailTemplates is loaded or can be loaded.
   *
   * @static
   * @return EmailTemplates - Main instance
   */
  public static function instance() {
        if (is_null(self::$_instance)) {
          self::$_instance = new self();
        }
        return self::$_instance;
    }

  function on_screen_layout_columns($columns, $screen) {
    if ($screen == $this->pagehook) {
      $columns[$this->pagehook] = 2;
    }
    return $columns;
  }

  //extend the admin menu
  function on_admin_menu() {
    $this->pagehook = add_submenu_page('edit.php?post_type=product', __('Email Templates', ''), __('Email Templates', ''), 'manage_options', 'MM_et_settings', array(&$this, 'on_show_page'), '', 92);
//    $this->pagehook = add_submenu_page('edit.php?post_type=made_to_order', __('Email Templates', ''), __('Email Templates', ''), 'manage_options', 'MM_et_settings', array(&$this, 'on_show_page'), '', 92);
    add_action('load-' . $this->pagehook, array(&$this, 'on_load_page'));
  }

  //will be executed if wordpress core detects this page has to be rendered
  function on_load_page() {
    //ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
    wp_enqueue_script('common');
    wp_enqueue_script('wp-lists');
    wp_enqueue_script('postbox');

    //add several metaboxes now, all metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
    add_meta_box('On Registration', 'On Registration', array(&$this, 'on_user_registration_to_user_email_settings'), $this->pagehook, 'normal', 'core');
    add_meta_box('Return Product', 'Return Product', array(&$this, 'on_user_return_product_email_settings'), $this->pagehook, 'normal', 'core');
    add_meta_box('Coupon', 'Coupon', array(&$this, 'on_user_coupon_generate_email_settings'), $this->pagehook, 'normal', 'core');
    add_meta_box('Remind Cart To User', 'Remind Cart To User', array(&$this, 'on_user_catt_reminder_email_settings'), $this->pagehook, 'normal', 'core');
     add_meta_box('New Customer Coupon', 'New Customer Coupon', array(&$this, 'new_user_coupon_generate_email_settings'), $this->pagehook, 'normal', 'core');
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
      <h2 style='font-family:tahoma;'> Email Templates </h2>
      <form action="admin-post.php" method="post">
        <?php wp_nonce_field('theme-settings-metaboxes'); ?>
        <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
        <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
        <input type="hidden" name="action" value="save_MM_email_template_settings" />
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
      jQuery(document).ready(function($) {
        // close postboxes that should be closed
        jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
        // postboxes setup
        postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
      });
    //]]>
    </script>
    <?php
  }
   
 
  //executed if the post arrives initiated by pressing the submit button of form
  function on_save_changes() {
    //user permission check
    if (!current_user_can('manage_options'))
      wp_die(__('Cheatin&#8217; uh?'));
    //cross check the given referer
    check_admin_referer('theme-settings-metaboxes');
    //process here your on $_POST validation and / or option saving
    $et_data = $_POST['MM_email_template_settings'];
    $et_data = stripslashes_deep($et_data);
    update_option('MM_email_template_settings',serialize($et_data));
    wp_redirect($_POST['_wp_http_referer']);
  }

  function on_user_registration_to_user_email_settings() {
        $MM_email_template_settings = unserialize(get_option('MM_email_template_settings'));
        ?>
        <p class="description">This e-mail will be sent to a new user upon registration. Please be sure to include the variable %user_pass% if using default passwords or else the user will not know their password! If any field is left empty, the default will be used instead.  </p>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">From : </p>
          <input type='text' name="MM_email_template_settings[user_reg_from]" placeholder="From" value="<?php echo $MM_email_template_settings['user_reg_from']; ?>" style="width : 300px; padding: 8px 5px 1px;" />
          <div class='clear'></div>
        </div>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Subject : </p>
          <input type='text' name="MM_email_template_settings[user_reg_subject]" placeholder="Subject" value="<?php echo $MM_email_template_settings['user_reg_subject']; ?>" style="width : 300px; padding: 8px 5px 1px;" />
          <div class='clear'></div>
        </div>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Message : </p>
          <p class="description" style="">Available Variables: %blogname%, %siteurl%, %confirmation_link%, %user_email%, %user_pass%, %first_name%, %last_name% </p>
          <?php wp_editor($MM_email_template_settings['user_reg_message'], 'MM_email_template_settings[user_reg_message]', array('textarea_name' => 'MM_email_template_settings[user_reg_message]', 'media_buttons' => false, 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8)); ?>
          <div class='clear'></div>
        </div>
        <?php
    }
     function on_user_return_product_email_settings() {
        $MM_email_template_settings = unserialize(get_option('MM_email_template_settings'));
        ?>
        <p class="description">This e-mail will be sent to System return product request by customer's.</p>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">To : </p>
          <input type='text' name="MM_email_template_settings[send_mail_to]" placeholder="To" value="<?php echo $MM_email_template_settings['send_mail_to']; ?>" style="width : 300px; padding: 8px 5px 1px;" />
          <div class='clear'></div>
        </div>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Subject : </p>
          <input type='text' name="MM_email_template_settings[user_return_product_subject]" placeholder="Subject" value="<?php echo $MM_email_template_settings['user_return_product_subject']; ?>" style="width : 300px; padding: 8px 5px 1px;" />
          <div class='clear'></div>
        </div>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Message : </p>
          <p class="description" style="">Available Variables: %blogname%, %siteurl%, %user_email%, %order_id%, %product_name%, %first_name%, %last_name% ,%mobile_no%</p>
          <?php wp_editor($MM_email_template_settings['user_return_product_message'], 'MM_email_template_settings[user_return_product_message]', array('textarea_name' => 'MM_email_template_settings[user_return_product_message]', 'media_buttons' => false, 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8)); ?>
          <div class='clear'></div>
        </div>
        <?php
    }
    function on_user_coupon_generate_email_settings() {
        $MM_email_template_settings = unserialize(get_option('MM_email_template_settings'));
        ?>
        <p class="description">This e-mail will be sent to customer's for new Coupon code.</p>
       
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Subject : </p>
          <input type='text' name="MM_email_template_settings[user_coupon_code_subject]" placeholder="Subject" value="<?php echo $MM_email_template_settings['user_coupon_code_subject']; ?>" style="width : 300px; padding: 8px 5px 1px;" />
          <div class='clear'></div>
        </div>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Message : </p>
          <p class="description" style="">Available Variables: %blogname%, %siteurl%, %user_email%, %order_id%, %first_name%, %last_name%,%coupon_code% </p>
          <?php wp_editor($MM_email_template_settings['user_coupon_code_message'], 'MM_email_template_settings[user_coupon_code_message]', array('textarea_name' => 'MM_email_template_settings[user_coupon_code_message]', 'media_buttons' => false, 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8)); ?>
          <div class='clear'></div>
        </div>
        <?php
    }
   
    function on_user_catt_reminder_email_settings() {
        $MM_email_template_settings = unserialize(get_option('MM_email_template_settings'));
        ?>
        <p class="description">This e-mail will be sent to customer's for pending cart details.remind them to after following days. </p>
       
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">How many Days to remind user's : </p>
          <input type='text' name="MM_email_template_settings[user_how_many_day_to_remind]" placeholder="How many Days" value="<?php echo $MM_email_template_settings['user_how_many_day_to_remind']; ?>" style="width : 300px; padding: 8px 5px 1px;" />
          <div class='clear'></div>
        </div>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Subject : </p>
          <input type='text' name="MM_email_template_settings[user_cart_reminder_subject]" placeholder="Subject" value="<?php echo $MM_email_template_settings['user_cart_reminder_subject']; ?>" style="width : 300px; padding: 8px 5px 1px;" />
          <div class='clear'></div>
        </div>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Message : </p>
          <p class="description" style="">Available Variables: %blogname%, %siteurl%, %user_email%, %quantity%, %first_name%, %last_name%,%product_name%,%cart_link% </p>
          <?php wp_editor($MM_email_template_settings['user_cart_reminder_message'], 'MM_email_template_settings[user_cart_reminder_message]', array('textarea_name' => 'MM_email_template_settings[user_cart_reminder_message]', 'media_buttons' => false, 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8)); ?>
          <div class='clear'></div>
        </div>
        <?php
    }
     function new_user_coupon_generate_email_settings() {
        $MM_email_template_settings = unserialize(get_option('MM_email_template_settings'));
        ?>
        <p class="description">This e-mail will be sent to New customer's 100rs Coupon code.</p>
       
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Subject : </p>
          <input type='text' name="MM_email_template_settings[new_user_coupon_code_subject]" placeholder="Subject" value="<?php echo $MM_email_template_settings['new_user_coupon_code_subject']; ?>" style="width : 300px; padding: 8px 5px 1px;" />
          <div class='clear'></div>
        </div>
        <div class='general_setting_sections'>
          <p style="color: #222222; font-weight: 600; line-height: 1; text-shadow: none; font-size: 14px;">Message : </p>
          <p class="description" style="">Available Variables: %blogname%, %siteurl%, %user_email%, %first_name%, %last_name%,%coupon_code% </p>
          <?php wp_editor($MM_email_template_settings['new_user_coupon_code_message'], 'MM_email_template_settings[new_user_coupon_code_message]', array('textarea_name' => 'MM_email_template_settings[new_user_coupon_code_message]', 'media_buttons' => false, 'editor_class' => 'requiredField', 'teeny' => false, 'textarea_rows' => 8)); ?>
          <div class='clear'></div>
        </div>
        <?php
    }

    public function set_content_type( $content_type ){
      return 'text/html';
    }
    
    public function load_email_templates($template_name, $message, $email_heading) {
        ob_start();
        $message = $message;
        $email_heading = $email_heading;
        include( 'email-templates/'.$template_name.'.php' );
        $template = ob_get_clean();
        return $template;
    }

    // User Registration (to User) Email Template - Function
    public function on_user_reg_to_user_et($user, $referer_link) {
    		global  $MM_email_template_settings;
        
                $site_title = get_bloginfo('name');
                $result = $MM_email_template_settings;
                $user_reg_from = $result['user_reg_from'];
                $admin_email = get_option('admin_email');
                $user_reg_subject = stripcslashes($result['user_reg_subject']);
                $user_reg_subject = $user_reg_subject;
                $user_reg_message = stripcslashes($result['user_reg_message']);
                $user_info = get_user_by('id', $user);
        
    		$user_email = trim($user_info->user_email);
        $message = $this->replace_tags($user_reg_message, $user,'', $referer_link);
        
        $email_heading = 'Registration';
        $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
       
    		$headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
//                    echo $user_email;  print_r($htmlmessage);exit;
    		$send_mail = wp_mail($user_email, $user_reg_subject, $htmlmessage, $headers);
                
       	return $send_mail;
    }
    // edit user to user mail templete.
    public function on_user_editreg_to_user_et($user, $referer_link) {
    		global $user_email, $MM_email_template_settings;
        
                $site_title = get_bloginfo('name');
                $result = $MM_email_template_settings;
                $user_reg_from = $result['user_reg_from'];
                $admin_email = get_option('admin_email');
                $user_reg_subject = stripcslashes($result['user_reg_subject']);
                $user_reg_subject = $user_reg_subject;
                $user_reg_message = stripcslashes($result['user_reg_message']);
                $user_info = get_user_by('id', $user);
        
    		$user_email = trim($user_info->user_email);
        $message = $this->replace_tags($user_reg_message, $user,'', $referer_link);
        $email_heading = 'Change Profile Mail Id';
        $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
    		$headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
    		$send_mail = wp_mail($user_email, $user_reg_subject, $htmlmessage, $headers);
       	return $send_mail;
    }
    
    
    public function on_user_reg_to_admin_et($first_name, $last_name, $user_email) {
        $site_title = get_bloginfo('name');
        $admin_email = get_option('admin_email');

        $subject = 'New User Registration';
               
        $message = '<p>User Registration Details</p>'; 
        $message .= '<p>First Name: '.$first_name.'</p>';
        $message .= '<p>Last Name: '.$first_name.'</p>';
        $message .= '<p>Email ID: '.$user_email.'</p>';

        $email_heading = 'New User Registration On Your Site';
        $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
        $headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
        $send_mail = wp_mail($admin_email, $subject, $htmlmessage, $headers );
        return $send_mail;
    }
    
    //Edit profile mail-templete to admin

     public function on_user_editreg_to_admin_et($first_name, $last_name, $user_email) {
        $site_title = get_bloginfo('name');
        $admin_email = get_option('admin_email');

        $subject = 'User profile Updated';
               
        $message = '<p>User Registration Details</p>'; 
        $message .= '<p>First Name: '.$first_name.'</p>';
        $message .= '<p>Last Name: '.$first_name.'</p>';
        $message .= '<p>Email ID: '.$user_email.'</p>';

        $email_heading = 'User profile Updated On Your Site';
        $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
        $headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
        $send_mail = wp_mail($admin_email, $subject, $htmlmessage, $headers );
        return $send_mail;
    }
    public function on_return_product_et($user_id, $product_id, $order_id) {
    		global  $MM_email_template_settings;
                $site_title = get_bloginfo('name');
                $result = $MM_email_template_settings;
                 $user_return_product = $result['send_mail_to'];
                 $admin_email = get_option('admin_email');
                 $user_reg_subject = stripcslashes($result['user_return_product_subject']);
//                $user_reg_subject = $user_reg_subject;
                $user_reg_message = stripcslashes($result['user_return_product_message']);
                $message = $this->replace_return_product_tags($user_reg_message, $product_id,$user_id, $order_id);
        $email_heading = 'Request For Return Product';
        $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
//        print_r($htmlmessage); exit;
    		$headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
    		$send_mail = wp_mail($user_return_product, $user_reg_subject, $htmlmessage, $headers);
               
       	return $send_mail;
    }
    public function on_coupon_code_et($user_id, $coupon_code, $order_id) {
    		global  $MM_email_template_settings;
                $site_title = get_bloginfo('name');
                $result = $MM_email_template_settings;
                $admin_email = get_option('admin_email');
                $user_reg_subject = stripcslashes($result['user_coupon_code_subject']);
                $user_info = get_user_by('id', $user_id);
          	$user_email = trim($user_info->user_email);
                $user_reg_message = stripcslashes($result['user_coupon_code_message']);
                $message = $this->replace_coupon_code_tags($user_reg_message,$user_id, $order_id,$coupon_code);
                $email_heading = 'Coupon Has been generated';
                $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
//                print_r($htmlmessage); exit;
                $headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
    		$send_mail = wp_mail($user_email, $user_reg_subject, $htmlmessage, $headers);
               
       	return $send_mail;
    }
      public function sent_register_user_coupon_code($user_id,$customer_coupon_code) {
    		global  $MM_email_template_settings;
                $site_title = get_bloginfo('name');
                $result = $MM_email_template_settings;
                $admin_email = get_option('admin_email');
                $user_reg_subject = stripcslashes($result['new_user_coupon_code_subject']);
                $user_info = get_user_by('id', $user_id);
          	$user_email = trim($user_info->user_email);
                $user_reg_message = stripcslashes($result['new_user_coupon_code_message']);
                $message = $this->replace_new_user_coupon_code_tags($user_reg_message,$user_id,$customer_coupon_code);
                $email_heading = 'Coupon For New Customer';
                $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
//                print_r($htmlmessage); exit;
                $headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
    		$send_mail = wp_mail($user_email, $user_reg_subject, $htmlmessage, $headers);
               
       	return $send_mail;
    }
    public function replace_new_user_coupon_code_tags($user_reg_message,$user_id,$customer_coupon_code) {
    		global $MM_email_template_settings;
//                $mobile = get_user_meta( $user_id, 'billing_phone', true );
//                $product_name =get_the_title($product_id);
    		$result = $MM_email_template_settings;
    		$user_info = get_user_by('id', $user_id);
    		$user_email = $user_info->user_email; 
    		$user_name = $user_info->user_login;
    		$first_name = $user_info->first_name;
                $last_name  = $user_info->last_name;
    		$blogname = get_bloginfo('name');
    		$site_url = get_bloginfo('siteurl');
                
        $user_reg_message = str_replace("%blogname%", $blogname, $user_reg_message);
        $user_message = str_replace("%siteurl%", $site_url, $user_reg_message);
        $user_message = str_replace("%user_email%", $user_email, $user_message);
        $user_message = str_replace("%first_name%", $first_name, $user_message);
        $user_message = str_replace("%last_name%", $last_name, $user_message);
        $user_message = str_replace("%coupon_code%", $customer_coupon_code, $user_message);
//      $user_message = str_replace("%product_name%", $product_name, $user_message);
//        $user_message = str_replace("%order_id%", $order_id, $user_message);
        $user_message = apply_filters( 'the_content', $user_message );
        
        return $user_message;
    }
    public function create_made_to_order_et($user_email, $made_to_order_id){
        $site_title = get_bloginfo('name');
//                $admin_email = get_option('admin_email');
//        echo "hello"; exit;
        $group_emails = array(
                    'chirag.mehta@jewelbuff.com',
                    'customercare@jewelbuff.com'
                );
                $subject = "New order has been created";
                $email_heading = 'Customers New Order';
                $message ='<p>Welcome to '.$site_title.'.</p>';
                $link = site_url().'/wp-admin/post.php?post='.$made_to_order_id.'&action=edit';
                $message .='<p>New Order ! please click to '.$link.' view order details</p>';
                $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
                $headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
//                print_r($htmlmessage); exit;
    		
               foreach($group_emails as $admin_email)
                {
                  $send_mail = wp_mail($admin_email, $subject, $htmlmessage, $headers);
                }
       	return $send_mail;
    }
    public function made_to_order_update_et($email_address, $subject, $message){
                $site_title = get_bloginfo('name');
                $admin_email = get_option('admin_email');
                $email_heading = 'Your Order';
                $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
                $headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
//                print_r($htmlmessage); exit;
    		$send_mail = wp_mail($email_address, $subject, $htmlmessage, $headers);
               
       	return $send_mail;
    }
    public function on_user_cart_reminder_et($user_id, $product_id, $quantity) {
    		global  $MM_email_template_settings;
                $site_title = get_bloginfo('name');
                $result = $MM_email_template_settings;
                $admin_email = get_option('admin_email');
                $user_reg_subject = stripcslashes($result['user_cart_reminder_subject']);
                $user_info = get_user_by('id', $user_id);
          	$user_email = trim($user_info->user_email);
                $user_reg_message = stripcslashes($result['user_cart_reminder_message']);
//                print_r($user_reg_message); //exit();
                $message = $this->replace_user_cart_reminder_tags($user_reg_message,$user_id,$product_id);
                $email_heading = 'Purchase the product';
                $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
//                print_r($htmlmessage); 
                $headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
    		$send_mail = wp_mail($user_email, $user_reg_subject, $htmlmessage, $headers);
               
       	return $send_mail;
    }
    public function user_birthday_et($user_id) {
                
    		$GLOBALS['admin_email_templete'] = unserialize(get_option('global_admin_email_templete'));
                $site_title = get_bloginfo('name');
                $result = $GLOBALS['admin_email_templete'];
                $admin_email = get_option('admin_email');
                $user_reg_subject = stripcslashes($result['birthday_subject']);
                $user_info = get_user_by('id', $user_id);
          	$user_email = trim($user_info->user_email);
                $user_reg_message = stripcslashes($result['birthday_message']);
                $message = $this->replace_birthday_tags($user_reg_message,$user_id);
                $email_heading = 'Happy Birth Day Advance!!';
                $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
                $headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
    		$send_mail = wp_mail($user_email, $user_reg_subject, $htmlmessage, $headers);
               
       	return $send_mail;
    }
    public function user_anniversary_et($user_id) {
    		$GLOBALS['admin_email_templete'] = unserialize(get_option('global_admin_email_templete'));
                $site_title = get_bloginfo('name');
                $result = $GLOBALS['admin_email_templete'];
                $admin_email = get_option('admin_email');
                $user_reg_subject = stripcslashes($result['anniversary_subject']);
                $user_info = get_user_by('id', $user_id);
          	$user_email = trim($user_info->user_email);
                $user_reg_message = stripcslashes($result['anniversary_message']);
//                print_r($user_reg_message); exit();
                $message = $this->replace_user_anniversary_tags($user_reg_message,$user_id);
                $email_heading = 'Happy Anniversary!!';
                $htmlmessage = $this->load_email_templates('email-template', $message, $email_heading);
//                print_r($htmlmessage);                exit();
                $headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
    		$send_mail = wp_mail($user_email, $user_reg_subject, $htmlmessage, $headers);
               
       	return $send_mail;
    }
     public function replace_birthday_tags($user_reg_message,$user_id) {
    		
    		$user_info = get_user_by('id', $user_id);
    		$user_email = $user_info->user_email; 
    		$user_name = $user_info->user_login;
    		$first_name = $user_info->first_name;
                $last_name  = $user_info->last_name;
    		$blogname = get_bloginfo('name');
    		$site_url = get_bloginfo('siteurl');
               
        $user_reg_message = str_replace("%blogname%", $blogname, $user_reg_message);
        $user_message = str_replace("%siteurl%", $site_url, $user_reg_message);
        $user_message = str_replace("%user_email%", $user_email, $user_message);
        $user_message = str_replace("%first_name%", $first_name, $user_message);
        $user_message = str_replace("%last_name%", $last_name, $user_message);
        $user_message = str_replace("%user_name%", $user_name, $user_message);
      
//        $user_message = str_replace("%order_id%", $order_id, $user_message);
        $user_message = apply_filters( 'the_content', $user_message );
        
        return $user_message;
    }
    public function replace_user_anniversary_tags($user_reg_message,$user_id) {
    		
    		$user_info = get_user_by('id', $user_id);
    		$user_email = $user_info->user_email; 
    		$user_name = $user_info->user_login;
    		$first_name = $user_info->first_name;
                $last_name  = $user_info->last_name;
    		$blogname = get_bloginfo('name');
    		$site_url = get_bloginfo('siteurl');
               
        $user_reg_message = str_replace("%blogname%", $blogname, $user_reg_message);
        $user_message = str_replace("%siteurl%", $site_url, $user_reg_message);
        $user_message = str_replace("%user_email%", $user_email, $user_message);
        $user_message = str_replace("%first_name%", $first_name, $user_message);
        $user_message = str_replace("%last_name%", $last_name, $user_message);
        $user_message = str_replace("%user_name%", $user_name, $user_message);
//        $user_message = str_replace("%order_id%", $order_id, $user_message);
        $user_message = apply_filters( 'the_content', $user_message );
        
        return $user_message;
    }
    public function replace_user_cart_reminder_tags($user_reg_message,$user_id,$product_id) {
    		global $MM_email_template_settings;
//                $mobile = get_user_meta( $user_id, 'billing_phone', true );
                $product_name =get_the_title($product_id);
    		$result = $MM_email_template_settings;
    		$user_info = get_user_by('id', $user_id);
    		$user_email = $user_info->user_email; 
    		$user_name = $user_info->user_login;
    		$first_name = $user_info->first_name;
                $last_name  = $user_info->last_name;
    		$blogname = get_bloginfo('name');
    		$site_url = get_bloginfo('siteurl');
                $cart_link = home_url().'/cart/';
        $user_reg_message = str_replace("%blogname%", $blogname, $user_reg_message);
        $user_message = str_replace("%siteurl%", $site_url, $user_reg_message);
        $user_message = str_replace("%user_email%", $user_email, $user_message);
        $user_message = str_replace("%first_name%", $first_name, $user_message);
        $user_message = str_replace("%last_name%", $last_name, $user_message);
        $user_message = str_replace("%cart_link%", $cart_link, $user_message);
      $user_message = str_replace("%product_name%", $product_name, $user_message);
//        $user_message = str_replace("%order_id%", $order_id, $user_message);
        $user_message = apply_filters( 'the_content', $user_message );
        
        return $user_message;
    }
    public function replace_coupon_code_tags($user_reg_message,$user_id, $order_id,$coupon_code) {
    		global $MM_email_template_settings;
//                $mobile = get_user_meta( $user_id, 'billing_phone', true );
//                $product_name =get_the_title($product_id);
    		$result = $MM_email_template_settings;
    		$user_info = get_user_by('id', $user_id);
    		$user_email = $user_info->user_email; 
    		$user_name = $user_info->user_login;
    		$first_name = $user_info->first_name;
                $last_name  = $user_info->last_name;
    		$blogname = get_bloginfo('name');
    		$site_url = get_bloginfo('siteurl');
                
        $user_reg_message = str_replace("%blogname%", $blogname, $user_reg_message);
        $user_message = str_replace("%siteurl%", $site_url, $user_reg_message);
        $user_message = str_replace("%user_email%", $user_email, $user_message);
        $user_message = str_replace("%first_name%", $first_name, $user_message);
        $user_message = str_replace("%last_name%", $last_name, $user_message);
        $user_message = str_replace("%coupon_code%", $coupon_code, $user_message);
//      $user_message = str_replace("%product_name%", $product_name, $user_message);
        $user_message = str_replace("%order_id%", $order_id, $user_message);
        $user_message = apply_filters( 'the_content', $user_message );
        
        return $user_message;
    }
    public function replace_return_product_tags($user_reg_message, $product_id,$user_id, $order_id) {
    		global $MM_email_template_settings;
                $mobile = get_user_meta( $user_id, 'billing_phone', true );
                $product_name =get_the_title($product_id);
    		$result = $MM_email_template_settings;
    		$user_info = get_user_by('id', $user_id);
    		$user_email = $user_info->user_email; 
    		$user_name = $user_info->user_login;
    		$first_name = $user_info->first_name;
                $last_name  = $user_info->last_name;
    		$blogname = get_bloginfo('name');
    		$site_url = get_bloginfo('siteurl');
                
        $user_reg_message = str_replace("%blogname%", $blogname, $user_reg_message);
        $user_message = str_replace("%siteurl%", $site_url, $user_reg_message);
        $user_message = str_replace("%user_email%", $user_email, $user_message);
        $user_message = str_replace("%first_name%", $first_name, $user_message);
        $user_message = str_replace("%last_name%", $last_name, $user_message);
        $user_message = str_replace("%mobile_no%", $mobile, $user_message);
        $user_message = str_replace("%product_name%", $product_name, $user_message);
        $user_message = str_replace("%order_id%", $order_id, $user_message);
        $user_message = apply_filters( 'the_content', $user_message );
        
        return $user_message;
    }
    public function replace_tags($message, $user, $user_pass, $referer_link) {
    		global $MM_email_template_settings;
        
    		$result = $MM_email_template_settings;
    		$user_info = get_user_by('id', $user);
    		$user_email = $user_info->user_email; 
    		$user_name = $user_info->user_login;
    		$first_name = $user_info->first_name;
                $last_name  = $user_info->last_name;
    		$blogname = get_bloginfo('name');
    		$site_url = get_bloginfo('siteurl');
        $activation_key = get_user_meta($user, 'activation_key', true);
        if($activation_key!="") {
            $confirmation_link = $referer_link."/merchant-dashboard/?activation_code=".$activation_key."&id=".$user;
        }
        $customer_verification = get_user_meta($user, 'customer_verification', true);
        if($customer_verification!="") {
            $confirmation_link = $referer_link."/?customer_verification=".$customer_verification."&id=".$user;
        }
        
        $user_reg_message = str_replace("%blogname%", $blogname, $message);
        $user_message = str_replace("%siteurl%", $site_url, $user_reg_message);
        $user_message = str_replace("%confirmation_link%", $confirmation_link, $user_message);

        $user_message = str_replace("%user_email%", $user_email, $user_message);
        $user_message = str_replace("%user_pass%", $user_pass, $user_message);
        $user_message = str_replace("%first_name%", $first_name, $user_message);
        $user_message = str_replace("%last_name%", $last_name, $user_message);
        $user_message = apply_filters( 'the_content', $user_message );
        
        return $user_message;
    }
    
    public function replace_subject_tags($message, $user) {
    		global $MM_email_template_settings;
    		$result = $MM_email_template_settings;
    		$user_info = get_user_by('id', $user);
    		$user_email = $user_info->user_email; 
    		$user_name = $user_info->user_login;
    		$first_name = $user_info->first_name;
        $last_name  = $user_info->last_name;
    		$blogname = get_bloginfo('name');
    		$site_url = get_bloginfo('siteurl');
                                 
    		$user_reg_message = str_replace("%blogname%", $blogname, $message);
    		$user_message = str_replace("%siteurl%", $site_url, $user_reg_message);
    		$user_message = str_replace("%user_email%", $user_email, $user_message);
        $user_message = str_replace("%first_name%", $first_name, $user_message);
        $user_message = str_replace("%last_name%", $last_name, $user_message);
        return $user_message;
    }
        
       

  }

  $et_object = new EmailTemplates();
  $et_object->load_settings_page();
 
  function MM_EMAILTEMPLATES() {
    return EmailTemplates::instance();
  }

// Global for backwards compatibility.
  $GLOBALS['MM_EMAILTEMPLATES'] = MM_EMAILTEMPLATES();
  $GLOBALS['MM_email_template_settings'] = unserialize(get_option('MM_email_template_settings'));
  