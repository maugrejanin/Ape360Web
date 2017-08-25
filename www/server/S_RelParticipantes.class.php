<?php 

class S_RelParticipantes{

	public function init(){
		$data_rel = Model::search(
			"SELECT 
				CONCAT(u.ds_nome, IFNULL(u.ds_sobrenome, '')) AS ds_nome, 
				u.ds_email, 
				u.ds_telefone, 
				u.dt_cadastro, 
				uc.id_usuario, 
				uc.ds_endereco, 
				uc.ds_cidade, 
				uc.ds_uf, 
				uc.ds_cpf, 
				uc.ds_rg, 
				uc.ds_celular, 
				uc.ds_sexo, 
				uc.ds_nascimento, 
				uc.ds_nome_cartao, 
				uc.ds_nome_amigo, 
				uc.ds_endereco_amigo, 
				uc.ds_cidade_amigo, 
				uc.ds_uf_amigo, 
				uc.ds_cpf_amigo, 
				uc.ds_rg_amigo, 
				uc.ds_telefone_amigo, 
				uc.ds_celular_amigo, 
				uc.ds_email_amigo, 
				uc.ds_sexo_amigo, 
				uc.ds_nascimento_amigo 
			FROM usuario_cadastro uc 
			INNER JOIN usuario u ON u.id_usuario = uc.id_usuario"
		);

		return compact('data_rel');
	}

	public function xls(){
		extract($this->init());//$data_rel

		if(sizeof($data_rel) == 0)
			throw new Ex_User("Dados insuficientes para o download da planilha");

		Sheet::run(
			'Relatório de participantes.xls',
			$data_rel,
			isset($_POST['columns'])? $_POST['columns']: []
		);
	}

}