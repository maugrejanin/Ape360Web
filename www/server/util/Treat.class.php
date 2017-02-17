<?php 

class Treat{

	static private $natural_treat = [
		'string',
		'trim'
	];

	static private $notset_treat = [
		'sn'
	];

	static public function run(array $config, array $data = null){
		$data = !is_null($data)? $data: $_POST;
		$treated = [];
		foreach ($config as $field => $treatment) {
			if(is_int($field)){
				$field = $treatment;
				$treatment = self::$natural_treat;
			}

			if(!isset($data[$field]) && !in_array($treatment, self::$notset_treat))
				continue;

			$treatment = (array)$treatment;

			foreach ($treatment as $validate => $param) {
				if(is_int($validate)){
					$validate = $param;
					$param = [];
				}

				$treated[$field] = $data[$field] = Treat::$validate(isset($data[$field])? $data[$field]: null, $data, $param);
			}
		}
		return $treated;
	}

	static public function naturalTreat(array $data){
		$data = $data? $data: $_POST;
		$config = array_fill_keys(array_keys($data), self::$natural_treat);

		return self::run($config, $data);
	}

	static public function string($value){
		return filter_var($value, FILTER_SANITIZE_STRING);
	}

	static public function sn($value){
		$upper_value = mb_strtoupper($value);
		return ($upper_value == 'S' || $upper_value == 'N')? $upper_value: (self::boolean_var($value)? 'S': 'N');
	}

	static public function boolean_var($value){
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	static public function trim($value){
		return trim($value);
	}

	static public function lowercase($value){
		return strtolower($value);
	}

	static public function uppercase($value){
		return strtoupper($value);
	}

	static public function absolute_number($value){
		return preg_replace('/[^\d]/', '', $value);
	}

	static public function money($value){
		if(is_numeric($value))
			return (double)$value;

		$value = preg_replace("/[^\d\,]/", "", $value);	
		return (double)str_replace(",", ".", $value);
	}

	static public function sanitize($value){
		return filter_var($value, FILTER_SANITIZE_STRING);
	}

	static public function date($value){
		$parts = explode("/", $value);

		if(count($parts) != 3)
			return $value;

		return $parts[2] . "-" . $parts[1] . "-" . $parts[0];
	}

	static public function datetime($value){
		$parts = explode(" ", $value);

		if(count($parts) != 2)
			return self::date($value);

		return self::date($parts[0]) . ' ' . $parts[1];
	}

	static public function own_name($value){
		$not_capitalize = ['de', 'da', 'do', 'das', 'dos', 'e'];

		$formatted = implode(' ', 
			array_map(function($name) use($not_capitalize){
				return in_array($name, $not_capitalize)? mb_strtolower($name): ucfirst(mb_strtolower($name));
			}, explode(' ', $value))
		);
		return $formatted;
	}

}