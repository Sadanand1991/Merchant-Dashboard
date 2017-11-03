<?php
/**
* Post Types Hooks Class
*/
 


class PostTypeHooks {

	public function __construct() {
		//Mall Admin Custom Columns 
//		add_filter( 'manage_mall_posts_columns', array($this, 'mm_mall_table_head') );
//		add_action( 'manage_mall_posts_custom_column', array($this, 'mm_mall_table_content'), 10, 2 );
//		add_action( 'restrict_manage_posts', array($this, 'mm_mall_table_filtering') );
//		add_filter( 'parse_query', array($this, 'mm_mall_table_filter') );

		//Shop Admin Custom Columns
		add_filter( 'manage_shop_posts_columns', array($this, 'mm_shop_table_head') );
		add_action( 'manage_shop_posts_custom_column', array($this, 'mm_shop_table_content'), 10, 2 );
//		add_action( 'restrict_manage_posts', array($this, 'mm_shop_table_filtering') );
		add_filter( 'parse_query', array($this, 'mm_shop_table_filter') );

		//Coupon Admin Custom Columns
		add_filter( 'manage_coupon_posts_columns', array($this, 'mm_coupon_table_head') );
		add_action( 'manage_coupon_posts_custom_column', array($this, 'mm_coupon_table_content'), 10, 2 );

		add_filter( 'manage_merchant_orders_posts_columns', array($this, 'mm_orders_table_head') );
		add_action( 'manage_merchant_orders_posts_custom_column', array($this, 'mm_orders_table_content'), 10, 2 );

		add_action( 'restrict_manage_posts', array($this, 'mm_product_table_filtering') );
		add_filter( 'parse_query', array($this, 'mm_product_table_filter') );
	}

	//Mall Admin Custom Columns 
	public function mm_mall_table_head( $defaults ) {
		unset($defaults['date']);
		$defaults['title']    = 'Mall Name';
	    $defaults['city']    = 'City';
	    $defaults['mall_image']    = 'Image';
	    $defaults['date']    = 'Date';
	    return $defaults;
	}

	public function mm_mall_table_content( $column_name, $post_id ) {
	    
	    if ($column_name == 'city') {
		    $mall_city = get_post_meta( $post_id, 'mall_city', true );
		    if($mall_city!="") {
		    	$city = get_page_by_path($mall_city,OBJECT,'city');
				$city = $city->post_title;
			} else {
				$city = '--';
			}
		    echo $city;
	    }

	    if ($column_name == 'mall_image') {
	    	echo get_the_post_thumbnail($post_id, 'thumbnail');
	    }	
	}

	//Shop Admin Custom Columns 
	public function mm_shop_table_head( $defaults ) {
		unset($defaults['date']);
		$defaults['title']    = 'Shop Name';
	    $defaults['mall']    = 'Mall Name';
	    $defaults['date']    = 'Date';
	    return $defaults;
	}

	public function mm_shop_table_content( $column_name, $post_id ) {
	    
	    if ($column_name == 'mall') {
		    $mall_id = get_post_meta( $post_id, 'mall', true );
		    if($mall_id!="") {
		    	$mall = get_post($mall_id);
		    	$mall_name = $mall->post_title;
		    } else {
		    	$mall_name = "--";
		    }
		    echo $mall_name;
	    }

	}

	//Coupon Admin Custom Columns 
	public function mm_coupon_table_head( $defaults ) {
		unset($defaults['date']);
		$defaults['title']    = 'Coupon Code';
	    $defaults['product_sku']    = 'Product SKU';
	    $defaults['product_publisher']    = 'Published By';
	    $defaults['shop_name']    = 'Shop Name';
	    $defaults['email_id']    = 'Coupon Generated For Email ID';
	    $defaults['coupon_date']    = 'Date';
	    return $defaults;
	}

	public function mm_coupon_table_content( $column_name, $post_id ) {
	    if ($column_name == 'product_sku') {
		    $product_id = get_post_meta( $post_id, '_product_id', true );
		    if($product_id!="") {
		    	echo get_post_meta($product_id, '_sku', true);
		    } else {
		    	echo '--';
		    }
		}

	    if ($column_name == 'product_publisher') {
	    	$product_id = get_post_meta( $post_id, '_product_id', true );
	    	$product = get_post($product_id);
	    	$publisher_id = $product->post_author;
	    	$publisher = get_user_meta($publisher_id, 'first_name', true)." ".get_user_meta($publisher_id, 'last_name', true);
	    	echo $publisher;
	    }	

	    if ($column_name == 'shop_name') {
	    	$product_id = get_post_meta( $post_id, '_product_id', true );
	    	$product_shop_id = get_post_meta( $product_id, 'shop', true );
	    	if($product_shop_id!="") {
	    		$shop = get_post($product_shop_id);
	    		echo $shop->post_title;
	    	} else {
	    		echo '--';
	    	}
	    }

	    if ($column_name == 'email_id') {
	    	$email = get_post_meta( $post_id, '_email_address', true );
	    	if($email!="") {
	    		echo $email;
	    	} else {
	    		echo '--';
	    	}
	    }	

	    if ($column_name == 'coupon_date') {
	    	$coupon_object = get_post($post_id);
	    	echo $coupon_object->post_date;
	    }
	    	
	}

	//Coupon Admin Custom Columns 
	public function mm_orders_table_head( $defaults ) {
		unset($defaults['title']);
		unset($defaults['date']);
	    $defaults['order_id']    = 'Order ID';
	    $defaults['product_sku']    = 'Product SKU';
	    $defaults['product_name']    = 'Product Name';
	    $defaults['order_from_details']    = 'Order From Details';	    
	    $defaults['shop_name']    = 'Shop Name';
	    $defaults['order_date']  = 'Date';
	    return $defaults;
	}

	public function mm_orders_table_content( $column_name, $post_id ) {

	    if ($column_name == 'order_id') {
		    echo $post_id;
		}

	    if ($column_name == 'product_sku') {
		    $product_id = get_post_meta( $post_id, '_product_id', true );
		    if($product_id!="") {
		    	echo get_post_meta($product_id, '_sku', true);
		    } else {
		    	echo '--';
		    }
		}	

	    if ($column_name == 'product_name') {
	    	$product_id = get_post_meta( $post_id, '_product_id', true );
	    	$product_object = get_post($product_id);	    	
	    	if($product_object->post_title) {	    		
	    		echo $product_object->post_title;
	    	} else {
	    		echo '--';
	    	}
	    }

	    if ($column_name == 'order_from_details') {
	    	$email = get_post_meta( $post_id, '_email_address', true );
                $phone = get_post_meta( $post_id, '_phone', true );
	    	echo '<b>Email ID : </b> <u>'.$email.'</u><br/>';
                echo '<b>Phone No. :</b> <u>'.$phone.'</u>';
	    }	

	    if ($column_name == 'shop_name') {
	    	$product_id = get_post_meta( $post_id, '_product_id', true );
	    	$product_shop_id = get_post_meta( $product_id, 'shop', true );
	    	if($product_shop_id!="") {
	    		$shop = get_post($product_shop_id);
	    		echo $shop->post_title;
	    	} else {
	    		echo '--';
	    	}
	    }

	    if ($column_name == 'order_date') {
	    	$order_object = get_post($post_id);
	    	echo $order_object->post_date;
	    }	
	    	
	}

//	
	
	
	public function mm_mall_table_filtering() {
//	  	if ( is_admin() AND $_GET['post_type'] == 'mall' ) {
//			$cities = $this->getCities();
//			
//		    echo '<select name="city" id="">';
//		    echo '<option value="">' . __( 'Show all Cities Mall', '' ) . '</option>';
//		    foreach( $cities as $city ) {
//		      $selected = ( !empty( $_GET['city'] ) AND $_GET['city'] == $city[0] ) ? 'selected="selected"' : '';
//		      echo '<option value="'.$city[0].'" '.$selected.'>' . $city[1] . '</option>';
//		    }
//		    echo '</select>';
//		}
//	}

////	public function mm_mall_table_filter( $query ) {
//		if( is_admin() AND $query->query['post_type'] == 'mall' ) {
//			$qv = &$query->query_vars;
//    		$qv['meta_query'] = array();
//    		if( !empty( $_GET['city'] ) ) {
//		      $qv['meta_query'][] = array(
//		        'field' => 'city',
//		        'value' => $_GET['city'],
//		        'compare' => '=',
//		        'type' => 'CHAR'
//		      );
//		    }
//		}	
//	}

//	public function mm_shop_table_filtering() {
//	  	if ( is_admin() AND $_GET['post_type'] == 'shop' ) {
//
//			$cities = $this->getCities();
//			echo '<select name="city_name" id="">';
//		    echo '<option value="">' . __( 'Show all Cities Shop', '' ) . '</option>';
//		    foreach( $cities as $city ) {
//		      $selected = ( !empty( $_GET['city_name'] ) AND $_GET['city_name'] == $city[0] ) ? 'selected="selected"' : '';
//		      echo '<option value="'.$city[0].'" '.$selected.'>' . $city[1] . '</option>';
//		    }
//		    echo '</select>';
//
//		    $malls = $this->getMalls();
//			echo '<select name="mall_id" id="">';
//		    echo '<option value="">' . __( 'Show all Malls Shop', '' ) . '</option>';
//		    foreach( $malls as $mall ) {
//		      $selected = ( !empty( $_GET['mall_id'] ) AND $_GET['mall_id'] == $mall[0] ) ? 'selected="selected"' : '';
//		      echo '<option value="'.$mall[0].'" '.$selected.'>' . $mall[1] . '</option>';
//		    }
//		    echo '</select>';
//
//		}
	}

	public function mm_shop_table_filter( $query ) {
		if( is_admin() AND $query->query['post_type'] == 'shop' ) {
			$qv = &$query->query_vars;
    		$qv['meta_query'] = array();
    		if( $_GET['city_name']!='' ) {
    			$qv['meta_query'][] = array(
		        'field' => 'city',
		        'value' => $_GET['city_name'],
		        'compare' => '=',
		        'type' => 'CHAR'
		      );
		    }
		    if( $_GET['mall_id']!='' ) {
		    	
		      $qv['meta_query'][] = array(
		        'field' => 'mall',
		        'value' => $_GET['mall_id'],
		        'compare' => '=',
		        'type' => 'NUMERIC'
		      );
		    }
		   
		}	
	}

	public function mm_product_table_filtering() {
	  	if ( is_admin() AND $_GET['post_type'] == 'product' ) {
		
//		  	$cities = $this->getCities();
//			echo '<select name="city" id="">';
//		    echo '<option value="">' . __( 'Show all Cities Products', '' ) . '</option>';
//		    foreach( $cities as $city ) {
//		      $selected = ( !empty( $_GET['city'] ) AND $_GET['city'] == $city[0] ) ? 'selected="selected"' : '';
//		      echo '<option value="'.$city[0].'" '.$selected.'>' . $city[1] . '</option>';
//		    }
//		    echo '</select>';

		    $merchant_users = get_users( 'role=merchant' );
		    echo '<select name="merchant_id" id="">';
		    echo '<option value="">' . __( 'Show all Merchant Products', '' ) . '</option>';
		    foreach( $merchant_users as $user ) {
		      $username = get_user_meta($user->ID, 'first_name', true).' '.get_user_meta($user->ID, 'last_name', true);
		      $selected = ( !empty( $_GET['merchant_id'] ) AND $_GET['merchant_id'] == $user->ID ) ? 'selected="selected"' : '';
		      echo '<option value="'.$user->ID.'" '.$selected.'>' . $username  . '</option>';
		    }
		    echo '</select>';

		}
	}

	public function mm_product_table_filter( $query ) {
		if( is_admin() AND $query->query['post_type'] == 'product' ) {
			$qv = &$query->query_vars;
			
			$qv['meta_query'] = array();
    		
    		if( !empty( $_GET['city'] ) ) {
		      $qv['meta_query'][] = array(
		        'field' => 'city',
		        'value' => $_GET['city'],
		        'compare' => '=',
		        'type' => 'CHAR'
		      );
		    }

		    if( !empty($_GET['merchant_id']) ) {
		    	$qv['author'] = $_GET['merchant_id'];
		    }

		}	
	}

} 

new PostTypeHooks();