/**
* MM Search JS
*/

var MMSearch = { 
	run: function() {
		this.search_global_view = new this.SearchGlobalView();
			
	}	
}

//SearchGlobalView View
MMSearch.SearchGlobalView = Backbone.View.extend({ 
	el: "body",

	initialize: function() {
		var _this = this;
		_.bindAll(this, 'render');
		_this.render();
	},
	
	render: function() {
		var _this = this;
		
	},	

});