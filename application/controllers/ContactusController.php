<?php

class ContactusController extends IndexController  {
	
	function indexAction() {
		$this->_redirect($this->view->baseUrl('notifications/index'));
	}
	
	/**
	 * Sends the details of the support form by email 
	 */
	public function processcontactusAction() {
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		// debugMessage($formvalues);
		
		$profile = new Member();
		if($profile->sendContactNotification($formvalues)){
			// after send events
			$session->setVar(SUCCESS_MESSAGE, "Thank you for contacting us. We shall get back to you shortly.");
			
			$this->_redirect($this->view->baseUrl('contactus/index/result/success'));
		} else {
			$session->setVar(ERROR_MESSAGE, 'Sorry! An error occured in sending the message. Please try again later ');
			
			$this->_redirect($this->view->baseUrl('contactus/index/result/error'));
		}
	}
}

