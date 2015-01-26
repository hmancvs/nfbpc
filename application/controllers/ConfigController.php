<?php
class ConfigController extends IndexController   {

	/**
	 * @see SecureController::getResourceForACL()
	 * 
	 * Return the Application Settings since we need to make the url more friendly 
	 *
	 * @return String
	 */
	function getResourceForACL() {
		return "Application Settings"; 
	}
	/**
	 * Get the name of the resource being accessed 
	 *
	 * @return String 
	 */
	function getActionforACL() {
		$action = strtolower($this->getRequest()->getActionName()); 
		if($action == "processvariables" || $action == "processglobalconfig" || $action == "add"){
			return ACTION_EDIT;
		}
		if($action == "variables" || $action == "globalconfig") {
			return ACTION_LIST; 
			// return ACTION_VIEW;
		}
		return parent::getActionforACL(); 
	}
	
	function addAction(){
    	// parent::listAction();
    }
    
	function variablesAction(){
    	// parent::listAction();
    }
    
	function variablessearchAction(){
		$this->_helper->redirector->gotoSimple("variables", "config", 
    											$this->getRequest()->getModuleName(),
    											array_remove_empty(array_merge_maintain_keys($this->_getAllParams(), $this->getRequest()->getQuery())));
	}
	
	function processvariablesAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		// debugMessage($formvalues); 
		if(isArrayKeyAnEmptyString('noreload', $formvalues)){
			$hasnoreload = false; 	
		} else {
			$hasnoreload = true;
		}
		
		$haserror = false;
		if(isArrayKeyAnEmptyString('value', $formvalues) && !$hasnoreload){
			$haserror = true;
			$session->setVar(ERROR_MESSAGE, 'Error: No value specified for addition');
			$session->setVar(FORM_VALUES, $formvalues);
			$this->_helper->redirector->gotoUrl($this->view->baseUrl('config/variables/'.$formvalues['lookupid']));
		}
		$successurl = $this->view->baseUrl('config/variables/type/'.$formvalues['lookupid']);
		$type_ext = '';
		$alias = '';
		if(!isArrayKeyAnEmptyString('alias', $formvalues)){
			$alias= trim($formvalues['alias']);
			if($alias == 'undefined'){
				$alias = '';
			}
		}
		// exit;
		// debugMessage()
		switch ($formvalues['lookupid']){
			default:
				$lookupvalue = new LookupTypeValue();
				$lookuptype = new LookupType();
				$lookuptype->populate($formvalues['lookupid']);
					
				$index = '';
				if($hasnoreload){
					$index = $lookuptype->getNextInsertIndex();
					$value  = trim($formvalues['value']);
				} else {
					if(!isArrayKeyAnEmptyString('index', $formvalues)){
						$index = $formvalues['index'];
					} else {
						$index = $lookuptype->getNextInsertIndex();
					}
					$value  = addslashes(decode(trim($formvalues['value'])));
				}
				
				$dataarray = array('id' => $formvalues['id'],
									'lookuptypeid' => $formvalues['lookupid'], 
									'lookuptypevalue' => $index, 
									'lookupvaluedescription' => $value,
									'alias' => $alias,
									'createdby' => $session->getVar('userid')
							);
				
				if(!isArrayKeyAnEmptyString('id', $formvalues)){
					$lookupvalue->populate($formvalues['id']);
					$beforesave = $lookupvalue->toArray(); // debugMessage($beforesave);
				}
				// unset($dataarray['id']);
				$lookupvalue->processPost($dataarray);
				/* debugMessage($lookupvalue->toArray());
		    	debugMessage('errors are '.$lookupvalue->getErrorStackAsString()); // exit(); */
				
		    	$result = array('id'=>'', 'name'=>'');
				if($lookupvalue->hasError()){
					$haserror = true;
					$session->setVar(ERROR_MESSAGE, $lookupvalue->getErrorStackAsString());
					$session->setVar(FORM_VALUES, $formvalues);
				} else {
					try {
						$lookupvalue->save();
						if(!$hasnoreload){
							$url = $this->view->serverUrl($this->view->baseUrl("config/variables/lookupid/".$formvalues['lookupid']));
							$module = '0';
							if(isArrayKeyAnEmptyString('id', $formvalues)){
								$session->setVar(SUCCESS_MESSAGE, "Successfully saved");
								$type = SYSTEM_ADDVARIABLE;
								$usecase = '0.1';
								$details = 'Variable - <b>'.$lookupvalue->getlookupvaluedescription().' </b>('.$lookupvalue->getLookupType()->getdisplayname().') addded';
							} else {
								$session->setVar(SUCCESS_MESSAGE, "Successfully updated");
								$type = SYSTEM_UPDATEVARIABLE;
								$usecase = '0.2';
								$details = 'Variable - <b>'.$lookupvalue->getlookupvaluedescription().' </b>('.$lookupvalue->getLookupType()->getdisplayname().') updated';
								$prejson = json_encode($beforesave);
								$lookupvalue->clearRelated();
								$after = $lookupvalue->toArray(); debugMessage($after);
								$postjson = json_encode($after); // debugMessage($postjson);
								$diff = array_diff($beforesave, $after);  // debugMessage($diff);
								$jsondiff = json_encode($diff); // debugMessage($jsondiff);
							}
							
							$browser = new Browser();
							$audit_values = $session->getVar('browseraudit');
							$audit_values['module'] = $module;
							$audit_values['usecase'] = $usecase;
							$audit_values['transactiontype'] = $type;
							$audit_values['status'] = "Y";
							$audit_values['userid'] = $session->getVar('userid');
							$audit_values['transactiondetails'] = $details;
							$audit_values['url'] = $url;
							if(!isArrayKeyAnEmptyString('id', $formvalues)){
								$audit_values['isupdate'] = 1;
								$audit_values['prejson'] = $prejson;
								$audit_values['postjson'] = $postjson;
								$audit_values['jsondiff'] = $jsondiff;
							}
							// debugMessage($audit_values);
							$this->notify(new sfEvent($this, $type, $audit_values));
						}
						$result = array('id'=>$lookupvalue->getlookuptypevalue(), 'name'=>$lookupvalue->getlookupvaluedescription(), 'alias'=>$lookupvalue->getalias());
					} catch (Exception $e) {
						$session->setVar(ERROR_MESSAGE, $e->getMessage()."<br />".$lookupvalue->getErrorStackAsString());
						$session->setVar(FORM_VALUES, $formvalues);
					}
				}
				break;
		}
		// debugMessage($successurl);exit(); 
		
		if(!$hasnoreload){
			$this->_helper->redirector->gotoUrl($successurl);	
		} else {
			echo json_encode($result);
		}
	}
    
	function globalconfigAction(){
    	// parent::listAction();
    }
    
	function globalconfigsearchAction(){
		$this->_helper->redirector->gotoSimple("globalconfig", "config", 
    											$this->getRequest()->getModuleName(),
    											array_remove_empty(array_merge_maintain_keys($this->_getAllParams(), $this->getRequest()->getQuery())));
	}
	
	function processglobalconfigAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		$successurl = decode($formvalues[URL_SUCCESS]);
		// debugMessage($formvalues);
		$postarray = array();
		for ($i = 1; $i <= $formvalues['t']; $i++) {
			$postarray[$i]['id'] = $formvalues['id_'.$i];
			$postarray[$i]['displayname'] = $formvalues['displayname_'.$i];
			$postarray[$i]['optionvalue'] = $formvalues['optionvalue_'.$i];
		}

		$config_collection = new Doctrine_Collection(Doctrine_Core::getTable("AppConfig"));
		foreach ($postarray as $line){
			$appconfig = new AppConfig();
			$appconfig->populate($line['id']);

			$appconfig->processPost($line);
			/*debugMessage('error is '.$appconfig->getErrorStackAsString());
			debugMessage($appconfig->toArray());*/
			if($appconfig->isValid()) {
				$config_collection->add($appconfig);
			}	
		}
		// check for atleast one option and save
		if($config_collection->count() > 0){
			try {
				// debugMessage($config_collection->toArray());
				$config_collection->save();
				$session->setVar(SUCCESS_MESSAGE, $formvalues[SUCCESS_MESSAGE]);
				
				# clear cache after updating options
				$temppath = APPLICATION_PATH.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR; // debugMessage($temppath);
				$files = glob($temppath.'zend_cache---config*');
				foreach($files as $file){
					debugMessage($file);
					if(is_file($file)){
						unlink($file);
				  	}
				}
			} catch (Exception $e) {
				$session->setVar(ERROR_MESSAGE, "An error occured in updating the parameters. ".$e->getMessage());
			}
		}
		// debugMessage($successurl);
		$this->_helper->redirector->gotoUrl($successurl);	
		// exit();
	}
	
	public function processpictureAction() {
		// disable rendering of the view and layout so that we can just echo the AJAX output 
	    $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
	    $session = SessionWrapper::getInstance(); 	
	    $config = Zend_Registry::get("config");
	    $this->_translate = Zend_Registry::get("translate"); 
		
	    $formvalues = $this->_getAllParams();
	    $type = $formvalues['type'];
	    
		//debugMessage($this->_getAllParams()); // exit();
		$appconfig = new AppConfig();
		$appconfig->populate($this->_getParam('id'));
		//debugMessage($appconfig->toArray());
		
		// only upload a file if the attachment field is specified		
		$upload = new Zend_File_Transfer();
		// set the file size in bytes
		$upload->setOptions(array('useByteString' => false));
		
		// Limit the extensions to the specified file extensions
		$upload->addValidator('Extension', false, $config->uploads->photoallowedformats);
	 	$upload->addValidator('Size', false, $config->uploads->photomaximumfilesize);
		
 		$destination_path = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."system".DIRECTORY_SEPARATOR."logo";
 		
		// create archive folder for each user
		$archivefolder = $destination_path.DIRECTORY_SEPARATOR."archive";
		if(!is_dir($archivefolder)){
			mkdir($archivefolder, 0755);
		}
		
		$oldfilename = $appconfig->getLogo();
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
		
		// rename the base image file 
		$upload->addFilter('Rename',  array('target' => $thefilename, 'overwrite' => true));		
		// exit();
		// process the file upload
		if($upload->receive()){
			// debugMessage('Completed');
			$file = $upload->getFileInfo('profileimage');
			// debugMessage($file); exit();
			
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
			// unlink($thefilename);
			unlink($destination_path.DIRECTORY_SEPARATOR.'base_'.$currenttime.'.jpg');
			
			// exit();
			// update the useraccount with the new profile images
			try {
				$appconfig->setDescription($currenttime.'.jpg');
				$appconfig->save();
				
				// check if user already has profile picture and archive it
				$ftimestamp = current(explode('.', $appconfig->getLogo()));
				
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
				// debugMessage('uploaded');
				$session->setVar(SUCCESS_MESSAGE, "Successfully uploaded image. Please crop image to resize.");
				$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
			} catch (Exception $e) {
				$session->setVar(ERROR_MESSAGE, $e->getMessage());
				$session->setVar(FORM_VALUES, $this->_getAllParams());
				$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
			}
		} else {
			// debugMessage($upload->getMessages()); exit();
			$uploaderrors = $upload->getMessages();
			$customerrors = array();
			if(!isArrayKeyAnEmptyString('fileUploadErrorNoFile', $uploaderrors)){
				$customerrors['fileUploadErrorNoFile'] = "Please browse for image on computer";
			}
			if(!isArrayKeyAnEmptyString('fileExtensionFalse', $uploaderrors)){
				$custom_exterr = sprintf($this->_translate->translate('upload_invalid_ext_error'), $config->uploads->photoallowedformats);
				$customerrors['fileExtensionFalse'] = $custom_exterr;
			}
			if(!isArrayKeyAnEmptyString('fileUploadErrorIniSize', $uploaderrors)){
				$custom_exterr = sprintf($this->_translate->translate('upload_invalid_size_error'), formatBytes($config->uploads->photomaximumfilesize,0));
				$customerrors['fileUploadErrorIniSize'] = $custom_exterr;
			}
			if(!isArrayKeyAnEmptyString('fileSizeTooBig', $uploaderrors)){
				$custom_exterr = sprintf($this->_translate->translate('upload_invalid_size_error'), formatBytes($config->uploads->photomaximumfilesize,0));
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
		$type = $formvalues['type'];
		
		$appconfig = new AppConfig();
		$appconfig->populate($formvalues['id']);
		// debugMessage($formvalues);
		//debugMessage($farmgroup->toArray());
		
		$oldfile = "large_".$appconfig->getLogo();
		$base = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."system".DIRECTORY_SEPARATOR."logo".DIRECTORY_SEPARATOR;
		
		$src = $base.$oldfile;
		// debugMessage($src); exit();
		
		$currenttime = mktime();
		$currenttime_file = $currenttime.'.jpg';
		$newlargefilename = $base.$currenttime_file;
		
		// exit();
		$image = WideImage::load($src);
		$cropped1 = $image->crop($formvalues['x1'], $formvalues['y1'], $formvalues['w'], $formvalues['h']);
		$resized_1 = $cropped1->resize(260, 110, 'fill');
		$resized_1->saveToFile($newlargefilename);
		
		$appconfig->setDescription($currenttime_file);
		$appconfig->save();
			
		// check if user already has profile picture and archive it
		$ftimestamp = current(explode('.', $appconfig->getLogo()));
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
		$session->setVar(SUCCESS_MESSAGE, "Successfully updated image. Proceed to save changes.");
		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
    }
}

