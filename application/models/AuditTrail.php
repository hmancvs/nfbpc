<?php

class AuditTrail extends BaseRecord {
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('audittrail');
		$this->hasColumn('userid', 'integer', null);
		$this->hasColumn('module', 'integer', null);
		$this->hasColumn('usecase', 'string', 50);
		$this->hasColumn('transactiontype', 'string', 50, array('notblank' => true));
		$this->hasColumn('transactiondetails', 'string', 1000);
		$this->hasColumn('transactiondate','timestamp', null, array('notblank' => true, 'default' => date("Y-m-d H:i:s")));
		$this->hasColumn('status', 'enum', null, array('values' => array(1 => 'Y', 0 => 'N'), 'default' => 'N'));
		
		$this->hasColumn('url', 'string', 1000);
		$this->hasColumn('isupdate', 'integer', null, array('default' => '0'));
		$this->hasColumn('prejson', 'string', 65536);
		$this->hasColumn('postjson', 'string', 65536);
		$this->hasColumn('jsondiff', 'string', 65536);
		
		$this->hasColumn('browserdetails', 'string', 1000);
		$this->hasColumn('browser', 'string', 50);
		$this->hasColumn('version', 'string', 50);
		$this->hasColumn('useragent', 'string', 255);
		$this->hasColumn('os', 'string', 50);
		$this->hasColumn('ismobile', 'string', 50);
		$this->hasColumn('ipaddress', 'string', 50);
	}
	/**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		//$this->addDateFields(array()); 
		// set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"userid.notblank" => "No User specified",
       									"transactiontype.notblank" => "No transaction type specified",
       									"transactiondetails.notblank" => "No transaction details specified",	
       									"transactiondate.notblank" => "No transaction date specified"
       								));
       
		parent::construct();		
	}
	
	public function setUp() {
		parent::setUp(); 
		# member being audited
		$this->hasOne('Member as user',
						 array(
								'local' => 'userid',
								'foreign' => 'id'
							)
					); 
	}
	/**
	 * Clean up the post array before populating the values of the object:
	 * - Remove the blank values for the executedby field which will cause a foreign key error
	 *
	 * @param Array $post_array The post array
	 * 
	 * @see BaseRecord::processPost
	 */
	public function processPost($formvalues) {
		// remove the executedby field if it is empty
		if (isArrayKeyAnEmptyString('isupdate', $formvalues)) {
			unset($formvalues['isupdate']); 
		}
		if (isArrayKeyAnEmptyString('userid', $formvalues)) {
			unset($formvalues['userid']);
		}
		parent::processPost($formvalues); 
	}	
}
?>
