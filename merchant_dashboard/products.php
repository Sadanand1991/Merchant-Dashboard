<?php
/**
* Class Merchant Products
*/

class Products {

	public function __construct() {
		add_action('wp_footer', array($this, 'products_template'));
		add_action('wp_ajax_show_merchant_products', array($this, 'show_merchant_products_ajax'));
		add_action('wp_ajax_delete_merchant_product', array($this, 'delete_merchant_product_ajax'));
		add_action('wp_ajax_search_merchant_products', array($this, 'search_merchant_products'));
		add_action('wp_ajax_filter_by_categories', array($this, 'filter_by_categories_ajax'));
	}

	public function products_template() {
		ob_start();
		$theme_setttings = unserialize(get_option('global_theme_settings'));
//		$merchant_dashboard = get_permalink($theme_setttings['merchantdashboard']);
		?>
		<script type="text/html" id="merchant_products_tml">
		<div id="merchant_products">
			<div class="products_container">
				<div class="container products_container_info">
					<h2 class="page-header"><i class="ion ion-bag"></i>Products</h2>
					
<!--					<div class="row search_merchant_products">
						<div class="col-sm-3">
							<a class="goto_products_listing btn btn-danger" href="<?php echo $merchant_dashboard; ?>#/products/">Goto Product Listing</a>
						</div>

						<div class="col-sm-offset-4 col-sm-5">
						<div class="search_filter">
                        		<input type="checkbox" name="bysku" id="bysku" value="yes" style="margin:0px;" /><label for="bysku" style="vertical-align: middle; cursor: pointer; margin-left: 5px; font-weight: normal;">By SKU</label>
                        </div>
						<div class="input-group">
                            <input type="text" class="form-control" placeholder="Search Products..">
                            <span class="input-group-btn search_button"><button type="button" class="btn btn-info btn-flat"><i class="fa fa-search"></i></button></span>

                        </div>
						</div>
					</div>-->

					<div class="row">
						<div class="col-sm-2" id="merchant_products_category_filters">
							<div class="inner_wrap" style="background: #f2f2f2;color: #111;padding: 15px;">
							<?php echo $this->categories_filters(); ?>
							</div>
						</div>
						<div id="products_list_view" class="col-sm-10">
                                                    
						</div>
					</div>

					<div id="merchant_products_pagination">
					  
					</div>

				</div>
			</div>
		</div>		
		</script>
		<?php
		echo ob_get_clean();
	}

	public function categories_filters() {
        ob_start();

        $html = "";

        $taxonomies = get_taxonomies(array('public' => true, '_builtin' => false));
//       $html .= '<h4 style="font-size: 15px; margin-top:0px;">Filter By Categories</h4>';
        foreach ($taxonomies as $taxonomy) {
            if($taxonomy != 'diamonds' && $taxonomy != 'carats' && $taxonomy != 'colour' && $taxonomy != 'cut' && $taxonomy != 'clarity' && $taxonomy != 'metal' && $taxonomy != 'metal_purity' && $taxonomy != 'sets' && $taxonomy != 'collections' && $taxonomy != 'specials' && $taxonomy != 'vendor_diamond' && $taxonomy != 'product_tag' && $taxonomy != 'featured_item_category'){
            $terms = get_terms($taxonomy, 'orderby=count&hide_empty=0');
            if (!empty($terms) && !is_wp_error($terms)) {

                $html .= '<div class="products_cat_filters taxonomy_search_container" style="margin-bottom: 10px;">';
                $html .= '<div style="margin-bottom: 5px;"><b>Filter By ' . $taxonomy . '</b></div>';
                foreach ($terms as $term) {

                    $html .= '<div class="products_cat_filters" data-taxonomy-name ="'.$taxonomy.'">';
                    $html .= '<input type="checkbox" name="product_categories" value="' . $term->slug . '" id="' . $term->slug . '" style="margin-right:5px; vertical-align: sub;"><label style="font-size:12px;" for="' . $term->slug . '">' . $term->name . '</label><i class="fa fa-cog fa-spin catfilterloader" style="color: #eb0d7d; margin-left: 5px; display: none;"></i>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
        }
    }
        $html .= ob_get_clean();
        return $html;
    }

    public function filter_by_categories_ajax() {
		$cats = $_POST['cats'];
                $taxonomies = $_POST['taxonomy'];
//                $taxonomy = $_GET['cats'];
		$author = wp_get_current_user();
		$args = array(
			'post_type' => 'product',
			'author' => $author->ID,
			'posts_per_page' => -1,
		);
                if (!empty($taxonomies)) {
            $tax_args = array('relation' => 'AND');
            foreach ($taxonomies as $taxonomy) {
                $terms = get_terms($taxonomy);
                foreach ($terms as $term) {
                    if (in_array($term->slug, $cats)) {
                        $tax_args[] = array(
                            'taxonomy' => $taxonomy,
                            'field' => 'slug',
                            'terms' => array($term->slug),
                        );
                    }
                }
            }
        }
        $args['tax_query'] = $tax_args;
                
		$products = "";
		$product_query = new WP_Query($args);
//                echo '<pre>';
//                print_r($product_query);
//                echo '</pre>'; 
//                echo '<br />';
                
		if( $product_query->have_posts() ) {
			while ( $product_query->have_posts() ) { 
					$product_query->the_post(); 
					$product_id = $product_query->post->ID;
					$products .= $this->product_view($product_id);
			}
		}
		wp_reset_postdata();
		$pagination = "";
		echo json_encode(array('products'=>$products, 'pagination'=>$pagination));
		die(0);
	}

	public function show_merchant_products_ajax() {
                
		$page = $_GET['page'];
		$author = wp_get_current_user();
		$args = array(
			'post_type' => 'product',
			'author' => $author->ID,
			'paged' => $page,
                        'posts_per_page' => -1,
		);
		$products = "";
		$product_query = new WP_Query($args);
		if( $product_query->have_posts() ) {
			while ( $product_query->have_posts() ) { 
					$product_query->the_post(); 
					$product_id = $product_query->post->ID;
					$products .= $this->product_view($product_id);
			}
		}
		wp_reset_postdata();
		//$pagination = mm_merchant_products_pagenavi( array('echo'=> false, 'query' => $product_query) );
		echo json_encode(array('products'=>$products, 'pagination'=>$pagination));
		die(0);
	}

	public function search_merchant_products() {
		$author = wp_get_current_user();
		if(isset($_GET['bysku'])) {
			$args = array(
				'post_type' => 'product',
				'author' => $author->ID,
				'posts_per_page' => -1,
			    'paged' => $page,
			);
		} else {
			$args = array(
				'post_type' => 'product',
				'author' => $author->ID,
				's' => $_GET['search'],
			    'posts_per_page' => -1,
			    'paged' => $page,
			);
		}
		$products = "";
		$product_query = new WP_Query($args);
		if( $product_query->have_posts() ) {
			while ( $product_query->have_posts() ) { 
					$product_query->the_post(); 
					$product_id = $product_query->post->ID;
					if(isset($_GET['bysku'])) {
						$sku = get_post_meta($product_id, '_sku', true);
						if($sku == trim($_GET['search'])) { 
							$products .= $this->product_view($product_id);		
						}
					} else {
						$products .= $this->product_view($product_id);		
					}
			}
		} else {
			$products .= '<h4>No Products Found !!!</h4>';
		}
		wp_reset_postdata();
		$pagination = mm_merchant_products_pagenavi( array('echo'=> false, 'query' => $product_query) );
		echo json_encode(array('products'=>$products, 'pagination'=>$pagination));	
		die(0);
	}

	public function product_view($product_id) {
		ob_start();
		global $MMGlobalFunctions;
		$theme_setttings = unserialize(get_option('global_theme_settings'));
		$merchant_dashboard = get_permalink($theme_setttings['merchantdashboard']);
		
		$product = get_post($product_id);
		$product_status = $product->post_status;
		$product_sku = get_post_meta($product_id, '_sku', true);
		$product_price = get_post_meta($product_id,'_regular_price', true);
		$product_sale_price = get_post_meta($product_id,'_sale_price', true);
		$product_discount = get_post_meta($product_id,'_product_discount', true);
		$currency = 'Rs. ';
		$terms = get_the_terms( $product_id, 'product-discount' );
		$mall_id = get_post_meta($product_id,'product_mall', true); 

		$saved_hightlight_attribute = get_option("product_highlight_attributes");
		$product_cats = get_the_terms($product_id, 'product_cat');

		$highlight_attribute = "";
	    if(!empty($product_cats)) {
	    	foreach($product_cats as $product_cat) {
	    		if($saved_hightlight_attribute[$product_cat->term_id]!='') {
	    			$highlight_attribute = $saved_hightlight_attribute[$product_cat->term_id];
	    		}
	       	}
	    }
	    	   	
	   	$product_attribute_object = get_the_terms($product_id, trim($highlight_attribute));
	   	$attribute_name = str_replace('pa_', '', $highlight_attribute);
	   	$attribute_name = ucfirst($attribute_name);

	   	$product_attribute = '';
	   	$k=0;
	   	if(!empty($product_attribute_object)) {
		   	foreach($product_attribute_object as $attribute) {
		   		if($k==0) {
		   			$product_attribute = $attribute->name;
		   		}
		   		$k++;
		   	}
	   	}
		?>
		<div class="merchant_product_wrapper col-sm-6" id="<?php echo 'merchant_product_'.$product_id; ?>">
			<div class="p_inner_wrapper">
				<div class="row">
					<?php	
					$width = '290';
		    	    $height = '315';
					if ( has_post_thumbnail() ) { 
						$attach_id = get_post_thumbnail_id($product_id);
		    	       	$image = $MMGlobalFunctions->vt_resize( $attach_id, '', $width, $height, true );
		    	    } else {
						$image_url = get_template_directory_uri().'/assets/img/placeholder-image.png';
						$image = $MMGlobalFunctions->vt_resize( '', $image_url, $width, $height, true );
					}
					$image = '<img src="'.$image[url].'" width="'.$image[width].'" height="'.$image[height].'" class="img-responsive" />';
					?>
					<div class="col-sm-4 product_image">
					<?php echo $image; ?>
					</div>
					<div class="col-sm-8">
						<div class="product_details">
							<?php if($product_sku != ''){ ?>
							<div class="product_id product_detail">
									<label class="badge bg-gray-bg plabels col-sm-4">Product ID: </label>
									<span class="col-sm-8"><?php echo ' '.$product_sku; ?></span>
									<div class="clearfix"></div>
							</div>
							<?php } ?>
							<div class="product_title product_detail">
	                    		<label class="badge bg-gray-bg plabels col-sm-4">Product Title: </label>
	                    		<span class="col-sm-8"><?php echo ' '.$product->post_title; ?></span>
	                    		<div class="clearfix"></div>
	                		</div>
<!--	                		<div class="product_bcg product_detail">
	                			<label class="badge bg-yellow plabels col-sm-3"><?php //echo $attribute_name; ?></label>
	                			<span class="col-sm-9"><?php //echo ' '.$product_attribute;; ?></span>
	                			<div class="clearfix"></div>
	                		</div>-->
	                		<div class="product_price product_detail">
	                    		<label class="badge bg-gray-bg plabels col-sm-4">Product Price: </label>
	                    		<span class="original_price col-sm-8"><?php if($product_price != ''){ echo ' '.$currency . $product_price."/-"; }?></span>
	                			<div class="clearfix"></div>
	                		</div>
	                		<div class="product_discount product_detail">
	                    		<?php if($product_discount !=''){ ?>
	                        	<label class="badge bg-gray-bg plabels col-sm-4">Product Discount: </label>
	                        	<span class="col-sm-8"><?php echo ' '.$product_discount.' off'; ?></span>
	                    		<?php } ?>
	                    		<div class="clearfix"></div>
	                		</div>
	                		<div class="product_status product_detail">
	                			<?php 
	                			if($product_status=='publish') { 
	                				$label_product_status = 'bg-gray-bg';
	                				$product_status_text = 'Published';
	                			} else {
	                				$label_product_status = 'bg-red';	
	                				$product_status_text = 'Not Published';
	                			}
	                			?>
	                			<label class="badge <?php echo $label_product_status; ?> plabels col-sm-4">Product Status: </label>
	                			<span class="col-sm-8"><?php echo ' '.$product_status_text; ?></span>
	                			<div class="clearfix"></div>
	                		</div>	

	                		<div class="product_preview_edit_buttons">
	                			<a href="<?php echo site_url(); ?>/merchant-dashboard/#/edit-product/<?php echo $product_id; ?>/" class="btn btn-primary btn-sm">Edit</a>
	                			<button class="btn btn-danger btn-sm merchant_product_delete" data-product-id="<?php echo $product_id; ?>" data-toggle="modal">Delete Product</button>
	                			<button class="btn btn-success btn-sm product_view product_preview" data-product-id="<?php echo $product_id; ?>" data-target="#productview<?php echo $product_id; ?>" data-loading-location="merchant" data-toggle="modal">Preview</button>
	                			<button class="btn btn-success product_view_loader disabled" style="display:none;">Preview Loading...</button>
	                		</div>	
						</div>
					</div>
				<div class="clearfix"></div>
				</div>
			</div>
		</div>
		<?php	
		return ob_get_clean();
	}

	public function delete_merchant_product_ajax(){
		$product_id = $_GET['product_id'];
		if($product_id) {
			$deleted = wp_delete_post($product_id);
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

}

new Products();