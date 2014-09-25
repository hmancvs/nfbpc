<?php

class Committee extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('committee');
		$this->hasColumn('code', 'string', 10);
		$this->hasColumn('name', 'string', 255, array('notblank' => true));
		$this->hasColumn('description', 'string', 1000);
		$this->hasColumn('abbr', 'string', 50);
		$this->hasColumn('isfeatured', 'integer', null);
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
		
	}
	/**
	 * Preprocess model data
	 */
	function processPost($formvalues){
		$session = SessionWrapper::getInstance();
		if(isArrayKeyAnEmptyString('isfeatured', $formvalues)){
			unset($formvalues['isfeatured']);
		}
		
		// debugMessage($formvalues); // exit();
		parent::processPost($formvalues);
	}
}
?>
