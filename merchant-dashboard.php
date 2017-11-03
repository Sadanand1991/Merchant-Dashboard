<?php

/**
 * @package merchantdashboard-plugin
 */
/*
  Plugin Name: Merchant Dashboard
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('merchantdashboard_VERSION', '1.0');
define('merchantdashboard-plugin__MINIMUM_WP_VERSION', '3.2');
define('merchantdashboard__PLUGIN_URL', plugin_dir_url(__FILE__));
define('MERCHANT_DASHBOARD__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('merchantdashboard_DELETE_LIMIT', 100000);

//if(!function_exists('wp_get_current_user') ) {
//    include(ABSPATH . "wp-includes/pluggable.php"); 
//}
//require (ABSPATH . WPINC . '/pluggable.php');
require_once ( MERCHANT_DASHBOARD__PLUGIN_DIR . 'lib/theme-settings.php');    //theme settings.
require_once ( MERCHANT_DASHBOARD__PLUGIN_DIR . 'lib/upload-admin-csv.php');
require_once ( MERCHANT_DASHBOARD__PLUGIN_DIR . 'lib/upload-vendor-csv.php');
require_once ( MERCHANT_DASHBOARD__PLUGIN_DIR . 'lib/email-templete.php');
require_once ( MERCHANT_DASHBOARD__PLUGIN_DIR . 'lib/custom-setting.php');
require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'merchant_dashboard/class-merchant.php' );
require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'class-admin.php' );
//require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'class-custom-cpt.php' );
require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'class-email-template.php' );
//require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'custom.php' );
//require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'post_types_hooks.php' );
require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'theme-functions.php' );
require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'theme-ajax.php' );


//require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'assets/js/_main.js' );
//require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'assets/js/scripts.min.js' );
//require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'base-merchant-template.php' );
add_filter('page_template', 'merchant_page_template');

function merchant_page_template($page_template) {
    if (is_page('merchant-dashboard')) {

        $page_template = MERCHANT_DASHBOARD__PLUGIN_DIR . 'template/merchant-template.php';
    }
    if (is_page('merchant-registration')) {

        $page_template = MERCHANT_DASHBOARD__PLUGIN_DIR . 'template/merchant-registration.php';
    }
//     if (is_page('merchant-verify-and-register')) {
//
//        $page_template = MERCHANT_DASHBOARD__PLUGIN_DIR . 'template/merchant-verify-and-register.php';
//    }
    return $page_template;
}

function merchantdashboard_enqueue_script() {
    if (is_page('merchant-dashboard')) {
        wp_enqueue_style('roots_main', plugins_url('/assets/css/bootstrap.min.css', __FILE__), false, '9880649384aea9f1ee166331c0a30daa');
        if (!is_admin() && current_theme_supports('jquery-cdn')) {
          wp_deregister_script('jquery');
          wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', array(), null, false);
          add_filter('script_loader_src', 'roots_jquery_local_fallback', 10, 2);
        }
//        if (is_single() && comments_open() && get_option('thread_comments')) {
//          wp_enqueue_script('comment-reply');
//        }
        wp_register_script('modernizr', plugins_url('/assets/js/vendor/modernizr-2.7.0.min.js', __FILE__), array(), null, false);
        wp_register_script('roots_scripts', plugins_url('/assets/js/bootstrap.min.js', __FILE__), array(), '', true);
        wp_enqueue_script('modernizr');
        wp_enqueue_script('jquery');
        wp_enqueue_script('roots_scripts');
        wp_register_script('jquery_min_script', plugins_url('assets/js/vendor/jquery-1.11.0.min.js', __FILE__), '', '', true);
        wp_register_script('my_merchantdashboard_script', plugins_url('assets/js/_main.js', __FILE__), '', '', true);
        wp_register_script('mm_merchant_script', plugins_url('assets/js/_mm_merchant.js', __FILE__), '', '', true);
        wp_register_script('mm_script', plugins_url('assets/js/_mm_script.js', __FILE__), '', '', true);
        wp_register_script('_mm_search_script', plugins_url('assets/js/_mm_search.js', __FILE__), '', '', true);
        wp_register_script('scripts_min_script', plugins_url('assets/js/scripts.min.js', __FILE__), '', '', true);
        if (is_single() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }

        wp_enqueue_script('backbone');
        wp_enqueue_script('underscore');
        wp_enqueue_script('plupload-handlers');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('wc-jquery-ui-touchpunch');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css');

        wp_register_script('mm-plupload-old', plugins_url('assets/js/plugins/bootstrap/plupload-old.js', __FILE__),array(), null, false);
//        wp_enqueue_script('mm-plupload-old');

        global $post;

        $mall_archive_url = site_url() . '/malls/';

        $city_dropdown_redirect = 'false';
        if (is_post_type_archive('mall') || is_singular('mall') || is_singular('shop') || is_singular('product')) {
            $city_dropdown_redirect = 'true';
        }

        $current_site_url = site_url();

        $current_city = get_query_var('city') ? get_query_var('city') : '';

        $max_file_size = 100 * 1000 * 1000;
        $max_upload_no = 200;
        $allow_ext = 'jpg,jpeg,gif,png';
        $zip_csv_allow_ext = 'zip,csv';

        $home_page = 'false';
        if (is_front_page() || is_home()) {
            $home_page = 'true';
        }

        $home_page_url = site_url();

        $ismerchant = 'no';

        $theme_setttings = unserialize(get_option('global_theme_settings'));
        if (isset($theme_setttings['merchantdashboard'])) {
            $merchant_dashboard_page = $theme_setttings['merchantdashboard'];
            $merchant_dashboard_obj = get_post($merchant_dashboard_page);
            $merchant_dashboard = $merchant_dashboard_obj->post_name;
        }

        if ($theme_setttings['merchantdashboard'] == $post->ID) {
            $ismerchant = 'yes';
        }

        $product_cats = '';
        $mall_id = '';
        $shop_id = '';
        $viewby = '';

        if ($theme_setttings['dealsdiscounts'] == $post->ID) {
            $ddfsproducts = 'dd';
            $products_source = 'dd';
        } else if ($theme_setttings['freshstocks'] == $post->ID) {
            $ddfsproducts = 'fs';
            $products_source = 'fs';
        } else if (is_post_type_archive('product')) {
            $ddfsproducts = '';
            $products_source = 'search_products_page';
            $search_term = get_query_var('search') ? get_query_var('search') : '';
        } else {
            $ddfsproducts = '';
            if (is_taxonomy('product_cat')) {
                $products_source = 'product_categories';
                $product_cats = get_term_by('slug', get_query_var('product_cat'), get_query_var('taxonomy'));
                $product_cats = $product_cats->term_id;
            }
        }

//        if (is_singular('mall')) {
//            $viewby = get_query_var('viewby') ? get_query_var('viewby') : '';
//        }

        if (isset($_GET['reset_password']) && $_GET['reset_password'] != '') {
            $reset_password_flag = 'true';
            $reset_password_parameters = array($_GET['reset_password'], $_GET['flag']);
        } else {
            $reset_password_flag = 'false';
            $reset_password_parameters = array();
        }

        $translation_array = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'current_site_url' => $current_site_url,
            'city_dropdown_redirect' => $city_dropdown_redirect,
            'is_home' => $home_page,
            'home_url' => $home_page_url,
            'mall_archive_url' => $mall_archive_url,
            'current_city' => $current_city,
            'ddfsproducts' => $ddfsproducts,
            'nonce' => wp_create_nonce('product_images_upload'),
            'remove' => wp_create_nonce('product_image_remove'),
            'number' => $max_upload_no,
            'upload_enabled' => true,
            'confirmMsg' => __('Are you sure you want to delete this?'),
            'plupload_images' => array(
                'runtimes' => 'html5,flash,html4',
                'drop_element' => 'select_drop_upload_images',
                'browse_button' => 'select_drop_upload_images',
                'container' => 'upload_product_images_container',
                'file_data_name' => 'product_images_file',
                'max_file_size' => $max_file_size . 'b',
                'url' => admin_url('admin-ajax.php') . '?action=product_images_upload&nonce=' . wp_create_nonce('product_images_upload_allow'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                'multipart' => true,
                'urlstream_upload' => true,
            ),
            'up_large_ads_images' => array(
                'runtimes' => 'html5,flash,html4',
                'drop_element' => '',
                'browse_button' => 'shop_large_advertisements',
                'container' => 'large_ads_images_container',
                'file_data_name' => 'shop_advertise_images',
//                'max_file_size' => $max_file_size . 'b',
                'url' => admin_url('admin-ajax.php') . '?action=advertisements_images_upload&ads_type=large&nonce=' . wp_create_nonce('advertisements_images_upload_allow'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                'multipart' => true,
                'multipart_params' => '',
                'urlstream_upload' => true,
            ),
            'up_small_ads_images' => array(
                'runtimes' => 'html5,flash,html4',
                'drop_element' => '',
                'browse_button' => 'shop_small_advertisements',
                'container' => 'small_ads_images_container',
                'file_data_name' => 'shop_advertise_images',
//                'max_file_size' => $max_file_size . 'b',
                'url' => admin_url('admin-ajax.php') . '?action=advertisements_images_upload&ads_type=small&nonce=' . wp_create_nonce('advertisements_images_upload_allow'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                'multipart' => true,
                'multipart_params' => '',
                'urlstream_upload' => true,
            ),
            'plupload_size_chart_images' => array(
                'runtimes' => 'html5,flash,html4',
                'drop_element' => '',
                'browse_button' => 'sizechart_upload_images_area',
                'container' => 'upload_size_chart_image_wrap',
                'file_data_name' => 'size_chart_image_file',
//                'max_file_size' => $max_file_size . 'b',
                'url' => admin_url('admin-ajax.php') . '?action=upload_size_chart_images&nonce=' . wp_create_nonce('upload_size_chart_images_allow'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                'multipart' => true,
                'urlstream_upload' => true,
            ),
            'plupload_zip_csv' => array(
                'runtimes' => 'html5,flash,html4',
                'drop_element' => 'upload_csv_zip',
                'browse_button' => 'upload_csv_zip',
                'container' => 'upload_csv_zip_container',
                'file_data_name' => 'product_csv_zip_file',
//                'max_file_size' => 0,
                'url' => admin_url('admin-ajax.php') . '?action=product_zip_csv_upload&nonce=' . wp_create_nonce('product_csv_zip_allow'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $zip_csv_allow_ext)),
                'multipart' => true,
                'urlstream_upload' => true,
            ),
            'merchant_dashboard_page' => $merchant_dashboard,
            'ismerchant' => $ismerchant,
            'products_source' => $products_source,
            'search_term' => $search_term,
            'product_cats' => $product_cats,
            'shop_id' => $shop_id,
            'viewby' => $viewby,
            'editprofileurl' => get_permalink($theme_setttings['editprofile']),
            'reset_password_flag' => $reset_password_flag,
            'reset_password_parameters' => $reset_password_parameters,
            'currency_symbol' => get_woocommerce_currency_symbol(),
        );
        wp_localize_script('my_merchantdashboard_script', 'script_object', $translation_array);




//    wp_enqueue_script('jquery_min', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', '','', true);
       
        wp_enqueue_script('editor_script', plugins_url('assets/js/plugins/bootstrap/editor.js', __FILE__), '', '', true);
        wp_enqueue_script('jrespond_script', plugins_url('assets/js/plugins/bootstrap/jrespond.js', __FILE__), '', '', true);
        wp_enqueue_script('jpanelmenu_script', plugins_url('assets/js/plugins/bootstrap/jpanelmenu.js', __FILE__), '', '', true);
        wp_enqueue_script('jquery_autocomplete_script', plugins_url('assets/js/plugins/bootstrap/jquery.autocomplete.js', __FILE__), '', '', true);
        wp_enqueue_script('jquery_elastislide_script', plugins_url('assets/js/plugins/bootstrap/jquery.elastislide.js', __FILE__), '', '', true);
        wp_enqueue_script('owl_carousel_script', plugins_url('assets/js/plugins/bootstrap/owl.carousel.js', __FILE__), '', '', true);
        wp_enqueue_script('transition_script', plugins_url('assets/js/plugins/bootstrap/transition.js', __FILE__), '', '', true);
        wp_enqueue_script('cloud-zoom_script', plugins_url('assets/js/plugins/bootstrap/cloud-zoom.js', __FILE__), '', '', true);
        wp_enqueue_script('alert_script', plugins_url('assets/js/plugins/bootstrap/alert.js', __FILE__), '', '', true);
        wp_enqueue_script('button_script', plugins_url('assets/js/plugins/bootstrap/button.js', __FILE__), '', '', true);
        wp_enqueue_script('carousel_script', plugins_url('assets/js/plugins/bootstrap/carousel.js', __FILE__), '', '', true);
        wp_enqueue_script('collapse_script', plugins_url('assets/js/plugins/bootstrap/collapse.js', __FILE__), '', '', true);
        
        wp_enqueue_script('modal_script', plugins_url('assets/js/plugins/bootstrap/modal.js', __FILE__), '', '', true);
        wp_enqueue_script('tooltip_script', plugins_url('assets/js/plugins/bootstrap/tooltip.js', __FILE__), '', '', true);
        wp_enqueue_script('popover_script', plugins_url('assets/js/plugins/bootstrap/popover.js', __FILE__), '', '', true);
        wp_enqueue_script('scrollspy_script', plugins_url('assets/js/plugins/bootstrap/scrollspy.js', __FILE__), '', '', true);
        wp_enqueue_script('tab_script', plugins_url('assets/js/plugins/bootstrap/tab.js', __FILE__), '', '', true);
        wp_enqueue_script('affix_script', plugins_url('assets/js/plugins/bootstrap/affix.js', __FILE__), '', '', true);
        wp_enqueue_script('typeahed_script', plugins_url('assets/js/plugins/bootstrap/typeahed.js', __FILE__), '', '', true);
        wp_enqueue_script('jquery_dataTables_script', plugins_url('assets/js/plugins/bootstrap/jquery.dataTables.js', __FILE__), '', '', true);
        wp_enqueue_script('dataTables_bootstrap_script', plugins_url('assets/js/plugins/bootstrap/dataTables.bootstrap.js', __FILE__), '', '', true);
//        wp_enqueue_script('ionicons_script', plugins_url('assets/fonts/ionicons.ttf', __FILE__), '', '', true);
//        wp_enqueue_script('ionicons1_script', plugins_url('assets/fonts/ionicons.woff', __FILE__), '', '', true);

//    wp_enqueue_script('plupload_old_script', plugins_url('assets/js/plugins/bootstrap/plupload-old.js', __FILE__), '','', true);
        wp_enqueue_script('classie_script', plugins_url('assets/js/plugins/bootstrap/classie.js', __FILE__), '', '', true);
        wp_enqueue_script('jquery_elevatezoom_script', plugins_url('assets/js/plugins/bootstrap/jquery.elevatezoom.js', __FILE__), '', '', true);
        wp_enqueue_script('jsCarousel_script', plugins_url('assets/js/plugins/bootstrap/jsCarousel-2.0.0.js', __FILE__), '', '', true);


//        wp_enqueue_script('jquery_min_script');
        //wp_enqueue_script('plupload_script');
//    wp_enqueue_script('scripts_min_script');
        // wp_enqueue_script('tab_script');

        wp_enqueue_script('my_merchantdashboard_script');
        wp_enqueue_script('mm_merchant_script');
        wp_enqueue_script('mm_script');
        wp_enqueue_script('_mm_search_script');


//    wp_enqueue_script( 'my-js', MERCHANT_DASHBOARD__PLUGIN_DIR . 'assets/js/_main.js', false );
    }
    if (is_page('merchant-registration')) {
        wp_enqueue_style('roots_main', plugins_url('/assets/css/bootstrap.min.css', __FILE__), false, '9880649384aea9f1ee166331c0a30daa');
        if (!is_admin() && current_theme_supports('jquery-cdn')) {
            wp_deregister_script('jquery');
            wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', array(), null, false);
//          add_filter('script_loader_src', 'roots_jquery_local_fallback', 10, 2);
        }
        
        wp_register_script('modernizr', plugins_url('/assets/js/vendor/modernizr-2.7.0.min.js', __FILE__), array(), null, false);
        wp_register_script('roots_scripts', plugins_url('/assets/js/bootstrap.min.js', __FILE__), array(), '', true);
        wp_enqueue_script('modernizr');
        wp_enqueue_script('jquery');
        wp_enqueue_script('roots_scripts');
        
        wp_enqueue_script('main_script', plugins_url('assets/js/main_registration.js', __FILE__), '', '', true);  
    }
    if (is_page('merchant-dashboard') || is_page('merchant-registration')) {
        wp_enqueue_style('dashboard-css', plugins_url('merchant-dashboard/assets/css/dashboard-style.css'), __FILE__);
        wp_enqueue_style('main-css', plugins_url('merchant-dashboard/assets/css/main.min.css'), __FILE__);
    }
//    wp_dequeue_style('flatsome-main');
//    wp_deregister_style('flatsome-main');

}
add_action('wp_enqueue_scripts', 'merchantdashboard_enqueue_script');
function roots_jquery_local_fallback($src, $handle = null) {
    static $add_jquery_fallback = false;

    if ($add_jquery_fallback) {
        echo '<script>window.jQuery || document.write(\'<script src="' . plugins_url('/assets/js/vendor/jquery-1.11.0.min.js') .'"><\/script>\')</script>' . "\n";
        $add_jquery_fallback = false;
    }
    if ($handle === 'jquery') {
        $add_jquery_fallback = true;
    }
    return $src;
}
add_action('wp_head', 'roots_jquery_local_fallback');

function merchantdashboard_admin_enqueue_script() {


    $current_screen = get_current_screen();
//    print_r($current_screen->base);
    if( $current_screen->base === "product_page_admin_add_csv" || $current_screen->base === "product_page_vendor_add_csv") {
        wp_enqueue_media();
        wp_enqueue_script('dropdown_script', plugins_url('assets/js/plugins/bootstrap/dropdown.js', __FILE__), array(), '1.0', true);
        
        wp_register_script('jquery_min_script', plugins_url('assets/js/vendor/jquery-1.11.0.min.js', __FILE__), '', '', true);
        wp_register_script('my_merchantdashboard_script', plugins_url('assets/js/_main.js', __FILE__), '', '', true);
        wp_register_script('mm_merchant_script', plugins_url('assets/js/_mm_merchant.js', __FILE__), '', '', true);
        wp_register_script('mm_script', plugins_url('assets/js/_mm_script.js', __FILE__), '', '', true);
        wp_register_script('_mm_search_script', plugins_url('assets/js/_mm_search.js', __FILE__), '', '', true);
//    wp_register_script('plupload_script', plugins_url('assets/js/plupload-old.js', __FILE__), '','', true);
        wp_register_script('scripts_min_script', plugins_url('assets/js/scripts.min.js', __FILE__), '', '', true);

        if (is_single() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }

        wp_enqueue_script('backbone');
        wp_enqueue_script('underscore');
        wp_enqueue_script('plupload-handlers');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('wc-jquery-ui-touchpunch');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css');

        wp_register_script('mm-plupload-old', plugins_url('assets/js/plugins/bootstrap/plupload-old.js', __FILE__),array(), null, false);
        wp_enqueue_script('mm-plupload-old');

        global $post;
        
        $current_site_url = site_url();

        $current_city = get_query_var('city') ? get_query_var('city') : '';

        $max_file_size = 100 * 1000 * 100000;
        $max_upload_no = 200;
        $allow_ext = 'jpg,jpeg,gif,png';
        $zip_csv_allow_ext = 'zip,csv';

        $home_page = 'false';
        if (is_front_page() || is_home()) {
            $home_page = 'true';
        }

        $home_page_url = site_url();

        $ismerchant = 'no';

        $theme_setttings = unserialize(get_option('global_theme_settings'));
        if (isset($theme_setttings['merchantdashboard'])) {
            $merchant_dashboard_page = $theme_setttings['merchantdashboard'];
            $merchant_dashboard_obj = get_post($merchant_dashboard_page);
            $merchant_dashboard = $merchant_dashboard_obj->post_name;
        }

        if ($theme_setttings['merchantdashboard'] == $post->ID) {
            $ismerchant = 'yes';
        }

        $product_cats = '';
        $mall_id = '';
        $shop_id = '';
        $viewby = '';

        if ($theme_setttings['dealsdiscounts'] == $post->ID) {
            $ddfsproducts = 'dd';
            $products_source = 'dd';
        } else if ($theme_setttings['freshstocks'] == $post->ID) {
            $ddfsproducts = 'fs';
            $products_source = 'fs';
        } else if (is_post_type_archive('product')) {
            $ddfsproducts = '';
            $products_source = 'search_products_page';
            $search_term = get_query_var('search') ? get_query_var('search') : '';
        } else {
            $ddfsproducts = '';
            if (is_taxonomy('product_cat')) {
                $products_source = 'product_categories';
                $product_cats = get_term_by('slug', get_query_var('product_cat'), get_query_var('taxonomy'));
                $product_cats = $product_cats->term_id;
            }
        }

//        if (is_singular('mall')) {
//            $viewby = get_query_var('viewby') ? get_query_var('viewby') : '';
//        }

        if (isset($_GET['reset_password']) && $_GET['reset_password'] != '') {
            $reset_password_flag = 'true';
            $reset_password_parameters = array($_GET['reset_password'], $_GET['flag']);
        } else {
            $reset_password_flag = 'false';
            $reset_password_parameters = array();
        }
        if($current_screen->base === "product_page_admin_add_csv"){
                $translation_array = array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'current_site_url' => $current_site_url,
                'city_dropdown_redirect' => $city_dropdown_redirect,
                'is_home' => $home_page,
                'home_url' => $home_page_url,
                'mall_archive_url' => $mall_archive_url,
                'current_city' => $current_city,
                'ddfsproducts' => $ddfsproducts,
                'nonce' => wp_create_nonce('product_images_upload'),
                'remove' => wp_create_nonce('product_image_remove'),
                'number' => $max_upload_no,
                'upload_enabled' => true,
                'confirmMsg' => __('Are you sure you want to delete this?'),
                'plupload_images' => array(
                    'runtimes' => 'html5,flash,html4',
                    'drop_element' => 'select_drop_upload_images',
                    'browse_button' => 'select_drop_upload_images',
                    'container' => 'upload_product_images_container',
                    'file_data_name' => 'product_images_file',
//                    'max_file_size' => 0,
                    'url' => admin_url('admin-ajax.php') . '?action=product_images_upload&nonce=' . wp_create_nonce('product_images_upload_allow'),
                    'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                    'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                    'multipart' => true,
                    'urlstream_upload' => true,
                ),
                'up_large_ads_images' => array(
                    'runtimes' => 'html5,flash,html4',
                    'drop_element' => '',
                    'browse_button' => 'shop_large_advertisements',
                    'container' => 'large_ads_images_container',
                    'file_data_name' => 'shop_advertise_images',
//                    'max_file_size' => 0,
                    'url' => admin_url('admin-ajax.php') . '?action=advertisements_images_upload&ads_type=large&nonce=' . wp_create_nonce('advertisements_images_upload_allow'),
                    'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                    'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                    'multipart' => true,
                    'multipart_params' => '',
                    'urlstream_upload' => true,
                ),
                'up_small_ads_images' => array(
                    'runtimes' => 'html5,flash,html4',
                    'drop_element' => '',
                    'browse_button' => 'shop_small_advertisements',
                    'container' => 'small_ads_images_container',
                    'file_data_name' => 'shop_advertise_images',
//                    'max_file_size' => 0,
                    'url' => admin_url('admin-ajax.php') . '?action=advertisements_images_upload&ads_type=small&nonce=' . wp_create_nonce('advertisements_images_upload_allow'),
                    'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                    'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                    'multipart' => true,
                    'multipart_params' => '',
                    'urlstream_upload' => true,
                ),
                'plupload_size_chart_images' => array(
                    'runtimes' => 'html5,flash,html4',
                    'drop_element' => '',
                    'browse_button' => 'sizechart_upload_images_area',
                    'container' => 'upload_size_chart_image_wrap',
                    'file_data_name' => 'size_chart_image_file',
//                    'max_file_size' => 0,
                    'url' => admin_url('admin-ajax.php') . '?action=upload_size_chart_images&nonce=' . wp_create_nonce('upload_size_chart_images_allow'),
                    'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                    'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                    'multipart' => true,
                    'urlstream_upload' => true,
                ),
                'plupload_zip_csv' => array(
                    'runtimes' => 'html5,flash,html4',
                    'drop_element' => 'upload_csv_zip',
                    'browse_button' => 'upload_csv_zip',
                    'container' => 'upload_csv_zip_container',
                    'file_data_name' => 'product_csv_zip_file',
//                    'max_file_size' => 0,
                    'url' => admin_url('admin-ajax.php') . '?action=vendor_product_zip_csv_upload&nonce=' . wp_create_nonce('product_csv_zip_allow'),
                    'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                    'filters' => array(array('title' => __('Allowed Files'), 'extensions' => 'csv')),
                    'multipart' => true,
                    'urlstream_upload' => true,
                ),
                'merchant_dashboard_page' => $merchant_dashboard,
                'ismerchant' => $ismerchant,
                'products_source' => $products_source,
                'search_term' => $search_term,
                'product_cats' => $product_cats,
                'shop_id' => $shop_id,
                'viewby' => $viewby,
                'editprofileurl' => get_permalink($theme_setttings['editprofile']),
                'reset_password_flag' => $reset_password_flag,
                'reset_password_parameters' => $reset_password_parameters,
                'currency_symbol' => get_woocommerce_currency_symbol(),
            );
        }else {
        $translation_array = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'current_site_url' => $current_site_url,
            'city_dropdown_redirect' => $city_dropdown_redirect,
            'is_home' => $home_page,
            'home_url' => $home_page_url,
            'mall_archive_url' => $mall_archive_url,
            'current_city' => $current_city,
            'ddfsproducts' => $ddfsproducts,
            'nonce' => wp_create_nonce('product_images_upload'),
            'remove' => wp_create_nonce('product_image_remove'),
            'number' => $max_upload_no,
            'upload_enabled' => true,
            'confirmMsg' => __('Are you sure you want to delete this?'),
            'plupload_images' => array(
                'runtimes' => 'html5,flash,html4',
                'drop_element' => 'select_drop_upload_images',
                'browse_button' => 'select_drop_upload_images',
                'container' => 'upload_product_images_container',
                'file_data_name' => 'product_images_file',
//                'max_file_size' => 0,
                'url' => admin_url('admin-ajax.php') . '?action=product_images_upload&nonce=' . wp_create_nonce('product_images_upload_allow'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                'multipart' => true,
                'urlstream_upload' => true,
            ),
            'up_large_ads_images' => array(
                'runtimes' => 'html5,flash,html4',
                'drop_element' => '',
                'browse_button' => 'shop_large_advertisements',
                'container' => 'large_ads_images_container',
                'file_data_name' => 'shop_advertise_images',
//                'max_file_size' => 0,
                'url' => admin_url('admin-ajax.php') . '?action=advertisements_images_upload&ads_type=large&nonce=' . wp_create_nonce('advertisements_images_upload_allow'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                'multipart' => true,
                'multipart_params' => '',
                'urlstream_upload' => true,
            ),
            'up_small_ads_images' => array(
                'runtimes' => 'html5,flash,html4',
                'drop_element' => '',
                'browse_button' => 'shop_small_advertisements',
                'container' => 'small_ads_images_container',
                'file_data_name' => 'shop_advertise_images',
//                'max_file_size' => 0,
                'url' => admin_url('admin-ajax.php') . '?action=advertisements_images_upload&ads_type=small&nonce=' . wp_create_nonce('advertisements_images_upload_allow'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                'multipart' => true,
                'multipart_params' => '',
                'urlstream_upload' => true,
            ),
            'plupload_size_chart_images' => array(
                'runtimes' => 'html5,flash,html4',
                'drop_element' => '',
                'browse_button' => 'sizechart_upload_images_area',
                'container' => 'upload_size_chart_image_wrap',
                'file_data_name' => 'size_chart_image_file',
//                'max_file_size' => 0,
                'url' => admin_url('admin-ajax.php') . '?action=upload_size_chart_images&nonce=' . wp_create_nonce('upload_size_chart_images_allow'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $allow_ext)),
                'multipart' => true,
                'urlstream_upload' => true,
            ),
            'plupload_zip_csv' => array(
                'runtimes' => 'html5,flash,html4',
                'drop_element' => 'upload_csv_zip',
                'browse_button' => 'upload_csv_zip',
                'container' => 'upload_csv_zip_container',
                'file_data_name' => 'product_csv_zip_file',
//                'max_file_size' => 0,
                'url' => admin_url('admin-ajax.php') . '?action=admin_product_zip_csv_upload&nonce=' . wp_create_nonce('product_csv_zip_allow'),
                'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                'filters' => array(array('title' => __('Allowed Files'), 'extensions' => $zip_csv_allow_ext)),
                'multipart' => true,
                'urlstream_upload' => true,
            ),
            'merchant_dashboard_page' => $merchant_dashboard,
            'ismerchant' => $ismerchant,
            'products_source' => $products_source,
            'search_term' => $search_term,
            'product_cats' => $product_cats,
            'shop_id' => $shop_id,
            'viewby' => $viewby,
            'editprofileurl' => get_permalink($theme_setttings['editprofile']),
            'reset_password_flag' => $reset_password_flag,
            'reset_password_parameters' => $reset_password_parameters,
            'currency_symbol' => get_woocommerce_currency_symbol(),
        );
       }
        wp_localize_script('my_merchantdashboard_script', 'script_object', $translation_array);




//    wp_enqueue_script('jquery_min', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js', '','', true);
       
        wp_enqueue_script('editor_script', plugins_url('assets/js/plugins/bootstrap/editor.js', __FILE__), '', '', true);
        wp_enqueue_script('jrespond_script', plugins_url('assets/js/plugins/bootstrap/jrespond.js', __FILE__), '', '', true);
        wp_enqueue_script('jpanelmenu_script', plugins_url('assets/js/plugins/bootstrap/jpanelmenu.js', __FILE__), '', '', true);
        wp_enqueue_script('jquery_autocomplete_script', plugins_url('assets/js/plugins/bootstrap/jquery.autocomplete.js', __FILE__), '', '', true);
        wp_enqueue_script('jquery_elastislide_script', plugins_url('assets/js/plugins/bootstrap/jquery.elastislide.js', __FILE__), '', '', true);
        wp_enqueue_script('owl_carousel_script', plugins_url('assets/js/plugins/bootstrap/owl.carousel.js', __FILE__), '', '', true);
        wp_enqueue_script('transition_script', plugins_url('assets/js/plugins/bootstrap/transition.js', __FILE__), '', '', true);
        wp_enqueue_script('cloud-zoom_script', plugins_url('assets/js/plugins/bootstrap/cloud-zoom.js', __FILE__), '', '', true);
        wp_enqueue_script('alert_script', plugins_url('assets/js/plugins/bootstrap/alert.js', __FILE__), '', '', true);
        wp_enqueue_script('button_script', plugins_url('assets/js/plugins/bootstrap/button.js', __FILE__), '', '', true);
        wp_enqueue_script('carousel_script', plugins_url('assets/js/plugins/bootstrap/carousel.js', __FILE__), '', '', true);
        wp_enqueue_script('collapse_script', plugins_url('assets/js/plugins/bootstrap/collapse.js', __FILE__), '', '', true);
        
        wp_enqueue_script('modal_script', plugins_url('assets/js/plugins/bootstrap/modal.js', __FILE__), '', '', true);
        wp_enqueue_script('tooltip_script', plugins_url('assets/js/plugins/bootstrap/tooltip.js', __FILE__), '', '', true);
        wp_enqueue_script('popover_script', plugins_url('assets/js/plugins/bootstrap/popover.js', __FILE__), '', '', true);
        wp_enqueue_script('scrollspy_script', plugins_url('assets/js/plugins/bootstrap/scrollspy.js', __FILE__), '', '', true);
        wp_enqueue_script('tab_script', plugins_url('assets/js/plugins/bootstrap/tab.js', __FILE__), '', '', true);
        wp_enqueue_script('affix_script', plugins_url('assets/js/plugins/bootstrap/affix.js', __FILE__), '', '', true);
        wp_enqueue_script('typeahed_script', plugins_url('assets/js/plugins/bootstrap/typeahed.js', __FILE__), '', '', true);
        wp_enqueue_script('jquery_dataTables_script', plugins_url('assets/js/plugins/bootstrap/jquery.dataTables.js', __FILE__), '', '', true);
        wp_enqueue_script('dataTables_bootstrap_script', plugins_url('assets/js/plugins/bootstrap/dataTables.bootstrap.js', __FILE__), '', '', true);
//        wp_enqueue_script('ionicons_script', plugins_url('assets/fonts/ionicons.ttf', __FILE__), '', '', true);
//        wp_enqueue_script('ionicons1_script', plugins_url('assets/fonts/ionicons.woff', __FILE__), '', '', true);

//    wp_enqueue_script('plupload_old_script', plugins_url('assets/js/plugins/bootstrap/plupload-old.js', __FILE__), '','', true);
        wp_enqueue_script('classie_script', plugins_url('assets/js/plugins/bootstrap/classie.js', __FILE__), '', '', true);
        wp_enqueue_script('jquery_elevatezoom_script', plugins_url('assets/js/plugins/bootstrap/jquery.elevatezoom.js', __FILE__), '', '', true);
        wp_enqueue_script('jsCarousel_script', plugins_url('assets/js/plugins/bootstrap/jsCarousel-2.0.0.js', __FILE__), '', '', true);
        wp_enqueue_style('fontawesome_',home_url().'/wp-content/plugins/yith-woocommerce-wishlist/assets/css/font-awesome.min.css', __FILE__, '', '', true); 

//        wp_enqueue_script('jquery_min_script');
        //wp_enqueue_script('plupload_script');
//    wp_enqueue_script('scripts_min_script');
        // wp_enqueue_script('tab_script');

        wp_enqueue_script('my_merchantdashboard_script');
        wp_enqueue_script('mm_merchant_script');
        wp_enqueue_script('mm_script');
        wp_enqueue_script('_mm_search_script');
//    wp_enqueue_script( 'my-js', MERCHANT_DASHBOARD__PLUGIN_DIR . 'assets/js/_main.js', false );
    }
    $current_screen = get_current_screen();
    if( $current_screen->base === "product_page_admin_add_csv" || $current_screen->base === "product_page_vendor_add_csv") {
        wp_enqueue_script('main_script', plugins_url('assets/js/main_registration.js', __FILE__), '', '', true);  
    }
    $current_screen = get_current_screen();
    if( $current_screen->base === "product_page_admin_add_csv" || $current_screen->base === "product_page_vendor_add_csv") {
        wp_enqueue_style('dashboard-css', plugins_url('merchant-dashboard/assets/css/dashboard-style.css'), __FILE__);
        wp_enqueue_style('main-css', plugins_url('merchant-dashboard/assets/css/main.min.css'), __FILE__);
    }
}
add_action( 'admin_enqueue_scripts', 'merchantdashboard_admin_enqueue_script' );
function load_custom_wp_admin() {
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');
    wp_enqueue_script('backbone');
    wp_register_style( 'custom_admin_script', plugins_url('assets/admin/admin_script.js', __FILE__), true, '1.0.0' );
    wp_register_style( 'custom_admin_style', plugins_url('assets/admin/admin_style.js', __FILE__), true, '1.0.0' );
    wp_enqueue_style( 'custom_admin_script' );
    wp_enqueue_style( 'custom_admin_style' );
}
//add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin' );
add_filter( 'post_row_actions', 'remove_row_actions', 10, 1 );
//function remove_row_actions( $actions )
//{
//    if( get_post_type() === 'coupon' ) {
//        unset( $actions['edit'] );
//        unset( $actions['view'] );
//        unset( $actions['trash'] );
//        unset( $actions['inline hide-if-no-js'] );
//    }   
//    if( get_post_type() === 'merchant_orders' ) {
//        unset( $actions['edit'] );
//        unset( $actions['view'] );
//        unset( $actions['trash'] );
//        unset( $actions['inline hide-if-no-js'] );
//    }    
//    return $actions;
//}

require_once( MERCHANT_DASHBOARD__PLUGIN_DIR . 'woocommerce-hooks.php' );