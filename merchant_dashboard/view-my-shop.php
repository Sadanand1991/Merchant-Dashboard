<?php
/**
* View My Shop
*/

class View_My_Shop {

	public function __construct() {
		add_action('wp_footer', array($this, 'merchant_shop_template'));
		add_action('wp_footer', array($this, 'coupons_list_tml'));
		add_action('wp_footer', array($this, 'orders_list_tml'));
		add_action('wp_footer', array($this, 'shop_advertisments_template'));
		add_action('wp_ajax_get_products_count', array($this, 'get_products_count'));
		add_action('wp_ajax_get_orders_count', array($this, 'get_orders_count'));
		add_action('wp_ajax_get_coupons_count', array($this, 'get_coupons_count'));
		add_action('wp_ajax_get_most_viewed_produts', array($this, 'get_most_viewed_produts'));
		add_action('wp_ajax_remove_product_coupon', array($this, 'remove_product_coupon_ajax'));
		add_action('wp_ajax_remove_merchant_orders', array($this, 'remove_merchant_orders_ajax'));
		add_action('wp_ajax_advertisements_images_upload', array($this, 'advertisements_images_upload_ajax'));
		add_action('wp_ajax_delete_shop_ads', array($this, 'delete_shop_ads'));
	}

	public function merchant_shop_template() {
		ob_start();
		global $theme_setttings;
		?>
		<script type="text/html" id="view_my_shop_tml">
		<?php
		if(is_user_logged_in() ) {
		$merchant_user = wp_get_current_user();
		if($merchant_user->roles[0] == 'merchant') {
		$shopid = get_user_meta($merchant_user->ID, 'user_shop', true);
		$shopobject = get_post($shopid);
		$shop = $shopobject->post_title;	
		?>
		<div class="my_shop_container">
			<div class="container" style="">
				<h2 class="page-header"><i class="icon ion-gear-b"></i>My Shop</h2>
				
				<h4 class="merchantshopname"><?php echo $shop; ?></h4>
				<div class="row">
                        <div class="col-lg-6 col-sm-6 total_products_count">
                            <div class="small-box bg-purple">
                                <a href="<?php echo get_permalink($theme_setttings['merchantdashboard']); ?>#/products/" class="small-box-footer">
                                <div class="inner">
                                    <h3>
                                        0
                                        <?php 
                                        	//$this->get_products_count(); 
                                        ?>
                                    </h3>
                                    <p>
                                        Products
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-bag"></i>
                                </div>
                                
                                    View Products <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-lg-6 col-sm-6 total_orders_count">
                            <div class="small-box bg-blue">
                               <a href="<?php echo get_permalink($theme_setttings['merchantdashboard']); ?>#/orders/" class="small-box-footer">  
                                <div class="inner">
                                    <h3>
                                    	0
                                        <?php 
//                                        	$this->get_orders_count(); 
                                        ?>
                                    </h3>
                                    <p>
                                        Orders
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-ios7-cart-outline"></i>
                                </div>
                                
                                    View Orders <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
<!--                        <div class="col-lg-4 col-sm-4 total_coupons_count">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3>
                                        0 
                                        <?php 
                                       		//$this->get_coupons_count(); 
                                        ?>
                                    </h3>
                                    <p>
                                        Coupons
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-ios7-pricetag-outline"></i>
                                </div>
                                <a href="<?php //echo get_permalink($theme_setttings['merchantdashboard']); ?>#/coupons/" class="small-box-footer">
                                    View Coupons <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div> ./col -->
                    </div><!-- /.row -->

                    <div class="row product_uploads_menu_row">
                        <div class="col-lg-6 col-sm-6">
                           
                            <div class="small-box bg-aqua">
                                <a href="<?php echo get_permalink($theme_setttings['merchantdashboard']); ?>#/upload-product/" class="small-box-footer">
                                <div class="inner">
                                    <p>
                                        Upload Product
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="icon ion-android-archive"></i>
                                </div>
                                
                                    <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->

                        <div class="col-lg-6 col-sm-6">
                            <div class="small-box bg-teal">
                                <a href="<?php echo get_permalink($theme_setttings['merchantdashboard']); ?>#/csv-products-upload/" class="small-box-footer">
                                <div class="inner">
                                    <p>
                                        Upload Products as CSV
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="icon ion-android-note"></i>
                                </div>
                                
                                    <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
                    </div><!-- ./row --> 
                    <div class="row product_uploads_menu_row">
                        <div class="col-lg-6 col-sm-6">
                           
                            <div class="small-box bg-purple">
                                <a href="<?php echo get_permalink($theme_setttings['merchantdashboard']); ?>#/manage-sets/" class="small-box-footer">
                                <div class="inner">
                                    <p>
                                        Manage Sets
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="icon ion-gear-a"></i>
                                </div>
                                
                                    <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->

                        <div class="col-lg-6 col-sm-6">
                            <div class="small-box bg-teal">
                               
                            </div>
                        </div><!-- ./col -->
                    </div><!-- ./row --> 
                    <div class="row">
                    	<div class="col-sm-12">
                    		<div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">Most Viewed Products</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body no-padding most_viewed_products">
                                     <?php echo $this->get_most_viewed_produts(); ?>
                                </div><!-- /.box-body -->
                            </div>
                    	</div>
                    </div> <!-- ./row --> 

			</div>	
		</div>
		<?php
		}
		}
		?>
		</script>	
		<?php
		echo ob_get_clean();
	}


	public function get_products_count() {
		global $wpdb;
		$merchant_user = wp_get_current_user();
		if($merchant_user->roles[0] == 'merchant') {
			$user_id = $merchant_user->ID;
			$products_count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_author = $user_id and post_type = 'product' and post_status = 'publish'" );
		} else {
			$products_count = 0;
		}
		echo $products_count;
		if($_GET['action']=="get_products_count") {
			die(0);
		}
	}

	public function get_coupons_count() {
		global $wpdb;
		$merchant_user = wp_get_current_user();
		if($merchant_user->roles[0] == 'merchant') {
			$user_id = $merchant_user->ID;
			$coupons_count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_author = $user_id and post_type = 'coupon'" );
		} else {
			$coupons_count = 0;
		}
		echo $coupons_count;
		if($_GET['action']=="get_coupons_count") {
			die(0);
		}
	}

	public function get_orders_count() {
		global $wpdb;
		$merchant_user = wp_get_current_user();
		if($merchant_user->roles[0] == 'merchant') {
			$user_id = $merchant_user->ID;
			$orders_count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_author = $user_id and post_type = 'merchant_orders'" );
		} else {
			$orders_count = 0;
		}
		echo $orders_count;
		if($_GET['action']=="get_orders_count") {
			die(0);
		}
	}

	public function get_most_viewed_produts() {
		ob_start();
		$merchant_user = wp_get_current_user();
			if($merchant_user->roles[0] == 'merchant') {
			$user_id = $merchant_user->ID;
			$viewsargs = array(
				'post_type' => 'product',
				'post_status' => 'publish',
				"meta_key" => "product_views",
			    "orderby" => "meta_value_num",
			    "order" => "DESC",
				'author' => $user_id,
				'posts_per_page' => 5,
			);
			$views_product_query = new WP_Query($viewsargs);
			if($views_product_query->have_posts()) {
				?>
				<table class="table table-striped">
                <tbody>
                <tr>
                <th>Product SKU</th>
                <th>Product</th>
                <th>Views</th>
                </tr>
                <?php                        
				while($views_product_query->have_posts()) {
					$views_product_query->the_post();
					$product_title = $views_product_query->post->post_title;
					$product_link = get_permalink($views_product_query->post->ID);	
					$product_sku = get_post_meta($views_product_query->post->ID, '_sku', true);	
					$product_views = get_post_meta($views_product_query->post->ID, 'product_views', true);	
					?>
					<tr>
	                    <td><span class="badge bg-purple"><?php echo $product_sku; ?></span></td>
	                    <td><a href="<?php echo $product_link; ?>" target="_blank"><?php echo $product_title; ?></a></td>
	                    <td><span class="badge bg-red"><?php echo $product_views; ?></span></td>
                    </tr>
					<?php
					}
					?>
					</tbody>
					</table>
					<?php
				}
				else {
					?>
					<div class="callout callout-info">
                        <h5 style="margin:0px;">There are no most viewed products now !!!</h5>
                    </div>
					<?php
				}
				wp_reset_postdata();
				?>
				
				<?php
		} 
		echo ob_get_clean();
		if($_GET['action']=="get_most_viewed_produts") {
			die(0);
		}
	}

	public function coupons_list_tml() {
		ob_start();
		?>
		<script type="text/html" id="coupons_list_view_tml">
		<div class="my_shop_container">
			<div class="container" style="">
				<h2 class="page-header"><i class="icon ion-ios7-pricetag-outline"></i>Coupons</h2>
				<div class="box-body table-responsive">
				<?php
				$merchant_user = wp_get_current_user();
				if($merchant_user->roles[0] == 'merchant') {
					$user_id = $merchant_user->ID;
					$args = array(
						'post_type' => 'coupon',
						'post_status' => 'publish',
						"order" => "DESC",
						'author' => $user_id,
						'posts_per_page' => -1,
					);
					$coupons_query = new WP_Query($args);
					if($coupons_query->have_posts()) {
						?>
						<table class="table table-bordered table-striped" id="coupons_list">
		                <thead>
                        <tr>
                        <th>Coupon Code</th>
		                <th>Product SKU</th>
		                <th>Product Title</th>
		                <th>Coupon Generated For Details</th>
		                <th>Date</th>
		                <th>Action</th>
                        </tr>
                        </thead>
		                <tbody>
		                <?php
						while($coupons_query->have_posts()) {
							$coupons_query->the_post();
							$coupon_id = $coupons_query->post->ID;
							$coupon_code = $coupons_query->post->post_title;
							$email = get_post_meta( $coupon_id, '_email_address', true );
							$phone = get_post_meta( $coupon_id, '_user_phone', true );
							$date = $coupons_query->post->post_date;
							$product_id = get_post_meta( $coupon_id, '_product_id', true );
							$product_object = get_post($product_id);
							$product_link = get_permalink($product_id);	
							?>
							<tr id="<?php echo 'coupon_row_'.$coupon_id; ?>">
								<td>
								<span>
									<?php
										echo $coupon_code;
									?>
								</span>
								</td>
								<td>
								<span class="bold-text">
									<?php
										if($product_id!="") {
									    	echo get_post_meta($product_id, '_sku', true);
									    } else {
									    	echo '--';
									    }
									?>
								</span>
								</td>
								<td>
								<a href="<?php echo $product_link ?>" target="_blank"><?php echo $product_object->post_title; ?></a>
								</td>
								<td>
									<?php 
										echo '<b>Email ID : </b> <u>'.$email.'</u><br/>';
										echo '<b>Phone No. :</b> <u>'.$phone.'</u>';
									?>
								</td>
								<td>
									<?php 
										echo $date;
									?>
								</td>
								<td>
									<button  class="btn btn-danger remove_product_coupon" data-coupon-id="<?php echo $coupon_id; ?>">Remove</button>	
								</td>
							</tr>
							<?php
						}	
					?>
					</tbody>
					</table>
					<?php 
					} else {
						?>
						<div class="callout callout-info">
                            <h4 style="margin-bottom: 0px;">Coupons not Generated Yet !!!</h4>
                        </div>
						<?php
					}
					wp_reset_postdata();
				}	
				?>
				</div>	
			</div>
		</div>	
		</script>
		<?php
		echo ob_get_clean();
	}


	public function orders_list_tml() {
		ob_start();
		?>
		<script type="text/html" id="orders_list_view_tml">
		<div class="my_shop_container">
			<div class="container" style="">
				<h2 class="page-header"><i class="ion ion-ios7-cart-outline"></i>Orders</h2>
				<div class="box-body table-responsive">
				<?php
				$merchant_user = wp_get_current_user();
				if($merchant_user->roles[0] == 'merchant') {
					$user_id = $merchant_user->ID;
                                    
					$args = array(
						'post_type' => 'shop_order',
						'post_status' => 'publish',
						"order" => "ASC",
//						'author' => $user_id,
						'posts_per_page' => -1,
					);
					$orders_query = new WP_Query($args);
//                                        echo '<pre>';
//                                        print_r($orders_query);
//                                        echo '</pre>';
			if($orders_query->have_posts()) {?>
				<table class="table table-bordered table-striped" id="orders_list">
		                <thead>
                        <tr>
                        <th>Order ID</th>
		                <th>Product Name</th>
		                <th>Order From Details</th>
		                <th>Date</th>
		                <th>Quality</th>
                        </tr>
                        </thead>
		                <tbody>
		                <?php
						while($orders_query->have_posts()) {
							$orders_query->the_post();
							$order_id = $orders_query->post->ID;
                                                        global $wpdb;
                                                        $order = new WC_Order( $order_id );
                                                        $order->billing_email;
                                                        $order->billing_phone;
                                                        
                                                        $items = $order->get_items();
                                                        
                                                        foreach ($items as $item) {
                                                         
                                                        $product_id = $item['product_id'];
                                                        $author_id = get_post_field ('post_author', $product_id);
                                                        if($author_id == $user_id){
                                                           
                                                        $product_name = $item['name'];
                                                        $product_qty = $item['qty'];
							$date = $orders_query->post->post_date; ?>
							<tr id="<?php echo 'order_row_'.$order_id; ?>">
								<td>
								<span class="bold-text">
									<?php
										echo $order_id;
									?>
								</span>
								</td>
								<td>
								<span class="bold-text">
									<?php
										
									    if($product_id!="") {
									    	echo $product_name;
									    } else {
									    	echo '--';
									    }
									?>
								</span>
								</td>
<!--								<td>
								<a href="<?php //echo $product_link ?>" target="_blank"><?php // echo $product_name; ?></a>
								</td>-->
								<td>
									<?php 
										echo '<b>Email ID : </b> <u>'.$order->billing_email.'</u><br/>';
										echo '<b>Phone No. :</b> <u>'.$order->billing_phone.'</u>';
									?>
								</td>
								<td>
									<?php 
										echo $date;
									?>
								</td>
								<td>
									<?php echo $product_qty; ?>
								</td>
							</tr>
							<?php
                                                        }
						}
                                               }
					?>
					</tbody>
					</table>
					<?php 
					} else {
						?>
						<div class="callout callout-info">
                        <h4 style="margin-bottom: 0px;">Orders not Generated Yet !!!</h4>
                        </div>
						<?php
					}
					wp_reset_postdata();
				}	
				?>
				</div>	
			</div>
		</div>	
		</script>
		<?php
		echo ob_get_clean();
	}
	
	public function remove_product_coupon_ajax() {
		$coupon_id = $_GET['coupon_id'];
		if($coupon_id) {
			$deleted = wp_delete_post($coupon_id);
		} else {
			$deleted = false;
		}
		if($deleted==false) {
			$response = 'notdeleted';
		} else {
			$response = 'deleted';
		}
		echo json_encode(array('response'=>$response));
		die(0);		
	}

	public function remove_merchant_orders_ajax() {
		$order_id = $_GET['order_id'];
		if($order_id) {
			$deleted = wp_delete_post($order_id);
		} else {
			$deleted = false;
		}
		if($deleted==false) {
			$response = 'notdeleted';
		} else {
			$response = 'deleted';
		}
		echo json_encode(array('response'=>$response));
		die(0);		
	}

	public function shop_advertisments_template() {
		ob_start();
		$merchant_user = wp_get_current_user();
        $merchant_shop_id = get_user_meta($merchant_user->ID, 'user_shop', true);
        $large_ads = get_post_meta( $merchant_shop_id, '_large_advertisements', true );
		$large_ads_array = explode(",", $large_ads);
		
		$small_ads = get_post_meta( $merchant_shop_id, '_small_advertisements', true );
        $small_ads_array = explode(",", $small_ads);
		?>
		<script type="text/html" id="shop_ads_view_tml">
		<div class="my_shop_container">
		<div class="container" style="">
		<h2 class="page-header"><i class="icon ion-android-archive"></i>Shop Advertisements</h2>
		
		<div id="large_ads_images_container" style="margin-bottom:20px;">
			<div class="shop_large_advertisements shop_advertisements upload_advertisements_images" data-ads-type="large">
					<h4>Click Here For Uploading Shop Large Size Advertisements</h4>
					<div id="large_ads_list_view" style="margin-top:30px">
					<?php
					if(!empty($large_ads_array)) {
						
						foreach($large_ads_array as $ads) {
	          	        if($ads!='') {	
	          	        	$adobject = get_post($ads);
	          	        	if($adobject->post_author == $merchant_user->ID) {
		          	        	$large_ads_url_array = wp_get_attachment_image_src($ads, 'thumbnail', true);
			              		$large_ads_url = $large_ads_url_array[0];	
			              		$largehtml = '';
			              		$largehtml .= '<div class="imagepreview ads_sortables col-sm-2 col-xs-2">';
						        $largehtml .= sprintf('<img src="%s" name="' . $post->post_title . '" class="img-responsive" />', $large_ads_url);
						        $largehtml .= sprintf('<a href="javascript:void(0);" title="Delete Ad" class="ads_image_delete fa-times-circle-o fa" data-upload_id="%d" data-ads-type="large">%s</a>', $ads, __(''));
						        $largehtml .= '</div>';
			              		echo $largehtml;
		              		}
	              		} 
	              		}
              		}
              		?>	
					</div>
					<div class="clearfix"></div>
			</div>
		</div>
		
		<div id="small_ads_images_container" style="margin-bottom:20px;">
			<div class="shop_small_advertisements shop_advertisements upload_advertisements_images" data-ads-type="small">
				<h4>Click Here For Uploading Shop Small Size Advertisements</h4>
				<div id="small_ads_list_view" style="margin-top:30px">
				<?php
				if(!empty($small_ads_array)) {
					
	          	    foreach($small_ads_array as $ads) {
	          	    if($ads!='') {		
	          	    	$adobject = get_post($ads);
	          	        if($adobject->post_author == $merchant_user->ID) {
			              	$small_ads_url_array = wp_get_attachment_image_src($ads, 'thumbnail', true);
			              	$small_ads_url = $small_ads_url_array[0];	
			              	$smallhtml = '';	
			            	$smallhtml .= '<div class="imagepreview ads_sortables col-sm-2 col-xs-2">';
						    $smallhtml .= sprintf('<img src="%s" name="' . $post->post_title . '" class="img-responsive" />', $small_ads_url);
						    $smallhtml .= sprintf('<a href="javascript:void(0);" title="Delete Ad" class="ads_image_delete fa-times-circle-o fa" data-upload_id="%d" data-ads-type="small">%s</a>', $ads, __(''));
						    $smallhtml .= '</div>';
			            	echo $smallhtml;
			           	} 	
	              	}	 
	            	}
	            	
              	}
              	?>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
		

		</div>
		</div>
		</script>
		<?php
		echo ob_get_clean();
	}

	//Upload Shop Ads AJAX Handlers
    public function advertisements_images_upload_ajax() {
        check_ajax_referer('advertisements_images_upload_allow', 'nonce');
        $ads_type = $_REQUEST['ads_type'];
        $file = array(
            'name' => $_FILES['shop_advertise_images']['name'],
            'type' => $_FILES['shop_advertise_images']['type'],
            'tmp_name' => $_FILES['shop_advertise_images']['tmp_name'],
            'error' => $_FILES['shop_advertise_images']['error'],
            'size' => $_FILES['shop_advertise_images']['size']
        );
        $file = $this->fileupload_process($file, $ads_type);
        die(0);
    }

    public function fileupload_process($file, $ads_type)
    {
        $attachment = $this->handle_file($file);
        $merchant_user = wp_get_current_user();
        $merchant_shop_id = get_user_meta($merchant_user->ID, 'user_shop', true);
        if($ads_type == 'large') {
        	$large_ads = get_post_meta( $merchant_shop_id, '_large_advertisements', true );
			$large_ads_array = explode(",", $large_ads);
			if(empty($large_ads_array)) {
				$large_ads_array = array();
			} 
			$large_ads_array[] = $attachment['id'];
			update_post_meta( $merchant_shop_id, '_large_advertisements', implode(",", $large_ads_array) );
        } else {
        	$small_ads = get_post_meta( $merchant_shop_id, '_small_advertisements', true );
        	$small_ads_array = explode(",", $small_ads);
        	if(empty($small_ads_array)) {
				$small_ads_array = array();
			}
        	$small_ads_array[] = $attachment['id'];
			update_post_meta( $merchant_shop_id, '_small_advertisements', implode(",", $small_ads_array) );
        }

        if (is_array($attachment)) {
            $html = $this->getHTML($attachment);
            $response = array(
                'success' => true,
                'html' => $html,
            );
            echo json_encode($response);
            exit;
        }
        $response = array('success' => false);
        echo json_encode($response);
        exit;
    }

    public function handle_file($upload_data)
    {
        $return = false;
        $uploaded_file = wp_handle_upload($upload_data, array('test_form' => false));
        if (isset($uploaded_file['file'])) {
            $file_loc = $uploaded_file['file'];
            $file_name = basename($upload_data['name']);
            $file_type = wp_check_filetype($file_name);
            $attachment = array(
                'post_mime_type' => $file_type['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_name)),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment($attachment, $file_loc);
            $attach_data = wp_generate_attachment_metadata($attach_id, $file_loc);
            wp_update_attachment_metadata($attach_id, $attach_data);
            $return = array('data' => $attach_data, 'id' => $attach_id);
            return $return;
        }
        return $return;
    }

    public function getHTML($attachment)
    {
        $attach_id = $attachment['id'];
        $file = explode('/', $attachment['data']['file']);
        $file = array_slice($file, 0, count($file) - 1);
        $path = implode('/', $file);
        $image = $attachment['data']['sizes']['thumbnail']['file'];
        $post = get_post($attach_id);
        $dir = wp_upload_dir();
        $path = $dir['baseurl'] . '/' . $path;
        $html = '';
        $html .= '<div class="imagepreview ads_sortables col-sm-2 col-xs-2">';
        $html .= sprintf('<img src="%s" name="' . $post->post_title . '" class="img-responsive" />', $path . '/' . $image);
        $html .= sprintf('<a href="javascript:void(0);" title="Delete Ad" class="ads_image_delete fa-times-circle-o fa" data-upload_id="%d">%s</a>', $attach_id, __(''));
        $html .= '</div>';
        return $html;
    }

    public function delete_shop_ads() {
        $attach_id = $_POST['attach_id'];
        $ads_type = $_POST['ads_type'];
        $merchant_user = wp_get_current_user();
        $merchant_shop_id = get_user_meta($merchant_user->ID, 'user_shop', true);
        if($ads_type == 'large') {
        	$large_ads = get_post_meta( $merchant_shop_id, '_large_advertisements', true );
			$large_ads_array = explode(",", $large_ads);
			if(($key = array_search($attach_id, $large_ads_array)) !== false) {
    			unset($large_ads_array[$key]);
			}
			update_post_meta( $merchant_shop_id, '_large_advertisements', implode(",", $large_ads_array) );
        } else {
        	$small_ads = get_post_meta( $merchant_shop_id, '_small_advertisements', true );
        	$small_ads_array = explode(",", $small_ads);
        	if(($key = array_search($attach_id, $small_ads_array)) !== false) {
    			unset($small_ads_array[$key]);
			}
			update_post_meta( $merchant_shop_id, '_small_advertisements', implode(",", $small_ads_array) );
        }
        wp_delete_attachment($attach_id, true);
        die(0);
    }

}

new View_My_Shop();

