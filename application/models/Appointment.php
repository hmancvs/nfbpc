<?php

class Appointment extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('appointment');
		$this->hasColumn('memberid', 'integer', null, array('notblank' => true));
		$this->hasColumn('organisationid', 'integer', null);
		$this->hasColumn('committeeid', 'integer', null);
		$this->hasColumn('positionid', 'integer', null, array('notblank' => true));
		$this->hasColumn('departmentid', 'integer', null);
		$this->hasColumn('locationid', 'integer', null);
		$this->hasColumn('level', 'integer', null);
		$this->hasColumn('startdate','date', null);
		$this->hasColumn('expectedenddate','date', null);
		$this->hasColumn('actualenddate','date', null);
		$this->hasColumn('tenure', 'string', 50);
		$this->hasColumn('status', 'integer', null);
		$this->hasColumn('endreason', 'string', 255);
	}
	
	# Contructor method for custom initialization
	public function construct() {
		parent::construct();
		
		$this->addDateFields(array("startdate","expectedenddate","actualenddate"));
		# set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"memberid.notblank" => $this->translate->_("appointment_memberid_error"),
       									"positionid.notblank" => $this->translate->_("appointment_positionid_error")
       	       						));
	}
	
	# Model relationships
	public function setUp() {
		parent::setUp(); 
		
		$this->hasOne('Member as member',
				array(
						'local' => 'memberid',
						'foreign' => 'id'
				)
		);
		
		$this->hasOne('Committee as committee',
				array(
						'local' => 'committeeid',
						'foreign' => 'id'
				)
		);
		
		$this->hasOne('Position as position',
				array(
						'local' => 'positionid',
						'foreign' => 'id'
				)
		);
		
		$this->hasOne('Department as department',
				array(
						'local' => 'departmentid',
						'foreign' => 'id'
				)
		);
		
		$this->hasOne('Location as location',
				array(
						'local' => 'locationid',
						'foreign' => 'id'
				)
		);
		
		$this->hasOne('Organisation as organisation',
				array(
						'local' => 'organisationid',
						'foreign' => 'id'
				)
		);
	}
	
	/**
	 * Custom model validation
	 */
	function validate() {
		# execute the column validation
		parent::validate();
		// debugMessage($this->toArray(true));
		# validate that username is unique
		if($this->appointmentExists()){
			$error = "Role <b>".$this->getPosition()->getName()."</b> already exists for <b>".$this->getMember()->getName()."</b>";
			$this->getErrorStack()->add("role.unique", $error);
		}
	}
	/**
	 * Preprocess model data
	 */
	function processPost($formvalues){
		$session = SessionWrapper::getInstance();
		if(isArrayKeyAnEmptyString('departmentid', $formvalues)){
			unset($formvalues['departmentid']);
		}
		if(isArrayKeyAnEmptyString('locationid', $formvalues)){
			unset($formvalues['locationid']);
		}
		if(isArrayKeyAnEmptyString('level', $formvalues)){
			unset($formvalues['level']);
		}
		if(isArrayKeyAnEmptyString('organisationid', $formvalues)){
			unset($formvalues['organisationid']);
		}
		if(isArrayKeyAnEmptyString('startdate', $formvalues)){
			unset($formvalues['startdate']);
		}
		if(isArrayKeyAnEmptyString('expectedenddate', $formvalues)){
			unset($formvalues['expectedenddate']);
		}
		if(isArrayKeyAnEmptyString('actualenddate', $formvalues)){
			unset($formvalues['actualenddate']);
		}
	
		if(!isArrayKeyAnEmptyString('startyear', $formvalues) && !isArrayKeyAnEmptyString('startmonth', $formvalues)){
			$formvalues['startdate'] = $formvalues['startyear'].'-'.$formvalues['startmonth'].'-1';
		}
		if(!isArrayKeyAnEmptyString('endyear', $formvalues) && !isArrayKeyAnEmptyString('endmonth', $formvalues)){
			$formvalues['expectedenddate'] = $formvalues['endyear'].'-'.$formvalues['endmonth'].'-1';
		}
		if(!isArrayKeyAnEmptyString('actualendyear', $formvalues) && !isArrayKeyAnEmptyString('actualendmonth', $formvalues)){
			$formvalues['actualenddate'] = $formvalues['actualendyear'].'-'.$formvalues['actualendmonth'].'-1';
		}
		
		if(!isArrayKeyAnEmptyString('committeeid', $formvalues)){ 
			if($formvalues['committeeid'] == 12 && !isArrayKeyAnEmptyString('subcountyid', $formvalues)){
				$formvalues['locationid'] = $formvalues['subcountyid'];
			}
			if($formvalues['committeeid'] == 11 && !isArrayKeyAnEmptyString('districtid', $formvalues)){
				$formvalues['locationid'] = $formvalues['districtid'];
			}
			if($formvalues['committeeid'] == 10 && !isArrayKeyAnEmptyString('provinceid', $formvalues)){
				$formvalues['locationid'] = $formvalues['provinceid'];
			}
			if($formvalues['committeeid'] == 9 && !isArrayKeyAnEmptyString('regionid', $formvalues)){
				$formvalues['locationid'] = $formvalues['regionid'];
			}
		}
		// debugMessage($formvalues); exit();
		parent::processPost($formvalues);
	}
	# determine if the role has already been assigned to the member
	function appointmentExists(){
		$conn = Doctrine_Manager::connection();
		# validate unique username and email
		$id_check = "";
		if(!isEmptyString($this->getID())){
			$id_check = " AND id <> '".$this->getID()."' ";
		}
		$custom_query = '';
		if(!isEmptyString($this->getDepartmentID())){
			$custom_query = " AND departmentid = '".$this->getDepartmentID()."' ";
		}
		if(!isEmptyString($this->getCommitteeID())){
			$custom_query = " AND committeeid = '".$this->getCommitteeID()."' ";
		}
		if(!isEmptyString($this->getOrganisationID())){
			$custom_query = " AND organisationid = '".$this->getOrganisationID()."' ";
		}
		
		$query = "SELECT id FROM appointment WHERE positionid = '".$this->getPositionID()."' AND memberid = '".$this->getMemberID()."' ".$custom_query." ".$id_check;
		// debugMessage($query);
		$result = $conn->fetchOne($query);
		// debugMessage($result);
		if(isEmptyString($result)){
			return false;
		}
		return true;
	}
	# determine status text
	function getStatusText(){
		$txt = '';
		if($this->getStatus() == '1'){
			$txt = 'Active';
		}
		if($this->getStatus() == '2'){
			$txt = 'In-active';
		}
		if($this->getStatus() == '0'){
			$txt = 'Terminated';
		}
		return $txt;
	}
	# determine the level
	function getLevelText(){
		switch ($this->getLevel()) {
			case 1:
				$txt = 'National';
				break;
			case 2:
				$txt = 'Provincial';
				break;
			case 3:
				$txt = 'District';
				break;
			case 4:
				$txt = 'Sub-county';
				break;
			default:
				$txt = '';
				break;
		}
		return $txt;
	}
	#determine if is region committee or department
	function isRegion(){
		if($this->getCommitteeID() == '9'){
			return true;
		}
		return false;
	}
	#determine if is province committee or  department
	function isProvince(){
		if($this->getCommitteeID() == '10'){
			return true;
		}
		return false;
	}
	#determine if is district committee or department
	function isDistrict(){
		if($this->getCommitteeID() == '11'){
			return true;
		}
		return false;
	}
	#determine if is subcounty committee or department
	function isSubcounty(){
		if($this->getCommitteeID() == '12'){
			return true;
		}
		return false;
	}
	# determine the regionid
	function getRegionID(){
		if(isEmptyString($this->getLocationID())){
			return '';
		}
		if($this->isRegion()){
			return $this->getLocationID();
		} else {
			return $this->getLocation()->getDistrict()->getNFBPCRegionID();
		}
	}
	# determine the provinceid
	function getProvinceID(){
		if(isEmptyString($this->getLocationID())){
			return '';
		}
		if($this->isProvince()){
			return $this->getLocationID();
		} else {
			return $this->getLocation()->getDistrict()->getProvinceID();
		}
	}
	# determine the districtid
	function getDistrictID(){
		if(isEmptyString($this->getLocationID())){
			return '';
		}
		if($this->isDistrict()){
			return $this->getLocationID();
		} else {
			return $this->getLocation()->getDistrictID();
		}
	}
	# determine the districtid
	function getSubcountyID(){
		if(isEmptyString($this->getLocationID())){
			return '';
		}
		return $this->getLocationID();
	}
	# determine if entry has a department
	function hasLocation(){
		return isEmptyString($this->getLocationID()) ? false : true;
	}
	# location type
	function getLocationType(){
    	$typelabel = '';
	    if($this->isRegion()){
	    	$typelabel = 'Region';
	    }
		if($this->isProvince()){
	    	$typelabel = 'Province';
	    }
		if($this->isDistrict()){
	    	$typelabel = 'District';
	    }
	   
	    return $typelabel;
    }
	function getTheLocationName(){
		$name = $this->getLocation()->getName();
	    if($this->isRegion()){
			$region = new Region();
			$region->populate($this->getLocationID());
	    	$name = $region->getName();
	    }
		if($this->isProvince()){
	    	$province = new Province();
			$province->populate($this->getLocationID());
	    	$name = $province->getName();
	    }
	   
	    return $name;
	}
}
?>
