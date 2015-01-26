<?php

class Member extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('member');
		$this->hasColumn('type', 'integer', null);
		$this->hasColumn('firstname', 'string', 255, array('notblank' => true));
		$this->hasColumn('lastname', 'string', 255, array('notblank' => true));
		$this->hasColumn('othername', 'string', 255);
		$this->hasColumn('displayname', 'string', 255);
		$this->hasColumn('nickname', 'string', 255);
		$this->hasColumn('salutation', 'string', 6);
		
		$this->hasColumn('country', 'string', 2, array('default' => 'UG'));
		$this->hasColumn('nationality', 'string', 255);
		$this->hasColumn('regionid', 'integer', null, array('default' => NULL));
		$this->hasColumn('provinceid', 'integer', null, array('default' => NULL));
		$this->hasColumn('districtid', 'integer', null, array('default' => NULL));
		// $this->hasColumn('memberdistrictid', 'integer', null, array('default' => NULL));
		$this->hasColumn('countyid', 'integer', null, array('default' => NULL));
		$this->hasColumn('subcountyid', 'integer', null, array('default' => NULL));
		$this->hasColumn('parishid', 'integer', null, array('default' => NULL));
		$this->hasColumn('villageid', 'integer', null, array('default' => NULL));
		$this->hasColumn('address1', 'string', 255);
		$this->hasColumn('address2', 'string', 255);
		$this->hasColumn('postalcode', 'string', 255);
		$this->hasColumn('gpslat', 'string', 15);
		$this->hasColumn('gpslng', 'string', 15);
		
		$this->hasColumn('email', 'string', 50); // only required during activation
		$this->hasColumn('email2', 'string', 50);
		$this->hasColumn('phone', 'string', 15);
		$this->hasColumn('phone2', 'string', 15);
		$this->hasColumn('phone_isactivated', 'integer', null, array('default' => '0'));
		$this->hasColumn('phone_actkey', 'string', 15);
		$this->hasColumn('username', 'string', 15); // only required during activation
		$this->hasColumn('password', 'string', 50); // only required during activation
		$this->hasColumn('trx', 'string', 50);
		$this->hasColumn('status', 'integer', null, array('default' => '0')); # 0=Pending, 1=Active, 2=Deactivated
		$this->hasColumn('activationkey', 'string', 15);
		$this->hasColumn('activationdate', 'date');
		$this->hasColumn('agreedtoterms', 'integer', null, array('default' => '0'));	# 0=NO, 1=YES
		$this->hasColumn('securityquestion', 'integer', null);
		$this->hasColumn('securityanswer', 'integer', null);
		$this->hasColumn('isinvited', 'integer', null, array('default' => NULL));
		$this->hasColumn('invitedbyid', 'integer', null);
		$this->hasColumn('hasacceptedinvite', 'integer', null, array('default' => 0));
		$this->hasColumn('dateinvited','date');
		
		$this->hasColumn('organisationid', 'integer', null);
		$this->hasColumn('bio', 'string', 65535);
		$this->hasColumn('gender', 'integer', null); # 1=Male, 2=Female, 3=Unknown
		$this->hasColumn('dateofbirth','date');
		$this->hasColumn('profilephoto', 'string', 50);
		$this->hasColumn('maritalstatus', 'integer', null, array('default' => NULL));
		$this->hasColumn('website', 'string', 50);
		$this->hasColumn('contactid', 'integer', null);
		$this->hasColumn('contactname', 'string', 255);
		$this->hasColumn('contactphone', 'string', 15);
		$this->hasColumn('contactrshp', 'string', 15);
		$this->hasColumn('noofchildren', 'integer', null, array('default' => NULL));
		$this->hasColumn('profession', 'string', 50);
		$this->hasColumn('specialisation', 'string', 50);
		
		# override the not null and not blank properties for the createdby column in the BaseEntity
		$this->hasColumn('createdby', 'integer', 11);
	}
	
	protected $oldpassword;
	protected $newpassword;
	protected $confirmpassword;
	protected $trx;
	protected $oldemail;
	protected $changeemail;
	protected $isinvited;
	protected $isphoneinvited;
	protected $preupdatedata;
	protected $controller;
	
	function getOldPassword(){
		return $this->oldpassword;
	}
	function setOldPassword($oldpassword) {
		$this->oldpassword = $oldpassword;
	}
	function getNewPassword(){
		return $this->newpassword;
	}
	function setNewPassword($newpassword) {
		$this->newpassword = $newpassword;
	}
	function getConfirmPassword(){
		return $this->confirmpassword;
	}
	function setConfirmPassword($confirmpassword) {
		$this->confirmpassword = $confirmpassword;
	}
	function getTrx(){
		return $this->trx;
	}
	function setTrx($trx) {
		$this->trx = $trx;
	}
	function getOldEmail(){
		return $this->oldemail;
	}
	function setOldEmail($oldemail) {
		$this->oldemail = $oldemail;
	}
	function getChangeEmail(){
		return $this->changeemail;
	}
	function setChangeEmail($changeemail) {
		$this->changeemail = $changeemail;
	}
	function getIsBeinginvited(){
		return $this->isbeinginvited;
	}
	function setIsBeingInvited($isbeinginvited) {
		$this->isbeinginvited = $isbeinginvited;
	}
	function getNameofmember(){
		return $this->nameofmember;
	}
	function setNameofmember($nameofmember) {
		$this->nameofmember = $nameofmember;
	}
	function getPreUpdateData(){
		return $this->preupdatedata;
	}
	function setPreUpdateData($preupdatedata) {
		$this->preupdatedata = $preupdatedata;
	}
	function getController(){
		return $this->controller;
	}
	function setController($controller) {
		$this->controller = $controller;
	}
	
	# Contructor method for custom initialization
	public function construct() {
		parent::construct();
		
		$this->addDateFields(array("dateofbirth","activationdate","dateinvited"));
		
		# set the custom error messages
       	$this->addCustomErrorMessages(array(
       									// "type.notblank" => $this->translate->_("profile_type_error"),
       									"firstname.notblank" => $this->translate->_("profile_firstname_error"),
       									"lastname.notblank" => $this->translate->_("profile_lastname_error")
       	       						));
	}
	
	# Model relationships
	public function setUp() {
		parent::setUp(); 
		# copied directly from BaseEntity since the createdby can be NULL when a user signs up 
		# automatically set timestamp column values for datecreated and lastupdatedate 
		$this->actAs('Timestampable', 
						array('created' => array(
												'name' => 'datecreated'
											),
							 'updated' => array(
												'name'     =>  'lastupdatedate',    
												'onInsert' => false,
												'options'  =>  array('notnull' => false)
											)
						)
					);
		$this->hasMany('UserGroup as usergroups',
							array('local' => 'id',
									'foreign' => 'userid'
							)
						);
		$this->hasOne('Member as creator', 
								array(
									'local' => 'createdby',
									'foreign' => 'id'
								)
						);
		$this->hasOne('Member as invitedby', 
								array(
									'local' => 'invitedbyid',
									'foreign' => 'id',
								)
						);
		$this->hasOne('Member as contact',
								array(
										'local' => 'contactid',
										'foreign' => 'id',
								)
						);
		$this->hasOne('Organisation as organisation',
								array(
										'local' => 'organisationid',
										'foreign' => 'id',
								)
						);
		$this->hasOne('Region as region',
								array(
										'local' => 'regionid',
										'foreign' => 'id'
								)
						);
		$this->hasOne('Province as province',
								array(
										'local' => 'provinceid',
										'foreign' => 'id'
								)
						);
		$this->hasOne('Location as district',
								array(
										'local' => 'districtid',
										'foreign' => 'id'
								)
						);
		$this->hasOne('Location as county',
								array(
										'local' => 'countyid',
										'foreign' => 'id'
								)
						);
		$this->hasOne('Location as subcounty',
								array(
										'local' => 'subcountyid',
										'foreign' => 'id'
								)
						);
		$this->hasOne('Location as parish',
								array(
										'local' => 'parishid',
										'foreign' => 'id'
								)
						);
		$this->hasOne('Location as village',
								array(
										'local' => 'villageid',
										'foreign' => 'id'
								)
						);
	}
	/**
	 * Custom model validation
	 */
	function validate() {
		# execute the column validation 
		parent::validate();
		// debugMessage($this->toArray(true));
		# validate that username is unique
		if($this->usernameExists()){
			$this->getErrorStack()->add("username.unique", sprintf($this->translate->_("profile_username_unique_error"), $this->getUsername()));	
		}
		# validate that email is unique
		if($this->emailExists()){
			$this->getErrorStack()->add("email.unique", sprintf($this->translate->_("profile_phone_unique_error"), $this->getEmail()));
		}
		
		/* if($this->phoneExists()){
			$this->getErrorStack()->add("phone.unique", sprintf($this->translate->_("profile_phone_unique_error"), $this->getPhone(), $this->getNameofmember()));
		} */
		# check that at least one group has been specified
		if ($this->getUserGroups()->count() == 0) {
			// $this->getErrorStack()->add("groups", $this->translate->_("profile_group_error"));
		}
		
		# validate attempt to change password with an invalid current password
		if(!isEmptyString($this->getNewPassword())){
			if(!isEmptyString($this->getOldPassword()) && sha1($this->getOldPassword()) != $this->getTrx()){
				$this->getErrorStack()->add("oldpassword", $this->translate->_("profile_oldpassword_invalid_error"));
			} else {
				$this->setPassword(sha1($this->getNewPassword()));
			}
		}
	}
	# determine if the username has already been assigned
	function usernameExists($username =''){
		$conn = Doctrine_Manager::connection();
		# validate unique username and email
		$id_check = "";
		if(!isEmptyString($this->getID())){
			$id_check = " AND id <> '".$this->getID()."' ";
		}
		
		if(isEmptyString($username)){
			$username = $this->getUsername();
		}
		$query = "SELECT id FROM member WHERE username = '".$username."' AND username <> '' ".$id_check;
		// debugMessage($query);
		$result = $conn->fetchOne($query);
		// debugMessage($result);
		if(isEmptyString($result)){
			return false;
		}
		return true;
	}
	# determine if the email has already been assigned
	function emailExists($email =''){
		$conn = Doctrine_Manager::connection();
		# validate unique username and email
		$id_check = "";
		if(!isEmptyString($this->getID())){
			$id_check = " AND id <> '".$this->getID()."' ";
		}
		
		if(isEmptyString($email)){
			$email = $this->getEmail();
		}
		$query = "SELECT id FROM member WHERE email = '".$email."' AND email <> '' ".$id_check;
		// debugMessage($ref_query);
		$result = $conn->fetchOne($query);
		// debugMessage($ref_result);
		if(isEmptyString($result)){
			return false;
		}
		return true;
	}
	# determine if the refno has already been assigned to another organisation
	function phoneExists($phone =''){
		$conn = Doctrine_Manager::connection();
		$id_check = "";
		if(!isEmptyString($this->getID())){
			$id_check = " AND id <> '".$this->getID()."' ";
		}
	
		$query_custom = '';
		if(isEmptyString($phone)){
			$phone = $this->getPhone();
		}
		
		# unique phone
		$phone_query = "SELECT id FROM member WHERE phone = '".$phone."' ".$id_check;
		// debugMessage($phone_query);
		$result = $conn->fetchOne($phone_query);
		// debugMessage($result);exit();
		if(isEmptyString($result)){
			return false;
		} else {
			$member = new Member();
			$member->populate($result);
			$this->setNameofmember($member->getfirstname().' '.$member->getlastname().' '.$member->getothername());
		}
		return true;
	}
	# check for user using password and phone number
	function validateUserUsingPhone($password, $phone){
		$formattedphone = getFullPhone($phone);
		$conn = Doctrine_Manager::connection();
		$query = "SELECT * from member as m where (m.phone = '".$formattedphone."' || m.phone = '".$phone."') AND (m.password = '".sha1($password)."' || m.trx = '".sha1($password)."') ";
		// debugMessage($query);
		$result = $conn->fetchRow($query);
		// debugMessage($result);
		return $result;
	}
	# check for user using password and phone number
	function validateExistingPhone($phone){
		$formattedphone = getFullPhone($phone);
		$conn = Doctrine_Manager::connection();
		$query = " ";
		// debugMessage($query);
		$result = $conn->fetchRow($query);
		// debugMessage($result);
		return $result;
	}
	/**
	 * Preprocess model data
	 */
	function processPost($formvalues){
		$session = SessionWrapper::getInstance(); //debugMessage($formvalues); 
		if(!isArrayKeyAnEmptyString('firstname', $formvalues)){
			$formvalues['firstname'] = ucwords(strtolower($formvalues['firstname']));
		}
		if(!isArrayKeyAnEmptyString('lastname', $formvalues)){
			$formvalues['lastname'] = ucwords(strtolower($formvalues['lastname']));
		}
		if(!isArrayKeyAnEmptyString('othername', $formvalues)){
			$formvalues['othername'] = ucwords(strtolower($formvalues['othername']));
		}
		if(!isArrayKeyAnEmptyString('displayname', $formvalues)){
			$formvalues['displayname'] = ucwords(strtolower($formvalues['displayname']));
		}
		# if the passwords are not changed , set value to null
		if(isArrayKeyAnEmptyString('password', $formvalues)){
			unset($formvalues['password']); 
		} else {
			$formvalues['password'] = sha1($formvalues['password']); 
		}
		if(!isArrayKeyAnEmptyString('oldpassword', $formvalues)){
			$this->setoldpassword($formvalues['oldpassword']);
		}
		if(!isArrayKeyAnEmptyString('confirmpassword', $formvalues)){
			$this->setconfirmpassword($formvalues['confirmpassword']);
		}
		if(!isArrayKeyAnEmptyString('trx', $formvalues)){
			$this->settrx($formvalues['trx']);
		}
		if(!isArrayKeyAnEmptyString('newpassword', $formvalues)){
			$this->setNewPassword($formvalues['newpassword']);
			$formvalues['password'] = sha1($formvalues['newpassword']); 
		}
		/*if(!isArrayKeyAnEmptyString('phone', $formvalues)){
			$formvalues['phone'] = str_pad(ltrim($formvalues['phone'], '0'), 12, getCountryCode(), STR_PAD_LEFT); 
		}*/
		if(!isArrayKeyAnEmptyString('email', $formvalues) && !isArrayKeyAnEmptyString('oldemail', $formvalues) && !isArrayKeyAnEmptyString('status', $formvalues)){
			if($formvalues['email'] != $formvalues['oldemail'] && $session->getVar('userid') == $formvalues['id']){
				$this->setChangeEmail('1');
				$this->setOldEmail($formvalues['oldemail']);
				$formvalues['email2'] = $formvalues['email'];
				$formvalues['email'] = $formvalues['oldemail'];
				$formvalues['activationkey'] = $this->generateActivationKey();
			}
		}
		# force setting of default none string column values. enum, int and date 	
		if(isArrayKeyAnEmptyString('status', $formvalues)){
			unset($formvalues['status']); 
		}
		if(isArrayKeyAnEmptyString('agreedtoterms', $formvalues)){
			unset($formvalues['agreedtoterms']); 
		}
		if(isArrayKeyAnEmptyString('gender', $formvalues)){
			unset($formvalues['gender']); 
		}
		if(isArrayKeyAnEmptyString('dateofbirth', $formvalues)){
			unset($formvalues['dateofbirth']);
		}
		if(isArrayKeyAnEmptyString('activationdate', $formvalues)){
			unset($formvalues['activationdate']); 
		}
		if(isArrayKeyAnEmptyString('type', $formvalues)){
			unset($formvalues['type']); 
		}
		if(isArrayKeyAnEmptyString('salutation', $formvalues)){
			unset($formvalues['salutation']);
		}
		if(isArrayKeyAnEmptyString('maritalstatus', $formvalues)){
			unset($formvalues['maritalstatus']);
		}
		if(isArrayKeyAnEmptyString('profession', $formvalues)){
			unset($formvalues['profession']);
		}
		if(isArrayKeyAnEmptyString('contactid', $formvalues)){
			unset($formvalues['contactid']);
		}
		if(isArrayKeyAnEmptyString('isinvited', $formvalues)){
			$formvalues['isinvited'] = NULL;
		}
		if(isArrayKeyAnEmptyString('hasacceptedinvite', $formvalues)){
			$formvalues['hasacceptedinvite'] = NULL; 
		}
		if(isArrayKeyAnEmptyString('dateinvited', $formvalues)){
			unset($formvalues['dateinvited']); 
		}
		if(!isArrayKeyAnEmptyString('isinvited', $formvalues)){
			if($formvalues['isinvited'] == 1){
				$this->setIsBeingInvited($formvalues['isinvited']);
				$formvalues['invitedbyid'] = $session->getVar('userid');
				$formvalues['dateinvited'] = date('Y-m-d');
				$formvalues['hasacceptedinvite'] = 0;
			}
		}
		if(isArrayKeyAnEmptyString('county', $formvalues)){
			if(isArrayKeyAnEmptyString('county_old', $formvalues)){
				unset($formvalues['county']);
			} else {
				$formvalues['county'] = NULL;
			}
		}
		
		# move the data from $formvalues['usergroups_groupid'] into $formvalues['usergroups'] array
		# the key for each group has to be the groupid
		if(isArrayKeyAnEmptyString('id', $formvalues)) {
			if(!isArrayKeyAnEmptyString('type', $formvalues)) {
				if(!isArrayKeyAnEmptyString('type', $formvalues)) {
					$formvalues['usergroups_groupid'] = array($formvalues['type']);
				}
				if(isArrayKeyAnEmptyString('createdby', $formvalues)) {
					$formvalues['createdby'] = DEFAULT_ID;
				}
				$formvalues['activationkey'] = $this->generateActivationKey();
			}
		}
		
		if (array_key_exists('usergroups_groupid', $formvalues)) {
			$groupids = $formvalues['usergroups_groupid']; 
			$usergroups = array(); 
			foreach ($groupids as $id) {
				$usergroups[]['groupid'] = $id; 
			}
			$formvalues['usergroups'] = $usergroups; 
			# remove the usergroups_groupid array, it will be ignored, but to be on the safe side
			unset($formvalues['usergroups_groupid']); 
		}
		
		# add the userid if the Member is being edited
		if (!isArrayKeyAnEmptyString('id', $formvalues)) {
			if (array_key_exists('usergroups', $formvalues)) {
				$usergroups = $formvalues['usergroups']; 
				foreach ($usergroups as $key=>$value) {
					$formvalues['usergroups'][$key]["userid"] = $formvalues["id"];
					if(!isArrayKeyAnEmptyString('type', $formvalues)){
						$formvalues['usergroups'][$key]["groupid"] = $formvalues['type'];
					}
				}
			} 
		}
		if(!isArrayKeyAnEmptyString('type', $formvalues) && !isArrayKeyAnEmptyString('type_old', $formvalues)) {
			if($formvalues['type'] && $formvalues['type_old']){
				$formvalues['usergroups'][0]["userid"] = $formvalues["id"];
				$formvalues['usergroups'][0]["groupid"] = $formvalues['type'];
			}
		}
		
		if(isArrayKeyAnEmptyString('organisationid', $formvalues)){
			unset($formvalues['organisationid']);
		}
		if(isArrayKeyAnEmptyString('regionid', $formvalues)){
			unset($formvalues['regionid']);
		}
		if(isArrayKeyAnEmptyString('provinceid', $formvalues)){
			unset($formvalues['provinceid']);
		}
		if(isArrayKeyAnEmptyString('districtid', $formvalues)){
			unset($formvalues['districtid']);
		}
		if(isArrayKeyAnEmptyString('countyid', $formvalues)){
			unset($formvalues['countyid']);
		}
		if(isArrayKeyAnEmptyString('subcountyid', $formvalues)){
			unset($formvalues['subcountyid']);
		}
		if(isArrayKeyAnEmptyString('parishid', $formvalues)){
			unset($formvalues['parishid']);
		}
		if(isArrayKeyAnEmptyString('villageid', $formvalues)){
			unset($formvalues['villageid']);
		}
		if(isArrayKeyAnEmptyString('contactid', $formvalues)){
			unset($formvalues['contactid']);
		}
		if(!isArrayKeyAnEmptyString('contactid', $formvalues) && isArrayKeyAnEmptyString('contactname', $formvalues)){
			$formvalues['contactname'] = '';
		}
		if(isArrayKeyAnEmptyString('contactname', $formvalues)){
			unset($formvalues['contactname']);
		}
		if(!isArrayKeyAnEmptyString('contactname', $formvalues) && isArrayKeyAnEmptyString('contactid', $formvalues)){
			$formvalues['contactid'] = NULL;
		}
		if(!isArrayKeyAnEmptyString('controller', $formvalues)){
			$this->setController($formvalues['controller']);
		}
		if(!isArrayKeyAnEmptyString('regionid_hidden', $formvalues)){
			$formvalues['regionid'] = $formvalues['regionid_hidden'];
		}
		if(!isArrayKeyAnEmptyString('provinceid_hidden', $formvalues)){
			$formvalues['provinceid'] = $formvalues['provinceid_hidden'];
		}
		if(!isArrayKeyAnEmptyString('districtid_hidden', $formvalues)){
			$formvalues['districtid'] = $formvalues['districtid_hidden'];
		}
		
		// debugMessage($formvalues); exit();
		parent::processPost($formvalues);
	}
	/*
	 * Custom save logic
	 */
	function transactionSave(){
		$conn = Doctrine_Manager::connection();
		$session = SessionWrapper::getInstance();
		
		# begin transaction to save
		try {
			$conn->beginTransaction();
			# initial save
			$this->save();
				
			# commit changes
			$conn->commit();
			
			# add log to audit trail
			$view = new Zend_View();
			$controller = $this->getController();
			$url = $view->serverUrl($view->baseUrl('member/view/id/'.encode($this->getID())));
			$profiletype = 'Member ';
			$usecase = '2.1';
			$module = '2';
			$type = MEMBER_CREATE;
			if($controller == 'profile'){
				$url = $view->serverUrl($view->baseUrl('profile/view/id/'.encode($this->getID())));
				$profiletype = 'User Profile ';
				$usecase = '1.3';
				$module = '1';
				$type = USER_CREATE;
			}
			
			$browser = new Browser();
			$audit_values = $session->getVar('browseraudit');
			$audit_values['module'] = $module;
			$audit_values['usecase'] = $usecase;
			$audit_values['transactiontype'] = $type;
			$audit_values['status'] = "Y";
			$audit_values['userid'] = $session->getVar('userid');
			$audit_values['transactiondetails'] = $profiletype." <a href='".$url."' class='blockanchor'>".$this->getName()."</a> created";
			$audit_values['url'] = $url;
			// debugMessage($audit_values);
			$this->notify(new sfEvent($this, $type, $audit_values));
			
		} catch(Exception $e){
			$conn->rollback();
			// debugMessage('Error is '.$e->getMessage());
			throw new Exception($e->getMessage());
			return false;
		}
		
		// invite via email
		if($this->getIsBeingInvited() == 1){
			$this->inviteViaEmail();
		}
		
		return true;
	}
	# update after
	function beforeUpdate(){
		$session = SessionWrapper::getInstance();
		# set object data to class variable before update
		$member = new Member();
		$member->populate($this->getID());
		$this->setPreUpdateData($member->toArray());
		// exit;
		return true;
	}
	# update after
	function afterUpdate($savetoaudit = true){
		$session = SessionWrapper::getInstance();
		# check if user is being invited during update
		if($this->getIsBeingInvited() == 1){
	 		$this->inviteViaEmail();
	 		
	 		# add log to audit trail
	 		$view = new Zend_View();
	 		$url = $view->serverUrl($view->baseUrl('profile/view/id/'.encode($this->getID())));
	 		 
	 		$browser = new Browser();
	 		$audit_values = $session->getVar('browseraudit');
	 		$audit_values['module'] = '1';
	 		$audit_values['usecase'] = '1.15';
	 		$audit_values['transactiontype'] = USER_INVITE;
	 		$audit_values['status'] = "Y";
	 		$audit_values['userid'] = $session->getVar('userid');
	 		$audit_values['transactiondetails'] = "User <a href='".$url."' class='blockanchor'>".$this->getName()."</a> Invited via Email ";
	 		$audit_values['url'] = $url;
	 		// debugMessage($audit_values);
	 		$this->notify(new sfEvent($this, USER_INVITE, $audit_values));
        }
		
        if($savetoaudit){
	        # set postupdate from class object, and then save to audit
	        $prejson = json_encode($this->getPreUpdateData()); // debugMessage($prejson);
	        
	        $this->clearRelated(); // clear any current object relations
	        $after = $this->toArray(); // debugMessage($after);
	        $postjson = json_encode($after); // debugMessage($postjson);
	        
	        // $diff = array_diff($prejson, $postjson);  // debugMessage($diff);
	        $diff = array_diff($this->getPreUpdateData(), $after);
	        $jsondiff = json_encode($diff); // debugMessage($jsondiff);
	        
	        $view = new Zend_View();
	        $controller = $this->getController();
	        $url = $view->serverUrl($view->baseUrl('member/view/id/'.encode($this->getID())));
	        $profiletype = 'Member ';
	        $usecase = '2.2';
	        $module = '2';
	        $type = MEMBER_UPDATE;
	        if($controller == 'profile'){
	        	$url = $view->serverUrl($view->baseUrl('profile/view/id/'.encode($this->getID())));
	        	$profiletype = 'User Profile ';
	        	$usecase = '1.4';
	        	$module = '1';
	        	$type = USER_UPDATE;
	        }
	        
	        $browser = new Browser();
	        $audit_values = $session->getVar('browseraudit');
	        $audit_values['module'] = $module;
	        $audit_values['usecase'] = $usecase;
	        $audit_values['transactiontype'] = $type;
	        $audit_values['status'] = "Y";
	        $audit_values['userid'] = $session->getVar('userid');
	        $audit_values['transactiondetails'] = $profiletype." <a href='".$url."' class='blockanchor'>".$this->getName()."</a> updated";
	        $audit_values['isupdate'] = 1;
	        $audit_values['prejson'] = $prejson;
	        $audit_values['postjson'] = $postjson;
	        $audit_values['jsondiff'] = $jsondiff;
	        $audit_values['url'] = $url;
	        // debugMessage($audit_values);
	        $this->notify(new sfEvent($this, $type, $audit_values));
        }
        
        return true;
	}
	# find duplicates after save
	function getDuplicates(){
		$q = Doctrine_Query::create()->from('Member u')->where("u.email = '".$this->getEmail()."' AND u.username = '".$this->getUserName()."' AND u.id <> '".$this->getID()."' ");
		
		$result = $q->execute();
		return $result;
	}
	# invite user to activate via email
	function inviteViaEmail(){
		$session = SessionWrapper::getInstance();
		if($this->sendProfileInvitationNotification()){
			$session->setVar('invitesuccess', "Email Invitation sent to ".$this->getEmail());
		}
	
		return true;
	}
	/**
	 * Reset the password for  the user. All checks and validations have been completed
	 * 
	 * @param String $newpassword The new password. If the new password is not defined, a new random password is generated
	 *
	 * @return Boolean TRUE if the password is changed, FALSE if it fails to change the user's password.
	 */
	 function resetPassword($newpassword = "") {
	 	# check if the password is empty 
	 	if (isEmptyString($newpassword)) {
	 		# generate a new random password
	 		$newpassword = $this->generatePassword(); 
	 	}
	 	return $this->changePassword($newpassword); 
	}
	/**
	 * Change the password for the user. All checks and validations have been completed
	 * 
	 * @param String $providedpassword The password provided on the screen
	 * @param String $newpassword The new password
	 *
	 * @return TRUE if the password is changed, FAlSE if it fails to change the user's password.
	 */
	function changePassword($newpassword){
		$session = SessionWrapper::getInstance();
		
		// now change the password
		$this->setPassword(sha1($newpassword));
		$this->setActivationKey('');
		
		try {
			$this->save();
			
			$view = new Zend_View();
			$url = $view->serverUrl($view->baseUrl('profile/view/id/'.encode($this->getID())));
			$usecase = '1.8';
			$module = '1';
			$type = USER_CHANGE_PASSWORD;
			$details = "Password for <a href='".$url."' class='blockanchor'>".$this->getName()."</a> Changed";
			
			$browser = new Browser();
			$audit_values = $session->getVar('browseraudit');
			$audit_values['module'] = $module;
			$audit_values['usecase'] = $usecase;
			$audit_values['transactiontype'] = $type;
			$audit_values['userid'] = $session->getVar('userid');
			$audit_values['url'] = $url;
			$audit_values['transactiondetails'] = $details;
			$audit_values['status'] = "Y";
			// debugMessage($audit_values);
			$this->notify(new sfEvent($this, $type, $audit_values));
			
			return true;
		} catch (Exception $e) {
			// debugMessage($e->getMessage());
			$session->setVar(ERROR_MESSAGE, "Error in changing Password. ".$e->getMessage());
			return false;
		}
	}
	/*
	 * Reset the user's password and send a notification to complete the recovery  
	 *
	 * @return Boolean TRUE if resetting is successful and FALSE if emailaddress security questions and answer is invalid or has no record in the database
	 */
	function recoverPassword() {
		$session = SessionWrapper::getInstance();
		# reset the password and set the next password change date
		$this->setActivationKey($this->generateActivationKey()); // debugMessage($this->toArray());
		# save the activation key for the user 
		$this->save();
		
		# Send the user the reset password email
		$this->sendRecoverPasswordEmail();
		
		$view = new Zend_View();
		$url = $view->serverUrl($view->baseUrl('profile/view/id/'.encode($this->getID())));
		$usecase = '1.14';
		$module = '1';
		$type = USER_RECOVER_PASSWORD;
		$details = "Recover password request for <a href='".$url."' class='blockanchor'>".$this->getName()."</a>";
		
		$browser = new Browser();
		$audit_values = $session->getVar('browseraudit');
		$audit_values['module'] = $module;
		$audit_values['usecase'] = $usecase;
		$audit_values['transactiontype'] = $type;
		$audit_values['userid'] = $session->getVar('userid');
		$audit_values['url'] = $url;
		$audit_values['transactiondetails'] = $details;
		$audit_values['status'] = "Y";
		// debugMessage($audit_values);
		$this->notify(new sfEvent($this, $type, $audit_values));
		
		return true;
	}
	/**
	 * Send an email with a link to activate the members' account
	 */
	function sendRecoverPasswordEmail() {
		$session = SessionWrapper::getInstance(); 
		$template = new EmailTemplate(); 
		// create mail object
		$mail = getMailInstance(); 

		// assign values
		$template->assign('firstname', $this->getFirstName());
		// just send the parameters for the activationurl, the actual url will be built in the view 
		// $template->assign('resetpasswordurl', array("controller"=> "user","action"=> "resetpassword", "actkey" => $this->getActivationKey(), "id" => encode($this->getID())));
		$viewurl = $template->serverUrl($template->baseUrl('user/resetpassword/id/'.encode($this->getID())."/actkey/".$this->getActivationKey()."/")); 
		$template->assign('resetpasswordurl', $viewurl);
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		
		// configure base stuff
		$mail->addTo($this->getEmail());
		// set the send of the email address
		$mail->setFrom($this->config->notification->emailmessagesender, $this->config->notification->notificationsendername);
		
		$mail->setSubject($this->translate->_('profile_email_subject_recoverpassword'));
		// render the view as the body of the email
		$mail->setBodyHtml($template->render('recoverpassword.phtml'));
		// debugMessage($template->render('recoverpassword.phtml')); 
		try {
			$mail->send();
		} catch (Exception $e) {
			$session->setVar(ERROR_MESSAGE, 'Email notification not sent! '.$e->getMessage());
		}
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		$mail->clearFrom();
		
		return true;
   }
   /**
    * Process the activation key from the activation email
    * 
    * @param $actkey The activation key 
    * 
    * @return bool TRUE if the signup process completes successfully, false if activation key is invalid or save fails
    */
   function activateAccount($actkey, $acttype = false) {
   		# save to the audit trail
		$isphoneactivation = $acttype;
		# validate the activation key 
		if($this->getActivationKey() != $actkey){
			// debugMessage('failed');
			# Log to audit trail when an invalid activation key is used to activate account
			$audit_values = array("executedby" => $this->getID(), "transactiontype" => USER_SIGNUP, "success" => "N");
			$audit_values["transactiondetails"] = "Invalid Activation Code specified for User(".$this->getID().") (".$this->getEmail()."). "; 
			// $this->notify(new sfEvent($this, USER_SIGNUP, $audit_values));
			$this->getErrorStack()->add("user.activationkey", $this->translate->_("profile_invalid_actkey_error"));
			return false;
		}
		
		# set active to true and blank out activation key
		$this->setStatus(1);		
		$this->setActivationKey("");
		$startdate = date("Y-m-d H:i:s");
		$this->setActivationDate($startdate);
		
		# save
		try {
			$this->save();
			
			# if user activated via phone. automatically set thier phone as validated.
			if($isphoneactivation){
				# activate account
				$this->activatePhone(1);
				# send confirmation to mobile
				$this->sendSignupConfirmationToMobile();
			}
			
			# Add to audittrail that a new user has been activated.
			$audit_values = array("executedby" => $this->getID(), "transactiontype" => USER_SIGNUP, "success" => "Y");
			$audit_values["transactiondetails"] = $this->getID()." (".$this->getEmail().") has completed the sign up process"; 
			// $this->notify(new sfEvent($this, USER_SIGNUP, $audit_values));
		
			return true;
			
		} catch (Exception $e){
			$this->getErrorStack()->add("user.activation", $this->translate->_("profile_activation_error"));
			$this->logger->err("Error activating Member ".$this->getEmail()." ".$e->getMessage());
			// debugMessage($e->getMessage());
			# log to audit trail when an error occurs in updating payee details on user account
			$audit_values = array("executedby" => $this->getID(), "transactiontype" => USER_SIGNUP, "success" => "N");
			$audit_values["transactiondetails"] = "An error occured in activating account for User(".$this->getID().") (".$this->getEmail()."): ".$e->getMessage(); 
			// $this->notify(new sfEvent($this, USER_SIGNUP, $audit_values));
			return false;
		}
   	}
   
	/**
    * Process the deactivation for an agent
    * 
    * @param $actkey The activation key 
    * 
    * @return bool TRUE if the signup process completes successfully, false if activation key is invalid or save fails
    */
   function deactivateAccount($status = 0) {
   		# save to the audit trail
   		
		# set active to true and blank out activation key
		$this->setStatus($status);		
		$this->setActivationKey('');
		// $this->setActivationDate(NULL);
		if($this->getusergroups()->count() == 0){
			$this->getusergroups()->get(1)->setUserID($this->getID());
			$this->getusergroups()->get(1)->setGroupID($this->getType());
		}
		
		$this->save();
		
		return true;
   }
	/**
	 * Send a notification to agent that their account will be approved shortly
	 * 
	 * @return bool whether or not the signup notification email has been sent
	 *
	 */
	function sendSignupNotification() {
		$session = SessionWrapper::getInstance(); 
		$template = new EmailTemplate(); 
		# create mail object
		$mail = getMailInstance(); 

		# assign values
		$template->assign('firstname', $this->getFirstName());
		$viewurl = $template->serverUrl($template->baseUrl('signup/activate/id/'.encode($this->getID())."/actkey/".$this->getActivationKey()."/")); 
		$template->assign('activationurl', $viewurl);
		$template->assign('usertype', 2);
				
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		
		# configure base stuff
		$mail->addTo($this->getEmail(), $this->getName());
		# set the send of the email address
		$subject = sprintf($this->translate->_('profile_email_subject_signup'), getAppName());
		$mail->setFrom($this->config->notification->emailmessagesender, $this->config->notification->notificationsendername);
		
		$mail->setSubject($subject);
		# render the view as the body of the email
		$mail->setBodyHtml($template->render('signupnotification.phtml'));
		// debugMessage($template->render('signupnotification.phtml')); // exit();
		
		try {
			$mail->send();
		} catch (Exception $e) {
			$session->setVar(ERROR_MESSAGE, 'Email notification not sent! '.$e->getMessage());
		}
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		$mail->clearFrom();
		
		return true;
	}
	# set activation code to change user's email
	function triggerEmailChange($newemail) {
		$this->setActivationKey($this->generateActivationKey());
		$this->setTempEmail($newemail);
		$this->save();
		$this->sendNewEmailActivation();
		return true;
	}
	
	# send new email change confirmation
	function sendNewEmailActivation() {
		$session = SessionWrapper::getInstance(); 
		$template = new EmailTemplate(); 
		# create mail object
		$mail = getMailInstance();
		$view = new Zend_View();
		
		// assign values
		$template->assign('firstname', $this->getFirstName());
		$template->assign('newemail', $this->getTempEmail());
		$viewurl = $template->serverUrl($template->baseUrl('profile/newemail/id/'.encode($this->getID()).'/actkey/'.$this->getActivationKey())); 
		$template->assign('activationurl', $viewurl);
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		
		// configure base stuff
		$mail->addTo($this->getEmail(), $this->getName());
		// set the send of the email address
		$mail->setFrom($this->config->notification->emailmessagesender, $this->config->notification->notificationsendername);
		
		$mail->setSubject($this->translate->_('profile_email_subject_changeemail'));
		// render the view as the body of the email
		$mail->setBodyHtml($template->render('emailchangenotification.phtml'));
		// debugMessage($template->render('emailchangenotification.phtml')); exit();
		try {
			$mail->send();
		} catch (Exception $e) {
			$session->setVar(ERROR_MESSAGE, 'Email notification not sent! '.$e->getMessage());
		}
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		$mail->clearFrom();
		
		return true;
	}
	/**
	 * Send a notification to agent that their account will be approved shortly
	 * 
	 * @return bool whether or not the signup notification email has been sent
	 *
	 */
	function sendDeactivateNotification() {
		$session = SessionWrapper::getInstance(); 
		$template = new EmailTemplate(); 
		# create mail object
		$mail = getMailInstance(); 

		// assign values
		$template->assign('firstname', $this->getFirstName());
		// $template->assign('activationurl', array("action"=> "activate", "actkey" => $this->getActivationKey(), "id" => encode($this->getID())));
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		
		// configure base stuff
		$mail->addTo($this->getEmail(), $this->getName());
		// set the send of the email address
		$mail->setFrom($this->config->notification->emailmessagesender, $this->config->notification->notificationsendername);
		
		$mail->setSubject("Account Deactivation");
		// render the view as the body of the email
		$mail->setBodyHtml($template->render('accountdeactivationconfirmation.phtml'));
		// debugMessage($template->render('accountdeactivationconfirmation.phtml')); // exit();
		try {
			$mail->send();
		} catch (Exception $e) {
			$session->setVar(ERROR_MESSAGE, 'Email notification not sent! '.$e->getMessage());
		}
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		$mail->clearFrom();
		
		return true;
	}
	# change email notification to new address
	function sendNewEmailNotification($newemail) {
		$session = SessionWrapper::getInstance(); 
		$template = new EmailTemplate(); 
		# create mail object
		$mail = getMailInstance(); 
		
		// assign values
		$template->assign('firstname', $this->getFirstName());
		$template->assign('fromemail', $this->getEmail());
		$template->assign('toemail', $newemail);
		$template->assign('code', $this->getActivationKey());
		$viewurl = $template->serverUrl($template->baseUrl('profile/changeemail/id/'.encode($this->getID())."/actkey/".$this->getActivationKey()."/ref/".encode($newemail)."/"));
		$template->assign('activationurl', $viewurl);
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		
		// configure base stuff
		$mail->addTo($newemail, $this->getName());
		// set the send of the email address
		$mail->setFrom($this->config->notification->emailmessagesender, $this->config->notification->notificationsendername);
		
		$mail->setSubject("Email Change Request");
		// render the view as the body of the email
		$mail->setBodyHtml($template->render('changeemail_newnotification.phtml'));
		// debugMessage($template->render('changeemail_newnotification.phtml')); exit();
		try {
			$mail->send();
		} catch (Exception $e) {
			$session->setVar(ERROR_MESSAGE, 'Email notification not sent! '.$e->getMessage());
		}
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		$mail->clearFrom();
		
		return true;
	}
	
	# change email notification to old address
	function sendOldEmailNotification($newemail) {
		$session = SessionWrapper::getInstance(); 
		$template = new EmailTemplate(); 
		# create mail object
		$mail = getMailInstance(); 
		
		// assign values
		$template->assign('firstname', $this->getFirstName());
		$template->assign('fromemail', $this->getEmail());
		$template->assign('toemail', $newemail);
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		
		// configure base stuff
		$mail->addTo($this->getEmail(), $this->getName());
		// set the send of the email address
		$mail->setFrom($this->config->notification->emailmessagesender, $this->config->notification->notificationsendername);
		
		$mail->setSubject("Email Change Request");
		// render the view as the body of the email
		$mail->setBodyHtml($template->render('changeemail_oldnotification.phtml'));
		// debugMessage($template->render('changeemail_oldnotification.phtml')); //exit();
		try {
			$mail->send();
		} catch (Exception $e) {
			$session->setVar(ERROR_MESSAGE, 'Email notification not sent! '.$e->getMessage());
		}
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		$mail->clearFrom();
		
		return true;
	}
	
	# Send notification to invite person to create an account
	function sendProfileInvitationNotification() {
		$session = SessionWrapper::getInstance(); 
		$template = new EmailTemplate(); 
		# create mail object
		$mail = getMailInstance();
		$view = new Zend_View(); 

		// assign values
		$template->assign('firstname', isEmptyString($this->getFirstName()) ? 'Friend' : $this->getFirstName());
		$template->assign('inviter', $this->getInvitedBy()->getName());
		// the actual url will be built in the view
		$viewurl = $template->serverUrl($template->baseUrl('signup/index/id/'.encode($this->getID())."/")); 
		$template->assign('invitelink', $viewurl);
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		
		// configure base stuff
		$mail->addTo($this->getEmail(), $this->getName());
		// set the send of the email address
		$mail->setFrom($this->config->notification->emailmessagesender, $this->config->notification->notificationsendername);
		
		$mail->setSubject(sprintf($this->translate->_('profile_email_subject_invite_user'), getAppName()));
		// render the view as the body of the email
		$mail->setBodyHtml($template->render('invitenotification.phtml'));
		// debugMessage($template->render('invitenotification.phtml')); exit();
		
		try {
			$mail->send();
		} catch (Exception $e) {
			$session->setVar(ERROR_MESSAGE, 'Email notification not sent! '.$e->getMessage());
		}
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		$mail->clearFrom();
		
		return true;
	}
	/**
	 * Generate a new password incase a user wants a new password
	 * 
	 * @return String a random password 
	 */
    function generatePassword() {
		return $this->generateRandomString($this->config->password->passwordminlength);
    }
	/**
	 * Generate a 10 digit activation key  
	 * 
	 * @return String An activation key
	 */
    function generateActivationKey() {
		return substr(md5(uniqid(mt_rand(), 1)), 0, 6);
    }
   /**
    * Find a user account either by their email address 
    * 
    * @param String $email The email
    * @return Member or FALSE if the user with the specified email does not exist 
    */
	function findByEmail($email) {
		# query active user details using email
		$q = Doctrine_Query::create()->from('Member u')->where('u.email = ?', $email);
		$result = $q->fetchOne(); 
		
		# check if the user exists 
		if(!$result){
			# user with specified email does not exist, therefore is valid
			$this->getErrorStack()->add("user.invalid", $this->translate->_("profile_user_invalid_error"));
			return false;
		}
		
		$data = $result->toArray(); 

		# merge all the data including the user groups 
		$this->merge($data);
		# also assign the identifier for the object so that it can be updated
		$this->assignIdentifier($data['id']); 
		
		return true; 
	}
	# find user by email
	function populateByEmail($email) {
		# query active user details using email
		$q = Doctrine_Query::create()->from('Member u')->where('u.email = ?', $email);
		$result = $q->fetchOne(); 
		
		# check if the user exists 
		if(!$result){
			$result = new Member();
		}
		
		return $result; 
	}
	# find user by phone
	function populateByPhone($phone) {
		$query = Doctrine_Query::create()->from('Member m')->where("m.phone = '".$phone."' || m.phone LIKE '%".getShortPhone($phone)."%'");
		//debugMessage($query->getSQLQuery());
		$result = $query->execute();
		return $result->get(0);
	}
	function findByUsername($username) {
		# query active user details using email
		$q = Doctrine_Query::create()->from('Member u')->where('u.username = ?', $username);
		$result = $q->fetchOne(); 
		
		if($result){
			$data = $result->toArray(); 
		} else {
			$data = $this->toArray(); 
		}
		
		# merge all the data including the user groups 
		$this->merge($data);
		# also assign the identifier for the object so that it can be updated
		if($result){
			$this->assignIdentifier($data['id']);
		} 
		
		return true; 
	}
	/**
	 * Return the user's full names, which is a concatenation of the first and last names
	 *
	 * @return String The full name of the user
	 */
	function getName() {
		return !isEmptyString($this->getDisplayName()) ? $this->getDisplayName() : $this->getFirstName()." ".$this->getLastName()." ".$this->getOtherName();
	}
	# function to determine the user's profile path
	function getProfilePath() {
		$path = "";
		$view = new Zend_View();
		// $path = '<a href="'.$view->serverUrl($view->baseUrl('user/'.strtolower($this->getUserName()))).'">'.$view->serverUrl($view->baseUrl('user/'.strtolower($this->getUserName()))).'</a>';
		$path = '<a href="javascript: void(0)">'.$view->serverUrl($view->baseUrl('user/'.strtolower($this->getUserName()))).'</a>';
		return $path;
	}
	/*
	 * TODO Put proper comments
	 */
	function generateRandomString($length) {
		$rand_array = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z","0","1","2","3","4","5","6","7","8","9");
		$rand_id = "";
		for ($i = 0; $i <= $length; $i++) {
			$rand_id .= $rand_array[rand(0, 35)];
		}
		return $rand_id;
	}
 	/**
     * Return an array containing the IDs of the groups that the user belongs to
     *
     * @return Array of the IDs of the groups that the user belongs to
     */
    function getGroupIDs() {
        $ids = array();
        $groups = $this->getUserGroups();
        //debugMessage($groups->toArray());
        foreach($groups as $thegroup) {
            $ids[] = $thegroup->getGroupID();
        }
        return $ids;
    }
    /**
     * Display a list of groups that the user belongs
     *
     * @return String HTML list of the groups that the user belongs to
     */
    function displayGroups() {
        $groups = $this->getUserGroups();
        $str = "";
        if ($groups->count() == 0) {
            return $str;
        }
        $str .= '<ul class="list">';
        foreach($groups as $thegroup) {
            $str .= "<li>".$thegroup->getGroup()->getName()."</li>"; 
        }
        $str .= "</ul>";
        return $str; 
    }
	
	/**
     * Determine the gender strinig of a person
     * @return String the gender
     */
    function getGenderLabel(){
    	if($this->isMale()){
			return 'Male';
		}
		if($this->isFemale()){		
			return 'Female';
		}
		return '';
    }
 	/**
     * Determine if a person is male
     * @return bool
     */
    function isMale(){
    	return $this->getGender() == '1' ? true : false; 
    }
	/**
     * Determine if a person is female
     * @return bool
     */
    function isFemale(){
    	return $this->getGender() == '2' ? true : false; 
    }
    
	# Determine gender text depending on the gender
	function getGenderText(){
		if($this->isMale()){
			return 'Male';
		}
		if($this->isFemale()){		
			return 'Female';
		}
		return '';
	}
	# Determine if user profile has been activated
	function isActivated(){
		return $this->getStatus() == 1;
	}
	# Determine if user has accepted terms
	function hasAcceptedTerms(){
		return $this->getAgreedToTerms() == 1;
	}
    # Determine if user is active	 
	function isUserActive() {
		return $this->getStatus() == 1;
	}
	# determine text to display depending on the status of the user
	function getStatusLabel(){
		return $this->getStatus() == 1 ? 'Active' : 'Inactive';
	}
    # Determine if user is deactivated
	function isUserInActive() {
		return $this->getStatus() == 0 ? true : false;
	}
	# determine if user has been pending
	function isPending() {
		return $this->getStatus() == 1 ? true : false;
	}
	# determine if user has been deactivated
	function isDeactivated() {
		return $this->getStatus() == 2 ? true : false;
	}
	# function get user type
	function getUserTypeText(){
		return getUserType($this->getType());
	}
	# determine if is an admin
	function isAdmin(){
    	return $this->getType() == 1 ? true : false; 
    }
	function isDataClerk(){
    	return $this->getType() == 3 ? true : false; 
    }
	# determine if a department
	function isManager(){
    	return $this->getType() == 4 ? true : false; 
    }
    # determine if a region clerk
    function isRegionalClerk(){
    	return $this->getType() == 3 ? true : false;
    }
    # determine if user is a province data clerk
    function isProvinceClerk(){
    	return $this->getType() == 4 ? true : false;
    }
    # determine if user is a district data clerk
    function isDistrictClerk(){
    	return $this->getType() == 5 || $this->getType() == 10 ? true : false;
    }
    # determine if user is a subcounty data clerk
    function isSubCountyClerk(){
    	return $this->getType() == 6 ? true : false;
    }
    # determine if account is a user
    function isUser(){
    	return !isEmptyString($this->getType()) ? true : false;
    }
    # determine if person has not been invited
    function hasNotBeenInvited() {
    	return $this->getIsInvited() == 0 ? true : false;
    }
    # determine if person has been invited
    function hasBeenInvited() {
    	return $this->getIsInvited() == 1 ? true : false;
    }
    function hasAcceptedInvitation() {
    	return $this->getHasAcceptedInvite() == 1 ? true : false;
    }
    # determine if user has pending activation
    function hasPendingActivation() {
   		return $this->isUserInActive() && $this->hasBeenInvited() && !isEmptyString($this->getInvitedByID()) ? true : false;
    }
	/**
	 * Return the date of birth 
	 * @return string dateofbirth 
	 */
	function getBirthDateFormatted() {
		$birth = "--";
		if(!isEmptyString($this->getDateOfBirth())){
			$birth = changeMySQLDateToPageFormat($this->getDateOfBirth());
		} 
		return $birth;
	}
	
	# relative path to profile image
	function hasProfileImage(){
		$real_path = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."members".DIRECTORY_SEPARATOR."member_";
		$real_path = $real_path.$this->getID().DIRECTORY_SEPARATOR."avatar".DIRECTORY_SEPARATOR."medium_".$this->getProfilePhoto();
		// debugMessage($real_path);
		if(file_exists($real_path) && !isEmptyString($this->getProfilePhoto())){
			return true;
		}
		return false;
	}
	# determine if person has profile image
	function getRelativeProfilePicturePath(){
		$real_path = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."members".DIRECTORY_SEPARATOR."member_";
		$real_path = $real_path.$this->getID().DIRECTORY_SEPARATOR."avatar".DIRECTORY_SEPARATOR."medium_".$this->getProfilePhoto();
		if(file_exists($real_path) && !isEmptyString($this->getProfilePhoto())){
			return $real_path;
		}
		$real_path = BASE_PATH.DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."members".DIRECTORY_SEPARATOR."member_0".DIRECTORY_SEPARATOR."avatar".DIRECTORY_SEPARATOR."default_medium_male.jpg";
		if($this->isFemale()){
			$real_path = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."members".DIRECTORY_SEPARATOR."member_0".DIRECTORY_SEPARATOR."avatar".DIRECTORY_SEPARATOR."default_medium_female.jpg";
		}
		return $real_path;
	}
	
	# determine path to small profile picture
	function getSmallPicturePath() {
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$path = $baseUrl.'/uploads/default/default_small_male.jpg';
		if($this->isFemale()){
			$path = $baseUrl.'/uploads/default/default_small_female.jpg'; 
		}
		if($this->hasProfileImage()){
			$path = $baseUrl.'/uploads/members/member_'.$this->getID().'/avatar/small_'.$this->getProfilePhoto();
		}
		return $path;
	}
	# determine path to thumbnail profile picture
	function getThumbnailPicturePath() {
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$path = $baseUrl.'/uploads/default/default_thumbnail_male.jpg';
		if($this->isFemale()){
			$path = $baseUrl.'/uploads/default/default_thumbnail_female.jpg'; 
		}
		if($this->hasProfileImage()){
			$path = $baseUrl.'/uploads/members/member_'.$this->getID().'/avatar/thumbnail_'.$this->getProfilePhoto();
		}
		return $path;
	}
	# determine path to medium profile picture
	function getMediumPicturePath() {
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$path = "";
		$path = $baseUrl.'/uploads/default/default_medium_male.jpg';
		if($this->isFemale()){
			$path = $baseUrl.'/uploads/default/default_medium_female.jpg'; 
		}
		if($this->hasProfileImage()){
			$path = $baseUrl.'/uploads/members/member_'.$this->getID().'/avatar/medium_'.$this->getProfilePhoto();
		}
		// debugMessage($path);
		return $path;
	}
	# determine path to large profile picture
	function getLargePicturePath() {
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$path = $baseUrl.'/uploads/default/default_large_male.jpg';
		if($this->isFemale()){
			$path = $baseUrl.'/uploads/default/default_large_female.jpg'; 
		}
		if($this->hasProfileImage()){
			$path = $baseUrl.'/uploads/members/member_'.$this->getID().'/avatar/large_'.$this->getProfilePhoto();
		}
		# debugMessage($path);
		return $path;
	}
	/**
	 * Get the full name of the country from the two digit code
	 * 
	 * @return String The full name of the state 
	 */
	function getCountryName() {
		if(isEmptyString($this->getCountry())){
			return "--";
		}
		$countries = getCountries(); 
		return $countries[$this->getCountry()];
	}
	# invite one user to join. already existing persons
	function inviteOne() {
		$this->setDateInvited(date('Y-m-d'));
		$this->setIsInvited('1');
		$this->setHasAcceptedInvite('0');

		// debugMessage($this->toArray()); exit();
		$this->save();
		
		// send email
		$this->sendProfileInvitationNotification();
		
		return true;
	}
	/**
	 * Send notification to inform user that profile has been activated
	 * @return bool whether or not the notification email has been sent
	 */
	function sendInviteConfirmationNotification() {
		$template = new EmailTemplate(); 
		# create mail object
		$mail = getMailInstance();
		$view = new Zend_View(); 

		// assign values
		$template->assign('firstname', $this->getFirstName());
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		
		// configure base stuff
		$mail->addTo($this->getEmail(), $this->getName());
		// set the send of the email address
		$mail->setFrom($this->config->notification->emailmessagesender, $this->config->notification->notificationsendername);
		
		$subject = sprintf($this->translate->_('profile_email_subject_invite_confirmation'), getAppName());
		$mail->setSubject($subject);
		// render the view as the body of the email
		$mail->setBodyHtml($template->render('inviteconfirmation.phtml'));
		$message_contents = $template->render('signupnotification.phtml');
		// debugMessage($template->render('inviteconfirmation.phtml')); exit();
		$mail->send();
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		$mail->clearFrom();
		
		return true;
	}
	# Send contact us notification
	function sendContactNotification($dataarray) {
		$template = new EmailTemplate(); 
		# create mail object
		$mail = getMailInstance();
		$view = new Zend_View(); 
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		
		// debugMessage($first);
		// assign values
		$template->assign('name', $dataarray['name']);
		$template->assign('email', $dataarray['email']);
		$template->assign('subject', $dataarray['subject']);
		$template->assign('message', nl2br($dataarray['message']));
		
		$mail->setSubject("New Contact Us Message: ".$dataarray['subject']);
		// set the send of the email address
		$mail->setFrom($dataarray['email'], $dataarray['name']);
		
		// configure base stuff
		$mail->addTo($this->config->notification->supportemailaddress);
		// render the view as the body of the email
		$mail->setBodyHtml($template->render('contactconfirmation.phtml'));
		// debugMessage($template->render('contactconfirmation.phtml')); exit();
		$mail->send();
		
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		$mail->clearFrom();
		
		return true;
	}
	# fetch member's gps cords
	function getGPSCordinates(){
		if(isEmptyString($this->getGPSLat()) || isEmptyString($this->getGPSLng())){
			return '';
		}
		return $this->getGPSLat().', '.$this->getGPSLng();
	}
	# determine if member has gps cords
	function hasGPSCoordinates(){
		if(!isEmptyString($this->getGPSLat()) && !isEmptyString($this->getGPSLng())){
			return true;
		}
		return false;
	}
	# determine appointments for a member
	function getAppointments(){
		$query = Doctrine_Query::create()->from('Appointment a')
		->where("a.memberid = '".$this->getID()."' AND a.organisationid IS NULL")->orderby("a.startdate desc");
		// debugMessage($query->getSQLQuery());
		$result = $query->execute();
		if($result){
			return $result;
		}
		return new Appointment();
	}
}
?>
