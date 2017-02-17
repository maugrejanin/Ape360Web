function Dashboard(post_data){

	var data_init, 
		update_interval;

	function populate(){
		$('#qt_cupom .macro-value').html(data_init.macro_data.qt_cupom);
		$('#vl_total .macro-value').html(UserTreat.money(data_init.macro_data.vl_total));
		$('#qt_usuario .macro-value').html(data_init.macro_data.qt_usuario);
		$('#qt_numero .macro-value').html(data_init.macro_data.qt_numero);
		$('#vl_medio .macro-value').html(UserTreat.money(data_init.macro_data.vl_medio));
		$('#vl_remain .macro-value').html(UserTreat.money(data_init.macro_data.vl_remain));
	}

	function setUpdateInterval(){
		update_interval = setInterval(function(){
			debug.log('uploading');

			Diplomat.request({
				success: function(data_server){
					data_init = data_server;
					populate();
				}, 
				databankError: function(data_server){
					debug.log('Problema de banco de dados. Error: %o', data_server);
				},
				action: 'init',
				loading: false
			});
		}, CONFIG_TIME_INTERVAL_DASHBOARD_UPDATE);
	}

	this.finalize = function(){
		clearInterval(update_interval);
	}

	this.init = function(){
		Loader.setTitle();

		Diplomat.fastRequest(function(data_server){
			data_init = data_server;
			debug.log('data_init', data_init);
			setUpdateInterval();
			populate();
			Loader.removeLoad();
		}, 'init');
	}
}