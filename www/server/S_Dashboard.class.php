<?php 

/**
* @title Dashboard
* @description Tela com os macro-dados do sistema
* @restriction A
*/

class S_Dashboard{
	
	public function init(){
		$macro_data = 
			Model::search(
				"SELECT 
					COUNT(*) AS qt_cupom, 
					SUM(vl_cupom) AS vl_total,
					SUM(vl_cupom)/COUNT(*) AS vl_medio,
					SUM(MOD(vl_cupom, 50)) AS vl_remain
				FROM cupom", [], false
			) +
			Model::search(
				"SELECT 
					COUNT(*) AS qt_usuario
				FROM usuario
				WHERE id_usuario_perfil NOT IN (?, ?)", [
					PERMIT_ADMIN, 
					PERMIT_TECHNICIAN
				], false
			) +
			Model::search(
				"SELECT 
					COUNT(*) AS qt_numero
				FROM numero_da_sorte", [], false
			);

		return compact('macro_data');
	}

}