<?php

class AppConfig extends BaseEntity {
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('appconfig');
		$this->hasColumn('section', 'string', 50);
		$this->hasColumn('sectiondisplay', 'string', 50);
		$this->hasColumn('description', 'string', 255);
		$this->hasColumn('optionname', 'string', 50);
		$this->hasColumn('optiontype', 'string', 255);
		$this->hasColumn('optionvalue', 'string', 50);
		$this->hasColumn('displayname', 'string', 50);
		$this->hasColumn('active', 'enum', array('values' => array(0 => 'Y', 1 => 'N'), 'default' => 'Y'));
		$this->hasColumn('editable', 'interger', null, array('default' => '1'));
	}
	/**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		// set the custom error messages
       	$this->addCustomErrorMessages(array(
       									/*"optionname.notblank" => $this->translate->_("appconfig_optionname_error"),								
       									"optionvalue.unique" => $this->translate->_("appconfig_optionvalue _error"),
       									"section.notblank" => $this->translate->_("appconfig_section_error")*/
       	       						));
	}
	/*
	 * Process object
	 */
	function processPost($formvalues){
		// check if the active is not specified and set to default value
		if(isArrayKeyAnEmptyString('active', $formvalues)) {
			unset($formvalues['active']);
		}
		if(isArrayKeyAnEmptyString('editable', $formvalues)) {
			unset($formvalues['editable']);
		}
		# debugMessage($formvalues);
		parent::processPost($formvalues);
	}
	/*
	 * Invalidate the cached application configuration
	 */
	function afterUpdate() {
		$cache = Zend_Registry::get('cache');
		$cache->remove('config');
		return true;
	}
	function getCurrentSection($section){
		$q = Doctrine_Query::create()->from('Appconfig a')->where("a.section = '".$section."' ");
		
		$result = $q->execute();
		return $result->get(0);
	}
	function getConfigByName($name){
		$q = Doctrine_Query::create()->from('Appconfig a')->where("a.optionname = '".$name."' ");
		
		$result = $q->execute();
		return $result->get(0);
	}
	function getOptions($section){
		$q = Doctrine_Query::create()->from('Appconfig a')->where("a.section = '".$section."' ")->orderby("a.id");
		
		$result = $q->execute();
		return $result;
	}
	function isEditable(){
		return $this->getEditable() == 1 ? true : false;
	}
	function getLogo(){
		return $this->getDescription();
	}
	# determine if person has profile image
	function hasLogo(){
		$real_path = APPLICATION_PATH."/../public/uploads/system/logo/".$this->getLogo();
		$real_path2 = APPLICATION_PATH."/../public/uploads/system/logo/large_".$this->getLogo();
		if((file_exists($real_path) || file_exists($real_path2)) && !isEmptyString($this->getLogo())){
			return true;
		}
		return false;
	}
	# determine path to medium profile picture
	function getLogoPath() {
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$path = $baseUrl.'/uploads/system/default_logo.png';
		if($this->hasLogo()){
			$path = $baseUrl.'/uploads/system/logo/'.$this->getLogo();
		}
		return $path;
	}
	# determine path to large profile picture
	function getLargeLogoPath() {
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$path = $baseUrl.'/uploads/system/default_logo.png';
		if($this->hasLogo()){
			$path = $baseUrl.'/uploads/system/logo/large_'.$this->getLogo();
		}
		// debugMessage($path);
		return $path;
	}
}
?>
