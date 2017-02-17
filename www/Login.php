<?php
include_once(dirname(__FILE__) . "/server/util/Consts.php");
include_once(dirname(__FILE__) . "/server/util/Config.php");
?>
<!DOCTYPE html>
<html lang="pt">
	<head>
		<script type="text/javascript" src="js/lib/jquery-1.11.2.min.js"></script>
		<script type="text/javascript" src="js/lib/jquery-migrate-1.2.1.min.js"></script>
		<script type="text/javascript" src="js/lib/jquery.alerts.js"></script>
		<script type="text/javascript" src="js/lib/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/lib/bootstrap-dialog.min.js"></script>

		<script type="text/javascript" src="js/util/Functions.js"></script>
		<script type="text/javascript" src="js/util/Form.obj.js"></script>
		<script type="text/javascript" src="js/util/Diplomat.obj.js"></script>
		<script type="text/javascript" src="js/util/Dialog.class.js"></script>
		<script type="text/javascript" src="js/util/Debugger.class.js"></script>
		<script type="text/javascript" src="js/util/Loader.obj.js"></script>
		<script type="text/javascript" src="js/util/Php.obj.js"></script>

		<script type="text/javascript" src="js/util/Globals.js"></script>

		<script type="text/javascript" src="js/Login.js"></script>

		<link rel="stylesheet" href="css/lib/bootstrap.min.css" type="text/css" media="screen" charset="utf-8">
		<link rel="stylesheet" href="css/lib/bootstrap-dialog.min.css" type="text/css" media="screen" charset="utf-8">
		<link rel="stylesheet" href="css/lib/jquery.alerts.css">
		<link rel="stylesheet" href="css/Master.css" type="text/css" media="screen" charset="utf-8">
		<link rel="stylesheet" href="css/Login.css" type="text/css" media="screen" charset="utf-8">
		<link rel="shortcut icon" href="<?= CONFIG_BASEURL_CLIENT ?>img/favicon.ico">
	</head>

	<body>
		<div class="BgHome">
			<div id="pnlLoginMaster" class="CenterAll">
				<div class="LogoClienteCentro"></div>
				<form id="frmLogin">
					<div class="ContainerLogin">
						<div class="PainelLogin">
							<div class="CampoLogin"><input type="text" id="user" name="user" required maxlength="80" placeholder="Usuário"></div>
							<div class="CampoSenha"><input type="password" id="password" name="password" required maxlength="40" placeholder="Senha"></div>
						</div>
						<div id="btnEntrar" class="BotaoEntrar login-btn">Entrar</div>
						<div id="btnSite" class="BotaoSite login-btn">Voltar ao site</div>
					</div>
				</form>
			</div>
		</div>

		<script>
			debug = Debugger();
			(new Login).init();
		</script>
	</body>
</html>