<?php

class OrganisationController extends SecureController {
	
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
		return parent::getActionforACL();
	}
	
	public function pictureAction() {
		
	}
	
	public function processpictureAction() {
		// disable rendering of the view and layout so that we can just echo the AJAX output
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
	
		$session = SessionWrapper::getInstance();
		$config = Zend_Registry::get("config");
		$this->_translate = Zend_Registry::get("translate");
	
		$formvalues = $this->_getAllParams();
	
		// debugMessage($this->_getAllParams()); // exit;
		$organisation = new Organisation();
		$organisation->populate(decode($this->_getParam('id')));
	
		// only upload a file if the attachment field is specified
		$upload = new Zend_File_Transfer();
		// set the file size in bytes
		$upload->setOptions(array('useByteString' => false));
	
		// Limit the extensions to the specified file extensions
		$upload->addValidator('Extension', false, $config->uploads->photoallowedformats);
		$upload->addValidator('Size', false, $config->uploads->photomaximumfilesize);
	
		// base path for profile pictures
		$destination_path = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."organisations".DIRECTORY_SEPARATOR."org_";
	
		// determine if client has destination avatar folder. Else client is editing there picture
		if(!is_dir($destination_path.$organisation->getID())){
			// no folder exits. Create the folder
			mkdir($destination_path.$organisation->getID(), 0775);
		}
	
		// set the destination path for the image
		$profilefolder = $organisation->getID();
		$destination_path = $destination_path.$profilefolder.DIRECTORY_SEPARATOR."cover";
	
		if(!is_dir($destination_path)){
			mkdir($destination_path, 0775);
		}
		// create archive folder for each client
		$archivefolder = $destination_path.DIRECTORY_SEPARATOR."archive";
		if(!is_dir($archivefolder)){
			mkdir($archivefolder, 0775);
		}
	
		$oldfilename = $organisation->getProfilePhoto();
	
		debugMessage($destination_path);
		$upload->setDestination($destination_path);
	
		// the profile image info before upload
		$file = $upload->getFileInfo('FileInput');
		$uploadedext = findExtension($file['FileInput']['name']);
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
			$file = $upload->getFileInfo('FileInput');
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
			resizeImage($basefile, $destination_path.DIRECTORY_SEPARATOR.'large_'.$currenttime.'.jpg', 600);
			resizeImage($basefile, $destination_path.DIRECTORY_SEPARATOR.'medium_'.$currenttime.'.jpg', 300);
			 
			// unlink($thefilename);
			unlink($destination_path.DIRECTORY_SEPARATOR.'base_'.$currenttime.'.jpg');
			 
			// exit();
			// update the Client with the new profile images
			try {
				$organisation->setProfilePhoto($currenttime.'.jpg');
				$organisation->save();
	
				// check if client already has profile picture and archive it
				$ftimestamp = current(explode('.', $organisation->getProfilePhoto()));
	
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
				
				$view = new Zend_View();
				$url = $this->view->serverUrl($this->view->baseUrl('organisation/view/id/'.encode($organisation->getID())));
				$usecase = '3.4';
				$module = '3';
				$type = ORG_UPLOADPHOTO;
				$details = "New Profile Photo uploaded for <a href='".$url."' class='blockanchor'>".$organisation->getName()."</a>";
				
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
				
				$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate("global_update_success"));
				$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
			} catch (Exception $e) {
				$session->setVar(ERROR_MESSAGE, $e->getMessage());
				$session->setVar(FORM_VALUES, $this->_getAllParams());
				$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
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
			 
			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
		}
		// exit();
	}
	
	function croppictureAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$session = SessionWrapper::getInstance();
		$formvalues = $this->_getAllParams();
	
		$organisation = new Organisation();
		$organisation->populate(decode($formvalues['id']));
		$organisationfolder = $organisation->getID();
		debugMessage($formvalues);
		
		$newheight_large = round(($formvalues['h']/$formvalues['w']) * 600);
		$newheight_medium = round(($formvalues['h']/$formvalues['w']) * 300);
		debugMessage($newheight_large);
		debugMessage($newheight_medium);
		
		$oldfile = "large_".$organisation->getProfilePhoto();
		$base = BASE_PATH.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR."organisations".DIRECTORY_SEPARATOR.'org_'.$organisationfolder.''.DIRECTORY_SEPARATOR.'cover'.DIRECTORY_SEPARATOR;
	
		// debugMessage($organisation->toArray());
		$src = $base.$oldfile;
	
		$currenttime = mktime();
		$currenttime_file = $currenttime.'.jpg';
		$newlargefilename = $base."large_".$currenttime_file;
		$newmediumfilename = $base."medium_".$currenttime_file;
	
		// exit();
		$image = WideImage::load($src);
		$cropped1 = $image->crop($formvalues['x1'], $formvalues['y1'], $formvalues['w'], $formvalues['h']);
		$resized_1 = $cropped1->resize(600, $newheight_large, 'fill');
		$resized_1->saveToFile($newlargefilename);
	
		//$image2 = WideImage::load($src);
		$cropped2 = $image->crop($formvalues['x1'], $formvalues['y1'], $formvalues['w'], $formvalues['h']);
		$resized_2 = $cropped2->resize(300, $newheight_medium, 'fill');
		$resized_2->saveToFile($newmediumfilename);
	
		$organisation->setProfilePhoto($currenttime_file);
		$organisation->save();
	
		// check if client already has profile picture and archive it
		$ftimestamp = current(explode('.', $organisation->getProfilePhoto()));
	
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
		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
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
	
			$real_path = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."organisations".DIRECTORY_SEPARATOR."org_";
			// determine if destination folder exists and create it if not
			if(!is_dir($real_path.$formvalues['organisationid'])){
				// no folder exits. Create the folder
				mkdir($real_path.$formvalues['organisationid'], 0775);
			}
			$real_path = $real_path.$formvalues['organisationid'].DIRECTORY_SEPARATOR."cover";
			if(!is_dir($real_path)){
				mkdir($real_path, 0775);
			}
			$archivefolder = $real_path.DIRECTORY_SEPARATOR."archive";
			if(!is_dir($archivefolder)){
				mkdir($archivefolder, 0775);
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
				resizeImage($UploadDirectory.$NewFileName, $UploadDirectory.'large_'.$Random_Number.'.jpg', 600);
				resizeImage($UploadDirectory.$NewFileName, $UploadDirectory.'medium_'.$Random_Number.'.jpg', 300);
				// unlink($thefilename);
				unlink($UploadDirectory.'base_'.$Random_Number.'.jpg');
				$session->setVar(SUCCESS_MESSAGE, "Successfully uploaded! Please resize/crop image to complete.");
		  
				$organisation = new Organisation();
				$organisation->populate($formvalues['organisationid']);
				$organisation->setProfilePhoto($Random_Number.'.jpg');
				$organisation->save();
			  
				// check if user already has profile picture and archive it
				$ftimestamp = current(explode('.', $organisation->getProfilePhoto()));
			  
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
			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
		}
	}
}