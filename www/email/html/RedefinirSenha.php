<?php
include_once(__DIR__ . "/../../server/util/Config.php");
include_once(__DIR__ . "/../../server/util/Consts.php");

if (empty($_REQUEST["id_comunicado"]) || empty($_REQUEST["id_usuario"])) {
	header("Location: " . CONFIG_BASEURL_CLIENT);
	die;
}
include_once(__DIR__ . "/../../server/util/ComunicadoEnvio.class.php");

$comunicado = new ComunicadoEnvio();
$user = $comunicado->infoDestinatario($_REQUEST["id_comunicado"], $_REQUEST["id_usuario"]);
$params = $comunicado->infoParametros($_REQUEST["id_comunicado"]);
$data_user = $user[0];

?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta charset="utf-8">
		<meta name = "format-detection" content = "telephone=no" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= CONFIG_WEBSITE_NAME_UTF?></title>
		<link rel="shortcut icon" href="<?= CONFIG_BASEURL_CLIENT?>/favicon.ico">
	</head>
	<body style="margin: 0px auto 0px auto;padding: 0px;width: 100%;background-color: #ffffff;font-family: Arial,Helvetica Neue,Helvetica,sans-serif;text-decoration: none;color: #909090; font-size: 1.5em;">
		<div style="width: 100%;">
			<div style="width: 100%; background: #ffffff;">
				<div style="width: 100%; text-align: center;">
					<img src="<?= CONFIG_BASEURL_CLIENT ?>img/mail_h_01.png" style="max-width: 1000px;width: 100%; height: auto;">
				</div>
			</div>
			<div style="width: 100%; background-color: #ffffff">
				<div style="width: 95%;max-width: 880px;margin: 6px auto; padding: 30px;">
					<div>
						<p><?= mb_strtoupper($data_user['ds_nome']) ?>,</p>
						<p>Para definir uma nova senha, <a href="<?= $params["ds_link"] ?>" target="_blank">clique neste link.</a></p>
						<p>Se preferir, acesse o link abaixo no seu navegador:</p>
						<p><?= $params["ds_link"] ?></p>
						<p>Esperamos te ver em breve!</p>
					</div>
				</div>
			</div>
			<div style="width: 100%; background: #ffffff;">
				<div style="width: 100%; text-align: center;">
					<img src="<?= CONFIG_BASEURL_CLIENT ?>img/mail_f_01.png" style="max-width: 1000px;width: 100%; height: auto;">
				</div>
			</div>
		</div>
	</body>

</html>

<script></script>
