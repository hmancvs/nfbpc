<?php

class UserController extends IndexController  {

    function checkloginAction() {
    	$session = SessionWrapper::getInstance();
    	$formvalues = $this->_getAllParams();
    	// debugMessage($formvalues); 
    	# check that an email has been provided
		if (isEmptyString(trim($this->_getParam("email")))) {
			$session->setVar(ERROR_MESSAGE, $this->_translate->translate("profile_email_error")); 
			$session->setVar(FORM_VALUES, $this->_getAllParams());
			// return to the home page
    		$this->_helper->redirector->gotoSimpleAndExit('login', "user");
		}
		if (isEmptyString(trim($this->_getParam("password")))) {
			$session->setVar(ERROR_MESSAGE, $this->_translate->translate("profile_password_error")); 
			$session->setVar(FORM_VALUES, $this->_getAllParams());
			// return to the home page
    		$this->_helper->redirector->gotoSimpleAndExit('login', "user");
		}
		
		# check which field user is using to login. default is username
		$credcolumn = "username";
    	$login = (string)trim($this->_getParam("email"));
    	
    	# check if credcolumn is phone 
    	if(strlen($login) == 10 && is_numeric(substr($login, -6, 6))){
    		$credcolumn = 'phone';
    	}
    	
    	# check if credcolumn is emai
    	$validator = new Zend_Validate_EmailAddress();
		if ($validator->isValid($login)) {
        	$usertable = new Member();
     		if($usertable->findByEmail($login)){
           		$credcolumn = 'email';
            }
        }
        /* debugMessage($credcolumn); // exit();
        debugMessage(decode($formvalues['redirecturl']));  */
        
        if($credcolumn == 'email' || $credcolumn == 'username'){
	        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get("dbAdapter"));
			// define the table, fields and additional rules to use for authentication 
			$authAdapter->setTableName('member');
			$authAdapter->setIdentityColumn($credcolumn);
			$authAdapter->setCredentialColumn('password');
			$authAdapter->setCredentialTreatment("sha1(?) AND status = '1'"); 
			// set the credentials from the login form
			$authAdapter->setIdentity($login);
			$authAdapter->setCredential($this->_getParam("password")); 
	
			// new class to audit the type of Browser and OS that the visitor is using
			$browser = new Browser();
			$audit_values = array("browserdetails" => $browser->getBrowserDetailsForAudit());
			
			if(!$authAdapter->authenticate()->isValid()) { debugMessage('failed');
				// add failed login to audit trail
	    		$audit_values['transactiontype'] = USER_LOGIN;
	    		$audit_values['success'] = "N";
	    		$audit_values['transactiondetails'] = "Login for user with email '".$this->_getParam("email")."' failed. Invalid username or password";
				$this->notify(new sfEvent($this, USER_LOGIN, $audit_values));
				
				// return to the home page
				if(!isArrayKeyAnEmptyString(URL_FAILURE, $formvalues)){
					$session->setVar(ERROR_MESSAGE, "Invalid Email or Username or Password. <br />Please Try Again."); 
					$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
				} else {
					$session->setVar(ERROR_MESSAGE, "Invalid Email or Username or Password. <br />Please Try Again."); 
					$this->_helper->redirector->gotoSimple('login', "user");
				}
	    		return false; 
			}
			
			// user is logged in sucessfully so add information to the session 
			$user = $authAdapter->getResultRowObject();
			$useraccount = new Member(); 
			$useraccount->populate($user->id);
        }
		
		/* debugMessage($useraccount->toArray());
		exit(); */
		$session->setVar("userid", $user->id);
		$session->setVar("type", $useraccount->getType());

		// clear user specific cache, before it is used again
    	$this->clearUserCache();
    
		// Add successful login event to the audit trail
		$audit_values['transactiontype'] = USER_LOGIN;
    	$audit_values['success'] = "Y";
		$audit_values['userid'] = $useraccount->getID();
		$audit_values['executedby'] = $useraccount->getID();
   		$audit_values['transactiondetails'] = "Login for user with id '".$this->_getParam("email")."' successful";
		$this->notify(new sfEvent($this, USER_LOGIN, $audit_values));
		
		if (isEmptyString($this->_getParam("redirecturl"))) {
			# forward to the dashboard
			// $this->_helper->redirector->gotoSimple("index", "dashboard");
			$this->_helper->redirector->gotoSimple("list", "member");
		} else {
			# redirect to the page the user was coming from
			if(!isEmptyString($this->_getParam(SUCCESS_MESSAGE))) {
				$successmessage = decode($this->_getParam(SUCCESS_MESSAGE));
				$session->setVar(SUCCESS_MESSAGE, $successmessage);
			}
			$this->_helper->redirector->gotoUrl(decode($this->_getParam("redirecturl")));
		}
    }
    
	/**
     * Action to display the Login page 
     */
    public function loginAction()  {
        // do nothing 
        $session = SessionWrapper::getInstance(); 
   		if(!isEmptyString($session->getVar('userid'))){
			$this->_helper->redirector->gotoUrl($this->view->baseUrl("dashboard"));	
		} 
    }
    public function recoverpasswordAction() {
    	
    }
    public function processrecoverpasswordAction(){
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
		$session = SessionWrapper::getInstance();
		
		// debugMessage($this->_getAllParams());
    	if (!isEmptyString($formvalues['email'])) {
    		// process the password recovery 
    		$user = new Member(); 
    		$useraccount = new Member(); 
    		// $user->setEmail($this->_getParam('email')); 
	    	# check which field user is using to login. default is username
			$credcolumn = "username";
	    	$login = (string)$formvalues['email'];
	    	
	    	# check if credcolumn is phone 
	    	if(strlen($login) == 10 && is_numeric(substr($login, -6, 6))){
	    		$credcolumn = 'phone';
	    	}
	    	
	    	# check if credcolumn is emai
	    	$validator = new Zend_Validate_EmailAddress();
			if ($validator->isValid($login)) {
	        	$credcolumn = 'email';
	        }
        	// debugMessage($credcolumn);
        	$userfond = false;
	        switch ($credcolumn) {
	        	case 'email':
	        		if($useraccount->findByEmail($formvalues['email'])){
	        			$userfond = true;
	        			// debugMessage($useraccount->toArray());
	        		}
	        		break;
	        	case 'phone':
	        		$useraccount = $user->populateByPhone(getFullPhone($formvalues['email']));
	        		if(!isEmptyString($useraccount->getID())){
	        			$userfond = true;
	        			// debugMessage($useraccount->toArray());
	        		}
	        		break;
	        	case 'username':
	       			if($useraccount->findByUsername($formvalues['email'])){
	        			$userfond = true;
	        			// debugMessage($useraccount->toArray());
	        		}
	        		break;
	        	default:
	        		break;
	        }
    		// debugMessage($user->toArray());
	        if(!isEmptyString($useraccount->getID())){
    			$useraccount->recoverPassword();
    			// send a link to enable the user to recover their password 
    			$this->_helper->redirector->gotoUrl($this->view->baseUrl("user/recoverpasswordconfirmation"));	
    		} else {
    			// send an error message that no user with that email was found 
    			$session = SessionWrapper::getInstance(); 
    			$session->setVar(FORM_VALUES, $this->_getAllParams()); 
    			$session->setVar(ERROR_MESSAGE, $this->_translate->translate("profile_user_invalid_error"));
    			$this->_helper->redirector->gotoUrl($this->view->baseUrl("user/recoverpassword"));
    		}
    	}
    	// exit();
    }
    
    public function resetpasswordAction() {
    	$user = new Member(); 
    	$user->populate(decode($this->_getParam('id')));

    	// verify that the activation key in the url matches the one in the database
	    if ($user->getActivationKey() != $this->_getParam('actkey')) {
    		// send a link to enable the user to recover their password 
    		$this->_helper->redirector->gotoUrl($this->view->baseUrl("user/activationerror"));
    	} 
    	
    }
    
    public function processresetpasswordAction(){
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$session = SessionWrapper::getInstance(); 
		$formvalues = $this->_getAllParams();
		// debugMessage($formvalues);
		
		$user = new Member(); 
    	$user->populate(decode($this->_getParam('id')));
    	// debugMessage($user->toArray());
    	$user->setUsername($formvalues['username']);
    	$user->setIsActive(1);
      	$user->setAgreedToTerms(1);
      	if(isEmptyString($user->getActivationDate())){
      		$startdate = date("Y-m-d H:i:s");
			$user->setActivationDate($startdate);
      	}
    	// exit();
   		if ($user->resetPassword($this->_getParam('password'))) {
    		// send a link to enable the user to recover their password 
    		$session->setVar(SUCCESS_MESSAGE, "Sucessfully saved. You can now log in using your new Password");
    		$this->_helper->redirector->gotoUrl($this->view->baseUrl("user/login"));
    	} else {
    		// echo "cannot reset password"; 
    		// send an error message that no user with that email was found 
    		$session = SessionWrapper::getInstance(); 
    		$session->setVar(ERROR_MESSAGE, $user->getErrorStackAsString());
    		$session->setVar(FORM_VALUES, $this->_getAllParams());
    		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
    	}
    }
    public function resetpasswordconfirmationAction() {}
    
    public function activationerrorAction() {}
    	
    public function recoverpasswordconfirmationAction() {}
	
	public function changepasswordconfirmationAction() {}
    
	/**
     * Action to display the Login page 
     */
    public function logoutAction()  {
    	$this->clearSession();
        // redirect to the login page 
        $this->_helper->redirector("login", "user");
    }
    
	public function changeemailAction(){
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
    }
}
