<?php

include_once(dirname(__FILE__) . '/Header.php');

class Server{

	static private $callback = false;

	public function init(){
		try{
			$url = Request::getUrl();	
			extract( Request::interpretUrl($url) );//controller, action, get, callback

			$controller_class_name = 'S_' . ucfirst($controller);
			self::$callback = $callback;

			// Permit::verify($controller_class_name, $action, $get);
			$server = new $controller_class_name();

			self::sendDataToClient(
				call_user_func_array([$server, $action], $get)//calling $action from $controller passing $get by parameter
			);
			
		}catch (Exception $e){
			Server::treatException($e);
		}
	}

	static public function treatException(Exception $e){
		$data_error['__trace'] = $e->getTrace();
		$data_error['__message'] = $e->getMessage();
		$data_error['__code'] = $e->getCode();
		$data_error['__status'] = false;
		$data_error['__post'] = $_POST;
		$data_error['__line'] = $e->getLine();
		$data_error['__file'] = $e->getFile();

		$type_exception = get_class($e);

		switch ($type_exception) {
			case 'PDOException':
				$data_error["__typeerror"] = "databank";
				MyPDO::errorLog($e);
			break;
			case 'Ex_User':
				$data_error["__typeerror"] = "user";
			break;
			case 'Ex_Detail':
				$data_error["__typeerror"] = "detail";
				$data_error['__message'] = json_decode($data_error['__message']);
			break;
			case 'Ex_Validate':
				$data_error["__typeerror"] = "validate";
				$data_error['__message'] = json_decode($data_error['__message']);
			break;
			case 'Ex_Authentication':
				$data_error["__typeerror"] = "authentication";
			break;
			case 'ErrorException':
				$data_error["__typeerror"] = "error";
				$data_error["__severity"] = $e->getSeverity();
				MyPDO::errorLog($e);
			break;
			case 'Ex_Permit':
				$data_error["__typeerror"] = "permit";
			break;
			case 'Ex_Specific':
				$data_error["__typeerror"] = "specific";
			break;
			default:
				$data_error["__typeerror"] = "generic";
				MyPDO::errorLog($e);
			break;
		}

		self::sendDataToClient($data_error);
	}

	static public function sendDataToClient($data){
		if (ob_get_length())
			ob_clean();

		if(self::$callback)
			print self::$callback . '(' . json_encode($data) . ')';
		else
			print json_encode($data);

		exit(0);
	}

	static public function sendIntractableError($message){
		MyPDO::errorLog( new Exception($message) );

		if (ob_get_length())
			ob_clean();

		print $message;

		exit(0);
	}

}

(new Server)->init();