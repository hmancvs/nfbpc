<?php 
# whether or not the session has errors
$session = SessionWrapper::getInstance(); 
$sessionhaserror = !isEmptyString($session->getVar(ERROR_MESSAGE));

$userid = $session->getVar("userid");
$user = new Member();

$isloggedin = false;
if(!isEmptyString($userid)){
	$isloggedin = true;
}
$type = $session->getVar("type");

# the request object instance
$request = Zend_Controller_Front::getInstance()->getRequest();

# application config
$config = Zend_Registry::get('config');

# pagination defaults
Zend_Paginator::setDefaultScrollingStyle('Sliding');
Zend_View_Helper_PaginationControl::setDefaultViewPartial("index/pagination_control.phtml");

$hide_on_print_class = $request->getParam(PAGE_CONTENTS_ONLY) == "true" ? "hideonprint" : ""; 

// initialize the ACL for all views
$acl = getACLInstance(); 
$os = browser_detection('os');
$islinux = false;
if($os != 'nt'){
  $islinux = true;
}

$controller = $request->getControllerName();
$action = $request->getActionName();

$browserappend = " | ".getAppName();
$showsearch = true;
$homedir = 'System / ';
$blockcontent = '<h4><img src="'.$this->baseUrl('images/loader.gif').'" /> Please wait...</h4>';

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);