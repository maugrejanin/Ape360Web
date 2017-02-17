function Crop(image, preview, file, options){

	var that = this;	
	this.data = {
		x: null,
		y: null,
		width: null,
		height: null,
		rotate: null,
		scaleX: null,
		scaleY: null,
	};

	this.run = function(){
		this.$image.cropper(this.options);
		this.handleEvents();
	}

	this.handleEvents = function(){
		this.$file.change(function(){
			if (this.files && this.files[0]) {//se algum arquivo foi escolhido
				var reader = new FileReader();

				reader.onload = function (e) {
					that.$image.attr('src', e.target.result);
					that.$image.cropper('destroy');
					that.$image.cropper(that.options);
				}

				var file = this.files[0];
				reader.readAsDataURL(file);
			}
		});

		this.$file.on('crop.cropper', function(e){
			that.data = that.$image.cropper('getData');
		});
	}

	this.setImgResult = function(img){
		var img = getJsObj(img);

		this.$image.cropper('getCroppedCanvas').toBlob(function (blob) {
			img.src = URL.createObjectURL(blob);
		});
	}

	this.send = function(callback){
		this.$image.cropper('getCroppedCanvas').toBlob(callback);
	}

	this.getData = function(){
		return this.data;
	}

	this.init = function(){
		this.$image = getJQueryObj(image);
		this.$preview = getJQueryObj(preview).css({
			overflow: 'hidden'
		});
		this.$file = getJQueryObj(file);

		// -----------------------------------------------------------

		this.options = $.extend(true, {
			aspectRatio: 16 / 9,
			background: false,
			preview: this.$preview,
		}, options);
	}

	this.init();
}