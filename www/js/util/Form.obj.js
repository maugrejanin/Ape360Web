function Form(form){
	form = getJsObj(form);
	var form_data = new FormData(form);

	(function(){//set not checked checkbox values to form_data
		var not_checkeds = $(form).find('input[type=checkbox]:not(:checked)').map(function(){
			return $(this).attr('name');
		});

		for (var i = 0; i < not_checkeds.length; i++)
			form_data.append(not_checkeds[i], 0);
	}());

	this.getData = function(){
		return form_data;
	}

	this.getForm = function(){
		return form;
	}

	this.append = function(name, value){
		this[name] = value;

		if(value instanceof Object)
			return Form.appendArray(form_data, value, name);
		else
			return form_data.append(name, value)
	}
}

Form.realSubmit = function(path, params, form) {
    form = form || document.createElement("form");

    form.setAttribute("method", "post");
    form.setAttribute("action", path);
    form.setAttribute("enctype", "multipart/form-data");

    var elements = [];

    function addField( key, value ){
    	if(typeof value == 'object'){
            for(sub_key in value)
                addField(key + '[' + sub_key + ']', value[sub_key]);
            return;
    	}

        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", key);
        hiddenField.setAttribute("value", value );

        form.appendChild(hiddenField);
        elements.push(hiddenField);
    }; 

    if(params)
	    for(var key in params)
	        if(params.hasOwnProperty(key))
	            addField( key, params[key] );

    form.submit();

    for (var i = 0; i < elements.length; i++)
    	$(elements[i]).remove();
}

Form.getInputIndexName = function(input){
	var name = getJQueryObj(input).attr("name");
	var index_str = name.indexOf("[") + 1;
	return name.substring(index_str, name.length - 1);
}

Form.valid = function(form){
	var form = getJsObj(form);
	return Validator.valid(form);
}

Form.appendArray = function(form_data, values, name){
	if(!values)
		form_data.append(name, 0);
	else{
		if(typeof values == 'object'){
			for(key in values){
				if(typeof values[key] == 'object')
					Form.appendArray(form_data, values[key], name + '[' + key + ']');
				else
					form_data.append(name + '[' + key + ']', values[key]);
			}
		}
	}

	return form_data;
}

Form.clearFileInput = function(input){
	var $file = getJQueryObj(input);
	$file.wrap('<form>').closest('form').get(0).reset();
	$file.unwrap();

	return $file;
}

Form.fillSelect = function(select, data, inner, value, none_option){
	if(typeof inner != 'undefined' && typeof none_option == 'undefined' && typeof value == 'undefined')
		none_option = inner;

	if(typeof data == 'object'){
		if(data instanceof Array)
			Form.fillSelectMatrix(select, data, inner, value, none_option);
		else
			Form.fillSelectAlphabetic(select, data, inner);
	}else
		throw Error('Informação %o, inválida para preenchimento de select %o.', data, select);
}

Form.fillSelectMatrix = function(select, data, inner, value, none_option, append){
	var $select = getJQueryObj(select);

	none_option = none_option? (typeof none_option == 'string'? none_option: 'Escolha uma opção') : false;

	if(!append)
		$select.html('');

	if(none_option)
		$select.append('<option value="">' + none_option + '</option>');

	for(var c = 0; c < Object.keys(data).length; c++)
		$select.append('<option value="' + data[c][value] + '">' + data[c][inner] + '</option>');

	$select.selectpicker('refresh');

	return $select;
}

Form.fillSelectAlphabetic = function(select, data, none_option, append){
	var $select = getJQueryObj(select);

	var objdata = [];
	none_option = none_option? (typeof none_option == 'string'? none_option: 'Escolha uma opção') : false;

	if(!append)
		$select.html('');

	for (key in data) {
		var item = data[key];
		objdata.push( {v: key, t: item} );
	};

	objdata.sort(function(o1, o2) {
        return o1.t.toLowerCase() > o2.t.toLowerCase() ? 1 : o1.t.toLowerCase() < o2.t.toLowerCase() ? -1 : 0;
    });

	if(none_option)
		$select.append('<option value="">' + none_option + '</option>');

	$.each(objdata, function(key, value) {
	    $select.append('<option value="' + value.v + '">' + value.t + '</option>');
	});

	$select.selectpicker('refresh');

	return $select;
}

Form.getJson = function($form){
	$form = getJQueryObj($form);

    var o = {};
    var a = $form.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
}

Form.reset = function (form) {
	form = (typeof form == 'string')? document.getElementById(form) : ((form instanceof jQuery)? form.get(0) : form);
	form.reset();

	$('.selectpicker').selectpicker('refresh');
}

Form.handleSelectEvents = function($container){
	var $container = $container? getJQueryObj($container) : $(document);
	
	$container.delegate("select[required]", "change", function(e){
		$(this).find("option[value='']").remove();

		if($(this).is(".selectpicker"))
			$(this).selectpicker("refresh");
	});
}

Form.handleEvents = function($container){
	var $container = $container? getJQueryObj($container) : $(document);
	$container.delegate(":input[type=number]", "keypress", function(e){

		if( e.which == 46 || e.which == 44 ){//não permite mais de que um ponto/vírgula no número
			if( this.value.search(/[\,\.]/) != -1 ){
				return e.preventDefault();
			}else if(this.value.length == 0){
				return e.preventDefault();
			}
		}else if(e.which == 69 || e.which == 101){//não permite a inserção de 'e' e 'E'
			return e.preventDefault();
		}else{//is a number
			var decimal_position = this.value.search(/[\,\.]/);
			if(decimal_position != -1){
				if( this.value.length - decimal_position > 2){
					return e.preventDefault();
				}
			}
		}

	});

	$container.delegate(":input[type=number]", "keyup", function(e){//não permite a inserção de número maior que atributo "max" ou menor que atributo "min"
		var that = this;
		var max = this.getAttribute("max");
		var min = this.getAttribute("min");
		var new_value = parseFloat(this.value);

		if(max && max < new_value){
			this.value = max;
			$(that).trigger('change');
			e.preventDefault();
		}

		clearTimeout(Form.minKeyUpTimeOut);
		Form.minKeyUpTimeOut = setTimeout(function(){
			if(min && min > new_value){
				that.value = min;
				$(that).trigger('change');
				e.preventDefault();
			}
		}, 800);
	});
}

Form.minKeyUpTimeOut;

Form.getTarget = function(field_name, index){
	if(field_name instanceof Object){
		$target = getJQueryObj(field_name);
	}else{
		if(typeof index != 'undefined'){
			var $targets = $(":input[name='" + field_name + "[]']");
			if($targets.length > 0)
				$target = $targets.eq(index+1);
			else
				$target = $(":input[name='" + field_name + "[" + index + "]']");
		}else{
			$target = $(":input[name='" + field_name + "']");

			if($target.length == 0)
				$target = $("#"+field_name);
		}
	}

	return $target;
}

Form.populateElement = function(target, value){
	$target = getJQueryObj(target);

	if($target.length > 0)
		if($target.is('select')){
	    	if( $target.is('.selectpicker') ){
				$target.val(value);
				$target.selectpicker("refresh");
        	}else
        		Form.setInputValue($target, value);
    	} else if ($target.is("span")) {
			Form.setHtmlValue($target, value);
    	} else {
			switch($target.attr("type")){
				case "file":
					if( $target.is('.file-image') ){
						$target.imagefile('val', value);
					}
				break;
		    	case "radio":
		           	$target.filter('[value="' + value + '"]').prop("checked", true);
		        break;
		        case "checkbox":
		        	if( (value === 'on') || (value === true) || (value === 'true') || (value.toUpperCase() === 'S') ){
		           		$target.prop("checked", true);

		           		if($target.is('.checkpicker'))
		           			$target.bootstrapSwitch('state', true, true);
		        	}
		        break;
		        case "text":
		        	if( $target.is('.moneypicker') ){
		        		$target.maskMoney('mask', DataTreat.money(value));
		        		break;
		        	}

		        	if( $target.is('.cpfcnpj') ){
		        		$target.unmask();
		        		$target.val( UserTreat.cpfcnpj(value) );
		        		Transformer.cpfcnpj($target);
		        		break;
		        	}

		        	if( $target.is('.celphone') ){
		        		$target.val( UserTreat.phone(value) );
		        		break;
		        	}

		        	if( $target.is('[mask]') ){
		        		$target.val( UserTreat.mask(value, $target.attr('mask')) );
		        		break;
		        	}

		        	if( $target.is('.datepicker') ){
		        		value = /^\d{2}\/\d{2}\/\d{4}$/.test(value)? value : UserTreat.date(value);
		        		//a seguinte linha foi alterado para que funcione com o js da porto, deve retornar ao que era quando entrarmos em outro sistemas!!!
		        		$target.val(value);//$target.datepicker('update', value);
		        		break;
		        	}

		        	Form.setInputValue($target, value);
		        break;
		        default:
		        	Form.setInputValue($target, value);
		    }
		}
}

Form.setHtmlValue = function($target, value){
	$target = getJQueryObj($target);
	return $target.html(value);
}

Form.setInputValue = function($target, value){
	$target = getJQueryObj($target);
	return $target.is(':input')? $target.val(value): $target.html(value);
}

Form.realSetInputValue = function($target, value){
	$target = getJQueryObj($target);
	switch( $target.get(0).nodeName.toLowerCase() ){
		case "input":
			$target.attr("value", value);
		break;
		case "select":
			$target.find("option[value=" + value + "]").prop("selected", true);
		break;
		case "textarea":
			$target.html(value);
		break;
	}
}

Form.populateMultElement = function(field_name, mult_value){
	
	for(c in mult_value){
		if (mult_value[c] instanceof Object) {
			Form.populateMultElement(field_name + "[" + c + "]", mult_value[c]);
		} else {
			var $target = Form.getTarget(field_name, c);
			Form.populateElement($target, mult_value[c]);
		}
	}
}

Form.populate = function($frm, data) {
	$frm = getJQueryObj($frm);
	var $target, that = this;

    $.each (data, function(key, value) {

    	$target = Form.getTarget(key);

    	if ((!$target || !$target.is("select[multiple]")) && value instanceof Object) {
    		Form.populateMultElement(key, value);
    	}else
	    	Form.populateElement($target, value);

    } );
}

Form.limitSum = function($collection, limit){

	limit = Number(limit);

	$collection.attr("max", limit);
	$collection.blur( function(){

		var values = $collection.map(function(){
			return Number($(this).val());
		}).toArray();

		var sum = PHP.array_sum( values );
		var remain = limit - sum;

		$collection.each(function(){
			var my_val = Number( $(this).val() );
			$(this).attr("max", remain + my_val);
		});

	});

}