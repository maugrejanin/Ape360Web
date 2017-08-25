<?php 

class S_Login{

	private $token_fields = [// $$$ -> esses campos vão para o javascript
		'id_usuario',
		'id_usuario_perfil',
		'ds_email',
		'ds_nome',
		'ds_sexo'
	];

	public function logout(){
		unset($_SESSION[TOKEN_INDEX]);
		return true;
	}

	public function login(){
		global $__post;
		
		extract(require_post('email', 'senha'));//$email, $senha

		$user_record = Model::search(
			"SELECT u.*, p.ds_nome, p.ds_sexo
			FROM usuario u
			INNER JOIN pessoa p ON p.id_pessoa = u.id_pessoa
			WHERE u.ds_email = ?", [$email], false
		);


		if (!$user_record)
			throw new Ex_User("Erro de autenticação: usuário não encontrado");

		if(password_verify($senha, $user_record['ds_pwd_hash'])) {
			$__token = JWT::encode(array_sub($user_record, $this->token_fields), CONFIG_SALT_TOKEN);
			$_SESSION[TOKEN_INDEX] = $__token;
			$nm_denied_permissions = $this->getDeniedPermission(
				$user_record['id_usuario'], $user_record['id_usuario_perfil']
			);

			return compact('nm_denied_permissions');
		} else
			throw new Ex_User("Erro de autenticação: senha inválida");

	}

	private function getDeniedPermission($id_usuario, $id_usuario_perfil){
		if($id_usuario_perfil != PERMIT_TECHNICIAN){
			$nm_denied_permissions = Model::search("
				SELECT 
				    nm_permissao
				FROM
				    permissao p
				WHERE
				    nm_permissao NOT IN (
					    SELECT DISTINCT nm_permissao
						FROM (
					       SELECT p.nm_permissao
					       FROM
					         permissao p
					         INNER JOIN
					         permissao_role pr ON p.id_permissao = pr.id_permissao
					         INNER JOIN
					         role_perfil rp ON rp.id_role = pr.id_role
					         INNER JOIN
					         usuario u ON u.id_usuario_perfil = rp.id_usuario_perfil
					         LEFT JOIN
					         permissao_usuario pu ON pu.id_usuario = u.id_usuario
					       WHERE
					         u.id_usuario = :id_usuario
					         AND pu.id_permissao IS NULL
					       UNION SELECT p.nm_permissao
					             FROM
					               permissao_usuario pu
					               INNER JOIN
					               permissao p ON p.id_permissao = pu.id_permissao
					             WHERE
					               pu.id_usuario = :id_usuario
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
						    ) a
			        )", compact('id_usuario')
			);

			return $nm_denied_permissions? array_column($nm_denied_permissions, 'nm_permissao'): [];
		}else
			return [];
	}

}