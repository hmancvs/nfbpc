<?php

/**
 * Access permissions for a group on a resource
 */
class AclPermission extends BaseEntity   {
	
	public function setTableDefinition() {
		parent::setTableDefinition();
        $this->setTableName('aclpermission');
        $this->hasColumn('groupid', 'integer', null, array('notblank' => true));
        $this->hasColumn('resourceid', 'integer', null, array('notblank' => true));
        $this->hasColumn('create', 'integer', null, array('default' => 0));
        $this->hasColumn('edit', 'integer', null, array('default' => 0));
        $this->hasColumn('approve', 'integer', null, array('default' => 0));
        $this->hasColumn('view', 'integer', null, array('default' => 0));
		$this->hasColumn('list','integer', null, array('default' => 0));
        $this->hasColumn('delete', 'integer', null, array('default' => 0));
        $this->hasColumn('export', 'integer', null, array('default' => 0));
    }
	/**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		
		// set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"groupid.notblank" => $this->translate->_("permission_groupid_error"),
       									"resourceid.notblank" => $this->translate->_("permission_resourceid_error")
       								)); 
		
	}
	
    function setUp() {
    	parent::setUp();
    	// foreign key for the group
    	$this->hasOne('AclGroup as group', array(
							'local' => 'groupid',
							'foreign' => 'id')
					);
		$this->hasOne('AclResource as resource', array(
							'local' => 'resourceid',
							'foreign' => 'id')
					);
    }
/*
	 * Pre process model data
	 */
	function processPost($formvalues) {
		$session = SessionWrapper::getInstance(); 
		// trim spaces from the name field
		if(isArrayKeyAnEmptyString('create', $formvalues)){
			$formvalues['create']=0; 
		}
		if(isArrayKeyAnEmptyString('edit', $formvalues)){
			$formvalues['edit']=0; 
		}
		if(isArrayKeyAnEmptyString('view', $formvalues)){
			$formvalues['view']=0;  
		}
		if(isArrayKeyAnEmptyString('list', $formvalues)){
			$formvalues['list']=0;  
		}
		if(isArrayKeyAnEmptyString('delete', $formvalues)){
			$formvalues['delete']=0; 
		}
		if(isArrayKeyAnEmptyString('export', $formvalues)){
			$formvalues['export']=0; 
		}
		if(isArrayKeyAnEmptyString('approve', $formvalues)){
			$formvalues['approve']=0;  
		}
		// debugMessage($formvalues); exit();
		parent::processPost($formvalues);
	}
    /**
     * Return the permission for the specified action
     *
     * @param String $action The action for which the permission is required
     * 
     * @return 1 if the action can be executed on the resource, and 0 if the action cannot be executed on the resource
     */
    function checkPermissionForAction($action) {
    	return $this->_get($action); 
    }
	/**
	 * Return the checked status for a checkbox signifying whether an action is allowed or denied on a resource.
	 *
	 * @param String $action The action to be executed on the resource
	 * 
	 * @return String the checked attribute value for the checkbox
	 */
	function getCheckedStatus($action) {
		return getCheckedAttribute($this->checkPermissionForAction($action));
	}
}
?>