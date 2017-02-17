<?php

class Ex_Error extends ErrorException
{
	public function __construct($message = null, $code = 0, $severity = 0, $filename = null, $lineno = 0, Exception $previous = null){
		$last_error = error_get_last();
		
		if( empty($last_error) ){
			parent::__construct('Not identified generic error');
		}else{
			extract($last_error);//$message, $type, $file, $line
			parent::__construct($message, 0, $type, $file, $line);
		}
	}

}