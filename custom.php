<?php
/**
 * Custom functions
 */

 
function change_wp_logo() {
global $theme_settings;
if(!empty($theme_settings)) {
  $site_logo = $theme_settings['site_logo'];
}
echo '
<style type="text/css">
.login h1 a { 
	background-image: url(' .$site_logo. ') !important; 
	background-position: 0 0;
	background-size:auto auto;
	height:42px;
	width: 190px;
}
</style>
';
}
add_action('login_head', 'change_wp_logo');

//Register CPT for Malls
//$mall_arg = array(true, true, true, true, true, true, 'dashicons-networking', null, array('title', 'editor', 'thumbnail'), true, '', true);
//$mall    = new MM_Register_Custom_CPT( 'Mall', $mall_arg );

//Register CPT for Shops
$shop_arg = array(true, true, true, true, true, true, 'dashicons-welcome-widgets-menus', null, array('title', 'editor', 'thumbnail'), true, 'shop', false);
$shop    = new MM_Register_Custom_CPT( 'Shop', $shop_arg );

//Register CPT for Products
//$product_arg = array(true, true, true, true, true, true, 'dashicons-cart', null, array('title', 'editor', 'thumbnail'), true, 'product', true);
//$product = new MM_Register_Custom_CPT( 'Product', $product_arg );

//Register CPT for Cities
//$city_arg = array(false, false, true, true, false, true, 'dashicons-location', null, array('title'));
//$city    = new MM_Register_Custom_CPT( 'City', $city_arg );

//Register CPT for Coupon
$coupons_arg = array(false, false, true, true, false, true, 'dashicons-tag', null, array('title'));
$coupons    = new MM_Register_Custom_CPT( 'Coupon', $coupons_arg );

//Register CPT for Orders
$orders_arg = array(false, false, true, true, false, true, 'dashicons-tag', null, array('title'));
$merchant_orders    = new MM_Register_Custom_CPT( 'Merchant Orders', $orders_arg );

//Register CPT for Home Page Slider
//$home_slider_arg = array(false, false, true, true, false, true, 'dashicons-feedback', null, array('title', 'thumbnail'));
//$home_slider    = new MM_Register_Custom_CPT( 'Home Slide', $home_slider_arg );



function remove_core_updates(){
	global $wp_version;return(object) array('last_checked'=> time(),'version_checked'=> $wp_version,);
}
add_filter('pre_site_transient_update_core','remove_core_updates');
add_filter('pre_site_transient_update_plugins','remove_core_updates');
add_filter('pre_site_transient_update_themes','remove_core_updates');


///////////////  function for merchant contact entries     /////////////////////////////////////////////////////////

add_action( 'admin_menu' , 'merchant_contact_list' );
function merchant_contact_list() {
    add_menu_page( 'Requests For Merchant Account', 'Requests For Merchant Account', 'nf_sub', 'edit.php?post_status=all&post_type=nf_sub&action=-1&m=0&form_id=2&begin_date&end_date&paged=1&mode=list&action2=-1', '', 'dashicons-groups', 10 );
} 

//function exclude_empty_cat_menu_items( $items, $menu, $args ) {
//  // Get a list of product categories that excludes empty categories
//  $non_empty_categories = get_categories(array('taxonomy' => 'product_cat', 'hide_empty' => 1));
//  $non_empty_cats = array();
//  if(!empty($non_empty_categories)) {
//	foreach ( $non_empty_categories as $key2 => $cat ) {
//		$non_empty_cats[] = trim($cat->term_id);
//	}	
//  }  
//  // Iterate over the menu items
//  $more_menu_item = '';
//  foreach ( $items as $key => $item ) {  	
//	    if($item->object === 'product_cat') {	   
//		    if (!in_array(trim($item->object_id), $non_empty_cats)) {
//		    	$item->classes = 'memalling_hide_product_cat_menu';
//		    } else {
//		    	$item->classes = '';
//		    }	   
//		}
//		if(is_array($item->classes)) {
//			if(in_array('more_categories_menu', $item->classes)) {
//				$more_menu_item = $item;
//			}
//		} else if($item->classes === 'more_categories_menu') {
//			$more_menu_item = $item;
//		}
//  }
//
//  $more_menu_cats = array();
//  $more_menu_empty_cats = array();
//
//  foreach ( $items as $key => $item ) {  
//  		if($item->menu_item_parent === $more_menu_item->object_id) {
//  			$more_menu_cats[] = $item->object_id;
//  		} 
//  }	
//
//  foreach ( $items as $key => $item ) {  
//  		if(($item->menu_item_parent === $more_menu_item->object_id) && ($item->classes === 'memalling_hide_product_cat_menu')) {
//  			$more_menu_empty_cats[] = $item->object_id;
//  		} 
//  }
//
//  if(count($more_menu_cats) == count($more_menu_empty_cats)) {
//  		$more_menu_item->classes = 'memalling_hide_product_cat_menu';
//  }
//
//  return $items;
//}
//add_filter( 'wp_get_nav_menu_items', 'exclude_empty_cat_menu_items', null, 3 );

function calc_distance($lat1, $lon1, $lat2, $lon2, $unit) {
    if(trim($lat1)!='' && trim($lon1)!='' && trim($lat2)!='' && trim($lon2)!='') {
      $theta = $lon1 - $lon2;
      $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
      $dist = acos($dist);
      $dist = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      $unit = strtoupper($unit);
      
      if ($unit == "K") {
        return ($miles * 1.609344);
      } else if ($unit == "N") {
        return ($miles * 0.8684);
      } else {
        return $miles;
      }
    } else {
      return 'error';
    }
}

function get_tiny_url($url)  {  
  $ch = curl_init();  
  $timeout = 5;  
  curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url);  
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);  
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);  
  $data = curl_exec($ch);  
  curl_close($ch);  
  return $data;  
}

function depluralize($word){
    // Here is the list of rules. To add a scenario,
    // Add the plural ending as the key and the singular
    // ending as the value for that key. This could be
    // turned into a preg_replace and probably will be
    // eventually, but for now, this is what it is.
    //
    // Note: The first rule has a value of false since
    // we don't want to mess with words that end with
    // double 's'. We normally wouldn't have to create
    // rules for words we don't want to mess with, but
    // the last rule (s) would catch double (ss) words
    // if we didn't stop before it got to that rule. 
    $rules = array( 
        'ss' => false, 
        'os' => 'o', 
        'ies' => 'y', 
        'xes' => 'x', 
        'oes' => 'o', 
        'ies' => 'y', 
        'ves' => 'f', 
        's' => '');
    // Loop through all the rules and do the replacement. 
    foreach(array_keys($rules) as $key){
        // If the end of the word doesn't match the key,
        // it's not a candidate for replacement. Move on
        // to the next plural ending. 
        if(substr($word, (strlen($key) * -1)) != $key) 
            continue;
        // If the value of the key is false, stop looping
        // and return the original version of the word. 
        if($key === false) 
            return $word;
        // We've made it this far, so we can do the
        // replacement. 
        return substr($word, 0, strlen($word) - strlen($key)) . $rules[$key]; 
    }
    return $word;
}

function get_terms_name( $atts ) {
	
    $cats = get_terms('product_cat', array('parent' => 0, 'hide_empty' => 0));
    if (!empty($cats)) {
            foreach ($cats as $cat) {
                $cat_term_id = $cat->term_id;
                $cat_name = $cat->name;
                ?>
                <div>*<?php echo $cat_name; ?></div>
                <?php
                
                 $child_cats = get_terms('product_cat', array('parent' => $cat_term_id, 'hide_empty' => 0));
                  if (!empty($child_cats)) {
                    foreach ($child_cats as $child_cat) {
                                $child_cat_term_id = $child_cat->term_id;
                                $child_cat_name   = $child_cat->name;
                                  ?>
                                    <div><?php echo '— — '.$child_cat_name; ?></div>
                                 <?php
                                    
                                    $grand_child_cats = get_terms('product_cat', array('parent' => $child_cat_term_id, 'hide_empty' => 0));
                                    if (!empty($grand_child_cats)) {
                                      foreach ($grand_child_cats as $grand_child_cat) {
                                                  $g_child_cat_term_id = $grand_child_cat->term_id;
                                                  $_gchild_cat_name   = $grand_child_cat->name;
                                                   ?>
                                                    <div><?php echo '— — — '.$_gchild_cat_name; ?></div>
                                                 <?php
                                                 
                                                  $grand_g_child_cats = get_terms('product_cat', array('parent' => $g_child_cat_term_id, 'hide_empty' => 0));
                                                    if (!empty($grand_g_child_cats)) {
                                                      foreach ($grand_g_child_cats as $grand_g_child_cat) {
                                                                  
                                                                  $gg_gchild_cat_name   = $grand_g_child_cat->name;
                                                                   ?>
                                                                    <div><?php echo '— — — — '.$gg_gchild_cat_name; ?></div>
                                                                 <?php
                                                         }
                                                    }
                                         }
                                    }
                                 
                                 
                        }
                    }
                
            }
     }
}
add_shortcode( 'get_terms_name', 'get_terms_name' );