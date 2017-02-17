<?php

class Ex_Validate extends Exception{
	
	public function __construct($message = null, $code = 0, Exception $previous = null){
		if(is_array($message))
			$message = json_encode($message);

		parent::__construct($message, $code, $previous);
	}

	static public function throwTreatPDOException(PDOException $pdoe){
		$sql_state = $pdoe->errorInfo[1];

		switch ($sql_state) {
			case SQLSTATE_DUPLICATE_KEY:
				$unique_field = substr(explode(" for key '", $pdoe->getMessage())[1], 0, -8);
				throw new Ex_Validate([
					$unique_field => 'unique'
				]);
			break;

			case SQLSTATE_TRUNCATE_COLUMN:
				$columns = [];
				preg_match('/long for column \'(.*)\' at row/i', $pdoe->getMessage(), $columns);

				if(isset($columns[1]))
					throw new Ex_Validate([
						$columns[1] => 'toolong'
					]);
				else
					throw new Ex_User('Você está excedendo o limite de algum campo não identificado do formulário');
			break;
			
			default: throw $pdoe;
		}

		
	}
	
}