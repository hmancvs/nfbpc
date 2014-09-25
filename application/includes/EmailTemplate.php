<?php
/**
 * Class that uses a Zend_View to creat an email template 
 */

class EmailTemplate extends Zend_View {
	
	public function __construct($config = array()) {
		parent::__construct($config = array());
		// add the path to the scripts
		$this->setScriptPath(APPLICATION_PATH."/views/scripts/email/"); 
		$this->appname = getAppName();
		
		$config = Zend_Registry::get("config"); 
	
		// default sign off name and email
		$mail = Zend_Registry::get('mail'); 
		$default_sender = $mail->getDefaultFrom(); 
		//$this->signoffname = $default_sender['name'];
		//$this->signoffemail = $default_sender['email'];  
		$this->signoffname = getDefaultAdminName();
		$this->signoffemail = getDefaultAdminEmail(); 
		$this->logourl = $this->serverUrl($this->baseUrl('images/logo.png'));
	}
}

?>