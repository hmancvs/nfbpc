<?php

class CommitteeController extends SecureController {
	
	/**
	 * Override unknown actions to enable ACL checking 
	 * 
	 * @see SecureController::getActionforACL()
	 *
	 * @return String
	 */
	public function getActionforACL() {
	 	$action = strtolower($this->getRequest()->getActionName());
	 	if($action = "preview"){
	 		return ACTION_VIEW;
	 	}
		return parent::getActionforACL();
    }
	
	function previewAction(){
		
	}
}