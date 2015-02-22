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
	 		$action == "uploadpicture" || $action == "updatebio"
		){
	 		return ACTION_EDIT;
	 	}
	 	if($action = "bulkupload" || $action == "processbulkupload"){
	 		return ACTION_CREATE;
	 	}
	 	if($action = "search" || $action = "searchparish" || $action = "populatemember"){
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
    			
	    		$url = $this->view->serverUrl($this->view->baseUrl('member/view/id/'.encode($member->getID())));
	    		$usecase = '2.4';
	    		$module = '2';
	    		$type = MEMBER_UPLOADPHOTO;
	    		$details = "New Profile Photo uploaded for <a href='".$url."' class='blockanchor'>".$member->getName()."</a>";
	    		 
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
    
    	$conn = Doctrine_Manager::connection();
		$session = SessionWrapper::getInstance();
    	$config = Zend_Registry::get("config");
    	$this->_translate = Zend_Registry::get("translate");
    
    	$formvalues = $this->_getAllParams();
    	// debugMessage($formvalues);
    	//exit();
    	
    	$q = $formvalues['term'];
    	$query = "SELECT 
    	l.id as villageid, 
    	l.id as id, 
    	l.locationtype as locationtype, 
    	l.name as name,
    	l.name as village,
    	l.nfbpcregionid as regionid, 
    	r.name as region,
    	l.provinceid, 
    	p.name as province, 
    	l.districtid, 
    	d.name as district, 
    	l.countyid, 
    	c.name as county, 
    	l.subcountyid, 
    	s.name as subcounty, 
    	l.parishid,
    	ps.name as parish  
    	FROM location as l
		left join location d on (l.districtid = d.id and d.locationtype = 2)
		left join region r on (d.nfbpcregionid = r.id) 
		left join province p on (d.provinceid = p.id) 
		left join location c on (l.countyid = c.id and c.locationtype = 3)
		left join location s on (l.subcountyid = s.id and s.locationtype = 4)
		left join location ps on (l.parishid = ps.id and ps.locationtype = 5)
    	WHERE l.name like '".$q."%' AND l.locationtype = 6
    	GROUP BY l.id order by l.name asc ";
    	// debugMessage($query);
    	$data = $conn->fetchAll($query);
    	$count_results = count($data);
    	// debugMessage($data);
    	
    	echo json_encode($data);
    }
    
    public function searchparishAction() {
    	// disable rendering of the view and layout so that we can just echo the AJAX output
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    
    	$conn = Doctrine_Manager::connection();
    	$session = SessionWrapper::getInstance();
    	$config = Zend_Registry::get("config");
    	$this->_translate = Zend_Registry::get("translate");
    
    	$formvalues = $this->_getAllParams();
    	// debugMessage($formvalues);
    	//exit();
    	 
    	$q = $formvalues['term'];
    	$query = "SELECT
    	l.id as parishid,
    	l.id as id,
    	l.locationtype as locationtype,
    	l.name as name,
    	l.name as parish,
    	l.nfbpcregionid as regionid,
    	r.name as region,
    	l.provinceid,
    	p.name as province,
    	l.districtid,
    	d.name as district,
    	l.countyid,
    	c.name as county,
    	l.subcountyid,
    	s.name as subcounty
    	FROM location as l
    	left join location d on (l.districtid = d.id and d.locationtype = 2)
    	left join region r on (d.nfbpcregionid = r.id)
    	left join province p on (d.provinceid = p.id)
    	left join location c on (l.countyid = c.id and c.locationtype = 3)
    	left join location s on (l.subcountyid = s.id and s.locationtype = 4)
    	WHERE l.name like '".$q."%' AND l.locationtype = 5
    	GROUP BY l.id order by l.name asc ";
    	// debugMessage($query);
    	$data = $conn->fetchAll($query);
    	$count_results = count($data);
    	// debugMessage($data);
    	 
    	echo json_encode($data);
    }
    
    function populatememberAction(){
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    
    	// debugMessage($this->_getAllParams());
    	$member = new Member();
    	$member->populate($this->_getParam('memberid'));
    
    	$resultarray = array(
    			'id' => $member->getID(),
    			'email' => $member->getEmail()
    	);
    	echo json_encode($resultarray);
    }
    
    public function updatebioAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    
    	$session = SessionWrapper::getInstance();
    	$config = Zend_Registry::get("config");
    	$this->_translate = Zend_Registry::get("translate");
    	$formvalues = $this->_getAllParams();
    	$id =  decode($formvalues['id']);
    	 
    	// debugMessage($formvalues);
    	$member = new Member();
    	$member->populate($id);
    	 
    	$member->setBio($formvalues['bio']);
    	/* debugMessage($member->toArray());
    	debugMessage('error '.$member->getErrorStackAsString()); exit(); */
    	 
    	try {
    		$member->save();
    		$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate($this->_getParam(SUCCESS_MESSAGE)));
    		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
    	} catch (Exception $e) {
    		$session->setVar(ERROR_MESSAGE, $e->getMessage());
    		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
    	}
    }
    
    public function bulkuploadAction() {
    	
    }
    
    public function processbulkuploadAction() {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	
    	$session = SessionWrapper::getInstance();
    	$config = Zend_Registry::get("config");
    	$this->_translate = Zend_Registry::get("translate");
    	$formvalues = $this->_getAllParams(); debugMessage($formvalues);
    	
    	// only upload a file if the attachment field is specified
    	$upload = new Zend_File_Transfer();
    	// set the file size in bytes
    	$upload->setOptions(array('useByteString' => false));
    	
    	// Limit the extensions to the specified file extensions
    	$upload->addValidator('Extension', false, $config->uploads->docallowedformats);
    	$upload->addValidator('Size', false, $config->uploads->docmaximumfilesize);
    	
    	// base path for profile pictures
    	$destination_path = BASE_PATH.DIRECTORY_SEPARATOR."temp";
    	
    	//debugMessage($destination_path);
    	$upload->setDestination($destination_path);
    	
    	// the profile image info before upload
    	$file = $upload->getFileInfo('filename');
    	$uploadedext = findExtension($file['filename']['name']);
    	$currenttime = mktime();
    	$currenttime_file = $currenttime.'.'.$uploadedext;
    	
    	$thefilename = $destination_path.DIRECTORY_SEPARATOR.$currenttime_file;
    	//debugMessage($thefilename);
    	// rename the base image file
    	$upload->addFilter('Rename',  array('target' => $thefilename, 'overwrite' => true));
    	// exit();
    	// process the file upload
    	if($upload->receive()){
    		// debugMessage('Completed');
    		$file = $upload->getFileInfo('filename'); // debugMessage($file); 
    		$csvpath = $file['filename']['tmp_name']; // debugMessage($csvpath); // exit();
    		$cvsarray = ImportCSV2Array($csvpath); // debugMessage($cvsarray); exit();
    		
    		$memberfails = array(); $issuesstr = '';
    		$member_collection = new Doctrine_Collection(Doctrine_Core::getTable("Member"));
    		foreach ($cvsarray as $key => $value){
    			if(!isArrayKeyAnEmptyString('id', $value)){
    				$member = new Member();
    				$member->populate($value['id']); 
    				
    				foreach ($value as $field => $data){
    					if(isArrayKeyAnEmptyString($field, $value)){
    						 unset($value[$field]);
    					}
    				}
    				
    				if(!isArrayKeyAnEmptyString('committeeid', $value) && !isArrayKeyAnEmptyString('positionid', $value)){
    					$value['locationid'] = NULL;
    					$value['committeeid'] = $value['committeid'];
    					if($value['committeeid'] == 12 && !isArrayKeyAnEmptyString('subcountyid', $value)){
    						$value['locationid'] = $member->getSubCountyID();
    					}
    					if($value['committeeid'] == 11 && !isArrayKeyAnEmptyString('districtid', $value)){
    						$value['locationid'] = $member->getDistrictID();
    					}
    					if($value['committeeid'] == 10 && !isArrayKeyAnEmptyString('provinceid', $value)){
    						$value['locationid'] = $member->getPID();
    					}
    					if($value['committeeid'] == 9 && !isArrayKeyAnEmptyString('regionid', $value)){
    						$value['locationid'] = $member->getRegionID();
    					}
    						
    					$appointment = array(
    							array(
    									"committeeid" => $value['committeeid'],
    									"positionid" => $value['positionid'],
    									"departmentid" => $value['departmentid'],
    									"locationid" => $value['locationid'],
    									"startdate" => "2013-06-01",
    									"enddate" => "2018-06-30",
    									"status" => "1",
    									"createdby" => $session->getVar('userid'),
    							)
    					);
    						
    					$value['appointments'] = $appointment; // debugMessage($appointment);
    				}
    				
    				debugMessage($value);
    				$member->processPost($value); debugMessage($member->toArray());
    				
    			} else{
    				if(isArrayKeyAnEmptyString('firstname', $value) && isArrayKeyAnEmptyString('lastname', $value) && isArrayKeyAnEmptyString('displayname', $value)){
    					unset($cvsarray[$key]); unset($value);
    				} else {
    				
    				
    				
    				}
    			} 
    				
    		}
    		
    		
    		exit();
    		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
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
    		$session->setVar(ERROR_MESSAGE, 'The following errors occured <ul><li>'.implode('</li><li>', $customerrors).'</li></ul>'); debugMessage('The following errors occured <ul><li>'.implode('</li><li>', $customerrors).'</li></ul>');
    		$session->setVar(FORM_VALUES, $this->_getAllParams());
    		// $this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
    	}
    }
}
