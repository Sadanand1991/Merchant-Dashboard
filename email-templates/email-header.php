<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load colours
$bg 		= get_option( 'woocommerce_email_background_color' );
$body		= get_option( 'woocommerce_email_body_background_color' );
$base 		= get_option( 'woocommerce_email_base_color' );
$base_text 	= wc_light_or_dark( $base, '#202020', '#ffffff' );
$text 		= get_option( 'woocommerce_email_text_color' );

$bg_darker_10 = wc_hex_darker( $bg, 10 );
$base_lighter_20 = wc_hex_lighter( $base, 20 );
$text_lighter_20 = wc_hex_lighter( $text, 20 );

// For gmail compatibility, including CSS styles in head/body are stripped out therefore styles need to be inline. These variables contain rules which are added to the template inline. !important; is a gmail hack to prevent styles being stripped if it doesn't like something.
$wrapper = "
	background-color:#ffffff;
	width:100%;
	-webkit-text-size-adjust:none !important;
	margin:0;
	padding: 70px 0 70px 0;
";
$template_container = "
	-webkit-box-shadow:0 0 0 3px rgba(0,0,0,0.025) !important;
	box-shadow:0 0 0 3px rgba(0,0,0,0.025) !important;
	-webkit-border-radius:6px !important;
	border-radius:6px !important;
	background-color: #fff;
	border: 1px solid $bg_darker_10;
	-webkit-border-radius:6px !important;
	border-radius:6px !important;
";
$template_header = "
	background-color: #eb0d7d;
	color: #fff;
	-webkit-border-top-left-radius:6px !important;
	-webkit-border-top-right-radius:6px !important;
	border-top-left-radius:6px !important;
	border-top-right-radius:6px !important;
	border-bottom: 0;
	font-family:Arial;
	font-weight:bold;
	line-height:100%;
	vertical-align:middle;

";
$body_content = "
	background-color: #fff;
	-webkit-border-radius:6px !important;
	border-radius:6px !important;
";
$body_content_inner = "
	color: #444;
	font-family:Arial;
	font-size:14px;
	line-height:150%;
	text-align:left;
	padding:30px;
";
$header_content_h1 = "
	color:#111;
	margin:0;
	padding: 0px;
	display:block;
	font-family:Arial;
	font-size:24px;
	font-weight:bold;
	text-align:left;
	line-height: 30px;
";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo get_bloginfo( 'name' ); ?></title>
	</head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    	<div style="<?php echo $wrapper; ?>">
        	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
            	<tr>
                	<td align="center" valign="top">
                		
                    	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" style="<?php echo $template_container; ?>">
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Header -->
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header" class="email-head" style="<?php echo $template_header; ?>" bgcolor="<?php echo $base; ?>">
                                        <tr>
                                            <td class="logo-email" style="text-align:center;">
                                                <?php echo '<p style="margin-top:0; text-align:center;"><a href="'.home_url().'"><img style="margin-top:10px; margin-bottom:0px" src="' . esc_url( home_url().'/wp-content/uploads/2016/07/logo-home-email.png' ) . '" alt="' . get_bloginfo( 'name' ) . '" /></a></p>'; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                           <?php global $theme_settings;
                                       if ($theme_settings['google_plus_link'] != '') { ?>
                                            <td style="float:left; margin-left: 15px; margin-bottom: 10px; width:25px;"><a href="<?php echo $theme_settings['google_plus_link']; ?>" target="_blank"><img width="25px" src='<?php echo $theme_settings['insta_icon']; ?>' /></a></td>
                                        <?php }
                                        if ($theme_settings['twitter_link'] != '') { ?>
                                            <td style="float:left; margin-left: 5px; margin-bottom: 10px; width:25px;"><a href="<?php echo $theme_settings['twitter_link']; ?>" target="_blank"><img width="25px" src='<?php echo $theme_settings['twitter_icon']; ?>' /></a></td>
                                        <?php }
                                            if ($theme_settings['facebook_link'] != '') {?>
                                            <td style="float:left; margin-left: 5px; margin-bottom: 10px; width:25px;"><a href="<?php echo $theme_settings['facebook_link']; ?>" target="_blank"><img width="25px" src='<?php echo $theme_settings['facbook_icon']; ?>' /></a></td>
                                        <?php } ?>
                                        </tr>
                                    </table>
                                    <!-- End Header -->
                                </td>
                            </tr>
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Body -->
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
                                    	<tr>
                                            <td valign="top" style="<?php echo $body_content; ?>">
                                                <!-- Content -->
                                                <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td valign="top">
                                                            <h1 style="<?php echo $header_content_h1; ?>"><?php echo $email_heading; ?></h1>
                                                            <div style="float:left; width:100%; line-height:20px; font-size:14px; font-family:Arial;" style="<?php echo $body_content_inner; ?>">