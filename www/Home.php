<?php

	include_once(dirname(__FILE__) . '/server/util/Header.php');

	try{
		Permit::setTokenCredentials();
	}catch(Ex_Authentication $ae){
		header('Location: Login.php');
	}

	define('INITIAL_PAGE', Request::getInitialPage());

?>

<!DOCTYPE html>
<html lang="pt">
	<head>
		<!-- META META META META META -->
		<script> 
			var initial_page = '<?= INITIAL_PAGE ?>',
				user_log = <?= json_encode($user_log) ?>;
		</script>
		<meta http-equiv="cache-control" content="max-age=0" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="0" />
		<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
		<meta http-equiv="pragma" content="no-cache" />

		<meta http-equiv="content-type" content="text/html; charset=utf-8" >
		<meta charset="utf-8">
		<meta name="format-detection" content="telephone=no" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0" />
		<meta http-equiv="content-language" content="pt-BR" />

		<!-- META META META META META -->

		<!-- CSS CSS CSS CSS CSS CSS CSS -->

		<link rel="stylesheet" href="css/lib/bootstrap.min.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/lib/bootstrap-table.min.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/lib/bootstrap-select.min.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/lib/bootstrap-switch.min.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/lib/bootstrap-datepicker.min.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/lib/bootstrap-dialog.min.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/Master.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/Admin.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/Home.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/Menu.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/Table.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/Form.css" type="text/css" charset="utf-8">
		<link rel="stylesheet" href="css/Dialog.css" type="text/css" charset="utf-8">

	    <!-- CSS CSS CSS CSS CSS CSS CSS -->

		<title><?= CONFIG_WEBSITE_NAME ?></title>
		<link rel="shortcut icon" href="<?= CONFIG_BASEURL_CLIENT ?>img/favicon.ico">
	</head>
	<body>

		<!-- WRAP WRAP WRAP WRAP WRAP WRAP -->

			<div id="home_loading" class="loading"></div>

		    <div id="wrapper">

		    	<?php //include_once(dirname(__FILE__) . "/template/Menu.html"); ?>

		    	<?php include_once(dirname(__FILE__) . "/template/Container.php"); ?>

		    	<?php include_once(dirname(__FILE__) . "/template/Footer.html"); ?>

		    </div>

		<!-- WRAP WRAP WRAP WRAP WRAP WRAP -->

		<!-- SCRIPT SCRIPT SCRIPT SCRIPT SCRIPT SCRIPT SCRIPT -->

		<script type="text/javascript" src="js/lib/jquery-1.11.2.min.js"></script>
		<script type="text/javascript" src="js/lib/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/util/Globals.js"></script>
		<script type="text/javascript" src="js/util/Debugger.class.js"></script>
		<script type="text/javascript" src="js/util/Functions.js"></script>
		<script type="text/javascript" src="js/util/Loader.obj.js"></script>
		<script type="text/javascript" src="js/Home.js"></script>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>

		<!-- SCRIPT SCRIPT SCRIPT SCRIPT SCRIPT SCRIPT SCRIPT -->

		<!-- Conteúdo abaixo da borda -->

	</body>
</html>

<script type="text/javascript">
	debug = Debugger();
	google.load("visualization", "1", {packages:['corechart', 'timeline'], 'language': 'pt-BR'});

	currentController = new Home("<?= INITIAL_PAGE ?>", "<?= CONFIG_BASEURL ?>")
	currentController.init();
</script>