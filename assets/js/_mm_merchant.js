/**
* Merchant Dashboard
*/

var MD = {
    run: function() {
//     this.register_merchant = new this.RegisterMerchant();
        this.dashboard_menu = new this.DashboardMenu();
        this.md_routes = new this.MDRoutes();
//      Backbone.history.stop();
        Backbone.history.start();
    }
}
//
//MD.run();
//console.log(MD);
//Routes MD Menu Navigations
MD.MDRoutes = Backbone.Router.extend({
	
	routes: {
            '' : 'myShopView',
            'products/' : 'mProductsView',
            'products/page/:page' : 'mProductsPaginationView',
            'products/page/:page/' : 'mProductsPaginationView',
            'upload-product/' : 'productUploadView',
            'edit-product/:product_id' : 'editProductView',
            'edit-product/:product_id/' : 'editProductView',
            'coupons/' : 'showCouponsList',
            'orders/' : 'showOrdersList',
            'manage-sets/' : 'manageSetList',
            'shop-advertisements/' : 'shopAdsView',
            'csv-products-upload/' : 'productsCSVUploads',
            "*path"  : "notFound"
	},
        
	initialize: function(){
            MD.my_shop_view = new MD.MyShopView();
//		MD.register_merchant_view = new MD.RegisterMerchantView();
            MD.m_products_view = new MD.MProductsView();
            MD.products_csv_upload_view = new MD.ProductsCSVUploadView();
            MD.product_upload_edit_view = new MD.ProductUploadEditView();
            MD.coupons_list_view = new MD.CouponsListView();
            MD.orders_list_view = new MD.OrdersListView();
            MD.manage_set_view = new MD.ManageSetList();
            MD.shop_ads_view = new MD.ShopAdsView();
	},

	myShopView: function() {
            MD.my_shop_view.render();
	},
//        registerMerchantView: function() {
//		MD.register_merchant_view.render();
//	},
	mProductsView: function(page) {
            MD.m_products_view.render(page);
	},

	mProductsPaginationView: function(page) {
            MD.m_products_view.getMerchantProducts(page);
	},

	productsCSVUploads: function() {
            MD.products_csv_upload_view.render();
	},

	productUploadView: function() {
            MD.product_upload_edit_view.render_upload_view();
	},

	editProductView: function(product_id) {
            MD.product_upload_edit_view.render_edit_view(product_id);
	},

	showCouponsList: function() {
            MD.coupons_list_view.render();
	},

	showOrdersList: function() {
            MD.orders_list_view.render();
	},
        manageSetList: function() {
            MD.manage_set_view.render();
	},
	shopAdsView: function() {
            MD.shop_ads_view.render();
	},
 
	notFound: function() {
            alert("Error: Invalid Page");
	}

});
//console.log(Backbone.history.getFragment());
//console.log('jbdfhd');
//Merchant Dashboard Menu
MD.DashboardMenu = Backbone.View.extend({
	
	el: 'body',
	events: {
            'click .md_dashboard_menu li a.navigate' : 'navigateMDMenus',
	},

	navigateMDMenus: function(event) {
            var target = jQuery( event.target );
            var data_menu_target = target.attr('data-menu-target');
            if(data_menu_target!="") {
                    jQuery(".merchant_dashboard_container").html('<div class="loading_templates"><i class="fa fa-spin fa-cog"></i></div>');
                    data_menu_target = '#/'+data_menu_target+'/';
            }
            MD.md_routes.navigate(data_menu_target, {trigger: true});
            target.parent().parent().parent().removeClass("open");
            return false;
	}

});	

MD.ShopAdsView = Backbone.View.extend({
	el: '.merchant_dashboard_container',
	
	template: _.template(jQuery("#shop_ads_view_tml").html()),

	large_ads_uploader: '',	
	small_ads_uploader: '',	

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
	},

	events: {
		'click .ads_image_delete' : 'deleteShopAds'
	},

	render: function() {
		var _this = this;
		_this.$el.html(_this.template());
		_this.largeAdsImagesUpload();
		_this.smallAdsImagesUpload();
	},

	deleteShopAds: function(event) {
		var _this = this;
        var target = jQuery(event.target);
        var ads_type = target.attr('data-ads-type');
        if (confirm(script_object.confirmMsg)) {
            data = {
                'attach_id':target.attr('data-upload_id'),
                'nonce':script_object.remove,
                'ads_type': ads_type,
                'action':'delete_shop_ads'
            };
            jQuery.post(script_object.ajaxurl, data, function () {
                target.parent().remove();
            });
        }
	},

	largeAdsImagesUpload: function() {
		var _this = this;
		if (typeof(plupload) === 'undefined') {
                return;
        }

        _this.large_ads_uploader = new plupload.Uploader(script_object.up_large_ads_images);
        _this.large_ads_uploader.bind('Init', function(up, params) { });

        //initilize  wp plupload
        _this.large_ads_uploader.init();
        _this.large_ads_uploader.bind('FilesAdded', function (up, files) {
        	jQuery.each(files, function (i, file) {
                jQuery('#large_ads_list_view').append(
                '<div id="' + file.id + '" class="imagepreview col-sm-2 col-xs-2">',
                '<img src="http://placehold.it/150x150&text=Uploading" />',
                '<div class="imageuploadprogress">',
                '<div class="progress"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"><span class="sr-only">0%</span></div></div>',
                '</div></div>');
            });

            up.refresh(); // Reposition Flash/Silverlight
            _this.large_ads_uploader.start();
        });

        _this.large_ads_uploader.bind('UploadProgress', function (up, file) {
        	jQuery('#' + file.id + " .progress .progress-bar").html(file.percent + "%");
        	jQuery('#' + file.id + " .progress .progress-bar").css('width', file.percent + "%");
        });

        // On Error occur
        _this.large_ads_uploader.bind('Error', function (up, err) {
        	alert("Error: While Uploading Images");
            up.refresh(); // Reposition Flash/Silverlight
            file_id = err.file.id;
            jQuery('#large_ads_list_view #'+ file_id).remove();
        });

        _this.large_ads_uploader.bind('FileUploaded', function (up, file, response) {
            var result = jQuery.parseJSON(response.response);
            if (result.success) {
                jQuery('#large_ads_list_view #'+ file.id).replaceWith(result.html);
                jQuery( "#large_ads_list_view" ).sortable();
            }
        });
    },


    smallAdsImagesUpload: function() {
		var _this = this;
		if (typeof(plupload) === 'undefined') {
                return;
        }

        _this.small_ads_uploader = new plupload.Uploader(script_object.up_small_ads_images);
        _this.small_ads_uploader.bind('Init', function(up, params) { });

        //initilize  wp plupload
        _this.small_ads_uploader.init();
        _this.small_ads_uploader.bind('FilesAdded', function (up, files) {
        	jQuery.each(files, function (i, file) {
                jQuery('#small_ads_list_view').append(
                '<div id="' + file.id + '" class="imagepreview col-sm-2 col-xs-2">',
                '<img src="http://placehold.it/150x150&text=Uploading" />',
                '<div class="imageuploadprogress">',
                '<div class="progress"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"><span class="sr-only">0%</span></div></div>',
                '</div></div>');
            });

            up.refresh(); // Reposition Flash/Silverlight
            _this.small_ads_uploader.start();
        });

        _this.small_ads_uploader.bind('UploadProgress', function (up, file) {
        	jQuery('#' + file.id + " .progress .progress-bar").html(file.percent + "%");
        	jQuery('#' + file.id + " .progress .progress-bar").css('width', file.percent + "%");
        });

        // On Error occur
        _this.small_ads_uploader.bind('Error', function (up, err) {
        	alert("Error: While Uploading Images");
            up.refresh(); // Reposition Flash/Silverlight
            file_id = err.file.id;
            jQuery('#small_ads_list_view #'+ file_id).remove();
        });

        _this.small_ads_uploader.bind('FileUploaded', function (up, file, response) {
            var result = jQuery.parseJSON(response.response);
            if (result.success) {
                jQuery('#small_ads_list_view #'+ file.id).replaceWith(result.html);
                jQuery( "#small_ads_list_view" ).sortable();
            }
        });
    },



});

//Render My Shop View
MD.MyShopView = Backbone.View.extend({
	
	el: '.merchant_dashboard_container',
	
	template: _.template(jQuery("#view_my_shop_tml").html()),
	
	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
	},

	render: function() {
		var _this = this;
		_this.$el.html(_this.template());
		_this.getProductsCount();
		_this.getOrdersCount();
		_this.getCouponsCount();
		_this.getMostViewedProduts();
	},

	getProductsCount: function() {
		var _this = this;
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'get_products_count',
            },
            cache: false,
            success: function(data, textStatus, XMLHttpRequest) {
            	//jQuery(".total_products_count").find("h3").html(data);
            	jQuery({count: 0}).animate({count: data}, {
                        duration: 1000,
                        easing:'swing', 
                        step: function() { 
                                jQuery('.total_products_count h3').text(Math.ceil(this.count));
                        }
                    });
                }
        }); 
	},

	getOrdersCount: function() {
		var _this = this;
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'get_orders_count',
            },
            cache: false,
            success: function(data, textStatus, XMLHttpRequest) {
            	//jQuery(".total_coupons_count").find("h3").html(data);
            	jQuery({count: 0}).animate({count: data}, {
					duration: 1000,
					easing:'swing', 
					step: function() { 
						jQuery('.total_orders_count h3').text(Math.ceil(this.count));
					}
				});
			}
        }); 
	},

	getCouponsCount: function() {
		var _this = this;
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'get_coupons_count',
            },
            cache: false,
            success: function(data, textStatus, XMLHttpRequest) {
            	//jQuery(".total_coupons_count").find("h3").html(data);
            	jQuery({count: 0}).animate({count: data}, {
					duration: 1000,
					easing:'swing', 
					step: function() { 
						jQuery('.total_coupons_count h3').text(Math.ceil(this.count));
					}
				});
            }
        }); 
	},

	getMostViewedProduts: function() {
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'get_most_viewed_produts',
            },
            cache: false,
            success: function(data, textStatus, XMLHttpRequest) {
            	jQuery(".most_viewed_products").html(data);
            }
        });
	}, 

});

//Render Coupons List View
MD.CouponsListView = Backbone.View.extend({

	el: '.merchant_dashboard_container',

	template: _.template(jQuery("#coupons_list_view_tml").html()),

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
	},

	events: {
		'click .remove_product_coupon' : 'removeProductCoupon',
	},

	render: function() {
		var _this = this;
		_this.$el.html(_this.template());
		jQuery('#coupons_list').dataTable();
	},

	removeProductCoupon: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		var coupon_id = target.attr("data-coupon-id");
		target.html("<i class='fa fa-cog fa-spin'></i>");
		jQuery.ajax({
	        type: 'GET',
	        url: script_object.ajaxurl,
	        data: {
	            action: 'remove_product_coupon',
	            coupon_id: coupon_id,
	        },
	        cache: false,
	        dataType: 'json',
	        success: function(data, textStatus, XMLHttpRequest) {
	        	target.html("Remove");
	        	if(data.response=='deleted') {
	           		jQuery("#coupons_list #coupon_row_"+coupon_id).fadeOut().promise().done(function(){
	           			jQuery("#coupons_list #merchant_product_"+coupon_id).remove();
	           		});	
	            } else {
	            	alert("Coupon not deleted please try again!!!");
	            }
	        }
	    });
	}
});


//Render Orders List View
MD.OrdersListView = Backbone.View.extend({

	el: '.merchant_dashboard_container',

	template: _.template(jQuery("#orders_list_view_tml").html()),

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
	},

	events: {
		'click .remove_merchant_order' : 'removeMerchantOrder',
	},

	render: function() {
		var _this = this;
		_this.$el.html(_this.template());
		jQuery('#orders_list').dataTable();
	},

	removeMerchantOrder: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		var order_id = target.attr("data-order-id");
		target.html("<i class='fa fa-cog fa-spin'></i>");
		jQuery.ajax({
	        type: 'GET',
	        url: script_object.ajaxurl,
	        data: {
	            action: 'remove_merchant_orders',
	            order_id: order_id,
	        },
	        cache: false,
	        dataType: 'json',
	        success: function(data, textStatus, XMLHttpRequest) {
	        	target.html("Remove");
	        	if(data.response=='deleted') {
	           		jQuery("#orders_list #order_row_"+order_id).fadeOut().promise().done(function(){
	           			jQuery("#orders_list #order_row_"+order_id).remove();
	           		});	
	            } else {
	            	alert("Order not deleted please try again!!!");
	            }
	        }
	    });
	}

});

//Render Orders List View
MD.ManageSetList = Backbone.View.extend({

	el: '.merchant_dashboard_container',

	template: _.template(jQuery("#manage_sets_tml").html()),

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
//                jQuery("#merchant_products_sets . input.created_set_details").attr("checked");
                
	},

	events: {
                'submit .create_product_set_from' : 'CreateProductsSet',
                'submit .edit_product_set_from' : 'EditProductsSet',
		'click #add_product_for_set' : 'addProductToSet',
                'click .delete_product_set' : 'deleteProductToSet',
                'click .created_set_details' : 'CreatedSetDetails',
                'click .product_set_edit' : 'ProductSetDetailsEdit',
                'click .product_set_delete' : 'ProductSetDetailsDelete'
                
	},

	render: function() {
		var _this = this;
		_this.$el.html(_this.template());
	},
      addProductToSet: function(event) {
		var _this = this;
		var target = jQuery(event.target);
                product = jQuery('#select_product_set').val();
                products_set_list_view_el = jQuery("#products_set_list_view");
                
               
//		console.log(product);
                if(product != ""){
                      target.append('<i class="fa fa-cog fa-spin"><i>');
                    jQuery.ajax({
                        type: 'GET',
                        url: script_object.ajaxurl,
                        data: {
                            action: 'add_merchant_set_products',
                            product_id: product,
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(data, textStatus, XMLHttpRequest) {
                            target.find(".fa-spin").remove();
                            products_set_list_view_el.append(data.response);
                            if(product == data.product_id){
                               jQuery("#select_product_set option[value=" + data.product_id + "]").hide();
                               jQuery('#select_product_set').val("");
                            }
                        }
                    });   	
                }else{
                alert("Please Select Product for set !");  
                }
            return false;     
	},
        deleteProductToSet: function(event) {
                var _this = this;
		var target = jQuery(event.target);
                var product_id = target.parent().parent('.added_product_into_set').attr('product_id');
                jQuery("#select_product_set option[value=" + product_id + "]").show();
                target.parent().parent('.added_product_into_set').remove();
               
	},
        CreateProductsSet: function(event) {
		var _this = this;
                var target = jQuery(event.target);
                jQuery(".product_set_container").find(".alert-success").hide();
		jQuery(".product_set_container").find(".alert-danger").hide();
                target.parent().find(".createsetfilterloader").css("display", "inline-block");
		var form_values = jQuery(".create_product_set_from").serialize();
//                console.log(form_values);
                jQuery.ajax({
                type: 'POST',
                url: script_object.ajaxurl,
                data: {
                    action: 'create_set_product_call',
                    form_values: form_values,
                },
                cache: false,
                dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
                target.parent().find(".createsetfilterloader").hide();
            	if(data.state=="success") {
            		jQuery(".product_set_container").find(".alert-success").show();
            		jQuery(".product_set_container").find(".alert-success ul").html(data.success);
            		
            		//Empty All Fields
            		jQuery(".create_product_set_from").find("input.form-control").val("");
                        jQuery(".create_product_set_from").find("#products_set_list_view").html("");
                        jQuery("#select_product_set").children('option').show();

            	} else {
            		jQuery(".product_set_container").find(".alert-danger").show();
            		jQuery(".product_set_container").find(".alert-danger ul").html(data.error);
            	}
            	
            }
        });    	
        return false;
	},
        CreatedSetDetails: function(event) {
		var _this = this;
                var target = jQuery(event.target);
                var cnt=0;
                target.parent().find(".catfilterloader").css("display", "inline-block");
               if(target.is(':checked')){
                    cnt++;
                }
                if(cnt == 0) {
			MD.mdroutes = new MD.MDRoutes();
			MD.mdroutes.navigate("#/manage-sets/", {trigger: true, replace: true});
			target.parent().find(".catfilterloader").hide();
		} else {
   
                jQuery.ajax({
                type: 'POST',
                url: script_object.ajaxurl,
                data: {
                    action: 'created_set_details_call'
                },
                cache: false,
                dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
                target.parent().find(".catfilterloader").hide();
//                if ($('#merchant_products_sets .product_set_inner').is(':empty')){
                        jQuery("#merchant_products_sets .product_set_inner").html(data.response);
//                    }
            	 	
            }
        });
    }  
    },
    ProductSetDetailsEdit: function(event) {
            var _this = this;
            var target = jQuery(event.target);
            var cnt=0;
            target.parent().find(".setfilterloader").css("display", "inline-block");
            var set_id = target.attr('data-set-id');

            jQuery.ajax({
                     type: 'POST',
                     url: script_object.ajaxurl,
                     data: {
                         action: 'edited_set_details_call',
                         set_id:set_id
                     },
                     cache: false,
                     dataType: 'json',
                 success: function(data, textStatus, XMLHttpRequest) {
                     target.parent().find(".setfilterloader").hide();
                        jQuery("#products_sets_list_view").html(data.response);
                        jQuery("#products_set_list_view .added_product_into_set").each(function(){
//                            product_id = jQuery(this).attr('product_id');
                            jQuery("#select_product_set option[value=" + jQuery(this).attr('product_id') + "]").hide();
                        });
                       
                 }
             });
	},
        EditProductsSet: function(event) {
		var _this = this;
                var target = jQuery(event.target);
                jQuery(".product_set_container").find(".alert-success").hide();
		jQuery(".product_set_container").find(".alert-danger").hide();
                target.parent().find(".editsetfilterloader").css("display", "inline-block");
		var form_values = jQuery(".edit_product_set_from").serialize();
//                console.log(form_values);
                jQuery.ajax({
                type: 'POST',
                url: script_object.ajaxurl,
                data: {
                    action: 'edit_set_product_call',
                    form_values: form_values,
                },
                cache: false,
                dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
                target.parent().find(".editsetfilterloader").hide();
            	if(data.state=="success") {
            		jQuery(".product_set_container").find(".alert-success").show();
            		jQuery(".product_set_container").find(".alert-success ul").html(data.success);
            		
            		//Empty All Fields
//                        MD.mdroutes = new MD.MDRoutes();
//			MD.mdroutes.navigate("#/manage-sets/", {trigger: true, replace: true});
//            		jQuery(".create_product_set_from").find("input.form-control").val("");
//                        jQuery(".create_product_set_from").find("#products_set_list_view").html("");
//                        jQuery("#select_product_set").children('option').show();

            	} else {
            		jQuery(".product_set_container").find(".alert-danger").show();
            		jQuery(".product_set_container").find(".alert-danger ul").html(data.error);
            	}
            	
            }
        });    	
        return false;
	},
        ProductSetDetailsDelete: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		var product_id = target.attr('data-set-id');
		jQuery(".product_set_container").find(".alert-success").hide();
		jQuery(".product_set_container").find(".alert-danger").hide();
                target.parent().find(".deletefilterloader").css("display", "inline-block");
		jQuery.ajax({
	        type: 'GET',
	        url: script_object.ajaxurl,
	        data: {
	            action: 'delete_set_product',
	            set_id: product_id,
	        },
	        cache: false,
	        dataType: 'json',
	        success: function(data, textStatus, XMLHttpRequest) {
	        	target.parent().find(".deletefilterloader").hide();
	        	if(data.state=="success") {
                        var set_id = target.parent('.product_sets_info').attr('set-id');
//                        console.log(data.set_id);
                        if(data.set_id == set_id){
//                             console.log("hello");
                            target.parent('.product_sets_info').hide();
                        }
                        jQuery(".product_set_container").find(".alert-success").show();
            		jQuery(".product_set_container").find(".alert-success ul").html(data.success);
                        
	            } else {
	            	alert("Product not deleted please try again!!!");
	            }
	        }
	    }); 
	}
});

//Render Products View 
MD.MProductsView = Backbone.View.extend({
	
	el: '.merchant_dashboard_container',

	template: _.template(jQuery("#merchant_products_tml").html()),

	tml_el: jQuery("#merchant_products"),

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
		jQuery("#merchant_products_category_filters .products_cat_filters input").removeAttr("checked");
	},

	events: {
		'click .navigate_pagination_pages' : 'productPagination',
		'click .product_preview_edit_buttons a': 'showProductEditLoader',
		'click .search_merchant_products .search_button button' : 'searchMerchantProducts',
		'click .goto_products_listing' : 'gotoProductsListing',
		'click .merchant_product_delete' : 'deleteMerchantProduct',	
		'click #merchant_products_category_filters .products_cat_filters input' : 'productCatFilters',
	},

	render: function(page) {
		var _this = this;
		
		if(typeof page === 'undefined') {
			page = 1;
		}
		_this.$el.html(_this.template());
		_this.getMerchantProducts(page);
	},

	getMerchantProducts: function(page) {
		var _this = this;
		if(jQuery("#merchant_products").length==0) {
			_this.$el.html(_this.template());
		}
		product_list_view_el = jQuery("#products_list_view");
		product_list_pagination = jQuery("#merchant_products_pagination");

		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'show_merchant_products',
                page: page,
            },
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
                
            	product_list_view_el.html(data.products);
            	product_list_pagination.html(data.pagination);
            }
        });   	
	},

	productCatFilters: function(event) {
		var _this = this;
		target = jQuery(event.target);
		target.parent().find(".catfilterloader").css("display", "inline-block");

		cats = [];
		taxonomy = [];
		product_list_view_el = jQuery("#products_list_view");
		product_list_pagination = jQuery("#merchant_products_pagination");

		jQuery("#merchant_products_category_filters .taxonomy_search_container input:checked").each(function(){
			taxonomy_value = jQuery(this).val();
                        taxonomy_name = jQuery(this).parents('.products_cat_filters').attr('data-taxonomy-name');
                        cats.push(jQuery(this).val());
                        taxonomy.push(taxonomy_name);
//                        jQuery(this).parents('.taxonomy_search_container').find('.taxonomy_hidden_container').val(jQuery(this).val());
		});
                taxonomy = jQuery.unique( taxonomy );
//                 jQuery("#merchant_products_category_filters .taxonomy_search_container .products_cat_filters  input[type=hidden]").each(function(){
//                     	taxonomy.push(jQuery(this).val());
//                 });   
		if(cats.length == 0) {
			MD.mdroutes = new MD.MDRoutes();
			MD.mdroutes.navigate("#/products/", {trigger: true, replace: true});
			target.parent().find(".catfilterloader").hide();
		} else {	
			jQuery.ajax({
	            type: 'POST',
	            url: script_object.ajaxurl,
	            data: {
	                action: 'filter_by_categories',
	                cats: cats,
                        taxonomy:taxonomy,
	            },
	            cache: false,
	            dataType: 'json',
	            success: function(data, textStatus, XMLHttpRequest) {
	            	if(data.products=="") {
	            		response = "<h4>There are no products</h4>";
	            	} else {
	            		response = data.products;
	            	}
	            	product_list_view_el.html(response);
	            	product_list_pagination.html(data.pagination);
	            	target.parent().find(".catfilterloader").hide();
	            }
	        });
		}
	},

	deleteMerchantProduct: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		var product_id = target.attr('data-product-id');
		target.html("<i class='fa fa-cog fa-spin'></i>");
		jQuery.ajax({
	        type: 'GET',
	        url: script_object.ajaxurl,
	        data: {
	            action: 'delete_merchant_product',
	            product_id: product_id,
	        },
	        cache: false,
	        dataType: 'json',
	        success: function(data, textStatus, XMLHttpRequest) {
	        	target.html("Delete Product");
	        	if(data.response=='deleted') {
	           		jQuery("#merchant_products #merchant_product_"+product_id).fadeOut().promise().done(function(){
	           			jQuery("#merchant_products #merchant_product_"+product_id).remove();
	           		});	
	            } else {
	            	alert("Product not deleted please try again!!!");
	            }
	        }
	    }); 
	},

	searchMerchantProducts: function(event){
		var _this = this;
		var target = jQuery(event.target);
		search = target.parent().parent().find("input").val();
		
		if(search!="" || typeof search === 'undefined') {
			target.find("i").removeClass("fa-search").addClass("fa-spin fa-cog");
			product_list_view_el = jQuery("#products_list_view");
			product_list_pagination = jQuery("#merchant_products_pagination");
			bysku = jQuery("#bysku:checked").val();
			jQuery.ajax({
	            type: 'GET',
	            url: script_object.ajaxurl,
	            data: {
	                action: 'search_merchant_products',
	                bysku: bysku,
	                search: search,
	            },
	            cache: false,
	            dataType: 'json',
	            success: function(data, textStatus, XMLHttpRequest) {
	            	target.find("i").removeClass("fa-spin fa-cog").addClass("fa-search");
	            	product_list_view_el.html(data.products);
	            	product_list_pagination.html("");
	            	jQuery(".goto_products_listing").show();
	            }
	        });  
		} else {
			alert("Please Enter Search Term !!!");
		}
	},

	gotoProductsListing: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		target.hide();
		_this.getMerchantProducts(1);
	},

	productPagination: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		target.html('<i class="fa fa-cog fa-spin"><i>');
	},

	showProductEditLoader: function(event) {
    	var _this = this;
    	var target = jQuery(event.target);
		target.html('<i class="fa fa-cog fa-spin"><i>');
    },

});

//Products CSV Uploads View
MD.ProductsCSVUploadView = Backbone.View.extend({

	el: '.merchant_dashboard_container',

	template: _.template(jQuery("#products_csv_uploads_tml").html()),

	uploader: '',

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
	},

	events: {
		'click #upload_csv_products_images' : 'uploadCSVProductsImages',
		'click #load_csv_products' : 'loadCSVProducts',
		'click .start_uploading_csv_products' : 'startUploadCSVProducts',
	},

	render: function(page) {
		var _this = this;
		_this.$el.html(_this.template());

		if (typeof(plupload) === 'undefined') {
                return;
        }
        _this.uploader = new plupload.Uploader(script_object.plupload_zip_csv);
        _this.uploader.bind('Init', function(up, params) { });

        //initilize  wp plupload
        _this.uploader.init();
        _this.uploader.bind('FilesAdded', function (up, files) {
        	jQuery('#upload_zip_csv_previews').addClass("activate_uploading");
        	jQuery.each(files, function (i, file) {
            	extension = file.name.replace(/^.*\./, '');
            	id = "";
            	if(extension=="zip") {
            		jQuery(".csv_zip_uploading_succes_error").hide();
            		id = "zipfile";
            	} else {
            		id = "csvfile";
            	}
            	if(jQuery("#"+id).length>0) {
            		jQuery("#"+id).html('<div class="zipcsvuploadprogress">',
	                '<div class="progress"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"><span class="sr-only">0%</span></div></div>');
            	} else {
	                jQuery('#upload_zip_csv_previews').append(
	                '<div id="' + id + '" class="csvzipuploadpreview">'+
	                '<div class="zipcsvuploadprogress">'+
	                '<div class="progress"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"><span class="sr-only">0%</span></div></div>'+
	                '</div></div>');
            	}
            });

            up.refresh(); // Reposition Flash/Silverlight
            _this.uploader.start();
        });

        _this.uploader.bind('UploadProgress', function (up, file) {
            extension = file.name.replace(/^.*\./, '');
            id = "";
            if(extension=="zip") {
            	id = "zipfile";
            } else {
            	id = "csvfile";
            }
        	console.log(jQuery('#' + id + " .progress .progress-bar").html(file.percent + "%"));
        	console.log(jQuery('#' + id + " .progress .progress-bar").css('width', file.percent + "%"));
        });

        // On Error occur
        _this.uploader.bind('Error', function (up, err) {
                
        	alert("Error: While Uploading CSV and ZIP");
            up.refresh(); // Reposition Flash/Silverlight
            file_id = err.file.id;
            jQuery('#upload_images_preview #'+ file_id).remove();
        });

        _this.uploader.bind('FileUploaded', function (up, file, response) {
            var result = jQuery.parseJSON(response.response);
            if (result.success) {
            	setTimeout(function(){
	            	if(result.filetype=="zip") {
	                	jQuery('#upload_zip_csv_previews #zipfile').replaceWith(result.html);
	                	jQuery(".upload_product_images_first").css("display", "inline-block");
	            		jQuery("#upload_csv_products").attr("disabled", "disabled");
	            		jQuery("#upload_csv_products_images").css("display", "inline-block");
	            		jQuery(".upload_csv_product_images_row").show();
	            	} else {
	            		jQuery('#upload_zip_csv_previews #csvfile').replaceWith(result.html);
	            		jQuery(".upload_products_by_csv").css('display', 'block');
	            		jQuery(".run_csv_upload").hide();
	            		jQuery("#csv_upload_products_list").html("");
	            	}
	            }, 1500); 
            }
        });
		
	},

	uploadCSVProductsImages: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		target.html('<i class="fa fa-spin fa-cog"></i>');
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'upload_csv_products_images',
            },
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
            	target.html('Upload Product Images');	
            	if(data.result=="success") {
            		jQuery(".csv_zip_uploading_succes_error").html("Images Uploaded Successfully...");
            		jQuery(".csv_zip_uploading_succes_error").removeClass("alert-danger").addClass('alert-success').show();
               		jQuery("#upload_csv_products").removeAttr("disabled");
               		jQuery(".upload_csv_product_images_row").hide();
               	} else {
            		jQuery(".csv_zip_uploading_succes_error").html("Error While Uploading Images Try Again!!!");
            		jQuery(".csv_zip_uploading_succes_error").removeClass("alert-success").addClass('alert-danger').show();
            	}
            }
        });    
	},

	loadCSVProducts: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		var chunk = target.attr("data-chunk");
                console.log(chunk);
		var products_chunks_count = jQuery("#products_chunks_count").val();
		target.html('<i class="fa fa-spin fa-cog"></i>');
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'load_products_from_csv',
                products_chunks_count: products_chunks_count,
                chunk: chunk,
            },
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
            	target.html('Load CSV Products');
            	if(data.seflag=='success') {
            		jQuery(".run_csv_upload").html(data.chunks_buttons).show();
	            	jQuery(".start_uploading_csv_products:first-child").css("display", "inline-block");
	            	jQuery("#csv_upload_products_list").html(data.response);
	            	jQuery(".upload_products_by_csv").hide();
            	} else {
            		jQuery(".start_uploading_csv_products").css("display", "none");
	            	jQuery("#csv_upload_products_list").html(data.response);
            	}
            }
        });
	},

	startUploadCSVProducts: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		var chunk = target.attr("data-chunk");
		product_rows = Array();
		jQuery(".each_csv_product").each(function(){
			product_rows.push(jQuery(this));
		});
		row_count = jQuery(".each_csv_product").length;
		_this.doCSVAJAXRequest(target, 0, product_rows, row_count, chunk);
	},

	doCSVAJAXRequest: function(target, product_index, product_rows, row_count, chunk) {
		var _this = this;
		product_row = product_rows[product_index];
		product_info_object = Array();
		if(chunk=="") {
			jQuery(product_row).find(".csv_product_loader").css("display", "inline-block");
			jQuery(product_row).find(".product_information").each(function(){
				product_info_object.push( jQuery(this).find("input").val() );
			});
		}
		
		jQuery.ajax({
	            type: 'POST',
	            url: script_object.ajaxurl,
	            data: {
	                action: 'run_uploads_products',
	                product_info_object: product_info_object,
	                chunk: chunk,
	            },
	            cache: false,
	            dataType: 'json',
	            success: function(data, textStatus, XMLHttpRequest) {
	            	if(data.load_csv) {
	            		target.attr("data-chunk", "");
	            		jQuery("#csv_upload_products_list").html(data.load_csv).promise().done(function(){
	            			product_rows = Array();
							jQuery(".each_csv_product").each(function(){
								product_rows.push(jQuery(this));
							});
							row_count = jQuery(".each_csv_product").length;
	            			_this.doCSVAJAXRequest(target, product_index, product_rows, row_count, '');
	            		});
	            	} else {
	            		jQuery(product_row).find(".csv_product_loader").css("display", "none");
		            	if(data.success_error=="success") {
		            		jQuery(product_row).find(".alert_error").addClass(data.class).html(data.success).css("display", "block");
		            		jQuery(product_row).find(".csv_product_loader_error").css("display", "none");
		            		jQuery(product_row).find(".csv_product_loader_completed").css("display", "inline-block");
		            		if(data.unuploaded_images_flag=='true') {
		            			jQuery(product_row).find(".product_unuploaded_images_error").html(data.unuploaded_images).css("display", "block");
		            		}
		            	} else {
		            		jQuery(product_row).find(".alert_error").addClass(data.class).html(data.error).css("display", "block");
		            		jQuery(product_row).find(".csv_product_loader_completed").css("display", "none");
		            		jQuery(product_row).find(".csv_product_loader_error").css("display", "inline-block");
		            	}
		            	
		            	count = parseInt(row_count)-2;
		            	if(parseInt(product_index)<=count) {
		            		product_index = product_index + 1;	
	 	            		_this.doCSVAJAXRequest(target, product_index, product_rows, row_count, '');
	 	            	} else {
	 	            		if(target.next().length>0) {
	 	            			target.hide();
	 	            			target.next().css('display', 'inline-block');
	 	            		} else {
	 	            			target.attr("disabled", "disabled");
	 	            			jQuery("#upload_zip_csv_previews").html("").removeClass("activate_uploading");
	 	            			target.html("CSV Uploading Completed");
	 	            		}
	 	            	}
 	            	}
	            }
	    });
	},


});

//Render Product Uploads Edit View 
MD.ProductUploadEditView = Backbone.View.extend({
	
	el: '.merchant_dashboard_container',
	
	upload_template: _.template(jQuery("#product_upload_tml").html()),
	edit_template: _.template(jQuery("#product_edit_tml").html()),
	
	uploader: '',
	size_chart_uploader: '',
	
	dragarea: jQuery("#select_drop_upload_images"),

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render_upload_view', 'render_edit_view');
	},

	events: {
		'submit .upload_edit_product_from' : 'UploadEditProduct',
		'keypress .product_upload_from .product_upload' : 'disableEnterKey',
		'keyup .product_upload_from .product_upload' : 'disableEnterKey',
		'click .sub_attribute_new_term' : 'subAttributeNewTerm',
		'dragenter #select_drop_upload_images' : 'onDragEnter',
		'dragleave #select_drop_upload_images' : 'onDragLeave',
		'drop #select_drop_upload_images' : 'onDrop',
		'click .product_image_delete' : 'removeProductImage',
		'click .delete_size_chart_image' : 'removeSizeChartImage',		
		'change .product_categories' : 'getSubCategories',
		'change .product_sub_categories' : 'getSubCategories',
		'click .sorthandle' : 'sortCategoryUpDown', 
		'click .refresh_product_attributes' : 'RefreshProductAttributs',
	},

	//Render Upload View
	render_upload_view: function() {
		var _this = this;
		_this.$el.html("");
		_this.$el.html(_this.upload_template());
		_this.productImagesUpload();
		_this.sizeChartImageUpload();
		jQuery( "#upload_images_preview" ).sortable();
		jQuery(".custom_select_box").chosen({no_results_text: "Oops, nothing found!", allow_single_deselect: true });
		jQuery('.popover-dismiss').popover({ trigger: 'focus', html: true });
		
		jQuery('#product_description').summernote({ height: 120 });
		jQuery('#product_additional_info').summernote({ height: 200 });
		jQuery('#clothing_size_chart').summernote({ height: 250 });
		jQuery("#measurement_guides").summernote({ height: 100 });
	},

	//Render Edit View
	render_edit_view: function(product_id) {
		var _this = this;
		_this.$el.html("");
		_this.$el.html(_this.edit_template());
		_this.load_product_information(product_id);
	},

	//Load Product Edit Information
	load_product_information: function(product_id) {
		var _this = this;
		product_edit_el = jQuery("#product_edit_wrap");
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'load_edit_product_information',
                product_id: product_id,
            },
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
            	product_edit_el.html(data);

           		//Load JS Resources
           		_this.productImagesUpload();
           		_this.sizeChartImageUpload();
				jQuery( "#upload_images_preview" ).sortable();
				jQuery(".custom_select_box").chosen({no_results_text: "Oops, nothing found!", allow_single_deselect: true });
				jQuery('.popover-dismiss').popover({ trigger: 'focus', html: true });
				jQuery('#product_description').summernote({ height: 120 });
				jQuery('#product_additional_info').summernote({ height: 200 });
				jQuery('#clothing_size_chart').summernote({ height: 250 });
				jQuery("#measurement_guides").summernote({ height: 100 });
				_this.subCategoriesTermsAutocomplete();
            	_this.renderCategoriesSelectBox();
            	jQuery(".product_sub_categories").removeAttr("disabled");
           	}
        });    	
	},

	disableEnterKey: function(event) {
		var code = event.keyCode || event.which; 
		if (code  == 13) {               
		   event.preventDefault();
		   return false;
		}
	},

	UploadEditProduct: function(event) {
		var _this = this;
		var form_values = jQuery(".upload_edit_product_from").serialize();
		jQuery(".product_upload").attr("disabled", "disabled");
		jQuery(".product_upload").addClass("sending_data");
		jQuery(".update_product_loader").css("display", "inline-block");
		jQuery(".product_uploads_container").find(".alert-success").hide();
		jQuery(".product_uploads_container").find(".alert-danger").hide();
		jQuery.ajax({
            type: 'POST',
            url: script_object.ajaxurl,
            data: {
                action: 'upload_edit_product',
                form_values: form_values,
            },
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
            	if(data.state=="success") {
            		jQuery(".product_uploads_container").find(".alert-success").show();
            		jQuery(".product_uploads_container").find(".alert-success ul").html(data.success);
            		
            		//Empty All Fields
            		if(data.form_action=="upload_product") {
            			jQuery("#upload_images_preview").html("");
		            	jQuery(".upload_edit_product_from").find("input.form-control").val("");
		            	jQuery(".upload_edit_product_from").find("input:checkbox").removeAttr("checked");
		            	jQuery(".upload_edit_product_from").find("textarea").val("");
		            	jQuery('#product_description').destroy();
		            	jQuery('#product_description').summernote({ height: 120 });
						jQuery("#product_main_category").val('').trigger('chosen:updated');
		            	jQuery("#categories_relationships").html("");
		            	jQuery(".refresh_product_attributes").hide();
		            	jQuery("#sub_categories").html("");
	            	}
            	} else {
            		jQuery(".product_uploads_container").find(".alert-danger").show();
            		jQuery(".product_uploads_container").find(".alert-danger ul").html(data.error);
            	}

            	jQuery('html, body').stop().animate({
		        'scrollTop': 10,
		    	}, 900, 'swing', function () {});
            	jQuery(".product_upload").removeClass("sending_data");
            	jQuery(".product_upload").removeAttr("disabled", "disabled");
            	jQuery(".update_product_loader").css("display", "none");
            	
            }
        });    	
        return false;
	},

	getSubCategories: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		category = target.val();		
		level = target.attr("data-level");
		if( jQuery("#sub_cat_level_2").length > 0 ) {
			filter_category = jQuery("#sub_cat_level_2").val();
		} else {
			filter_category = jQuery("#sub_cat_level_1").val();
		}
		cat_attributes_ids = _this.$el.find(".product_categories_wrap").attr("product_cat_attribute_ids");
		if(target.attr("data-top-category")=='true') {			
			jQuery("#sub_categories").html("");
			jQuery("#categories_relationships").html("");
			jQuery(".refresh_product_attributes").hide();
			jQuery(".size_chart_option").hide();
			_this.$el.find(".product_categories_wrap").attr("product_cat_attribute_ids", "");
		}
		if(category!="") {
		jQuery("#sub_categories").append("<p style='margin-top:10px;' class='loading_sub_category'>Loading...</p>");
		jQuery(".product_sub_categories").attr("disabled", "disabled").trigger("chosen:updated");
		jQuery(".product_sub_categories").removeAttr("disabled");
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'get_sub_categories',
                category: category,
                level: level,
                filter_category: filter_category,
                cat_attributes_ids: cat_attributes_ids,
            },
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
            	jQuery(".loading_sub_category").remove();
            	cat_attributes_ids = data.cat_attributes_ids;
            	if( level > 0 ) {
            		_this.$el.find(".product_categories_wrap").attr("product_cat_attribute_ids", cat_attributes_ids);
            	}
            	if(data.sub_category_flag=='true') {
            		jQuery("#sub_categories").append(data.output); 
            		jQuery("#categories_relationships").append(data.category_attributes);
            		jQuery("#categories_relationships").css("opacity", "0");            		            		
            		_this.renderCategoriesSelectBox();
            	} else {
            		jQuery("#categories_relationships").append(data.category_attributes); 
            		_this.renderCategoriesSelectBox();
            		if(data.sizechartoption == 'true') {
            			jQuery(".size_chart_option").show();
            		} else {
            			jQuery(".size_chart_option").hide();
            		}
            		jQuery("#categories_relationships").css("opacity", "1");
            	}
            	jQuery(".product_sub_categories").chosen({no_results_text: "Oops, nothing found!"});
            }
        }); 
        } 
        return false;
	},

	RefreshProductAttributs: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		var product_id = target.attr('data-product-id');
		var label = target.attr('data-label');
		target.text("Wait..");
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'refresh_product_attributes',
                product_id: product_id,                
            },
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
            	target.text(label);            	
            	if(data.new_category_attributes!='') {
            		jQuery("#categories_relationships").append(data.new_category_attributes);
            		_this.subCategoriesTermsAutocomplete();
            		_this.renderCategoriesSelectBox();
            		jQuery(".refresh_product_attributes").hide();
            	}
            }
        });    	
	},

	getCategoriesRelationship: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		category = target.val();
		jQuery("#categories_relationships").html("");
		jQuery(".refresh_product_attributes").hide();
		if(category!="") {
		jQuery("#categories_relationships").html("Loading...");
		jQuery.ajax({
            type: 'GET',
            url: script_object.ajaxurl,
            data: {
                action: 'get_categories_relationship',
                category: category,
            },
            cache: false,
            dataType: 'json',
            success: function(data, textStatus, XMLHttpRequest) {
            	jQuery("#categories_relationships").html(data);
            	_this.subCategoriesTermsAutocomplete();
            	_this.renderCategoriesSelectBox();
            }
        }); 
        } 
        return false;
	},

	subCategoriesTermsAutocomplete: function() {
		jQuery(".autocomplete_sub_terms").each(function(){
			var id = jQuery(this).attr("id");
			var taxonomy = jQuery(this).attr("taxonomy");
			jQuery(this).parent().append('<i class="fa fa-cog fa-spin typeahedloader" style="display: none;"></i>');
			jQuery("#"+id).typeahead({
		        source: function (query, process){
		        	obj = jQuery("#"+id);
		        	obj.parent().find(".typeahedloader").css('display', 'inline');
		        	main_category = jQuery(".product_categories").val();
					jQuery.ajax({
			            type: 'GET',
			            url: script_object.ajaxurl,
			            data: {
			                action: 'get_sub_categories_terms',
			                taxonomy: taxonomy,
			                main_category: main_category,
			            },
			            cache: false,
			            dataType: 'json',
			            success: function(data, textStatus, XMLHttpRequest) {
			            	obj.parent().find(".typeahedloader").css('display', 'none');
			            	return process(data);
			            }
		            }); 	
				}
		    });
		});
	},

	renderCategoriesSelectBox: function() {
		jQuery(".multiselectterms").chosen({no_results_text: "Oops, nothing found!"});
		jQuery(".selectsingleterm").chosen({no_results_text: "Oops, nothing found!", allow_single_deselect: true});
	},

	subAttributeNewTerm: function(event) {
		var _this = this;
		var target = jQuery(event.target);
		if(!target.hasClass('add_new_term')) {
			target = target.parent();
		}
		var taxonomy = target.attr('data-taxonomy');
		var tax_label = target.attr('data-taxonomy-label');
		var select_type = target.attr('data-select-type');
		var product_main_cat = jQuery(".product_categories").val();
		category_level_1 = jQuery("#sub_cat_level_1").val();
		var category_levels = Array();
		jQuery("#sub_categories .custom_select").each(function(){
			category_levels.push(jQuery(this).find("select").val());
		});
		
		var term = prompt("Please Enter "+tax_label, "");
			
		if (term === "") { } 

		else if (term) {
			target.find("span").addClass("fa-spin");
			jQuery.ajax({
				type: 'GET',
				url: script_object.ajaxurl,
				data: {
				    action: 'set_sub_attribute_new_term',
				    category_levels: category_levels,
				    taxonomy: taxonomy,
				    term: term,
				},
				cache: false,
				dataType: 'json',
				success: function(data, textStatus, XMLHttpRequest) {
					if(select_type=="single") {
						jQuery("#"+taxonomy +"option").removeAttr("selected");
					}
					jQuery("#"+taxonomy).append(data.term_option);
					jQuery("#"+taxonomy).trigger('chosen:updated');
					target.find("span").removeClass("fa-spin");
				}
			});   
		} 

		else { }
		
	},

	sortCategoryUpDown: function(event) {
		var target = jQuery(event.target);
		var sort = target.attr("data-sort");
		var parent = target.parent().parent();
		if(sort=="up") {
			parent.insertBefore(parent.prev());
		} else {
			parent.insertAfter(parent.next());
		}
	},

	onDragEnter: function(event) {
		var _this = this;
		if (_this.uploader.features.dragdrop) {
			var target = jQuery(event.target);
			target.addClass("dragenter");
		}
	},

	onDragLeave: function(event) {
		var _this = this;
		if (_this.uploader.features.dragdrop) {
			var target = jQuery(event.target);
			target.removeClass("dragenter");
		}
	},

	onDrop: function(event) {
		var _this = this;
		if (_this.uploader.features.dragdrop) {
			var target = jQuery(event.target);
			target.removeClass("dragenter");
		}
	},

	productImagesUpload: function() {
		var _this = this;
		if (typeof(plupload) === 'undefined') {
                return;
        }
        _this.uploader = new plupload.Uploader(script_object.plupload_images);
        _this.uploader.bind('Init', function(up, params) { });

        //initilize  wp plupload
        _this.uploader.init();
        _this.uploader.bind('FilesAdded', function (up, files) {
            jQuery.each(files, function (i, file) {
                jQuery('#upload_images_preview').append(
                '<div id="' + file.id + '" class="imagepreview col-sm-3 col-xs-3">',
                '<img src="http://placehold.it/150x150&text=Uploading" />',
                '<div class="imageuploadprogress">',
                '<div class="progress"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"><span class="sr-only">0%</span></div></div>',
                '</div></div>');
            });

            up.refresh(); // Reposition Flash/Silverlight
            _this.uploader.start();
        });

        _this.uploader.bind('UploadProgress', function (up, file) {
        	jQuery('#' + file.id + " .progress .progress-bar").html(file.percent + "%");
        	jQuery('#' + file.id + " .progress .progress-bar").css('width', file.percent + "%");
        });

        // On Error occur
        _this.uploader.bind('Error', function (up, err) {
        	alert("Error: While Uploading Images");
            up.refresh(); // Reposition Flash/Silverlight
            file_id = err.file.id;
            jQuery('#upload_images_preview #'+ file_id).remove();
        });

        _this.uploader.bind('FileUploaded', function (up, file, response) {
            var result = jQuery.parseJSON(response.response);
            if (result.success) {
                jQuery('#upload_images_preview #'+ file.id).replaceWith(result.html);
                jQuery( "#upload_images_preview" ).sortable();
            }
        });

	},

	removeProductImage: function (event) {
            var _this = this;
            var target = jQuery(event.target);
            if (confirm(script_object.confirmMsg)) {
  
                data = {
                    'attach_id':target.attr('data-upload_id'),
                    'nonce':script_object.remove,
                    'action':'delete_product_image'
                };

                jQuery.post(script_object.ajaxurl, data, function () {
                    target.parent().remove();
	            });
            }
    },

    sizeChartImageUpload: function() {
		var _this = this;

		if(jQuery("#sizechartimagepreview").length>0) {
			if (typeof(plupload) === 'undefined') {
	                return;
	        }
	        _this.size_chart_uploader = new plupload.Uploader(script_object.plupload_size_chart_images);
	        _this.size_chart_uploader.bind('Init', function(up, params) { });

	        //initilize  wp plupload
	        _this.size_chart_uploader.init();
	        _this.size_chart_uploader.bind('FilesAdded', function (up, files) {
	        	jQuery("#sizechart_upload_images_area").hide();
	        	jQuery.each(files, function (i, file) {
	        		if(i==0) {
		                jQuery('#sizechartimagepreview').append(
		                '<div id="' + file.id + '" class="sizechartimagepreview">',
		                '<div class="imageuploadprogress">',
		                '<div class="progress"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"><span class="sr-only">0%</span></div></div>',
		                '</div></div>');
	            	}
	            });

	            up.refresh(); // Reposition Flash/Silverlight
	            _this.size_chart_uploader.start();
	        });

	        _this.size_chart_uploader.bind('UploadProgress', function (up, file) {
	        	jQuery('#' + file.id + " .progress .progress-bar").html(file.percent + "%");
	        	jQuery('#' + file.id + " .progress .progress-bar").css('width', file.percent + "%");
	        });

	        // On Error occur
	        _this.size_chart_uploader.bind('Error', function (up, err) {
	        	alert("Error: While Uploading Images");
	            up.refresh(); // Reposition Flash/Silverlight
	            file_id = err.file.id;
	            jQuery('#sizechartimagepreview #'+ file_id).remove();
	        });

	        _this.size_chart_uploader.bind('FileUploaded', function (up, file, response) {
	            var result = jQuery.parseJSON(response.response);
	            if (result.success) {
	                jQuery('#sizechartimagepreview #'+ file.id).replaceWith(result.html);
	                jQuery("#sizechart_upload_images_area").hide();
	            }
	        });
    	}

	},

	removeSizeChartImage: function (event) {
        var _this = this;
        var target = jQuery(event.target);
        if (confirm(script_object.confirmMsg)) {
            data = {
                'attach_id':target.attr('data-upload_id'),
                'nonce':script_object.remove,
                'action':'delete_product_image'
            };
            jQuery.post(script_object.ajaxurl, data, function () {
                target.parent().remove();
                jQuery("#sizechart_upload_images_area").show();
	        });
        }
    }

});