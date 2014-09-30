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
	}
	/**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		
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
		
		$this->hasOne('Region as regionid',
				array(
						'local' => 'regionid',
						'foreign' => 'id'
				)
		);
		$this->hasOne('Province as provinceid',
				array(
						'local' => 'regionid',
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
		if(isArrayKeyAnEmptyString('leadid', $formvalues)){
			unset($formvalues['leadid']);
		}
		if(isArrayKeyAnEmptyString('regionid', $formvalues)){
			unset($formvalues['regionid']);
		}
		if(isArrayKeyAnEmptyString('provinceid', $formvalues)){
			unset($formvalues['provinceid']);
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
		// debugMessage($formvalues); // exit();
		parent::processPost($formvalues);
	}
	
}

?>