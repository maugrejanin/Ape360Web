var Loader = {
	map: {
		Dashboard: 'Início',
		RelNumeroSorte: 'Relatório de Cupons',
		RelParticipantes: 'Relatório de Participantes',
		RelTriagem: 'Triagem',
		Permit: 'Permissões',
	},

	load_list_length: 0,
	flag_load: false,
	current_controller: null,
	load_list: {
		http: [
		],
		lib: [
			'bootstrap-dialog.min',
			'jquery.mask.min',
			'jquery.maskMoney.min',
			'bootstrap-table.min',
			'bootstrap-select.min',
			'bootstrap-datepicker.min',
			'bootstrap-switch.min',
		],
		util: [
			'History',
			'Transformer.obj',
			'Treat.obj',
			'Form.obj',
			'Php.obj',
			'Dialog.class',
			'Diplomat.obj',
			'Validator.obj'
		]
	},
		
	default_page: "Dashboard",

	getLoadPageLength: function(){
		if(!this.load_list_length)
			for(path in this.load_list)
				this.load_list_length += this.load_list[path].length;

		return this.load_list_length;
	},

	init: function(initial_page, onfinish){
		//console.log("Loader Init!");
		this.load(initial_page, onfinish);
	},

	load: function (page_reference, onfinish) {
		if(this.flag_load)
			return;
		else
			this.flag_load = true;

		var that = this, 
			count = 0,
			length = this.getLoadPageLength();

		for(var path in this.load_list){
			var group = this.load_list[path];
			if (path == "http") {
				for(var c = 0; c < group.length; c++)
					$.getScript(group[c] + '.js').complete(function(){
						count++;
					}).fail(function( jqxhr, settings, exception ) {
					    console.log('O script não pôde ser carregado. Error: %o', exception);
					    console.log('Settings: %o', settings);
					});
			} else {
				for(var c = 0; c < group.length; c++)
					$.getScript('js/' +  path + '/' + group[c] + '.js').complete(function(){
						count++;

						if(length == count)
							$.getScript('js/' + page_reference + '.js').done(function(){
								//console.log("Loader Load - pageload!");
								that.pageLoad(page_reference);
								onfinish();
								//$.getScript('js/lib/bootstrap-datepicker-pt-br.js');
							});

					}).fail(function( jqxhr, settings, exception ) {
						console.log('error loader: ', jqxhr, settings, exception);
					    console.log('O script  não pôde ser carregado. Error: %o', exception);
					    console.log('Settings: %o', settings);
					});
			}
		}

		for(page in this.map)
			if(page != page_reference)
				$.getScript('js/' + page + '.js').fail(function( jqxhr, settings, exception ) {
					console.log('error loader: ', jqxhr, settings, exception);
				    console.log('O script "' + page + '" não pôde ser carregado. Error: %o', exception);
				});
	},

	getCurrentPage: function(){
		return location.hash? location.hash.substring(1): //substring(1) remove o char '#'
			PHP.ucfirst(PHP.basename(location.href, ".html")); // em página de acesso direto, como o login, não possuem hash, obtem-se então o nome da página
	},

	redirect: function(file){
		window.location.href = CONFIG_CLIENT_URI + file;
	},

	pageLoad: function (page_url, target_load, post_data, back, forward) {
		target_load = target_load || 'pnlContent';
		var $target_load = $("#" + target_load);
		isPopStateChange = !!(back || forward);

		if(!isPopStateChange) {
			if (typeof pushHistory == 'function') {
				pushHistory(page_url, target_load, post_data);
			}
		}
		page_url = page_url + ".html";
		// console.log("page_url: ", page_url);
		return Diplomat.request({
			url: page_url,
			async: true,
			context: $target_load,
			data: post_data,
			client_side: true,
			dataType: "html",
			beforeSend: function(){
				Loader.setLoad();

				if(Loader.current_controller && Loader.current_controller.finalize){
					//!!! esvaziar hidden para não ocupar muita memória
					Loader.current_controller.finalize();
				}
			},
			loading: false,
			success: function(result_html) {
				var control_name = PHP.ucfirst(PHP.basename(page_url, ".html"));
				var control_class = window[control_name];
				var $result_html = Home.manageHTMLActionAccess(result_html, control_name);

				$target_load.find('> :not(.loading)').remove();
				$target_load.append($result_html);

				__hidden = getHiddenHtml($target_load);

				if(!control_class){
					Dialog.alert('Por favor implemente o seguinte controller js: <b>' + control_name + '</b>', function() {
						Loader.removeLoad();
						$target_load.load("ActionCancelled.html");
					});
					return false;
				}

				Loader.current_controller = new control_class(post_data || {}, target_load);
				Loader.current_controller.init();//calling init

				constructBar(back);
			}
		});
	},

	refreshCurrentPage: function(){
		for(key in __history){
			if(__history[key].current){
				var current_page = __history[key];
				break;
			}
		}

		if(CURRENT_MODE == CONFIG_MODE_DEVELOPMENT)
			$.getScript('js/' + current_page.pageUrl + '.js').complete(function(){
				Loader.pageLoad(current_page.pageUrl, current_page.panelId, current_page.postData, false, true);
			}).fail(function( jqxhr, settings, exception ) {
			    console.log('O script "' + current_page.pageUrl + '" não pôde ser carregado. Error: %o', exception);
			});
		else
			Loader.pageLoad(current_page.pageUrl, current_page.panelId, current_page.postData, false, true);
	},

	setTitle: function(title, target){
		target = target || 'page_header';
		title = title || Loader.map[Loader.getCurrentPage()];
		$target = getJQueryObj(target);

		return $target.html(title);
	},

	/**
	* @details 
	* 1 - Temporariamente insere o style position=relative no container passado por parâmetro
	* 2 - Dá suporte para o parâmetro container ser um jquery object com muitos containers
	*/

	setLoad: function(container, append){
		container = container || 'pnlContent';
		var $container = container instanceof BootstrapDialog? getJQueryObj(container.getModalBody()): getJQueryObj(container);

		if($container.length > 1){
			$container.each(function(){
				Loader.setLoad(this, append);
			});
			return true;
		}

		var div = document.createElement('div');
		var count = this.__loading_count;
		var container_obj = this.__loading_container_obj;
		var container_id = $container.attr('id');

		if(!container_id){
			container_id = new Date().valueOf();
			$container.attr('temp-loading-id', container_id);
		}

		$(div).addClass("loading");

		if(container_id)
			if(count[ container_id ]){
				count[ container_id ]++;
				return true;
			}else{
				//console.log("container_id", container_id);
				container_obj[ container_id ] = {};
				container_obj[ container_id ].old_position = $container.css('position');
				container_obj[ container_id ].obj = $container.css('position', 'relative');
				count[ container_id ] = 1;
			}

		if(append) {
			$container.append(div);
		} else {
			$container.html(div);
		}
	},

	removeLoad: function(container){
		container = container || 'pnlContent';
		var $container = container instanceof BootstrapDialog? getJQueryObj(container.getModalBody()): getJQueryObj(container);

		if($container.length > 1){
			$container.each(function(){
				Loader.removeLoad(this);
			});
			return true;
		}

		var $load = $container.find(".loading").eq(0);
		var count = this.__loading_count;
		var container_obj = this.__loading_container_obj;
		var container_id = $container.attr('id') || $container.attr('temp-loading-id');

		if(container_id)
			if(count[ container_id ]){
				if(count[ container_id ] > 1){
					count[ container_id ]--;
					return true;
				}else
					delete count[ container_id ];
			}else{
				console.log('Ocorreu um erro com o contador do loading: Tentativa de remoção de um loading que não foi iniciado');
				return false;
			}

		$load.fadeOut(function(){
			$load.remove();
			container_obj[ container_id ].obj.css('position', container_obj[ container_id ].old_position);
			delete container_obj[ container_id ];
		});
	},

	__loading_count: {//objeto vazio que visa contar os loadings de cada container do sistema

	},

	__loading_container_obj: {//objeto vazio que visa armazenar os containers nos quais são inseridos os loadings até que estes sejam removidos.

	}
}