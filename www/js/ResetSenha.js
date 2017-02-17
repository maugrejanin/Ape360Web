	var hash_code = "";
	$(document).ready(function () {
		init();

		//Click event to scroll to top
		$('#btn_site').click(function(event){
			window.open("index.php", "_self");
		});
		$('#btn_reset_senha').click(function(event){
			resetSenha();
		});
	});
	function resetSenha() {
		if(document.getElementById("ds_password").value == "") {
			jAlert("Por favor, digite a sua nova senha.", "");
			return false;
		}
		document.getElementById("ds_hash").value = hash_code;
		console.log("Form data: ", new FormData(document.getElementById("frm_reset_senha")));
		Diplomat.request({
			url: 'FrontEnd/doResetSenha',
			data: {
				"ds_password": document.getElementById("ds_password").value,
				"ds_hash": hash_code
			},
			success: function(data){
				console.log("data: ", data);
				if (data.success == "1") {
					jAlert("Sua senha foi redefinida com sucesso!", "Reset de Senha", function() {
						window.open("index.php", "_self");
					});
				}
				else {
					jAlert("Sua senha não pôde ser redefinida.", "Reset de Senha", function() {
						window.open("index.php", "_self");
					});	
				}
			}
		});
	}
	function init() {
		hash_code = getParameterByName("h");
		console.log("hash_code: ", hash_code);
		console.log("form: ", document.getElementById("frm_reset_senha"));
		debug = Debugger();
		Diplomat.request({
			url: 'FrontEnd/openResetSenha',
			get: [hash_code],
			success: function(data){
				console.log("data: ", data);
				if (data.success != "1") {
					jAlert("Esta solicitação não existe ou está expirada.", "Solicitação inválida", function() {
						window.open("index.php", "_self");
					});		
				}
				$('#loading').fadeOut("slow", function() {
					$('#wrap').fadeIn("slow");
					$('html, body, #wrap').css('overflow-y', 'visible');	
				});
			}
		});
	}
	function getParameterByName(name, url) {
	    if (!url) url = window.location.href;
	    name = name.replace(/[\[\]]/g, "\\$&");
	    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
	        results = regex.exec(url);
	    if (!results) return '';
	    if (!results[2]) return '';
	    return decodeURIComponent(results[2].replace(/\+/g, " "));
	}