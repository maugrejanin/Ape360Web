var Diplomat = {
	request: function(data){
		if (!data.confirm || data.__confirmed) {
			var data_request = this.loadFinalData(data);
			//console.log("data_request", data_request);
			if(data_request){
				if(data_request.loading){
					Loader.setLoad(data_request.loading, true);
				}

				if(data_request.button){
					var $button = getJQueryObj(data_request.button);
					$button.prop('disabled', true);
				}

				return $.ajax(data_request);
			}

			return false;
		} else {
			jConfirm(data.confirm, 'Confirmação', function(answer){
				if(answer){
					data.__confirmed = true;
					Diplomat.request(data);
				}
			});
		}
	},
	fastRequest: function(success, post, get){
		var is_form_data = post instanceof FormData;
		var action = typeof post == 'string'? post: (is_form_data? false: post.__action);

		var request_config = {
			url: this.getDefaultUrl(action, is_form_data),
			data: post,
			success: success
		};

		if(is_form_data && get){
			throw Error('Não é permitido o uso de get em requisições que usam FormData');
		}else
			request_config.get = get;

		Diplomat.request(request_config);
	},
	getDefaultUrl: function(action, is_form_data){
		if(!is_form_data && !action){
			throw Error('Action não informada à requisição');
		}

		return Loader.getCurrentPage() + (!is_form_data? '/' + action: '');
	},
	getComplete: function(data_request){//o complete padrão chama um complete passado pelo programador, se ele passar, mas independente disso remove o loading que foi iniciado no começo da requisição
		var that = this;
		var user_complete = data_request.complete;
		
		return function(){
			if(user_complete)
				user_complete();

			if(data_request.loading)
				Loader.removeLoad(data_request.loading);

			if(data_request.button){
				var $button = getJQueryObj(data_request.button);
				$button.prop('disabled', false);
			}
		};
	},
	getBeforeSend: function(data) {
		var user_before_send = data.beforeSend;

		return function(xhr){
			if(user_before_send)
				user_before_send();
		}
	},
	loadDefaultData: function(data){
		var data_default = {};

		data_default.method = 'POST',
		data_default.data = {};
		data_default.loading = 'pnlContent';

		if(data.client_side){
			data_default.dataType = 'jsonp';
			data_default.crossDomain = true;
			data_default.xhrFields = {
	           withCredentials: true
			};
		}else
			data_default.dataType = 'json';

		if (data.data instanceof FormData || data.data instanceof Form) {
			data_default.cache = false;
			data_default.contentType = false;
			data_default.processData = false;
		}

		return data_default;
	},
	loadFinalData: function(data){
		//var __token = window.sessionStorage.__token;

		//---------------------------------------------------------------------------------------
		
		var default_data = this.loadDefaultData(data);
		var final_data = $.extend( {}, default_data, data );
		var is_form_data = final_data.data instanceof FormData;

		if(typeof final_data.data == "string")
			final_data.data = {
				__action: final_data.data
			};

		//---------------------------------------------------------------------------------------

		final_data.success = this.getCompleteSuccess(final_data);
		final_data.error = this.getCompleteError(final_data);
		final_data.complete = this.getComplete(final_data);
		final_data.beforeSend = this.getBeforeSend(final_data);

		//---------------------------------------------------------------------------------------

		if(!final_data.url)
			final_data.url = this.getDefaultUrl(final_data.action || final_data.data.__action, is_form_data);
		final_data.url += final_data.get? '/' + (
			final_data.get instanceof Array? final_data.get.join('/'): final_data.get
		) : '';
		final_data.url = (final_data.client_side? CONFIG_CLIENT_URI: CONFIG_SERVER_URI) + final_data.url;

		//---------------------------------------------------------------------------------------

		if(final_data.data instanceof Form){
			final_data.form = final_data.data.getForm();
			final_data.data = final_data.data.getData();//getting FormData object
		}

		return final_data;
	},
	handleTreatError: function(success_data, config){
		var typeerror = success_data.__typeerror;

		switch(typeerror){
			case 'validate':
				var validationError = config.validationError || this.validationError;
				validationError(success_data, config.form);
			break;
			case 'databank':
				var databankError = config.databankError || this.databankError;
				databankError(success_data);
			break;
			case 'user':
				var userError = config.userError || this.userError;
				userError(success_data);
			break;
			case 'permit':
				var permitError = config.permitError || this.permitError;
				permitError(success_data);
			break;
			case 'generic':
				var genericError = config.genericError || this.genericError;
				genericError(success_data);
			break;
			case 'detail':
				var detailError = config.detailError || this.detailError;
				detailError(success_data);
			break;
			case 'authentication':
				var authenticationError = config.authenticationError || this.authenticationError;
				authenticationError(success_data);
			break;
			case 'error':
				var realError = config.realError || this.realError;
				realError(success_data);
			break;
			case 'specific': break;
			default: 
				console.log('Categoria do erro: ' + typeerror + ' - data: %o', success_data);
		}

		if (typeof config.fail === 'function')
			config.fail(success_data, success_data.__code);		
	},
	getCompleteSuccess: function(data){
		var user_success = data.success? data.success : this.defaultSuccess;
		var that = this;

		return function(success_data){
			//console.log("success_data: ", success_data);
			if(typeof success_data.__status == 'undefined' || success_data.__status)
				user_success(success_data);
			else{
				that.handleTreatError(success_data, data);
			}
		};
	},
	validationError: function(data_server, form){
		//console.log('data_server.__message', data_server.__message);
		Validator.setErrors(data_server.__message, form);
	},
	authenticationError: function(){
		if(parseBoolean(location.hash))
			Diplomat.redirectToLogin();
		else{
			Dialog.alert("Sua sessão expirou. Por favor, faça o login novamente.");
			doLogout();
		}
	},
	redirectToLogin: function(){
		//alert('Não autenticado');
		window.location = CONFIG_CLIENT_URI + 'Login.php';
	},
	userError: function(data_server){
		Dialog.alert(data_server.__message);
	},
	databankError: function(data_server){
		console.log("Databank error(PDOException): ", data_server);
		Diplomat.showError("camada de dados da aplicação");
	},
	genericError: function(data_server){
		console.log("Generic error(Exception): ", data_server);
		Diplomat.showError("camada genérica da aplicação");
	},
	defaultSuccess: function(data_server){
		//console.log("Resultado da requisição: %o", success_data);
	},
	defaultError: function(error_data){
		console.log("Default error (not captured): ", error_data);
		if (error_data.status == 404) {
			Loader.removeLoad();
			$("#pnlContent").load("NotFound.html");
		}
		else if (error_data.status == 403) {
			Diplomat.showError("permissão de acesso ao servidor (se você estiver navegando em uma rede corporativa, pode haver um bloqueio. Neste caso, tente acessar via 3G)");
		}
		else {
			Diplomat.showError("camada geral da aplicação");
			Diplomat.request({
				url: 'FrontEnd/registerUnhandledError',
				data: {
					error_message: error_data.responseText
				}
			});
		}
	},
	detailError: function(data_server){
		console.log("Detail error(DetailException): ", data_server);
		Dialog.error("<b>Oops! Tivemos um problema:</b> <br><br>" + function(){
			var result = "";
			for(key in data_server.__message)
				result += "<b>" + key + "</b>" + ": " + data_server.__message[key];
			return result;
		}());
	},
	realError: function(data_server){
		console.log("Real error(Captured Error): ", data_server);
		Diplomat.showError("camada de componentes da aplicação");
	},
	permitError: function(data_server){
		Dialog.error("Você não tem permissão para executar esta ação.");
	},
	getCompleteError: function(data){
		var user_error = data.error? data.error : this.defaultError;
		var that = this;

		return function(error_data){
			if( error_data.status == 500 )
				that.handleTreatError( error_data.responseText, data );
			else
				user_error(error_data);
		};
	},
	showError: function(error_location) {
		Dialog.error("<b>Oops! Tivemos um problema:</b> <br><br>Ocorreu um erro inesperado na " + error_location + ".<br><br>Se o problema persistir, entre em contato com a <b>Eurekaria</b> pelo telefone <b>(11) 2538-4971</b> e informe sobre este erro.", function() {
			Loader.removeLoad();
			$("#pnlContent").load("ActionCancelled.html");
		});
	}
}