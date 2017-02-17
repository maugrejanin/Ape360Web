<?php
Autoload::clear();
require_once(dirname(__FILE__) . "/../lib/phpmailer/PHPMailerAutoload.php");

class MyMail extends PHPMailer {

	protected $mailTest;
	protected $mailTestTo;
	protected $mailTestToName;
	public $mensagemErro;

	public function __construct() 
   	{ 
    	parent::__construct();
    	$this->setLanguage(CONFIG_MAIL_LANGUAGE); 
    	$this->CharSet = CONFIG_MAIL_CHARSET;

  		// $this->SMTPDebug = 3;
		// $this->Debugoutput = 'error_log';

    	$this->IsSMTP();
    	// $this->IsSendmail();  // tell the class to use Sendmail
    	
    	$this->Host = CONFIG_SMTP_SERVER;
		$this->SMTPAuth = CONFIG_SMTP_AUTH;
		$this->Username = CONFIG_SMTP_USER;
		$this->Password = CONFIG_SMTP_PWD;
		$this->SMTPSecure = CONFIG_SMTP_SECURE;
		$this->Port = CONFIG_SMTP_PORT;
		
		$this->setFrom(CONFIG_MAIL_FROM, CONFIG_MAIL_FROM_NAME);
		$this->addReplyTo(CONFIG_MAIL_REPLY_TO, CONFIG_MAIL_REPLY_TO_NAME);

		$this->mailTest = CONFIG_MAIL_TEST;
		$this->mailTestTo = CONFIG_MAIL_TEST_TO;
		$this->mailTestToName = CONFIG_MAIL_TEST_TO_NAME;
		$this->mensagemErro = "";
		
		$this->IsHTML(CONFIG_MAIL_HTML);
    }

	public function sendMail ($assunto, $urlHtml, $textoHtml, $textoAlternativo) {
		if ($this->mailTest) {
			$this->ClearAllRecipients();
			$this->AddAddress($this->mailTestTo, $this->mailTestToName); //, $nomesDestinatarios[$i]);
		}

		$this->Subject  = $assunto;
		
		if (!isset($urlHtml) || empty($urlHtml)) {
			$this->Body = $textoHtml;
		}
		else {
			$this->msgHTML($this->GetEmailTemplate($urlHtml), dirname(__FILE__));
		}
		$this->AltBody = $textoAlternativo;
		$enviado = false;
		try{
			$enviado = @$this->send();
		}
		catch(Exception $ex){
			MyPDO::errorLog($ex);
		}
		finally {
			$this->mensagemErro = "no errors";
			if ($enviado == false) {
				$this->mensagemErro = $this->ErrorInfo;
				MyPDO::errorLog(new Exception($this->ErrorInfo));
			}
			
			$this->ClearAllRecipients();
			$this->ClearAttachments();		
			return $enviado;
		}
	}

	protected function getEmailTemplate($urlHtml) {
		$data = "";
		if ( function_exists("curl_init") ) {
			$ch = curl_init($urlHtml);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_HEADER, true);
		    
		    $data = curl_exec($ch);
		    $raw_headers = substr($data, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		    $headers = preg_split("/[\n\r]+/", trim($raw_headers));
		    $data = substr($data, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		    curl_close($ch);
	    }
	    return $data;
	}
}

?>