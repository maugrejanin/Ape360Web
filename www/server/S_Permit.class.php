<?php

/**
* @title Permissões
* @description Tela de concessão de permissões
*/

class S_Permit{

	public function init(){
		global $user_log;

		$role_list = $this->getRoleList();
		$profile_list = $this->getProfileList();
		$data_roles = $this->getRoles();
		$data_actions = $this->getActions();

		return compact('data_actions', 'data_roles', 'role_list', 'profile_list');
	}

	private function getActions(){
		$data_action = Model::search(
			"SELECT 
				id_permissao,
				ds_titulo,
				ds_permissao,
				SUBSTRING_INDEX(nm_permissao, '.', 1) AS server,
				SUBSTRING_INDEX(nm_permissao, '.', -1) AS action
			FROM permissao");

		return array_map(function(array $actions){
			return array_key_column($actions, 'action');
		}, array_group($data_action, 'server'));
	}

	private function getRoles(){
		return Model::search(
			"SELECT id_role, nm_role, ds_role FROM role"
		);
	}

	public function getUserPermits($id_usuario){
		$data_permit = Model::search("
			SELECT DISTINCT
				p.id_permissao AS profile_permission,
				CASE
					WHEN
						pu.id_permissao IS NULL -- Se for NULL, a permissão de usuário corresponde a do perfil
					THEN
						p.id_permissao
					ELSE 0 -- Se não, tem que ser necessariamente uma restrição: ic_concessao = N. Ver persistUserActions
				END AS user_permission
			FROM
				permissao p
					INNER JOIN
				permissao_role pr ON p.id_permissao = pr.id_permissao
					INNER JOIN
				role_perfil rp ON rp.id_role = pr.id_role
					INNER JOIN
				usuario u ON u.id_usuario_perfil = rp.id_usuario_perfil
					LEFT JOIN
				permissao_usuario pu ON pu.id_permissao = p.id_permissao
					AND u.id_usuario = pu.id_usuario
			WHERE
				u.id_usuario = :id_usuario
		UNION 
			SELECT 
				0 AS profile_permission,
				pu.id_permissao
			FROM permissao_usuario pu
			WHERE
				pu.id_usuario = :id_usuario AND
				pu.ic_concessao = 'S'
		", [
			':id_usuario' => $id_usuario
		]);

		$profile_permission = [];
		$user_permission = [];

		for ($i = 0; $i < sizeof($data_permit); $i++) { 
			if($data_permit[$i]['user_permission'] != 0)
				$user_permission[$data_permit[$i]['user_permission']] = true;

			if($data_permit[$i]['profile_permission'] != 0)
				$profile_permission[$data_permit[$i]['profile_permission']] = true;
		}

		return compact('user_permission', 'profile_permission');
	}

	/**
	* @title Obter lista de permissões do perfil de um usuário
	*/

	private function getUserProfilePermits($id_usuario){
		$data_permit = Model::search("
			SELECT DISTINCT
				p.id_permissao
			FROM
				permissao p
					INNER JOIN
				permissao_role pr ON p.id_permissao = pr.id_permissao
					INNER JOIN
				role_perfil rp ON rp.id_role = pr.id_role
					INNER JOIN
				usuario u ON u.id_usuario_perfil = rp.id_usuario_perfil
			WHERE
				u.id_usuario = ?
		", [$id_usuario]);

		return array_column($data_permit, 'id_permissao');
	}

	private function getRoleList(){
		return array_column(Model::search(
			"SELECT nm_role, id_role FROM role"
		), 'nm_role', 'id_role');
	}

	public function getUserList(){
		$pagdata = Filter::pagination( 
			"SELECT 
				up.nm_perfil,
				u.id_usuario,
				p.ds_nome
			FROM usuario u
			INNER JOIN pessoa p ON p.id_pessoa = u.id_pessoa
			INNER JOIN usuario_perfil up ON up.id_usuario_perfil = u.id_usuario_perfil
			"
		);

		Server::sendDataToClient($pagdata);
	}

	private function getProfileList(){
		return array_column(Model::search(
			"SELECT id_usuario_perfil, nm_perfil FROM usuario_perfil"
		), 'nm_perfil', 'id_usuario_perfil');
	}

	/**
	* @title Delegar permissões a usuários
	* @description Adiciona e remove permissões das dos usuários do sistema, sobrescrevendo as permissões atreladas ao seu perfil
	*/

	public function persistUserActions(){
		extract(require_post('id_user_permissions', 'id_usuario'));//$id_user_permissions, $id_usuario
		global $mypdo;

		$id_user_permissions = array_keys($id_user_permissions);

		//--------------------------------------------------------
		//Dividindo as permissões removidas e as inseridas

		$user_profile_permits = $this->getUserProfilePermits($id_usuario);

		$remove_permissions = array_values(array_diff($user_profile_permits, $id_user_permissions));
		$insert_permissions = array_values(array_diff($id_user_permissions, $user_profile_permits));

		//--------------------------------------------------------
		//Preparando dados para inserção

		$data_insert = array_fill(0, sizeof($insert_permissions) + sizeof($remove_permissions), compact('id_usuario'));

		for ($i = 0; $i < sizeof($remove_permissions); $i++) { 
			$data_insert[$i]['id_permissao'] = $remove_permissions[$i];
			$data_insert[$i]['ic_concessao'] = 'N';
		}

		for ($j = sizeof($remove_permissions); $j < sizeof($data_insert); $j++) { 
			$data_insert[$j]['id_permissao'] = $insert_permissions[$j - $i];
			$data_insert[$j]['ic_concessao'] = 'S';
		}

		//--------------------------------------------------------

		return $mypdo->transaction(function() use($data_insert, $id_usuario){
			//--------------------------------------------------------
			// O primeiro passo é remover todas as permissões de usuário do usuário $id_usuario

			$user_permission_model = new Model('permissao_usuario');

			Model::exec(
				"DELETE FROM permissao_usuario 
				WHERE id_usuario = ?", [$id_usuario]
			);

			//--------------------------------------------------------
			//Insert de todas as permissões, removidas e inseridas

			return $user_permission_model->multInsert($data_insert);
		});
	}

	/**
	* @title Delegar permissões a roles
	* @description Adiciona e remove permissões das roles existentes
	*/

	public function persistActions(){
		extract(require_post('id_role', 'id_permissoes'));//$id_role, $id_permissoes

		global $mypdo;

		return $mypdo->transaction(function() use($id_role, $id_permissoes) {
			Model::exec("DELETE FROM permissao_role WHERE id_role = ?", [$id_role]);

			$m_role_permission = new Model('permissao_role');
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
	*/

	public function persistRoles(){
		extract(require_post('id_role_profile', 'id_usuario_perfil'));//$id_role_profile, $id_usuario_perfil

		$m_profile_role = new Model('role_perfil');
		$data_insert = [];

		foreach ($id_role_profile as $id_role => $ic_concedida)
			if(Treat::sn($ic_concedida) == 'S')
				array_push($data_insert, compact('id_usuario_perfil', 'id_role'));

		return $m_profile_role->multInsert($data_insert);
	}

	/**
	* @title Visualizar roles
	* @description Apresenta as permissões atreladas a cada role existente
	*/

	public function getRoleActions($id_role){
		$role_actions = Model::search("
			SELECT pr.id_permissao FROM permissao_role pr WHERE pr.id_role = ?
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
			"SELECT r.id_role FROM role r
			INNER JOIN role_perfil rp ON r.id_role = rp.id_role
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
			'nm_role' => 'required'
		]);

		$m_role = new Model('role');
		$data_insert = array_sub($_POST, ['nm_role', 'ds_role']);

		return $m_role->insert($data_insert);
	}

	/**
	* @title Criar perfis
	* @description Criação de novos perfis de acesso
	*/

	public function createProfile($nm_perfil = null){
		if(!$nm_perfil)
			extract(require_post('nm_perfil'));//$nm_perfil

		$m_profile = new Model('usuario_perfil');
		return $m_profile->insert(compact('nm_perfil'));
	}

	public function persistActionsDB(){
		global $mypdo;

		$local_access_options = $this->getLocalAccessOptions();
		$permission_model = new Model('permissao', 'id_permissao');

		unset($local_access_options[__CLASS__][__METHOD__]);

		return $mypdo->transaction(function() use($permission_model, $local_access_options){

			foreach($local_access_options as $server => $actions){
				foreach($actions as $action => $action_data) {
					$data_insert['ds_titulo'] = $action_data['title'];
					$data_insert['ds_permissao'] = $action_data['description'];
					$data_insert['nm_permissao'] = mb_strtolower($server . '.' . $action);

					$permission_model->insertUpdate($data_insert);
				}
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
			$server_name = substr($servers[$i], 2);

			$comment = $reflect_class->getDocComment();
			$permissions[$server_name][$server_name] = $this->treatComment($comment, $server_name);

			for ($j = 0; $j < sizeof($methods); $j++) { 
				$method = $methods[$j]->name;

				if($method == 'init')
					continue;

				$comment = (new ReflectionMethod($servers[$i] . '::' . $method))->getDocComment();
				$permissions[$server_name][$method] = $this->treatComment($comment, $method);
			}
		}

		return $permissions;
	}

	private function treatComment($comment, $method){
		$default_return = [
			'title' => $method,
			'description' => NULL
		];

		if(!$comment)
			return $default_return;

		preg_match_all('/\* +?@(title|description) (.+)/', $comment, $matches);

		if(empty($matches))
			return $default_return;

		$return['title'] = isset($matches[2][0])? $matches[2][0]: $default_return['title'];
		$return['description'] = isset($matches[2][1])? $matches[2][1]: $default_return['description'];

		return $return;
	}

}