<?php

//nessa classe ficarãos os mescânismos que fazem o Validator funcionar.
//No validator haverá apenas as funções de validação utilizadas por este mecanismo
abstract class abtValidator{
	static public function run(array $config, array $data = []){
		$data = $data? $data: $_POST;
		$errors = [];

		foreach ($config as $field => $validation) {
			$validation = (array)$validation;

			$required_key = array_search('required', $validation);//!!!include required_file in exceptions

			if($required_key !== false)
				unset($validation[$required_key]);

			if($required_key !== false and (!isset($data[$field]) or empty($data[$field])))
				$errors[$field] = 'required';
			else{

				if(!isset($data[$field]) || empty($data[$field]))//se o valor é vazio, não pode haver a validação 'required' porque se tivesse teria entrado no 'if' e não no 'else', logo é um campo não obrigatório que veio vazio, isso dispensa qualquer outra validação.
					continue;

				foreach ($validation as $validate => $param) {
					if(is_int($validate)){
						$validate = $param;
						$param = null;
					}

					if(!Validator::$validate($data[$field], $data, $param))
						$errors[$field] = $validate;
				}
			}
		}

		if($errors)
			throw new Ex_Validate($errors);
	}
}

class Validator extends abtValidator{

	static public function in_list($value, array $data, array $param){
		return in_array($value, $param);
	}

	static public function email($value){
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	static public function equal($value, array $data, $param){
		return $value == $param;
	}

	static public function bigger($value, array $data, $param){
		$param = is_string($param)? (isset($data[$param])? $data[$param]: 0): $param;
		return $value > $param;
	}

	static public function date($value){
		$parts = array_filter(explode('-', $value));

		if(sizeof($parts) != 3)
			return false;

		return checkdate($parts[1], $parts[2], $parts[0]);
	}

	static public function file_type($value, array $data, $param){
		$accepted_types = (array)$param;

		$path = $value['name'];
		$ext = mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));

		return in_array($ext, $accepted_types);
	}

	//static public function required_file($value);//!!!implement....

	static public function max($value, array $data, $param){
		return $value <= $param;
	}

	static public function max_file_size(array $value, array $data, $param = null){
		$param = ($param != null ? $param : TAMANHO_MAXIMO_UPLOAD_MB);
		return (($value['size'] / 1024 / 1024) <= $param); 
	}

	static public function min($value, array $data, $param){
		return $value >= $param;
	}

	static public function between($value, array $data, array $param){
		$max = array_pop($param);
		$min = array_pop($param);
		return $this->min($value, $min) and $this->max($value, $max);
	}

	static public function maxlength($value, array $data, $param){
		$length = is_array($value)? sizeof($value): strlen($value);
		return ($length <= (int)$param);
	}

	static public function minlength($value, array $data, $param){
		$length = is_array($value)? sizeof($value): strlen($value);
		return ($length >= (int)$param);
	}

	static public function integ($value){	
		if(is_numeric($value))
			return is_integer($value+0);

		return false;
	}

	static public function decimal($value){
		if(is_numeric($value))
			return is_double($value+0);

		return false;
	}

	static public function uf($value){
		return in_array(strtolower($value), [
			'ac','al','am','ap','ba','ce','df','es','go','ma','mt','ms','mg','pa',
			'pb','pr','pe','pi','rj','rn','ro','rs','rr','sc','se','sp','to'
		]);
	}

	static public function extension($value, array $data, $param){
    	$param = (array)$param;

        $path_info = pathinfo($value['name']);
        if (!isset($path_info['extension'])) 
        	return false;
        $extension = mb_strtolower($path_info['extension']);

        return in_array($extension, $param);
    }

    static public function cpfcnpj($value){
    	if(strlen($value) > 11)
    		return self::cnpj($value);
    	else
    		return self::cpf($value);
    }

	static public function cnpj($value){
		$value = preg_replace('/[^0-9]/', '', (string) $value);
		
		// Valida tamanho
		if (strlen($value) != 14)
			return false;

		// Valida primeiro dígito verificador
		for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
			$soma += $value{$i} * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}

		$resto = $soma % 11;
		if ($value{12} != ($resto < 2 ? 0 : 11 - $resto))
			return false;

		// Valida segundo dígito verificador
		for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
			$soma += $value{$i} * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}

		$resto = $soma % 11;
		return $value{13} == ($resto < 2 ? 0 : 11 - $resto);
	}

	static public function cpf($value){
		$value = preg_replace('/[^0-9]/', '', (string) $value);

		// Valida tamanho
		if (strlen($value) != 11)
			return false;

		// Calcula e confere primeiro dígito verificador
		for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--)
			$soma += $value{$i} * $j;

		$resto = $soma % 11;
		if ($value{9} != ($resto < 2 ? 0 : 11 - $resto))
			return false;

		// Calcula e confere segundo dígito verificador
		for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--)
			$soma += $value{$i} * $j;

		$resto = $soma % 11;
		return $value{10} == ($resto < 2 ? 0 : 11 - $resto);
	}

}