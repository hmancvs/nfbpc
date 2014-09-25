<?php

class Position extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('position');
		$this->hasColumn('code', 'string', 10);
		$this->hasColumn('name', 'string', 255, array('notblank' => true));
		$this->hasColumn('description', 'string', 1000);
		$this->hasColumn('title', 'string', 50);
		$this->hasColumn('abbr', 'string', 50);
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
		
		debugMessage($formvalues); // exit();
		parent::processPost($formvalues);
	}
}
?>
