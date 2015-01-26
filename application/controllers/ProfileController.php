<?php

class ProfileController extends SecureController  {
	
	/**
	 * @see SecureController::getResourceForACL()
	 *
	 * @return String
	 */
	function getResourceForACL() {
		return "User Account";
	}
	
	/**
	 * Override unknown actions to enable ACL checking 
	 * 
	 * @see SecureController::getActionforACL()
	 *
	 * @return String
	 */
	public function getActionforACL() {
	 	$action = strtolower($this->getRequest()->getActionName()); 
		if($action == "add" || $action == "other" || $action == "processother" || $action == "processadd" || 
			$action == "processvalidatephone" || $action == "validatephone" || $action == "changesettings") {
			return ACTION_CREATE; 
		}
		if($action == "changepassword" || $action == "processchangepassword" || $action == "changeusername" || 
			$action == "processchangeusername" || $action == "changeemail" || $action == "processchangeemail" ||
			$action == "changephone" || $action == "processchangephone" || $action == "processgps" || $action == "resetpassword" || $action == "inviteuser"){
			return ACTION_EDIT; 
		}
		if($action == "view" || $action == "picture" || $action == "processpicture" || $action == "uploadpicture" || $action == "croppicture"){
			return ACTION_VIEW;
		}
		if($action == "updatestatus"){
			return ACTION_DELETE;
		}
		return parent::getActionforACL();
    }
    
	public function addAction(){}
	
 	public function addsuccessAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
		
		$session->setVar(SUCCESS_MESSAGE, "Successfully saved");
   		if(!isArrayKeyAnEmptyString('successmessage', $formvalues)){
			$session->setVar(SUCCESS_MESSAGE, decode($formvalues['successmessage']));
		}
		
		/*$user = new Member();
		$id = $user->getLastInsertID();*/
		$this->_helper->redirector->gotoUrl($this->view->baseUrl("profile/list"));
    }
	
	public function createAction() {
		// the group to which the user is to be added
		$formvalues = $this->_getAllParams();
		
		// $this->_setParam('usergroups_groupid', array($formvalues['type']));
		
		parent::createAction();
	}
	
	public function indexAction(){
	}
	
	function changepasswordAction()  {
    	
    }
    
    function processchangepasswordAction(){
    	$session = SessionWrapper::getInstance(); 
        $this->_translate = Zend_Registry::get("translate"); 
    	if(!isEmptyString($this->_getParam('password'))){
	        $user = new Member(); 
	    	$user->populate(decode($this->_getParam('id')));
	    	// debugMessage($user->toArray());
	    	# Change password
	    	try {
	    		$user->changePassword($this->_getParam('oldpassword'), $this->_getParam('password'));
	    		$session->setVar(SUCCESS_MESSAGE, "Password successfully updated");
	    		$this->_redirect($this->view->baseUrl('index/profileupdatesuccess'));
	    	} catch (Exception $e) {
	    		$session->setVar(ERROR_MESSAGE, "Error in changing Password. ".$e->getMessage());
	    	}
		}
    }
    function changeusernameAction()  {
    	
    }
	function processchangeusernameAction()  {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance(); 
        $this->_translate = Zend_Registry::get("translate");
        $formvalues = $this->_getAllParams();
        
    	if(!isArrayKeyAnEmptyString('username', $formvalues)){
	        $user = new Member(); 
	    	$user->populate(decode($formvalues['id']));
	    	// debugMessage($user->toArray());
	    	
	    	if($user->usernameExists($formvalues['username'])){
	    		$session->setVar(ERROR_MESSAGE, sprintf($this->_translate->translate("profile_username_unique_error"), $formvalues['username']));
	    		return false;
	    	}
	    	# save new username
	    	$user->setUsername($formvalues['username']);
	    	$user->save();
	    	
	    	$view = new Zend_View();
	    	$url = $this->view->serverUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID())));
	    	$usecase = '1.17';
	    	$module = '1';
	    	$type = USER_CHANGE_EMAIL;
	    	$details = "Username for <a href='".$url."' class='blockanchor'>".$user->getName()."</a> Changed";
	    		
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
	    	
	   		$this->_redirect($this->view->baseUrl('index/profileupdatesuccess'));
		}
    }
    
	function changeemailAction()  {
		$session = SessionWrapper::getInstance(); 
		
		$formvalues = $this->_getAllParams();
		if(!isArrayKeyAnEmptyString('actkey', $formvalues) && !isArrayKeyAnEmptyString('ref', $formvalues)){
        	$newemail = decode($formvalues['ref']);
		
			$user = new Member();
			$user->populate(decode($formvalues['id']));
			$oldemail = $user->getEmail();
			
			# validate the activation code
			if($formvalues['actkey'] != $user->getActivationKey()){
				$session->setVar(ERROR_MESSAGE, "Invalid activation code specified for email activation");
				$failureurl = $this->view->baseUrl('profile/view/id/'.encode($user->getID()));
				$this->_helper->redirector->gotoUrl($failureurl);
			}
			
			$user->setActivationKey('');
			$user->setEmail($newemail);
			$user->setEmail2(''); 
			$user->save();
			
			$view = new Zend_View();
			$url = $this->view->serverUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID())));
			$usecase = '1.12';
			$module = '1';
			$type = USER_CHANGE_EMAIL_CONFIRM;
			$details = "New Email (".$user->getEmail().") activated for <a href='".$url."' class='blockanchor'>".$user->getName()."</a>";
			 
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
			
			$successmessage = "Successfully updated. Please note that you can no longer login using your previous Email Address";
	    	$session->setVar(SUCCESS_MESSAGE, $successmessage);
	   		$this->_helper->redirector->gotoUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID())));
        }
    }
	function processchangeemailAction()  {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance(); 
        $this->_translate = Zend_Registry::get("translate");
        $formvalues = $this->_getAllParams();
         
        if(!isArrayKeyAnEmptyString('email', $formvalues)){
	        $user = new Member(); 
	    	$user->populate(decode($formvalues['id']));
	    	// debugMessage($user->toArray());
	    	
	    	if($user->emailExists($formvalues['email'])){
	    		$session->setVar(ERROR_MESSAGE, sprintf($this->_translate->translate("profile_email_unique_error"), $formvalues['email']));
	    		return false;
	    	}
	    	# save new username
	    	$user->setEmail2($formvalues['email']);
	    	$user->setActivationKey($user->generateActivationKey());
	    	$user->save();
	    	
	    	$user->sendNewEmailNotification($formvalues['email']);
    		$user->sendOldEmailNotification($formvalues['email']);
    		
    		$view = new Zend_View();
    		$url = $this->view->serverUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID())));
    		$usecase = '1.11';
    		$module = '1';
    		$type = USER_CHANGE_EMAIL;
    		$details = "Email change request for <a href='".$url."' class='blockanchor'>".$user->getName()."</a> from ".$user->getEmail()." to ".$user->getEmail2();
    		 
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
    		
	    	$successmessage = "A request to change your login email has been recieved. <br />To complete this process check your new email Inbox and click on the activation link sent. ";
	   		$this->_redirect($this->view->baseUrl('index/updatesuccess/successmessage/'.encode($successmessage)));
		}
    }
    
	function resendemailcodeAction()  {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance(); 
        $formvalues = $this->_getAllParams();
         
        $user = new Member(); 
    	$user->populate(decode($formvalues['id']));
    	// debugMessage($user->toArray());
    	
    	$session->setVar('contactuslink', "<a href='".$this->view->baseUrl('contactus')."' title='Contact Support'>Contact us</a>");
    	$user->sendNewEmailNotification($user->getEmail2());
    	$successmessage = "A new activation code has been sent to your new email address. If you are still having any problems please do contact us";
    	$session->setVar(SUCCESS_MESSAGE, $successmessage);
   		$this->_redirect($this->view->baseUrl('profile/view/id/'.encode($user->getID())));
    }
    
	function resetpasswordAction(){
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
	   	$session = SessionWrapper::getInstance(); 
       	$this->_translate = Zend_Registry::get("translate"); 
       	$id = decode($this->_getParam('id')); // debugMessage($id);
       	
		$user = new Member(); 
		$user->populate($id); debugMessage($user->toArray());
		// $formvalues = array('email'=>$user->getEmail());
    	$user->setEmail($user->getEmail());
    	// debugMessage('error '.$user->getErrorStackAsString()); exit();
    	if ($user->recoverPassword()) {
       		$session->setVar(SUCCESS_MESSAGE, sprintf($this->_translate->translate('profile_change_password_admin_confirmation'), $user->getName()));
   			// send a link to enable the user to recover their password 
   			// debugMessage('no error found ');
   			
       		$view = new Zend_View();
       		$url = $this->view->serverUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID())));
       		$usecase = '1.9';
       		$module = '1';
       		$type = USER_RESET_PASSWORD;
       		$details = "Reset password request. Reset link sent to <a href='".$url."' class='blockanchor'>".$user->getName()."</a>";
       		 
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
       		
    	} else {
   			$session->setVar(ERROR_MESSAGE, $user->getErrorStackAsString());
   			$session->setVar(FORM_VALUES, $this->_getAllParams()); // debugMessage('no error found ');
    	}
    	// exit();
    	$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
   	}
   	
	public function pictureAction() {}
	
	public function updatestatusAction() {
		$this->_setParam("action", ACTION_DELETE); 
		
    	$session = SessionWrapper::getInstance(); 
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams(); //debugMessage($formvalues);
		$successurl = decode($formvalues[URL_SUCCESS]);
		$successmessage = '';
		if(!isArrayKeyAnEmptyString(SUCCESS_MESSAGE, $formvalues)){
			$successmessage = $this->_translate->translate($this->_getParam(SUCCESS_MESSAGE));
		}
		
    	$user = new Member();
    	$id = is_numeric($formvalues['id']) ? $formvalues['id'] : decode($formvalues['id']);
    	$user->populate($id);
    	/*debugMessage($successmessage);
    	debugMessage($user->toArray());
    	exit();*/
    	if($user->deactivateAccount($formvalues['status'])) {
    		if(!isEmptyString($successmessage)){
    			$session->setVar(SUCCESS_MESSAGE, $successmessage);
    		}
    		$view = new Zend_View();
    		$url = $this->view->serverUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID())));
    		
    		if($formvalues['status'] == 2){
    			# add log to audit trail
    			$usecase = '1.6';
    			$module = '1';
    			$type = USER_DEACTIVATE;
    			$details = "User Profile <a href='".$url."' class='blockanchor'>".$user->getName()."</a> Deactivated";
    		} else {
    			$usecase = '1.7';
    			$module = '1';
    			$type = USER_REACTIVATE;
    			$details = "User Profile <a href='".$url."' class='blockanchor'>".$user->getName()."</a> Re-activated";
    		}
    		
    		$browser = new Browser();
    		$audit_values = $session->getVar('browseraudit');
    		$audit_values['module'] = $module;
    		$audit_values['usecase'] = $usecase;
    		$audit_values['transactiontype'] = $type;
    		$audit_values['status'] = "Y";
    		$audit_values['userid'] = $session->getVar('userid');
    		$audit_values['transactiondetails'] = $details;
    		
    		// debugMessage($audit_values);
    		$this->notify(new sfEvent($this, $type, $audit_values));
    		
    		$this->_helper->redirector->gotoUrl($successurl);
    	}
    	
    	return false;
    }
    
	public function processgpsAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$session = SessionWrapper::getInstance(); 	
	    $config = Zend_Registry::get("config");
	    $this->_translate = Zend_Registry::get("translate");
	    $formvalues = $this->_getAllParams();
	    $formvalues['id'] =  decode($formvalues['id']);
	    
	    // debugMessage($formvalues);
	    $user = new Member();
	    $user->populate($formvalues['id']);
	    
	    $user->setGpsLat($formvalues['lat']);
	    $user->setGpsLng($formvalues['lng']);
	    // debugMessage($user->toArray());
	    // $user->processPost($formvalues);
	   //  debugMessage($user->toArray());
	    // debugMessage('error is '.$user->getErrorStackAsString());
	    
	    // exit();
	    try {
	    	$user->save();
	    	$session->setVar(SUCCESS_MESSAGE, "Location successfully saved");
	    	$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
	    } catch (Exception $e) {
	    	$session->setVar(ERROR_MESSAGE, $e->getMessage()); 
	    	$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
	    }
	}
    
    function inviteuserAction(){
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance();
    	$formvalues = $this->_getAllParams(); debugMessage($this->_getAllParams());
    	
    	$member = new Member();
    	$member->populate($formvalues['memberid']);
    	$member->processPost($formvalues);
    	
    	/* debugMessage($member->toArray());
    	debugMessage('errors are '.$member->getErrorStackAsString()); exit; */
    	if($member->hasError()){
    		$session->setVar(ERROR_MESSAGE, $member->getErrorStackAsString());
    		$session->setVar(FORM_VALUES, $formvalues);
    		$this->_helper->redirector->gotoUrl(decode($this->_getParam('failureurl')));
    	} 
    	try {
    		$member->save();
    		
    		# add log to audit trail
    		$url = $this->view->serverUrl($this->view->baseUrl('profile/view/id/'.encode($member->getID())));
    			
    		$browser = new Browser();
    		$audit_values = $session->getVar('browseraudit');
    		$audit_values['module'] = '1';
    		$audit_values['usecase'] = '1.3';
    		$audit_values['transactiontype'] = USER_CREATE;
    		$audit_values['status'] = "Y";
    		$audit_values['userid'] = $session->getVar('userid');
    		$audit_values['transactiondetails'] = "User Profile <a href='".$url."' class='blockanchor'>".$member->getName()."</a> created from Member";
    		$audit_values['url'] = $url;
    		// debugMessage($audit_values);
    		$this->notify(new sfEvent($this, USER_CREATE, $audit_values));
    		
    		$member->afterUpdate(false);
    		$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate("global_profile_update_success"));
    		$this->_helper->redirector->gotoUrl($this->view->baseUrl('profile/view/id/'.encode($member->getID())));
    			
   		} catch (Exception $e) {
   			$session->setVar(ERROR_MESSAGE, $e->getMessage()."<br />".$lookupvalue->getErrorStackAsString());
   			$session->setVar(FORM_VALUES, $formvalues);
   			$this->_helper->redirector->gotoUrl(decode($this->_getParam('failureurl')));
   		}
    	
    	// exit();
    }
}
