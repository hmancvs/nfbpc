<?php

class Department extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('department');
		$this->hasColumn('code', 'string', 10);
		$this->hasColumn('name', 'string', 255, array('notblank' => true));
		$this->hasColumn('description', 'string', 65535);
		$this->hasColumn('abbr', 'string', 50);
		$this->hasColumn('filename', 'string', 255);
		$this->hasColumn('level', 'string', 50);
		$this->hasColumn('parentid', 'string', 50);
		$this->hasColumn('defaultemail', 'string', 255);
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
		if(!isArrayKeyAnEmptyString('name', $formvalues)){
			$formvalues['name'] = ucfirst($formvalues['name']);
		}
		// debugMessage($formvalues); // exit();
		parent::processPost($formvalues);
	}
	
	# determine the minister incharge of the ministry
	function getMinister(){
		# query active user details using email
		$q = Doctrine_Query::create()->from('Appointment a')->where("a.departmentid = '".$this->getID()."' AND a.positionid = '5' AND a.status = 1 ");
		// debugMessage($q->fetchOne()->toArray());
		$result = $q->execute();
		if($result){
			return $result->get(0);
		}
		return new Appointment();
	}
}
?>
