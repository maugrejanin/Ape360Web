	var date_is_expired = false;
	$(document).ready(function () {
		init();
	});
	function setImageJavascript(objId, imageName, force_update){
		force_update = typeof force_update == 'undefined'? FORCE_UPDATE_IMG: force_update;
		var image_url = PATH_IMG + "/" + imageName;
		var image_err = PATH_IMG + "/event-not-found.jpg";
	    $.ajax({
			url: image_url,
			method: "GET",
			success: function(data_return){
				objId.style.background = "url('" + image_url + (force_update? '?' + new Date().getTime(): '') + "') no-repeat center center";
				objId.className += " image-default-position";
				// objId.style.backgroundSize = "auto 100%";

			},
			error: function(error_data){
				objId.style.background = "url('" + image_err + "') no-repeat center center";
				// objId.style.backgroundSize = "auto 100%";
				objId.className += " image-default-position";
			}
		});
	}
	function handleAccessExpireDateFunctions(){
		var expire_date = new Date(DATE_EXPIRE_PROMOTION);
		var now_date = new Date();

		if(now_date > expire_date){
			date_is_expired = true;
			$('.menu-item[target=pnl_cadastro]')
				.add('#btn_novo_cupom')
				.remove();

			$('.item1').css('left', '255px');
			$('.item3').css('left', '518px');
			$('.item4').css('left', '781px');
		}
	}

	function init() {
		// Navigator.validNavigator();

		gtmPageView("pnl_home");
		Diplomat.request({
			url: 'FrontEnd/verifyCredentials',
			success: function(data){
				getHiddenHtml(hiddens);

				if (data.user === null) {
					activePanel("pnl_home");
				} else {
					USER_OBJ = data.user;
					openLogin(true);
				}

				debug = Debugger();
				transform();
				$('#loading').fadeOut("slow", function() {
					$('#wrap').fadeIn("slow");
					$('html, body, #wrap').css('overflow-y', 'visible');	
				});

				handleAccessExpireDateFunctions();
				handleEvents();	

				if(!show_winner)
					jAlert("Campanha Finalizada. Data da divulgação dos resultados 31/01.");
			}
		});
	}
	function openCrop(){
		var $popup_container = $(getObjHTML(hiddens.popup_container_html));
		$popup_container.hide();

		if($(window).outerWidth() <= 712)//Se for mudar os 712, lembre-se de fazer isso no midiaquery do index.css também
			$('body, html').css('overflow', 'hidden');

		$('html').append($popup_container);

		$('#btn_file_crop').click(function(){
			$('#new_image').click();
		});

		$('.btn-close-crop').click(function(){
			$('.crop-container').fadeOut(function(){
				$(this).remove();
			});

			if($(window).outerWidth() <= 712)
				$('body, html').css('overflow', 'visible');
		});

		$('#btn_confirm_crop').click(function(){
			$('.btn-close-crop').click();
			crop.setImgResult('img_avatar');
			crop.send(function(cropped_image){
				var form_data = new FormData();
				form_data.append('cropped_image', cropped_image);

				Diplomat.request({
					url: 'FrontEnd/saveAvatar',
					data: form_data,
					success: function(){
						jAlert('Avatar salvo com sucesso!', 'Mensagem');
					}
				})
			});
		});

		var crop = new Crop('img_crop', 'img_preview', 'new_image', {
			aspectRatio: 164/135
		});
		crop.run();

		$popup_container.fadeIn();
	}
	function handleEvents() {
		$(".menu-item, .logo-top").click(function(event){
			switch ($(this).attr("target")) {
				case "pnl_cupons":
					listarCupons();
					break;
				case "pnl_cadastro":
					abrirCadastro();
					break;
				default:
					activePanel($(this).attr("target"));
					break;
			}
		});

		$('.panel-backdrop > div').click(function(e){
			e.stopPropagation();
		});
		$('.panel-backdrop').click(function(){
			$(this).fadeOut();
		});
		// $('.novo-cupom-opcao > .painel-b').click(function(){
		// 	$(this).parents('.panel-backdrop').eq(0).fadeOut();
		// });

		$('.btn-avatar').click(openCrop);

		$( ".login-input" ).keypress(function(key) {
			if (key.keyCode == 13) {
				doLogin();
				key.preventDefault();
			}
		});

		$('#lnk_cupom_upload').click(function(){
			$('#new_image_cupom').click();
		});

		$('#new_image_cupom').change(function(){
			$('#lbl_cupom_imagem').html(
				this.files.length == 0? 'NENHUMA IMAGEM SELECIONADA': this.files.item(0).name
			);
		});

		$("#btn_fale").click(function(){
			jAlert("Em caso de dúvidas, sugestões ou reclamações, envie um e-mail para <a href='mailto:falecom@amigosecretoenahering.com.br' style='color: #ffffff; text-decoration: underline;'>falecom@amigosecretoenahering.com.br</a>.", "Fale conosco");
		});

		$('#send_cad').click(regulamentoAceite);
		$('#btn_regulamento_aceite').click(register);
		$('#btn_login').click(doLogin);
		$('#btn_logout').click(doLogout);
		$('#btn_regulamento').click(function() {
			activePanel("pnl_regulamento");
		});
		$("#btn_novo_cupom").click(novoCupom);
		$("#lnk_modelo_cfe").hover(function() {
			$(".novo-cupom-opcao-cfe").fadeIn();
		}, function() {
			$(".novo-cupom-opcao-cfe").fadeOut();
		});
		$("#lnk_modelo_nfe").hover(function() {
			$(".novo-cupom-opcao-nfe").fadeIn();
		}, function() {
			$(".novo-cupom-opcao-nfe").fadeOut();
		});
		$("#lnk_modelo_cupom").hover(function() {
			$(".novo-cupom-opcao-cupom").fadeIn();
		}, function() {
			$(".novo-cupom-opcao-cupom").fadeOut();
		});
		$("#btn_novo_cupom_c").click(function() {
			cadastrarNovoCupom("C");
		});
		$("#btn_novo_cupom_f").click(function() {
			cadastrarNovoCupom("F");
		});
		$("#btn_novo_cupom_n").click(function() {
			cadastrarNovoCupom("N");
		});
		$("#send_cupom").click(salvarNovoCupom);
		$("#btn_novo_cupom_s_fechar").click(function() {
			$("#pnl_novo_cupom_sucesso").fadeOut();
		});
		$("#lnk_cupom_duvida").click(mostrarAjudaCupom);
		$("#btn_novo_cupom_d_fechar").click(function() {
			$("#pnl_novo_cupom_duvida").fadeOut();
		});
		$("#btn_cupom_d_fechar").click(function() {
			$("#pnl_cupom_detalhe").fadeOut();
		});
		$("#btn_senha_a_fechar").click(function() {
			$("#pnl_alterar_senha").fadeOut();
		});
		$("#btn_regulamento_aceite_fechar,#btn_regulamento_recusa").click(function() {
			$("#pnl_regulamento_aceite").fadeOut();
		});
		$("#btn_senha").click(abrirAlterarSenha);
		$("#btn_alt_senha_ok").click(alterarSenha);
		$("#btn_cupom_com_cartao, #btn_cupom_sem_cartao").click(abrirOpcaoCupom);

		// $("#pnl_novo_cupom_opcao").click(function() {
		// 	$("#pnl_novo_cupom_opcao").fadeOut();
		// });
		$('[data-toggle="tooltip"]').tooltip();
	}
	function loadReenvioSenha(){
		$("#pnl_esqueci_senha").fadeIn(function(){
			$('#esq_ds_email').val($('#lgn_ds_email').val());

			$('#btn_senha_e_fechar').click(function(){
				$("#pnl_esqueci_senha").fadeOut();
			});

			$('#btn_esq_senha_ok').click(function(){
				var form = document.getElementById('frm_esqueci_senha');

				if(!Validator.valid(form, setError))
					return false;

				var form_data = new FormData(form);

				Diplomat.request({
					url: 'FrontEnd/solicitarResetSenha',
					data: form_data,
					success: function(data){
						if (parseInt(data.success) == 1) {
							jAlert(data.message, "Senha enviada");
						} else {
							jAlert(data.message, "Senha não alterada");
						}
					}, 
					complete: function(){
						$("#pnl_esqueci_senha").fadeOut();
					}
				});
			});

			$('#esq_ds_cpf').focus();
			gtmPageView("pnl_esqueci_senha");
		});
	}
	function abrirOpcaoCupom() {
		$("#ic_cartao").val($(this).attr("vl_opcao"));
		$("#pnl_cupom_cartao").fadeOut();
		$("#pnl_novo_cupom_opcao").fadeIn();
	}
	function abrirAlterarSenha() {
		$(".input-as").val("");
		$("#pnl_alterar_senha").fadeIn();
		gtmPageView("pnl_alterar_senha");
	}
	function alterarSenha() {
		Diplomat.request({
			url: 'Login/alterarsenha',
			data: new FormData(document.getElementById("frm_alterar_senha")),
			success: function(data){
				if (data.success == "1") {
					$("#pnl_alterar_senha").fadeOut();
					jAlert("Sua senha foi alterada. Use-a no próximo login.", "Senha alterada")
				}
				else {
					jAlert(data.message, "Senha não alterada");
				}
			}
		});
	}
	function setRGMask(){
		var rgBeravior = function(val){
			var normal_string = val.replace(/[^0-9a-zA-Z]/g, '');
			var normal_char_length = normal_string.length;
			var lastchar_mask = /[a-zA-Z]/.test(normal_string.substring(normal_string.length - 1))? '': 'Y';

			if(normal_char_length <= 9)
				var returno = '00.000.000-Y' + lastchar_mask;
			else if(normal_char_length == 10)
				var returno = '00.000.000-0Y' + lastchar_mask;
			else if(normal_char_length == 11)
				var returno = '00.000.000-00Y' + (lastchar_mask? '-' + lastchar_mask: lastchar_mask);
			else if(normal_char_length == 12)
				var returno = '00.000.000-000-Y' + lastchar_mask;
			else if(normal_char_length == 13)
				var returno = '00.000.000-000-0Y' + lastchar_mask;
			else if(normal_char_length == 14)
				var returno = '00.000.000-000-00Y' + (lastchar_mask? '-' + lastchar_mask: lastchar_mask);
			else 
				var returno = '00.000.000-000-000-Y';

			return returno;
		};

		options = {
			translation: {
				Y: {pattern: /[a-zA-Z0-9]/},
			}
		};

		$('.rg').keypress(function(){
			$(this).mask(rgBeravior.apply({}, [$(this).val()]), options);
		}).mask(rgBeravior, options);
	}
	function transform(){
		Transformer.money('vl_cupom');
		setRGMask();
		// $('#cd_cupom').mask('9999999999');
		$('.date').mask('99/99/9999');
		$('.cpf').mask('999.999.999-99');
		$('.uf').mask('SS');
		$('.genre').mask('G', {'translation': {
				G: {pattern: /[mfMF]/},
			}
		});

		var SPMaskBehavior = function (val) {
		  return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
		},
		spOptions = {
		    onKeyPress: function(val, e, field, options) {
				field.mask(SPMaskBehavior.apply({}, arguments), options);
		    }
		};

		$('.phone').mask(SPMaskBehavior, spOptions);
		$('#frm_cadastro input:not([type=password])').maskUpperName();
	}
	function setError(element, message){
		jAlert(message, "Validação", function(){
			element.focus();
		});
		throw Error();
	}
	function regulamentoAceite() {
		var form = document.getElementById('frm_cadastro');

		if(!Validator.valid(form, setError))
			return false;
		$("#pnl_regulamento_aceite").fadeIn();
	}
	function register(){
		$("#pnl_regulamento_aceite").fadeOut();
		var form = document.getElementById('frm_cadastro');

		if(!Validator.valid(form, setError))
			return false;

		var nasc1 = document.getElementById('ds_nascimento');
		var nasc2 = document.getElementById('ds_nascimento_amigo');

		if (nasc1.value.length != 10 || nasc2.value.length != 10) {
			jAlert("Por favor, preencha as datas de nascimento no fotmato dd/mm/aaaa.", "Formato de data");
			return false;
		}

		var form_data = new FormData(form);

		Diplomat.request({
			url: 'FrontEnd/cadastrar',
			data: form_data,
			button: 'send_cad',
			validationError: function(data_server){
				Validator.setErrors(data_server.__message, form, setError);
			},
			success: function(data){
				if (data.success == "1") {
					console.log("data: ", data);
					USER_OBJ = data.user;
					jAlert(data.message, data.title, function() {
						openLogin(true);
					});
				}
				else {
					jAlert(data.message, data.title);
				}
			}
		});
	}
	function openLogin(logado) {
		if (logado) {
			$("#pnlLogin").fadeOut();
			$("#pnl_boas_vindas").html("Olá, " + (USER_OBJ.ds_nome.indexOf(" ") == -1 ? USER_OBJ.ds_nome : USER_OBJ.ds_nome.substring(0, USER_OBJ.ds_nome.indexOf(" "))));
			$("#pnlLogado").fadeIn();
			listarCupons();
		}
		else {
			$("#pnlLogado").fadeOut();
			$("#pnlLogin").fadeIn();
			activePanel("pnl_home");
		}
	}
	function getParameterByName(name, url) {
	    if (!url) url = window.location.href;
	    name = name.replace(/[\[\]]/g, "\\$&");
	    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
	        results = regex.exec(url);
	    if (!results) return '';
	    if (!results[2]) return '';
	    return decodeURIComponent(results[2].replace(/\+/g, " "));
	};
	function activePanel(pnlId) {
		console.log("pnlId: ", pnlId);
		$(".pagina").fadeOut("fast");
		setTimeout(function(){
			$("#" + pnlId).fadeIn("slow"); 
		}, 300);
		gtmPageView(pnlId);
	}
	function listarCupons() {
		if(USER_OBJ)
			$("#img_avatar").attr("src", "img/avatar/" + USER_OBJ.id_usuario + ".jpg");

		Diplomat.request({
			url: 'Cupom/listCupons',
			success: function(data){
				console.log("data cupons: ", data);
				popularCupons(data);
				$('#loading').fadeOut("slow", function() {
					$('#wrap').fadeIn("slow");
					$('html, body, #wrap').css('overflow-y', 'visible');
				});
				activePanel("pnl_cupons");
			},
			authenticationError: function() {
				console.log("Permit error!");
				if(date_is_expired){
					jAlert("Por favor, faça login.", "Você está desconectado", function(){
						document.getElementById('lgn_ds_email').focus();
					});
				}else{
					activePanel("pnl_cadastro");
					jAlert("Por favor, faça login ou cadastre-se", "Você está desconectado");
				}
			}
		});
	}
	function popularCupons(data) {
		COMPRAS = data.data;

		console.log("populate cupons compras: ", COMPRAS);
		var indices = ['dt_envio', 'vl_cupom', 'cd_cupom'];
		var cupons   = data.data.cupons;
		var numeros  = data.data.numeros;

		//------------------ SALDO --------------------
		$("#lbl_nome_usuario").html((USER_OBJ.ds_nome.indexOf(" ") == -1 ? USER_OBJ.ds_nome : USER_OBJ.ds_nome.substring(0, USER_OBJ.ds_nome.indexOf(" "))));
		$("#lbl_local").html(USER_OBJ.ds_local);
		console.log("USER_OBJ: ", USER_OBJ);
		// $("#lbl_saldo").html();
		if( cupons.length > 0 ){
			if( typeof data.saldo !== 'undefined' ){
				// $('#lbl_saldo').html("Saldo: " + data.saldo);
				$('#lbl_saldo').html("");
			}
		}
		else {
			// $('#lbl_saldo').html("R$ 0,00");
			$('#lbl_saldo').html("");
		}
		$('#lbl_saldo').html("");
		//------------------ SALDO --------------------

		//------------------ LISTAS --------------------

		lst_cupons = document.getElementById("lst_cupons");
		lst_cupons.innerHTML = "";
		lst_numeros = document.getElementById("lst_numeros");
		lst_numeros.innerHTML = "";
		tbl_cupons = document.createElement("table");

		for(var id_cupom in cupons) {
			row_cupom = document.createElement("tr");
			row_cupom.className = "linha-cupom";
			
			col_cupom = document.createElement("td");
			col_cupom.className = "coluna-cupom cc-lupa";
			col_cupom.setAttribute("key", id_cupom);
			row_cupom.appendChild(col_cupom);

			col_cupom = document.createElement("div");
			col_cupom.className = "coluna-cupom cc-data";
			col_cupom.innerHTML = cupons[id_cupom]["dt_envio"];
			row_cupom.appendChild(col_cupom);

			col_cupom = document.createElement("div");
			col_cupom.className = "coluna-cupom cc-cod";
			col_cupom.innerHTML = cupons[id_cupom]["cd_cupom"];
			row_cupom.appendChild(col_cupom);

			col_cupom = document.createElement("div");
			col_cupom.className = "coluna-cupom cc-valor";
			col_cupom.innerHTML = cupons[id_cupom]["vl_cupom"];
			row_cupom.appendChild(col_cupom);
			
			tbl_cupons.appendChild(row_cupom);
		}

		lst_cupons.appendChild(tbl_cupons);
		$(".cc-lupa").click(function(){
			detalheCupom($(this).attr("key"), false);
		});

		for(var key in numeros) {
			row_numero = document.createElement("div");
			row_numero.className = "linha-numero";
			row_numero.innerHTML = '<div class="coluna-cupom cc-lupa" key="' + numeros[key].id_cupom + '"></div><div class="number-container">' + numeros[key].ds_numero + '</div>';
			lst_numeros.appendChild(row_numero);
		}

		$(".linha-numero .cc-lupa").click(function(){
			detalheCupom($(this).attr("key"), true);
		});
	}
	function detalheCupom(key, from_number) {
		var html_delete_btn = from_number? '': '<div class="botao-listrado" id="delete_cupom">EXCLUIR ESTE CUPOM</div>';
		
		if(from_number)
			$("#extra_message_title_cupom").show();
		else
			$("#extra_message_title_cupom").hide();

		$("#lbl_cupom_d_tipo").html("Tipo: <span class='cupom-d-result'>" + COMPRAS.cupons[key].ds_tipo + "</span>");
		$("#lbl_cupom_d_codigo").html("Código: <span class='cupom-d-result'>" + COMPRAS.cupons[key].cd_cupom + "</span>");
		$("#lbl_cupom_d_data").html("Data: <span class='cupom-d-result'>" + COMPRAS.cupons[key].ds_dt_cupom + "</span>");
		$("#lbl_cupom_d_valor").html("Valor: <span class='cupom-d-result'>" + COMPRAS.cupons[key].vl_cupom + "</span>");
		$("#lbl_cupom_d_status").html("Situação: <span class='cupom-d-result'>" + COMPRAS.cupons[key].ds_cupom_status + "</span>" + html_delete_btn);

		console.log("Código: ", COMPRAS.cupons[key]["cd_cupom"]);
		console.log("Situação: ", COMPRAS.cupons[key]["ds_cupom_status"]);
		console.log("Data da Avaliação: ", COMPRAS.cupons[key]["ds_dt_status"]);
		console.log("Motivo: ", COMPRAS.cupons[key]["ds_motivo_status"]);
		$("#pnl_cupom_detalhe").fadeIn();

		$('#delete_cupom').click(function(){
			deleteCupom(key);
		});
	}
	function deleteCupom(id_cupom){
		var this_luck_numbers = COMPRAS.numeros.filter(function(numero){
			return numero.id_cupom == id_cupom;
		}).map(function(numero){
			return numero.ds_numero;
		});

		$('html').append(hiddens.delete_popup_container_html);
		$('.popup-confirm #popup_ok').click(function(){
			Diplomat.request({
				url: 'Cupom/delete/' + id_cupom,
				success: function(server_result){
					if(server_result)
						jAlert('Cupom excluído com sucesso', 'Mensagem', function(){
							window.location.reload();
						});
				}
			});
		});
		$('.popup-confirm #popup_cancel').click(function(){
			$('.popup-confirm').remove();
		});
	}
	function doLogin() {
		if(!validateLoginForm())
			return false;

		Diplomat.request({
			url: 'Login/loginFrontEnd',
			data: new FormData(document.getElementById("frm_login")),
			success: function(data){
				if (data.success == "1") {
					USER_OBJ = data.user;
					openLogin(true);
				}
				else {
					jAlert(data.message, data.title);
				}
			}
		});
	}
	function doLogout() {
		Diplomat.request({
			url: 'FrontEnd/logout',
			success: function(){
				USER_OBJ = undefined;
				openLogin(false);
			}
		});
	}
	function validateLoginForm(){
		return Validator.valid('frm_login', setError);
	}
	function abrirCadastro() {
		document.getElementById("frm_cadastro").reset();
		activePanel("pnl_cadastro");
	}
	function novoCupom() {
		$("#pnl_cupom_cartao").fadeIn();
	}
	function mostrarAjudaCupom() {
		var tipo = $("#tp_cupom").val();
		$("#pnl_cupom_duvida").removeClass();
		switch (tipo) {
			case "C":
				$("#pnl_cupom_duvida").addClass("novo-cupom-duvida");
				break;
			case "F":
				$("#pnl_cupom_duvida").addClass("novo-cfe-duvida");
				break;
			case "N":
				$("#pnl_cupom_duvida").addClass("novo-nfe-duvida");
				break;
		}
		$("#pnl_novo_cupom_duvida").fadeIn();
	}
	function cadastrarNovoCupom(tipo) {
		$("#pnl_novo_cupom_opcao").fadeOut();
		$("#tp_cupom").val(tipo);
		switch (tipo) {
			case "C":
			case "F":
				$(".lbl_form_cupom_tipo").html("CUPOM");
				$(".lbl_form_cupom_tipo_de").html("DO");
				$("#lbl_cupom_rodape").html("OBS: GUARDE SEU CUPOM! SERÁ INDISPENSÁVEL A APRESENTAÇÃO DELE CASO VOCÊ SEJA SORTEAD" + (USER_OBJ.ds_sexo == "F" ? "A" : "O") + ".");
				$('#new_image_cupom').attr('placeholder', 'Foto do cupom');
				break;
			case "N":
				$(".lbl_form_cupom_tipo").html("NOTA");
				$(".lbl_form_cupom_tipo_de").html("DA");
				$("#lbl_cupom_rodape").html("OBS: GUARDE SUA NOTA FISCAL! SERÁ INDISPENSÁVEL A APRESENTAÇÃO DELE CASO VOCÊ SEJA SORTEAD" + (USER_OBJ.ds_sexo == "F" ? "A" : "O") + ".");
				$('#new_image_cupom').attr('placeholder', 'Foto da nota');
				break;
		}
		activePanel("pnl_novo_cupom");
	}
	function salvarNovoCupom() {
		var frm_cupom = document.getElementById('frm_cupom');

		if(!Validator.valid(frm_cupom, setError))
			return false;

		var frm_cupom_data = new FormData(frm_cupom);

		Diplomat.request({
			url: 'Cupom/insertCupons',
			button: 'send_cupom',
			data: frm_cupom_data,
			validationError: function(data_server){
				console.log("Error: ", data_server);
				Validator.setErrors(data_server.__message, frm_cupom, setError);
			},
			success: function(data){
				console.log("Inserido: ", data);
				listarCupons();
				var tipo = $('#tp_cupom').val(); // O tipo que estava no hidden vai definir a mensagem
				switch (tipo) {
					case "C":
					case "F":
						$("#lbl_novo_cupom_s_titulo").html("CUPOM CADASTRADO COM SUCESSO");
						$("#lbl_novo_cupom_s_mensagem").html("Se você ganhar, será necessário apresentar os seus<br>cupons cadastrados. Guarde-os em um local seguro.");
						break;
					case "N":
						$("#lbl_novo_cupom_s_titulo").html("NOTA CADASTRADA COM SUCESSO");
						$("#lbl_novo_cupom_s_mensagem").html("Se você ganhar, será necessário apresentar os suas<br>notas cadastradas. Guarde-as em um local seguro.");
						break;
				}
				$("#pnl_novo_cupom_sucesso").fadeIn();
				// gtmEvent({'event': 'CupomCadastrado', 'tipoCupom': tipo});
				//RESET NO FORM
				frm_cupom.reset();
				$('#lbl_cupom_imagem').html('NENHUMA IMAGEM SELECIONADA');
			}
		});
	}
	function removeWinnerMessage(){
		$('.popup_container_winner, #popup_overlay_winner').fadeOut();
	}