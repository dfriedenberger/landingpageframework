<?php

  

	
class EmailSender
{

    public function __construct($config) {
	    $this->mailFrom = $config['noreply'];
		$this->admin = $config['admin'];
	}

    public function mailTo($to) {
		$this->mailTo = $to;
    }
	
	public function mailToAdmin() {
		$this->mailTo = $this->admin;
    }
	
	
	public function subject($subject) {
    	$this->mailSubject = '=?UTF-8?B?'.base64_encode($subject).'?=';
    }
	
	public function body($body) {
    	$this->mailBody = $body;
    }
	
	public function send()
	{
		$headers = 'From: Dialogo<'. $this->mailFrom  . ">\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		
		//simulate
		
        file_put_contents("C:\\temp\\emails.htm", $this->mailBody );
		//mail( $this->mailTo, $this->mailSubject, $this->mailBody , $headers);
	}
}
?>