<?php

/**
* @title Permissões
* @description Tela de concessão de permissões
*/

class S_Permit{

	private $comment_db_fields = [//!!!implement dynamic fields
		'title' => [
			'db_name' => 'nm_permissao',
			'default_value' => NULL
		],
		'description' => [
			'db_name' => 'ds_permissao',
			'default_value' => NULL
		],
		'restriction' => [
			'db_name' => 'tp_restricao',
			'default_value' => 'R'
		]
	];

	public function init(){
		$role_data = $this->getRoleData();
		$profile_list = $this->getProfileList();
		$data_roles = $this->getRoles();
		$data_actions = $this->getActions();

		return compact('data_actions', 'data_roles', 'role_data', 'profile_list');
	}

	private function getActionsAdmin(){
		$data_action = Model::search(
			"SELECT 
				id_permissao,
				ds_titulo,
				ds_permissao,
				tp_restricao,
				SUBSTRING_INDEX(nm_permissao, '.', 1) AS server,
				SUBSTRING_INDEX(nm_permissao, '.', -1) AS action
			FROM t_permissao p");

		return array_map(function(array $actions){
			return array_key_column($actions, 'action');
		}, array_group($data_action, 'server'));
	}

	private function getActions(){
		global $user_log;

		if($user_log->id_usuario_perfil == ID_PROFILE_TECNICO)
			return $this->getActionsAdmin();

		$data_action = Model::search(
			"SELECT 
				id_permissao,
				ds_titulo,
				ds_permissao,
				tp_restricao,
				SUBSTRING_INDEX(nm_permissao, '.', 1) AS server,
				SUBSTRING_INDEX(nm_permissao, '.', -1) AS action
			FROM t_permissao p
			WHERE 
				tp_restricao <> :tech_restriction AND (
					SELECT p2.id_permissao FROM t_permissao p2 
					WHERE 
					p2.nm_permissao = CONCAT(SUBSTRING_INDEX(p.nm_permissao, :action_separator, 1), :action_separator, SUBSTRING_INDEX(p.nm_permissao, :action_separator, 1)) AND
					p2.tp_restricao = :tech_restriction
				) IS NULL", [
				':tech_restriction' => TYPE_RESTRICT_ACTION_TECH,
				':action_separator' => ACTION_SERVER_CHAR_SEPARATOR
			]);

		return array_map(function(array $actions){
			return array_key_column($actions, 'action');
		}, array_group($data_action, 'server'));
	}

	private function getRoles(){
		return Model::search(
			"SELECT id_role, nm_role, ds_role FROM t_role"
		);
	}

	private function getRoleData(){
		return Model::search(
			"SELECT nm_role, ds_role, id_role FROM t_role"
		);
	}

	private function getProfileList(){
		return array_column(Model::search(
			"SELECT id_usuario_perfil, nm_usuario_perfil FROM t_usuario_perfil"
		), 'nm_usuario_perfil', 'id_usuario_perfil');
	}

	/**
	* @title Delegar permissões
	* @description Adiciona e remove permissões das roles existentes
	* @restriction F
	*/

	public function persistActions(){
		extract(require_post('id_role', 'id_permissoes'));//$id_role, $id_permissoes

		global $mypdo;

		return $mypdo->transaction(function() use($id_role, $id_permissoes) {
			Model::exec("DELETE FROM t_permissao_role WHERE id_role = ?", [$id_role]);

			$m_role_permission = new Model('t_permissao_role');
			$data_insert = [];

			foreach ($id_permissoes as $id_permissao => $ic_concedida)
				if(Treat::sn($ic_concedida) == 'S')
					array_push($data_insert, compact('id_permissao', 'id_role'));

			return $m_role_permission->multInsert($data_insert);
		});
	}

	/**
	* @title Delegar roles
	* @description Adiciona e remove roles dos perfis existentes
	* @restriction T
	*/

	public function persistRoles(){
		extract(require_post('id_role_profile', 'id_usuario_perfil'));//$id_role_profile, $id_usuario_perfil

		$m_profile_role = new Model('t_role_perfil');
		$data_insert = [];

		foreach ($id_role_profile as $id_role => $ic_concedida)
			if(Treat::sn($ic_concedida) == 'S')
				array_push($data_insert, compact('id_usuario_perfil', 'id_role'));

		return $m_profile_role->multInsert($data_insert);
	}

	/**
	* @title Visualizar roles
	* @description Apresenta as permissões atreladas a cada role existente
	* @restriction A
	*/

	public function getRoleActions($id_role){
		$role_actions = Model::search("
			SELECT pr.id_permissao FROM t_permissao_role pr WHERE pr.id_role = ?
		", [$id_role]);

		if($role_actions)
			$id_permissoes = array_combine(
				array_column($role_actions, 'id_permissao'), array_fill(0, sizeof($role_actions), true)
			);
		else
			$id_permissoes = [];

		return compact('id_permissoes');
	}

	/**
	* @title Visualizar permissões de perfis
	* @description Apresenta as roles atreladas a cada perfil existente
	*/

	public function getProfileRoles($id_usuario_perfil){
		$profile_roles = Model::search(
			"SELECT r.id_role FROM t_role r
			INNER JOIN t_role_perfil rp ON r.id_role = rp.id_role
			WHERE rp.id_usuario_perfil = ?", [$id_usuario_perfil]
		);

		if($profile_roles){
			$id_role_profile = array_combine(array_column($profile_roles, 'id_role'), array_fill(0, sizeof($profile_roles), true));
		}else
			$id_role_profile = [];

		return compact('id_role_profile');
	}

	/**
	* @title Criar roles
	* @description Criação de novas roles
	*/

	public function createRole(){
		Validator::run([
			'nm_role' => 'required',
			'ds_role' => 'required'
		]);

		$m_role = new Model('t_role');
		$data_insert = array_sub($_POST, ['nm_role', 'ds_role']);

		return $m_role->insert($data_insert);
	}

	/**
	* @title Criar perfis
	* @description Criação de novos perfis de acesso
	*/

	public function createProfile($nm_usuario_perfil = null){
		if(!$nm_usuario_perfil)
			extract(require_post('nm_usuario_perfil'));//$nm_usuario_perfil

		$m_profile = new Model('t_usuario_perfil');
		return $m_profile->insert(compact('nm_usuario_perfil'));
	}

	/**
	* @title Criar perfis
	* @description Criação de novos perfis de acesso
	* @restriction T
	*/

	public function persistActionsDB(){
		global $mypdo;

		$local_access_options = $this->getLocalAccessOptions();
		$permission_model = new Model('t_permissao', 'id_permissao');

		return $mypdo->transaction(function() use($permission_model, $local_access_options){

			foreach($local_access_options as $server => $actions)
				foreach($actions as $action => $action_data) {
					foreach ($action_data as $comment_index => $comment_value)
						$data_insert[$this->comment_db_fields[$comment_index]['db_name']] = $comment_value;
					
					$data_insert['nm_permissao'] = mb_strtolower($server . '.' . $action);
					$permission_model->insertUpdate($data_insert);
				}

			return true;
		});
	}

	private function getLocalAccessOptions(){
		$scan = scandir(dirname(__FILE__) . '/');

		$servers = array_values(array_map(
			function($server){
				return substr($server, 0, strpos($server, '.'));
			}, array_filter($scan, function($dir_file){
				return preg_match('/S\_.+\.class\.php/', $dir_file);
			})
		));

		$permissions = [];
		for ($i = 0; $i < sizeof($servers); $i++) { 
			$reflect_class = new ReflectionClass($servers[$i]);
			$methods = $reflect_class->getMethods(ReflectionMethod::IS_PUBLIC);
			$server_name = Permit::getServerName($servers[$i]);

			$comment = $reflect_class->getDocComment();
			$permissions[$server_name][$server_name] = $this->treatComment($comment, $server_name);

			for ($j = 0; $j < sizeof($methods); $j++) { 
				$method = $methods[$j]->name;

				if($method == 'init')
					continue;

				$comment = (new ReflectionMethod($servers[$i] . '::' . $method))->getDocComment();
				$permissions[$server_name][Permit::getActionName($method)] = $this->treatComment($comment, $method);
			}
		}

		return $permissions;
	}

	private function treatComment($comment, $method){
		$default_return = array_combine(array_keys($this->comment_db_fields), array_column($this->comment_db_fields, 'default_value'));
		$default_return['title'] = $method;

		if(!$comment)
			return $default_return;

		preg_match_all('/\* +?@(' . implode('|', array_keys($this->comment_db_fields)) . ') (.+)/', $comment, $matches);

		if(empty($matches))
			return $default_return;

		array_shift($matches);//remove o índice 0 com os matches inteiros, deixando o índice 1(agora 0) com os @... e o ínidice 2(agora 1) com os valores dos respectivos @...

		return array_combine($matches[0], $matches[1]) + $default_return;
	}

}