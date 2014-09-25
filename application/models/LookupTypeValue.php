<?php

class LookupTypeValue extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('lookuptypevalue');
		$this->hasColumn('lookuptypeid', 'integer', null, array('notblank' => true));
		$this->hasColumn('lookuptypevalue', 'string', 50, array('notblank' => true));
		$this->hasColumn('lookupvaluedescription', 'string', 500);
		$this->hasColumn('alias', 'string', 500);
		$this->hasColumn('code', 'string', 500);
		$this->hasColumn('info', 'string', 65535);
	}
	
	function setUp() {
		parent::setUp();
		# Create the lookuptype vs value relationship
		$this->hasOne('LookupType as lookuptype',
						 array(
								'local' => 'lookuptypeid',
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
		if($this->valueExists()){
			$this->getErrorStack()->add("lookupvaluedescription.unique", "The value <b>".$this->getlookupvaluedescription()."</b> already exists. <br />Please specify another.");
		}
	}
	# determine if the refno has already been assigned to another organisation
	function valueExists($value =''){
		$conn = Doctrine_Manager::connection();
		$id_check = "";
		if(!isEmptyString($this->getID())){
			$id_check = " AND id <> '".$this->getID()."' ";
		}
		if(isEmptyString($value)){
			$value = $this->getlookupvaluedescription();
		}
		
		# unique value
		$value_query = "SELECT id FROM lookuptypevalue WHERE lookupvaluedescription = '".$value."' AND lookuptypeid = '".$this->getLookupTypeID()."' ".$id_check;
		// debugMessage($value_query);
		$value_result = $conn->fetchOne($value_query);
		// debugMessage($value_result);
		if(isEmptyString($value_result)){
			return false;
		}
		return true;
	}
	# return the description for a lookup type value
	function getDescriptionForLookupValue($thedescription) {
		$conn = Doctrine_Manager::connection(); 
		$resultvalues = $conn->fetchAll("SELECT lookupvaluedescription FROM lookuptypevalue WHERE lookuptypevalue = '".$thedescription."'");
		//debugMessage($resultvalues);
		foreach ($resultvalues as $value) {
			$lookupdescription = $value;
		}
		return $lookupdescription;			
	}
}
?>