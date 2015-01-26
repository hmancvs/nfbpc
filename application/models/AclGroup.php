<?php
/**
 * A collection of a set of permissions resources
 * 
 */
class AclGroup extends BaseEntity implements Zend_Acl_Role_Interface {
	
	public function setTableDefinition() {
		parent::setTableDefinition();
		$this->setTableName('aclgroup');
		$this->hasColumn('name', 'string', 50, array('unique' => true, 'notnull' => true, 'notblank' => true, 'unique' => true));
		$this->hasColumn('description', 'string', 255, array('notnull' => true, 'notblank' => true));
	}
	/**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		
		// set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"name.unique" => "The Group&nbsp;<span id='uniquegroupname'></span>&nbsp;already exists.",
       									"name.notblank" => "Please enter the Group Name",
       									"description.notblank" => "Please enter the Description"
       								)); 
	}
	/**
	 * Setup the permissions for the ACLGroup class
	 */
	public function setUp() {
		parent::setUp();
		// the permissions for the group
		$this->hasMany('AclPermission as permissions', 
						array('local' => 'id', 
								'foreign' => 'groupid',
						));
		$this->hasMany('UserGroup as usergroups',
							array('local' => 'id',
									'foreign' => 'groupid',
                                    )
						);
	}
	
	function validate() {
		# check that the permissions are specfied
		/* if (($this->getPermissions()->count() == 0)) {
			$this->getErrorStack()->add('permissions', 'Please select at least one set of permissions for the group');
		} */
	}
	
	/**
	 * @see Zend_Acl_Role_Interface::getRoleId()
	 *
	 * @return string The ID of the role
	 */
	public function getRoleId() {
		return $this->getID();
	}
	
	/**
	 * Get the AclPermission instance for the specified resource. 
	 * If the permission does not exist, then a new empty ACLPermission instance is returned
	 *
	 * @param Integer $resourceid The ID of the resource
	 * @return AclPermission instance for the resource
	 */
	function getPermissionForResource($resourceid) {
		$thepermissions = $this->getPermissions();
		foreach ($thepermissions as $aperm ) {
			// debugMessage($resourceid.' - '.$aperm->getResourceID());
			// check if we are dealing with the specified resource
			if ($aperm->getResourceID() == $resourceid) {
				return $aperm;
				// debugMessage($aperm->toArray());
			}
		}
		return new AclPermission(); 
	}
	/**
	 * Clean out the data received from the screen by:
	 * - remove empty/blank groupid  - the groupid is not required and therefore is an empty value is maintained will cause an out of range exception 
	 *
	 * @param Array $post_array
	 */
	function processPost($post_array) {
		$session = SessionWrapper::getInstance(); 
		// check if the groupid is blank then remove it
		$permissions = $this->getPermissions();
		$permissions_array = $permissions->toArray();
		if (array_key_exists('permissions', $post_array)) {
			if (is_array($post_array['permissions'])) {
				$data = array();
				foreach($post_array['permissions'] as $key => $value) {
					$data[$key] = $value;
					if(array_key_exists('groupid', $value)) {
						if(isEmptyString($value['groupid'])) {
							unset($post_array['permissions'][$key]['groupid']); 
						}
					}
					if(isArrayKeyAnEmptyString('create', $value)){
						$post_array['permissions'][$key]['create'] = 0;
					} else {
						$post_array['permissions'][$key]['create'] = trim(intval($value['create']));
					}
					if(isArrayKeyAnEmptyString('edit', $value)){
						$post_array['permissions'][$key]['edit'] = 0;
					} else {
						$post_array['permissions'][$key]['edit'] = trim(intval($value['edit']));
					}
					if(isArrayKeyAnEmptyString('view', $value)){
						$post_array['permissions'][$key]['view'] = 0;
					} else {
						$post_array['permissions'][$key]['view'] = trim(intval($value['view']));
					}
					if(isArrayKeyAnEmptyString('list', $value)){
						$post_array['permissions'][$key]['list'] = 0;
					} else {
						$post_array['permissions'][$key]['list'] = trim(intval($value['list']));
					}
					if(isArrayKeyAnEmptyString('delete', $value)){
						$post_array['permissions'][$key]['delete'] = 0;
					} else {
						$post_array['permissions'][$key]['delete'] = trim(intval($value['delete']));
					}
					if(isArrayKeyAnEmptyString('approve', $value)){
						$post_array['permissions'][$key]['approve'] = 0;
					} else {
						$post_array['permissions'][$key]['approve'] = trim(intval($value['approve']));
					}
					if(isArrayKeyAnEmptyString('export', $value)){
						$post_array['permissions'][$key]['export'] = 0;
					} else {
						$post_array['permissions'][$key]['export'] = trim(intval($value['export']));
					}
					if(isArrayKeyAnEmptyString('id', $value)){
						unset($post_array['permissions'][$key]['id']);
						$post_array['permissions'][$key]['createdby'] = $session->getVar('userid');
						$post_array['permissions'][$key]['datecreated'] = getCurrentMysqlTimestamp();
					}
					if(!isArrayKeyAnEmptyString('id', $value)){
						$post_array['permissions'][$key]['lastupdatedby'] = $session->getVar('userid');
						$post_array['permissions'][$key]['lastupdatedate'] = getCurrentMysqlTimestamp();
						$data = $post_array['permissions'][$key];
						unset($post_array['permissions'][$key]);
						$newkey = array_search_key_by_id($permissions_array, $value['id']);
						// debugMessage($data);
						$post_array['permissions'][$newkey] = $data;
					}
				} // end loop through permissions to unset empty groupids 
			} 
		}
		// now process the data
		// debugMessage($post_array['permissions']); // exit();
		parent::processPost($post_array); 
	}
	
	function afterSave(){
		$session = SessionWrapper::getInstance();
		# add log to audit trail
		$view = new Zend_View();
		$url = $view->serverUrl($view->baseUrl('role/view/id/'.encode($this->getID())));
		$usecase = '0.4';
		$module = '0';
		$type = SYSTEM_CREATEROLE;
		$details = "Role <a href='".$url."' class='blockanchor'>".$this->getName()."</a> created";
			
		$browser = new Browser();
		$audit_values = $session->getVar('browseraudit');
		$audit_values['module'] = $module;
		$audit_values['usecase'] = $usecase;
		$audit_values['transactiontype'] = $type;
		$audit_values['status'] = "Y";
		$audit_values['userid'] = $session->getVar('userid');
		$audit_values['transactiondetails'] = $details;
		$audit_values['url'] = $url;
		// debugMessage($audit_values);
		$this->notify(new sfEvent($this, $type, $audit_values));
	}
}
?>