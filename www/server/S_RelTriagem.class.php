<?php

class S_RelTriagem{

	public function init(){
		$data_rel = Model::search(
			"SELECT 
				id_cupom, 
				cd_cupom, 
				tp_cupom, 
				vl_cupom, 
				dt_cupom, 
				dt_cadastro, 
				nm_arquivo
			FROM cupom c
			WHERE ic_status = ?", [
				CUPOM_STATUS_PENDENTE
			]
		);

		return compact('data_rel');
	}

	public function approve($id_cupom){
		global $mypdo;

		return $mypdo->transaction(function() use($id_cupom){

			$record_cupom = Model::search("SELECT id_usuario FROM cupom WHERE id_cupom = ?", [$id_cupom], false);

			if(!$record_cupom)
				throw new Ex_User('Cupom não encontrado');
				
			$this->changeCupomStatus($id_cupom, CUPOM_STATUS_APROVADO);
			$s_numero = new S_NumeroSorte();

			return $s_numero->processarNumerosDaSorte($record_cupom['id_usuario']);

		});
	}

	public function disapprove($id_cupom){
		return $this->changeCupomStatus($id_cupom, CUPOM_STATUS_REPROVADO);
	}

	public function xls(){
		extract($this->init());//$data_rel

		if(sizeof($data_rel) == 0)
			throw new Ex_User("Dados insuficientes para o download da planilha");

		Sheet::run(
			'Relatório de cupons pendentes.xls',
			$data_rel,
			isset($_POST['columns'])? $_POST['columns']: []
		);
	}

	private function changeCupomStatus($id_cupom, $new_status){
		if(!$this->validStatus($new_status))
			return false;

		return Model::exec("UPDATE cupom SET ic_status = ? WHERE id_cupom = ?", [
			$new_status, 
			$id_cupom
		]);
	}

	private function validStatus($new_status){
		return in_array($new_status, [
			CUPOM_STATUS_APROVADO,
			CUPOM_STATUS_PENDENTE,
			CUPOM_STATUS_REPROVADO,
		]);
	}

}