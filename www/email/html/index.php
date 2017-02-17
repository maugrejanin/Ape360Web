<?php
include_once(__DIR__ . "/../../server/util/Config.php");
include_once(__DIR__ . "/../../server/util/Consts.php");

if (empty($_REQUEST["h"]) || empty($_REQUEST["a"])) {
	header("Location: " . CONFIG_BASEURL_CLIENT . "Redirect.php?err=notset");
	die;
}
include_once(__DIR__ . "/../../server/util/ComunicadoEnvio.class.php");

$comunicado = new ComunicadoEnvio();
$comunicado->acaoComunicado($_REQUEST["a"], $_REQUEST["h"]);
if (empty($_REQUEST["r"])) {
	header("Location: " . CONFIG_BASEURL_CLIENT);
}
else if ($_REQUEST["r"] == MAIL_CONTEUDO_IMAGEM) {
	header('Content-type: image/gif');
	echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
}
else if ($_REQUEST["r"] == MAIL_CONTEUDO_EMBED) {
	header('Content-type: text/html; charset=utf-8');
	echo "EMBED";
}
else {
	header("Location: " . $_REQUEST["r"]);
}

?>
