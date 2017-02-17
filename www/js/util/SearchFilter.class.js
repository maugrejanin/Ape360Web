var SearchFilter = function(filter_data){
	this.form = document.getElementById("form_filter");
	this.btn_submit = document.getElementById("btn_filter_submit");
	this.elements_id;
	this.data = filter_data;

	this.loadElementsId = function(){
		this.elements_id = $(this.form).find(":input:not(button)").map(function(){
			return this.name || this.id;
		}).toArray();
		delete this.elements_id[this.elements_id.indexOf('__filter')];
	}

	this.Construct = function(){
		
	}

	this.show = function(elmts_id){

		this.fill();
		this.loadElementsId();
		this.handleEvents();

		if(elmts_id){
			var that = this;
			var elmts_id = elmts_id.filter(function(n) {
			    return that.elements_id.indexOf(n) != -1
			});

			for (var i = 0; i < elmts_id.length; i++) {
				$("#" + elmts_id[i]).parent(".filter-cell").css("display", "inline-table");
			};

		}else
			$(".filter-cell").css("display", "inline-table");
	}

	this.handleEvents = function(){
		var that = this;
		if (that.btn_submit) {
			$(this.form).find("#" + that.btn_submit.id).click(function(){
				var form_data = Form.getFormData(that.form);
				that.filter(form_data);
			});
		}
	}

	this.filter = function(form_data){
		console.log("sobreescreva-me(filter)");
	}

	this.fill = function(){
		console.log("sobreescreva-me(fill)");
	}

	this.loadDataPopulate = function(success){
		var obj_request = {
			url: "server/nineBox.php",
			data: {__action: 'fillfilter'},
			success: success
		};

		Diplomat.request(obj_request);
	}

	this.Construct();
}