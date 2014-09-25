<?php

class Privacy extends BaseRecord {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('privacy');
		$this->hasColumn('userid', 'integer', null);
		$this->hasColumn('personid', 'integer', null);
		$this->hasColumn('namesection', 'integer', null, array('default' => '3')); // 1. Public, 2. Subscriber, 3. Relatives, 4. Immediate Family, 5. Only me
		$this->hasColumn('familysection', 'integer', null, array('default' => '3'));
		$this->hasColumn('clansection', 'integer', null, array('default' => '3'));
		$this->hasColumn('personalsection', 'integer', null, array('default' => '3'));
		$this->hasColumn('emailaddresssection', 'integer', null, array('default' => '3'));
		$this->hasColumn('phonesection', 'integer', null, array('default' => '3'));
		$this->hasColumn('physicaladdresssection', 'integer', null, array('default' => '3'));
		$this->hasColumn('webaddresssection', 'integer', null, array('default' => '3'));
		$this->hasColumn('birthsection', 'integer', null, array('default' => '3'));
		$this->hasColumn('birthrule', 'integer', null, array('default' => '3'));
		$this->hasColumn('defaultprivacy', 'integer', null, array('default' => '3'));		
	}
	/**
	 * Contructor method for custom initialization
	 */
	public function construct() {
		parent::construct();
		
	}
	/**
	 * Model relationships
	 */
	public function setUp() {
		parent::setUp();
		
		$this->hasOne('Person as person', 
								array(
									'local' => 'personid',
									'foreign' => 'id',
								)
						);
		$this->hasOne('Member as user', 
								array(
									'local' => 'userid',
									'foreign' => 'id',
								)
						);
	}
	/**
	 * Preprocess model data
	 */
	function processPost($formvalues){
		// set default values for integers, dates, decimals
		if(isArrayKeyAnEmptyString('userid', $formvalues)){
			unset($formvalues['userid']); 
		}
		if(isArrayKeyAnEmptyString('personid', $formvalues)){
			unset($formvalues['personid']); 
		}
		if(isArrayKeyAnEmptyString('namesection', $formvalues)){
			unset($formvalues['namesection']); 
		}
		if(isArrayKeyAnEmptyString('familysection', $formvalues)){
			unset($formvalues['familysection']);
		}
		if(isArrayKeyAnEmptyString('clansection', $formvalues)){
			unset($formvalues['clansection']);
		}
		if(isArrayKeyAnEmptyString('personalsection', $formvalues)){
			unset($formvalues['personalsection']);
		}
		if(isArrayKeyAnEmptyString('emailaddresssection', $formvalues)){
			unset($formvalues['emailaddresssection']);
		}
		if(isArrayKeyAnEmptyString('phonesection', $formvalues)){
			unset($formvalues['phonesection']);
		}
		if(isArrayKeyAnEmptyString('physicaladdresssection', $formvalues)){
			unset($formvalues['physicaladdresssection']);
		}
		if(isArrayKeyAnEmptyString('webaddresssection', $formvalues)){
			unset($formvalues['webaddresssection']);
		}
		if(isArrayKeyAnEmptyString('birthsection', $formvalues)){
			unset($formvalues['birthsection']);
		}
		if(isArrayKeyAnEmptyString('birthrule', $formvalues)){
			unset($formvalues['birthrule']);
		}
		if(isArrayKeyAnEmptyString('defaultprivacy', $formvalues)){	
			unset($formvalues['defaultprivacy']);
		}	
		parent::processPost($formvalues);
	}
	# determine current privacy letter
    function getCurrentPrivacyLetter($value) {
    	$current = '';
    	switch ($value) {
    		case PRIVACY_1:
	    		$current = "P";
	    		break;
	    	case PRIVACY_2:
	    		$current = "S";
	    		break;
	    	case PRIVACY_3:
	    		$current = "R";
	    		break;
	    	case PRIVACY_4:
	    		$current = "F";
	    		break;
	    	case PRIVACY_5:
	    		$current = "M";
	    		break;
    		default:
    			break;
    	}
    	return $current;
    }
    # determine the current privacy text
	function getCurrentPrivacyText($value) {
    	$current = '';
    	switch ($value) {
    		case PRIVACY_1:
	    		$current = $this->translate->_("privacy_level_public_label");
	    		break;
	    	case PRIVACY_2:
	    		$current = $this->translate->_("privacy_level_subscriber_label");
	    		break;
	    	case PRIVACY_3:
	    		$current = $this->translate->_("privacy_level_relative_label");
	    		break;
	    	case PRIVACY_4:
	    		$current = $this->translate->_("privacy_level_family_label");
	    		break;
	    	case PRIVACY_5:
	    		$current = $this->translate->_("privacy_level_me_label");
	    		break;
    		default:
    			break;
    	}
    	return $current;
    }
	# determine if settings is the default privacy
	function getDefaultPrivacyClass($test) {
    	$class = '';
    	if($this->getDefaultPrivacy() == $test){
    		$class = 'activeprivacy';
    	}
    	return $class;
    }
	# determine if the default privacy should be selected or not
	function getDefaultPrivacyStatus($test) {
    	$class = '0';
    	if($this->getDefaultPrivacy() == $test){
    		$class = '1';
    	}
    	return $class;
    }
}
?>