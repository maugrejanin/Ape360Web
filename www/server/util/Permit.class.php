<?php

final class Permit{

	static private 
		$token,
	 	$free_action = [
			'S_Login' => ['login', 'loginFrontEnd', 'logout'],
			'S_FrontEnd' => '*',
			'S_Servico' => ['start'],
			'S_Cadastro' => ['insert']
		], 
		$date_expire_action = [//format: Y-m-d H:i:s
			'S_FrontEnd' => ['cadastrar' => '2017-01-04'],
			'S_Cupom' => ['insertCupons' => '2017-01-04']
		];

	static public function setTokenCredentials($throwException = true){//usada na Home
		global $user_log, $__post;

		if(!empty($_SESSION[TOKEN_INDEX]))
			self::$token = $_SESSION[TOKEN_INDEX];
		else if(!empty($_POST[TOKEN_INDEX]))
			self::$token = $_POST[TOKEN_INDEX];
		else {
			if ($throwException === true)
				throw new Ex_Authentication();
			else {
				return;
			}
		}

		$user_log = (object)JWT::decode(self::$token, CONFIG_SALT_TOKEN);
	}

	public static function verify($controller_class_name, $action_method_name, array $args){
		self::verifyValidAction($controller_class_name, $action_method_name);
		self::verifyJWT($controller_class_name, $action_method_name);
		self::verifyExpireDate($controller_class_name, $action_method_name);
		self::verifyUserPermission($controller_class_name, $action_method_name);
	}

	private static function verifyValidAction($controller_class_name, $action_method_name){
		$actions = array_map('mb_strtolower', get_class_methods($controller_class_name));

		if(!in_array(mb_strtolower($action_method_name), $actions)){
			Server::sendIntractableError('Serviço indisponível: ' . mb_strtolower($action_method_name));
		}
	}

	private static function verifyExpireDate($controller_class_name, $action_method_name){
		if (
			isset(self::$date_expire_action[$controller_class_name]) and 
			isset(self::$date_expire_action[$controller_class_name][$action_method_name]) and 
			strtotime(date('Y-m-d H:i:s')) > strtotime(self::$date_expire_action[$controller_class_name][$action_method_name])
		)
			throw new Ex_User('Essa funcionalidade está desabilitada');

		return true;
	}

	private static function verifyJWT($controller_class_name, $action_method_name){
		if(!self::isFreeAction($controller_class_name, $action_method_name))
			self::setTokenCredentials();
	}

	private static function isFreeAction($controller_class_name, $action_method_name){
		return (
			isset(self::$free_action[$controller_class_name]) && (
				self::$free_action[$controller_class_name] == "*" || 
				in_array($action_method_name, self::$free_action[$controller_class_name])
			)
		);
	}

	private static function verifyUserPermission($controller_class_name, $action_method_name){
		global $user_log;

		if(
			self::isFreeAction($controller_class_name, $action_method_name) || 
			(int)$user_log->id_usuario_perfil === ID_PROFILE_TECNICO // Usuários técnicos não têm restrições atreladas a seu perfil
		)
			return true;

		$server_name = substr($controller_class_name, 2);

		$permission_name = self::getActionName(
			$server_name,
			mb_strtolower($action_method_name) == INIT_ACTION? $server_name: $action_method_name 
		);

		$has_permit = (boolean)Model::search("
			SELECT 
			    p.id_permissao
			FROM
			   permissao p
			        INNER JOIN
			   permissao_role pr ON p.id_permissao = pr.id_permissao
			        INNER JOIN
			   role_perfil rp ON rp.id_role = pr.id_role
			        INNER JOIN
			   usuario u ON u.id_usuario_perfil = rp.id_usuario_perfil 
			   		INNER JOIN
			   role_usuario ru ON ru.id_role = pr.id_role AND ru.id_usuario = u.id_usuario
			        LEFT JOIN
			   permissao_usuario pu ON pu.id_usuario = u.id_usuario
			WHERE
			    u.id_usuario = :id_usuario
			        AND p.nm_permissao = :nm_permissao
			        AND pu.id_permissao IS NULL 
			UNION SELECT 
			    pu.id_permissao
			FROM
			   permissao_usuario pu
			        INNER JOIN
			   permissao p ON p.id_permissao = pu.id_permissao
			WHERE
			    pu.id_usuario = :id_usuario
			        AND p.nm_permissao = :nm_permissao
			        AND pu.ic_concessao = 'S'
			UNION SELECT p.nm_permissao
	            FROM
	               permissao p
	               INNER JOIN
	               permissao_role pr ON p.id_permissao = pr.id_permissao
	               INNER JOIN
	               role_usuario ru ON ru.id_role = pr.id_role
	            WHERE
	               ru.id_usuario = :id_usuario
	               AND p.nm_permissao = :nm_permissao
		", [
			'id_usuario' => $user_log->id_usuario,
			'nm_permissao' => $permission_name
		]);

		if(!$has_permit)
			throw new Exception("Vaza parça! :) daqui: " . $permission_name);
	}

	private static function getActionName($server_name, $action_method_name){
		return mb_strtolower($server_name . ACTION_SERVER_CHAR_SEPARATOR . $action_method_name);
	}

	public static function getToken(){
		return self::$token;
	}

}