<?php

class SignupController extends IndexController {
	
	function createAction() {
		// the group to which the user is to be added
		$formvalues = $this->_getAllParams();
		// debugMessage($formvalues);
		$this->_setParam("action", ACTION_CREATE); 
		$this->_setParam('entityname', 'Member');
		$this->_setParam('firstname', ucfirst($formvalues['firstname']));
		$this->_setParam('lastname', ucfirst($formvalues['lastname']));
		
		if(!isEmptyString($this->_getParam('spamcheck')) || !is_numeric($this->_getParam('phone')) || isEmptyString($this->_getParam('agreedtoterms')) || isEmptyString($this->_getParam('gender'))){
			// debugMessage('failed');
			$session = SessionWrapper::getInstance(); 
			$session->setVar(ERROR_MESSAGE, 'Spam detected. Try again later'); 
			$this->_helper->redirector->gotoUrl($this->view->baseUrl('signup'));
		}
		
		// debugMessage($this->_getAllParams()); exit();
		parent::createAction();
	}
	
	function processinviteAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		$session = SessionWrapper::getInstance(); 
		$id = decode($formvalues['id']);
		$formvalues['id'] = $id;
		// debugMessage($formvalues);
		
		$user = new Member();
		$user->populate($id);
		$user->processPost($formvalues);
		
		// debugMessage('error > '.$user->getErrorStackAsString()); debugMessage($user->toArray()); exit();
		$user->setPassword(sha1($formvalues['password']));
		$user->setActivationDate(date('Y-m-d H:i:s'));
		$user->setActivationKey('');
		$user->setStatus(1);
		$user->setAgreedToTerms(1);
		// debugMessage($user->toArray()); debugMessage("Error > ".$user->getErrorStackAsString()); exit();
		$user->save();
		
		$url = $this->view->serverUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID())));
		$usecase = '1.16';
		$module = '1';
		$type = USER_ACTIVATE;
		$details = "User Profile <a href='".$url."' class='blockanchor'>".$user->getName()."</a> activated";
		
		$browser = new Browser();
		$audit_values = $session->getVar('browseraudit');
		$audit_values['module'] = $module;
		$audit_values['usecase'] = $usecase;
		$audit_values['transactiontype'] = $type;
		$audit_values['userid'] = $session->getVar('userid');
		$audit_values['url'] = $url;
		$audit_values['transactiondetails'] = $details;
		$audit_values['status'] = "Y";
		// debugMessage($audit_values);
		$this->notify(new sfEvent($this, $type, $audit_values));
		
		$session->setVar(SUCCESS_MESSAGE, "You can now login using your Username or Password");
		$this->clearSession();
		$session->setVar(SUCCESS_MESSAGE, "You can now login using your Username or Email or Phone and Password");
		// $loginurl = $this->view->baseUrl("user/checklogin/email/".$user->getEmail().'/password/'.$formvalues['password']);
		$loginurl = $this->view->baseUrl("user/login");
		$this->_helper->redirector->gotoUrl($loginurl);
		
		return false;
	}
	
	function activateAction() {
		$session = SessionWrapper::getInstance(); 
		$formvalues = $this->_getAllParams();
		$isphoneactivation = false;
		if(!isArrayKeyAnEmptyString('id', $formvalues)){
			// debugMessage($formvalues);
			$user = new Member(); 
			$user->populate(decode($formvalues['id']));
			// debugMessage($user->toArray());
			
			if ($user->isUserActive() && isEmptyString($user->getActivationKey())) {
				// account already activated 
	    		$session->setVar(ERROR_MESSAGE, 'Account is already activated. Please login.'); 
				$this->_helper->redirector->gotoUrl($this->view->baseUrl("user/login"));
			}
			
			$this->_setParam(URL_FAILURE, encode($this->view->baseUrl("signup/confirm/id/".encode($user->getID()))));
			$key = $this->_getParam('actkey');
			
			$this->view->result = $user->activateAccount($key, $isphoneactivation);
			// exit();
			if (!$this->view->result) {
				// activation failed
				$this->view->message = $user->getErrorStackAsString();
				$session->setVar(FORM_VALUES, $this->_getAllParams());
	    		$session->setVar(ERROR_MESSAGE, $user->getErrorStackAsString()); 
				$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
			}
		}
		if(!isArrayKeyAnEmptyString('act_byphone', $formvalues)){
			// debugMessage('activated via phone');
			$isphoneactivation = true;
			$this->_helper->redirector->gotoUrl($this->view->baseUrl("user/login/tab/activate/act_byphone/1"));
		}
		$this->_helper->redirector->gotoUrl($this->view->baseUrl("user/login"));
	}
	
	function activateaccountAction() {
		$session = SessionWrapper::getInstance(); 
		// replace the decoded id with an undecoded value which will be used during processPost() 
		$id = decode($this->_getParam('id')); 
		$this->_setParam('id', $id); 
		
		$user = new Member(); 
		$user->populate($id);
		$user->processPost($this->_getAllParams());
		
		if ($user->hasError()) {
			$session->setVar(FORM_VALUES, $this->_getAllParams());
    		$session->setVar(ERROR_MESSAGE, $user->getErrorStackAsString()); 
			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
		}
		
		$result = $user->activateAccount($this->_getParam('activationkey'));
		if ($result) {
			// go to sucess page 
			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS))); 
		} else {
			$session->setVar(FORM_VALUES, $this->_getAllParams());
    		$session->setVar(ERROR_MESSAGE, $user->getErrorStackAsString()); 
			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
		}
	}
	
	
	function confirmAction() {
		
	}
	
	function activationerrorAction() {
		
	}
	
	function activationconfirmationAction() {
		
	}
	
	function inviteconfirmationAction() {
		
	}
	
	function getcaptchaAction(){
		$this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
		$session = SessionWrapper::getInstance(); 
		
		$string = '';
		for ($i = 0; $i < 5; $i++) {
			$string .= chr(rand(97, 122));
		}
		$session->setVar('random_number', $string);
		// $_SESSION['random_number'] = $string;

		$dir = $this->view->baseUrl("images/fonts/");
		//$dir = APPLICATION_PATH."/../public/images/fonts";
		// debugMessage($dir);
		$image = imagecreatetruecolor(165, 50);

		// random number 1 or 2
		
		echo $session->getVar('random_number');
	}
	function captchaAction() {
		$this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
		$session = SessionWrapper::getInstance();
		//include('dbcon.php');
		// debugMessage('scre is '.strtolower($this->_getParam('code')));
		// debugMessage('rand is '.strtolower($session->getVar('random_number')));
		if(strtolower($this->_getParam('code')) == strtolower($session->getVar('random_number'))){
			echo 1;// submitted 
		} else {
			echo 0; // invalid code
		}
	}
	
	function checkusernameAction(){
		$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
		$formvalues = $this->_getAllParams();
		$username = trim($formvalues['username']);
		// debugMessage($formvalues);
		$user = new Member();
		if(!isArrayKeyAnEmptyString('userid', $formvalues)){
			$user->populate($formvalues['userid']);
		}
		if($user->usernameExists($username)){
			echo '1';
		} else {
			echo '0';
		}
		
	}
	
	function checkemailAction(){
		$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
		$formvalues = $this->_getAllParams();
		$email = trim($formvalues['email']);
		// debugMessage($formvalues);
		$user = new Member();
		if(!isArrayKeyAnEmptyString('userid', $formvalues)){
			$user->populate($formvalues['userid']);
		}
		if($user->emailExists($email)){
			echo '1';
		} else {
			echo '0';
		}
	}
	
	function checkphoneAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		 
		$formvalues = $this->_getAllParams();
		$phone = trim($formvalues['phone']);
		// debugMessage($formvalues);
		$user = new Member();
		if(!isArrayKeyAnEmptyString('userid', $formvalues)){
			$user->populate($formvalues['userid']);
		}
		if($user->phoneExists($phone)){
			echo '1';
		} else {
			echo '0';
		}
	}
}
