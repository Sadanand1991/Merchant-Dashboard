(function ($) {
    "use strict";
    var abc = 0;
    jQuery(document).ready(function () {
         var storedFiles = [];
        jQuery('body').on('change', '.upload_pan_vat_tan', function () {
            if (this.files) {

                jQuery.each(this.files, function (key, file) {
                   storedFiles.push(file);
                    abc += 1;
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        jQuery('.upload_pan_vat_tan').after("<div class='previewImage col-sm-12'><img class='upload_pan_vat_tan' src='" + e.target.result + "' data-file='" + file.name + "'/><img  src='http://jewelb.testnme.com/wp-content/plugins/merchant-dashboard/assets/images/icon-close.png' class='deleleimage'/></div>");
                    };
                    reader.readAsDataURL(file);
                });
            }
        });

        $("body").on("click", ".deleleimage", removeFile);
        function removeFile(e) {
            var file = jQuery(this).parent().find('img.upload_pan_vat_tan').attr("data-file");
            for (var i = 0; i < storedFiles.length; i++) {
                if (storedFiles[i].name === file) {

                    storedFiles.splice(i, 1);
                    break;
                }
            }
            jQuery(this).parent().remove();
        }
        
        // Upload Address proof
        
         jQuery('body').on('change', '.upload_address_proof', function () {
            if (this.files) {
                jQuery.each(this.files, function (key, file) {
                    storedFiles.push(file);
                    abc += 1;
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        jQuery('.upload_address_proof').after("<div class='previewImage col-sm-12'><img class='upload_address_proof' src='" + e.target.result + "' data-file='" + file.name + "'/><img  src='http://jewelb.testnme.com/wp-content/plugins/merchant-dashboard/assets/images/icon-close.png' class='deleleimage'/></div>");
                    };
                    reader.readAsDataURL(file);
                });
            }
        });

        $("body").on("click", ".deleleimage", removeFile);
        function removeFile(e) {
            var file = jQuery(this).parent().find('img.upload_address_proof').attr("data-file");
            for (var i = 0; i < storedFiles.length; i++) {
                if (storedFiles[i].name === file) {
                    storedFiles.splice(i, 1);
                    break;
                }
            }
            jQuery(this).parent().remove();
        }
    // Upload ID Proof
    
     jQuery('body').on('change', '.upload_id_proof', function () {
            if (this.files) {

                jQuery.each(this.files, function (key, file) {
                    storedFiles.push(file);
                    abc += 1;
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        jQuery('.upload_id_proof').after("<div class='previewImage col-sm-12'><img class='upload_id_proof' src='" + e.target.result + "' data-file='" + file.name + "'/><img  src='http://jewelb.testnme.com/wp-content/plugins/merchant-dashboard/assets/images/icon-close.png' class='deleleimage'/></div>");
                    };
                    reader.readAsDataURL(file);
                });
            }
        });

        $("body").on("click", ".deleleimage", removeFile);
        function removeFile(e) {
            var file = jQuery(this).parent().find('img.upload_id_proof').attr("data-file");
            for (var i = 0; i < storedFiles.length; i++) {
                if (storedFiles[i].name === file) {
                    storedFiles.splice(i, 1);
                    break;
                }
            }
            jQuery(this).parent().remove();
        }
    
    // Upload Canceled Cheque 
    
     jQuery('body').on('change', '.upload_cheque', function () {
            if (this.files) {

                jQuery.each(this.files, function (key, file) {
                    storedFiles.push(file);
                    abc += 1;
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        jQuery('.upload_cheque').after("<div class='previewImage col-sm-12'><img class='upload_cheque' src='" + e.target.result + "' data-file='" + file.name + "'/><img  src='http://jewelb.testnme.com/wp-content/plugins/merchant-dashboard/assets/images/icon-close.png' class='deleleimage'/></div>");
                    };
                    reader.readAsDataURL(file);
                });
            }
        });

        $("body").on("click", ".deleleimage", removeFile);
        function removeFile(e) {
            var file = jQuery(this).parent().find('img.upload_cheque').attr("data-file");
            for (var i = 0; i < storedFiles.length; i++) {
                if (storedFiles[i].name === file) {
                    storedFiles.splice(i, 1);
                    break;
                }
            }
            jQuery(this).parent().remove();
        }
         jQuery('body').on('change', '.upload_scancopy', function () {
            if (this.files) {

                jQuery.each(this.files, function (key, file) {
                    storedFiles.push(file);
                    abc += 1;
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        jQuery('.upload_scancopy').after("<div class='previewImage col-sm-12'><img class='upload_cheque' src='" + e.target.result + "' data-file='" + file.name + "'/><img  src='http://jewelb.testnme.com/wp-content/plugins/merchant-dashboard/assets/images/icon-close.png' class='deleleimage'/></div>");
                    };
                    reader.readAsDataURL(file);
                });
            }
        });

        $("body").on("click", ".deleleimage", removeFile);
        function removeFile(e) {
            var file = jQuery(this).parent().find('img.upload_scancopy').attr("data-file");
            for (var i = 0; i < storedFiles.length; i++) {
                if (storedFiles[i].name === file) {
                    storedFiles.splice(i, 1);
                    break;
                }
            }
            jQuery(this).parent().remove();
        }
        
      });  
})(jQuery);        