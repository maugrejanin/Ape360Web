<?php 

class S_NumeroSorte{
	private $numeros_alocados = [];

	private function gerarNumeroDaSorte() { 
	    $begin = 1;
	    $end = 1999999;
	    $rand_key = rand($begin, $end);
		$exceptions = $this->getExceptions();
		if (!empty($exceptions)) {
		    while(array_search($rand_key, $exceptions) !== false) {
		        $rand_key = ($rand_key+1)%($end+1);
		    }
		}
		array_push($this->numeros_alocados, $rand_key);		
	    return str_pad($rand_key, 7, "0", STR_PAD_LEFT);
	}

	private function getExceptions(){
	    return array_merge(array_column(Model::search("SELECT id_numero FROM t_numero_da_sorte", []), "id_numero"), $this->numeros_alocados);
	}

	// Processar números da sorte sem saldo, mas a partir da qtd de números originais
	public function processarNumerosDaSorte($id_usuario) {
		global $mypdo;
		$valorNumeroDaSorte = VALOR_NUMERO_DA_SORTE;
		$valorMaximoCupomAprovado = VALOR_MAXIMO_CUPOM_APROVADO;
		Model::exec("UPDATE t_cupom SET ic_status = 'A' WHERE ic_status = 'P' and id_usuario = ? and vl_cupom <= ?", [$id_usuario, $valorMaximoCupomAprovado]);
		$cupons = Model::search("SELECT id_cupom, cd_cupom, DATE_FORMAT(dt_cupom, '%d/%m/%Y') as dt_compra, vl_cupom, vl_cupom as vl_compra, vl_disponivel, ic_cartao, case when ic_cartao = 'S' then 'Sim' else 'Não' end ds_ic_cartao FROM t_cupom WHERE id_usuario = ? and ic_status = 'A' and vl_disponivel > 0 order by 3", [$id_usuario]);
		array_walk($cupons, function(&$row){
			$row['vl_compra'] = "R$ " . number_format($row['vl_compra'], 2, ',', '.');
		});
		$nGerados = 0;
		$nGeradosCupom = 0;
		$mypdo->transaction(function() use($valorNumeroDaSorte, $cupons, $id_usuario, $nGerados){
			$relacao = array();
			for ($i = 0; $i < count($cupons); $i++) {
				$nGeradosCupom = 0;
				$saldo = $cupons[$i]["vl_disponivel"];
				while($saldo >= $valorNumeroDaSorte) {
					$numeroSorte = $this->gerarNumeroDaSorte();
					array_push($relacao, $numeroSorte . "|" . $cupons[$i]["id_cupom"] . "|" . $valorNumeroDaSorte);
					$nGerados++;
					$nGeradosCupom++;
					$saldo -= $valorNumeroDaSorte;
				}
				if ($cupons[$i]["ic_cartao"] == "S") {
					for($j = 0; $j < $nGeradosCupom; $j++) {
						$numeroSorte = $this->gerarNumeroDaSorte();
						array_push($relacao, $numeroSorte . "|" . $cupons[$i]["id_cupom"] . "|" . $valorNumeroDaSorte);
						$nGerados++;
					}
					$nGeradosCupom *= 2;
				}
				Model::exec("UPDATE t_cupom SET vl_disponivel = 0 WHERE id_cupom = ?", [$cupons[$i]["id_cupom"]]);
				$comunicado = new Comunicado();
				$id_comunicado = $comunicado->criar(COMUNICADO_CUPOM_RECEBIDO, [$id_usuario], [], ['cd_cupom' => $cupons[$i]["cd_cupom"], 'dt_compra' => $cupons[$i]["dt_compra"], 'vl_compra' => $cupons[$i]["vl_compra"], 'ds_ic_cartao' => $cupons[$i]["ds_ic_cartao"], 'qt_numeros' => $nGeradosCupom]);
			}
			
			$dtCadastro = new DateTime("now", new DateTimeZone('America/Sao_Paulo'));
			$dsAgora = $dtCadastro->format('Y-m-d H:i:s');
			$ultimoNumeroAtualizado = 0;
			foreach ($relacao as $item){
				$valores = explode("|", $item);
				Model::exec("INSERT INTO t_cupom_numero_da_sorte (id_usuario, id_cupom, id_numero, vl_cupom, dt_cadastro) VALUES (?, ?, ?, ?, ?)", [$id_usuario, $valores[1], $valores[0], $valores[2], $dsAgora]);
				
				if ($ultimoNumeroAtualizado != $valores[0]) {
					Model::exec("INSERT IGNORE INTO t_numero_da_sorte (id_numero, id_usuario, dt_cadastro) VALUES (?, ?, ?)", [$valores[0], $id_usuario, $dsAgora]);
				}
			}
		});
		return ["success" => "1", "numeros" => $nGerados];
	}

	// Processar números da sorte sem saldo, mas a partir da qtd de números originais
	public function pendenciasNumerosDaSorte() {
		global $mypdo;
		$valorNumeroDaSorte = VALOR_NUMERO_DA_SORTE;
		$valorMaximoCupomAprovado = VALOR_MAXIMO_CUPOM_APROVADO;
		$pendentes = Model::search("SELECT cn.id_usuario, cn.id_cupom, cn.dt_cadastro, cn.id_numero from t_cupom_numero_da_sorte cn inner join t_cupom c on cn.id_cupom = c.id_cupom left join t_numero_da_sorte n on cn.id_numero = n.id_numero where cn.id_usuario <> n.id_usuario", []);
		$mypdo->transaction(function() use($pendentes){
			for ($i = 0; $i < count($pendentes); $i++) {
				$numeroSorte = $this->gerarNumeroDaSorte();
				$stm = Model::exec("INSERT IGNORE INTO t_numero_da_sorte (id_numero, id_usuario, dt_cadastro) VALUES (?, ?, ?)", [$numeroSorte, $pendentes[$i]["id_usuario"], $pendentes[$i]["dt_cadastro"]]);
				if ($stm->rowCount() > 0) {
					Model::exec("UPDATE t_cupom_numero_da_sorte SET id_numero = ? WHERE id_numero = ? and id_cupom = ? and id_usuario = ? and dt_cadastro = ?", [$numeroSorte, $pendentes[$i]["id_numero"], $pendentes[$i]["id_cupom"], $pendentes[$i]["id_usuario"], $pendentes[$i]["dt_cadastro"]]);
				}
			}
		});
		return ["success" => "1", "numeros" => count($pendentes)];
	}
}