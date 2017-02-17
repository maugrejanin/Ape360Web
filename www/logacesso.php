<?php
	header('Content-type: text/html; charset=utf-8');
	include_once 'myconn.php';
	if (!empty($_POST["pagina"])) {
		try{
			$now = new DateTime('now', new DateTimeZone( 'America/Sao_Paulo' ) );
			$strNow = $now->format('Y-m-d H:i:s');
			$myCommand = $myConn->prepare('insert into t_log_acesso_site (dt_log, ds_pagina, ds_ip) values (?, ?, ?)');
			$myCommand->bindValue(1, $strNow);
			$myCommand->bindValue(2, $_POST["pagina"]);
			$myCommand->bindValue(3, $_SERVER['REMOTE_ADDR']);
			$myCommand->execute();
		}
		catch(Exception $ex){
		}
	}
?>