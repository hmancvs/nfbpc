<?php
class RoleController extends SecureController   {
	
	/**
	 * Override unknown actions to enable ACL checking 
	 * 
	 * @see SecureController::getActionforACL()
	 *
	 * @return String
	 */
	public function getActionforACL() {
        $action = strtolower($this->getRequest()->getActionName()); 
		if($action == "processroles") {
			return ACTION_CREATE; 
		}
		return parent::getActionforACL(); 
	}
    
	public function processrolesAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$post_array = $this->_getAllParams();
		$id = $post_array['id'];
		$post_array['id'] = decode($id);
		// debugMessage($post_array);
		
		$role = new AclGroup();
		$role->populate(decode($id));
		$permissions = $role->getPermissions();
		$permissions_array = $permissions->toArray();
		
		if(!isArrayKeyAnEmptyString('permissions', $post_array)){
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
					$post_array['permissions'][$key]['id'] = NULL;
					$post_array['permissions'][$key]['createdby'] = $session->getVar('userid');
					$post_array['permissions'][$key]['datecreated'] = getCurrentMysqlTimestamp();
				}
				if(!isArrayKeyAnEmptyString('id', $value)){
					$post_array['permissions'][$key]['lastupdatedby'] = $session->getVar('userid');
					$post_array['permissions'][$key]['lastupdatedate'] = getCurrentMysqlTimestamp();
				}
			} // end loop through permissions to unset empty groupids 
		}
		
		$perm_collection = new Doctrine_Collection(Doctrine_Core::getTable("AclPermission"));
		foreach($post_array['permissions'] as $key => $value) {
			$perm = new AclPermission();
			if(!isArrayKeyAnEmptyString('id', $value)){
				$perm->populate($value['id']);
			}
			
			$perm->processPost($value);
			if($perm->isValid()) {
				$perm_collection->add($perm);
			}
		}
		// debugMessage($perm_collection->toArray()); // exit();
		if($perm_collection->count() > 0){
			try {
				$perm_collection->save();
				
				# clear cache after updating options
				$temppath = APPLICATION_PATH.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR; // debugMessage($temppath);
				$files = glob($temppath.'zend_cache---*');
				foreach($files as $file){
					debugMessage($file);
					if(is_file($file)){
						unlink($file);
				  	}
				}
				
				$session->setVar(SUCCESS_MESSAGE, "Successfully saved.");
				$this->_helper->redirector->gotoUrl($this->view->baseUrl("role/view/id/".encode($role->getID())));
			} catch (Exception $e) {
				/*debugMessage($perm_collection->toArray()); 
				debugMessage('error in save. '.$e->getMessage());*/
				$session->setVar(ERROR_MESSAGE, $e->getMessage());
				$this->_helper->redirector->gotoUrl($this->view->baseUrl("role/index/id/".encode($role->getID())));
			}
		}
	}
}
