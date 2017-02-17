function RelNumeroSorte(){
	var data_init;

	function transform(){
		Transformer.erktable('numero_sorte_table');
	}

	function load(){
		$('#numero_sorte_table').bootstrapTable("load", data_init.data_rel);
	}

	function downloadXls(){
		var columns = getTableColumns('numero_sorte_table', false);

		Form.realSubmit('server/RelNumeroSorte/xls', {
			columns: columns
		});
	}

	function showCupom(file_name){
		Dialog.show({
			title: 'Cupom',
			message: __hidden.cupom_dialog_html,
			onshown: function(){
				$('#cupom_img').attr('src', PATH_CUPOM + file_name);
			},
			buttons: [{
				label: 'Ok',
				action: function(dialogRef){
					dialogRef.close();
				}
			}, {
				label: 'Download',
				action: function(dialogRef){
					download(file_name, PATH_CUPOM, 'Cupom');
					dialogRef.close();
				}
			}]
		});
	}

	function handleEvents(){
		$('#btn_download_xls').click(downloadXls);
		$('#numero_sorte_table').on('click-row.bs.table', function(e, row, $element){
			showCupom(row.nm_arquivo);
		});
	}

	this.init = function(){
		Loader.setTitle();

		Diplomat.fastRequest(function(server_data){
			data_init = server_data;
			console.log("data_init", data_init);
			transform();
			load();
			handleEvents();
		}, 'init');
		Loader.removeLoad();
	}
}

RelNumeroSorte.treatStatus = function(value){
	switch(value){
		case 'P': return 'Pendente';
		case 'A': return 'Aprovado';
		case 'R': return 'Reprovado';
	}
}

RelNumeroSorte.treatType = function(value){
	switch(value){
		case 'C': return 'Cupom';
		case 'N': return 'NFE';
	}
}