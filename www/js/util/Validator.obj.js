var Validator = {
	uf_list: [
		'ac',
		'al',
		'am',
		'ap',
		'ba',
		'ce',
		'df',
		'es',
		'go',
		'ma',
		'mt',
		'ms',
		'mg',
		'pa',
		'pb',
		'pr',
		'pe',
		'pi',
		'rj',
		'rn',
		'ro',
		'rs',
		'rr',
		'sc',
		'se',
		'sp',
		'to'
	],

	setError: function (field_name, message_str, index) {
		var message = "<span class='help-block with-errors'><ul class='list-unstyled'><li>" + message_str + "</li></ul></span>";
		var $target = Validator.getTarget(field_name, index);
		var glyphicon = "";//"<span class='glyphicon form-control-feedback glyphicon-remove' aria-hidden='true'></span>";

		Validator.removeError(field_name);

		var $form_group = $target.after(message).after(glyphicon).parents(".form-group");
		if($form_group.length > 0)
			$form_group.addClass("has-error");
		else{
			$target.addClass("with-errors");
		}
	},

	removeError: function (field_name) {
		var $target = Validator.getTarget(field_name),
			$form_group = $target.parents(".form-group");

		$target.next(".help-block.with-errors").remove();

		if($form_group.length > 0)
			$form_group.eq(0).removeClass("has-error");
		else
			$target.removeClass("with-errors");
	},

	getErrorData: function(form, identify_by_id){
		var form = getJsObj(form);

		var errors = {};
		for(var c = 0; c < form.elements.length; c++){
			var current = form.elements[c],
				required = current.required;

			var value = $(current).val();
			var identify = identify_by_id? (current.id || current.name): c;

			if(required && value === ''){
				errors[identify] = 'required'; continue;
			}

			var type = current.getAttribute('type');

			switch(type){
				case 'email':
					if(!Validator.email(value)){
						errors[identify] = 'email'; continue;
					}
				break;
			}

			var maxlength = current.getAttribute('maxlength');

			if(maxlength && value.length > maxlength){
				errors[identify] = 'maxlength'; continue;
			}

			var minlength = current.getAttribute('minlength');

			if(minlength && value.length < minlength){
				errors[identify] = 'minlength'; continue;
			}

			if($(current).hasClass('cpf') && !Validator.cpf(DataTreat.numeric(value))) {
				errors[identify] = 'cpf'; continue;
			}

			if($(current).hasClass('date') && !Validator.date(value)) {
				errors[identify] = 'date'; continue;
			}

			var max = current.getAttribute('max');

			if(max && DataTreat.treat(value) > max){
				errors[identify] = 'max'; continue;
			}

			var min = current.getAttribute('min');

			if(min && DataTreat.treat(value) < min){
				errors[identify] = 'min'; continue;
			}

			if($(current).hasClass('uf') && Validator.uf_list.indexOf(value.toLowerCase()) == -1) {
				errors[identify] = 'in_list'; continue;
			}
		}

		return errors;
	},

	valid: function(form, custom_error){
		form = getJsObj(form);
		var errors = this.getErrorData(form);

		if(Object.keys(errors).length > 0){
			try{
				this.setErrors(errors, form, custom_error);
			}catch(e){
				if (CONFIG_MODE_DEVELOPMENT == CURRENT_MODE)
					console.log(e);
				
				return false;
			}

			return false;
		}

		return true;
	},

	isInteger: function(x) {
        return (typeof x === 'number') && (x % 1 === 0);
    },

	setErrors: function(errors, form, custom_error){
		form = getJsObj(form);
		this.cleanForm(form);

		function getLabel(element){
			return '<b>' + ($('label[for="' + element.id + '"]').html() || $(element).attr('placeholder'))+ '</b>';
		}

		for(key in errors){
			var type = errors[key];
			var element = this.isInteger(parseInt(key))? form.elements[key]: Validator.getTarget(key).get(0);
			var label = getLabel(element);
			var setError = typeof custom_error == 'function'? custom_error: this.setError;

			try{
				switch(type){
					case 'email': setError(element, 'O campo ' + label + ' requer um e-mail válido'); break;
					case 'cnpj': setError(element, 'O campo ' + label + ' requer um CNPJ válido'); break;
					case 'cpf': setError(element, 'O campo ' + label + ' requer um CPF válido'); break;
					case 'cpfcnpj': 
						var value = DataTreat.numeric($(element).val());
						var message = value.length > 11? 'O campo ' + label + ' requer um CNPJ válido': 'O campo ' + label + ' requer um CPF válido';
						setError(element, message);
					break;
					case 'date':
						setError(element, 'O campo ' + label + ' requer uma data válida (dd/mm/aaaa).');
					break;
					case 'unique':
						setError(element, 'O campo ' + label + ' contem um valor já cadastrado, favor escolher outro.');
					break;
					case 'max':
						var max = element.getAttribute('max');

						if( $(element).is('.moneypicker') )
							max = UserTreat.money(max);
						else if($(element).is('.datepicker'))
							max = UserTreat.date(max);

						setError(element, 'O campo ' + label + ' utrapassa o valor máximo permitido: <b>' + max + '</b>');
					break;
					case 'min':
						var min = element.getAttribute('min');

						if( $(element).is('.moneypicker') )
							min = UserTreat.money(min);
						else if($(element).is('.datepicker'))
							min = UserTreat.date(min);

						setError(element, 'O campo ' + label + ' não contempla o valor mínimo exigido: <b>' + min + '</b>');
					break;
					case 'extension':
						var accept = element.getAttribute('accept');
						var message = 'O campo ' + label + ' contem um arquivo de extensão inválida.';

						if(accept)
							message += ' São aceitas apenas as extensões: ' + accept.split(',').map(function(type){ return type.split('/').pop(); }).join(', ');

						setError(element, message);
					break;
					case 'maxlength':
						var maxlength = element.getAttribute('maxlength');
						setError(element, 'O campo ' + label + ' deve conter no máximo <b>' + maxlength + '</b> caracteres.');
					break;
					case 'equal':
						var elemt_compare = document.getElementById(element.getAttribute('equal'));
						var lebel_compare = getLabel(elemt_compare);
						setError(element, 'O campo ' + label + ' deve ser igual ao campo ' + lebel_compare + '.');
					break;
					case 'minlength':
						var minlength = element.getAttribute('minlength');
						setError(element, 'O campo ' + label + ' deve conter no mínimo <b>' + minlength + '</b> caracteres.');
					break;
					case 'uf':
					case 'not_in_list':
					case 'in_list':
						setError(element, 'O campo ' + label + ' contem um valor inválido.');
					break;
					case 'required': setError(element, 'O campo ' + label + ' é obrigatório'); break;
					case 'max_file_size':
						setError(element, 'O arquivo de ' + label + ' que você está tentando enviar excede o tamanho máximo permitido.');
					break;
					case 'toolong':
						setError(element, 'O campo ' + label + ' está excedendo o tamanho limite');
					break;
					default://mensagem personalizada
						setError(element, type);
					break;
				}
			}catch(err){
				break;
			}
		}
	},

	getTarget: function (field_name, index) {
		var $target;

		if(field_name instanceof Object){
			$target = getJQueryObj(field_name);
		}else{
			if(index){
				var $targets = $(":input[name='" + field_name + "[]']");
				if($targets.length > 0)
					$target = $targets.eq(index);
				else
					$target = $(":input[name='" + field_name + "[" + index + "]']");
			}else{
				$target = $(":input[name='" + field_name + "']").eq(0);

				if($target.length == 0)
					$target = $("#"+field_name);
			}
		}

		if( $target.is('.selectpicker') )
			$target = $target.next(".btn-group").find(".btn");

		if( $target.is('.ps-frm-radio') )
			$target = $('label[for="' + $target.attr('id') + '"]');

		if( $target.is('.fileporto') )
			$target = $target.siblings('.text-file-porto');

		if( $target.is(':hidden[delegate]') )
			$target = getJQueryObj($target.attr('delegate'));

		return $target;
	},

	cleanForm: function (form_obj) {
		$container = getJQueryObj(form_obj);
		if($container.length == 0)
			$container = $(document);

		var that = this;

		$container.find(":input:not(button, [type=button])").each(function(){
			that.removeError(this);
		});
	}
}

Validator.email = function (value) {
    var rgx = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return rgx.test(value);
}

Validator.cpf = function (value) {
    var Soma;
    var Resto;
    Soma = 0;
	if (value == "00000000000") return false;
    
	for (i=1; i<=9; i++) Soma = Soma + parseInt(value.substring(i-1, i)) * (11 - i);
	Resto = (Soma * 10) % 11;
	
    if ((Resto == 10) || (Resto == 11))  Resto = 0;
    if (Resto != parseInt(value.substring(9, 10)) ) return false;
	
	Soma = 0;
    for (i = 1; i <= 10; i++) Soma = Soma + parseInt(value.substring(i-1, i)) * (12 - i);
    Resto = (Soma * 10) % 11;
	
    if ((Resto == 10) || (Resto == 11))  Resto = 0;
    if (Resto != parseInt(value.substring(10, 11) ) ) return false;
    return true;
}

Validator.date = function (value) {
	var parts = value.split('/');

	if(parts.length != 3)
		return false;

	var d = new Date(parts[2], parts[1] - 1, parts[0]);
	return d && (d.getMonth() + 1) == parts[1] && (d.getFullYear()) == parts[2];
} 