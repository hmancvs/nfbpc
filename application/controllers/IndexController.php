<?php

require_once 'eventdispatcher/sfEventDispatcher.php';
require_once 'eventdispatcher/sfEvent.php';

# event hander functionality
require_once APPLICATION_PATH.'/includes/eventhandlerfunctions.php';

class IndexController extends Zend_Controller_Action  {

	/**
	 * Logger instance
	 * 
	 * @var Zend_Log
	 */
	protected $_logger; 
	/**
	 * Translation instance
	 * 
	 * @var Zend_Translate 
	 */
	protected $_translate; 
	/**
	 * Dispatcher to handle events
	 *
	 * @var sfEventDispatcher
	 */
	protected $_eventdispatcher; 
	
	public function init()    {
        // Initialize logger and translate actions
		$this->_logger = Zend_Registry::get("logger"); 
		$this->_translate = Zend_Registry::get("translate");
		// set the redirector to ignore the baseurl for redirections
		$this->_helper->redirector->setPrependBase(false); 
		
		$this->_eventdispatcher = initializeSFEventDispatcher();
		
		// load the application configuration
		loadConfig(); 
		
		$this->view->referer = $this->getRequest()->getHeader('referer');
		$this->view->viewurl = $_SERVER['REQUEST_URI'];
		/*debugMessage($_SERVER['REQUEST_URI']);
		debugMessage($this->getRequest()); exit();*/
    }
    
    /**
     * Application landing page 
     */
    public function indexAction()  {
    	$session = SessionWrapper::getInstance(); 
    	if($this->getRequest()->getControllerName() == 'index' && !isEmptyString($session->getVar('userid'))){
    		$this->_helper->redirector->gotoUrl($this->view->baseUrl("dashboard"));
    	}
   	 	if($this->getRequest()->getControllerName() == 'index' && isEmptyString($session->getVar('userid'))){
    		$this->_helper->redirector->gotoUrl($this->view->baseUrl("user/login"));
    	}
    }
    
	/**
     * Action to display the access denied page when a user cannot execute the specified action on a resource    
     */
    public function accessdeniedAction()  {
        // do nothing 
    }
    
   public function createAction() {
    	// debugMessage($this->_getAllParams()); exit();
   		// $this->_setParam('id', NULL); // exit();
   		$session = SessionWrapper::getInstance(); 
    	// the name of the class to be instantiated
    	$classname = $this->_getParam("entityname");
    	$new_object = new $classname();
    	
    	// parameters for the response - default do not prepend the baseurl 
    	$response_params = array(); 
    	// add the createdby using the id of the user in session
    	if (isEmptyString($this->_getParam('id'))) {
    		// new entity
    		$this->_setParam('createdby', $session->getVar('userid'));
    		
    		// merge the post data to enable loading of any relationships in process post
    		//  TODO: Verify if this breaks any other functionality
			$new_object->merge(array_remove_empty($this->_getAllParams()), false); 
    	} else {
    		// id is already encoded during update so no need to encode it again 
    		$response_params['id'] = $this->_getParam('id'); 
    		// decode the id field and add it back to the array otherwise it will cause a type error during processPost
    		$this->_setParam('id', decode($this->_getParam('id'))); 
    		// load the details for the current entity from the database 
    		$new_object->populate($this->_getParam('id'));
    		$this->_setParam('lastupdatedby', $session->getVar('userid'));
    	}
    	
    	// populate the object with data from the post and validate the object
    	// to ensure that its wellformed 
    	$new_object->processPost($this->_getAllParams());
		/* debugMessage($new_object->toArray());
		debugMessage('errors are '.$new_object->getErrorStackAsString()); */
		// exit();
    	if ($new_object->hasError()) {
    		// there were errors - add them to the session
    		$this->_logger->info("Validation Error for ".$classname." - ".$new_object->getErrorStackAsString());
    		$session->setVar(FORM_VALUES, $this->_getAllParams());
    		$session->setVar(ERROR_MESSAGE, $new_object->getErrorStackAsString());
    		$response_params['id'] = encode($this->_getParam('id'));  
    		 
    		// return to the create page
    		if (isEmptyString($this->_getParam(URL_FAILURE))) {
    			$this->_helper->redirector->gotoSimple('index', # the action 
	    							    $this->getRequest()->getControllerName(), # the current controller
	    								$this->getRequest()->getModuleName(), # the current module,
	    								$response_params
    	                             );
    	        return false; 
    		} else {
    			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)), $response_params); 
    			return false; 
    		}
    	} // end check for whether errors occured during the population of the object instance from the submitted data
    	
    	// save the object to the database
    	try {
    		switch ($this->_getParam('action')) {
				case "" :
				case ACTION_CREATE :
					if(in_array($new_object->getTableName(), array('member'))){
						$new_object->transactionSave();
					} else {
						$new_object->beforeSave(); 
						$new_object->save(); 
						// there are no errors so call the afterSave() hook
						$new_object->afterSave();
					}
					/*debugMessage($new_object->toArray());
					debugMessage('errors are '.$new_object->getErrorStackAsString()); exit();*/
					break;
				case ACTION_EDIT:  
					// update the entity 
					$new_object->beforeUpdate();
					$new_object->save(); 
					// there are no errors so call the afterSave() hook
					$new_object->afterUpdate();
					// debugMessage('errors are '.$new_object->getErrorStackAsString()); exit();
					break; 
				case ACTION_DELETE:  
					// update the entity 
					$new_object->delete(); 
					// there are no errors so call the afterSave() hook
					$new_object->afterDelete();
					break;
				case ACTION_APPROVE:  
					// update the entity 
					$new_object->approve(); 
					// there are no errors so call the afterSave() hook
					$new_object->afterApprove();
					break;  
				default :
					break;
    		}
    		// exit();
    		// add a success message, if any, to the session for display
    		if (!isEmptyString($this->_getParam(SUCCESS_MESSAGE))) {
    			$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate($this->_getParam(SUCCESS_MESSAGE)));
    		}
    		if (isEmptyString($this->_getParam(URL_SUCCESS))) {
    			// add the id of the new object created which is encoded 
    			$response_params['id'] = encode($new_object->getID()); 
	    		$this->_helper->redirector->gotoSimple('view', # the action 
	    							    $this->getRequest()->getControllerName(), # the current controller
	    								$this->getRequest()->getModuleName(), # the current module,     															
	    	                             $response_params # the parameters for the response
	    	                             );
	    	    return false; 
    		} else {
    			$url = decode($this->_getParam(URL_SUCCESS));
    			if(!isArrayKeyAnEmptyString('nosuccessid', $this->_getAllParams())){
    				$this->_helper->redirector->gotoUrl($url);	
    			} else {
    				// check if the last character is a / then add it
	    			if (substr($url, -1) != "/") {
	    				 // add the slash
	    				 $url.= "/"; 
	    			}
	    			// add the ID parameter
	    			$url.= "id/".encode($new_object->getID()); 
	    			$this->_helper->redirector->gotoUrl($url, $response_params); 
    			}
    			
    			return false; 
    		}
    	} catch (Exception $e) {
    		$session->setVar(FORM_VALUES, $this->_getAllParams());
    		$session->setVar(ERROR_MESSAGE, $e->getMessage()); 
    		$this->_logger->err("Saving Error ".$e->getMessage());
    		// debugMessage($e->getMessage()); exit();
    		
    		// return to the create page
    		if (isEmptyString($this->_getParam(URL_FAILURE))) {
    			$this->_helper->redirector->gotoSimple('index', # the action 
	    							    $this->getRequest()->getControllerName(), # the current controller
	    								$this->getRequest()->getModuleName(), # the current module, 
	    								$response_params 
    	                             );
    	        return false; 
    		} else {
    			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)), $response_params); 
    			return false; 
    		}
    	}
    	// exit();
    }

    public function editAction() {
    	$this->_setParam("action", ACTION_EDIT); 
    	$this->createAction();
    }
    
	public function deleteAction() {
    	$this->_setParam("action", ACTION_DELETE);
    
    	$session = SessionWrapper::getInstance();
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(TRUE);
    
    	$formvalues = $this->_getAllParams(); // debugMessage($formvalues); // exit;
    	$successurl = decode($formvalues[URL_SUCCESS]);
    	if(!isArrayKeyAnEmptyString(SUCCESS_MESSAGE, $formvalues)){
    		$successmessage = decode($formvalues[SUCCESS_MESSAGE]);
    	}
    	$classname = $formvalues['entityname'];
    	$altclassname = '';
    	if(!isArrayKeyAnEmptyString('altdeleteentity', $formvalues)){
    		$altclassname = $formvalues['altdeleteentity'];
    	}
    	// debugMessage($successurl);
    
    	$obj = new $classname;
    	$id = is_numeric($formvalues['id']) ? $formvalues['id'] : decode($formvalues['id']); // debugMessage($id);
    	$obj->populate($id); // debugMessage($obj->toArray());
    	$beforedelete = $obj->toArray(true); // debugMessage($beforedelete);
    	$prejson = json_encode($beforedelete); // debugMessage($postjson);
    	/* debugMessage($obj->toArray());
    	 exit(); */
    	
    	# prepare to notify depending on the action
    	switch ($classname) {
    		case 'Member':
    			if($formvalues['controller'] == 'profile'){
    				$module = '1';
    				$usecase = '1.5';
    				$type = USER_DELETE;
    				$deletedetails = 'User Profile <b>'.$obj->getName().'</b> successfully deleted';
    			}
    			if($formvalues['controller'] == 'member'){
    				$module = '2';
    				$usecase = '2.3';
    				$type = MEMBER_DELETE;
    				$deletedetails = 'Member Profile <b>'.$obj->getName().'</b> successfully deleted';
    			}
    	
    			break;
    		case 'Organisation':
    			$module = '3';
    			$usecase = '3.3';
    			$type = ORG_DELETE;
    			$deletedetails = ' Organisation <b>'.$obj->getName().'</b> successfully deleted';
    			if($obj->getType() == '1'){
    				$deletedetails = ' Church <b>'.$obj->getName().'</b> successfully deleted';
    			}
    			if($obj->getType() == '2'){
    				$deletedetails = ' Ministry <b>'.$obj->getName().'</b> successfully deleted';
    			}
    			break;
    		case 'Location':
    			$module = '4';
    			$usecase = '4.3';
    			$type = LOCATION_DELETE;
    			$location_type = $obj->getLocationTypeName();
    			$deletedetails = $location_type.' <b>'.$obj->getName().'</b> successfully deleted';
    			break;
    		case 'Region':
    			$module = '4';
    			$usecase = '4.3';
    			$type = LOCATION_DELETE;
    			$deletedetails = 'Region <b>'.$obj->getName().'</b> successfully deleted';
    			break;
    		case 'Province':
    			$module = '4';
    			$usecase = '4.3';
    			$type = LOCATION_DELETE;
    			$deletedetails = 'Province <b>'.$obj->getName().'</b> successfully deleted';
    			break;
			case 'Committee':
    			$module = '5';
    			$usecase = '5.3';
    			$type = COMMITTEE_DELETE;
    			$deletedetails = 'Committee <b>'.$obj->getName().'</b> successfully deleted';
    			break;
    		case 'Department':
    		case 'Position':
    		case 'LookupTypeValue':
    			$module = '0';
    			$usecase = '0.3';
    			$type = SYSTEM_DELETEVARIABLE;
    			$var_type = 'Variable';
    			if($classname == 'Department'){
    				$var_type = 'Department';
    				$deletedetails = $var_type.' <b>'.$obj->getName().'</b> successfully deleted';
    			}
    			if($classname == 'Position'){
    				$var_type = 'Position';
    				$deletedetails = $var_type.' <b>'.$obj->getName().'</b> successfully deleted';
    			}
    			if($classname == 'LookupTypeValue'){
    				$var_type = 'Variable ';
    				$deletedetails = 'Variable - <b>'.$obj->getlookupvaluedescription().' </b>('.$obj->getLookupType()->getdisplayname().') successfully deleted';
    			}
    			break;
    		case 'AclGroup':
    			$module = '0';
    			$usecase = '0.6';
    			$type = SYSTEM_DELETEROLE;
    			$deletedetails = 'Role <b>'.$obj->getName().'</b> successfully deleted';
    			break;
    		default:
    			break;
    	}
    	
    	$browser = new Browser();
    	$audit_values = $session->getVar('browseraudit');
    	$audit_values['module'] = $module;
    	$audit_values['usecase'] = $usecase;
    	$audit_values['transactiontype'] = $type;
    	$audit_values['status'] = "Y";
    	$audit_values['userid'] = $session->getVar('userid');
    	$audit_values['transactiondetails'] = $deletedetails;
    	$audit_values['prejson'] = $prejson;
    	// debugMessage($audit_values); 
    	// exit(); 
    	
    	if($obj->delete()) {
    		if(!isArrayKeyAnEmptyString('altdeleteid', $formvalues)){
    			$altobj = new $altclassname;
    			$altobj->populate($formvalues['altdeleteid']);
    			if(!isEmptyString($altobj->getID())){
    				$altobj->delete();
    			}
    		}
    		$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate("global_delete_success"));
    		$successmessage = $this->_getParam(SUCCESS_MESSAGE);
    		if(!isEmptyString($successmessage)){
    			$session->setVar(SUCCESS_MESSAGE, $successmessage);
    		}
    		$this->notify(new sfEvent($this, $type, $audit_values));
    	}
    	$this->_helper->redirector->gotoUrl($successurl);
    }
    
	public function approveAction() {
    	$this->_setParam("action", ACTION_APPROVE); 
    	$this->createAction();
    }
    
	public function rejectAction() {
    	$this->_setParam("action", ACTION_APPROVE); 
    	$this->createAction();
    }
    
    public function listAction() {
    	$listcount = new LookupType();
    	$listcount->setName("LIST_ITEM_COUNT_OPTIONS");
    	$values = $listcount->getOptionValues(); 
    	asort($values, SORT_NUMERIC); 
    	$session = SessionWrapper::getInstance();
    	
    	$dropdown = new Zend_Form_Element_Select('itemcountperpage',
							array(
								'multiOptions' => $values, 
								'view' => new Zend_View(),
								'decorators' => array('ViewHelper'),
							    'class' => array('form-control','width75','inline','perpageswitcher')
							)
						);
		if (isEmptyString($this->_getParam('itemcountperpage'))) {
			if(!isEmptyString($session->getVar('itemcountperpage'))){			
				$dropdown->setValue($session->getVar('itemcountperpage'));
				if($session->getVar('itemcountperpage') == 'ALL'){
					$session->setVar('itemcountperpage', '');
					$dropdown->setValue('50');
				}
			} else {
				$dropdown->setValue('50');
			}
		} else {
			$session->setVar('itemcountperpage', $this->_getParam('itemcountperpage'));
			$dropdown->setValue($session->getVar('itemcountperpage'));
		}
		
	    $this->view->listcountdropdown = '<span>Per page: '.$dropdown->render().'</span>'; 
    }
    /**
     * Redirect list searches to maintain the urls as per zend format 
     */
    public function listsearchAction() {
    	//debugMessage($this->getRequest()->getQuery());
    	// debugMessage($this->_getAllParams()); exit();
    	$this->_helper->redirector->gotoSimple(ACTION_LIST, $this->getRequest()->getControllerName(), 
    											$this->getRequest()->getModuleName(),
    											array_remove_empty(array_merge_maintain_keys($this->_getAllParams(), $this->getRequest()->getQuery())));
    }
    public function viewAction() {
    	
    }
    
    public function returntolistAction(){
    	$this->_helper->redirector->gotoSimple(ACTION_LIST, $this->getRequest()->getControllerName(), 
    											$this->getRequest()->getModuleName(), 
    											array_remove_empty(array_merge_maintain_keys($this->_getAllParams(), $this->getRequest()->getQuery())));    
    }
    public function newAction(){
    	$this->_helper->redirector->gotoSimple(ACTION_INDEX, $this->getRequest()->getControllerName(), 
    											$this->getRequest()->getModuleName(), 
    											array_remove_empty(array_merge_maintain_keys($this->_getAllParams(), $this->getRequest()->getQuery())));  
    }	
    
    function overviewAction() {
    	
    }
	
    public function exportAction() {
    	
    }
	/**
     * Notify all listeners of the event, through the event dispatcher instance for the class. This is just a convenience method to
     * avoid accessing the event dispatcher directly
     *
     * @param sfEvent $event The event that has occured
     */
    function notify($event) {
    	$this->_eventdispatcher->notify($event); 
    }
    
    function selectchainAction() {
	    $select_type = $this->_getParam(SELECT_CHAIN_TYPE); 
		
    	switch ($select_type) { 		
			case 'region_districts': 
				# get all the districts in a region			
				echo generateJSONStringForSelectChain(getDistrictsInRegion($this->_getParam('regionid')), $this->_getParam('currentvalue'));			
				break;
			case 'region_provinces':
				echo generateJSONStringForSelectChain(getProvinces($this->_getParam('regionid')), $this->_getParam('currentvalue'));
				break;
			case 'district_counties': 
				# get all the counties in a district				
				echo generateJSONStringForSelectChain(getCountiesInDistrict($districtid), $this->_getParam('currentvalue'));			
				break;
			case 'location_counties': 
				# get all the counties in a district			
				echo generateJSONStringForSelectChain(getCountiesInDistrict($this->_getParam('locationid')), $this->_getParam('currentvalue'));			
				break;
			case 'county_subcounties': 
				# get all the subcounties in a county			
				echo generateJSONStringForSelectChain(getSubcountiesInCounty($this->_getParam('countyid')), $this->_getParam('currentvalue'));			
				break;
			case 'subcounty_parishes': 
				# get all the parishes in a subcounty			
				echo generateJSONStringForSelectChain(getParishesInSubCounty($this->_getParam('subcountyid')), $this->_getParam('currentvalue'));			
				break;
			case 'parish_villages': 
				# get all the villages in a parishes			
				echo generateJSONStringForSelectChain(getVillagesInParishes($this->_getParam('parishid')), $this->_getParam('currentvalue'));			
				break;	
			case 'district_sub_counties':
				echo generateJSONStringForSelectChain(getSubcountiesInDistrict($this->_getParam('districtid')), $this->_getParam('currentvalue'));			
				break;
			default:
				echo '';
				break;
		}
		
		// disable rendering of the view and layout so that we can just echo the AJAX output 
	    $this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	}
	function selectchaincustomAction() {
		// disable rendering of the view and layout so that we can just echo the AJAX output 
	    $this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);    
		$select_type = $this->_getParam(SELECT_CHAIN_TYPE); 
		
		switch ($select_type) {
			case 'region_provinces':
				$result = getProvinces($this->_getParam('regionid'));
				$result = json_encode($result);
				echo ($result);
				break;
			case 'region_districts':
				$result = getDistricts($this->_getParam('regionid'), false);
				$result = json_encode($result);
				echo ($result);
				break;
			case 'region_districts_nfbpc':
				$result = getDistricts($this->_getParam('regionid'), false);
				$result = json_encode($result);
				echo ($result);
				break;
			case 'province_districts':
				$result = getDistrictsInProvince($this->_getParam('provinceid'), false);
				$result = json_encode($result);
				echo ($result);
				break;
			case 'district_counties':
				$locationid = $districtid = $this->_getParam('districtid');
				$location = new Location();
				$location->populate($districtid);
				if(!isEmptyString($location->getDistrictID())){
					$districtid = $location->getDistrictID();
				}
				
				$result = getCountiesInDistrict($districtid);
				$result = json_encode($result);
				echo ($result);
				break;
			case 'district_subcounties':
				$result = getSubcountiesInDistrict($this->_getParam('districtid'));
				$result = json_encode($result);
				echo ($result);
				break;
			case 'county_subcounties':
				$result = getSubcountiesInCounty($this->_getParam('countyid'));
				$result = json_encode($result);
				echo ($result);
				break;
			case 'subcounty_parishes':
				$result = getParishesInSubCounty($this->_getParam('subcountyid'));
				$result = json_encode($result);
				echo ($result);
				break;
			case 'parish_villages':
				$result = getVillagesInParishes($this->_getParam('parishid'));
				$result = json_encode($result);
				echo ($result);
				break;
			default:
				# get all the villages in a parishes
				echo '';
				break;
		}
	}
	/**
     * Action to download details into MS Excel
    */
    public function exceldownloadAction()  {
    	// disable rendering of the view and layout so that we can just echo the AJAX output
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

        // required for IE, otherwise Content-disposition is ignored
		if(ini_get('zlib.output_compression')) {
			ini_set('zlib.output_compression', 'Off');
		}
		
		$response = $this->getResponse();
		
		# This line will stream the file to the user rather than spray it across the screen
		$response->setHeader("Content-type", "application/vnd.ms-excel");
		
		# replace excelfile.xls with whatever you want the filename to default to
		$response->setHeader("Content-Disposition", "attachment;filename=".time().rand(1, 10).".xls");
		$response->setHeader("Expires", 0);
		$response->setHeader("Cache-Control", "private");
		session_cache_limiter("public");
		
		$session = SessionWrapper::getInstance();
		
		# the coluumns that have numbers, these have to be formatted differently from the rest of the
		# columns
		$number_column_array = explode(",", $this->_getParam(EXPORT_NUMBER_COLUMN_LIST));
		
		$xml = new ExcelXML();
		// the number of columns to ignore in the query, these are usually ids
		$xml->setStartingColumn(trim($this->_getParam(EXPORT_IGNORE_COLUMN_COUNT)));
		echo $xml->generateXMLFromQuery($session->getVar(CURRENT_RESULTS_QUERY));
    }
	/**
     * Action to download details into MS Excel
    */
    public function printerfriendlyAction()  {
    	
    }
    /**
     * Clear user specific cache items on expiry of the session or logout of the user
     *
     */
    public function clearUserCache() {
    	$session = SessionWrapper::getInstance(); 
    	
    	// clear the acl instance for the user
        $aclkey = "acl".$session->getVar('userid'); 
        $cache = Zend_Registry::get('cache');
        $cache->remove($aclkey); 
    }
    /**
     * Clear the user session and any cache files 
     *
     */
    function clearSession() {
    	// clear user specific cache
    	$this->clearUserCache();
    	
        // clear the session
        $session = SessionWrapper::getInstance(); 
        $session->clearSession();
    }
    
    /**
     * Pre-processing for all actions
     *
     * - Disable the layout when displaying printer friendly pages 
     *
     */
    function preDispatch(){
    	// disable rendering of the layout so that we can just echo the AJAX output
    	if(!isEmptyString($this->_getParam(PAGE_CONTENTS_ONLY))) { 
    		$this->_helper->layout->disableLayout();
    	}
    } 
    
    public function addsuccessAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
		$this->_setParam("action", ACTION_VIEW);
		
		$session->setVar(SUCCESS_MESSAGE, "Successfully saved");
   		if(!isArrayKeyAnEmptyString('successmessage', $formvalues)){
			$session->setVar(SUCCESS_MESSAGE, decode($formvalues['successmessage']));
		}
		
    } 

	public function adderrorAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
		
		$currenterror = $session->getVar(ERROR_MESSAGE);
		if(isEmptyString($currenterror)){
			$session->setVar(ERROR_MESSAGE, "An error occured in updating database");
				}  
			}
	
	public function profileupdatesuccessAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
		
		$session->setVar(SUCCESS_MESSAGE, "Profile successfully updated");
		if(!isArrayKeyAnEmptyString('successmessage', $formvalues)){
			$session->setVar(SUCCESS_MESSAGE, decode($formvalues['successmessage']));
		}
	}
	
	public function updatesuccessAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
    	
		$session->setVar(SUCCESS_MESSAGE, "Successfully updated");
		if(!isArrayKeyAnEmptyString('successmessage', $formvalues)){
			$session->setVar(SUCCESS_MESSAGE, decode($formvalues['successmessage']));
		}
    }
	
	public function leftcolumnAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
    	// debugMessage($formvalues);
		
		if($this->_getParam('value') == 1){
			 $session->setVar('toggled', "1");
		} else {
			 $session->setVar('toggled', "");
		}
	}
	
	public function committeeAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
	}
	
	public function profileAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
	}

	public function churchAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
	}
	
	public function ministriesAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
	}
	
	public function searchAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
	}
	
	public function processsearchAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		//$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
		
		//debugMessage($this->getRequest()->getQuery());
    	// debugMessage($this->_getAllParams()); exit();
    	$this->_helper->redirector->gotoSimple('search', $this->getRequest()->getControllerName(), 
    											$this->getRequest()->getModuleName(),
    											array_remove_empty(array_merge_maintain_keys($this->_getAllParams(), $this->getRequest()->getQuery())));
    }
	
	function processcontactAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
		// debugMessage($formvalues);
		
		$recipients_array = array(); 
	    $messagedata = array(); 
		$users = array($formvalues['id']);
	    $execresult = array('result'=>'', 'msg'=>'');
		
		if(count($users) == 0){
	    	$session->setVar(ERROR_MESSAGE, "Error: No Member specified!");
	    	$this->_helper->redirector->gotoUrl(decode($formvalues[URL_SUCCESS]));
	    	$execresult = array('result'=>'fail', 'msg'=>"Error: No Receipients specified!");
	    }
		
		$messages = array(); $sent = array(); $phones = array();
	    $messages['contents'] = $formvalues['contents'];
	    $messages['type'] = 1;
		$messages['subject'] = '';
	    if(!isArrayKeyAnEmptyString('subject', $formvalues)){
	    	$messages['subject'] = $formvalues['subject'];
	    }
		$messages['senderid'] = NULL;
		if(!isArrayKeyAnEmptyString('senderid', $formvalues)){
			$messages['senderid'] = $formvalues['senderid'];
		}
		if(!isArrayKeyAnEmptyString('senderemail', $formvalues)){
			$messages['senderemail'] = $formvalues['senderemail'];
		}
		if(!isArrayKeyAnEmptyString('sendername', $formvalues)){
			$messages['sendername'] = $formvalues['sendername'];
		}
		# process receipients depending on select type
		foreach ($users as $key => $userid){
			$memb = new Member();
			$id = $userid;
			
			$memb->populate($id); // debugMessage($memb->toArray());
			if($memb->isUser()){
				$recipients_array[$id]['recipientid'] = $memb->getID();
			}
			$messagedata[$id]['id'] = $memb->getID();
			$messagedata[$id]['name'] = $memb->getName();
			$messagedata[$id]['email'] = $memb->getEmail();
			$messagedata[$id]['phone'] = $memb->getPhone();
			$sent[] = $memb->getName();
			
			$messages['recipients'] = $recipients_array;
			$messages['membertotal'] = count($messagedata);
			$messages['usertotal'] = count($recipients_array);
			// debugMessage($sent); 
			// debugMessage($messagedata); 
				
			$msg = new Message();
			$msg->processPost($messages);
			/*debugMessage($msg->toArray());
			debugMessage('error is '.$msg->getErrorStackAsString()); exit();*/
		}
		
		if($msg->hasError()){
			$session->setVar(ERROR_MESSAGE, "Error: ".$msg->getErrorStackAsString());
			$session->setVar(FORM_VALUES, $this->_getAllParams());
			$execresult = array('result'=>'fail', 'msg'=>"Error: ".$msg->getErrorStackAsString());
			$this->_helper->redirector->gotoUrl(decode($formvalues[URL_SUCCESS]));
		} else {
			try {
				$msg->save();
				// send message to emails
				if(count($messagedata) > 0){
					foreach($messagedata as $key => $receipient){
						$msgdetail = new MessageRecipient();
						if(!isArrayKeyAnEmptyString('email', $receipient)){
							// debugMessage($formvalues['senderemail'].'-'.$formvalues['sendername'].'-'.$messages['subject'].'-'. $receipient['email'].'-'.$receipient['name'].'-'.$messages['contents']);
							// $msgdetail->sendInboxEmailNotification($formvalues['senderemail'], $formvalues['sendername'], $messages['subject'], $receipient['email'], $receipient['name'], $messages['contents']);
						}
					}
				}
							
				if(count($messagedata) == 1){
					$key = current(array_keys($messagedata));
					$rcpt = $messagedata[$key]['name'];
					$sentmessage = "Message sent to ".$rcpt;
					$session->setVar(SUCCESS_MESSAGE, $sentmessage);
					
				} else { 
					$sentmessage = "Message successfully sent to <b>".count($messagedata)."</b> member(s). <br />See full list of recipient(s) at the bottom of this page.";
					$sentresult = createHTMLListFromArray($sent, 'successmsg alert alert-success');
					$session->setVar('sentlist', $sentresult);
					$session->setVar(SUCCESS_MESSAGE, "Message sent to ".count($messagedata)." members. <br />See full list of recipients at the bottom of this page.");
				}
				$execresult = array('result'=>'success', 'msg'=>$sentmessage);
			} catch (Exception $e) {
				$session->setVar(ERROR_MESSAGE, "An error occured in sending the message. ".$e->getMessage());
				$session->setVar(FORM_VALUES, $this->_getAllParams());
				$execresult = array('result'=>'success', 'msg'=>"An error occured in sending the message. ".$e->getMessage());
			}
		}
	    // exit;
	   	$this->_helper->redirector->gotoUrl(decode($formvalues[URL_SUCCESS]));
	}
}