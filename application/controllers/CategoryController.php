<?php

class CategoryController extends SecureController  {
	/**
	 * Override unknown actions to enable ACL checking 
	 * 
	 * @see SecureController::getActionforACL()
	 *
	 * @return String
	 */
	public function getActionforACL() {
        $action = strtolower($this->getRequest()->getActionName()); 
		
		return parent::getActionforACL(); 
    }
    
	public function getResourceForACL(){
        return "Category"; 
    }
       
}
