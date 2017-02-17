<?php 	

/**
* @title Front-End
* @description Tela com os macro-dados do sistema
* @restriction F
*/

class S_FrontEnd{
	public function cadastrar(){
		global $user_log;

		$amizade_columns = [
			'id_usuario',
			'ds_endereco',
			'ds_cidade',
			'ds_uf',
			'ds_cpf',
			'ds_rg',
			'ds_celular',
			'ds_sexo',
			'ds_nascimento',
			'ds_nome_cartao',
			'ds_nome_amigo',
			'ds_endereco_amigo',
			'ds_cidade_amigo',
			'ds_uf_amigo',
			'ds_cpf_amigo',
			'ds_rg_amigo',
			'ds_telefone_amigo',
			'ds_celular_amigo',
			'ds_email_amigo',
			'ds_sexo_amigo',
			'ds_nascimento_amigo',
		];

		$m_amizade = new Model('t_usuario_cadastro', null);
		$ds_senha_not_treat = isset($_POST['ds_senha'])? $_POST['ds_senha']: false;
		$insert_data = Treat::run([
			'ds_cpf' => 'absolute_number',
			'ds_rg' => 'notspecialchar',
			'ds_cpf_amigo' => 'absolute_number',
			'ds_rg_amigo' => 'notspecialchar',
			'ds_senha' => 'pwd_hash',
			'ds_nascimento' => 'date',
			'ds_nascimento_amigo' => 'date',
			'ds_sexo' => 'uppercase',
			'ds_sexo_amigo' => 'uppercase',
			'ds_telefone' => 'absolute_number',
			'ds_telefone_amigo' => 'absolute_number',
			'ds_celular' => 'absolute_number',
			'ds_celular_amigo' => 'absolute_number',
		]) + $_POST;

		//-------------------------------------------------------------------------------------

		Validator::run([
			'ds_nome' => 'required', 
			'ds_endereco' => 'required', 
			'ds_cidade' => 'required', 
			'ds_uf' => ['required', 'uf'], 
			'ds_cpf' => ['required', 'cpf'], 
			'ds_telefone' => ['required', 'minlength' => 10], 
			'ds_celular' => ['required', 'minlength' => 10], 
			'ds_email' => ['required', 'email'], 
			'ds_sexo' => ['required', 'in_list' => ['M', 'F']], 
			'ds_nascimento' => ['required', 'date'], 
			'ds_senha' => 'required', 
			'ds_senha_confirmacao' => ['required', 'equal' => $ds_senha_not_treat],
			'ds_nome_amigo' => 'required', 
			'ds_endereco_amigo' => 'required', 
			'ds_cidade_amigo' => 'required', 
			'ds_uf_amigo' => ['required', 'uf'], 
			'ds_cpf_amigo' => ['required', 'cpf'], 
			'ds_telefone_amigo' => ['required', 'minlength' => 10], 
			'ds_celular_amigo' => ['required', 'minlength' => 10], 
			'ds_email_amigo' => ['required', 'email'], 
			'ds_sexo_amigo' => ['required', 'in_list' => ['M', 'F']], 
			'ds_nascimento_amigo' => ['required', 'date'], 
		], $insert_data);

		$usuario = Model::search("SELECT id_usuario, ds_nome, ds_email, ds_pwd_hash FROM t_usuario where ds_email = ?", [$insert_data["ds_email"]]);
		$agora = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );
		if (count($usuario) > 0) {
			return ["success" => "0", "title" => "E-mail já cadastrado", "message" => "Oops! Este e-mail já está cadastrado."];
		}
		else {
			$m_usuario = new Model("t_usuario", "id_usuario");
			$insert_data["dt_change_pwd"] = $agora->format('Y-m-d H:i:s');
			$insert_data["id_usuario_perfil"] = ID_PROFILE_PARTICIPANT;
			$insert_data["dt_cadastro"] = $agora->format('Y-m-d H:i:s');
			$insert_data["ds_sobrenome"] = "";
			$insert_data["ds_pwd_hash"] = $insert_data["ds_senha"];
			$id_usuario = $m_usuario->insert($insert_data, ["ds_email", "ds_nome", "ds_telefone", "ds_pwd_hash", "id_usuario_perfil", "dt_change_pwd", "dt_cadastro"]);
			$insert_data["id_usuario"] = $id_usuario;
			$m_amizade->insert($insert_data, $amizade_columns);
			$auth = (new S_Login())->authFrontEnd($insert_data["ds_email"], $ds_senha_not_treat);
			// dump($_SESSION);
			return ["success" => "1", "title" => "Cadastrado realizado", "message" => "Obrigado, " . $insert_data["ds_nome"] . "! A partir de agora, basta acessar com o seu e-mail e senha.", "user" => $user_log];
		}
	}
	public function logout(){
		if (isset($_SESSION[TOKEN_INDEX])) {
			session_unset();
			session_destroy();
		}
		return true;
	}
	public function openResetSenha($hash = ''){
		if ($hash == '') {
			return ["success" => "0", "message" => "Solicitação não encontrada."];
		}
		$agora = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );
		$solicitacao = Model::search("SELECT s.ds_codigo, s.ic_ativo, s.id_usuario, u.ds_nome, u.ds_email
				FROM t_solicitacao_reset_senha s
				inner join t_usuario u
				on s.id_usuario = u.id_usuario
				where s.ds_codigo = ? and s.ic_ativo = 'S' and s.dt_validade >= ?", [$hash, $agora->format('Y-m-d H:i:s')]);
		if (count($solicitacao) == 0) {
			return ["success" => "0", "message" => "Solicitação não encontrada."];
		}
		$_SESSION['id_usuario'] = $solicitacao[0]["id_usuario"];
		$_SESSION['ds_email'] = $solicitacao[0]["ds_email"];
		$_SESSION['ds_nome'] = $solicitacao[0]["ds_nome"];
		$_SESSION['ds_codigo'] = $solicitacao[0]["ds_codigo"];
		return ["success" => "1", "message" => "Solicitação encontrada."];
	}
	public function doResetSenha(){
		if (empty($_POST["ds_password"])) {
			return ["success" => "0", "message" => "Por favor, informe a sua nova senha para continuar."];
		}
		if (empty($_SESSION['id_usuario'])) {
			return ["success" => "0", "message" => "Usuário não encontrado."];	
		}
		if (empty($_POST["ds_hash"]) || empty($_SESSION['ds_codigo'])) {
			return ["success" => "0", "message" => "Solicitação não encontrada."];
		}
		if ($_POST["ds_hash"] != $_SESSION['ds_codigo']) {
			return ["success" => "0", "message" => "Solicitação inválida."];
		}
		$agora = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );
		$pwd = password_hash($_POST["ds_password"], PASSWORD_DEFAULT);
		Model::exec("UPDATE t_usuario set ds_pwd_hash = ?, dt_change_pwd = ? where id_usuario = ?", [$pwd, $agora->format('Y-m-d H:i:s'), $_SESSION["id_usuario"]]);
		Model::exec("UPDATE t_solicitacao_reset_senha set ic_ativo = 'N', dt_utilizacao = ? where id_usuario = ? and ds_codigo = ?", [$agora->format('Y-m-d H:i:s'), $_SESSION["id_usuario"], $_SESSION["ds_codigo"]]);
		return ["success" => "1", "message" => "Senha redefinida."];
	}
	public function solicitarResetSenha(){
		global $user_log;
		try{
			Validator::run([
				'ds_email' => ['required', 'email'],
				'ds_cpf' => ['required', 'cpf']
			]);

			$insert_data = Treat::run([
				'ds_cpf' => 'absolute_number',
				'ds_email' => 'lowercase',
			]) + $_POST;
		}
		catch (Exception $ex) {
			return ["success" => "0", "message" => "Por favor, preencha corretamente seu e-mail para continuar."];
		}
		$usuario = Model::search("SELECT u.id_usuario, u.ds_nome, u.ds_email, u.ds_pwd_hash FROM t_usuario u inner join t_usuario_cadastro c on u.id_usuario = c.id_usuario where u.ds_email = ? and c.ds_cpf = ?", [$insert_data["ds_email"], $insert_data["ds_cpf"]]);
		if (count($usuario) > 0) {
			$codigo = substr(md5(rand()), 0, 7);
			$ds_codigo = substr(str_replace("%", "", urlencode(password_hash($codigo, PASSWORD_DEFAULT))), 0, 45);
			$agora = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );
			$validade = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );
			$validade->add(new DateInterval('PT3H'));
			Model::exec("INSERT INTO t_solicitacao_reset_senha (dt_solicitacao, ds_codigo, dt_validade, ic_ativo, id_usuario) values (?, ?, ?, ?, ?)", [$agora->format('Y-m-d H:i:s'),  $ds_codigo, $validade->format('Y-m-d H:i:s'), 'S', $usuario[0]["id_usuario"]]);
			Permit::setTokenCredentials(false);
			$comunicado = new Comunicado();
			$id_comunicado = $comunicado->criar(COMUNICADO_NOVA_SENHA, [$usuario[0]["id_usuario"]], [], ['ds_link' => CONFIG_BASEURL_CLIENT . "ResetSenha.php?h=" . $ds_codigo]);
			return ["success" => "1", "message" => "Um e-mail foi enviado a você com as instruções para a definição de uma nova senha."];
		}
		else {
			return ["success" => "0", "message" => "Oops! Este e-mail não consta em nosso cadastro."];
		}
	}
	public function verifyCredentials(){
		global $user_log;
		Permit::setTokenCredentials(false);
		return ["success" => "1", "user" => $user_log];
	}
	public function registerUnhandledError(){
		extract(require_post('error_message'));//$error_message
		return MyPDO::errorLog($error_message);
	}
	public function saveAvatar(){
		global $user_log;

		if(!isset($user_log->id_usuario) && !isset($_SESSION["id_usuario"]))
			throw new Ex_Authentication();

		$id_usuario = isset($user_log->id_usuario)? $user_log->id_usuario: $_SESSION["id_usuario"];

		Permit::setTokenCredentials(false);
		if(!isset($_FILES['cropped_image']) or $_FILES['cropped_image']['error'])
			throw new Ex_User('Imagem não encontrada');

		return fastUpload(
			dirname(__FILE__) . '/../img/',
			'avatar/',
			$_FILES['cropped_image'],
			$id_usuario . ".jpg"
		);
	}
}