<?php
include_once(__DIR__ . "/ComunicadoEnvio.class.php");

class Comunicado extends ComunicadoEnvio {

	public function criar($id_tipo_comunicado, array $id_destinatarios, array $id_destinatarios_bcc, array $params) {
		global $mypdo, $user_log;
		$id_usuario_criacao = null;
		if (!empty($user_log) && !empty($user_log->id_usuario)) {
			$id_usuario_criacao = $user_log->id_usuario;
		}
		$query = "SELECT id_tipo_comunicado, ic_periodicidade FROM tipo_comunicado WHERE id_tipo_comunicado = ?";
		$myCommand = $mypdo->prepare($query); 
		$bind = [ $id_tipo_comunicado ];
		$myCommand->execute($bind);
		$tipo_comunicado = $myCommand->fetchAll(PDO::FETCH_ASSOC);
		if (count($tipo_comunicado) < 1) {
			throw new Ex_Comunicado("Tentativa de envio de comunicado com tipo inválido (" . $id_tipo_comunicado . ")");
			return 0;
		}

		$query = "INSERT INTO comunicado (
						id_tipo_comunicado,
						dt_comunicado_agendado,
						dt_comunicado_envio,
						id_usuario_criacao,
						ds_parametros
					)
					VALUES (?, ?, ?, ?, ?)";
		$myCommand = $mypdo->prepare($query); 
		$dtCriacao = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );
		$bind = [	$id_tipo_comunicado, 
					$dtCriacao->format('Y-m-d H:i:s'), 
					NULL,
					$id_usuario_criacao,
					json_encode($params)
				];
		$myCommand->execute($bind);
		$id_comunicado = $mypdo->lastInsertId();

		$query_destinatarios = "INSERT INTO comunicado_destinatario (id_comunicado, id_usuario, ic_bcc, hash_comunicado) values ";
		$bind = [];
		$primeiro = true;
		foreach ($id_destinatarios as $destinatario) {
			if ($primeiro === true) {
				$primeiro = false;
			}
			else {
				$query_destinatarios .= ", ";
			}
			$query_destinatarios .= " (?, ?, ?, ?)";
			array_push($bind, $id_comunicado, $destinatario, "N", md5(strval($id_comunicado) . $destinatario));
		}

		foreach ($id_destinatarios_bcc as $destinatario) {
			if ($primeiro === true) {
				$primeiro = false;
			}
			else {
				$query_destinatarios .= ", ";
			}
			$query_destinatarios .= " (?, ?, ?, ?)";
			array_push($bind, $id_comunicado, $destinatario, "S", md5(strval($id_comunicado) . $destinatario));
		}
		$myCommand = $mypdo->prepare($query_destinatarios); 
		$myCommand->execute($bind);
		
		return $id_comunicado;
	}
}

?>