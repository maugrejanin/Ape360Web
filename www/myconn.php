<?php
header('Content-type: text/html; charset=UTF-8');
require_once 'config.php';
try{

    $myConn = new \PDO(   'mysql:host=' . $config['dbHost'] . ';dbname=' . $config['dbName'] . ';charset=' . $config['dbCharset'], 
                        $config['dbUser'], 
                        $config['dbPwd'], 
                        array(
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, 
                            \PDO::ATTR_PERSISTENT => $config['dbPersistent']
                        )
                    );
//var_dump($myConn);
}
catch(\PDOException $ex){
    print('Erro: ' . $ex->getMessage());
	die;
}
?>