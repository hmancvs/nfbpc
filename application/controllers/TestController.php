<?php

class TestController extends IndexController  {
	
	function emailAction(){
    	$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	    
	    sendTestMessage('test email','this is a test message please ignore - '.APPLICATION_ENV);
    }
    
    function smsAction(){
    	// disable rendering of the view and layout so that we can just echo the AJAX output
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    
    	$session = SessionWrapper::getInstance();
    	$formvalues = $this->_getAllParams();
    	// debugMessage($formvalues);
    	$phone = $this->_getParam('phone');
    	$message = $this->_getParam('msg');
    	$sender =  $this->_getParam('sender');
    	if(isEmptyString($message)){
    		$message = "Dear User, This is an automated test message from NFBPC via shreweb. confirm route - ".mktime();
    	}
    	if(isEmptyString($phone)){
    		$phone = getSmsTestNumber();
    	}
    	if(isEmptyString($sender)){
    		$sender = 'NFBPC';
    	}
    	
    	/* $body = 'SUBMIT_SUCCESS | 53d5cc68-6522-4562-1db4-bee4ae855484';
    	$msgarray = explode('|',trim($body));
    	if(!isArrayKeyAnEmptyString('0', $msgarray)){
    		$smsresult[1] = trim($msgarray[0]);
    	} else {
    		$smsresult[1] = '';
    	}
    	if(!isArrayKeyAnEmptyString('1', $msgarray)){
    		$smsresult[2] = trim($msgarray[1]);
    	} else {
    		$smsresult[2] = '';
    	} */
    	
    	// save to outbox too
    	$query = "INSERT INTO outbox (phone, msg, source, resultcode, smsid, datecreated, createdby, messageid) values ('".$phone."', '".$message."', 'NFBPC', '".$smsresult[1]."', '".$smsresult[2]."', '".getCurrentMysqlTimestamp()."', '".$session->getVar('userid')."', '1') ";
    	// debugMessage($query);
    	$conn = Doctrine_Manager::connection();
    	$conn->execute($query);
    	
    	//sendSMSMessage($phone, $message, $sender);
    }
    
    function jikAction(){
    	// disable rendering of the view and layout so that we can just echo the AJAX output
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance();
    	$formvalues = $this->_getAllParams(); debugMessage($formvalues);
    	
    	$path = APPLICATION_PATH.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'sms.php'; debugMessage($path);
    	// require_once $path;
    	// require_once APPLICATION_PATH.'/includes/sms.php'
    	
    	
    	$phone = $this->_getParam('phone');
    	$message = $this->_getParam('msg');
    	$sender =  $this->_getParam('sender');
    	if(isEmptyString($message)){
    		$message = "Dear User, This is JIK SMS message from NFBPC via solns4mobile. confirm route - ".mktime();
    	}
    	if(isEmptyString($phone)){
    		$phone = getSmsTestNumber();
    	}
    	if(isEmptyString($sender)){
    		$sender = 'NFBPC';
    	}
    	
    	$sol4mob_sms = new sms();
    	$sol4mob_sms->send_url= 'http://sms.jikuganda.org';			// The HTTP request URL used for messaging.
    	$sol4mob_sms->username= 'support@rbmafrica.com';									// The HTTP API username of your account.
    	$sol4mob_sms->password= 'FA2030relevance';									// The HTTP API password of your account.
    	$sol4mob_sms->msgtext= "This is a test message from api  Hellorld";								// The SMS Message text.
    	$sol4mob_sms->originator= 'NFBPC';							// The desired Originator of your message.
    	$sol4mob_sms->phone= '256701595279';									// The full International mobile number of the
    	// recipient excluding the leeding +.
    	echo $sol4mob_sms->send();											// Call method send() to send SMS Message.
    	
    }
}
