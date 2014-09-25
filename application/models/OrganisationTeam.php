<?php

class OrganisationTeam extends BaseRecord  {
	
	public function setTableDefinition() {
		parent::setTableDefinition();
		$this->setTableName('organisationteam');
		
		$this->hasColumn('id', 'integer', 11, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('organisationid', 'integer', 11, array("notblank" => true);
		$this->hasColumn('memberid', 'integer', null, array('default' => NULL));
		$this->hasColumn('role', 'integer', null, array("notblank" => true);
	}
	
	public function setUp() {
		parent::setUp(); 
		$this->hasOne('Organisation as organisation',
							array('local' => 'organisationid',
									'foreign' => 'id'
							)
						);	
		$this->hasOne('Member as member',
							array('local' => 'memberid',
									'foreign' => 'id'
							)
						);	
	}
	
   /**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		
		// set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"organisationid.notblank" => $this->translate->_("organisation_team_id_error")
       									"role.notblank" => $this->translate->_("organisation_team_role_error")
       	       						));
	}
}