function Dialog(config){
	if(!Dialog.count)
		$("html").css("overflow", "hidden");

	var old_onhidden = config.onhidden;
	config.onhidden = function(dialogRef){
		Dialog.count--;

		if(!Dialog.count)
			$("html").css("overflow", 'visible');

		if(old_onhidden)
			old_onhidden(dialogRef);

		delete Dialog.instances[dialogRef.getData('id')];
	}

	var final_config = $.extend(true, {
		nl2br: false,
		size: BootstrapDialog.SIZE_WIDE,
		type: BootstrapDialog.TYPE_PRIMARY,
	}, config);

	var instance = new BootstrapDialog(final_config);
	instance.setData('id', Dialog.acumulador);

	Dialog.instances[Dialog.acumulador++] = instance;
	Dialog.count++;
	return instance;
}

Dialog.acumulador = 0;
Dialog.count = 0;
Dialog.instances = {};

Dialog.closeAll = function(){
	for(key in Dialog.instances){
		Dialog.instances[key].close();
	}
}

Dialog.setLoad = function(dialogRef){
	dialogRef.getModalBody().append('<div class="loading"></div>');
}

Dialog.removeLoad = function(dialogRef){
	dialogRef.getModalBody().find('.loading').remove();
}

Dialog.show = function(config){
	return Dialog(config).open();
}

Dialog.success = function(message, onfinish){
	onfinish = onfinish || null;

	Dialog.show({
		title: "Informação",
		message: message,
		type: BootstrapDialog.TYPE_SUCCESS,
		buttons: [{
			label: 'Ok',
			action: function(dialogRef){
				if(onfinish)
					onfinish();

				dialogRef.close();
			},
			cssClass: 'btn btn-way',
		}],
	});
}

Dialog.alert = function(message, onfinish){
	onfinish = onfinish || null;

	Dialog.show({
		title: "Atenção",
		message: message,
		type: BootstrapDialog.TYPE_PRIMARY,
		closable: false,
		buttons: [{
			label: 'Ok',
			hotkey: 13, // Enter.
			action: function(dialogRef){
				if(onfinish)
					onfinish();

				dialogRef.close();
			},
			cssClass: 'btn btn-way',
		}],
	});
}

Dialog.error = function(message, onfinish){
	onfinish = onfinish || null;

	Dialog.show({
		title: "Mensagem de erro",
		message: message,
		type: BootstrapDialog.TYPE_DANGER,
		buttons: [{
			label: 'Ok',
			action: function(dialogRef){
				if(onfinish)
					onfinish();

				dialogRef.close();
			},
			cssClass: 'btn btn-way',
		}],
	});
}

Dialog.getConfirmButtons = function(callback, options){
	options = options? options : {};

	var btnOKLabel = options.btnOKLabel || 'Confirmar';
	var btnCancelLabel = options.btnCancelLabel || 'Cancelar';
	var btnOKClass = options.btnOKClass || 'btn-primary';
	var btnCancelClass = options.btnCancelClass || '';

	return [{
		label: btnOKLabel,
		id: 'btnOK',
		cssClass: 'btn btn-way ' + btnOKClass,
		action: function(dialogRef){
			if(callback){
				if(callback(true, dialogRef) !== false)
					dialogRef.close();
			}else
				dialogRef.close();
		},
	}, {
		label: btnCancelLabel,
		id: 'btnCancel',
		cssClass: 'btn btn-way ' + btnCancelClass,
		action: function(dialogRef){
			if(callback)
				callback(false, dialogRef);
			dialogRef.close();
		},
	}];
}

Dialog.confirm = function(message, callback, options){
	options = $.extend(true, {
		btnOKClass: 'btn-warning'
	}, options);
	
	var final_options = $.extend(true, {
		message: message,
		title: "Confirmação",
		type: BootstrapDialog.TYPE_WARNING,
		draggable: true, 
		closable: true,
		nl2br: false,
		buttons: Dialog.getConfirmButtons(callback, options)
	}, options);

	return Dialog.show(final_options);
}