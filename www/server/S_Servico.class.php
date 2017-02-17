<?php

/**
* @title Serviços
* @description Tela de serviços administrativos
* @restriction T
*/

class S_Servico {
	public function start($cd_servico){
		$this->cd_servico=$cd_servico;
		$status = false;
		if (strlen($this->cd_servico) > 20) return $status;
		$now = new DateTime('now', new DateTimeZone( 'America/Sao_Paulo' ) );
		$ic_esta_comigo = false;

		try{
			$query = "UPDATE t_agenda_servico set ic_em_execucao = 'S' where cd_servico = ? and ic_em_execucao = 'N'";
			$oStatus = Model::exec($query, [$this->cd_servico]);
			if ($oStatus->rowCount() > 0) {
				$ic_esta_comigo = true;
				$this->logAgendaServico($this->cd_servico, SERVICO_STATUS_INICIADO, "Início do processamento");
				switch ($this->cd_servico) {
					case SERVICO_COMUNICADO_IMEDIATO:
						$this->envioComunicado($this->cd_servico, COMUNICADO_PERIODICIDADE_IMEDIATA);
						break;
				}
				$status = true;
			}
		}
		catch(Exception $ex) {
			$id_erro = MyPDO::errorLog($ex);
			$this->logAgendaServico($this->cd_servico, SERVICO_STATUS_ERRO, "Erro no processamento.", $id_erro);
			$status = false;
		}
		finally {
			if ($ic_esta_comigo === true) {
				$query = "UPDATE t_agenda_servico set ic_em_execucao = 'N' where cd_servico = ?";
				$oStatus = Model::exec($query, [$this->cd_servico]);
				$this->logAgendaServico($this->cd_servico, SERVICO_STATUS_FINALIZADO, "Término do processamento.");
			}
			return $status;
		}  
	}

	protected function logAgendaServico($cd_servico, $id_status, $ds_status, $id_erro = NULL) {
		$now = new DateTime('now', new DateTimeZone( 'America/Sao_Paulo' ) );
		$query = "INSERT INTO t_agenda_servico_log (dt_evento, cd_servico, id_Servico_status, ds_status, id_erro) values (?, ?, ?, ?, ?)";
		Model::exec($query, [$now->format('Y-m-d H:i:s'), $cd_servico, $id_status, $ds_status, $id_erro]);
	}

	protected function envioComunicado($tipo_servico_comunicado, $ic_periodicidade) {
		$comunicado = new ComunicadoEnvio();
		try{
			$query = "SELECT c.id_comunicado, c.id_tipo_comunicado, c.dt_comunicado_agendado, c.ds_parametros, 
						tc.ds_assunto_email, tc.ic_periodicidade, tc.nr_index_periodicidade, tc.ds_template_url
						from t_comunicado c 
						inner join t_tipo_comunicado tc
						on c.id_tipo_comunicado = tc.id_tipo_comunicado 
						where tc.ic_periodicidade = ? and tc.nr_index_periodicidade = 0 and c.dt_comunicado_envio is null order by c.id_comunicado";
			$fila = Model::search($query, [$ic_periodicidade]);
			if (count($fila) > 0) {
				$this->logAgendaServico($tipo_servico_comunicado, SERVICO_STATUS_PROCESSAMENTO, "Encontrados " . count($fila) . " comunicados. Iniciando...");
				foreach ($fila as $item) {
					$this->logAgendaServico($tipo_servico_comunicado, SERVICO_STATUS_PROCESSAMENTO, "Comunicado " . $item["id_comunicado"] . " - Enviando...");

					$envios = $comunicado->enviar($item["id_comunicado"], $item["id_tipo_comunicado"], json_decode($item["ds_parametros"], true));
					
					$this->logAgendaServico($tipo_servico_comunicado, SERVICO_STATUS_PROCESSAMENTO, "Comunicado " . $item["id_comunicado"] . " - Enviado para " . $envios . " destinatários.");
				}
				$this->logAgendaServico($tipo_servico_comunicado, SERVICO_STATUS_PROCESSAMENTO, "Feito! Saindo...");
			}
			else {
				$this->logAgendaServico($tipo_servico_comunicado, SERVICO_STATUS_PROCESSAMENTO, "Nenhum comunicado encontrado.");
			}
		}
		catch(Exception $ex) {
			$id_erro = MyPDO::errorLog($ex);
			throw $ex;
		}
	} 
}