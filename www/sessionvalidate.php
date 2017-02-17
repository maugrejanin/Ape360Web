<?php
	include_once 'config.php';
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
        
	//die;
	if (isset($_SESSION['UsrActivity']) && (time() - $_SESSION['UsrActivity'] > 1200)) {
		session_unset();
		session_destroy();
	}
	if (!empty($_POST['hdnSair'])){
		session_unset();
		session_destroy();
    }
	if (!isset($_SESSION['Ambiente']) || $_SESSION['Ambiente'] != $config['ChaveSite']) {
		if (session_status() != PHP_SESSION_NONE) {
			session_unset();
			session_destroy();
		}
	}
        
	$_SESSION['UsrActivity'] = time();
	
?>