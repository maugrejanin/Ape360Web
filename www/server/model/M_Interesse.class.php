<?php

class M_Interesse extends Model{

	public function __construct(){
		parent::__construct('t_interesse', [
			'id_usuario',
			'id_evento'
		]);
	}

}