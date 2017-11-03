<?php 
/**
* Products CSV Uploads
*/

class ProductsCSVUploads {

	public function __construct() {
               
		add_action( 'wp_footer', array($this, 'products_csv_uploads_tml') );
		add_action( 'plugins_loaded', array($this,'wpse_92517_init') );
	}
        public function wpse_92517_init()
        {
                add_action('wp_ajax_product_zip_csv_upload', array($this, 'product_zip_csv_upload_ajax'));
		add_action('wp_ajax_upload_csv_products_images', array($this, 'upload_csv_products_images_ajax'));
		add_action('wp_ajax_load_products_from_csv', array($this, 'load_products_from_csv'));
		add_action('wp_ajax_run_uploads_products', array($this, 'run_uploads_products_ajax'));
		add_filter( 'upload_dir', array($this, 'mm_set__merchant_upload_dir') );
        }
	public function products_csv_uploads_tml() {
		ob_start();
		?>
		<script type="text/html" id="products_csv_uploads_tml">
		<div class="products_csv_uploads_container">
			<div class="container" style="">
				<h2 class="page-header"><i class="icon ion-android-note"></i>Upload Products as CSV</h2>

					<div class="form-group">
						<div id="upload_csv_zip_container" class="upload_csv_zip_wrap col-sm-7 col-md-7 col-lg-7">	
							<div class="upload_csv_zip_area" id="upload_csv_zip">
									<h3>Upload Product Images ZIP and CSV HERE..</h3>
									<h4 style="color: red; font-size: 15px;">(Note:- Upload New Zip and CSV Start New CSV Uploading)</h4>
									<div id="upload_zip_csv_previews">

									</div>
							</div>
						</div>
					</div>
					
					<div class="csv_zip_uploading_succes_error alert"></div>

					<div class="form-group upload_csv_product_images_row" style="text-align:center; display: none;">
						<div class="alert alert-info" role="alert" style="margin-bottom: 10px;">
							<label>If there are product images then first upload product images by below [ Upload Product Images ] button </label>
						</div>
						<button class="btn btn-primary upload_product_images_first" id="upload_csv_products_images">Upload Product Images</button>
					</div>

					<div class="form-group upload_products_by_csv" style="text-align:center; margin-top:20px;">
						<div class="alert alert-info products_chunks_count" role="alert">
                                                    <label for="products_chunks_count">If Numbers of Products are larger than 50 or more then just go with below option</label>
                                                    <label for="products_chunks_count">Enter Number/Count of Products which are uploads into one chunk </label>
                                                    <label for="products_chunks_count" style="color: red; font-size: 14px;">(If not given All Products Uploads into one chunk)</label>
						</div>
						<input type="text" placeholder="E.g: 15" value="" class="products_chunks_count" id="products_chunks_count" />
						<button class="btn btn-success" id="load_csv_products" data-chunk="1">Load CSV Products</button>
					</div>
					<div class="form-group run_csv_upload" style="text-align:center; margin-top:20px;">
						<button class="btn btn-danger start_upload_products_by_csv start_uploading_csv_products">Run Upload Products</button>
					</div>
					<div id="csv_upload_products_list" style="margin-top:20px;">
					</div>
			</div>
		</div>
		</script>
		<?php
		echo ob_get_clean();
	}

	//Upload ZIP CSV AJAX Handlers
	public function product_zip_csv_upload_ajax() {
            check_ajax_referer('product_csv_zip_allow', 'nonce');
            $merchant_user = wp_get_current_user();
            $merchant_user_id = $merchant_user->ID;
            $upload_dir = wp_upload_dir();

            //Create Merchant Upload DIR IF not Exist
            $merchant_upload_dir = $upload_dir['basedir'].'/merchant_'.$merchant_user_id.'_uploads';
            if (wp_mkdir_p($merchant_upload_dir)) {
                chmod( $merchant_upload_dir, 0777);
            }
            $file = array(
                'name' => $_FILES['product_csv_zip_file']['name'],
                'type' => $_FILES['product_csv_zip_file']['type'],
                'tmp_name' => $_FILES['product_csv_zip_file']['tmp_name'],
                'error' => $_FILES['product_csv_zip_file']['error'],
                'size' => $_FILES['product_csv_zip_file']['size']
            );
            $file = $this->fileupload_process($file);
            die(0);
	}

	public function fileupload_process($file) {
            $merchant_user = wp_get_current_user();
            $merchant_user_id = $merchant_user->ID;
            $uploadfile = $this->handle_file($file);

            if (is_array($uploadfile)) {
                $html = $this->getHTML($uploadfile);
                $filetype = $uploadfile['type'];
                    $filetype = explode("/", $filetype);
                $response = array(
                    'success' => true,
                    'html' => $html,
                    'filetype' => $filetype[1],
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
    	$uploaded_file = wp_handle_upload($upload_data, array('test_form' => false));
    	return $uploaded_file;
    }

    public function mm_set__merchant_upload_dir( $upload ) {
    	if(isset($_REQUEST)) {
            if($_REQUEST['action']=='product_zip_csv_upload') {
                $merchant_user = wp_get_current_user();
                $merchant_user_id = $merchant_user->ID;
                $upload['subdir'] = '/merchant_'.$merchant_user_id.'_uploads/';
                $upload['path'] = $upload['basedir'] . $upload['subdir'];
                $upload['url']  = $upload['baseurl'] . $upload['subdir'];
            }
    	}
    	return $upload;
	}

    public function getHTML($uploadfile)
    {
    	global $wpdb;
    	$merchant_user = wp_get_current_user();
        $merchant_user_id = $merchant_user->ID;
        $filetype = $uploadfile['type'];
        $filetype = explode("/", $filetype);
        $html = '';
        if($filetype[1] == "zip") {
            $file_name = basename($uploadfile['file']);
            update_user_meta($merchant_user_id, 'product_images_zip', $file_name);
            $html .= '<div class="csvzipuploadpreview" id="zipfile">';
            $html .= '<h4>Products Images ZIP</h4>';
            $html .= '<i class="fa fa-file-zip-o"></i>';
            $html .= '</div>';
    	} else if($filetype[1] == "csv") {
            $file_name = basename($uploadfile['file']);

            //Reset all saved things before csv upload
            update_user_meta($merchant_user_id, 'products_csv_file', $file_name);
            update_user_meta($merchant_user_id, 'csv_headers', '');
            $wpdb->query( 
                            $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE %s", $merchant_user_id, '%products_csv_rows%' )
                    );
            update_user_meta($merchant_user_id, 'product_csv_chunks_flag', 'false');
            update_user_meta($merchant_user_id, 'products_remaining', '');
//            update_user_meta($merchant_user_id, 'extract_dir', '');

            $html .= '<div class="csvzipuploadpreview" id="csvfile">';
            $html .= '<h4>Products CSV</h4>';
            $html .= '<i class="fa fa-file-text"></i>';
            $html .= '</div>';
    	}	
        return $html;
    }

    //Upload CSV Products Images from ZIP
    public function upload_csv_products_images_ajax() {
    	$merchant_user = wp_get_current_user();
        $merchant_user_id = $merchant_user->ID;
        $upload_dir = wp_upload_dir();
        $merchant_upload_dir = $upload_dir['basedir'].'/merchant_'.$merchant_user_id.'_uploads';
        $zipfile = get_user_meta($merchant_user_id, 'product_images_zip', true);
        $file = $merchant_upload_dir."/".$zipfile;
        WP_Filesystem();
        $random_dir = rand();
        $random_dir = $random_dir.'#'.$merchant_user_id.'merchant';
        $extract_dir = $upload_dir['basedir'].'/merchant_'.$merchant_user_id.'_uploads/'.$random_dir;
        if (wp_mkdir_p($extract_dir)) {
            chmod( $extract_dir, 0777);
            if( !is_wp_error( unzip_file( $file, $extract_dir ) ) ) {
                update_user_meta($merchant_user_id, 'extract_dir', $random_dir);
                $response = "success";
            } else {
                $response = "error";
            }
        } else {
            $response = "error";
        }

        $images = $this->get_directory_images($extract_dir);
        echo json_encode(array('result'=>$response));
    	die(0);
    }

    //Get Directory Sub Directory Images
    public function get_directory_images($dir) {
    	$dir = trailingslashit( $dir );
    	$images = glob("$dir*.*" ); //get files
        $dirs = glob("$dir*", GLOB_ONLYDIR|GLOB_MARK ); //get subdirectories
        return $images;
    }

    //Upload Products From CSV
    public function load_products_from_csv($chunk) {
        
    	if(isset($_GET['chunk'])) {
            $chunk = $_GET['chunk'];
    	}

    	$products_chunks_count = $_GET['products_chunks_count'];
		
        $seflag = 'success';

    	$merchant_user = wp_get_current_user();
        $merchant_user_id = $merchant_user->ID;
        $upload_dir = wp_upload_dir();
        $merchant_upload_dir = $upload_dir['basedir'].'/merchant_'.$merchant_user_id.'_uploads';
    	$csv_file = get_user_meta($merchant_user_id, 'products_csv_file', true);
    	$file = $merchant_upload_dir.'/'.$csv_file;
    	$handle = fopen($file, 'r');
    	$count = 0;
    	$db_csvContents = get_user_meta($merchant_user_id, 'products_csv_rows'.$chunk, true);
    	$db_csvContents = unserialize($db_csvContents);
        $csvContents = array();
        while (($line = fgetcsv($handle, 0, ';')) !== FALSE) {
            $csvContents[$count] = $line;
            $count ++;
        }

        $csv_headers = $csvContents[0];
        update_user_meta($merchant_user_id, 'csv_headers', $csv_headers);

        //Unset Header From CSV
        unset($csvContents[0]);

        //Set Total CSV Products
        $total_csv_products = count($csvContents);

        //Set Number of Products into each chunk
        $number_of_chunks = $total_csv_products;
        if($products_chunks_count!="") {
        $number_of_chunks = $products_chunks_count;
        } 

        update_user_meta($merchant_user_id, 'total_csv_products', $total_csv_products);

        $db_csvContents_chunks = array_chunk($csvContents, $number_of_chunks);
        $total_csv_files_chunks = "";
        $product_csv_chunks_flag = get_user_meta($merchant_user_id, 'product_csv_chunks_flag', true);
        $chunks_buttons = '';
        if($product_csv_chunks_flag=='false') {
            foreach($db_csvContents_chunks as $key => $values) { 
                    $count_products_into_chunks = count($values);
                    $key = $key + 1;
                    $total_csv_files_chunks = $key;
                     foreach ($values as $vkey => $vvalue) {
                        foreach ($vvalue as $fkey => $fvalue) {
//                            var_dump($vvalue[$fkey]);
                            $new_string = preg_replace("/[^A-Za-z0-9 '\"-_.,]/", " ", $fvalue); 
                            $fvalue = $new_string;
                            $vvalue[$fkey] = $fvalue;
                        }
                        $values[$vkey] = $vvalue;
                    }
                    update_user_meta($merchant_user_id, 'products_csv_rows'.$key, serialize($values));

                    if($key!=1) {
                            $remaining_chunk = "Remaining ";
                            $key_chunk = $key;
                    } else {
                            $remaining_chunk = "";
                            $key_chunk = "";
                    }
                    $chunks_buttons .= '<button class="btn btn-danger start_upload_products_by_csv start_uploading_csv_products" data-chunk="'.$key_chunk.'">Run Upload Products ('.$remaining_chunk.''.$count_products_into_chunks.' out of '.$total_csv_products.')</button>';
                    if($key==count($db_csvContents_chunks)) {
                            update_user_meta($merchant_user_id, 'product_csv_chunks_flag', 'true');
                    }
            }
            update_user_meta($merchant_user_id, 'total_csv_files_chunks', $total_csv_files_chunks);
        }
			
		
		ob_start();
		$db_csvContents = get_user_meta($merchant_user_id, 'products_csv_rows'.$chunk, true);
    	$db_csvContents = unserialize($db_csvContents);

    	if(is_array($db_csvContents)) {
	    	array_unshift($db_csvContents, $csv_headers);
    		$db_csv_contents = $db_csvContents;

			$row = 0;
			if(!empty($db_csv_contents)) {
				foreach($db_csv_contents as $rowcontent) {

					if($row==0) {
						$headers = $rowcontent;
					}

					if($row!=0) {
					?>
						<div class="col-sm-12 each_csv_product">
						<?php $row_bg_color = $row%2 ? '#f7f7f7' : '#ffffff'; ?>	
						<div class="inner_csv_product" style="background: <?php echo $row_bg_color; ?>; padding:20px; border:1px solid #ddd; margin-bottom: 10px; position: relative; ">
						<i class="fa fa-cog fa-spin csv_product_loader" style="color: red; font-size:28px; position: absolute; left: auto; right: 10px; top: 10px; display: none;"></i>
						<i class="fa fa-check csv_product_loader_completed" style="color: #00a65a; font-size:28px; position: absolute; left: auto; right: 10px; top: 10px; display: none;"></i>
						<i class="fa fa-exclamation-triangle csv_product_loader_error" style="color: tomato; font-size:28px; position: absolute; left: auto; right: 10px; top: 10px; display: none;"></i>
						<div role="alert" class="alert alert_error" style="margin: 25px 0px 15px; display: none; padding: 10px 15px;"></div>
						<div role="alert" class="alert alert-danger product_unuploaded_images_error" style="margin: 10px 0px 15px; display: none; padding: 10px 15px;"></div>
						<?php
						foreach($rowcontent as $i => $content) {
							$heading = $headers[$i];
							$heading = strtoupper($heading);
							?>	
							<div class="row" style="border-bottom:1px solid #ddd; margin-bottom:10px; padding-bottom:10px;">
							<div class="col-sm-5"><b><?php echo $heading ?></b></div>
							<div class="col-sm-7 product_information">
								<?php 
								$value = $content; 
								echo $value;
								?>
								<input type="hidden" value='<?php echo $content; ?>' class="csvcontent" />
							</div>
							<div class="clearfix"></div>
							</div>
						<?php
						} 
						?>
						</div>
						</div>
					<?php	
					}	
					$row++;
				} 
			}
			$seflag = 'success';
		?>
		<div class="clearfix"></div>
		<?php
		} else {
			$seflag = 'error';
			?>
			<div class="alert alert-danger" role="alert">There is error while getting csv products!!!</div>
			<?php
		}	
		$response = ob_get_clean();
		if(isset($_GET['chunk'])) {
			echo json_encode(array('response'=>$response, 'seflag'=>$seflag, 'chunks_buttons'=>$chunks_buttons));
		} else {
			return $response;
		}
	   	die(0);
    }


    public function run_uploads_products_ajax() {
    	global $ProductUploadObject, $wpdb, $disable_parent_cat_support, $product_cat_relationship, $sub_cat_field_type, $sub_cat_add_new_support;

    	$chunk = $_POST['chunk'];

    	if($chunk!="") {
    		
    		$load_csv = $this->load_products_from_csv($chunk);
    		echo json_encode(array('load_csv'=>$load_csv));

    	} else {

    		$error_flag = false;
    		$outofstockflag = false;
    		$error = "";
    		$update_product_id = "";

    		$class = "alert-success";

	    	$merchant_user = wp_get_current_user();
                $merchant_user_id = $merchant_user->ID;
                $upload_dir = wp_upload_dir();
                $merchant_upload_dir = $upload_dir['basedir'].'/merchant_'.$merchant_user_id.'_uploads';
	    	$zipfile = get_user_meta($merchant_user_id, 'product_images_zip', true);
	    	$csv_file = get_user_meta($merchant_user_id, 'products_csv_file', true);
	    	$extract_dir = get_user_meta($merchant_user_id, 'extract_dir', true);
	    	$total_csv_files_chunks = get_user_meta($merchant_user_id, 'total_csv_files_chunks', true);
	    	$total_csv_products = get_user_meta($merchant_user_id, 'total_csv_products', true);

//	    	$sku = $_POST['product_info_object'][0];
//	    	$sku = trim($sku);
                 $product_elements = $_POST['product_info_object'][0];
                $metal_color = $_POST['product_info_object'][1];
                $metal_purity = $_POST['product_info_object'][2];
                $product_name = $_POST['product_info_object'][3];
                $style_no = $_POST['product_info_object'][4];
                $round_daim_pcs = $_POST['product_info_object'][5];
                $round_daim_wt_ct = $_POST['product_info_object'][6];
                $round_daim_wt_ct = number_format((float)$round_daim_wt_ct, 2, '.', '');
                $other_diam_name = $_POST['product_info_object'][7];
                $other_diam_pcs = $_POST['product_info_object'][8];
                $other_diam_pcs_wt_ct = $_POST['product_info_object'][9];
                $other_diam_pcs_wt_ct = number_format((float)$other_diam_pcs_wt_ct, 2, '.', '');
                $diamond_color = $_POST['product_info_object'][10];
                $diamond_calarity = $_POST['product_info_object'][11];
                $gemstone_name = $_POST['product_info_object'][12];
                $gemstone_pcs = $_POST['product_info_object'][13];
                $gemstone_wt_ct = $_POST['product_info_object'][14];
                $product_gross_wt_gms = $_POST['product_info_object'][15];
                $product_gross_wt_gms = number_format((float)$product_gross_wt_gms, 2, '.', '');
                $net_wt_metal_gms = $_POST['product_info_object'][16];
                $net_wt_metal_gms = number_format((float)$net_wt_metal_gms, 2, '.', '');
                $gross_stone_price = $_POST['product_info_object'][17];
                $labour_actual_charge = $_POST['product_info_object'][18];
                $labour_percentage_charge = $_POST['product_info_object'][19];
                $product_filename = $_POST['product_info_object'][20];
                $diamention_length_cm = $_POST['product_info_object'][21];
                $diamention_width_cm = $_POST['product_info_object'][22];
                $dimention = $diamention_length_cm .'/'. $diamention_width_cm;
                $product_set_name = $_POST['product_info_object'][23];
                $ships_in_days = $_POST['product_info_object'][24];
                
                if(!empty($labour_actual_charge)){
                    $labour_charge = $labour_actual_charge;
                }else{
                    $global_custom_setting = unserialize(get_option('global_custom_setting'));
                    $gold_price = $global_custom_setting['gold_price'];
                    $labour_percentage_charge = $labour_percentage_charge/100;
                    $labour_charge = $gold_price*$labour_percentage_charge*$net_wt_metal_gms;
                }
                
	    	$product_status = 'draft';
                
                if($style_no!="") {
                if ( $wpdb->get_var( $wpdb->prepare("
                    SELECT $wpdb->posts.ID
                    FROM $wpdb->posts
                    LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
                    WHERE $wpdb->posts.post_author = $merchant_user_id AND $wpdb->posts.post_type = 'product'
                    AND $wpdb->postmeta.meta_key = 'style_no' AND $wpdb->postmeta.meta_value = '%s'
                    ", $style_no ) ) ) 
                            {
                                $update_product_id = $wpdb->get_var( $wpdb->prepare("
                                    SELECT $wpdb->posts.ID
                                    FROM $wpdb->posts
                                    LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
                                    WHERE $wpdb->posts.post_author = $merchant_user_id AND $wpdb->posts.post_type = 'product'
                                    AND $wpdb->postmeta.meta_key = 'style_no' AND $wpdb->postmeta.meta_value = '%s'
                                    ", $style_no ) );
                            }    
                  }

//	    	if( $error_flag == false && $outofstockflag == false ) {
            if( $error_flag == false && $style_no!="") {
		    	if (is_numeric($update_product_id) && $update_product_id > 0) {
                                    $product_id = $update_product_id;
                                    wp_update_post( array('ID' => $product_id,'post_title'   => wp_strip_all_tags($product_name) ));
                        } else {
                            $product_post = array(
                                'post_title' => wp_strip_all_tags($product_name),
                                'post_status' => $product_status,
                                'post_author' => $merchant_user_id,
                                'post_type' => 'product',
                            );

                            $product_id = wp_insert_post( $product_post );
                        }
                        
                        $other_diam_name = $_POST['product_info_object'][7];
                        
                        if($product_elements !=""){
                        $product_elements = explode(',', $product_elements);
                       // wp_set_post_terms( $product_id, $product_descmasters,'jewellary');
                        $input_terms = array_map( 'sanitize_text_field', $product_elements );
                        $terms = array();
                        foreach( $input_terms as $term ) {
                            $existent_term = term_exists( $term, 'elements' );
                            if( $existent_term && isset($existent_term['term_id']) ) {
                                $term_id = $existent_term['term_id'];
//                            } else {
//                                //intert the term if it doesn't exsit
//                                $term = wp_insert_term(
//                                    $term, // the term 
//                                    'elements' // the taxonomy
//                                );
//                                if( !is_wp_error($term ) && isset($term['term_id']) ) {
//                                     $term_id = $term['term_id'];
//                                } 
                           }
                           //Fill the array of terms for later use on wp_set_object_terms
                           $terms[] = (int) $term_id;
                        }
                    wp_set_object_terms( $product_id, $terms, 'elements' );
                    }
                    
                    if($other_diam_name !=""){
                        $other_diam_name = explode(',', $other_diam_name);
                       // wp_set_post_terms( $product_id, $product_descmasters,'jewellary');
                        $input_terms = array_map( 'sanitize_text_field', $other_diam_name );
                        $terms = array();
                        foreach( $input_terms as $term ) {
                            $existent_term = term_exists( $term, 'vendor_diamond' );
                            if( $existent_term && isset($existent_term['term_id']) ) {
                                $term_id = $existent_term['term_id'];
                            } else {
                                //intert the term if it doesn't exsit
                                $term = wp_insert_term(
                                    $term, // the term 
                                    'vendor_diamond' // the taxonomy
                                );
                                if( !is_wp_error($term ) && isset($term['term_id']) ) {
                                     $term_id = $term['term_id'];
                                } 
                           }
                           //Fill the array of terms for later use on wp_set_object_terms
                           $terms[] = (int) $term_id;
                        }
                    wp_set_object_terms( $product_id, $terms, 'vendor_diamond' );
                    }
		        update_post_meta($product_id, 'style_no', $style_no);
                        update_post_meta($product_id, 'product_title', $product_name);
                        update_post_meta($product_id, 'metal_color', $metal_color);
                        update_post_meta($product_id, 'metal_purity', $metal_purity);
                        
                        update_post_meta($product_id, 'round_diamond_pcs', $round_daim_pcs);
                        update_post_meta($product_id, 'round_diamond_weight(carat)', $round_daim_wt_ct);
                        update_post_meta($product_id, 'other_diamond_pieces', $other_diam_pcs);
                        update_post_meta($product_id, 'other_diamond_pieces_weight(carat)', $other_diam_pcs_wt_ct);
                        update_post_meta($product_id, 'diamond_color', $diamond_color);
                        update_post_meta($product_id, 'diamond_clarity', $diamond_calarity);
                        
                        update_post_meta($product_id, 'gemstone_name', $gemstone_name);
                        update_post_meta($product_id, 'gemstone_pieces', $gemstone_pcs);
                        update_post_meta($product_id, 'gemstone_weight(carat)', $gemstone_wt_ct);
                        update_post_meta($product_id, 'total_wt._(gms)', $product_gross_wt_gms);
                        
                        update_post_meta($product_id, 'metal_wt._(gms)', $net_wt_metal_gms);
                        update_post_meta($product_id, 'diamond_&_stone_value', $gross_stone_price);
                        
                        update_post_meta($product_id, 'dimensions', $dimention);
                        
                        update_post_meta($product_id, 'labour_charge', $labour_charge);
		        //Set Product Regular Price
		        update_post_meta($product_id, 'set_name', $product_set_name);
                        update_post_meta($product_id, 'ships_in_days', $ships_in_days);
                        update_post_meta($product_id, 'prod_file_name', $product_filename);
                     
                  
		    //Set Product Sale/Discount Price if Discount Exist
//                if($product_discount!="") {
//                    $discount = preg_replace("/[^0-9]/", "", $product_discount);
//                    $discount = intval($discount);
//                    $discount_price = $product_price * $discount / 100;
//                    $product_sale_price = $product_price - $discount_price;
//                    update_post_meta($product_id, '_product_discount', $product_discount);
//                    update_post_meta($product_id, '_product_discount_number', $discount);
//                    //Get Product Discount Term                
//                    $product_discount_term = $ProductUploadObject->get_discount_range_term($discount);
//                    wp_set_object_terms( $product_id, $product_discount_term, 'product-discount', false );
//                    update_post_meta($product_id, '_sale_price', $product_sale_price);
//                    update_post_meta($product_id, '_product_deal_discount', $discount);
//                    update_post_meta($product_id, '_product_dd_ndd_order', $discount);
//                } else {
//                    delete_post_meta($product_id, '_product_discount');
//                    delete_post_meta($product_id, '_product_discount_number');
//                    delete_post_meta($product_id, '_sale_price');
//                }

                //Set Product Deal if Exist
//                update_post_meta($product_id, 'product_deal', $product_deal);
//                if($product_deal!="") {
//                    update_post_meta($product_id, 'product_deal_flag', 'true');
//                    update_post_meta($product_id, '_product_deal_discount', 100);
//                    update_post_meta($product_id, '_product_dd_ndd_order', 100);
//                } else {
//                    delete_post_meta($product_id, 'product_deal_flag');
//                }
//
//                //Set Meta Flag Product If No Deal and Discount 
//                if($product_discount == '' && $product_deal == '') {
//                    update_post_meta($product_id, 'no_deal_discount', 'true');
//                    update_post_meta($product_id, '_product_dd_ndd_order', 0);
//                    delete_post_meta($product_id, '_product_deal_discount');
//                } else {
//                    delete_post_meta($product_id, 'no_deal_discount');
//                }

		        //Set Product Images
		        $unuploaded_images = array();
		        $unuploaded_images_flag = 'false';
                         $dir = $merchant_upload_dir.'/'.$extract_dir.'/'.$product_filename;
                         if (is_dir($dir)) {
                            if ($dh = opendir($dir)) {
                                $product_images = array();
//                                var_dump($dir);
                                while (($file = readdir($dh)) !== false) {
                                    $prt_images = mime_content_type($dir . '/' . $file);
//                                    var_dump($prt_images, $file);
//                                    var_dump('1');
                                    if ($prt_images === 'image/jpeg') {
//                                        var_dump('11');
                                        $product_images[] = $file;
                                    }
                                }
                                closedir($dh);
                            }
                        }
//                        var_dump($product_images); die;
		        if (!empty($product_images)) {
//		        	$product_images = explode("|", $product_images);
		        	$icount=0;
		        	$product_gallery_images = array();
		        	foreach($product_images as $product_image) {
		        		$img = $merchant_upload_dir.'/'.$extract_dir.'/'.$product_filename.'/'.$product_image;
		        		$img_name = basename( $img );
                                        $im_n = explode('.',$img_name);
                                        $file = array( 'file' => $img, 'tmp_name' => $img, 'name' => $img_name );
			            if($im_n[1] == 1) {
			            	if(is_file($img)) {
				            	$img_id = media_handle_sideload( $file, $parent, $img_name );
                                                $featured_image = $img_id;
				            	update_post_meta($product_id, '_thumbnail_id', $featured_image);
			            	} else {
			            		$unuploaded_images[] = $img_name;
			            		$unuploaded_images_flag = 'true';
			            	}
			            } else { 
			            	if(is_file($img)) {
			            		$img_id = media_handle_sideload( $file, $product_id, $img_name );
//			            		$product_gallery_images[] = $img_id;
                                                $product_gallery_images[] = array('key' => $im_n[1], 'image_id' => $img_id);
			            	} else {
			            		$unuploaded_images[] = $img_name;
			            		$unuploaded_images_flag = 'true';
			            	}
			            }
			        	$icount++;
		        	}
                                
                                $pro_gallery_images =array();
                                $p_images = array();
                                foreach ($product_gallery_images as $key => $row)
                                {
                                    $p_images[$key] = $row['key'];
                                }
                                array_multisort($p_images, SORT_ASC, $product_gallery_images);
                              
                                foreach ($product_gallery_images as $item) {
                                    $pro_gallery_images[] = $item['image_id'];
                                }
		        	$product_gallery_images = implode(", ", $pro_gallery_images);
		        	update_post_meta($product_id, '_product_image_gallery', $product_gallery_images);
		    		$unuploaded_images = implode(", ", $unuploaded_images);
		    		$unuploaded_images = "This images not attach to product while uploading product : ".$unuploaded_images;
		        }

		        //Set Products Categories and Attributes
//		        $product_categories_array = array();
//		        $apply_product_categories = array();

                    //First Remove All Apply Product Categories
//                    wp_delete_object_term_relationships( $product_id, 'product_cat' );
                    
                    //Apply Product Main Categories
//                    $product_categories_array = explode("->", $product_categories);
//                    
//                    $count_categories = count($product_categories_array);
//                    if(!empty($product_categories_array)) {
//                    	$parentcatid = '';
//                    	$ci=0;
//                    	foreach($product_categories_array as $product_category) {
//                    		if($ci == 0) {
//                    			$product_category = sanitize_title($product_category);
//	                    		$parentcatobject = get_term_by('slug', $product_category, 'product_cat');
//	                    		wp_set_object_terms( $product_id, $parentcatobject->slug, 'product_cat', true );
//	                    		$apply_product_categories[] = $parentcatobject->slug;
//	                    		$parentcatid = $parentcatobject->term_id;
//	                       	} else {
//	                    		$childterms = get_terms('product_cat', array('hide_empty'=>0, 'parent'=>$parentcatid));
//								if(!empty($childterms)) {
//									foreach($childterms as $childterm) {
//										if ( preg_match('/'.trim($product_category).'/i',htmlspecialchars_decode($childterm->name)) ) {
//											$parentcatid = $childterm->term_id;
//											$apply_product_categories[] = $childterm->slug;
//											wp_set_object_terms( $product_id, $childterm->slug, 'product_cat', true );			
//										}
//									}	
//								} 
//							}                  		
//                    		$ci++;
//                    	}
//                    } 
//
//                    //Save Product Categories Sub Categories Structure
//                    update_post_meta($product_id, 'product_categories_array', $apply_product_categories);
//
//                    //Apply Categories Attributes To Product
//                    $product_attributes_array = explode("#", $product_attributes);
////var_dump($product_attributes);die;
//                  
//                    if(!empty($product_attributes_array)) {
//                        foreach($product_attributes_array as $product_attribute) {
//                            if(!empty($product_attributes_array)) {
//                                foreach($product_attributes_array as $product_attribute_object) {
//                                	$object = explode("->", $product_attribute_object);
////                                          echo '<pre>';
////                                      print_r($object);
////                    echo '</pre>';
//                                	if(!empty($object)) {
//                                            $product_attribute_val_object = '';
//                                		$tax = $object[0];
//                                                if($tax!=''){
//                                                    $tax = sanitize_title($tax);
//                                                    $tax = "pa_".$tax;
//                                                    $product_attribute_val_object = $object[1];
//                                                }
//                                                if($product_attribute_val_object!=''){
//                                                    $product_attribute_vals = explode("|", $product_attribute_val_object);
//                                                }
//	                                    //First Remove All Apply Attribute Terms
//	                                    wp_delete_object_term_relationships( $product_id, $tax );
//                                        if(!empty($product_attribute_vals)) {
//                                            foreach($product_attribute_vals as $attribute_val) {
//                                                wp_set_object_terms( $product_id, trim($attribute_val), $tax, true );
//                                                $ProductUploadObject->apply_terms_parent_relationships($attribute_val, $tax, array(), $apply_product_categories);
//                                            }
//                                        }
//	                                    
//                                    }   
//                                }
//                            }
//                        }
//                    }
//
//                    //Update Product Attributes
//                    $attributes = array();
//                    $product_attribute_order = array();
//
//                    if(is_array($product_attributes_array)) {
//                        if(!empty($product_attributes_array)) {
//                            foreach($product_attributes_array as $product_attribute_object) {
//                            	$object = explode("->", $product_attribute_object);
//                                if(!empty($object)) {
//	                                $taxonomy = $object[0];
//	                                $taxonomy = sanitize_title($taxonomy);
//	                                
//	                                $attribute_name = $object[0];
//	                                $attribute_name = sanitize_title($attribute_name);
//	                                $woo_attributes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies where attribute_name = '$attribute_name'");
//                                	
//                                	$woo_attributes = $woo_attributes[0];
//                                	$attribute_id = $woo_attributes->attribute_id;
//            						$product_attribute_order[] = $attribute_id;
//
//                                	$taxonomy = "pa_".$taxonomy;
//	                                $product_attribute_val = $object[1]; 
//
//	                                if($product_attribute_val!="" || !empty($product_attribute_val)) {
//	                                    $attributes[$taxonomy] = array('name'=>$taxonomy, 'value'=>0, 'position'=>0, 'is_visible'=>0, 'is_variation'=>0, 'is_taxonomy'=>1);
//	                                    update_post_meta($product_id, '_product_attributes', $attributes);
//	                                }
//                            	}
//                            }    
//                        }
//                    }
//                   
//                    if(!empty($product_attribute_order)) {
//                        update_post_meta($product_id, 'product_attribute_order', $product_attribute_order);
//                    } else {
//                        update_post_meta($product_id, 'product_attribute_order', '');
//                    }		 
//
//


		    	if($product_id!=0 || $product_id!="") {
		    		$success_error = "success";
		    		$success = "Product Uploaded Successfully";
		    		delete_post_meta($product_id, 'product_search_meta');
                    //Search Metas
                    add_post_meta($product_id, 'product_search_meta', strtolower(get_the_title($product_id)));
                    add_post_meta($product_id, 'product_search_meta', strtolower(get_the_title($shop_id)));
                    add_post_meta($product_id, 'product_search_meta', strtolower(get_the_title($mall_id)));            

                    $product_search_terms = wp_get_post_terms( $product_id, 'product_cat' );
                    if(!empty($product_search_terms)) {
                        foreach($product_search_terms as $psterm) {
                            $pskeyword = str_replace("-", " ", $psterm->name);
                            $pskeyword = strtolower($pskeyword);
                            add_post_meta($product_id, 'product_search_meta', $pskeyword);
                        }
                    }

                    $brand_terms = wp_get_post_terms( $product_id, 'pa_brand' );
                    if(!empty($brand_terms)) {
                        foreach($brand_terms as $bterm) {
                            $bkeyword = str_replace("-", " ", $bterm->name);
                            $bkeyword = strtolower($bkeyword);
                            add_post_meta($product_id, 'product_search_meta', $bkeyword);
                        }
                    }

                    $colors_terms = wp_get_post_terms( $product_id, 'pa_color' );
                    if(!empty($colors_terms)) {
                        foreach($colors_terms as $cterm) {
                            $ckeyword = str_replace("-", " ", $cterm->name);
                            $ckeyword = strtolower($ckeyword);
                            add_post_meta($product_id, 'product_search_meta', $ckeyword);
                        }
                    }
		    		$class = "alert-success";
		    	} else {
		    		$success_error = "error";
		    		$error .= "Error while uploading product";
		    		$class = "alert-danger";
		    	}	

	    	} //insert product end

	    	if( $error_flag == false && $outofstockflag == true ) {

	    		if($product_outofstock=='yes') {
		    		$product_update_post = array(
			            'ID' => $update_product_id,
			            'post_status' => 'draft',
			            'post_type' => 'product',
			        );
	    		} else {
	    			$product_update_post = array(
			            'ID' => $update_product_id,
			            'post_status' => 'publish',
			            'post_type' => 'product',
			        );
	    		}

		        $product_update = wp_update_post( $product_update_post );

	    		if($product_update!=0 || $product_update!="") {
		    		if($product_outofstock=='yes') {
		    			$success = "Product Outofstock Successfully";
		    		} else {
		    			$success = "Product Instock Successfully";
		    		}
		    		$success_error = "success";
		    		$class = "alert-success";
	    		
		    		update_post_meta($update_product_id, 'city', $city);
			        update_post_meta($update_product_id, 'mall', $mall_id);
			        update_post_meta($update_product_id, 'shop', $shop_id);

		    	} else {
		    		$success_error = "error";
		    		$error .= "Error while updating product";
		    		$class = "alert-danger";
		    	}
	    	} //update product end	

	    	//Check All Products are Uploaded and then Delete ZIP and CSV
		    $products_remaining = get_user_meta($merchant_user_id, 'products_remaining', true);
		    if($products_remaining=="") {
		    	$products_remaining = $total_csv_products - 1;
		    	update_user_meta($merchant_user_id, 'products_remaining', $products_remaining);
		    } else {
		    	$products_remaining = $products_remaining - 1;
		    	update_user_meta($merchant_user_id, 'products_remaining', $products_remaining);
		    }
		    
		    if($products_remaining==0) {
		    	//Delete Uploaded CSV File..
                            if (file_exists( $merchant_upload_dir.'/'.$csv_file )) {
                                    @unlink( $merchant_upload_dir.'/'.$csv_file );
                            }
                            //Delete Uploaded ZIP File..
                            if (file_exists( $merchant_upload_dir.'/'.$zipfile )) {
                                    @unlink( $merchant_upload_dir.'/'.$zipfile );
                            }

                            if($extract_dir!="") {
                                    $e_directory = $merchant_upload_dir.'/'.$extract_dir;
                                    self::deleteDir($e_directory);
                            }
			}
	    	
	    	echo json_encode(array('product'=>$product_id, 'success_error'=>$success_error, 'error'=>$error, 'success'=>$success, 'class'=>$class, 'unuploaded_images_flag'=>$unuploaded_images_flag, 'unuploaded_images'=>$unuploaded_images));
    	}
    	
    	die(0);
    }

    public function get_woo_taxonomy_by_label($tax_label) {
    	global $wpdb;
    	$db_prefix = $wpdb->prefix;
		$taxonomy = $wpdb->get_row("select * from ".$db_prefix."woocommerce_attribute_taxonomies where attribute_label = '".$tax_label."'");
		$taxonomy_slug = 'pa_'.$taxonomy->attribute_name;
		$taxonomy = get_taxonomy( $taxonomy_slug );
		return $taxonomy->name;
    }

    public function get_wp_taxonomy_by_label($tax_label) {
    	global $wpdb;
    	$tax_label = sanitize_title($tax_label);
		$taxonomy = get_taxonomy( $tax_label );
		return $taxonomy->name;
    }

    //Delete Temp Directory and its files
    public static function deleteDir($dirPath) {
	    if (is_dir($dirPath)) {
		    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
		        $dirPath .= '/';
		    }
		    $files = glob($dirPath . '*', GLOB_MARK);
		    foreach ($files as $file) {
		        if (is_dir($file)) {
		            self::deleteDir($file);
		        } else {
		            @unlink($file);
		        }
		    }
		    @rmdir($dirPath);
		}
	}

}

new ProductsCSVUploads();

//add_action('init', 'testing_mime');

function testing_mime () {
    var_dump(mime_content_type('/var/www/jewelbuff/._ER2254.1.jpg'), '<hr/>');
    var_dump(mime_content_type('/var/www/jewelbuff/ER2254.1.jpg'), '<hr/>');
    var_dump(mime_content_type('/var/www/jewelbuff/ER3900.1.jpg'), '<hr/>');
    var_dump(mime_content_type('/var/www/jewelbuff/._ER3900.1.jpg'));die();
}