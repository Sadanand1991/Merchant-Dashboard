<?php
/**
* Admin Class
*/

class MM_Admin {
	
	function __construct() {
		add_action('admin_print_scripts', array(&$this,'register_scripts'));
		add_action('admin_print_styles', array(&$this,'register_styles'));
		add_action('edit_user_profile_update', array(&$this,'save_mail_field'));
		add_action('user_register', array(&$this,'save_mail_field'));
		add_action('edit_user_profile', array(&$this, 'send_mail_fields'));
                add_action( 'restrict_manage_users', array(&$this,'add_verified_user' ));
                add_filter( 'pre_get_users', array(&$this,'filter_users_by_user_verification' ));
//		add_action('user_new_form', array(&$this, 'new_user_shop_field'));
//		add_action('wp_ajax_send_notification_email_to_user', array($this, 'send_notification_email_to_user'));	
//		add_filter('manage_users_columns', array(&$this, 'shop_column_to_user_table' ));
//		add_filter('manage_users_custom_column', array(&$this, 'shop_column_user_table_row'), 10, 3 );
	}

	function register_scripts() {
		wp_enqueue_script('backbone');
		wp_enqueue_script('underscore');
		wp_enqueue_media();
		
//		wp_register_script('mm-admin-gm', 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places', '', '1.0', true);
//		wp_enqueue_script('mm-admin-gm');
		wp_register_script('mm-admin-script', plugins_url('assets/js/admin/admin_script.js', __FILE__), array('jquery','media-upload','thickbox'), '1.0', true);
		wp_enqueue_script('mm-admin-script');
	}
	
	function register_styles() {
		wp_enqueue_style('thickbox');
		wp_enqueue_style('mm-admin-style', plugins_url('assets/js/admin/admin_style.css', __FILE__));
	}


    

    public function send_mail_fields($user) {
        if($user->roles[0] == 'merchant'){?>
        <table class="form-table">
            <tr>
                <th scope="row" style="padding: 15px;"><lable for="user_phone">Phone No:. </lable></th>
                <td style="padding: 15px;">
                    <input type="text" name="user_phone" id="phone" class="regular-text" value="<?php echo esc_attr( get_the_author_meta( 'user_phone', $user->ID ) ); ?>" />
                </td>    
            </tr>
            <tr>
                <th scope="row" style="padding: 15px;"><lable for="user_shop">Shop Name:. </lable></th>
                <td style="padding: 15px;">
                    <input type="text" name="user_shop" id="shop_name" class="regular-text" value="<?php echo esc_attr( get_the_author_meta( 'user_shop', $user->ID ) ); ?>" />
                </td>    
            </tr>
            <tr>
            <th><label for="user_pan_vat_tan"><?php _e( 'User Pan Vat Tan', 'textdomain' ); ?></label></th>
            <?php $image = wp_get_attachment_metadata( get_the_author_meta( 'user_pan_vat_tan', $user->ID )); 
            if(!empty($image[file])){?>
            <td>
                <img src="<?php echo site_url(); ?>/wp-content/uploads/<?php echo $image[file]; ?>" style="width:150px;"><br />
            </td>
            <?php }else { ?>
            <td>
                <input type="file" name="upload_pan_vat_tan" id="upload_pan_vat_tan"   class="upload_pan_vat_tan" />  
            </td>
            <?php } ?>
        </tr>
        <tr>
            <th><label for="user_address_proof"><?php _e( 'User Address Proof', 'textdomain' ); ?></label></th>
            <?php $image = wp_get_attachment_metadata( get_the_author_meta( 'user_address_proof', $user->ID )); 
            if(!empty($image[file])){?>
            <td>
                <img src="<?php echo site_url(); ?>/wp-content/uploads/<?php echo $image[file]; ?>" style="width:150px;"><br />
            </td>
            <?php }else { ?>
            <td>
                <input type="file" name="upload_address_proof" id="upload_address_proof"   class="upload_address_proof" />  
            </td>
            <?php }?>
        </tr>
        <tr>
            <th><label for="user_id_proof"><?php _e( 'User ID Proof', 'textdomain' ); ?></label></th>
            <?php $image = wp_get_attachment_metadata( get_the_author_meta( 'user_id_proof', $user->ID )); 
            if(!empty($image[file])){?>
                <td>
                    <img src="<?php echo site_url(); ?>/wp-content/uploads/<?php echo $image[file]; ?>" style="width:150px;"><br />
                </td>
            <?php }else { ?>
                <td>
                   <input type="file" name="upload_id_proof" id="upload_id_proof"   class="upload_id_proof" />  
                </td>
            <?php } ?>
        </tr>
        <tr>
            <th><label for="user_cheque"><?php _e( 'User Cancelled Cheque', 'textdomain' ); ?></label></th>
            <?php $image = wp_get_attachment_metadata( get_the_author_meta( 'user_cheque', $user->ID )); 
            if(!empty($image[file])){ ?>
                <td>
                    <img src="<?php echo site_url(); ?>/wp-content/uploads/<?php echo $image[file]; ?>" style="width:150px;"><br />
                </td>
            <?php }else { ?>
                <td>
                   <input type="file" name="upload_cheque" id="upload_cheque"   class="upload_cheque" />  
                </td>
            <?php } ?>    
        </tr>
       
        <tr>
            <th><label for="upload_scancopy"><?php _e( 'User Scan Copy', 'textdomain' ); ?></label></th>
            <?php $image_scan = wp_get_attachment_metadata( get_the_author_meta( 'user_scancopy', $user->ID )); 
            if(!empty($image_scan[file])){?>
                <td>
                    <img src="<?php echo site_url(); ?>/wp-content/uploads/<?php echo $image_scan[file]; ?>" style="width:150px;"><br />
                </td>
            <?php }else { ?>
                <td>
                   <input type="file" name="upload_scancopy" id="upload_scancopy"   class="upload_scancopy" />  
                </td>
            <?php } ?>    
        </tr>
        
        <tr>
            <th><label for="user_verified"><?php _e( 'Is Verified', 'textdomain' ); ?></label></th>
            <td>
                <?php $user = get_user_meta($user->ID, 'user_verified', true ); 
                $select='';
                if($user == 'verified'){ $select='checked'; }?>
                
                <input type="checkbox" name="user_verified" <?php echo $select; ?>><label for="user_verified"><?php _e( 'User Verified', 'textdomain' ); ?></label>                                              
            </td>
        </tr>	
        </table>
    <?php } 
     }
//Save new field for user in users_meta table
    
    

   public function save_mail_field($user_id) {
       
       require_once( ABSPATH . 'wp-admin/includes/image.php' );

        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        
//        print_r($_POST['upload_address_proof']); exit;
        if (isset($_POST['upload_pan_vat_tan']) && $_POST['upload_pan_vat_tan'] != '') {
            $upload_pan_vat_tan = media_handle_upload( 'upload_pan_vat_tan', $_POST['upload_pan_vat_tan'] );
           
            update_usermeta($user_id, 'user_pan_vat_tan', $upload_pan_vat_tan);
            
        }
        if (isset($_POST['upload_address_proof']) && $_POST['upload_address_proof'] != '') {
            $upload_address_proof = media_handle_upload( 'upload_address_proof', $_POST['upload_address_proof'] );
            update_usermeta($user_id, 'user_address_proof', $upload_address_proof);
            
        }
        if (isset($_POST['upload_id_proof']) && $_POST['upload_id_proof'] != '') {
            $upload_id_proof = media_handle_upload( 'upload_id_proof', $_POST['upload_id_proof'] );
            update_usermeta($user_id, 'user_id_proof', $upload_id_proof);
        }
        if (isset($_POST['upload_cheque']) && $_POST['upload_cheque'] != '') {
            $upload_cheque = media_handle_upload( 'upload_cheque', $_POST['upload_cheque'] ); 
            update_usermeta($user_id, 'user_cheque', $upload_cheque);
        }    
        if (isset($_POST['upload_scancopy']) && $_POST['upload_scancopy'] != '') {
            $upload_scancopy = media_handle_upload( 'upload_scancopy', $_POST['upload_scancopy'] );
            update_usermeta($user_id, 'user_scancopy', $upload_scancopy);
//            print_r($_FILES['upload_address_proof']); exit;
                        
        }
        
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        if (isset($_POST['user_phone']) && $_POST['user_phone'] != '') {
            update_usermeta($user_id, 'user_phone', $_POST['user_phone']);
           
        }
        if (isset($_POST['user_shop']) && $_POST['user_shop'] != '') {
            update_usermeta($user_id, 'user_shop', $_POST['user_shop']);
           
        }
        if (isset($_POST['user_verified']) && $_POST['user_verified'] != '') {
            update_usermeta($user_id, 'user_verified', 'verified');
            delete_user_meta( $user_id, 'activation_key');
           
        }
//        update_usermeta( $user_id, 'user_pan_vat_tan', $_POST['user_pan_vat_tan'] );
//        if (isset($_POST['mail_message']) && $_POST['mail_message'] != '') {
//            update_usermeta($user_id, 'mail_message', $_POST['mail_message']);
//           
//        }
//       	update_usermeta($user_id, 'user_shop', $_POST['user_shop']);
//       	$city = get_post_meta($_POST['user_shop'],'city', true);
//       	$mall = get_post_meta($_POST['user_shop'],'mall', true);
//       	update_usermeta($user_id,'user_mall', $mall);
//       	update_usermeta($user_id,'user_city', $city);
    }
    function add_verified_user()
        {
        if ( isset( $_GET[ 'user_verification' ]) ) {
                $section = $_GET[ 'user_verification' ];
              //  $section = !empty( $section[ 0 ] ) ? $section[ 0 ] : $section[ 1 ];
                if($section[0] == 'verified'){
                    $verified = "selected" ;
                }
                else if($section[0] == 'non-verified'){
                   $non_verified ="selected"; 
                }
            } 
        ?>
        <select name="user_verification[]"  style="float: none;" >
            <option value=""><?php _e('Filter By Verification', 'Jewelbuff'); ?></option>
            <option value="verified" <?php echo $verified; ?> >Verified Users</option>
            <option value="non-verified" <?php echo $non_verified; ?> >Non-Verified Users</option>
         </select> 
         <input type="submit" class="button" value="Filter">
        <?php  
        }
        
    function filter_users_by_user_verification( $query ) {
        global $pagenow;

        if ( is_admin() && 'users.php' == $pagenow && isset($_GET[ 'user_verification' ])) {
            $section = $_GET[ 'user_verification' ];
             $section = !empty( $section[ 0 ] ) ? $section[ 0 ] : $section[ 1 ];
            if ( $section != "" ) {
                $meta_query = array(
                    array(
                        'key' => 'user_verified',
                        'value' => $section
                    )
                );
                $query->set( 'meta_key', 'user_verified' );
                $query->set( 'meta_query', $meta_query );
            }
        }
    }
//    public function send_notification_email_to_user(){
//    	global $MM_EMAILTEMPLATES;
//    	$success = '';
//    	$user_id = $_POST['user_id'];
//    	$mail_message = stripcslashes($_POST['message']);
//    	$subject = $_POST['subject'];
//    	$user_info = get_user_by('id', $user_id);
//        $user_email = trim($user_info->user_email);
//        $site_title = get_bloginfo('name');
//        $admin_email = get_option('admin_email');
//        $message = $this->replace_mail_tags($mail_message, $user_id);
//        $email_heading = 'Thank You For Registration';
//        $htmlmessage = $MM_EMAILTEMPLATES->load_email_templates('email-template', $message, $email_heading);
//    	$headers = 'From: '.$site_title.' <'.$admin_email.'>' . "\r\n";
//    	if(wp_mail($user_email, $subject, $htmlmessage, $headers)){
//    		$success = 'true';
//    	}
//    	else{
//    		$success = 'false';	
//    	}
//    	echo json_encode(array('success'=>$success));
//		die(0);
//    }

//    public function replace_mail_tags($mail_message, $user_id){
//    	$global_theme_settings = unserialize(get_option('global_theme_settings'));
//    	$user_info = get_user_by('id', $user_id);
//    	$user_email = $user_info->user_email; 
//    	$user_name = $user_info->user_login;
//    	$first_name = $user_info->first_name;
//        $last_name  = $user_info->last_name;
//    	$blogname = get_bloginfo('name');
//    	$site_url = get_bloginfo('siteurl');
//    	$shop_id = get_user_meta($user_id, 'user_shop', true);
//    	$shop_name = get_the_title($shop_id);
//        $confirmation_link = get_permalink($global_theme_settings['merchantdashboard']);
//        $user_message = str_replace("%blogname%", $blogname, $mail_message);
//    	$user_message = str_replace("%siteurl%", $site_url, $user_message);
//    	$user_message = str_replace("%merchant_dashboard_link%", $confirmation_link, $user_message);
//        $user_message = str_replace("%user_email%", $user_email, $user_message);
//        $user_message = str_replace("%first_name%", $first_name, $user_message);
//        $user_message = str_replace("%shop_name%", $shop_name, $user_message);
//        $user_message = str_replace("%last_name%", $last_name, $user_message);
//        $user_message = apply_filters( 'the_content', $user_message );
//        return $user_message;
//    }
//    public function shop_column_to_user_table($column) {
//	    $column['shopname'] = 'Shop';
//
//	    return $column;
//	}

//   public function shop_column_user_table_row($value, $column_name, $user_id) {
//	    
//	    $user = get_userdata( $user_id );
//	    if($column_name == 'shopname') 
//	    {    
//	        $shop_id = get_user_meta($user_id, 'user_shop', true);
//	        $shop_name = get_the_title($shop_id); 
//	        $value = $shop_name;
//	    } 
//	    return $value;      
//	}


}

new MM_Admin();
