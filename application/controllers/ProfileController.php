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
			$action == "changephone" || $action == "processchangephone" || $action == "processgps" || $action == "resetpassword"){
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
	    	$user->changePassword($this->_getParam('oldpassword'), $this->_getParam('password'));
    		// clear the session
   			// send a link to enable the user to recover their password 
   			$session->setVar(SUCCESS_MESSAGE, "Password successfully updated");
	   		// $this->_redirect($this->view->baseUrl('profile/updatesuccess'));
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
	   		$this->_redirect($this->view->baseUrl('index/updatesuccess'));
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
	    	$successmessage = "A request to change your login email has been recieved. <br />To complete this process check your Inbox for a confirmation code and enter it below. Alternatively, click the activation link sent in the same email.";
	   		$this->_redirect($this->view->baseUrl('index/updatesuccess/successmessage/'.encode($successmessage)));
		}
    }
    
	function changephoneAction()  {
		$session = SessionWrapper::getInstance(); 
		
		$formvalues = $this->_getAllParams();
		if(!isArrayKeyAnEmptyString('actkey', $formvalues) && !isArrayKeyAnEmptyString('ref', $formvalues)){
        	$newphone = decode($formvalues['ref']);
		
			$user = new Member();
			$user->populate(decode($formvalues['id']));
			$oldphone = $user->getPhone();
			$newprimary = $user->getPhone2();
			
			# validate the activation code
			if($formvalues['actkey'] != $user->getPhone2_ActKey()){
				$session->setVar(ERROR_MESSAGE, "Invalid code specified for phone activation");
				$failureurl = $this->view->baseUrl('profile/view/id/'.encode($user->getID()));
				$this->_helper->redirector->gotoUrl($failureurl);
			}
			
			$user->setPhone($newprimary);
			$user->setPhone2($oldphone);
			$user->setPhone2_ActKey('');
			$user->setPhone2_IsActivated(1);
			$user->save();
			
	    	$successmessage = "Successfully updated. Please note that you can no longer login using your previous primary phone";
	    	$session->setVar(SUCCESS_MESSAGE, $successmessage);
	   		$this->_helper->redirector->gotoUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID())));
        }
    }
	function processchangephoneAction()  {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance(); 
        $this->_translate = Zend_Registry::get("translate");
        $formvalues = $this->_getAllParams();
         
        if(!isArrayKeyAnEmptyString('phone', $formvalues)){
	        $user = new Member(); 
	    	$user->populate(decode($formvalues['id']));
	    	
			// debugMessage($formvalues);
	    	
	    	if($formvalues['phone'] == getShortPhone($user->getPhone2()) && $user->isValidated(2)){
		    	try {
		    		$user->setPhone(getFullPhone($formvalues['phone']));
		    		$user->setPhone2(getFullPhone($formvalues['oldphone']));
		    		$user->save();
		    	} catch (Exception $e) {
		    		debugMessage($e->getMessage());
		    	}
	    		
				$successmessage = "Successfully updated. Please note that you can no longer login using your previous primary phone";
		    	$session->setVar(SUCCESS_MESSAGE, $successmessage);
	    	} else {
	    		if($user->phoneExists($formvalues['phone'])){
		    		$session->setVar(ERROR_MESSAGE, sprintf($this->_translate->translate("profile_phone_unique_error"), $formvalues['phone']));
		    		return false;
		    	}
		    	# save new phone
		    	$user->setPhone2(getFullPhone($formvalues['phone']));
		    	$user->setPhone2_isActivated(0);
		    	$user->generatePhoneActivationCode(2);
		    	// $user->save(); 
		    	
		    	$user->sendActivationCodeToMobile($formvalues['phone2']);
		    	$successmessage = "A request to change your primary phone has been recieved. <br />To complete this process check your phone inbox for a confirmation code and enter it below.";
		   		$this->_redirect($this->view->baseUrl('index/updatesuccess/successmessage/'.encode($successmessage)));
	    	}
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
       		
		$user = new Member(); 
		$user->populate(decode($this->_getParam('id')));
    	$user->setEmail($user->getEmail());
    	
    	if ($user->recoverPassword()) {
       		$session->setVar(SUCCESS_MESSAGE, sprintf($this->_translate->translate('profile_change_password_admin_confirmation'), $user->getName()));
   			// send a link to enable the user to recover their password 
   			// debugMessage('no error found ');
    	} else {
   			$session->setVar(ERROR_MESSAGE, $user->getErrorStackAsString());
   			$session->setVar(FORM_VALUES, $this->_getAllParams()); // debugMessage('no error found ');
    	}
    	// exit();
    	$this->_helper->redirector->gotoUrl($this->view->baseUrl("profile/view/id/".encode($user->getID())));
    	
   	}
   	
	public function validatephoneAction(){
    	
    }
    public function processvalidatephoneAction(){
    	$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
		
		// debugMessage($formvalues);
		$successurl = decode($formvalues['successurl']);
		
		$user = new Member();
		$user->populate($formvalues['id']);
		$type = $formvalues['phonetype'];
		
		// debugMessage($user->toArray()); 
		try {
			$user->generatePhoneActivationCode($type);
			$user->sendActivationCodeToMobile($type);
			
			$session->setVar(SUCCESS_MESSAGE, 'Validation code has been sent to the mobile phone. Please check Inbox and enter the code sent below to confirm.');
		} catch (Exception $e) {
			$txt = $e->getMessage();
			$session->setVar(ERROR_MESSAGE, 'An error occured in requesting activation for your Phone. Please contact support for resolution. '.$txt);
		}
		// exit();
    	$this->_helper->redirector->gotoUrl($successurl);
    }
	public function validatephonesuccessAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);

		$session->setVar(SUCCESS_MESSAGE, 'Validation code has been sent to the mobile phone. Please check Inbox and enter the code sent below to confirm.');
    }
	public function verifyphoneAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		$successurl = decode($formvalues['successurl']);
		$type = $formvalues['type'];
		// debugMessage($formvalues);
		// debugMessage($successurl);
		
		$user = new Member();
		$user->populate($formvalues['id']);
		// debugMessage($user->toArray());
		if($user->verifyPhone($formvalues['code'], $type)){
			$user->activatePhone($type);
			$user->sendActivationConfirmationToMobile($type);
			
			$session->setVar(SUCCESS_MESSAGE, 'Phone Number Successfully Verified and Confirmed');
			$session->setVar(ERROR_MESSAGE, '');
		} else {
			$session->setVar(SUCCESS_MESSAGE, '');
			$session->setVar(ERROR_MESSAGE, 'Invalid activation code specified. Please try again.');
		}
		
		// exit();
		// return to successpage
		$this->_helper->redirector->gotoUrl($successurl);
    }
    
	public function pictureAction() {}
	
	public function processpictureAction() {
		// disable rendering of the view and layout so that we can just echo the AJAX output 
	    $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
	    $session = SessionWrapper::getInstance(); 	
	    $config = Zend_Registry::get("config");
	    $this->_translate = Zend_Registry::get("translate"); 
		
	    $formvalues = $this->_getAllParams();
	    
		debugMessage($this->_getAllParams());
		$user = new Member();
		$user->populate(decode($this->_getParam('id')));
		
		// only upload a file if the attachment field is specified		
		$upload = new Zend_File_Transfer(); 
		// set the file size in bytes
		$upload->setOptions(array('useByteString' => false));
		
		// Limit the extensions to the specified file extensions
		$upload->addValidator('Extension', false, $config->uploads->photoallowedformats);
	 	$upload->addValidator('Size', false, $config->uploads->photomaximumfilesize);
		
		// base path for profile pictures
 		$destination_path = APPLICATION_PATH.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."users".DIRECTORY_SEPARATOR."user_";
	
		// determine if user has destination avatar folder. Else user is editing there picture
		if(!is_dir($destination_path.$user->getID())){
			// no folder exits. Create the folder
			mkdir($destination_path.$user->getID(), 0777);
		} 
		
		// set the destination path for the image
		$profilefolder = $user->getID();
		$destination_path = $destination_path.$profilefolder.DIRECTORY_SEPARATOR."avatar";
		
		if(!is_dir($destination_path)){
			mkdir($destination_path, 0777);
		}
		// create archive folder for each user
		$archivefolder = $destination_path.DIRECTORY_SEPARATOR."archive";
		if(!is_dir($archivefolder)){
			mkdir($archivefolder, 0777);
		}
		
		$oldfilename = $user->getProfilePhoto();
		
		//debugMessage($destination_path); 
		$upload->setDestination($destination_path);
		
		// the profile image info before upload
		$file = $upload->getFileInfo('profileimage');
		$uploadedext = findExtension($file['profileimage']['name']);
		$currenttime = mktime();
		$currenttime_file = $currenttime.'.'.$uploadedext;
		// debugMessage($file);
		
		$thefilename = $destination_path.DIRECTORY_SEPARATOR.'base_'.$currenttime_file;
		$thelargefilename = $destination_path.DIRECTORY_SEPARATOR.'large_'.$currenttime_file;
		$updateablefile = $destination_path.DIRECTORY_SEPARATOR.'base_'.$currenttime;
		$updateablelarge = $destination_path.DIRECTORY_SEPARATOR.'large_'.$currenttime;
		// exit(); 
		// rename the base image file 
		$upload->addFilter('Rename',  array('target' => $thefilename, 'overwrite' => true));		
		// exit();
		// process the file upload
		if($upload->receive()){
			// debugMessage('Completed');
			$file = $upload->getFileInfo('profileimage');
			// debugMessage($file);
			
			$basefile = $thefilename;
			// convert png to jpg
			if(in_array(strtolower($uploadedext), array('png','PNG','gif','GIF'))){
				ak_img_convert_to_jpg($thefilename, $updateablefile.'.jpg', $uploadedext);
				unlink($thefilename);
			}
			$basefile = $updateablefile.'.jpg';
			
			// new profilenames
			$newlargefilename = "large_".$currenttime_file;
			// generate and save thumbnails for sizes 250, 125 and 50 pixels
			resizeImage($basefile, $destination_path.DIRECTORY_SEPARATOR.'large_'.$currenttime.'.jpg', 400);
			resizeImage($basefile, $destination_path.DIRECTORY_SEPARATOR.'medium_'.$currenttime.'.jpg', 165);
			
			// unlink($thefilename);
			unlink($destination_path.DIRECTORY_SEPARATOR.'base_'.$currenttime.'.jpg');
			
			// exit();
			// update the useraccount with the new profile images
			try {
				$user->setProfilePhoto($currenttime.'.jpg');
				$user->save();
				
				// check if user already has profile picture and archive it
				$ftimestamp = current(explode('.', $user->getProfilePhoto()));
				
				$allfiles = glob($destination_path.DIRECTORY_SEPARATOR.'*.*');
				$currentfiles = glob($destination_path.DIRECTORY_SEPARATOR.'*'.$ftimestamp.'*.*');
				// debugMessage($currentfiles);
				$deletearray = array();
				foreach ($allfiles as $value) {
					if(!in_array($value, $currentfiles)){
						$deletearray[] = $value;
					}
				}
				// debugMessage($deletearray);
				if(count($deletearray) > 0){
					foreach ($deletearray as $afile){
						$afile_filename = basename($afile);
						rename($afile, $archivefolder.DIRECTORY_SEPARATOR.$afile_filename);
					}
				}
				
				$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate("global_update_success"));
				$this->_helper->redirector->gotoUrl($this->view->baseUrl("profile/picture/id/".encode($user->getID()).'/crop/1'));
			} catch (Exception $e) {
				$session->setVar(ERROR_MESSAGE, $e->getMessage());
				$session->setVar(FORM_VALUES, $this->_getAllParams());
				$this->_helper->redirector->gotoUrl($this->view->baseUrl('profile/picture/id/'.encode($user->getID())));
			}
		} else {
			// debugMessage($upload->getMessages());
			$uploaderrors = $upload->getMessages();
			$customerrors = array();
			if(!isArrayKeyAnEmptyString('fileUploadErrorNoFile', $uploaderrors)){
				$customerrors['fileUploadErrorNoFile'] = "Please browse for image on computer";
			}
			if(!isArrayKeyAnEmptyString('fileExtensionFalse', $uploaderrors)){
				$custom_exterr = sprintf($this->_translate->translate('global_invalid_ext_error'), $config->uploads->photoallowedformats);
				$customerrors['fileExtensionFalse'] = $custom_exterr;
			}
			if(!isArrayKeyAnEmptyString('fileUploadErrorIniSize', $uploaderrors)){
				$custom_exterr = sprintf($this->_translate->translate('global_invalid_size_error'), formatBytes($config->uploads->photomaximumfilesize,0));
				$customerrors['fileUploadErrorIniSize'] = $custom_exterr;
			}
			if(!isArrayKeyAnEmptyString('fileSizeTooBig', $uploaderrors)){
				$custom_exterr = sprintf($this->_translate->translate('global_invalid_size_error'), formatBytes($config->uploads->photomaximumfilesize,0));
				$customerrors['fileSizeTooBig'] = $custom_exterr;
			}
			$session->setVar(ERROR_MESSAGE, 'The following errors occured <ul><li>'.implode('</li><li>', $customerrors).'</li></ul>');
			$session->setVar(FORM_VALUES, $this->_getAllParams());
			
			$this->_helper->redirector->gotoUrl($this->view->baseUrl('profile/picture/id/'.encode($user->getID())));
		}
		// exit();
	}
	
	function croppictureAction(){
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$session = SessionWrapper::getInstance(); 	
		$formvalues = $this->_getAllParams();
		
		$user = new Member();
		$user->populate(decode($formvalues['id']));
		$userfolder = $user->getID();
		// debugMessage($formvalues);
		//debugMessage($user->toArray());
		
		$oldfile = "large_".$user->getProfilePhoto();
		$base = BASE_PATH.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR."users".DIRECTORY_SEPARATOR.'user_'.$userfolder.''.DIRECTORY_SEPARATOR.'avatar'.DIRECTORY_SEPARATOR;
		
		// debugMessage($user->toArray()); 
		$src = $base.$oldfile;
		
		$currenttime = mktime();
		$currenttime_file = $currenttime.'.jpg';
		$newlargefilename = $base."large_".$currenttime_file;
		$newmediumfilename = $base."medium_".$currenttime_file;
		
		// exit();
		$image = WideImage::load($src);
		$cropped1 = $image->crop($formvalues['x1'], $formvalues['y1'], $formvalues['w'], $formvalues['h']);
		$resized_1 = $cropped1->resize(300, 300, 'fill');
		$resized_1->saveToFile($newlargefilename);
			
		//$image2 = WideImage::load($src);
		$cropped2 = $image->crop($formvalues['x1'], $formvalues['y1'], $formvalues['w'], $formvalues['h']);
		$resized_2 = $cropped2->resize(165, 165, 'fill');
		$resized_2->saveToFile($newmediumfilename);
		
		$user->setProfilePhoto($currenttime_file);
		$user->save();
			
		// check if user already has profile picture and archive it
		$ftimestamp = current(explode('.', $user->getProfilePhoto()));
		
		$allfiles = glob($base.DIRECTORY_SEPARATOR.'*.*');
		$currentfiles = glob($base.DIRECTORY_SEPARATOR.'*'.$ftimestamp.'*.*');
		// debugMessage($currentfiles);
		$deletearray = array();
		foreach ($allfiles as $value) {
			if(!in_array($value, $currentfiles)){
				$deletearray[] = $value;
			}
		}
		// debugMessage($deletearray);
		if(count($deletearray) > 0){
			foreach ($deletearray as $afile){
				$afile_filename = basename($afile);
				rename($afile, $base.DIRECTORY_SEPARATOR.'archive'.DIRECTORY_SEPARATOR.$afile_filename);
			}
		}
		$session->setVar(SUCCESS_MESSAGE, "Successfully updated profile picture");
		$this->_helper->redirector->gotoUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID())));
		// exit();
    }
    
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
	
	function uploadpictureAction(){
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	
		$session = SessionWrapper::getInstance(); 	
	    $config = Zend_Registry::get("config");
		
	    $formvalues = $this->_getAllParams();
	    
		if(isset($_FILES["FileInput"]) && $_FILES["FileInput"]["error"] == UPLOAD_ERR_OK) {
			if(!isset($_FILES['FileInput']['name'])){
		    	die("<span class='alert alert-danger'>Error: Please select an Image to Upload.</span>");
		    }
	 		
			$real_path = APPLICATION_PATH.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."users".DIRECTORY_SEPARATOR."user_";
			// determine if destination folder exists and create it if not
			if(!is_dir($real_path.$formvalues['userid'])){
				// no folder exits. Create the folder
				mkdir($real_path.$formvalues['userid'], 0777);
			} 
			$real_path = $real_path.$formvalues['userid'].DIRECTORY_SEPARATOR."avatar";
			if(!is_dir($real_path)){
				mkdir($real_path, 0777);
			}
			$archivefolder = $real_path.DIRECTORY_SEPARATOR."archive";
			if(!is_dir($archivefolder)){
				mkdir($archivefolder, 0777);
			}
			
			$UploadDirectory = $real_path.DIRECTORY_SEPARATOR;
			$File_Name = strtolower($_FILES['FileInput']['name']);
			$File_Ext = findExtension($File_Name); //get file extention
			$ext = strtolower($_FILES['FileInput']['type']);
			$allowedformatsarray = explode(',', $config->uploads->photoallowedformats);
			$Random_Number = mktime(); //Random number to be added to name.
			$NewFileName = "base_".$Random_Number.'.'.$File_Ext; //new file name
			
			// check if this is an ajax request
			if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
				die("<span class='alert alert-danger'>Error: No Request received.</span>");
			}
			// validate maximum allowed size
			if ($_FILES["FileInput"]["size"] > $config->uploads->photomaximumfilesize) {
				die("<span class='alert alert-danger'>Error: Maximum allowed size exceeded.</span>");
			}
			// validate allowed formats
			if(!in_array($File_Ext, $allowedformatsarray)){
				die("<span class='alert alert-danger'>Error: Format '".$File_Ext."' not supported. Only formats '".$config->uploads->photoallowedformats."' allowed</span>");
			}
			
			# move the file
			try {
				move_uploaded_file($_FILES['FileInput']['tmp_name'], $UploadDirectory.$NewFileName);
				resizeImage($UploadDirectory.$NewFileName, $UploadDirectory.'large_'.$Random_Number.'.jpg', 400);
				resizeImage($UploadDirectory.$NewFileName, $UploadDirectory.'medium_'.$Random_Number.'.jpg', 165);
				// unlink($thefilename);
				unlink($UploadDirectory.'base_'.$Random_Number.'.jpg');
				$session->setVar(SUCCESS_MESSAGE, "Successfully uploaded! Please resize/crop image to complete.");
				
				$user = new Member();
				$user->populate($formvalues['userid']);
				$user->setProfilePhoto($Random_Number.'.jpg');
				$user->save();
				
				// check if user already has profile picture and archive it
				$ftimestamp = current(explode('.', $user->getProfilePhoto()));
				
				$allfiles = glob($UploadDirectory.'*.*');
				$currentfiles = glob($UploadDirectory.'*'.$ftimestamp.'*.*');
				// debugMessage($currentfiles);
				$deletearray = array();
				foreach ($allfiles as $value) {
					if(!in_array($value, $currentfiles)){
						$deletearray[] = $value;
					}
				}
				// debugMessage($deletearray);
				if(count($deletearray) > 0){
					foreach ($deletearray as $afile){
						$afile_filename = basename($afile);
						rename($afile, $archivefolder.DIRECTORY_SEPARATOR.$afile_filename);
					}
				}
				
				// die('File '.$NewFileName.' Uploaded.');
				$result = array('oldfilename' => $File_Name, 'newfilename'=>$NewFileName, 'msg'=>'File '.$File_Name.' successfully uploaded', 'result'=>1);
				// json_decode($result);
				die(json_encode($result));
			} catch (Exception $e) {
				die('Error in uploading File '.$File_Name.'. '.$e->getMessage());
			}
		} else {
			// debugMessage($formvalues);
			$this->_helper->redirector->gotoUrl($this->view->baseUrl('profile/picture/id/'.encode($formvalues['userid']).'/crop/1'));
		}
    }
}
