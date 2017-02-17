<?php

class M_Pessoa extends Model{

	public function __construct(){
		parent::__construct('t_pessoa', 'id_pessoa');
	}

	public function securityInsert(array $data, array $columns = []){
		$treated_data = self::validTreat($data) + $data;
		return $this->insert($treated_data, $columns);
	}

	static public function validTreat(array $data, array $fields = null){
		Validator::run([
			'ds_pessoa' => 'required'
		], $data);

		$ds_pessoa = $data['ds_pessoa'];

		$treat_config = [
			'ds_cpf' => 'absolute_number',
			'ds_rg' => 'absolute_number',
		];

		if ($ds_pessoa == TIPO_PESSOA_FISICA)
			$treat_config += [
				'ds_sexo' => 'uppercase',
				'ds_rg' => 'absolute_number',
				'dt_nascimento' => 'date',
			];

		//-------------------------------------------------------------------------------------

		$valid_config = [
			'ds_telefone' => 'required',
			'ds_email' => ['required', 'email'],
			'ds_nome' => 'required',
		];

		if ($ds_pessoa == TIPO_PESSOA_FISICA)
			$valid_config += [
				'ds_cpf' => ['required', 'cpf'],
				'ds_sexo' => [
					'required',
					'in_list' => [
						'M', 'F'
					]
				],
				'dt_nascimento' => 'date',
			];
		else
			$valid_config += [
				'ds_cpf' => ['required', 'cnpj'],
				'nm_fantasia' => 'required',
			];

		if($fields)
			$valid_config = array_sub($valid_config, $fields);

		$treated_data = Treat::run($treat_config, $data);
		Validator::run($valid_config, $treated_data + $data);

		return $treated_data;
	}

}