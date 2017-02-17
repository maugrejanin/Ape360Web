<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

function shutdown_function(){
    Autoload::register();
    checkPostSizeExceeded();

	if(!empty(error_get_last()))
		Server::treatException( new Ex_Error() );
}

function error_handler($nivel, $message, $file, $line ) {
	throw new ErrorException($message, 0, $nivel, $file, $line);//0 refere-se ao code da exception
}

function exception_handler($e){
	if ( ! $e instanceof Exception) {
		$e = new ErrorException(
            $e->getMessage(),
            $e->getCode(),
            E_ERROR,
            $e->getFile(),
            $e->getLine()
        );
    }

	Server::treatException($e);
}

register_shutdown_function('shutdown_function');
set_error_handler('error_handler');
set_exception_handler('exception_handler');