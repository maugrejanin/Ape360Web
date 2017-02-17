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
						<p>Recebemos o seu cupom com sucesso e já efetuamos o seu processamento.</b></p>
						<p>Os dados que você cadastrou foram os seguintes:</b></p>
						<p>
							<ul>
								<li>Número do cupom: <b><?= $params["cd_cupom"] ?></b></li>
								<li>Data da compra: <b><?= $params["dt_compra"] ?></b></li>
								<li>Valor da compra: <b><?= $params["vl_compra"] ?></b></li>
								<li>Compra com cartão Hering: <b><?= $params["ds_ic_cartao"] ?></b></li>
								<li>Quantidade de números da sorte gerados: <b><?= $params["qt_numeros"] ?></b></li>
							</ul>
						</p>
						<p>A cada R$<?= VALOR_NUMERO_DA_SORTE ?> em compras você ganha um número da sorte para concorrer a <b>um iPhone 6s 16GB</b>!</b></p>
						<p>Você poderá consultar a qualquer momento seus cupons e números da sorte. Basta acessar o site e fazer seu login.</p>
						<p><b>LEMBRE-SE:</b> Se você ganhar, será necessário apresentar os seus cupons cadastrados. Guarde-os em um local seguro.</p>
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
