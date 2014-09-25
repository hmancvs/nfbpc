<?php

class TestController extends IndexController  {
	
    
    function testmailAction(){
    	$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	    
    	$user = new Member();
    	$user->populate(2);
    	$user->sendProfileInvitationNotification();
    	debugMessage($user->toArray());
    }
    
	function emailAction(){
    	$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	    
	    sendTestMessage('test farmis email','this is a test message please ignore - '.APPLICATION_ENV);
    }
    
    function popAction(){
    	$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
    	
    	$test = array(
    		"clientid" => 12,
    		"status" => 1,
    		"userid" => 4,
    		"role" => 1,
    		"startdate" => "Mar 4, 2014"
    	);
    	$ass = new Assignment();
    	$ass->processPost($test);
    	debugMessage($ass->toArray());
    	debugMessage($ass->getErrorStackAsString());
    	
    	// $ass->save();
    }
}
