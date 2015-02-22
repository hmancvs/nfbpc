<?php
/**
 * Collection of functions to handle application events
 */
# list of transaction types
function getTransactionTypes(){
	$types = array(
		'0.1' => 'system.addvariable', // done
		'0.2' => 'system.updatevariable', // done
		'0.3' => 'system.deletevariable', // done
		'0.4' => 'system.createrole', // done
		'0.5' => 'system.updaterole', // done
		'0.6' => 'system.deleterole', // done
	
		'1.1' => 'user.login', // done
		'1.2' => 'user.logout', // done
		'1.3' => 'user.create', // done
		'1.4' => 'user.update', // done
		'1.5' => 'user.delete', // done
		'1.6' => 'user.deactivate', // done
		'1.7' => 'user.reactivate', // done
		'1.8' => 'user.changepassword', // done
		'1.9' => 'user.resetpassword', // done
		'1.10' => 'user.resetpasswordconfirmation', // done
		'1.11' => 'user.changeemail', // done
		'1.12' => 'user.changeemailconfirmation', // done
		'1.17' => 'user.changeusername', // done
		'1.13' => 'user.uploadphoto', // done
		'1.14' => 'user.lostpassword', // done
		'1.15' => 'user.invite', // done
		'1.16' => 'user.activate',
	
		'2.1' => 'member.create', // done
		'2.2' => 'member.update', // done
		'2.3' => 'member.delete', // done
		'2.4' => 'member.uploadphoto', // done
		'2.5' => 'member.bulkupload', // done
	
		'3.1' => 'organisation.create', // done
		'3.2' => 'organisation.update', // done
		'3.3' => 'organisation.delete', // done
		'3.4' => 'organisation.uploadphoto', // done
	
		'4.1' => 'location.create', // done
		'4.2' => 'location.update', // done
		'4.3' => 'location.delete', // done
	
		'5.1' => 'committee.create', // done
		'5.2' => 'committee.update', // done
		'5.3' => 'committee.delete', // done
	);
	return $types;
}
/**
 * Constants defining the available events within the application
 */
define("SYSTEM_ADDVARIABLE", "system.addvariable");
define("SYSTEM_UPDATEVARIABLE", "system.updatevariable");
define("SYSTEM_DELETEVARIABLE", "system.deletevariable");
define("SYSTEM_CREATEROLE", "system.createrole");
define("SYSTEM_UPDATEROLE", "system.updaterole");
define("SYSTEM_DELETEROLE", "system.deleterole");

define("USER_LOGIN", "user.login"); 
define("USER_LOGOUT", "user.logout");
define("USER_CREATE", "user.create");
define("USER_UPDATE", "user.update");
define("USER_DELETE", "user.delete");
define("USER_DEACTIVATE", "user.deactivate");
define("USER_REACTIVATE", "user.reactivate");
define("USER_CHANGE_PASSWORD", "user.changepassword");
define("USER_RESET_PASSWORD", "user.resetpassword");
define("USER_RESET_PASSWORD_CONFIRM", "user.resetpasswordconfirmation");
define("USER_CHANGE_EMAIL", "user.changeemail");
define("USER_CHANGE_EMAIL_CONFIRM", "user.changeemailconfirmation");
define("USER_CHANGE_USERNAME", "user.changeusername");  
define("USER_RECOVER_PASSWORD", "user.lostpassword");
define("USER_INVITE", "user.invite");
define("USER_ACTIVATE", "user.activate");
define("USER_SIGNUP", "user.signup");
define("USER_UPLOADPHOTO", "user.uploadphoto");

define("MEMBER_CREATE", "member.create");
define("MEMBER_UPDATE", "member.update"); 
define("MEMBER_DELETE", "member.delete");
define("MEMBER_UPLOADPHOTO", "member.uploadphoto");
define("MEMBER_BULKUPLOAD", "member.bulkupload");

define("ORG_CREATE", "organisation.create");
define("ORG_UPDATE", "organisation.update");
define("ORG_DELETE", "organisation.delete");
define("ORG_UPLOADPHOTO", "organisation.uploadphoto");

define("LOCATION_CREATE", "location.create");
define("LOCATION_UPDATE", "location.update");
define("LOCATION_DELETE", "location.delete");

define("COMMITTEE_CREATE", "committee.create");
define("COMMITTEE_UPDATE", "committee.update");
define("COMMITTEE_DELETE", "committee.delete");

/**
 * Initialize and Configure an SFEventDispatcher instance
 *
 * @return sfEventDispatcher A configured event dispatcher instance
 */
function initializeSFEventDispatcher() {
   $eventdispatcher = new sfEventDispatcher();
   $eventdispatcher->connect(SYSTEM_ADDVARIABLE, "auditTransactionEventHandler");
   $eventdispatcher->connect(SYSTEM_UPDATEVARIABLE, "auditTransactionEventHandler");
   $eventdispatcher->connect(SYSTEM_DELETEVARIABLE, "auditTransactionEventHandler");
   $eventdispatcher->connect(SYSTEM_CREATEROLE, "auditTransactionEventHandler");
   $eventdispatcher->connect(SYSTEM_UPDATEROLE, "auditTransactionEventHandler");
   $eventdispatcher->connect(SYSTEM_DELETEROLE, "auditTransactionEventHandler");
   
   $eventdispatcher->connect(USER_LOGIN, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_LOGOUT, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_CREATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_UPDATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_DELETE, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_DEACTIVATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_REACTIVATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_CHANGE_PASSWORD, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_RESET_PASSWORD, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_RESET_PASSWORD_CONFIRM, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_CHANGE_EMAIL, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_CHANGE_EMAIL_CONFIRM, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_CHANGE_USERNAME, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_RECOVER_PASSWORD, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_INVITE, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_ACTIVATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_SIGNUP, "auditTransactionEventHandler");
   $eventdispatcher->connect(USER_UPLOADPHOTO, "auditTransactionEventHandler");
   
   $eventdispatcher->connect(MEMBER_CREATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(MEMBER_UPDATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(MEMBER_DELETE, "auditTransactionEventHandler");
   $eventdispatcher->connect(MEMBER_UPLOADPHOTO, "auditTransactionEventHandler");
   $eventdispatcher->connect(MEMBER_BULKUPLOAD, "auditTransactionEventHandler");
   
   $eventdispatcher->connect(ORG_CREATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(ORG_UPDATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(ORG_DELETE, "auditTransactionEventHandler");
   $eventdispatcher->connect(ORG_UPLOADPHOTO, "auditTransactionEventHandler");
   
   $eventdispatcher->connect(LOCATION_CREATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(LOCATION_UPDATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(LOCATION_DELETE, "auditTransactionEventHandler");
   
   $eventdispatcher->connect(COMMITTEE_CREATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(COMMITTEE_UPDATE, "auditTransactionEventHandler");
   $eventdispatcher->connect(COMMITTEE_DELETE, "auditTransactionEventHandler");
   
   return $eventdispatcher; 
}
/**
 * Handle the events
 *
 * @param sfEvent $event The event being handled
 * @return bool TRUE if the audit trail for the transaction is saved sucessfully, FALSE otherwise
 */

function auditTransactionEventHandler($event) {
	$audit_trail = new AuditTrail();
	$audit_trail->processPost($event->getParameters());
	try {
		$audit_trail->save();
	} catch (Exception $e) {
		$logger = Zend_Registry::get("logger");
		$logger->err($e->getMessage()); 
		return false; 
	}
	
	return true;
}
# list of system modules
function getSystemModules(){
	$types = array(
			0 => 'system',
			1 => 'user',
			2 => 'member',
			3 => 'organisation',
			4 => 'location',
			5 => 'committee',
			6 => 'notification'
	);
	return $types;
}
?>