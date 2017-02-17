<?php 

/**
* @title Cupons
* @description Tela de visualização e acompanhamento de cupons
* @restriction T
*/

class S_Cupom{

	/**
	* @title teste
	* @description teste teste teste
	*/

	public function listCupons(){
		global $user_log;
		$saldo = "";
		if (empty($user_log)) return ["success" => "0", "message" => "Por favor, faça login para continuar."];

		$cupons = Model::search(
			"SELECT 
			    DATE_FORMAT(c.dt_cadastro, '%d/%m/%Y') AS dt_envio,
			    DATE_FORMAT(c.dt_cupom, '%d/%m/%Y') AS ds_dt_cupom,
			    IFNULL(DATE_FORMAT(c.dt_status, '%d/%m/%Y'), '') AS ds_dt_status,
			    s.ds_cupom_status,
			    c.id_cupom,
			    c.id_usuario,
			    c.cd_cupom,
			    c.vl_cupom,
			    c.dt_cupom,
			    c.nm_arquivo,
			    c.dt_cadastro,
			    c.vl_disponivel,
			    c.ic_status,
			    IFNULL(c.ds_motivo_status, '') ds_motivo_status,
			    c.dt_status,
			    c.id_usuario_status,
			    c.tp_cupom,
			    CASE c.tp_cupom
			        WHEN 'C' THEN 'Cupom'
			        WHEN 'F' THEN 'CF Eletrônico/SAT'
			        ELSE 'NF eletrônica'
			    END ds_tipo
			FROM
			    t_cupom c
			        INNER JOIN
			    t_cupom_status s ON c.ic_status = s.ic_cupom_status
			WHERE
			    c.id_usuario = ? AND 
			    c.ic_status <> ?
			ORDER BY c.dt_cadastro DESC", [
				$user_log->id_usuario,
				CUPOM_STATUS_EXCLUIDO
			]
		);

		$numeros = Model::search(
			"SELECT 
			    ns.id_numero,
			    LPAD(ns.id_numero, 7, '0') ds_numero,
			    cns.id_cupom
			FROM
			    t_numero_da_sorte ns
			        INNER JOIN
			    t_cupom_numero_da_sorte cns ON cns.id_numero = ns.id_numero
			    	INNER JOIN
			    t_cupom c ON c.id_cupom = cns.id_cupom
			WHERE
			    ns.id_usuario = ? AND 
			    c.ic_status <> ?", [
			    $user_log->id_usuario,
				CUPOM_STATUS_EXCLUIDO
			]
		);

		if (count($cupons) > 0) {
			$cupons_validos = array_filter($cupons, function($row){
				return ( ($row['ic_status'] != 'R') && ($row['ic_status'] != 'r') );
			});

			$saldo = "R$ " . number_format( array_sum( array_column($cupons_validos, 'vl_cupom') ) - (VALOR_NUMERO_DA_SORTE * count($numeros)), 2, ',', '.');
			$saldo = "R$ " . number_format( array_sum( array_column($cupons_validos, 'vl_disponivel') ), 2, ',', '.');
		}
		array_walk($cupons, function(&$row){
			$row['vl_cupom'] = "R$ " . number_format($row['vl_cupom'], 2, ',', '.');
		});

		$cupons = array_key_column($cupons, 'id_cupom');

		return ["success" => "1", "message" => "", "saldo" => $saldo, "data" => compact("cupons", "numeros")];
	}

	public function delete($id_cupom){
		$model = new Model('t_cupom', 'id_cupom');
		$data_insert['ic_status'] = CUPOM_STATUS_EXCLUIDO;
		$agora = date('Y-m-d H:i:s');
		$data_insert['dt_status'] = $agora;
		return (boolean)$model->update($data_insert, $id_cupom);
	}

	public function insertCupons(){
		global $user_log, $mypdo;

		$complement = ((empty($_POST['tp_cupom']) || $_POST['tp_cupom'] == 'C')? 'do cupom': 'da nota');

		if(!isset($_FILES['img_cupom']) or $_FILES['img_cupom']['error'])
			throw new Ex_User('Por favor carregue a foto ' . $complement);

		$agora = date('Y-m-d H:i:s');

		$dafault_values = [
			'dt_cadastro' => $agora,
			'vl_disponivel' => isset($_POST['vl_cupom'])? Treat::money(Treat::money($_POST['vl_cupom']) * ($_POST['ic_cartao'] == "S"? 1: 1)): 0,//!!!cartão não dobra saldo desde 2016-11-14
			'id_usuario' => $user_log->id_usuario
		];

		$data_insert = Treat::run([
			'dt_cupom' => 'date',
			'vl_cupom' => 'money',
			'tp_cupom' => 'uppercase',
			'ic_cartao' => 'uppercase'
		]) + $dafault_values + $_POST;

		Validator::run([
			'tp_cupom' => 'required',
			'ic_cartao' => 'required',
			'cd_cupom' => 'required',
			'dt_cupom' => ['required', 'date'],
			'vl_cupom' => ['required', 'min' => 50, 'decimal', 'bigger' => 0],
			'img_cupom' => ['extension' => ['gif', 'jpeg', 'jpg', 'png', 'pdf'], 'max_file_size']
		], $data_insert + $_FILES);

		if($data_insert['dt_cupom'] < '2016-11-30' || $data_insert['dt_cupom'] > '2016-12-25')
			throw new Ex_Validate([
				'dt_cupom' => 'O campo <b>DATA</b> deve estar dentro do intervalo válido de compra: De 30/11/2016 a 25/12/2016.'
			]);

		if($data_insert['dt_cupom'] > $agora)
			throw new Ex_Validate([
				'dt_cupom' => 'O campo <b>DATA</b> deve não pode ser uma data futura.'
			]);
		if ($data_insert['tp_cupom'] != "F") {
			$existe = Model::search("SELECT 1 as existe FROM t_cupom c WHERE c.id_usuario = ? and c.cd_cupom = ? and c.dt_cupom = ? and ic_status in ('P', 'A')", [$user_log->id_usuario, $data_insert['cd_cupom'], $data_insert['dt_cupom']]);
		}
		else {
			$existe = [];
		}

		if (!empty($existe)) {
			throw new Ex_Validate([
				'cd_cupom' => 'Você já cadastrou um cupom com este código.'
			]);
		}
		$id_cupom = null;
		$trans = $mypdo->transaction(function() use($id_cupom, $data_insert, $complement){
			$m_cupom = new Model('t_cupom', 'id_cupom');

			$id_cupom = $m_cupom->insert($data_insert, [
				'id_usuario',
				'cd_cupom',
				'tp_cupom',
				'vl_cupom',
				'dt_cupom',
				'dt_cadastro',
				'vl_disponivel',
				'ic_cartao',
			]);

			$nm_arquivo = fastUpload(
				dirname(__FILE__) . '/../img/',
				'cupom/',
				$_FILES['img_cupom'],
				$id_cupom
			);

			if(!$nm_arquivo)
				throw new Ex_User("Falha no upload " . $complement);

			$m_cupom->update(compact('nm_arquivo'), $id_cupom);

			return true;
		}, function() use($id_cupom){
			$nm_arquivo = $id_cupom . '.jpg';
			$file_path = dirname(__FILE__) . '/../img/cupom/' . $nm_arquivo;

			if(is_file($file_path))
				unlink($file_path);
		});

		if ($trans == true) {
			$numeros = (new S_NumeroSorte())->processarNumerosDaSorte($user_log->id_usuario);
		}
		// return $trans;
		return ["success" => "1", "message" => "Cupom cadastrado com sucesso", "numeros" => $numeros];
		// TODO:
		// 1) Verificar se a data está dentro do período de compras
	}
}