
/**
* Theme Front Script 
*/

//ShareLocation Popup
var ShareLocation = {
	run: function() {
		this.share_location_popup = new this.ShareLocationPopup();	
	}
}

ShareLocation.ShareLocationPopup = Backbone.View.extend({

	el: 'body',

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'shareLocationModal');
		city = jQuery.cookie("city");
		if(!city) {
			jQuery("#SHARELOCATIONMODAL h2").html("Please wait while we access your location...");
			jQuery('#SHARELOCATIONMODAL').find('.select_city_location_wrap').css('display', 'none');
			_this.shareLocationModal();
			if (navigator.geolocation) {
				var timeoutVal = 10 * 1000 * 1000;
				navigator.geolocation.getCurrentPosition(
					_this.geoSuccess, 
					_this.geoError,
					{ enableHighAccuracy: true, timeout: timeoutVal, maximumAge: 0 }
				);
			}						
		} else {
			if (navigator.geolocation) {
				var timeoutVal = 10 * 1000 * 1000;
				navigator.geolocation.getCurrentPosition(
					function(position) {
						lat = position.coords.latitude;
						lng = position.coords.longitude;							
						jQuery.cookie("me_lat", lat, {path: "/", domain: ""});
						jQuery.cookie("me_lng", lng, {path: "/", domain: ""});
						jQuery.ajax({ url:script_object.ajaxurl+'?action=get_city_by_latlng&lat='+lat+'&lng='+lng,
					        success: function(data){	        	
					        	var city_name = data;
					        	selected_city = jQuery(".mm_select_city").val();
					        	if ( jQuery(".mm_select_city option[value='"+city_name+"']").length == 0 ){
					        		city_name = 'pune';
					        	} else {
					        		city_name = city_name;
					        	}
					        	jQuery('.mm_select_city option[value='+city_name+']').attr("selected", "selected");
					      		jQuery(".mm_select_city").trigger('chosen:updated');					      		
					      		jQuery.cookie("city", city_name, {path: "/", domain: ""});					      		
					        }
						});
					}, 
					function(error) {

					},					
					{ enableHighAccuracy: true, timeout: timeoutVal, maximumAge: 0 }
				);
			}
		}
	},

	events: {
		'change .mm_select_city' : 'changeLocation',
		'change .location_popup_city_select' : 'changeLocation',
	},

	shareLocationModal: function () {
		jQuery('#SHARELOCATIONMODAL').modal({
			backdrop: 'static',
			keyboard: false
		});
		jQuery('#SHARELOCATIONMODAL').on('shown.bs.modal', function (e) {
			jQuery(".modal-backdrop").addClass("share_location_modal_backdrop");			
		});
	},

	geoSuccess: function(position) {		
		var _this = this;
		lat = position.coords.latitude;
		lng = position.coords.longitude;				
		jQuery.cookie("me_lat", lat, {path: "/", domain: ""});
		jQuery.cookie("me_lng", lng, {path: "/", domain: ""});
		jQuery.ajax({ url:script_object.ajaxurl+'?action=get_city_by_latlng&lat='+lat+'&lng='+lng,
	        success: function(data){	        	
	        	var city_name = data;
	        	selected_city = jQuery(".mm_select_city").val();
	        	if ( jQuery(".mm_select_city option[value='"+city_name+"']").length == 0 ){
	        		city_name = 'pune';
	        	} else {
	        		city_name = city_name;
	        	}
	        	jQuery('.mm_select_city option[value='+city_name+']').attr("selected", "selected");
	      		jQuery(".mm_select_city").trigger('chosen:updated');
	      		jQuery(".mm_select_city").trigger('change');
	      		jQuery.cookie("city", city_name, {path: "/", domain: ""});
	      		setTimeout(function(){ 
		      		jQuery('#SHARELOCATIONMODAL').modal('hide');
		      	}, 1500);
	        }
		});
	},

	geoError: function(error) {
		jQuery('#SHARELOCATIONMODAL h2').html('Error while accessing your location');
		jQuery('#SHARELOCATIONMODAL').find('.select_city_location_wrap').css('display', 'block');
		jQuery('#SHARELOCATIONMODAL').find('.select_city_location_wrap ')
		.find('.location_popup_city_select option[value=""]').attr("selected", "selected");
	    jQuery(".location_popup_city_select").trigger('chosen:updated');
	},

});	

//MallS Dropdown Menu Backbone
var MallsDropdown = {
	run: function() {
		this.mall_dropdown_view = new this.MallDDView();	
	}
}

MallsDropdown.MallDDView = Backbone.View.extend({
	el: 'body',

	malls_menu: jQuery(".mega-menu-item a[title='malls']").parent(),
	mobile_malls_menu: jQuery(".menu-malls"),
	
	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
		_this.render();
	},

	events: {
		'change .mm_select_city' : 'changeLocation',
		'change .location_popup_city_select' : 'changeLocation',
	},

	render: function() {
		var _this = this;
		var city = jQuery.cookie("city");
		if(typeof city != 'undefined') {
			_this.renderDropdown();
		}
	},

	renderDropdown: function() {
		var _this = this;
		jQuery.ajax({
			type: 'GET',				
		    url: script_object.ajaxurl, 
		    data: {
		      	action: "show_malls_dropdown",
		      	city: jQuery.cookie("city"),
		    },
		    dataType: 'json',
		    success: function(res) {
		       	_this.malls_menu.find(".mega-sub-menu").remove();
		       	_this.malls_menu.append(res.malls);
		       	_this.mobile_malls_menu.find("a").attr("href", res.mobile_malls_url);
		    }
	    });
	},

	changeLocation: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		var location = target.val();
		_this.malls_menu.find(".dropdown-menu").html('<li class="menu-loading-malls"><a href="javascript:void(0);">Loading Malls</a></li>');
		if(location!="") {
			_this.renderDropdown();
		} else {
			
		}
		return false;
	},


});	


//DFProducts Backbone
var DFProducts = {
	run: function() {
		this.df_product_view = new this.DFProductView();	
	}
}

DFProducts.DFProductView = Backbone.View.extend({
	el: 'body',

	deals_discounts_products: jQuery("#deals_discounts_products"),
	fresh_stock_products: jQuery("#fresh_stock_products"),

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
		_this.render();
	},

	events: {
		'change .mm_select_city' : 'changeLocation',
		'change .location_popup_city_select' : 'changeLocation',
	},

	render: function() {
		var _this = this;
		var city = jQuery.cookie("city");
		if(typeof city != 'undefined') {
			_this.renderDDProducts();
			_this.renderFSProducts();
		}
	},

	renderDDProducts: function() {
		var _this = this;
		jQuery.ajax({
			type: 'GET',				
		    url: script_object.ajaxurl, 
		    data: {
		      	action: "get_deals_discount_products",
		      	city: jQuery.cookie("city"),
		    },
		    dataType: 'json',
		    success: function(res) {
		    	jQuery(".deals_discounts_fresh_stock_wrapper .ddloader").hide();
		    	_this.deals_discounts_products.find(".product_inner_row").html(res.products);
		       	_this.deals_discounts_products.find(".product_inner_row").append(res.view_all_button);
		       	_this.deals_discounts_products.css("height", "auto");
		       	_this.deals_discounts_products.css("opacity", "1");
		    }
	    });
	},

	renderFSProducts: function() {
		var _this = this;
		jQuery.ajax({
			type: 'GET',				
		    url: script_object.ajaxurl, 
		    data: {
		      	action: "get_fresh_stock_products",
		      	city: jQuery.cookie("city"),
		    },
		    dataType: 'json',
		    success: function(res) {
		    	jQuery(".deals_discounts_fresh_stock_wrapper .fsloader").hide();
		       	_this.fresh_stock_products.find(".product_inner_row").html(res.products);
		       	_this.fresh_stock_products.find(".product_inner_row").append(res.view_all_button);
		       	_this.fresh_stock_products.css("height", "auto");
		       	_this.fresh_stock_products.css("opacity", "1");
		    }
	    });
	},

	changeLocation: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		var location = target.val();
		if(location!="") {
			_this.renderDDProducts();
			_this.renderFSProducts();
		} else {
			alert("Please Select City...");
		}
		return false;
	},


});	

//Products Backbone
var Products = { 
	run: function() {
		this.product_view = new this.ProductView();		
	}	
}


Products.ProductView = Backbone.View.extend({
	
	el: 'body',

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'productViewModal');
	},

	events: {
		'click .product_preview' : 'productViewModal',
		'singletap .product_preview' : 'productViewModal',
		'doubletap .product_preview' : 'productViewModal',
		'click .add_product_to_wishlist' : 'addProductToWishList',
		'click .product_malling_delivery' : 'productMallingDelivery',
		'click .malling_gd' : 'generateProductCoupon',
		'click .show_product_detailed_desc' : 'showDetailedProductDesc',
	},

	addProductToWishList: function(event) {
		var _this = this;
		var target = jQuery( event.target );
		var data_action = target.parent().attr("data-action");
		target.hide();
		target.parent().find("i").css('display', 'inline-block');
		if(data_action!="") {
			jQuery.post( data_action, function( data ) {
				if(data == "added") {
					target.parent().hide();
					target.parent().next().show();
					target.show();
					target.parent().find("i").hide();
				} else {
					alert("There is error please try again later !!!");
					target.show();
					target.parent().find("i").hide();
				}
			});
		}
	},

	productViewModal: function(event) {
		var _this = this;
		var target = jQuery( event.target );
		if(!target.hasClass('product_preview')) {
			target = target.parent();
		}	
		var data_target = target.attr('data-target');
		var productid = target.attr('data-product-id');
		var loading_location = target.attr('data-loading-location');
		var modal = jQuery(data_target);
		jQuery('#productview'+productid).find(".success_error").html("");
		jQuery('#productview'+productid).find(".success_error").hide();
		if(modal.length == 0) {
			//target.css('display', 'none');
			target.parent().find(".product_view_loader").css('display', 'inline-block');
			jQuery.ajax({
				type: 'GET',				
		        url: script_object.ajaxurl, 
		        data: {
		           	productid: productid,
		           	action: "load_product_modal",
		           	loading_location: loading_location,
		        },
		        dataType: 'json',
		        success: function(res) {
		        	modal = res.modal;
		        	jQuery("body").append(modal);
		        	jQuery('#productview'+productid).modal('show');
		        	//target.css('display', 'inline-block');
					target.parent().find(".product_view_loader").css('display', 'none');
					jQuery(".elastislide-vertical ul li:first-child").addClass("active_product_thumb");
					setTimeout(function(){
						product_images_slider();
						poup_product_vertical_image_slider('productvertical'+productid);
					}, 1500);
					product_malling_lazy_tabs_tooltips();
			    }
	        });	
		}
		jQuery(".elastislide-vertical ul li:first-child").addClass("active_product_thumb");
	},

	showDetailedProductDesc: function(event) {
		var _this = this;
		var target = jQuery( event.target );
		text1 = target.attr("change_desc_text1");
		text2 = target.attr("change_desc_text2");
		open_close = target.attr("open_close");
		if(open_close == "open") {
			target.attr("open_close", "close");
			target.html(text1);
			target.next().slideDown();
		} else {
			target.attr("open_close", "open");
			target.html(text2);
			target.next().slideUp();
		}	
	},

	productMallingDelivery: function(event) {
		var _this = this;

		var target = jQuery( event.target );
		target.parent().find(".activetab").removeClass("activetab");
		target.addClass("activetab");
		action = target.attr("data-action");
		
		var button_text = "Get Coupon";
		var product_enquiry = target.parent().parent().find(".product_enquiry");
		product_enquiry.css("display", "block");
 		if(action=="malling") {
 			button_text = product_enquiry.find(".malling_gd").attr("data-malling");
 			product_enquiry.find(".form-action").val("malling");
 			product_enquiry.find("input[name='user_phone']").val('');
 			product_enquiry.find("input[name='user_email']").val('');
 			product_enquiry.find("ul.success_error li").html('');
 			product_enquiry.find("ul.success_error").hide();
 			product_enquiry.find("input[name='user_phone']").removeAttr("disabled", "disabled");
 			product_enquiry.find("input[name='user_email']").removeAttr("disabled", "disabled");
 			product_enquiry.find(".malling_gd").removeAttr("disabled", "disabled");
		} else {
			button_text = product_enquiry.find(".malling_gd").attr("data-gd");
			product_enquiry.find(".form-action").val("gd");
			product_enquiry.find("input[name='user_phone']").val('');
 			product_enquiry.find("input[name='user_email']").val('');
 			product_enquiry.find("ul.success_error li").html('');
 			product_enquiry.find("ul.success_error").hide();
			product_enquiry.find("input[name='user_phone']").removeAttr("disabled", "disabled");
 			product_enquiry.find("input[name='user_email']").removeAttr("disabled", "disabled");
 			product_enquiry.find(".malling_gd").removeAttr("disabled", "disabled");
		}
		product_enquiry.find(".malling_gd").html(button_text);		
	},

	generateProductCoupon: function(event) {
		var _this = this;
		var target = jQuery( event.target );
		var form_values = target.parent().parent().serialize();
		var selected_size = '';
		if(target.parent().parent().parent().parent().find(".product_size").length>0) {
			selected_size = target.parent().parent().parent().parent().find(".product_size .selected_csize").val();
		}
		
		success_error = target.parent().parent().parent().find(".success_error");
		loader = target.parent().find(".cgd_loader");
		success_error.hide();
		loader.css('display', 'inline-block');
		jQuery.ajax({
            type: 'POST',
            url: script_object.ajaxurl,
            data: {
                action: 'get_product_coupon',
                form_values: form_values,
                selected_size: selected_size,
            },
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
            	loader.hide();
            	success_error.show();
            	if(data.result=="success") {
            		success_error.removeClass("error").addClass(data.result).html(data.message);
            	} else {
            		success_error.removeClass("success").addClass(data.result).html(data.message);
            	}
            }
        });  
		return false;
	},

});


var MMBackbone = {
	run: function() {
		this.mm_routes = new this.MMRoutes();
		Backbone.history.start();
	}
}

//Routes MM Paginations AJAX Routes
MMBackbone.MMRoutes = Backbone.Router.extend({
	routes: {
		'' : '',
		'products/paged/:page' : 'mmProductsPaginationView',
		'products/paged/:page/' : 'mmProductsPaginationView',
		'*path'  : 'notFound'
	},

	initialize: function(){
		ProductsPagination.product_pagination_view = new ProductsPagination.ProductsPaginationView();
	},

	mmProductsPaginationView: function(page) {
		ProductsPagination.product_pagination_view.showProducts(page);
	},

	notFound: function() {
		alert("Error: Invalid Page");
	}

});

//ProductsPaginationView 

var ProductsPagination = {
	run: function() {
		this.product_pagination_view = new this.ProductsPaginationView();	
	}
}

ProductsPagination.ProductsPaginationView = Backbone.View.extend({
	el: 'body',

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
		_this.render();
	},

	events: {
		'click .navigate_pagination_pages' : 'productPagination',		
	},

	productPagination: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		target.html('<i class="fa fa-cog fa-spin"><i>');
	},

	showProducts: function(page) {
		dnd = '';
		jQuery(".dd_products_tax_page_filtering .dnd_filters input:checked").each(function(){
			key = jQuery(this).val();
			dnd = key;
		});
		var min_price = jQuery( '.price_filter_slider_wrap input[name="min_price"]' ).val(),
			max_price = jQuery( '.price_filter_slider_wrap input[name="max_price"]' ).val();
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
            	action: 'show_paginate_products',
                products_source: script_object.products_source,
                product_cats: script_object.product_cats,
                shop_id: script_object.shop_id,
                page: page,
                city: jQuery.cookie("city"),
                search_term: script_object.search_term,
                dnd: dnd,
                min_price: min_price,
                max_price: max_price,
            },
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
            	jQuery(".products_inner_wrap").html(data.products);
	            jQuery(".products_pagination").html(data.pagination);
            }
        });
	}

});	


//ProductsFiltering Backbone
var ProductsFiltering = {
	run: function() {
		this.product_filtering_view = new this.ProductsFilteringView();	
	}
}

ProductsFiltering.ProductsFilteringView = Backbone.View.extend({
	el: 'body',

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
		_this.render();
	},

	events: {
		"click .dd_fs_product_cat_filters .products_cat_filters input" : "DProductCatFiltering",
		"change .dd_product_filters .dd_filters input" : "DDProductFiltering",
		"click .mm_product_attributes_widget .product_attributes input" : 'ProductAttributeFiltering',
		"change .dd_products_tax_page_filtering .dnd_filters input" : 'ProductAttributeFiltering',
		"click .mm_product_attributes_widget h4" : "productAttributesAccordion",
		"click .mm_product_attributes_widget h4 i" : "productAttributesAccordionIcon",
	},

	render: function() {
		var _this = this;
		jQuery(".dd_fs_product_cat_filters input").removeAttr("checked");
		jQuery(".dd_filters input").removeAttr("checked");
		jQuery(".dd_filters input#bothdd").attr("checked", "checked");
		jQuery(".mm_product_attributes_widget .product_attributes input").removeAttr("checked");
	},

	DProductCatFiltering: function(event) {
		var _this = this;

		MMBackbone.mmroutes = new MMBackbone.MMRoutes();
		MMBackbone.mmroutes.navigate("", {trigger: true, replace: true});
		jQuery(".products_pagination").html("");

		var dd = jQuery(".dd_product_filters .dd_filters input:checked").val();
		var cats = Array();
		jQuery(".dd_fs_product_cat_filters .products_cat_filters input:checked").each(function(){
			cats.push(jQuery(this).val());
		});

		if(script_object.ddfsproducts == 'dd') {
			if(dd!='bothdd') {
				_this.DDProductFiltering();
			} else {
				_this.DProductCatFilterings(cats);
			}
		} else {
			_this.DProductCatFilterings(cats);
		}
	},

	DProductCatFilterings: function(cats) {

		if(cats.length>0) {
				jQuery.ajax({
					type: 'GET',				
				    url: script_object.ajaxurl, 
				    data: {
				       	action: "dd_fs_product_cat_filters",
				       	cats: cats,
				       	ddfsproducts: script_object.ddfsproducts,
				       	city: jQuery.cookie("city"),
				    },
				    dataType: 'json',
				    success: function(res) {
				    	if(res.products!='') {
				       		jQuery(".dd_fs_products_inner_wrap").html(res.products);
				       	} else {
				       		jQuery(".dd_fs_products_inner_wrap").html("<h4>There are no products..</h4>");
				       	}
				    }
			    });		
			} else {
				var _this = this;
				var cats = Array();
				jQuery(".dd_fs_product_cat_filters .products_cat_filters input:checked").each(function(){
					cats.push(jQuery(this).val());
				});
				jQuery.ajax({
					type: 'GET',				
				    url: script_object.ajaxurl, 
				    data: {
				       	action: "load_all_dd_fs_products",
				       	ddfsproducts: script_object.ddfsproducts,
				       	city: jQuery.cookie("city"),
				    },
				    dataType: 'json',
				    success: function(res) {
				       	jQuery(".dd_fs_products_inner_wrap").html(res.products);
				       	jQuery(".products_pagination").html(res.pagination);
				    }
			    });
			}
	},

	DDProductFiltering: function(event) {
		MMBackbone.mmroutes = new MMBackbone.MMRoutes();
		MMBackbone.mmroutes.navigate("", {trigger: true, replace: true});
		jQuery(".products_pagination").html("");

		var dd = jQuery(".dd_product_filters .dd_filters input:checked").val();
		var cats = Array();
		jQuery(".dd_fs_product_cat_filters .products_cat_filters input:checked").each(function(){
			cats.push(jQuery(this).val());
		});

		jQuery.ajax({
				type: 'GET',				
			    url: script_object.ajaxurl, 
			    data: {
			       	action: "deal_discounts_product_filters",
			       	cats: cats,
			       	dd: dd,
			       	city: jQuery.cookie("city"),
			    },
			    dataType: 'json',
			    success: function(res) {
			    	if(res.products!='') {
			       		jQuery(".dd_fs_products_inner_wrap").html(res.products);
			       		jQuery(".products_pagination").html(res.pagination);
			       	} else {
			       		jQuery(".dd_fs_products_inner_wrap").html("<h4>There are no products..</h4>");
			       		jQuery(".products_pagination").html(res.pagination);
			       	}
			    }
		});	
	},

	ProductAttributeFiltering: function(event) {
		var _this = this;
		var cats = Array();

		MMBackbone.mmroutes = new MMBackbone.MMRoutes();
		MMBackbone.mmroutes.navigate("", {trigger: true, replace: true});
		jQuery(".products_pagination").html("");

		var min_price = jQuery( '.price_filter_slider_wrap input[name="min_price"]' ).val(),
                max_price = jQuery( '.price_filter_slider_wrap input[name="max_price"]' ).val();

		var main_category = script_object.product_cats;
                var selected_post_ids = Array();
                var post_ids = '';
                var post_ids_new_array = Array();
		jQuery(".mm_product_attributes_widget .product_attributes input:checked").each(function(){
			taxonomy = jQuery(this).attr("taxonomy");
                        var tax = jQuery(this).attr('taxonomy');
                        post_ids = jQuery(this).attr('data-post-ids');
			cats.push({'taxonomy': taxonomy,  'term':  jQuery(this).val()});
                        post_ids_new_array.push(post_ids);
		});
                
		dnd = '';
		jQuery(".dd_products_tax_page_filtering .dnd_filters input:checked").each(function(){
			key = jQuery(this).val();
			dnd = key;
		});

		jQuery('.category_products_inner_wrap').block({ message: null, overlayCSS: { background: 'snow url(' + woocommerce_params.ajax_loader_url +') no-repeat center', opacity: 0.5 } });
		jQuery(".sidebar").block({ message: null, overlayCSS: { background: 'snow url(' + woocommerce_params.ajax_loader_url +') no-repeat center 35%', opacity: 0.5 } });
                var currency = script_object.currency_symbol;
		if(cats.length==0) {
                    console.log('1');
                    var min_price1 = jQuery( '.price_filter_slider_wrap input[name="min_price"]' ).attr('data-min');
                    var max_price1 = jQuery( '.price_filter_slider_wrap input[name="max_price"]' ).attr('data-max');
                                         jQuery( ".price_filter_slider" ).slider({
                                            min: parseInt(min_price1),
                                            max: parseInt(max_price1)
                                          });
                                          jQuery('.price_range_wrap .price_range').text( " "+currency+" " + min_price1 + " - "+currency+" " + max_price1 );
                                          jQuery( '.price_filter_slider_wrap input[name="min_price"]' ).val( min_price1 );
                                            jQuery( '.price_filter_slider_wrap input[name="max_price"]' ).val( max_price1 );
			jQuery.ajax({
					type: 'GET',				
				    url: script_object.ajaxurl, 
				    data: {
				       	action: "show_current_category_products",
				       	main_category: main_category,
				       	city: jQuery.cookie("city"),
				       	dnd: dnd,
				       	min_price: min_price1,
				       	max_price: max_price1,
				       	search_term: script_object.search_term,
				    },
				    dataType: 'json',
				    success: function(res) {
				    	jQuery('.category_products_inner_wrap').unblock();
				       	jQuery('.sidebar').unblock();
				    	if(res.products!='') {
				       		jQuery(".category_products_inner_wrap").html(res.products);
				       		jQuery(".products_pagination").html(res.pagination);
				       		//jQuery.scrollTo(10, 1000);
				       	} else {
				       		jQuery(".category_products_inner_wrap").html("<h4>There are no products..</h4>");
				       		jQuery(".products_pagination").html("");
				       	}
                                        
                                        var commonProductIds = Array();
                                        if(res.new_product_attributes != '') {
                                            commonProductIds = res.new_product_attributes;
                                            jQuery('body').find('#product-attributes-container .mm_product_attributes_widget').each(function() {
                                            jQuery(this).find('.product_attributes').hide();
                                            jQuery(this).find('.product_attributes').each(function() {
                                                var product_post_ids = jQuery(this).find('input').attr('data-post-ids');
                                                var product_post_ids_array = product_post_ids.split("|");
                                                var value = jQuery(this).find('input').attr('value');
                                                for (var i = 0; i < product_post_ids_array.length; i++) {
                                                    for (var j = 0; j < commonProductIds.length; j++) {
                                                        if(commonProductIds[j] == product_post_ids_array[i]) {
                                                            jQuery(this).addClass('active');
                                                            jQuery(this).show();
                                                        }
                                                    }
                                                
                                                }
                                                });
                                            });
                                        }
                                        
//                                        jQuery('body').find('#product-attributes-container .mm_product_attributes_widget').each(function() {
//                                            jQuery(this).find('.product_attributes').show();
//                                        });
				       	var top_offset = jQuery(".page-header").offset();
                        top_offset = parseInt(top_offset.top);                             
                        jQuery(window).scrollTo(top_offset, 800);
				    }
			});
		} else {
                    console.log('2');
                    var min_price1 = jQuery( '.price_filter_slider_wrap input[name="min_price"]' ).attr('data-min');
                    var max_price1 = jQuery( '.price_filter_slider_wrap input[name="max_price"]' ).attr('data-max');
                    var product_max_price = '';
                    var product_min_price = '';
                    var current_min_price = '';
                    var current_max_price = '';
			jQuery.ajax({
					type: 'GET',				
				    url: script_object.ajaxurl, 
				    data: {
				       	action: "products_attributes_filtering",
				       	main_category: main_category,
				       	cats: cats,
				       	dnd: dnd,
				       	city: jQuery.cookie("city"),
				       	min_price: min_price,
				       	max_price: max_price,
				    },
				    dataType: 'json',
				    success: function(res) {
				    	if(res.products!='') {
				       		jQuery(".category_products_inner_wrap").html(res.products);
				       		jQuery(".products_pagination").html(res.pagination);
				       	} else {
				       		jQuery(".category_products_inner_wrap").html("<h4>There are no products..</h4>");
				       		jQuery(".products_pagination").html("");
				       	}
                                        if(res.product_max_price != '') {
                                            product_max_price = parseInt(res.product_max_price);
                                        }
                                        if(res.product_min_price != '') {
                                            product_min_price = parseInt(res.product_min_price);
                                        }
                                        if(min_price!='' && max_price != '') {
                                         jQuery( ".price_filter_slider" ).slider({
                                            min: product_min_price,
                                            max: product_max_price
                                          });
                                      }
                                      if(res.current_min_price !='') {
                                          current_min_price = res.current_min_price;
                                      }
                                      if(res.current_max_price !='') {
                                          current_max_price = res.current_max_price;
                                      }
                                          jQuery('.price_range_wrap .price_range').text( " "+currency+" " + current_min_price + " - "+currency+" " + current_max_price );
                                          jQuery( '.price_filter_slider_wrap input[name="min_price"]' ).val( current_min_price );
                                            jQuery( '.price_filter_slider_wrap input[name="max_price"]' ).val( current_max_price );
//                                            jQuery( '.price_filter_slider_wrap input[name="min_price"]' ).attr('data-min', min_price);
//                                            jQuery( '.price_filter_slider_wrap input[name="max_price"]' ).attr('data-max', max_price );
                                        var newCommonProductArray = [];
                                        for (var n = 0; n < post_ids_new_array.length; n++) {
                                            newCommonProductArray.push(post_ids_new_array[n].split("|"));
                                        }
                                        var newArrayLength = newCommonProductArray.length;
                                        var commonProductIds = [];
                                        if (newArrayLength) {
                                            var commonProductIds = newCommonProductArray[0];
                                            if (newArrayLength > 1) {
                                                for (var m = 0; m < newArrayLength; m++){
                                                    if (m != newArrayLength) {
                                                        var idtemp = jQuery.grep(commonProductIds, function(element) {
                                                            return jQuery.inArray(element, newCommonProductArray[m+1] ) !== -1;
                                                        });
                                                        if (idtemp.length > 0) {
                                                            commonProductIds = idtemp;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        
                                        if(res.new_product_attributes != '') {
                                            commonProductIds = res.new_product_attributes;
                                            jQuery('body').find('#product-attributes-container .mm_product_attributes_widget').each(function() {
                                            jQuery(this).find('.product_attributes').hide();
                                            jQuery(this).find('.product_attributes').each(function() {
                                                var product_post_ids = jQuery(this).find('input').attr('data-post-ids');
                                                var product_post_ids_array = product_post_ids.split("|");
                                                var value = jQuery(this).find('input').attr('value');
                                                for (var i = 0; i < product_post_ids_array.length; i++) {
                                                    for (var j = 0; j < commonProductIds.length; j++) {
                                                        if(commonProductIds[j] == product_post_ids_array[i]) {
                                                            jQuery(this).addClass('active');
                                                            jQuery(this).show();
                                                        }
                                                    }
                                                
                                                }
                                                });
                                            });
                                        }
                                        
                                        
                                        jQuery('body').find('#product-attributes-container .mm_product_attributes_widget').each(function() {
                                            jQuery(this).find('.product_attributes').hide();
                                            jQuery(this).find('.product_attributes').each(function() {
                                                var product_post_ids = jQuery(this).find('input').attr('data-post-ids');
                                                var product_post_ids_array = product_post_ids.split("|");
                                                var value = jQuery(this).find('input').attr('value');
                                                for (var i = 0; i < product_post_ids_array.length; i++) {
                                                    for (var j = 0; j < commonProductIds.length; j++) {
                                                        if(commonProductIds[j] == product_post_ids_array[i]) {
                                                            jQuery(this).addClass('active');
                                                            jQuery(this).show();
                                                        }
                                                    }
                                                
                                            }
                                            });
                                        });
                                    
				       	jQuery('.category_products_inner_wrap').unblock();
				       	jQuery('.sidebar').unblock();			       	
				       	var top_offset = jQuery(".page-header").offset();
                        top_offset = parseInt(top_offset.top);                             
                        jQuery(window).scrollTo(top_offset, 800);
				    }
			});	

		}
	},

	productAttributesAccordion: function(event) {
		var _this = this;
		var target = jQuery( event.target );
		if(!target.hasClass("opencat")) {
			target.next().slideDown(function(){
				target.addClass("opencat");
				target.find("i").removeClass("fa-plus-square").addClass("fa-minus-square");
			});

		} else {
			target.next().slideUp(function(){
				target.removeClass("opencat");
				target.find("i").removeClass("fa-minus-square").addClass("fa-plus-square");
			});
		}
	},

	productAttributesAccordionIcon: function(event) {
		var _this = this;
		var target_icon = jQuery( event.target );
		var target = target_icon.parent();
		if(!target.hasClass("opencat")) {
			target.next().slideDown(function(){
				target.addClass("opencat");
				target.find("i").removeClass("fa-plus-square").addClass("fa-minus-square");
			});

		} else {
			target.next().slideUp(function(){
				target.removeClass("opencat");
				target.find("i").removeClass("fa-minus-square").addClass("fa-plus-square");
			});
		}
	},

});

