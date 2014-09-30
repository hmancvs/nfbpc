<?php

class MemberController extends SecureController {
	
	/**
	 * Override unknown actions to enable ACL checking 
	 * 
	 * @see SecureController::getActionforACL()
	 *
	 * @return String
	 */
	public function getActionforACL() {
	 	$action = strtolower($this->getRequest()->getActionName()); 
	 	if($action == "picture" || $action == "processpicture" || $action == "croppicture" || 
	 		$action == "uploadpicture" 
		){
	 		return ACTION_EDIT;
	 	}
	 	if($action = "search"){
	 		return ACTION_VIEW;
	 	}
		return parent::getActionforACL();
    }
    
    function createAction(){
    	$formvalues = $this->_getAllParams(); // debugMessage($formvalues); exit(); 
    	// if id exists use action edit
    	if(!isArrayKeyAnEmptyString('id', $formvalues)){
    		$this->_setParam('action', ACTION_EDIT);
    	}
    	
    	parent::createAction();
    }
    
    function editAction(){
    	return $this->createAction();
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
    	 
    	//debugMessage($this->_getAllParams());
    	$member = new Client();
    	$member->populate(decode($this->_getParam('id')));
    
    	// only upload a file if the attachment field is specified
    	$upload = new Zend_File_Transfer();
    	// set the file size in bytes
    	$upload->setOptions(array('useByteString' => false));
    
    	// Limit the extensions to the specified file extensions
    	$upload->addValidator('Extension', false, $config->uploads->photoallowedformats);
    	$upload->addValidator('Size', false, $config->uploads->photomaximumfilesize);
    
    	// base path for profile pictures
    	$destination_path = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."clients".DIRECTORY_SEPARATOR."client_";
    
    	// determine if client has destination avatar folder. Else client is editing there picture
    	if(!is_dir($destination_path.$member->getID())){
    		// no folder exits. Create the folder
    		mkdir($destination_path.$member->getID(), 0775);
    	}
    
    	// set the destination path for the image
    	$profilefolder = $member->getID();
    	$destination_path = $destination_path.$profilefolder.DIRECTORY_SEPARATOR."avatar";
    
    	if(!is_dir($destination_path)){
    		mkdir($destination_path, 0775);
    	}
    	// create archive folder for each client
    	$archivefolder = $destination_path.DIRECTORY_SEPARATOR."archive";
    	if(!is_dir($archivefolder)){
    		mkdir($archivefolder, 0775);
    	}
    
    	$oldfilename = $member->getProfilePhoto();
    
    	//debugMessage($destination_path);
    	$upload->setDestination($destination_path);
    
    	// the profile image info before upload
    	$file = $upload->getFileInfo('profileimage');
    	$uploadedext = findExtension($file['profileimage']['name']);
    	$currenttime = mktime();
    	$currenttime_file = $currenttime.'.'.$uploadedext;
    
    	$thefilename = $destination_path.DIRECTORY_SEPARATOR.'base_'.$currenttime_file;
    	$thelargefilename = $destination_path.DIRECTORY_SEPARATOR.'large_'.$currenttime_file;
    	$updateablefile = $destination_path.DIRECTORY_SEPARATOR.'base_'.$currenttime;
    	$updateablelarge = $destination_path.DIRECTORY_SEPARATOR.'large_'.$currenttime;
    	//debugMessage($thefilename);
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
    		// update the Client with the new profile images
    		try {
    			$member->setProfilePhoto($currenttime.'.jpg');
    			$member->save();
    
    			// check if client already has profile picture and archive it
    			$ftimestamp = current(explode('.', $member->getProfilePhoto()));
    
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
    			$this->_helper->redirector->gotoUrl($this->view->baseUrl("member/picture/id/".encode($member->getID()).'/crop/1'));
    		} catch (Exception $e) {
    			$session->setVar(ERROR_MESSAGE, $e->getMessage());
    			$session->setVar(FORM_VALUES, $this->_getAllParams());
    			$this->_helper->redirector->gotoUrl($this->view->baseUrl('member/picture/id/'.encode($member->getID())));
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
    			
    		$this->_helper->redirector->gotoUrl($this->view->baseUrl('member/picture/id/'.encode($member->getID())));
    	}
    	// exit();
    }
    
    function croppictureAction(){
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance();
    	$formvalues = $this->_getAllParams();
    
    	$member = new Member();
    	$member->populate(decode($formvalues['id']));
    	$memberfolder = $member->getID();
    	// debugMessage($formvalues);
    	//debugMessage($member->toArray());
    
    	$oldfile = "large_".$member->getProfilePhoto();
    	$base = BASE_PATH.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR."members".DIRECTORY_SEPARATOR.'member_'.$memberfolder.''.DIRECTORY_SEPARATOR.'avatar'.DIRECTORY_SEPARATOR;
    
    	// debugMessage($member->toArray());
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
    
    	$member->setProfilePhoto($currenttime_file);
    	$member->save();
    		
    	// check if client already has profile picture and archive it
    	$ftimestamp = current(explode('.', $member->getProfilePhoto()));
    
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
    		$this->_helper->redirector->gotoUrl($this->view->baseUrl('member/view/id/'.encode($member->getID())));
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
    
    		$real_path = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."members".DIRECTORY_SEPARATOR."member_";
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
	    
	    				$member = new Member();
	    				$member->populate($formvalues['userid']);
	    				$member->setProfilePhoto($Random_Number.'.jpg');
	    				$member->save();
	    
	    		// check if user already has profile picture and archive it
	    		$ftimestamp = current(explode('.', $member->getProfilePhoto()));
	    
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
    			$this->_helper->redirector->gotoUrl($this->view->baseUrl('member/index/id/'.encode($formvalues['userid']).'/tab/picture/crop/1'));
    	}
    }
    
    public function searchAction() {
    	// disable rendering of the view and layout so that we can just echo the AJAX output
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    
    	$session = SessionWrapper::getInstance();
    	$config = Zend_Registry::get("config");
    	$this->_translate = Zend_Registry::get("translate");
    
    	$formvalues = $this->_getAllParams();
    	/* debugMessage($formvalues);
    	exit(); */
    	$data = array(
    		array('value'=>'jQuery','label'=>'jQuery','desc'=>'the write less, do more, JavaScript library','icon'=>''),
    		array('value'=>'jquery-ui','label'=>'jQuery UI','desc'=>'the official user interface library for jQuery','icon'=>'jqueryui_32x32.png'),
    		array('value'=>'sizzlejs','label'=>'Sizzle JS','desc'=>'a pure-JavaScript CSS selector engine','icon'=>'sizzlejs_32x32.png')
    				
    	);
    	echo json_encode($data);
    }
}
