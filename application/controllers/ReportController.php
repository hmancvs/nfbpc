<?php
class ReportController extends SecureController   {
	
	/**
	 * Get the name of the resource being accessed 
	 *
	 * @return String 
	 */
	function getActionforACL() {
		return ACTION_VIEW;
	}
	/**
	 * Get the name of the resource being accessed 
	 *
	 * @return String 
	 */
	function getResourceForACL() {
		$action = strtolower($this->getRequest()->getActionName()); 
		
		if ($action == "dashboard" || $action == "reportsearch") {
			return "Report Dashboard";
		}
		if ($action == "processbillablehours") {
			return "Billable Hours Report";
		}
		if ($action == "processactivitylog") {
			return "Activity Report";
		}
		return parent::getResourceForACL(); 
	}
	function dashboardAction() {
    	
    }
	/**
     * Redirect list searches to maintain the urls as per zend format 
     */
    public function reportsearchAction() {
    	// debugMessage($this->getRequest()->getQuery());
    	// debugMessage($this->_getAllParams());
    	$action = $this->_getParam('page');
    	// exit();
    	if(!isEmptyString($action)){
    		$this->_helper->redirector->gotoSimple($action, $this->getRequest()->getControllerName(), 
    											$this->getRequest()->getModuleName(),
    											array_remove_empty(array_merge_maintain_keys($this->_getAllParams(), $this->getRequest()->getQuery())));
    	}
    }
    
    function processbillablehoursAction(){
    	$session = SessionWrapper::getInstance();
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	
    	$id = $this->_getParam('id');
    	if(!isEmptyString($id)){
    		# if id present, change action to edit
    		$this->_setParam('action', ACTION_EDIT);
    	} else {
    		$this->_setParam('createdby', $session->getVar('userid'));
    	}
    	$formvalues = $this->_getAllParams();
    	$formvalues['title'] = "Billable Hours Report "; // ".formatDateAndTime($formvalues['startdate'], false).' - '.formatDateAndTime($formvalues['enddate'], false); 
    	$formvalues['completedby'] = $session->getVar('userid');
    	$formvalues['reportdate'] = DEFAULT_DATETIME;
    	// debugMessage($formvalues);
    	
    	$actreport = new ActivityReport();
    	if(!isEmptyString($id)){
    		$actreport->populate($id); // debugMessage($actreport->toArray());
    	} else {
    		if(!isEmptyString($formvalues['voucherid'])){
    			$voucher = new Voucher();
    			$voucher->populate($formvalues['voucherid']);
    			$formvalues['clientid'] = $voucher->getClientID();
    		}
    	}
    	 
    	# process the report
    	$actreport->processPost($formvalues); // debugMessage($actreport->toArray()); debugMessage('error is '.$actreport->getErrorStackAsString()); exit();
    	 
    	# check for processing errors
    	if($actreport->hasError()){
	    	$session->setVar(ERROR_MESSAGE, $actreport->getErrorStackAsString());
	    	$session->setVar(FORM_VALUES, $formvalues);
	    	$this->_helper->redirector->gotoUrl(decode($this->_getParam('failureurl')));
    	}
    	
    	try {
    		// save the invoice and return to success page (client view)
    		$actreport->save(); // debugMessage('ssaved perfect');
    		if(!isEmptyString($actreport->getInvoiceID())){
    			if($actreport->getInvoice()->getHoursTaken() != $actreport->getBillableHours() ){
	    			$actreport->getInvoice()->setHoursTaken($actreport->getBillableHours());
	    			$actreport->getInvoice()->setInvoiceAmount($actreport->getBillableHours() * $actreport->getVoucher()->getRate());
	    			$actreport->getInvoice()->save();
    			}
    		}
    		if(isEmptyString($id)){
    			if($formvalues['reportaction'] == 'savetheninvoice'){
    				$session->setVar(SUCCESS_MESSAGE, "Report successfully saved. Proceed to generate invoice");
    				$this->_helper->redirector->gotoUrl(decode($this->_getParam('invoiceurl')).'/rid/'.$actreport->getID());
    			} else {
    				$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate('global_save_success'));
    				$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)).'/rid/'.$actreport->getID());
    			}
    		} else {
    			if($formvalues['reportaction'] == 'savetheninvoice'){
    				$session->setVar(SUCCESS_MESSAGE, "Report successfully updated. Proceed to generate invoice");
    				$this->_helper->redirector->gotoUrl(decode($this->_getParam('invoiceurl')).'/rid/'.$id);
    			} else {
    				$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate('global_update_success'));
    				$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)).'/rid/'.$id);
    			}
    		}
    	} catch (Exception $e) {
    		// debugMessage($e->getMessage());
    		// failed to save invoice, return to failure page (client view)
    		$session->setVar(ERROR_MESSAGE, $e->getMessage());
    		$session->setVar(FORM_VALUES, $formvalues);
    		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
    	}
    }
    
    function processactivitylogAction(){
    	$session = SessionWrapper::getInstance();
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    	
    	$id = $this->_getParam('id');
    	if(!isEmptyString($id)){
    		# if id present, change action to edit
    		$this->_setParam('action', ACTION_EDIT);
    		$this->_setParam('id', decode($id));
    	} else {
    		$this->_setParam('createdby', $session->getVar('userid'));
    	}
    	$formvalues = $this->_getAllParams();
    	$formvalues['completedby'] = $session->getVar('userid');
    	$formvalues['reportdate'] = DEFAULT_DATETIME;
    	if(isArrayKeyAnEmptyString('type', $formvalues)){
    		$formvalues['type'] = 2;
    	}
    	debugMessage($formvalues);
    	 
    	$actreport = new ActivityReport();
    	if(!isEmptyString($id)){
    		$actreport->populate(decode($id)); // debugMessage($actreport->toArray());
    	} else {
    		if(!isArrayKeyAnEmptyString('voucherid', $formvalues) && isArrayKeyAnEmptyString('clientid', $formvalues)){
    			$voucher = new Voucher();
    			$voucher->populate($formvalues['voucherid']);
    			$formvalues['clientid'] = $voucher->getClientID();
    		}
    	}
    	# process the report
    	$actreport->processPost($formvalues);
    	debugMessage($actreport->toArray()); 
    	debugMessage('error is '.$actreport->getErrorStackAsString()); 
    	// exit();
    	
    	# check for processing errors
    	if($actreport->hasError()){
	    	$session->setVar(ERROR_MESSAGE, $actreport->getErrorStackAsString());
	    	$session->setVar(FORM_VALUES, $formvalues);
	    	$this->_helper->redirector->gotoUrl(decode($this->_getParam('failureurl')));
    	}
    	
    	try {
    		$actreport->save();
    		if(isEmptyString($id)){
    			$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate('global_save_success'));				
    		} else {
    			$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate('global_update_success'));
    		}
    		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)).'/rid/'.encode($actreport->getID()));
    		
    	} catch (Exception $e) {
    		// debugMessage($e->getMessage());
    		$session->setVar(ERROR_MESSAGE, $e->getMessage());
    		$session->setVar(FORM_VALUES, $formvalues);
    		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
    	}
    }
}

