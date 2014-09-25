<?php
/**
 * Model for a location
 *
 */

class Location extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('location');
		$this->hasColumn('name', 'string', 255, array('notnull' => true, 'notblank' => true));
		$this->hasColumn('code', 'string', 10);
		$this->hasColumn('description', 'string', 500);
		$this->hasColumn('locationtype', 'tinyint');
		//0=NFBPC Region, 7=Province, 1=Region, 2=District, 3=County, 4=Subcounty, 5=Parish, 6=Village
		$this->hasColumn('country', 'string', 2, array('default' => 'UG'));
		$this->hasColumn('regionid','integer', null, array('default' => NULL));
		$this->hasColumn('nfbpcregionid','integer', null, array('default' => NULL));
		$this->hasColumn('provinceid','integer', null, array('default' => NULL));
		$this->hasColumn('districtid','integer', null, array('default' => NULL));
		$this->hasColumn('countyid','integer', null, array('default' => NULL));
		$this->hasColumn('subcountyid','integer', null, array('default' => NULL));
		$this->hasColumn('parishid','integer', null, array('default' => NULL));
		$this->hasColumn('gpslat', 'string', 15);
		$this->hasColumn('gpslng', 'string', 15);
	}
	/**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		// set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"name.notblank" => $this->translate->_("region_name_error")
       	       						));
	}
	public function setUp() {
		parent::setUp(); 
		# the relationships to the different location types
		$this->hasOne('Location as region',
						 array(
								'local' => 'regionid',
								'foreign' => 'id'
							)
					); 
		$this->hasOne('Region as nfbpcregion',
				array(
						'local' => 'nfbpcregionid',
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
	}
	/*
	 * 
	 */
	function processPost($formvalues){
		// Custom processing for integer and enum fields
		if(isArrayKeyAnEmptyString('regionid', $formvalues)) {
			$formvalues['regionid'] = NULL;
		} 
		if (isArrayKeyAnEmptyString('nfbpcregionid', $formvalues)) {
			$formvalues['nfbpcregionid'] = NULL;
		}
		if (!isArrayKeyAnEmptyString('districtid', $formvalues) ) {
			$location = new Location();
			$location->populate($formvalues['districtid']);
			$formvalues['nfbpcregionid'] = $formvalues['regionid'];
			$formvalues['regionid'] = $location->getRegionID();
		}
		if ($formvalues['locationtype'] == 2) {
			$formvalues['nfbpcregionid'] = $formvalues['regionid'];
			$formvalues['regionid'] = NULL;
		}
		if (isArrayKeyAnEmptyString('provinceid', $formvalues)) {
			$formvalues['provinceid'] = NULL;
		}
		if (isArrayKeyAnEmptyString('districtid', $formvalues)) {
			$formvalues['districtid'] = NULL;
		}
		if (isArrayKeyAnEmptyString('countyid', $formvalues)) {
			$formvalues['countyid'] = NULL;
		}
		if (isArrayKeyAnEmptyString('subcountyid', $formvalues)) {
			$formvalues['subcountyid'] = NULL;
		}
		if (isArrayKeyAnEmptyString('parishid', $formvalues)) {
			$formvalues['parishid'] = NULL;
		}
		if (isArrayKeyAnEmptyString('villageid', $formvalues)) {
			$formvalues['villageid'] = NULL;
		}
		debugMessage($formvalues);
		parent::processPost($formvalues);
	}
	/*
	 * 
	 */
	function validate() {
		// execute validation in parent
		parent::validate();
		
		# check that region is unique for locationtype = 1
		if (!$this->locationExists()) {
			$this->getErrorStack()->add("name.unique", sprintf($this->translate->_("location_unique_name_label"), $this->getName()));
		}
	}
	/*
	 * Validate that the location name is unique depending on the location type 
	 */
	function locationExists() {
		// connection		
		$conn = Doctrine_Manager::connection();
		
		// query for check if location exists
		$unique_query = "SELECT id FROM location WHERE name = '".$this->getName()."' AND locationtype = '".$this->getLocationType()."' AND id <> '".$this->getID()."' ";
		$result = $conn->fetchOne($unique_query);
		//debugMessage($unique_query);
		//debugMessage($result);
		if(!isEmptyString($result)){ 
			return false;
		}
		
		return true;
	}
	
	# determine commodityid from searchable name
    function findByName($name, $type) {
    	$str_len = strlen($name);
    	trim($name);
    	$name = str_replace('district', '', strtolower($name));
    	
		$conn = Doctrine_Manager::connection();
		// query for check if location exists
		$unique_query = "SELECT id FROM location l WHERE (lower(l.name) LIKE lower('%".$name."%') OR lower(l.alias) LIKE lower('%".$name."%')) AND l.locationtype = '".$type."' ";
		$result = $conn->fetchOne($unique_query);
		// debugMessage($unique_query);
		// debugMessage($result);
		return $result; 
	}
	# determine if location is a region
	function isRegion(){
		return $this->getLocationType() == 1 ? true : false;
	}
	# determine if location is an NFBPC region
	function isNFBPCRegion(){
		return $this->getLocationType() == 0 ? true : false;
	}
	# determine if location is a province
	function isProvince(){
		return $this->getLocationType() == 7 ? true : false;
	}
	# determine if location is a district
	function isDistrict(){
		return $this->getLocationType() == 2 ? true : false;
	}
	# determine if location is a county
	function isCounty(){
		return $this->getLocationType() == 3 ? true : false;
	}
	# determine if location is a subcounty
	function isSubcounty(){
		return $this->getLocationType() == 4 ? true : false;
	}
	# determine if location is a parish
	function isParish(){
		return $this->getLocationType() == 5 ? true : false;
	}
	# determine if location is a village
	function isVillage(){
		return $this->getLocationType() == 6 ? true : false;
	}
	# determine if location has gps location so as to plot out their data
    function hasGPSCoordinates() {
    	return !isEmptyString($this->getGPSLat()) && !isEmptyString($this->getGPSLng()) ? true : false;
    }
}

?>