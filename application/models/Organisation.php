<?php
/**
 * Model for payments
 */
class Organisation extends BaseEntity  {
	
	public function setTableDefinition() {
		parent::setTableDefinition();
		$this->setTableName('organisation');
		$this->hasColumn('refno', 'string', 15);
		$this->hasColumn('type', 'integer', null, array('default'=>'1'));
		$this->hasColumn('fraternity', 'integer', null);
		$this->hasColumn('name', 'string', 255, array('notblank' => true));
		$this->hasColumn('phone', 'string', 15);
		$this->hasColumn('phone2', 'string', 15);
		$this->hasColumn('email', 'string', 50); 
		$this->hasColumn('website', 'string', 255);
		$this->hasColumn('address1', 'string', 255);
		$this->hasColumn('address2', 'string', 255);
		$this->hasColumn('regionid', 'integer', null, array('default' => NULL));
		$this->hasColumn('provinceid', 'integer', null, array('default' => NULL));
		$this->hasColumn('orgdistrictid', 'integer', null, array('default' => NULL));
		$this->hasColumn('districtid', 'integer', null, array('default' => NULL));
		$this->hasColumn('countyid', 'integer', null, array('default' => NULL));
		$this->hasColumn('subcountyid', 'integer', null, array('default' => NULL));
		$this->hasColumn('parishid', 'integer', null, array('default' => NULL));
		$this->hasColumn('villageid', 'integer', null, array('default' => NULL));
		$this->hasColumn('gpslat', 'string', 15);
		$this->hasColumn('gpslng', 'string', 15);
		$this->hasColumn('vision', 'string', 255);
		$this->hasColumn('mission', 'string', 255);
		$this->hasColumn('bio', 'string', 1000);
		$this->hasColumn('profilephoto', 'string', 50);
		$this->hasColumn('leadid', 'integer', null);
		$this->hasColumn('leadname', 'string', 50);
		$this->hasColumn('leadrole', 'string', 50);
		$this->hasColumn('regdate','date', null);
		$this->hasColumn('membercount', 'string', 50);
	}
	
	/* protected $preupdatedata;
	
	function getPreUpdateData(){
		return $this->preupdatedata;
	}
	function setPreUpdateData($preupdatedata) {
		$this->preupdatedata = $preupdatedata;
	} */
	/**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		
		$this->addDateFields(array("regdate"));
		// set the custom error messages
		$this->addCustomErrorMessages(array(
										"name.notblank" => $this->translate->_("global_name_error"),
       	       						));     
	}
	/*
	 * Relationships for the model
	 */
	public function setUp() {
		parent::setUp(); 
		
		$this->hasOne('Member as lead',
				array(
						'local' => 'leadid',
						'foreign' => 'id'
				)
		);
		
		$this->hasOne('Region as region',
				array(
						'local' => 'regionid',
						'foreign' => 'id'
				)
		);
		$this->hasOne('Province as province',
				array(
						'local' => 'provinceid',
						'foreign' => 'id'
				)
		);
		$this->hasOne('Location as district',
				array(
						'local' => 'districtid',
						'foreign' => 'id'
				)
		);
		$this->hasOne('Location as orgdistrict',
				array(
						'local' => 'orgdistrictid',
						'foreign' => 'id'
				)
		);
		$this->hasOne('Location as county',
				array(
						'local' => 'countyid',
						'foreign' => 'id'
				)
		);
		$this->hasOne('Location as subcounty',
				array(
						'local' => 'subcountyid',
						'foreign' => 'id'
				)
		);
		$this->hasOne('Location as parish',
				array(
						'local' => 'parishid',
						'foreign' => 'id'
				)
		);
		$this->hasOne('Location as village',
				array(
						'local' => 'villageid',
						'foreign' => 'id'
				)
		);
	}
	/*
	 * Pre process model data
	 */
	function processPost($formvalues) {
		// trim spaces from the name field
		if(!isArrayKeyAnEmptyString('name', $formvalues)){
			$formvalues['name'] = ucfirst($formvalues['name']);
		}
		
		if(!isArrayKeyAnEmptyString('leadid', $formvalues) && isArrayKeyAnEmptyString('leadname', $formvalues)){
			$formvalues['leadname'] = '';
		}
		if(isArrayKeyAnEmptyString('leadname', $formvalues)){
			unset($formvalues['leadname']);
		}
		if(!isArrayKeyAnEmptyString('leadname', $formvalues) && isArrayKeyAnEmptyString('leadid', $formvalues)){
			$formvalues['leadid'] = NULL;
		}
		if(isArrayKeyAnEmptyString('regionid', $formvalues)){
			unset($formvalues['regionid']);
		}
		if(isArrayKeyAnEmptyString('provinceid', $formvalues)){
			unset($formvalues['provinceid']);
		}
		if(isArrayKeyAnEmptyString('orgdistrictid', $formvalues)){
			unset($formvalues['orgdistrictid']);
		}
		if(isArrayKeyAnEmptyString('districtid', $formvalues)){
			unset($formvalues['districtid']);
		}
		if(isArrayKeyAnEmptyString('countyid', $formvalues)){
			unset($formvalues['countyid']);
		}
		if(isArrayKeyAnEmptyString('subcountyid', $formvalues)){
			unset($formvalues['subcountyid']);
		}
		if(isArrayKeyAnEmptyString('parishid', $formvalues)){
			unset($formvalues['parishid']);
		}
		if(isArrayKeyAnEmptyString('villageid', $formvalues)){
			unset($formvalues['villageid']);
		}
		if(!isArrayKeyAnEmptyString('regdate', $formvalues)){
			$formvalues['regdate'] = changeDateFromPageToMySQLFormat($formvalues['regdate']);
		} else {
			if(!isArrayKeyAnEmptyString('datecreated', $formvalues)){
				$formvalues['regdate'] = $formvalues['datecreated'];
			}
		}
		// debugMessage($formvalues); exit();
		parent::processPost($formvalues);
	}
	
	function getGPSCordinates(){
		if(isEmptyString($this->getGPSLat()) || isEmptyString($this->getGPSLng())){
			return '';
		}
		return $this->getGPSLat().', '.$this->getGPSLng();
	}
	
	function hasGPSCoordinates(){
		if(!isEmptyString($this->getGPSLat()) && !isEmptyString($this->getGPSLng())){
			return true;
		}
		return false;
	}
	# relative path to profile image
	function hasProfileImage(){
		$real_path = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."organisations".DIRECTORY_SEPARATOR."org_";
		$real_path = $real_path.$this->getID().DIRECTORY_SEPARATOR."cover".DIRECTORY_SEPARATOR."medium_".$this->getProfilePhoto();
		// debugMessage($real_path);
		if(file_exists($real_path) && !isEmptyString($this->getProfilePhoto())){
			return true;
		}
		return false;
	}
	# determine path to medium profile picture
	function getMediumPicturePath() {
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$path = "";
		$path = $baseUrl.'/uploads/default/default_cover.png';
		if($this->hasProfileImage()){
			$path = $baseUrl.'/uploads/organisations/org_'.$this->getID().'/cover/medium_'.$this->getProfilePhoto();
	}
		// debugMessage($path);
		return $path;
	}
	# determine path to large profile picture
	function getLargePicturePath() {
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$path = $baseUrl.'/uploads/default/default_cover.png';
		if($this->hasProfileImage()){
			$path = $baseUrl.'/uploads/organisations/org_'.$this->getID().'/cover/large_'.$this->getProfilePhoto();
		}
		# debugMessage($path);
		return $path;
	}
	# determine number of members in the organisation
	function countRegistered(){
		$conn = Doctrine_Manager::connection(); 
		$query = "SELECT count(id) from member where organisationid = '".$this->getID()."' ";
		return $conn->fetchOne($query);
	}
	# determine appointments for a member
	function getAppointments(){
		$query = Doctrine_Query::create()->from('Appointment a')
		->where("a.organisationid = '".$this->getID()."'")->orderby("a.startdate desc");
		// debugMessage($query->getSQLQuery());
		$result = $query->execute();
		if($result){
			return $result;
		}
		return new Appointment();
	}
	function afterSave(){
		$session = SessionWrapper::getInstance();
		# add log to audit trail
		$view = new Zend_View();
		$url = $view->serverUrl($view->baseUrl('organisation/view/id/'.encode($this->getID())));
		$profiletype = 'Church Profile ';
		$usecase = '3.1';
		$module = '3';
		$type = ORG_CREATE;
		if($this->getType() == '2'){
			$profiletype = 'Ministry Profile ';
		}
			
		$browser = new Browser();
		$audit_values = $session->getVar('browseraudit');
		$audit_values['module'] = $module;
		$audit_values['usecase'] = $usecase;
		$audit_values['transactiontype'] = $type;
		$audit_values['status'] = "Y";
		$audit_values['userid'] = $session->getVar('userid');
		$audit_values['transactiondetails'] = $profiletype." <a href='".$url."' class='blockanchor'>".$this->getName()."</a> created";
		$audit_values['url'] = $url;
		// debugMessage($audit_values);
		$this->notify(new sfEvent($this, $type, $audit_values));
	}
	# update after
	function beforeUpdate(){
		$session = SessionWrapper::getInstance();
		# set object data to class variable before update
		$org = new Organisation();
		$org->populate($this->getID());
		$this->setPreUpdateData($org->toArray()); // debugMessage($this->toAray());
		// exit;
		return true;
	}
	
	function afterUpdate(){
		$session = SessionWrapper::getInstance();
		
		# set postupdate from class object, and then save to audit
		$prejson = json_encode($this->getPreUpdateData()); // debugMessage($prejson); exit;
		 
		$this->clearRelated(); // clear any current object relations
		$after = $this->toArray(); // debugMessage($after);
		$postjson = json_encode($after); debugMessage($postjson);
		 
		$diff = array_diff($this->getPreUpdateData(), $after);  // debugMessage($diff);
		$jsondiff = json_encode($diff); // debugMessage($jsondiff);

		$view = new Zend_View();
		$url = $view->serverUrl($view->baseUrl('organisation/view/id/'.encode($this->getID())));
		$profiletype = 'Church Profile ';
		$usecase = '3.2';
		$module = '3';
		$type = ORG_UPDATE;
		if($this->getType() == '2'){
			$profiletype = 'Ministry Profile ';
		}
		 
		$browser = new Browser();
		$audit_values = $session->getVar('browseraudit');
		$audit_values['module'] = $module;
		$audit_values['usecase'] = $usecase;
		$audit_values['transactiontype'] = $type;
		$audit_values['status'] = "Y";
		$audit_values['userid'] = $session->getVar('userid');
		$audit_values['transactiondetails'] = $profiletype." <a href='".$url."' class='blockanchor'>".$this->getName()."</a> updated";
		$audit_values['isupdate'] = 1;
		$audit_values['prejson'] = $prejson;
		$audit_values['postjson'] = $postjson;
		$audit_values['jsondiff'] = $jsondiff;
		$audit_values['url'] = $url;
		// debugMessage($audit_values);
		$this->notify(new sfEvent($this, $type, $audit_values));
	
		return true;
	}
	
	function getTypeLabel(){
		$label = "Organisation";
		if($this->getType() == '1'){
			$label = "Church";
		}
		if($this->getType() == '2'){
			$label = "Ministry";
		}
		return  $label;
	}
	
	function isChurch(){
		return  $this->getType() == 1 ? true : false;
	}
	
	function isMinistry(){
		return  $this->getType() == 2 ? true : false;
	}
}

?>