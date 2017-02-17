<?php

class Ex_Detail extends Exception{
	
	public function __construct($message = null, $code = 0, Exception $previous = null){
		if(is_array($message))
			$message = json_encode($message);
		elseif(is_object($message))
			$message = json_encode(get_object_vars($message));
		else
			trigger_error(__CLASS__ . " requer um array ou objeto como primeiro parâmetro de seu construtor.");
		
		parent::__construct($message, $code, $previous);
	}

}