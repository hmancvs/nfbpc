<?php

class Province extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('province');
		$this->hasColumn('name', 'string', 50, array("notblank" => true));
		$this->hasColumn('code', 'string', 50);
		$this->hasColumn('pcode', 'string', 50);
		$this->hasColumn('description', 'string', 1000);
		$this->hasColumn('regionid', 'integer', null, array('notblank' => true));
	}
	
	# Contructor method for custom initialization
	public function construct() {
		parent::construct();
		
		# set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"name.notblank" => $this->translate->_("global_name_error")
       	       						));
	}
	
	# Model relationships
	public function setUp() {
		parent::setUp(); 
		
		$this->hasOne('Region as region',
				array(
						'local' => 'regionid',
						'foreign' => 'id'
				)
		);
		
	}
	/**
	 * Preprocess model data
	 */
	function processPost($formvalues){
		$session = SessionWrapper::getInstance();
		if(isArrayKeyAnEmptyString('regionid', $formvalues)){
			unset($formvalues['regionid']);
		}
		
		// debugMessage($formvalues); // exit();
		parent::processPost($formvalues);
	}
}
?>
