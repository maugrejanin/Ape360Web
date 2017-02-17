<?php

class M_Usuario extends Model{

	public function __construct(){
		parent::__construct('t_usuario', 'id_usuario');
	}

	public function insert(array $data, array $columns = []){
		if($columns and !in_array('ds_pwd_hash', $columns))
			array_push($columns, 'ds_pwd_hash');

		$data['ds_pwd_hash'] = $this->generatePassword(
			isset($data['ds_pwd_hash'])? $data['ds_pwd_hash']: null
		);

		$id_usuario = parent::insert($data, $columns);

		if($id_usuario)
			$this->sendWelcomeEmail();

		return $id_usuario;
	}

	public function securityInsert(array $data, array $columns = []){
		$treated_data = self::validTreat($data) + $data;
		return $this->insert($treated_data, $columns);
	}

	static public function validTreat(array $data, array $fields = null){
		$valid_config = [
			'ds_email' => 'required',
			'id_usuario_perfil' => 'required',
			'id_pessoa' => 'required',
		];

		if($fields)
			$valid_config = array_sub($valid_config, $fields);

		Validator::run($valid_config, $data);

		return [];
	}

	public function findByEmail($email, array $columns = ['*']){
		$str_columns = implode(', ', $columns);
		$query = "SELECT {$str_columns} FROM $this->name WHERE email = ?";

		return Model::search($query, [$email], false);
	}

	public function disable($id_usuario){
		return $this->update([
			'ic_ativo' => 'N'
		], $id_usuario);
	}

	private function generatePassword($password){
		$password = $password? $password: 'WayPortal';//gerar senha padrão
		return password_hash($password, PASSWORD_DEFAULT);
	}

	private function sendWelcomeEmail(){
		return true;
	}

}