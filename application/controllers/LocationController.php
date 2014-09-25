<?php

class LocationController extends SecureController {
/**
	 * @see SecureController::getResourceForACL()
	 *
	 * @return String
	 */
	function getResourceForACL() {
		return "Location";
	}
	/**
	 * Override unknown actions to enable ACL checking 
	 * 
	 * @see SecureController::getActionforACL()
	 *
	 * @return String
	 */
	public function getActionforACL() {
        $action = strtolower($this->getRequest()->getActionName()); 
		if($action == "processgps"){
			return ACTION_EDIT; 
		}
		return parent::getActionforACL(); 
    }
    
	public function processgpsAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$session = SessionWrapper::getInstance(); 	
	    $config = Zend_Registry::get("config");
	    $this->_translate = Zend_Registry::get("translate");
	    $formvalues = $this->_getAllParams();
	    $formvalues['id'] =  decode($formvalues['id']);
	    
	    // debugMessage($formvalues);
	    $location = new Location();
	    $location->populate($formvalues['id']);
	    
	    $location->setGpsLat($formvalues['lat']);
	    $location->setGpsLng($formvalues['lng']);
	    // debugMessage($location->toArray());
	    
	    // exit();
	    try {
	    	$location->save();
	    	$session->setVar(SUCCESS_MESSAGE, "Successfully saved");
	    	$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
	    } catch (Exception $e) {
	    	$session->setVar(ERROR_MESSAGE, $e->getMessage()); 
	    	$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
	    }
	}
}