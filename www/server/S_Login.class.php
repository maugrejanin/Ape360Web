<?php 

class S_Login{

	private $token_fields = [// $$$ -> esses campos vão para o javascript
		'id_usuario',
		'id_usuario_perfil',
		'ds_email',
		'ds_nome',
		'ds_sexo',
		'ds_local'
	];

	public function logout(){
		unset($_SESSION[TOKEN_INDEX]);
		return true;
	}

	public function login(){
		global $__post;
		
		extract(require_post('email', 'senha'));//$email, $senha

		$user_record = Model::search(
			"SELECT concat(c.ds_cidade, ' - ', c.ds_uf) as ds_local, u.* 
			FROM t_usuario u
			LEFT join t_usuario_cadastro c on u.id_usuario = c.id_usuario
			WHERE u.ds_email = ?", [$email], false
		);

		if (!$user_record)
			throw new Ex_User("Erro de autenticação: usuário não encontrado");

		if(password_verify($senha, $user_record['ds_pwd_hash'])) {
			$__token = JWT::encode(array_sub($user_record, $this->token_fields), CONFIG_SALT_TOKEN);
			$_SESSION[TOKEN_INDEX] = $__token;

			$nm_denied_permissions = Model::search("
				SELECT nm_permissao FROM t_permissao p WHERE nm_permissao NOT IN (
				SELECT p.nm_permissao FROM t_permissao p
				INNER JOIN t_permissao_role pr ON p.id_permissao = pr.id_permissao
				INNER JOIN t_role_perfil ru ON ru.id_role = pr.id_role
				WHERE ru.id_usuario_perfil = ?
				)", [$user_record['id_usuario_perfil']]
			);

			$nm_denied_permissions = $nm_denied_permissions? array_column($nm_denied_permissions, 'nm_permissao'): [];

			return compact('nm_denied_permissions');
		} else {
			throw new Ex_User("Erro de autenticação: senha inválida");
		}
	}
	public function loginFrontEnd(){
		global $user_log;
		try{
			Validator::run([
			'ds_email' => ['required', 'email'],
			'ds_senha' => 'required'
			]);
		}
		catch (Exception $ex) {
			return ["success" => "0", "title" => "Usuário ou senha incorretos", "message" => "Por favor, preencha todos os campos para continuar"];
		} 
		return $this->authFrontEnd($_POST["ds_email"], $_POST["ds_senha"]);
	}

	public function authFrontEnd($ds_email, $ds_senha){
		global $user_log;

		$usuario = Model::search("SELECT u.id_usuario, u.ds_nome, u.ds_email, u.ds_pwd_hash, c.ds_sexo, concat(c.ds_cidade, ' - ', c.ds_uf) as ds_local FROM t_usuario u  inner join t_usuario_cadastro c on u.id_usuario = c.id_usuario where u.ds_email = ?", [$ds_email]);
		if (count($usuario) > 0 && password_verify(htmlspecialchars($ds_senha), $usuario[0]["ds_pwd_hash"])) {
			$__token = JWT::encode(array_sub($usuario[0], $this->token_fields), CONFIG_SALT_TOKEN);
			$_SESSION[TOKEN_INDEX] = $__token;
			$_SESSION['id_usuario'] = $usuario[0]["id_usuario"];
			$_SESSION['ds_email'] = $usuario[0]["ds_email"];
			$_SESSION['ds_nome'] = $usuario[0]["ds_nome"];
			$_SESSION['ds_sexo'] = $usuario[0]["ds_sexo"];
			$_SESSION['ds_local'] = $usuario[0]["ds_local"];
			// $_SESSION['ds_cidade'] = $usuario[0]["ds_cidade"];
			// $_SESSION['ds_uf'] = $usuario[0]["ds_uf"];

			$user_log = new StdClass();
			$user_log->id_usuario = $_SESSION['id_usuario'];
			$user_log->ds_email = $_SESSION['ds_email'];
			$user_log->ds_nome = $_SESSION['ds_nome'];
			$user_log->ds_sexo = $_SESSION['ds_sexo'];
			$user_log->ds_local = $_SESSION['ds_local'];
			// dump(gettype($usuario));
			return ["success" => "1", "message" => "Bem-vindo, " . $usuario[0]["ds_nome"] . "!", "user" => $user_log];
		}
		return ["success" => "0", "message" => "E-mail ou senha incorretos."];
	}

	public function alterarSenha(){
		global $__post, $user_log;
		
		extract(require_post('senha', 'nova_senha'));//$email, $senha

		$user_record = Model::search(
			"SELECT u.*
			FROM t_usuario u
			WHERE u.id_usuario = ?", [$user_log->id_usuario], false
		);

		if (!$user_record)
			throw new Ex_User("Erro de autenticação: usuário não encontrado");

		if(password_verify($senha, $user_record['ds_pwd_hash'])) {
			Model::exec(
			"UPDATE t_usuario u set ds_pwd_hash = ?
			WHERE u.id_usuario = ?", [$this->generatePassword($nova_senha), $user_log->id_usuario]);			
			return ["success" => "1", "message" => "Senha alterada com sucesso."];
		} else {
			throw new Ex_User("Erro de autenticação: senha inválida");
		}
		return ["success" => "0", "message" => "Senha não alterada."];
	}
	private function generatePassword($password){
		$password = $password? $password: 'WayPortal';//gerar senha padrão
		return password_hash($password, PASSWORD_DEFAULT);
	}

}