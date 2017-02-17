<?php

function gerarNumeroDaSorte() { 
    $begin = 1;
    $end = 99999;
    $rand_key = rand($begin, $end);
	$exceptions = getExceptions();
    while(array_search($rand_key, $exceptions) !== false) {
        $rand_key = ($rand_key+1)%($end+1);
    }
    return str_pad($rand_key, 5, "0", STR_PAD_LEFT);
}

function getExceptions(){
    global $myConn;
    $myCommand = $myConn->prepare('SELECT id_numero FROM t_numero_da_sorte');
    $myCommand->execute();
    return $myCommand->fetchAll(PDO::FETCH_COLUMN, 'numero');
}

function processarNumerosDaSorte() {
	global $myConn, $config;
	$valorNumeroDaSorte = $config['ValorNumeroSorte'];
	//$myCommand = $myConn->prepare('SELECT id_participante, SUM(vl_disponivel) AS valor FROM t_cupom WHERE ic_status = \'A\' and vl_disponivel > ' . $valorNumeroDaSorte . ' group by id_participante order by 1');
	$myCommand = $myConn->prepare('select * from (SELECT id_participante, SUM(vl_disponivel) AS valor FROM t_cupom WHERE ic_status = \'A\' group by id_participante) a where valor >= ' . $valorNumeroDaSorte . ' order by 1');
	$myCommand->execute();
	$myResult = $myCommand->fetchAll(\PDO::FETCH_OBJ);
	$retorno = "";
	$nPrevistos = 0;
	foreach($myResult as $row){
		$numerosParaGerar = floor($row->valor / $valorNumeroDaSorte);
		$nPrevistos = $numerosParaGerar;
		if ($numerosParaGerar > 0) {
			$myCommand = $myConn->prepare('SELECT id_cupom, vl_cupom, vl_disponivel FROM t_cupom WHERE id_participante = ? and ic_status = \'A\' and vl_disponivel > 0 order by 3');
			$myCommand->bindValue(1, $row->id_participante);
			$myCommand->execute();		
			$myCupons = $myCommand->fetchAll(\PDO::FETCH_OBJ);
			try{
				$myConn->beginTransaction();
				$valorParcial = 0;
				$valorSaldo = 0;
				$relacao = array();
				$numeroSorte = gerarNumeroDaSorte();
				$i = 0;
				$ultimoValorDisponivel = 0;
				while($numerosParaGerar > 0) {
					if ($valorSaldo >= $valorNumeroDaSorte) {
						array_push($relacao, $numeroSorte . "|" . $myCupons[$i]->id_cupom . "|" . $valorNumeroDaSorte);
						$valorSaldo -= $valorNumeroDaSorte;
					}
					else if (($valorSaldo + $myCupons[$i]->vl_disponivel) < $valorNumeroDaSorte) {
						array_push($relacao, $numeroSorte . "|" . $myCupons[$i]->id_cupom . "|" . $myCupons[$i]->vl_disponivel);
						$valorSaldo += $myCupons[$i]->vl_disponivel;
						$i++;
					}
					else {
						$valorParcial = ($valorNumeroDaSorte - $valorSaldo);
						array_push($relacao, $numeroSorte . "|" . $myCupons[$i]->id_cupom . "|" . $valorParcial);
						$myCupons[$i]->vl_disponivel -= $valorParcial;
						$ultimoValorDisponivel = $myCupons[$i]->vl_disponivel;
						$valorSaldo = 0;
						$numeroSorte = gerarNumeroDaSorte();
						$numerosParaGerar--;
					}
				}
				$dtCadastro = new DateTime("now", new DateTimeZone('America/Sao_Paulo'));
				$dsAgora = $dtCadastro->format('Y-m-d H:i:s');
				$ultimoCupomAtualizado = 0;
				$ultimoNumeroAtualizado = 0;
				foreach ($relacao as $item){
					$myCommand = $myConn->prepare('INSERT INTO t_cupom_numero_da_sorte (id_participante, id_cupom, id_numero, vl_cupom, dt_cadastro) VALUES (?, ?, ?, ?, ?)');
					$valores = explode("|", $item);
					//echo $item . "<br>";
					$myCommand->bindValue(1, $row->id_participante);
					$myCommand->bindValue(2, $valores[1]);
					$myCommand->bindValue(3, $valores[0]);
					$myCommand->bindValue(4, $valores[2]);
					$myCommand->bindValue(5, $dsAgora);
					$myCommand->execute();
					if ($ultimoNumeroAtualizado != $valores[0]) {
						$myCommand = $myConn->prepare('INSERT IGNORE INTO t_numero_da_sorte (id_numero, id_participante, dt_cadastro) VALUES (?, ?, ?)');
						$myCommand->bindValue(1, $valores[0]);
						$myCommand->bindValue(2, $row->id_participante);
						$myCommand->bindValue(3, $dsAgora);
						$myCommand->execute();
					}
					if ($ultimoCupomAtualizado != $valores[1]) {
						$updQuery = 'UPDATE t_cupom SET vl_disponivel = ? where id_cupom = ?';
						$myCommand = $myConn->prepare($updQuery);
						if ($valores[1] != $myCupons[$i]->id_cupom) {
							$myCommand->bindValue(1, 0);
						}
						else {
							$myCommand->bindValue(1, $ultimoValorDisponivel);
						}
						//echo "<br><br><h2>Ultimo disp:" . $ultimoValorDisponivel . "</h2><br><br>";
						$myCommand->bindValue(2, $valores[1]);
						$myCommand->execute();
						$ultimoCupomAtualizado = $valores[1];
					}
				}
				$myConn->commit();
			}
			catch(\PDOException $ex){
				$myConn->rollBack();
				if ($ex->getCode() == 23000) {
					// Duplicate key
					$dbError = '(' . $ex->getCode() . ' - ' . $ex->getLine() . ') ' . $ex->getMessage();
				}
				else {
					$dbError = '(' . $ex->getCode() . ' - ' . $ex->getLine() . ') ' . $ex->getMessage();
				}
				$retorno = $dbError;
			}
			if ($retorno != "") {
				return $retorno;
			}
		}
	}
	return "OK:" . $nPrevistos;
}

?>