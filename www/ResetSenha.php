<?php

include_once(dirname(__FILE__) . "/server/util/Consts.php");
include_once(dirname(__FILE__) . "/server/util/Config.php");
?>

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
					</div>
					<div class="painel-body">
						<div class="pagina painel-reset-senha" id="pnl_reset_senha">
							<div class="painel-b">
								Digite sua nova senha para continuar
								<form id="frm_reset_senha">
									<input type="hidden" id="ds_hash" name="ds_hash">
									<div class="painel-senha-pontilhada">
										<input required type="password" id="ds_password" name="ds_password" class="input-as input-rs" maxlength="40" placeholder="Digite sua nova senha">
									</div>
								</form>
								<div class="botao-listrado bt-reset-senha" id="btn_reset_senha">Confirmar</div>
								<div class="botao-listrado bt-reset-senha" id="btn_site">Cancelar</div>
							</div>
						</div>
					</div>
				</div>
				<div class="logo-top"></div>
				<div class="footer-text">Certificado de Autorização CAIXA n° 1-2302/2016. Período de compra para participação: 30/11/2016 a 25/12/2016. Período de inscrição no site da Promoção: de 30/11/2016 até as 23h59 do dia 03/01/2017<br>(horário de Brasília). Consulte o regulamento no site www.amigosecretoenahering.com.br. Serão 65 premiados, cada um recebendo 02 iPhone. Totalizando 130 prêmios distribuídos. Imagens meramente ilustrativas.</div>
			</div>
		</div>
		
		<script type="text/javascript" src="js/lib/jquery-1.11.2.min.js"></script>
		<script type="text/javascript" src="js/lib/jquery.mask.min.js"></script>
		<script type="text/javascript" src="js/lib/jquery-migrate-1.2.1.min.js"></script>
		<script type="text/javascript" src="js/lib/jquery.alerts.js"></script>
		<script type="text/javascript" src="js/lib/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/lib/bootstrap-dialog.min.js"></script>
		<script type="text/javascript" src="js/util/Transformer.obj.js"></script>
		<script type="text/javascript" src="js/ResetSenha.js"></script>

		<script type="text/javascript" src="js/util/Util.obj.js"></script>
		<script type="text/javascript" src="js/util/Validator.obj.js"></script>
		<script type="text/javascript" src="js/util/Form.obj.js"></script>
		<script type="text/javascript" src="js/util/Diplomat.obj.js"></script>
		<script type="text/javascript" src="js/util/Dialog.class.js"></script>
		<script type="text/javascript" src="js/util/Debugger.class.js"></script>
		<script type="text/javascript" src="js/util/Loader.obj.js"></script>
		<script type="text/javascript" src="js/util/Php.obj.js"></script>
	</body>
</html>