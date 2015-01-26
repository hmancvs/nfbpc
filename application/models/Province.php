<?php

class Province extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('province');
		$this->hasColumn('name', 'string', 50, array("notblank" => true));
		$this->hasColumn('code', 'string', 50);
		$this->hasColumn('pcode', 'string', 50);
		$this->hasColumn('description', 'string', 1000);
		$this->hasColumn('regionid', 'integer', null, array('notblank' => true));
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
		
		$this->hasOne('Region as region',
				array(
						'local' => 'regionid',
						'foreign' => 'id'
				)
		);
		
	}
	/**
	 * Preprocess model data
	 */
	function processPost($formvalues){
		$session = SessionWrapper::getInstance();
		if(isArrayKeyAnEmptyString('regionid', $formvalues)){
			unset($formvalues['regionid']);
		}
		
		// debugMessage($formvalues); // exit();
		parent::processPost($formvalues);
	}
	# determine number of members
	function getNumberOfMembers(){
		$conn = Doctrine_Manager::connection();
		$custom_query = "";
		$custom_query = " AND m.provinceid = '".$this->getID()."' ";
		$unique_query = "SELECT count(m.id) FROM member m WHERE m.id <> '' ".$custom_query." ";
		$result = $conn->fetchOne($unique_query);
		// debugMessage($unique_query);
		// debugMessage($result);
		return $result;
	}
	# determine number of churches
	function getNumberOfChurches(){
		$conn = Doctrine_Manager::connection();
		$custom_query = "";
		$custom_query = " AND o.provinceid = '".$this->getID()."' ";
		$unique_query = "SELECT count(o.id) FROM organisation o WHERE o.id <> '' ".$custom_query." ";
		$result = $conn->fetchOne($unique_query);
		// debugMessage($unique_query);
		// debugMessage($result);
		return $result;
	}
	function getProvinceID(){
		return $this->getID();
	}
	function getPopulation(){
		return '';
	}
	function getDistrictID(){
		return '';
	}
	function afterSave(){
		$session = SessionWrapper::getInstance();
		# add log to audit trail
		$view = new Zend_View();
		$url = $view->serverUrl($view->baseUrl('location/view/id/'.encode($this->getID()).'type/7'));
		$usecase = '4.1';
		$module = '4';
		$type = LOCATION_CREATE;
		$details = "Province <a href='".$url."' class='blockanchor'>".$this->getName()."</a> created";
			
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
		$province = new Province();
		$province->populate($this->getID());
		$this->setPreUpdateData($province->toArray()); // debugMessage($this->toAray());
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
		$url = $view->serverUrl($view->baseUrl('location/view/id/'.encode($this->getID()).'type/7'));
		$usecase = '4.2';
		$module = '4';
		$type = LOCATION_UPDATE;
		$details = "Province <a href='".$url."' class='blockanchor'>".$this->getName()."</a> updated.";
			
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
}
?>
