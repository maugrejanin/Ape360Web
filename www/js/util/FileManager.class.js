function FileManager(name, container){
	this.$container = getJQueryObj(container);
	this.name = name;

	this.choose = function(index){
		var selector = '#' + this.name + '_' + index;

		if(!this.$container.find(selector).length)
			this.create(index);

		var $input = this.$container.find(selector);
		$input.trigger('click');

		return $input;
	}

	this.create = function(index){
		return this.$container.append(
			'<input type="file" accept="image/png,image/jpeg,application/pdf" name="' + this.name + '[' + index + ']" id="' + this.name + '_' + index + '">'
		);
	}

	this.remove = function(index){
		var $selector = this.$container.find('#' + this.name + '_' + index),
			that = this;

		if(!$selector.length)
			return false;

		//decrementando os índices dos arquivos que sucediam esse
		this.$container.children().filter(function(){
			return parseInt(Form.getInputIndexName(this)) > index;
		}).each(function(){
			var new_index = (parseInt(Form.getInputIndexName(this)) - 1);

			$(this).attr({
				'name': that.name + '[' + new_index + ']',
				'id': that.name + '_' + new_index,
			});
		});

		$selector.remove();
		return true;
	}
}