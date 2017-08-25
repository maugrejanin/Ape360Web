<?php
include_once(dirname(__FILE__) . "/Config.php");
include_once(dirname(__FILE__) . "/Consts.php");
include_once(dirname(__FILE__) . "/MyPDO.class.php");

class ComunicadoEnvio {

	public function enviar($id_comunicado, $id_tipo_comunicado, $tokens_assunto = array()) {
		global $mypdo;
		try{
			// Autoload::clear();
			include_once(dirname(__FILE__) . "/MyMail.class.php");
		    $mymail = new MyMail();
		}
		catch(Exception $ex){
			MyPDO::errorLog($ex);
			die;
		}
		$query = "	SELECT 
						c.id_comunicado, c.id_tipo_comunicado, c.ds_parametros, 
						tc.ic_periodicidade, tc.nr_index_periodicidade, tc.ds_template_url, tc.ds_assunto_email, cd.id_usuario, cd.ic_bcc, 
						u.ds_nome as nm_destinatario, u.ds_email as email_destinatario 
					FROM 
						t_comunicado c
					INNER JOIN 
						t_tipo_comunicado tc
					ON 
						c.id_tipo_comunicado = tc.id_tipo_comunicado
					INNER JOIN 
						t_comunicado_destinatario cd
					ON 
						c.id_comunicado = cd.id_comunicado
					INNER JOIN usuario u
					ON 
						cd.id_usuario = u.id_usuario 
					WHERE 
						c.id_comunicado = ?
						and c.dt_comunicado_envio IS NULL
						and cd.dt_comunicado_envio IS NULL";
		$myCommand = $mypdo->prepare($query); 
		$bind = [ $id_comunicado ];
		$myCommand->execute($bind);
		$comunicado = $myCommand->fetchAll(PDO::FETCH_ASSOC);
		$rows_affected = 0;
		foreach ($comunicado as $item) {
			$mymail->AddAddress($item["email_destinatario"], $item["nm_destinatario"]);
			$assunto_email = $item["ds_assunto_email"];
			foreach ($tokens_assunto as $key => $value) {
				$assunto_email = str_replace('[@' . $key . ']', $value, $assunto_email);
			}
			$mailSent = $mymail->SendMail($assunto_email, CONFIG_BASEURL_CLIENT . "/" . $item["ds_template_url"] . "?id_comunicado=" . $item["id_comunicado"] . "&id_usuario=" . $item["id_usuario"], "", "");

			if ($mailSent === true) {			
				// $query = "UPDATE comunicado_destinatario SET dt_comunicado_envio = ? WHERE id_comunicado = ? and id_usuario = ?";
				$query = "UPDATE comunicado_destinatario SET dt_comunicado_envio = ?, ds_email_enviado = ? WHERE id_comunicado = ? and id_usuario = ?";
				$myCommand = $mypdo->prepare($query); 
				
				$dtEnvio = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );

				$bind = [	$dtEnvio->format('Y-m-d H:i:s'), 
							$item["nm_destinatario"] . "<" . $item["email_destinatario"] . ">",
							$item["id_comunicado"],
							$item["id_usuario"]
						];
				$myCommand->execute($bind);
				$rows_affected += $myCommand->rowCount();
			}
		}
		// Autoload::register();
		if ($rows_affected == count($comunicado)) {
			$dtEnvio = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );
			$query = "UPDATE comunicado SET dt_comunicado_envio = ? WHERE id_comunicado = ? ";
			$myCommand = $mypdo->prepare($query); 
			
			$dtEnvio = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );

			$bind = [	$dtEnvio->format('Y-m-d H:i:s'), 
						$id_comunicado
					];
			$myCommand->execute($bind);
			return $myCommand->rowCount();
		}
		return 0;
	}

	public function infoDestinatario($id_comunicado, $id_usuario){
		global $mypdo;

		$query = 
				"SELECT 
				u.id_usuario, u.ds_email, u.ds_nome, u.ds_sobrenome, u.ds_telefone, u.id_usuario_perfil, cd.hash_comunicado as hash
				FROM usuario u
				INNER JOIN comunicado_destinatario cd
				WHERE u.id_usuario = cd.id_usuario
				AND cd.id_comunicado = ?
				AND cd.id_usuario = ?
				ORDER BY u.id_usuario";

		$stm = $mypdo->prepare($query);
		$stm->execute([$id_comunicado, $id_usuario]);
		$result = $stm->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	public function infoParametros($id_comunicado){
		global $mypdo;

		$query = 
				"SELECT 
				ds_parametros
				FROM comunicado c
				WHERE c.id_comunicado = ?";

		$stm = $mypdo->prepare($query);
		$stm->execute([$id_comunicado]);
		$result = $stm->fetchAll(PDO::FETCH_ASSOC);
		$parametros = (count($result) > 0 ? json_decode($result[0]["ds_parametros"], true) : []);
		return $parametros;
	}

	public function infoComunicado($id_comunicado, $id_usuario){
		$destinatario = $this->infoDestinatario($id_comunicado, $id_usuario);
		$parametros = $this->infoParametros($id_comunicado);
		return compact("destinatario", "parametros");
	}

	public function acaoComunicado($acao, $hash) {
		global $mypdo;
		$campo = "";
		switch ($acao) {
			case EMAIL_ACAO_LEITURA:
				$campo = "dt_comunicado_leitura";
			break;
			case EMAIL_ACAO_CLIQUE:
				$campo = "dt_comunicado_clique";
			break;
			default:
				return;
			break;
		}
		$agora = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );
		$query = 
				"UPDATE comunicado_destinatario set " . $campo . " = ? where hash_comunicado = ? and " . $campo . " is NULL";
		$stm = $mypdo->prepare($query);
		$stm->execute([$agora->format('Y-m-d H:i:s'), $hash]);
	}
}
?>