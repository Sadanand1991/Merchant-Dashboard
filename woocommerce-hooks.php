<?php
/**
* MM Woocommerce Hooks Class
*/



class WoocommerceHooks {

	public function __construct() {

		//Custom Filter/Action for Edit Columns in Product CPT
		remove_filter('manage_edit-product_columns' , 'edit_columns', 9999);        
		add_filter('manage_edit-product_columns' , array($this, 'mm_edit_columns'), 9999999);
		add_action( 'manage_product_posts_custom_column', array($this, 'mm_show_product_data_columns'), 999999999 );

		//Filter Register CPT Product Arguments
		add_filter( 'woocommerce_register_post_type_product', array(&$this, 'mm_override_woo_product_cpt_args'), 10);

		//Set Product View hook on wp_head hook
		add_action('wp_head', array($this, 'setProductViews'), 10);
        
        add_action('wp_ajax_get_trending_products', array($this, 'getTrendingproducts') );
        add_action('wp_ajax_nopriv_get_trending_products', array($this, 'getTrendingproducts') );

        add_action('wp_ajax_load_product_modal', array($this, 'productpreviewmodal') );
        add_action('wp_ajax_nopriv_load_product_modal', array($this, 'productpreviewmodal') );

        add_filter( 'wp_terms_checklist_args', array($this, 'checked_not_ontop'), 1, 2 );

//        add_action("wp_footer", array($this, "size_chart"));
    }

	public function mm_override_woo_product_cpt_args($post_type) {
        $post_type['has_archive'] = true;
        return $post_type;
    }

    public function checked_not_ontop( $args, $post_id ) {
	    if ( 'product' == get_post_type( $post_id )  )
	        $args['checked_ontop'] = false;
	    return $args;
	}

	public function mm_edit_columns( $existing_columns ) {
		$existing_columns = array();
		$columns = array();
		unset($columns['sku']);
		$columns['cb'] = '<input type="checkbox" />';
		$columns['thumb'] = '<span class="wc-image tips" data-tip="' . __( 'Image', 'woocommerce' ) . '">' . __( 'Image', 'woocommerce' ) . '</span>';

		$columns['product_sku']  = __('SKU', 'woocommerce');
		$columns['name'] = __( 'Name', 'woocommerce' );

		if ( wc_product_sku_enabled() ) {
                    //$columns['sku'] = __( 'SKU', 'woocommerce' );
		}

		if ( 'yes' == get_option( 'woocommerce_manage_stock' ) ) {
                    //$columns['is_in_stock'] = __( 'Stock', 'woocommerce' );
		}

//		$columns['undershop'] = __( 'Shop', 'woocommerce' );
//		$columns['undermall'] = __( 'Mall', 'woocommerce' );
//		$columns['undercity'] = __( 'City', 'woocommerce' );
                $columns['featured'] = __( '<span class="wc-featured parent-tips" data-tip="Featured">Featured</span>', 'woocommerce' );
		$columns['shoppers_name'] = __( 'Shopkeeper', 'woocommerce' );
		
		$columns['product_views'] = __( 'Product Views', 'woocommerce' );
		$columns['date'] = __( 'Date', 'woocommerce' );
                
		return array_merge( $columns, $existing_columns );

	}

	public function mm_show_product_data_columns( $column ) {
        global $post, $woocommerce, $the_product, $wpdb;

		if ( empty( $the_product ) || $the_product->id != $post->ID ) {
			$the_product = get_product( $post );
		}

		switch ( $column ) {
			case 'product_sku' :
				$product_sku = get_post_meta($the_product->id,'_sku', true);
				if($product_sku) {
					echo $product_sku;
				} else {
					echo "--";
				}
			break;	
 			case 'undermall' :
                $mall_id = get_post_meta($the_product->id, 'mall', true);
                if($mall_id) {
	                $mall = get_post($mall_id);
					echo $mall->post_title;
				}
			break;
			case 'undershop' :
                $shop_id = get_post_meta($the_product->id, 'shop', true);
                if($shop_id) {
	                $shop = get_post($shop_id);
					echo $shop->post_title;
				}
			break;
			case 'undercity' :
                $city = get_post_meta($the_product->id, 'city', true);
                if($city) {
	                $cityobj = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_name = '".$city."' and post_type = 'city'", OBJECT );
					if($cityobj) {
						echo $cityobj[0]->post_title;
					}
				}
			break;
			case 'shoppers_name' :
                $product_author = $post->post_author;
                $shoppers_name = get_user_meta($product_author, 'first_name', true)." ".get_user_meta($product_author, 'last_name', true);
				echo $shoppers_name;
			break;
			case 'product_views' :
                $product_views = get_post_meta($the_product->id, 'product_views', true);
                if(!$product_views) {
                	echo '0';
                } else {
                	echo $product_views;
            	}
			break;
                
			default :
				break;
		}
	}


	public function setProductViews() {
		global $post;
		$postID = $post->ID;
		if(is_singular('product')) {
		    $count_key = 'product_views';
		    $count = get_post_meta($postID, $count_key, true);
		    if($count==''){
		        $count = 0;
		        delete_post_meta($postID, $count_key);
		        add_post_meta($postID, $count_key, '1');
		    }else{
		        $count++;
		        update_post_meta($postID, $count_key, $count);
		    }
		}
	}


	public function product_slider($productid) {
		$sliderhtml = "";
		$sliderhtml .='<div class="item active">';
		$sliderhtml .= get_the_post_thumbnail($productid, 'full');
		$sliderhtml .= '</div>';
		
		$images = get_post_meta($productid, '_product_image_gallery', true);
		$images_array = explode(",", $images);
	    if ( $images!="" ) {
	        foreach ( $images_array as $image ) {
	           $sliderhtml .='<div class="item">';
	           $sliderhtml .= wp_get_attachment_image( $image, 'full' );
	           $sliderhtml .= '</div>';
	          }
	    }
		return $sliderhtml;
	}

	public function mobile_product_slider($productid) {
		$sliderhtml = "";
		$thumb_id = get_post_thumbnail_id($productid);
		$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full', true);
		$thumb_url = $thumb_url_array[0];
		$sliderhtml .='<a href="'.$thumb_url.'" class="cloud-zoom" id="productimgzoom1" rel="">';
		$sliderhtml .= '<img class="" src="'.$thumb_url.'" />';
		$sliderhtml .= '</a>';
		
		$images = get_post_meta($productid, '_product_image_gallery', true);
		$images_array = explode(",", $images);
	    if ( $images!="" ) {
	    	$i=2;
	        foreach ( $images_array as $image ) {
	           $image_array = wp_get_attachment_image_src($image, 'full', true);
			   $image_url = $image_array[0];
			   $image_url_resize = get_template_directory_uri()."/timthumb.php?src=".$image_url."&h=315&w=290&zc=2";
	           $sliderhtml .='<a href="'.$image_url.'" class="cloud-zoom" id="productimgzoom'.$i.'" style="display: none;" rel="">';
	           $sliderhtml .= '<img class="" src="'.$image_url_resize.'" />';
	           $sliderhtml .= '</a>';
	           $i++;
	        }
	    }
		return $sliderhtml;
	}

	//Set Trending Products Query
	public function set_trending_products_query($city, $excludeproducts = array(), $posts_per_page = 9) {
		$viewsargs = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			"meta_key" => "product_views",
		    "orderby" => "meta_value",
		    "order" => "DESC",
			'meta_query' => array(
		        array(
		            'key' => 'city',
		            'value' => $city,
		            'compare'       => '==',
                    'type'          => 'CHAR'
		        ),
		    ),
			'posts_per_page' => 9,
		);
		$views_product_query = new WP_Query($viewsargs);

		$args = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'meta_query' => array(
		        array(
		            'key' => 'city',
		            'value' => $city,
		            'compare'       => '==',
                    'type'          => 'CHAR'
		        ),
		    ),
		    'orderby' => 'rand',
			'post__not_in' => $excludeproducts,
			'posts_per_page' => $posts_per_page,
		);
		$product_query = new WP_Query($args);

		$countargs = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'meta_query' => array(
		        array(
		            'key' => 'city',
		            'value' => $city,
		            'compare'       => '==',
                    'type'          => 'CHAR'
		        ),
		    ),
		    'orderby' => 'rand',
			'posts_per_page' => -1,
		);
		$total_products_query = new WP_Query($countargs);

		return array($views_product_query, $product_query, $total_products_query);
	} 

	public function singleTrendingProductHtml($product_id) {
		ob_start();
		global $MMGlobalFunctions;
		$product_price = get_post_meta($product_id,'_regular_price', true);
		$product_sale_price = get_post_meta($product_id,'_sale_price', true);
		$product_discount = get_post_meta($product_id,'_product_discount', true);
		$currency = 'Rs. ';
		$terms = get_the_terms( $product_id, 'product-discount' );
		$mall_id = get_post_meta($product_id,'mall', true); 
		$brands = get_the_terms( $product_id, 'brand');
	    $company = get_the_terms( $product_id, 'company');
	    $genre = get_the_terms( $product_id, 'genre');
	    $productbcg = "";
	    if(!$brands->errors && $brands){ 
	    	$i = 1;
	    	foreach ( $brands as $brand ) { if($i == 1){ $productbcg = $brand->name; }  $i++; } 
		}  
	    elseif(!$company->errors && $company) {
	        $j = 1;
	        foreach ($company as $company) {  if($j == 1){ $productbcg = $company->name; } $j++; }
	    } 
	    elseif(!$genre->errors && $genre) { 
	    	$k = 1; 
	    	foreach ($genre as $genre) { if($k == 1){ $productbcg = $genre->name; } $k++; }
	    }
	    else { }
		?>
		<div class="product_outer_wrapper col-sm-4">
			<div class="product_inner_wrapper">
				<?php	if ( has_post_thumbnail() ) { 
				$attach_id = get_post_thumbnail_id($product_id);
	    	        $width = '290';
	    	        $height = '315';
	    	        $image = $MMGlobalFunctions->vt_resize( $attach_id, '', $width, $height, true );
				//$productimage = wp_get_attachment_url( get_post_thumbnail_id($product_id) ); ?>
				<div class="product_image product_preview" data-toggle="modal" data-target="#productview<?php echo $product_id; ?>" data-product-id="<?php echo $product_id; ?>">
				 <img src="<?php echo $image[url]; ?>" width="<?php echo $image[width]; ?>" height="<?php echo $image[height]; ?>" class="img-responsive" />
				<!-- <img src="<?php //echo $productimage; ?>" class="img-responsive"/>-->
				<?php if($product_discount !=''){ ?>
				<div class="product_discount"><?php echo $product_discount.' off'; ?></div>
				<?php } 
				else{ }
				?>
				<!-- <div class="product_view product_preview" data-toggle="modal" data-target="#productview<?php //echo $product_id; ?>" data-product-id="<?php //echo $product_id; ?>" >Preview</div> -->
				<div class="product_view product_view_loader" style='display:none; background: #ccc;'>Preview Loading...</div>
				</div>
				<?php } ?>
				 <div class="product_price">
	                <?php if($product_sale_price!='') { ?>
	                <span class="discounted_price col-sm-6"><?php if($product_sale_price != ''){ echo $currency . $product_sale_price."/-" ;}?></span>
	                <span class="original_price col-sm-6"><?php if($product_price != ''){ echo $currency . $product_price."/-"; }?></span>
	                <?php } else { ?>
	                <span class="regular_product_price"><?php if($product_price != ''){ echo $currency . $product_price."/-" ;}?></span>
	                <?php } ?>
	                <div class="clearfix"></div>
	            </div>
				<div class="product_title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</div>
				<div class="product_shop_mall">
				<?php 
				$m_bcg_col = 'col-sm-12';
				if($productbcg) { ?>
				<div class="product_shop col-sm-6">
					<?php 
						echo $productbcg; 
						$m_bcg_col = 'col-sm-6';
					?>
				</div>
				<?php } ?>
				<div class="product_mall <?php echo $m_bcg_col; ?>"><?php if($mall_id != ''){ echo get_the_title($mall_id); }?></div>
				<div class="clearfix"></div>
			</div>
		</div>
		</div>
		<?php	
		return ob_get_clean();
	}

	//Get Trending Products
	public function getTrendingproducts() {
		ob_start();
		$city = $_GET['city'];  
		
		$queries = $this->set_trending_products_query($city);
		
		if($queries[0]->found_posts) {
			$product_query = $queries[0];
		} else if($queries[1]->found_posts) {
			$product_query = $queries[1];
		}
	 
		if($product_query->have_posts()) {
			$excludeproducts = array();
			?>
			<div class="section_heading"><?php _e('Trending Products','memalling') ?> </div>
			<?php
			while ( $product_query->have_posts() ) { 
				$product_query->the_post(); 
				$product_id = $product_query->post->ID;
				echo $this->singleTrendingProductHtml($product_id);
				$excludeproducts[] = $product_id;
			}

			
			if($queries[0]->post_count < 9) {
				$posts_per_page = $queries[0]->post_count - 9;
				$posts_per_page = abs($posts_per_page);
				$random_product_query = $this->set_trending_products_query($city, $excludeproducts, $posts_per_page);
				
				if($random_product_query[1]) {
					$random_product_query = $random_product_query[1];
					while( $random_product_query->have_posts() ) {
						$random_product_query->the_post();
						$product_id = $random_product_query->post->ID;
						echo $this->singleTrendingProductHtml($product_id);
					}
				}
			}
			?>
			<div class="clearfix"></div>
			<?php 
			$total_product_count_query = $queries[2];
			if($total_product_count_query->found_posts>9) {  
			?>
				<a href="<?php echo site_url().'/products/'.$city.'/'; ?>" class="button_viewall btn btn-default">View All</a>
			<?php 
			} 	
		}
		wp_reset_postdata();
		$product_query = null;
		$trendingproduts = ob_get_clean();
		echo json_encode( array('products'=>$trendingproduts) );
		die(0);
	}

	//Render Product Preview Modal
	public function productpreviewmodal() { 
		ob_start(); 
		global $yith_wcwl, $theme_settings;
		$productid = $_GET['productid']; 
		$loading_location = $_GET['loading_location']; 
		$temp = $post;
		$productpost = get_post( $productid );
		setup_postdata( $post );
		$product_sku = get_post_meta($productid,'_sku', true);
		$product_price = get_post_meta($productid,'_regular_price', true);
		$currency = 'Rs. ';
		$product_sale_price = get_post_meta($productid,'_sale_price', true);
		$product_discount = get_post_meta($productid,'_product_discount', true);
		$images = get_post_meta($productid, '_product_image_gallery', true);
		$terms = get_the_terms( $productid, 'product-discount' );
		$product_author = $productpost->post_author;
		$product_deal = get_post_meta($productid,'product_deal', true);
		$product_mallid = get_post_meta($productid,'mall',true);
		$product_mall = get_the_title($product_mallid);
		$product_shopid = get_post_meta($productid,'shop',true);
		$product_shop = get_the_title($product_shopid);
		$merchant_preview_product = '';
		$content = $productpost->post_excerpt;
		$phoneno = get_post_meta($product_shopid, 'shop_phone_num', true);
		$size_chart = get_post_meta($productid, 'clothing_size_chart', true);
		
                $product_view_count = get_post_meta($productid, 'product_views', true);
                $product_view_count++;
                update_post_meta($productid, 'product_views', $product_view_count);
                
		$lat1 = isset($_COOKIE['me_lat']) ? $_COOKIE['me_lat'] : '';
		$lng1 = isset($_COOKIE['me_lng']) ? $_COOKIE['me_lng'] : '';
		$lat2 = get_post_meta($product_shopid, 'latitude', true);
		$lng2 = get_post_meta($product_shopid, 'longitude', true);
		$unitdistance = 'K';

//		$distance_flag = calc_distance($lat1, $lng1, $lat2, $lng2, $unitdistance);		

		if($loading_location=='merchant') {
			$merchant_preview_product = 'merchant_preview_product';
		}
		$attributes = get_post_meta($productid);
		$url = site_url().'/wp-content/plugins/yith-woocommerce-wishlist/yith-wcwl-ajax.php?action=mm_add_to_wishlist&add_to_wishlist='.$productid;
		?>
		<div class="product_view_wrap <?php echo $merchant_preview_product; ?>">
			<div class="modal fade" id="productview<?php echo $productid; ?>" tabindex="-1" role="dialog" aria-labelledby="productviewmodal" aria-hidden="true">
			  <div class="modal-dialog modal-lg">
			    <div class="modal-content">
				    	<div class="modal-header">
				    		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><img src="<?php echo  plugins_url();?>/merchant-dashboard/assets/images/close-btn-prod.png"></span><span class="sr-only"></span></button>
				        </div>
				        <div class="modal-body">
					        <div class="product_info row">
					        	<?php 
					        	$urlview = get_permalink($theme_settings['wishlistpage']);
					        	if(!$yith_wcwl->is_product_in_wishlist( $productid )) { ?>
					      	  	<div class="product_wishlist add_product_to_wishlist" style="cursor:pointer;" data-action="<?php echo $url; ?>" title="Add To Wishlist"><img src="<?php echo  plugins_url();?>/merchant-dashboard/assets/images/icon-wishlist.png" /><i class="fa fa-cog fa-spin wishlistajaxloader"></i></div>
					      	  	<a href="<?php echo $urlview; ?>" class="product_wishlist_added" title="View Wishlist" style="cursor:pointer; display: none;"><img src="<?php echo  plugins_url();?>/merchant-dashboard/assets/images/icon-wishlist-red.png" /></a>
					      	  	<?php } else { ?>
					      	  	<a href="<?php echo $urlview; ?>" class="product_wishlist_added" title="View Wishlist" style="cursor:pointer; text-decoration: none;"><img src="<?php echo  plugins_url();?>/merchant-dashboard/assets/images/icon-wishlist-red.png" /></a>
					      	  	<?php } ?>
					      	  	<div class="clearfix"></div>
						      	  	<div class="product_slider col-sm-4">
						      	  	  <div class="row">
<!--							      	  	   <div class="vericalwrap col-sm-4">
								      	  		<ul id="productvertical<?php echo $productid; ?>" class="elastislide-list">
								      	  		 	<?php
//								      	  		 		$sliderhtml = '';
//								      	  		 		$thumb_id = get_post_thumbnail_id($productid);
//								      	  		 		$first_thumb_url_array = wp_get_attachment_image_src($thumb_id, 'thumbnail', true);
//														$first_thumb_url = $first_thumb_url_array[0];	
//													    $sliderhtml .='<li slideto="0" style="cursor: pointer;" class="active_product_thumb">';
//													    $sliderhtml .= '<img src="'.$first_thumb_url.'" />';
//													    $sliderhtml .= '</li>';
//								      	  		 		
//								      	  		 		$images = get_post_meta($productid, '_product_image_gallery', true);
//														$images_array = explode(",", $images);
//													    if ( $images!="" ) {
//													    	$ic=1;
//													        foreach ( $images_array as $image ) {
//													           $thumb_url_array = wp_get_attachment_image_src($image, 'thumbnail', true);
//															   $thumb_url = $thumb_url_array[0];	
//													           $sliderhtml .='<li slideto="'.$ic.'" style="cursor: pointer;">';
//													           $sliderhtml .= '<img src="'.$thumb_url.'" />';
//													           $sliderhtml .= '</li>';
//													           $ic++;
//													        }
//													    } 
//													    echo $sliderhtml;
								      	  		 	?>
								      	  		</ul>  carousevertical end 
							      	    	</div>-->
							      	  		<div class="full_product_slider col-sm-12">
							      	  			<!-- <div id="carousel-product<?php echo $productid; ?>" class="carousel slide" data-ride="carousel">
							      	  				<div class="carousel-inner">
													 	<?php //echo $this->product_slider($productid); ?>
													</div> 
							      	  			</div> -->
							      	  			<div class="product_images_slider">
													<?php echo $this->mobile_product_slider($productid); ?>
												</div>						      	  			

							      	  		</div> <!-- full_product_slider end -->
							      	  	</div>
						      	  	</div> <!-- product_slider end -->
						      	  	<div class="product_description col-sm-8">
						      	  		
						      	  		<h2 class="product_title"><a href="<?php echo get_permalink($productid); ?>"><?php echo get_the_title($productid); ?></a></h2>
						      	  		
						      	  		<?php if($product_sku != ''){ ?>
											<div class="product_id">Product Id: <span><?php echo $product_sku; ?></span></div> <!-- product_id end -->
										<?php } ?> 
										
										<?php //if($product_mall != ''){ ?>
											<!--<div class="product_mall"><i class="fa fa-building-o"></i><b>  Location:</b> <span><?php //echo $product_mall; ?></span></div>  product_id end -->
										<?php //} ?>

						      	  		<?php if($product_shop != ''){ ?>
											<div class="product_shop"><i class="fa fa-bank"></i><b> Shop:</b> <span><?php echo $product_shop; ?></span></div> <!-- product_id end -->
										<?php } ?>
										
										<?php if($phoneno != ''){ ?>
											<!--<div class="phoneno"><i class="fa fa-phone"></i><b>  Phone No:</b> <span><?php // echo $phoneno; ?></span></div>  product_id end -->
										<?php } ?>
						      	  		<div class="product_size">
						      	  			<?php 
//						      	  				if(!empty($attributes) || $attributes!="") {
//													foreach ( $attributes as $attribute ) :
//													$taxonomy_object = get_taxonomy( $attribute['name'] );
//				            						if($attribute['name'] == 'pa_size' || $attribute['name'] == 'pa_shoes-sizes'){	
//					            						$label = $taxonomy_object->label;		
//														echo 'Size: '; 
//														if ( $attribute['is_taxonomy'] ) {
//															$values = wc_get_product_terms( $productid, $attribute['name'], array( 'fields' => 'names', 'orderby' => 'term_order' ) );
//															asort($values);
//															foreach ($values as $value) {
//																echo '<span class="size" style="cursor:pointer;">' .$value.'</span>';
//															}
//														} else {
//															// Convert pipes to commas and display values
//															$values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
//															echo '<span class="size" style="cursor:pointer;">' .implode( ', ', $values ).'</span>';
//														}
//														if(trim(strip_tags($size_chart)) !=''){
//															echo '<p class="sizechartlink" style="margin-top: 10px;"><a href="'.get_permalink($productid).'#sizechart">Size Help</a></p>';	
//														}
//
//													}
//													else{}
//													endforeach; 
//												} ?>
												<!--<input type="hidden" class="selected_csize" value="" />-->
						      	  		</div><!-- product_size end -->

						      	  		<?php
//						      	  		if($distance_flag!='error') {
//						      	  		?>	
<!--						      	  		<div class="distance_from_your_location">
						      	  			<label>Distance From Your Location : </label> //<?php // echo round($distance_flag, 2).' KM'; ?>
						      	  			<a href="https://maps.google.com?saddr=//<?php // echo $lat1.','.$lng1; ?>&daddr=<?php // echo $lat2.','.$lng2; ?>" target="_blank">Get Direction</a>
						      	  		</div>-->
						      	  		<?php
//						      	  		}
						      	  		?>
						      	  			
						      	  		<?php if($content !='' ){ ?>
							      	  		<div class="product_detail_info">
							      	  			
								      	  			<div class="show_product_detailed_desc" change_desc_text1="Detailed Product Description" change_desc_text2="Show Detailed Product Description..." open_close="open" style="cursor: pointer;">Show Detailed Product Description...</div>
								      	  			<div class="product_detail_info_inner" style="display: none;">
								      	  				<?php 								      	  				
//								      	  				if(!empty($attributes) || $attributes!="") {
//                                                                                                                        echo '</pre>';
////                                                                                                                        print_r($attributes);
//                                                                                                                        echo '</pre>';
//															foreach ( $attributes as $key => $attribute ) :
////															$taxonomy_object = get_taxonomy( $attribute['name'] );
//                                                                                                                            
//                                                                                                                        //$label = $taxonomy_object->label;
//                                                                                                                            if(!empty($attribute[0])){
//																echo '<div><b>'.htmlspecialchars($key).' :- </b>'; 
//                                                                                                                                    foreach ( $attribute as $attribute) :
//                                                                                                                                     echo $attribute;  
//                                                                                                                                    endforeach;
//																echo '</div>';
//                                                                                                                            }
//															endforeach; 
//														} 
                                                                                                                
								      	  				if($content != ''){ ?>
								      	  				<div><b>Detailed:- </b> <?php 
								      	  					$words = explode(" ",$content);
															echo '<div class="popup-details">'; echo implode(" ",array_splice($words, 0 , 25))."... <a href='".get_permalink($productid)."'>Read More</a>"; echo '</div>';
								      	  				?> </div>
								      	  				<?php } ?>
								      	  			</div>
								      	  		
								      	  	</div> <!-- product_detail_info end -->
							      	  	<?php } else{ } ?>
						      	  		<div class="product_price">
						      	  			<?php
                                                                                setlocale(LC_MONETARY, 'en_IN');
                                                                                $product_price1 = money_format('%!.0n', $product_price);
                                                                                if($product_sale_price!='') {
                                                                                    
                                                                                    $product_sale_price1 = money_format('%!.0n', $product_sale_price);
                                                                                    
                                                                                    ?>
	                    						<div class="discount_price col-sm-4 col-md-3 col-xs-4"><?php if($product_sale_price != ''){ echo $currency . $product_sale_price1."/-" ;}?></div>
	                   							 <div class="original_price col-sm-4 col-md-3 col-xs-4"><?php if($product_price != ''){ echo $currency . $product_price1."/-"; }?></div>
	                    					<?php } else { ?>
	                    					<div class="discount_price col-sm-4 col-md-3 col-xs-4"><?php if($product_price != ''){ echo $currency . $product_price1."/-" ;}?></div>
	                    					<?php } ?>
											<?php if($product_deal!=''){$dealcol = 'col-xs-5';} else{$dealcol = 'col-xs-4';} ?>
						      	  			<div class="discount col-sm-4 col-md-6 <?php echo $dealcol; ?>">
							      	  			<?php 
		                   						if($product_deal!='') { ?>
							      	  				<span><?php echo $product_deal; ?></span>
							      	  			<?php
		                    					}
		                    					else if($product_discount !=''){ ?>
		                    						<span><?php echo $product_discount.''; ?> </span>
							      	  			<?php } 
		                    					else{}
		                        				?>
						      	  			</div>
						      	  		</div>
						      	  		<div class="clearfix"></div>
										<div class="product_malling_delivery">
											<div class="product_malling product_malling_delivery_tooltip col-sm-6 col-xs-6" data-action="malling" data-toggle="tooltip" data-placement="top" title="Generate coupon and pick up ur order">Malling</div>
											<div class="product_delivery product_malling_delivery_tooltip col-sm-6 col-xs-6" data-action="gd" data-toggle="tooltip" data-placement="top" title="Get it delivered with Rs 100 Laziness Charge">Feeling Lazy</div>
											<div class="clearfix"></div>
										</div> <!-- end product_malling_delivery -->
										<div class="clearfix"></div>
										<div class="product_enquiry col-sm-12">
											<span>How do we connect with you? </span>
											<ul class="success_error"></ul>
											<form class="get_coupon" action="">
												<p><input type="text" name="user_phone" placeholder="enter your phone number" /></p>
												<p class="product_enquiry_option">or</p>
												<p><input type="text" name="user_email" placeholder="enter your email address" required /></p>
												<p class="product_enquiry_button">
													<i class="fa fa-cog fa-spin cgd_loader"></i>
													<button type="submit" class="btn btn-primary malling_gd" data-malling="Generate Coupon" data-gd="Shopkeeper will connect with you">Generate Coupon</button>
												</p>
												<input type="hidden" name="product_author" value="<?php echo $product_author; ?>" />
												<input type="hidden" name="product_id" value="<?php echo $productid; ?>" />
												<input type="hidden" name="form-action" class="form-action" value="malling" />
											</form>
										

										</div><!-- end product_enquiry-->
										<div class="clearfix"></div>

						      	  	</div> <!-- product_description end -->
						      	
				      	    </div><!-- product_info end -->
				        </div> <!-- end modal-body -->
			        </div> <!-- end modal-content -->
			    </div><!-- end modal-dialog -->
			</div><!-- end modal -->
		</div><!-- end product_view_wrap -->

		<?php	
		wp_reset_postdata();
		$post = $temp;
		$modal = ob_get_clean();
		echo json_encode(array('modal'=>$modal));
		die(0);
	}

	
	public function size_chart() { 
		if(is_singular('product')) {
		global $post;
		$productid = $post->ID;

		$size_chart = get_post_meta($productid, 'clothing_size_chart', true);
		$measurement_guides = get_post_meta($productid, 'measurement_guides', true);
		$size_chart_image_id = get_post_meta($productid,'size_chart_image', true);
		$size_chart_thumb_array = wp_get_attachment_image_src($size_chart_image_id, 'full', true);
		$size_chart_thumb_url = $size_chart_thumb_array[0];	
		
		?>	
		<div class="sizechart_outer_wrap">
			<div class="modal fade" id="sizechart" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			    <div class="modal-dialog modal-lg">
				    <div class="modal-content">
					      <div class="modal-header">
						    	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                                                                <!--<img src="<?php // echo get_template_directory_uri(); ?>/assets/img/close-btn-prod.png">-->
                                                            </span><span class="sr-only"></span></button>
						  </div>
					      <div class="modal-body">
					        <div class="sizechart_inner_wrap">
					        	<h3>Size Chart</h3>
					        	<ul class="nav nav-tabs" role="tablist" id="SizeChartTab">
					        	  <?php if(trim(strip_tags($size_chart)) !=''){ ?>
								      <li class="active"><a href="#sizeoption" role="tab" data-toggle="tab">Size Options</a></li>
								  <?php } 
								  if(trim(strip_tags($measurement_guides)) != ''){ ?>
								  	<li><a href="#measurementguides" role="tab" data-toggle="tab">Measurement Guides</a></li>
								  <?php } ?>

								</ul>

								<!-- Tab panes -->
								<div class="tab-content">
								  <div class="tab-pane active" id="sizeoption">
								  		<div class="row">
								  		<?php if($size_chart_image_id !=''){$col = 'col-sm-7'; } 
								  			else{$col = 'col-sm-12';}
								  		?>
								  		<div class="<?php echo $col; ?>">
								  			<?php echo $size_chart; ?>
								  		</div>
								  		<?php if($size_chart_image_id != ''){ ?>
								  			<div class="col-sm-5"><img src="<?php echo $size_chart_thumb_url; ?>" /></div>
								  		<?php } ?>
								  	</div>
								  </div>
								 <?php  if(trim(strip_tags($measurement_guides)) !=''){ ?>
								  <div class="tab-pane measurement" id="measurementguides">
								  	<div class="row">
								  		<?php if($size_chart_image_id !=''){$col = 'col-sm-7'; } 
								  			else{$col = 'col-sm-12';}
								  		?>
								  		<div class="<?php echo $col; ?>">
								  			<?php echo $measurement_guides; ?>
								  		</div>
								  		<?php if($size_chart_image_id != ''){ ?>
								  			<div class="col-sm-5"><img src="<?php echo $size_chart_thumb_url; ?>" /></div>
								  		<?php } ?>
								  	</div>
								  </div>
								  <?php } ?>
								</div>
					        </div>
					      </div>
				      
				    </div>
			    </div>
			</div>
		</div>
		<?php ob_start();

		echo ob_get_clean();
		}
	}
	
}

$GLOBALS['MMWooFunctions'] = new WoocommerceHooks();