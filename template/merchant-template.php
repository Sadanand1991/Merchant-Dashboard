<?php
/*
Template Name: Merchant Dashboard Template
*/
?>
<!doctype html><html class="no-js" lang="en-US" xmlns:fb="http://ogp.me/ns/fb#" xmlns:addthis="http://www.addthis.com/help/api-spec"  prefix="og: http://ogp.me/ns#">
<?php
global $MerchantObject,$theme_settings, $post, $MMGlobalFunctions, $MMWooFunctions;
wp_head();
?>
<body class="page page-id-225 page-template-default merchant-dashboard sidebar-primary" style="">
<?php
//get_header();
?>
<?php    
$site_logo = "";
if(!empty($theme_settings)) {
    $site_logo = str_replace('http://', 'http://', $theme_settings['site_logo']);
}
if(isset($_GET['activation_code']) && !empty($_GET['activation_code']) ) {
    if(isset($_GET['id'])) {
        $user_id = $_GET['id'];
        $activation_code = get_user_meta($user_id, 'activation_key', true);
        if( trim($activation_code) == trim($_GET['activation_code']) ) {
            $user_verified = get_user_meta($user_id, 'user_verified', true );
            if($user_verified == 'verified'){
            delete_user_meta( $user_id, 'activation_key');
            wp_set_current_user( $user_id, $user->user_login );
            wp_set_auth_cookie( $user_id );
            do_action( 'wp_login', $user->user_login );
            echo ob_get_clean();
            }else {
                echo '<h3>You are not approaved user</h3>';
            }
        }
    }
}
?>
<div  id="wrapper" class="wrap"  role="document">
    <div class="mask"></div>
    <div id="page-content-wrapper">
        <div class="container-fluid">
            <div class="content row">
                <div class="container main-container">
                <header class="banner navbar navbar-default navbar-static-top " role="banner" <?php echo $header_image; ?>>
                    <div class="header_cols" style="padding-top:10px; padding-bottom:10px; ">
                      <div class="container">  
                        <div class="col-sm-4 col-xs-4 col-md-4 col-lg-4">
                            <div class="header_col_1">  
                              <a class="site_logo" href="<?php echo home_url(); ?>/">
                                <?php if( $site_logo == "" ) { ?>
                                  <?php bloginfo('name'); ?>  
                                <?php } else { ?>
                                  <img src="<?php echo $site_logo; ?>" alt="<?php bloginfo('name'); ?>"  />        
                                <?php } ?>  
                              </a>
                          </div>      
                        </div>
                        <div class="col-sm-8 col-xs-8 col-md-8 col-lg-8 dashboard_merchant_account_menu">
                          <?php do_action('merchant_account_menu'); ?>
                        </div>
                        <?php 
                        if(is_page( 'merchant-registration' ) && !is_user_logged_in ()){ ?>
                            <div class="login-merchant"> <i class="fa fa-sign-in"></i><a href="<?php echo site_url();?>/merchant-dashboard">Merchant Login</a></div>
                        <?php } ?>
                        <div class="clear"></div>
                      </div>
                    </div>
                </header>
                <main class="main" role="main">
                <?php 
                    if(!is_user_logged_in()) {
                        echo $MerchantObject->merchant_login_window();
                    } else {
                        $merchant_user = wp_get_current_user();
                        if($merchant_user->roles[0] != 'merchant') {
                                echo $MerchantObject->merchant_login_window();
                        } else {
                        ?>
                        <div class='merchant_dashboard_container'>
                            <div class="loading_templates"><i class="fa fa-spin fa-cog"></i></div>
                        </div>
                        <?php
                        }	
                    }
                ?>
                </main>
                <!--footer-->
                </div><!-- /.content -->
            </div><!-- /.container -->
        </div><!-- /.wrap -->
    </div>
</div>
<?php // get_footer();
wp_footer();
?>
</body>
</html>

