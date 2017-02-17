<?php

date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
if(session_status() == PHP_SESSION_NONE)
	session_start();

include_once(dirname(__FILE__) . '/Config.php');
include_once(dirname(__FILE__) . '/Consts.php');
include_once(dirname(__FILE__) . '/AutoLoad.class.php');

AutoLoad::register();

// --------------------------------------------------
// Arquivos não relativos a classes -> não são chamados pelo Autoload

include_once(dirname(__FILE__) . '/ErrorHandler.php');
include_once(dirname(__FILE__) . '/MyPDO.class.php');
include_once(dirname(__FILE__) . '/Functions.php');
// --------------------------------------------------

new SessionManager();//register session manager

$user_log;
$__post = [];