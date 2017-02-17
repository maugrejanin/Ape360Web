<?php

class MyPDO extends \PDO{

    protected $transactionCounter = 0;

    public function transaction(callable $function, callable $catch = null){
    	try{
    		$this->beginTransaction();
    			$return = $function();
    		$this->commit();

    		return $return;
    	}catch(Exception $e){
    		$this->rollback();

    		if($catch)
    			$catch();

    		if($e instanceof PDOException)
    			$this->treatTransactionException($e);

    		throw $e;
    	}
    }

    private function treatTransactionException(PDOException $pdoe){
		$sql_state = $pdoe->errorInfo[1];

		switch ($sql_state) {
			case SQLSTATE_TRANSACTION_MANY_CONCURRENTS:
			case SQLSTATE_TRANSACTION_TIME_OUT:
				throw new Ex_User('Foi atingido o tempo limite de processamento. Isso pode ocorrer em horários de muito acesso aos nossos serviços. Por favor tente novamente ou, se o erro persistir, tente mais tarde.');
			break;
		}

    }

    public function beginTransaction() {
        if (!$this->transactionCounter++) {
            return parent::beginTransaction();
        }
        $transCmd = $this->prepare('SAVEPOINT trans'.$this->transactionCounter);
        $transCmd->execute();
        return $this->transactionCounter >= 0;
    }

    public function commit() {
        if (!--$this->transactionCounter) {
            return parent::commit();
        }
        return $this->transactionCounter >= 0;
    }

    public function rollback() {
        if (--$this->transactionCounter) {
            $transCmd = $this->prepare('ROLLBACK TO trans'. ($this->transactionCounter + 1) );
        	$transCmd->execute();
            return true;
        }
        return parent::rollback();
    }

    static public function errorLog($e) {
    	global $config;

    	if(gettype($e) == 'string')
    		$e = new Exception($e);

		$erroId = 0;
		try {
			$errPdo = new \PDO(   'mysql:host=' . CONFIG_DB_HOST . ';dbname=' . CONFIG_DB_NAME . ';charset=' . CONFIG_DB_CHARSET, 
				CONFIG_DB_USER, 
				CONFIG_DB_PWD, 
				array(
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, 
					\PDO::ATTR_PERSISTENT => CONFIG_DB_PERSISTENT
				)
			);
			$dtErro = new DateTime("now", new DateTimeZone( 'America/Sao_Paulo' ) );
			$idUsuario = (isset($_SESSION["UsrId"]) ? $_SESSION["UsrId"] : (isset($_SESSION["id_usuario"]) ? $_SESSION["id_usuario"] : null));
			$errCmd = $errPdo->prepare('INSERT INTO t_erro (dt_erro, id_usuario, ds_message, id_code, nm_file, nr_line, ds_trace, ip_usuario, ds_post) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'); 
			$paramNum = 1;
			$errCmd->bindValue($paramNum++, $dtErro->format('Y-m-d H:i:s'));
			$errCmd->bindValue($paramNum++, $idUsuario);
			$errCmd->bindValue($paramNum++, $e->getMessage());
			$errCmd->bindValue($paramNum++, $e->getCode());
			$errCmd->bindValue($paramNum++, $e->getFile());
			$errCmd->bindValue($paramNum++, $e->getLine());
			$errCmd->bindValue($paramNum++, $e->getTraceAsString());
			$errCmd->bindValue($paramNum++, get_ip_address());
			$errCmd->bindValue($paramNum++, json_encode($_POST));
			$errCmd->execute();
			$erroId = $errPdo->lastInsertId();
			unset($errPdo);
		} catch (Exception $ex) {
			// TODO: método de log de erro alternativo.
		}
		return $erroId;
	}

	static public function recordAction ($nomeEvento, $pagina, $detalhe) {
		if (isset($_SESSION['UsrId'])) {
			global $mypdo;
			try {
				$now = new DateTime('now', new DateTimeZone( 'America/Sao_Paulo' ) );
				$myCommand = $mypdo->prepare('INSERT INTO t_log_acao (dt_log_acao, id_usuario, nm_evento, nm_pagina, ds_log_acao) values (?,?,?,?,?)'); 
				$myCommand->bindValue(1, $now->format('Y-m-d H:i:s'));
				$myCommand->bindValue(2, $_SESSION['UsrId']);
				$myCommand->bindValue(3, $nomeEvento);
				$myCommand->bindValue(4, $pagina);
				$myCommand->bindValue(5, $detalhe);
				$myCommand->execute();
			} catch(Exception $ex){
				MyPDO::errorLog($ex);
			}
		}
	}
    
}

try {
    $mypdo = new MyPDO(   'mysql:host=' . CONFIG_DB_HOST . ';dbname=' . CONFIG_DB_NAME . ';charset=' . CONFIG_DB_CHARSET, 
		CONFIG_DB_USER, 
		CONFIG_DB_PWD, [
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, 
			\PDO::ATTR_PERSISTENT => CONFIG_DB_PERSISTENT
		]
	);
} catch(\PDOException $e){
	//MyPDO::errorLog($e); -- não faz sentido persistir informações no banco que deu erro de conexão...
    //throw new Ex_Specific($e->getMessage(), SPECIFIC_ERROR_DB_CONECT); -- Só uma ideia, mas que não parece muito boa...
    throw $e;    
}