function RelParticipantes(){
	var data_init;

	function transform(){
		Transformer.erktable('participantes_table');
	}

	function load(){
		$('#participantes_table').bootstrapTable("load", data_init.data_rel);
	}

	function downloadXls(){
		var columns = getTableColumns('participantes_table', false);

		Form.realSubmit('server/RelParticipantes/xls', {
			columns: columns
		});
	}

	function downloadXlsAllColumns(){
		var columns = getTableColumns('participantes_table');

		Form.realSubmit('server/RelParticipantes/xls', {
			columns: columns
		});
	}

	function handleEvents(){
		$('#btn_download_xls').click(downloadXls);
		$('#btn_download_xls_all').click(downloadXlsAllColumns);
	}

	this.init = function(){
		Loader.setTitle();
		Diplomat.fastRequest(function(server_data){
			data_init = server_data;
			transform();
			load();
			handleEvents();
			Loader.removeLoad();
		}, 'init');
	}
}