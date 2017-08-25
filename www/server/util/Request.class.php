<?php

class Request{

	static public function getInitialPage(){//aqui retorna-se a página inicial de acordo com algum parâmetro ou perfil de usuário...
		return 'Permit';
	}

    static public function getUrl(){
        return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    static public function isAjax(){
        return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) and ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') );
    }

    static public function interpretUrl($url){
		global $__post;

        extract(parse_url($url));//scheme, host, port, user, pass, path, query, fragment
        //var_dump(CONFIG_SERVER_PATH, $path);exit;
		$path = str_replace(CONFIG_SERVER_PATH, '', $path);

		if(isset($query))
        	parse_str($query, $__post);
        else
        	$__post = $_POST;

        $callback = isset($__post['callback'])? $__post['callback']: false;

        $parts = array_values(array_filter( explode('/', $path) ));
        $get = (count($parts) > 2)? array_slice($parts, 2) : [];
        $controller = basename($parts[0], '.php');
        
        if(!isset($parts[1]) || empty($parts[1]))
            $action = isset($_POST['__action'])? $_POST['__action']: CONFIG_ACTION_DEFAULT;
        else
            $action = $parts[1];
        
        return compact('controller', 'action', 'get', 'callback');
    }
}

?>