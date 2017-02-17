function RelTriagem(){
	var data_init;

	function transform(){
		Transformer.erktable('numero_sorte_table');
	}

	function load(){
		$('#numero_sorte_table').bootstrapTable("load", data_init.data_rel);
	}

	function downloadXls(){
		var columns = getTableColumns('numero_sorte_table', false);

		Form.realSubmit('server/RelTriagem/xls', {
			columns: columns
		});
	}

	function showCupom(file_name, id_cupom){
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
					download(file_name, PATH_CUPOM, 'Cupom.jpg');
					dialogRef.close();
				},
			}, {
				label: 'Aprovar',
				cssClass: 'btn-success',
				action: function(dialogRef){
					Diplomat.request({
						action: 'approve',
						loading: dialogRef,
						get: [id_cupom],
						success: function(server_data){
							if(parseInt(server_data.success)){
								Dialog.alert('<p>Cupom aprovado!</p>');
								Loader.refreshCurrentPage();
							}
						}, 
						complete: function(){
							dialogRef.close();
						}
					});
				}
			}, {
				label: 'Reprovar',
				cssClass: 'btn-danger',
				action: function(dialogRef){
					Diplomat.request({
						action: 'disapprove',
						loading: dialogRef,
						get: [id_cupom],
						success: function(server_data){
							if(server_data){
								Dialog.alert('Cupom reprovado!');
								Loader.refreshCurrentPage();
							}
						}, 
						complete: function(){
							dialogRef.close();
						}
					});
				}
			}]
		});
	}

	function handleEvents(){
		$('#btn_download_xls').click(downloadXls);
		$('#numero_sorte_table').on('click-row.bs.table', function(e, row, $element){
			showCupom(row.nm_arquivo, row.id_cupom);
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