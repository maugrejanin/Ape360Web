var Transformer = {

	celphone: function(element){
		var $element = getJQueryObj(element);

		var cellphoneBehavior = function (val) {
			return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
		}, cellphoneOptions = {
			onKeyPress: function(val, e, field, options) {
				field.mask(cellphoneBehavior.apply({}, arguments), options);
			}
		};

		$element.addClass('celphone');
		$element.mask(cellphoneBehavior, cellphoneOptions);
	},

	cpfcnpj: function(element){
		var $element = getJQueryObj(element);

		var cellphoneBehavior = function (val) {
			return val.replace(/\D/g, '').length <= 11 ? '000.000.000-00999': '00.000.000/0000-00';
		}, cellphoneOptions = {
			onKeyPress: function(val, e, field, options) {
				field.mask(cellphoneBehavior.apply({}, arguments), options);
			}
		};

		$element.addClass('cpfcnpj');
		$element.mask(cellphoneBehavior, cellphoneOptions);
	},

	mask: function(element, mask){
		var $element = getJQueryObj(element);

		$element.attr('mask', mask);
		$element.mask(mask);
	},

	table: function(table, config){
		var $table = getJQueryObj(table);

		$table
			.addClass('bootstrap-table table table-bordered');

		config = $.extend(true, {
			search: true,
			showColumns: true,
			pagination: true,
			pageSize: 15,
			pageList: [15, 30, 50],
			clickToSelect: true,
		}, config);

		return $table.bootstrapTable(config);
	},

	date: function(input, config){
		var $input = getJQueryObj(input);

		$input.addClass('datepicker');

		config = $.extend(true, {
			format: 'dd/mm/yyyy',
			language: 'pt-BR'
		}, config);

		return $input.datepicker(config);
	},

	money: function(input){
		var $input = getJQueryObj(input);
		$input.addClass('moneypicker');

		return $input.maskMoney({
			prefix: "R$ ",
			thousands: ".",
			decimal: ",",
		});
	},

	select: function(select, options){
		var $select = getJQueryObj(select);

		if($select.is('.selectpicker'))
			return;

		$select.addClass('selectpicker');

		options = $.extend({
			//default options
		}, options);

		$select.selectpicker(options);
	},

	erktable: function(table, options){
		options = $.extend({
			size_hide_column: true
		}, options);

		var $table = getJQueryObj(table);

		function hideColumns(){
			var table_width = $table.is(':hidden')? $table.parents(':not(:hidden)').eq(0).outerWidth(): $table.outerWidth();
			var max_columns = Math.floor(table_width/MIN_WIDTH_COLUMN_TABLE) || 1;
			
			$table.bootstrapTable('getVisibleColumns').filter(function(col){
				return !col.checkbox && !col.radio;
			}).slice(max_columns).forEach(function(col){
				$table.bootstrapTable('hideColumn', col.field);
			});
		}

		this.table($table);

		if(options.size_hide_column)
			hideColumns();

		$table.addClass('way-table');
		$table.parents('.bootstrap-table').eq(0).addClass('way-table-container').find('.form-control').addClass('input-way');
	},

	switcher: function(checkbox){
		var $checkbox = getJQueryObj(checkbox);

		var result = $checkbox.bootstrapSwitch({
			offText: 'Não',
			onText: 'Sim',
			inverse: true,
		});

		$checkbox.addClass('checkpicker');
		$checkbox.on('switchChange.bootstrapSwitch', function(event, state) {
			$(this).trigger("change");
		});

		return result;
	},

	file: function(input, config){// o config será usado quando necessário
		var input = getJsObj(input);
		var id = input.id;
		var btn_id = '_btn_file_porto_' + id + '_';
		var text_id = '_text_file_porto_' + id + '_';

		$(input).hide().addClass('filepicker').attr('delegate', text_id);
		$(input).after(
			'<button type="button" id="' + btn_id + '" class="btn btn-primary btn-filepicker">'
				+ 'Escolha um arquivo' + 
			'</button>' +
			'<input type="text" id="' + text_id + '" class="form-control text-filepicker" disabled/>'
		);

		var btn = document.getElementById(btn_id);
		var text = document.getElementById(text_id);
		var wrap = document.createElement('div');

		wrap.className = 'file-wrap';
		$(btn).before(wrap);
		$(btn).appendTo(wrap);
		$(text).appendTo(wrap);

		$(btn).click(function(){
			input.click();
		});

		$(input).change(function(){
			var names = [], 
				$badge = $(btn).find('.badge');

			for (var i = 0; i < this.files.length; ++i)
				names.push(this.files.item(i).name);

			if(names.length > 0){

				if(names.length > 1){
					if( $badge.length > 0 )
						$badge.html(names.length);
					else
						$(btn).append('<span class="badge">' + names.length + '</span>')
				}
			}else
				$badge.remove();

			text.value = names.join('; ');
		});
	}
}