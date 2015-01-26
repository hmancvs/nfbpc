<?php

class Committee extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('committee');
		$this->hasColumn('code', 'string', 10);
		$this->hasColumn('name', 'string', 255, array('notblank' => true));
		$this->hasColumn('description', 'string', 1000);
		$this->hasColumn('abbr', 'string', 50);
		$this->hasColumn('isfeatured', 'integer', null);
		$this->hasColumn('sortorder', 'integer', null);
		$this->hasColumn('defaultemail', 'string', 255);
	}
	
	# Contructor method for custom initialization
	public function construct() {
		parent::construct();
		
		# set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"name.notblank" => $this->translate->_("global_name_error")
       	       						));
	}
	
	# Model relationships
	public function setUp() {
		parent::setUp(); 
		
	}
	/**
	 * Preprocess model data
	 */
	function processPost($formvalues){
		$session = SessionWrapper::getInstance();
		if(isArrayKeyAnEmptyString('isfeatured', $formvalues)){
			unset($formvalues['isfeatured']);
		}
		if(!isArrayKeyAnEmptyString('name', $formvalues)){
			$formvalues['name'] = ucfirst($formvalues['name']);
		}
		if(isArrayKeyAnEmptyString('sortorder', $formvalues)){
			unset($formvalues['sortorder']);
		}
		// debugMessage($formvalues); // exit();
		parent::processPost($formvalues);
	}
	# determine appointments for a member
	function getAppointments($locationid = ''){
		$custom_query = '';
		if(!isEmptyString($locationid)){
			$custom_query = " AND a.locationid = '".$locationid."' ";
		}
		$query = Doctrine_Query::create()->from('Appointment a')->innerjoin('a.member m')
		->where("a.committeeid = '".$this->getID()."' ".$custom_query." ")->orderby("if(m.displayname <> '',m.displayname, concat(m.firstname, ' ', m.lastname)) asc");
		// debugMessage($query->getSQLQuery());
		$result = $query->execute();
		if($result){
			return $result;
		}
		return new Appointment();
	}
	function afterSave(){
		$session = SessionWrapper::getInstance();
		# add log to audit trail
		$view = new Zend_View();
		$url = $view->serverUrl($view->baseUrl('committee/view/id/'.encode($this->getID())));
		$usecase = '5.1';
		$module = '5';
		$type = COMMITTEE_CREATE;
		$details = "Committee <a href='".$url."' class='blockanchor'>".$this->getName()."</a> created";
			
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
	# update after
	function beforeUpdate(){
		$session = SessionWrapper::getInstance();
		# set object data to class variable before update
		$committee = new Committee();
		$committee->populate($this->getID());
		$this->setPreUpdateData($committee->toArray()); // debugMessage($this->toAray());
		// exit;
		return true;
	}
	
	function afterUpdate(){
		$session = SessionWrapper::getInstance();
		
		# set postupdate from class object, and then save to audit
		$prejson = json_encode($this->getPreUpdateData()); // debugMessage($prejson); exit;
			
		$this->clearRelated(); // clear any current object relations
		$after = $this->toArray(); // debugMessage($after);
		$postjson = json_encode($after); // debugMessage($postjson);
			
		$diff = array_diff($this->getPreUpdateData(), $after);  // debugMessage($diff);
		$jsondiff = json_encode($diff); // debugMessage($jsondiff);
		
		$view = new Zend_View();
		$url = $view->serverUrl($view->baseUrl('committee/view/id/'.encode($this->getID())));
		$usecase = '5.1';
		$module = '5';
		$type = COMMITTEE_UPDATE;
		$details = "Committee <a href='".$url."' class='blockanchor'>".$this->getName()."</a> updated.";
		
		$browser = new Browser();
		$audit_values = $session->getVar('browseraudit');
		$audit_values['module'] = $module;
		$audit_values['usecase'] = $usecase;
		$audit_values['transactiontype'] = $type;
		$audit_values['status'] = "Y";
		$audit_values['userid'] = $session->getVar('userid');
		$audit_values['transactiondetails'] = $details;
		$audit_values['isupdate'] = 1;
		$audit_values['prejson'] = $prejson;
		$audit_values['postjson'] = $postjson;
		$audit_values['jsondiff'] = $jsondiff;
		$audit_values['url'] = $url;
		// debugMessage($audit_values);
		$this->notify(new sfEvent($this, $type, $audit_values));
	
		return true;
	}
	# find user by phone
	function populateByAbbr($abbr) {
		$query = Doctrine_Query::create()->from('Committee c')->where("upper(c.abbr) = upper('".$abbr."')");
		// debugMessage($query->getSQLQuery());
		$result = $query->execute();
		return $result->get(0);
	}
}
?>
