<?php

class S_RelNumeroSorte{

	public function init(){
		$data_rel = Model::search(
			"SELECT 
				GROUP_CONCAT(cns.id_numero SEPARATOR ', ') AS id_numero, 
				c.id_cupom, 
				c.cd_cupom, 
				c.tp_cupom, 
				c.vl_cupom, 
				c.dt_cupom, 
				c.dt_cadastro, 
				c.nm_arquivo, 
				c.ic_status 
			FROM t_cupom_numero_da_sorte cns
			INNER JOIN t_cupom c ON c.id_cupom = cns.id_cupom
			GROUP BY c.id_cupom"
		);

		return compact('data_rel');
	}

	public function xls(){
		extract($this->init());//$data_rel

		if(sizeof($data_rel) == 0)
			throw new Ex_User('Dados insuficientes para o download da planilha');

		Sheet::run(
			'Relatório de cupons.xls',
			$data_rel,
			isset($_POST['columns'])? $_POST['columns']: []
		);
	}

}