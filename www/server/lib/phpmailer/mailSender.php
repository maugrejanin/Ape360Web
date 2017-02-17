<?php
require_once("PHPMailerAutoload.php");
require_once(__DIR__ . "/../Config.php");

$smtpAuth = $config['smtpAuth'];
$smtpServer = $config['smtpServer'];
$smtpUser = $config['smtpUser'];
$smtpPwd = $config['smtpPwd'];
$smtpPort = $config['smtpPort'];
$smtpSecure = $config['smtpSecure'];
$mailTest = $config['mailTest'];
$mailTestTo = $config['mailTestTo'];
$mailTestToName = $config['mailTestToName'];
$mailFrom = $config['mailFrom'];
$mailFromName = $config['mailFromName'];
$mailReplyTo = $config['mailReplyTo'];
$mailReplyToName = $config['mailReplyToName'];
$mailHtml = $config['mailHtml'];
$mailCharset = $config['mailCharset'];
$mensagemErro = "";

function montarListaEmail($emailsDestinatarios, $nomesDestinatarios) {
	$lista = [];
	for ($i = 0; $i < count($emailsDestinatarios); $i++){
		array_push($lista, ["name" => $nomesDestinatarios[$i], 'mail' => $emailsDestinatarios[$i] ]);
	}
	return $lista;
}

function EnviarEmail ($assunto, $urlHtml, $textoHtml, $textoAlternativo, $emailsDestinatarios, $nomesDestinatarios, $emailsBcc, $nomessBcc) {
	$listaDestinatarios = montarListaEmail($emailsDestinatarios, $nomesDestinatarios);
	$listaDestinatariosBcc = montarListaEmail($emailsBcc, $nomessBcc);
	return EnviarEmailLista($assunto, $urlHtml, $textoHtml, $textoAlternativo, $listaDestinatarios, $listaDestinatariosBcc);
}	

function EnviarEmailLista ($assunto, $urlHtml, $textoHtml, $textoAlternativo, $destinatarios, $destinatariosBcc) {
	global $smtpAuth, $smtpServer, $smtpUser, $smtpPwd, $smtpPort, $smtpSecure, $mailFrom, $mailFromName, $mailHtml, $mailCharset, $mailTest, $mailReplyTo, $mailReplyToName, $mensagemErro, $mailTestTo, $mailTestToName;
	$mail = new PHPMailer();
	$mail->setFrom($mailFrom, $mailFromName);
	$mail->addReplyTo($mailReplyTo, $mailReplyToName);
	$mail->IsSMTP();
	$mail->Host = $smtpServer;
	$mail->SMTPAuth = $smtpAuth;
	$mail->Username = $smtpUser;
	$mail->Password = $smtpPwd;
	$mail->SMTPSecure = $smtpSecure;
	$mail->Port = $smtpPort;

	$mail->From = $mailFrom; 
	$mail->FromName = $mailFromName;

	if (!$mailTest) {
		foreach ($destinatarios as $destinatario){
			$mail->AddAddress($destinatario["mail"], $destinatario["name"]);
		}
	
		foreach ($destinatariosBcc as $destinatarioBcc){
			$mail->AddBCC($destinatarioBcc["mail"], $destinatarioBcc["name"]);
		}
	}
	else {
		$mail->AddAddress($mailTestTo, $mailTestToName); //, $nomesDestinatarios[$i]);
	}

	$mail->IsHTML($mailHtml);
	$mail->CharSet = $mailCharset;

	$mail->Subject  = $assunto;
	if (!isset($urlHtml) || empty($urlHtml)) {
		$mail->Body = $textoHtml;
	}
	else {
		$mail->msgHTML(GetEmailTemplate($urlHtml), dirname(__FILE__));
	}
	$mail->AltBody = $textoAlternativo;

	$enviado = $mail->send();
	$mensagemErro = "no errors";
	if (!$enviado) {
		$mensagemErro = $mail->ErrorInfo;
	}
	
	$mail->ClearAllRecipients();
	$mail->ClearAttachments();
	
	return ["error_message" => $mensagemErro, "status" => $enviado? "success" : "error"];
	
}

function GetEmailTemplate($urlHtml) {
	$data = "";
	if ( function_exists("curl_init") ) {
		$ch = curl_init($urlHtml);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_HEADER, true);
	    
	    $data = curl_exec($ch);
	    $raw_headers = substr($data, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
	    $headers = preg_split("/[\n\r]+/", trim($raw_headers));
	    $data = substr($data, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
	    curl_close($ch);
    }
    return $data;
}

?>