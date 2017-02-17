<?php
// if (empty($_GET["erkr"])) {
// 	die;
// }
date_default_timezone_set('America/Sao_Paulo');
include_once(dirname(__FILE__) . "/server/util/Consts.php");
include_once(dirname(__FILE__) . "/server/util/Config.php");
?>

<style>
	.linha-numero .cc-lupa {
		height: 28px;
		background-size: contain;
		float: left;
		background-position-x: 10px
	}

	.number-container{
		float: left;
	    text-align: center;
	    width: 108px;
	    line-height: 28px;
	}

	#delete_cupom {
		width: 297px;
		padding: 10px;
		line-height: 20px;
		height: 43px;
		margin-top: -10px;
		margin-left: 420px;
	}

	#frm_cupom{
		margin: 0!important;
	}

	.popup-confirm #popup_panel input[type=button] {
		display: block!important;
		float: left;
		margin: 6px 100px;
		font-weight: bold!important;
		width: 150px!important;
		height: 35px!important;
		background-color: transparent!important;
		font-size: 30px;
	}
</style>

<!DOCTYPE html>
<html lang="pt">
<!--[if IE 9]><html class="no-js ie9"><![endif]-->
<!--[if gt IE 9]><!--><html class="no-js"><!--<![endif]-->
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" >
		<meta charset="utf-8">
		<meta name = "format-detection" content = "telephone=no" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0" />
		
		<meta http-equiv="content-language" content="pt-BR" />
		<title><?= CONFIG_WEBSITE_NAME ?></title>
		<link rel="shortcut icon" href="<?= CONFIG_BASEURL_CLIENT ?>favicon.ico">
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-N8ZSMDX');</script>
		<!-- End Google Tag Manager -->
		<link rel="stylesheet" href="css/lib/bootstrap.min.css">
		<link rel="stylesheet" href="css/lib/bootstrap-dialog.min.css">
		<link rel="stylesheet" href="css/lib/jquery.alerts.css">
		<link rel="stylesheet" href="css/lib/cropper.min.css">
		<link rel="stylesheet" href="css/index.css" type="text/css" media="screen" charset="utf-8">
		<script type="text/javascript" src="js/gtm.js"></script>
		<script type="text/javascript" src="js/util/Globals.js"></script>
	</head>
	<body>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N8ZSMDX"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<div id="loading"><img src="img/loading.gif" alt="Carregando..."></div>
		<div id="wrap">
			<div class="elastic-header"></div>
			<div class="elastic-footer"></div>
			<div class="border-v-left"></div>
			<div class="border-v-right"></div>
			<div class="border-h-top"></div>
			<div class="border-h-bottom"></div>
			<div class="fixed-wrap">
				<div class="painel-header">
				</div>
				<div class="painel-branco">
					<div class="painel-menu">
						<div class="painel-b">
							<div class="menu-item item1" target="pnl_mecanica">Como funciona?</div>
							<div class="menu-item item2" target="pnl_cadastro">Cadastro</div>
							<div class="menu-item item3" target="pnl_cupons">Cupons</div>
							<div class="menu-item item4" target="pnl_regulamento">Regulamento</div>
						</div>
					</div>
					<div class="painel-body">
						<div class="pagina painel-1240 painel-home" id="pnl_home">
							<div class="painel-b">
								<div class="home-texto01">A cada <span class="home-texto01-b">R$ 50,<sup class="home-sup">00</sup></span> em<br>compras, concorra a um<br><span class="home-texto01-b">iPhone 6s 16GB</span> e indique um amigo secreto.</div>
								<div class="home-texto02">São mais de 100 iPhone.</div>
							</div>
						</div>
						<div class="pagina painel-1240 painel-cadastro" id="pnl_cadastro">
							<div class="painel-b">
								<form id="frm_cadastro">
									<!-- YOUR DATA -->

									<input required placeholder="Nome" type="text" class="input-c c-nome" id="ds_nome" name="ds_nome" maxlength="80">
									<input required placeholder="Endereço" type="text" class="input-c c-end" id="ds_endereco" name="ds_endereco" maxlength="255">
									<input required placeholder="Cidade" type="text" class="input-c c-cidade" id="ds_cidade" name="ds_cidade" maxlength="60">
									<input required placeholder="Estado" type="text" class="input-c c-uf uf" id="ds_uf" name="ds_uf" maxlength="2">
									<input placeholder="RG" data-toggle="tooltip" data-html="true" title="Caso você não possua RG, deixe este campo em branco" type="text" class="input-c c-rg rg" id="ds_rg" name="ds_rg" maxlength="20">
									<input required placeholder="CPF" type="text" class="input-c c-cpf cpf" id="ds_cpf" name="ds_cpf" maxlength="20">
									<input required placeholder="Telefone" type="text" class="input-c c-tel phone" minlength="10" id="ds_telefone" name="ds_telefone" maxlength="20">
									<input required placeholder="Celular" type="text" class="input-c c-cel phone" minlength="10" id="ds_celular" name="ds_celular" maxlength="20">
									<input required placeholder="E-mail" type="email" class="input-c c-email" id="ds_email" name="ds_email" maxlength="80">
									<input required placeholder="Sexo" type="text" class="input-c c-sexo genre" id="ds_sexo" name="ds_sexo" maxlength="1">
									<input required placeholder="Data de nascimento" type="text" class="input-c c-nasc date" id="ds_nascimento" name="ds_nascimento" maxlength="10">
									<input required placeholder="Senha" type="password" class="input-c c-senha" id="ds_senha" name="ds_senha" maxlength="30">
									<input required placeholder="Confirmação de senha" type="password" class="input-c c-senha2" id="ds_senha_confirmacao" name="ds_senha_confirmacao" equal="ds_senha" maxlength="30">
									<input type="text" class="input-c c-nome-c" id="ds_nome_cartao" name="ds_nome_cartao" maxlength="40">

									<!-- FRIEND DATA -->

									<input required placeholder="Nome do amigo" type="text" class="input-c c-nome-a" id="ds_nome_amigo" name="ds_nome_amigo" maxlength="80">
									<input required placeholder="Endereço do amigo" type="text" class="input-c c-end-a" id="ds_endereco_amigo" name="ds_endereco_amigo" maxlength="255">
									<input required placeholder="Cidade do amigo" type="text" class="input-c c-cidade-a" id="ds_cidade_amigo" name="ds_cidade_amigo" maxlength="60">
									<input required placeholder="Estado do amigo" type="text" class="input-c c-uf-a uf" id="ds_uf_amigo" name="ds_uf_amigo" maxlength="2">
									<input placeholder="RG do amigo" data-toggle="tooltip" data-html="true" title="Caso seu amigo não possua RG, deixe este campo em branco" type="text" class="input-c c-rg-a rg" id="ds_rg_amigo" name="ds_rg_amigo" maxlength="20">
									<input required placeholder="CPF do amigo" type="text" class="input-c c-cpf-a cpf" id="ds_cpf_amigo" name="ds_cpf_amigo" maxlength="20">
									<input required placeholder="Telefone do amigo" type="text" class="input-c c-tel-a phone" minlength="10" id="ds_telefone_amigo" name="ds_telefone_amigo" maxlength="20">
									<input required placeholder="Celular do amigo" type="text" class="input-c c-cel-a phone" minlength="10" id="ds_celular_amigo" name="ds_celular_amigo" maxlength="20">
									<input required placeholder="E-mail do amigo" type="email" class="input-c c-email-a" id="ds_email_amigo" name="ds_email_amigo" maxlength="80">
									<input required placeholder="Sexo do amigo" type="text" class="input-c c-sexo-a genre" id="ds_sexo_amigo" name="ds_sexo_amigo" maxlength="1">
									<input required placeholder="Data de nascimento do amigo" type="text" class="input-c c-nasc-a date" id="ds_nascimento_amigo" name="ds_nascimento_amigo" maxlength="10">
									<span>&nbsp;</span>
									<button type="button" id="send_cad" class="botao-listrado bt-enviar">Enviar</button>
								</form>
							</div>
						</div>
						<div class="pagina painel-mecanica" id="pnl_mecanica">
							<div class="painel-b">
								<button type="button" id="btn_regulamento" class="botao-listrado bt-regulamento">Regulamento</button>
							</div>
						</div>
						<div class="pagina painel-1240 painel-cupons" id="pnl_cupons">
							<div class="painel-b">
								<div class="painel-avatar">
									<img id="img_avatar" class="img-avatar" onerror="this.src = 'img/avatar/0.jpg'" >
								</div>
								<div class="btn-avatar"></div>
								<div class="painel-info">
									<div class="painel-b">
										<div class="info-label" id="lbl_nome_usuario">Leo Rombesso</div>
										<div class="info-label" id="lbl_local">São Paulo - SP</div>
										<div class="info-label" id="lbl_saldo">Saldo: R$0,00</div>
									</div>
								</div>
								<div class="lista-cupom-titulo lista-ct-1">Data do envio</div>
								<div class="lista-cupom-titulo lista-ct-2">Cupom</div>
								<div class="lista-cupom-titulo lista-ct-3">Valor</div>
								<div class="lista-cupom-titulo lista-ct-4">Número da sorte</div>
								<div class="lista-cupons"><div class="painel-b" id="lst_cupons"></div></div>
								<div class="lista-numeros"><div class="painel-b" id="lst_numeros"></div></div>
								<div class="botao-listrado bt-novo-cupom" id="btn_novo_cupom">UPLOAD DE UM<br>NOVO CUPOM</div>
							</div>
						</div>
						<div class="pagina painel-1240 painel-novo-cupom" id="pnl_novo_cupom">
							<div class="painel-b">
								<div class="frm-cupom-titulo">CADASTRO <span class="lbl_form_cupom_tipo">CUPOM</span></div>
								<div class="frm-cupom-texto cupom-cod">N° <span class="lbl_form_cupom_tipo_de">DO</span> <span class="lbl_form_cupom_tipo">CUPOM</span></div>
								<div class="frm-cupom-texto cupom-data">DATA</div>
								<div class="frm-cupom-texto cupom-valor">VALOR</div>
								<form id="frm_cupom">
									<input required placeholder="N° do cupom" type="text" class="input-cp cp-cod" id="cd_cupom" name="cd_cupom" maxlength="20">
									<input required placeholder="Data" type="text" min="2016-11-30" max="2016-12-25" class="input-cp cp-data date datepicker" id="dt_cupom" name="dt_cupom" maxlength="10">
									<input required placeholder="Valor" type="text" class="input-cp cp-valor" id="vl_cupom" name="vl_cupom" maxlength="15" min="50">
									<input required type="hidden" id="tp_cupom" name="tp_cupom" value="C">
									<input required type="hidden" id="ic_cartao" name="ic_cartao">
									<input required type="file" name="img_cupom" style="display: none" accept="image/jpeg,image/png,image/gif,application/pdf" id="new_image_cupom" placeholder="Imagem do cupom">
									<div class="link-cupom-upload" id="lnk_cupom_upload">CARREGAR FOTO <span class="lbl_form_cupom_tipo_de">DO</span> <span class="lbl_form_cupom_tipo">CUPOM</span></div>
									<div class="label-cupom-imagem" id="lbl_cupom_imagem">NENHUMA IMAGEM SELECIONADA</div>
								</form>
								<div class="link-cupom-duvida" id="lnk_cupom_duvida">COMO CADASTRAR</div>
								<button type="button" id="send_cupom" class="botao-listrado bt-enviar-cupom">Enviar</button>
								<div class="label-cupom-rodape" id="lbl_cupom_rodape"></div>
							</div>
						</div>
						<div class="pagina painel-1240 painel-regulamento" id="pnl_regulamento">
							<div class="regulamentoscroll">
								<?php
								include 'regulamento.html';
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="panel-backdrop" id="pnl_novo_cupom_opcao">
					<div class="novo-cupom-opcao">
						<div class="painel-b">
							<div class="novo-cupom-t t-cupom" id="btn_novo_cupom_c">CUPOM</div>
							<div class="novo-cupom-t t-cfe" id="btn_novo_cupom_f">CF ELETRÔNICO/SAT</div>
							<div class="novo-cupom-t t-nfe" id="btn_novo_cupom_n">NF ELETRÔNICA</div>
							<div class="novo-cupom-opcao-cupom"></div>
							<div class="novo-cupom-opcao-cfe"></div>
							<div class="novo-cupom-opcao-nfe"></div>
							<div class="novo-cupom-opcao-link1" id="lnk_modelo_cupom"></div>
							<div class="novo-cupom-opcao-link2" id="lnk_modelo_cfe"></div>
							<div class="novo-cupom-opcao-link3" id="lnk_modelo_nfe"></div>
						</div>
					</div>
				</div>
				<div class="panel-backdrop" id="pnl_novo_cupom_sucesso">
					<div class="novo-cupom-sucesso">
						<div class="painel-b">
							<div class="novo-cupom-s-fechar" id="btn_novo_cupom_s_fechar"></div>
							<div class="novo-cupom-s-titulo" id="lbl_novo_cupom_s_titulo"></div>
							<div class="novo-cupom-s-mensagem" id="lbl_novo_cupom_s_mensagem"></div>
						</div>
					</div>
				</div>
				<div class="panel-backdrop" id="pnl_novo_cupom_duvida">
					<div id="pnl_cupom_duvida">
						<div class="painel-b">
							<div class="novo-cupom-d-fechar" id="btn_novo_cupom_d_fechar"></div>
						</div>
					</div>
				</div>
				<div class="panel-backdrop" id="pnl_cupom_detalhe">
					<div class="painel-cupom-detalhe">
						<div class="painel-b">
							<div id="extra_message_title_cupom">QUE GEROU ESTE NÚMERO</div>
							<div class="cupom-d-fechar" id="btn_cupom_d_fechar"></div>
							<div class="cupom-d-tipo" id="lbl_cupom_d_tipo"></div>
							<div class="cupom-d-codigo" id="lbl_cupom_d_codigo"></div>
							<div class="cupom-d-data" id="lbl_cupom_d_data"></div>
							<div class="cupom-d-valor" id="lbl_cupom_d_valor"></div>
							<div class="cupom-d-status" id="lbl_cupom_d_status"></div>
						</div>
					</div>
				</div>
				<div class="panel-backdrop" id="pnl_cupom_cartao">
					<div class="painel-cupom-cartao">
						<div class="painel-b">
							<div class="cupom-c-com" id="btn_cupom_com_cartao" vl_opcao="S"></div>
							<div class="cupom-c-sem" id="btn_cupom_sem_cartao" vl_opcao="N"></div>
						</div>
					</div>
				</div>
				<div class="panel-backdrop" id="pnl_alterar_senha">
					<div class="painel-alterar-senha">
						<div class="painel-b">
							<div class="senha-a-fechar" id="btn_senha_a_fechar"></div>
							<form id="frm_alterar_senha">
								<input required type="password" id="falt_senha" name="senha" placeholder="Senha" class="input-as alt-senha login-input">
								<input required type="password" id="falt_novasenha" name="nova_senha" placeholder="Noca senha" class="input-as alt-nova-senha login-input">
								<input required type="password" id="falt_novasenha2" name="nova_senha2" placeholder="Noca senha" class="input-as alt-nova-senha2 login-input">
								<div class="botao-listrado bt-alt-confirmar" id="btn_alt_senha_ok">Confirmar</div>
							</form>
						</div>
					</div>
				</div>
				<div class="panel-backdrop" id="pnl_esqueci_senha">
					<div class="painel-esqueci-senha">
						<div class="painel-b">
							<div class="senha-a-fechar" id="btn_senha_e_fechar"></div>
							<form id="frm_esqueci_senha">
								<input required placeholder="CPF" type="text" class="input-as c-cpf cpf" id="esq_ds_cpf" name="ds_cpf" maxlength="20">
								<input required placeholder="E-mail" type="email" class="input-as c-email" id="esq_ds_email" name="ds_email" maxlength="80">
								<div class="botao-listrado reenviar-senha bt-esq-enviar" id="btn_esq_senha_ok">Enviar</div>
							</form>
						</div>
					</div>
				</div>
				<div class="panel-backdrop" id="pnl_regulamento_aceite">
					<div class="painel-regulamento-aceite">
						<div class="painel-b">
							<div class="regulamento-aceite-fechar" id="btn_regulamento_aceite_fechar"></div>
							<div class="regulamentoscroll">
								<?php
								include 'regulamento.html';
								?>
							</div>
							<div class="botao-listrado btn-regulamento-aceite" id="btn_regulamento_aceite">Aceitar</div>
							<div class="botao-listrado btn-regulamento-recusa" id="btn_regulamento_recusa">Recusar</div>
						</div>
					</div>
				</div>
				<div class="logo-top" target="pnl_home"></div>
				<div class="footer-text">Certificado de Autorização CAIXA n° 1-2302/2016. Período de compra para participação: 30/11/2016 a 25/12/2016. Período de inscrição no site da Promoção: de 30/11/2016 até as 23h59 do dia 03/01/2017<br>(horário de Brasília). Consulte o regulamento no site www.amigosecretoenahering.com.br. Serão 65 premiados, cada um recebendo 02 iPhone. Totalizando 130 prêmios distribuídos. Imagens meramente ilustrativas.</div>
				<div class="fale-conosco" id="btn_fale"><span class="glyphicon glyphicon-bullhorn"></span>&nbsp;<span class="label">Fale Conosco</span></div>
				<div id="pnlLogin" class="painel-login" style="<?= (isset($_SESSION["UsrId"]) ? "display: none;" : "") ?>">
					<form id="frm_login">
						<input required type="email" name="ds_email" id="lgn_ds_email" placeholder="E-mail" class="input-login login-input">
						<input required type="password" name="ds_senha" placeholder="Senha" class="input-senha login-input">
						<div class="botao-header-ok" id="btn_login">OK</div>
					</form>
					<div class="login-cadastro" onclick="activePanel('pnl_cadastro')">CADASTRE-SE</div>
					<div class="login-novasenha" onclick="loadReenvioSenha()">ESQUECEU A SENHA?</div>
				</div>
				<div id="pnlLogado" class="painel-logado" style="<?= (isset($_SESSION["UsrId"]) ? "" : "display: none;") ?>">
					<div class="painel-header-boas-vindas" id="pnl_boas_vindas"></div>
					<div class="botao-header-sair" id="btn_logout">Sair</div>
					<div class="botao-header-senha" id="btn_senha">Trocar senha</div>
				</div>
			</div>

			<?php if(date("Y-m-d H:i:s") > "2017-01-31 00:00:00"){ ?>
				<div id="popup_container" class="popup_container_winner" style="position: fixed; z-index: 99999; padding: 0px; margin: 0px; min-width: 752px; max-width: 752px; top: 223.5px; left: 498.5px;">
					<div id="popup_close" onclick="removeWinnerMessage()"></div>
					<div id="popup_content" style="padding-top: 0; font-weight: bolder; text-align: center; text-transform: uppercase;" class="alert">
						<div style="font-size: 29px; line-height: 35px;">Segue o número da sorte contemplado no sorteio da loteria federal do dia 07/01/2017:</div>
						<div style="font-size: 75px; line-height: 90px;">55573</div>
						<div style="font-size: 22px; line-height: 35px;">Os sorteados serão contatados para a validação da participação, conforme dados cadastrados nesta promoção.</div>
					</div>
				</div>
				<div id="popup_overlay_winner" style="position: fixed; z-index: 99998; top: 0px; left: 0px; width: 100%; height: 900px; background: rgb(0, 0, 0); opacity: 0.5;"></div>
				<script> var show_winner = true; </script>
			<?php }else{ ?>
				<script> var show_winner = false; </script>
			<?php } ?>

		</div>
		
		<script type="text/javascript" src="js/lib/jquery-1.11.2.min.js"></script>
		<script type="text/javascript" src="js/lib/jquery.mask.min.js"></script>
		<script type="text/javascript" src="js/lib/jquery-migrate-1.2.1.min.js"></script>
		<script type="text/javascript" src="js/lib/jquery.alerts.js"></script>
		<script type="text/javascript" src="js/lib/jquery.maskMoney.min.js"></script>
		<script type="text/javascript" src="js/lib/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/lib/bootstrap-dialog.min.js"></script>
		<script type="text/javascript" src="js/lib/cropper.min.js"></script>
		<script type="text/javascript" src="js/index.js"></script>

		<!-- <script type="text/javascript" src="js/util/Navigator.obj.js"></script> -->
		<script type="text/javascript" src="js/util/Util.obj.js"></script>
		<script type="text/javascript" src="js/util/Functions.js"></script>
		<script type="text/javascript" src="js/util/Treat.obj.js"></script>
		<script type="text/javascript" src="js/util/Validator.obj.js"></script>
		<script type="text/javascript" src="js/util/Transformer.obj.js"></script>
		<script type="text/javascript" src="js/util/Form.obj.js"></script>
		<script type="text/javascript" src="js/util/Diplomat.obj.js"></script>
		<script type="text/javascript" src="js/util/Dialog.class.js"></script>
		<script type="text/javascript" src="js/util/Debugger.class.js"></script>
		<script type="text/javascript" src="js/util/Loader.obj.js"></script>
		<script type="text/javascript" src="js/util/Php.obj.js"></script>
		<script type="text/javascript" src="js/util/Crop.class.js"></script>
		<script type="text/javascript" src="js/lib/jquery.maskName.js"></script>
		
		<div id="popup_container_html" class="hidden-html">
			<div id="popup_container" class="crop-container">
				<div id="popup_content" class="alert">
					<div class="popup-container" id="file_container">
						<div id="btn_file_crop">Escolher Imagem</div>
						<div id="popup_close" class="btn-close-crop"></div>
						<input type="file" name="new_image" accept="image/jpeg,image/png,image/gif" style="display: none" id="new_image">
					</div>
					<div class="popup-container" id="img_crop_container">
						<img id="img_crop" src="img/avatar/0.jpg" alt="Picture">
					</div>
					<div class="popup-container" id="preview_container">
						<div id="img_preview"></div>
					</div>
					<div class="popup-container" id="confirm_container">
						<div class="botao-listrado" id="btn_confirm_crop">Confirmar</div>
					</div>
				</div>
			</div>
		</div>

		<div id="delete_popup_container_html" class="hidden-html">
			<div id="popup_container" class="popup-confirm" style="position: fixed; z-index: 99999; padding: 0px; margin: 0px; min-width: 752px; max-width: 752px; top: 229px; left: 388px;"><div id="popup_close" style="visibility: hidden"></div><h1 id="popup_title" style="margin-top: 0">Atenção</h1><div id="popup_content" class="confirm" style="padding: 0 15px;"><div id="popup_message"><span style="font-size: 20px;">Ao excluir este cupom, todos os números da sorte vinculados a ele também serão excluídos. Prossiga apenas se você tiver cadastrado alguma informação errada.<br>Deseja Prosseguir?</div><div id="popup_panel" style="margin: 0"><input type="button" value="&nbsp;OK&nbsp;" id="popup_ok"> <input type="button" value="&nbsp;Cancelar&nbsp;" id="popup_cancel"></div></div></div>
		</div>
	</body>
</html>