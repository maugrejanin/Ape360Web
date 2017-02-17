<?php

class M_Evento extends Model{

	public function __construct(){
		parent::__construct('t_evento', 'id_evento');
	}

	public function safeUpdate(array $data, $primary, array $columns = []){
		$treated_data = self::validTreat($data) + $data;
		return $this->update($treated_data, $primary, $columns);
	}

	static public function validTreat(array $data, array $fields = null){
		$valid_config = [
			'nm_evento' => 'required',
			'ds_evento' => 'required',
			'qt_pax' => ['required', 'integ'],
			'qt_pax_bloqueada' => ['integ'],
		];

		if($fields)
			$valid_config = array_sub($valid_config, $fields);

		Validator::run($valid_config, $data);

		return [];
	}

}