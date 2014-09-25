<?php

/**
 * Model for a a category 
 *
 */
class Category extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		$this->setTableName('category');
		
		$this->hasColumn('type', 'integer', null, array('default' => 1)); 
		$this->hasColumn('name', 'string', 255, array('notnull' => true, 'notblank' => true));
		$this->hasColumn('description', 'string', 500);
		$this->hasColumn('alias', 'string', 500);
		$this->hasColumn('parentid', 'integer', null, array('default' => NULL)); 
		$this->hasColumn('sectorid', 'integer', null, array('default' => NULL)); 
		$this->hasColumn('value', 'string', 1000);
		
		$this->hasColumn('status', 'integer', null, array('default' => NULL)); 
		$this->hasColumn('sortorder', 'integer', null, array('default' => NULL)); 
		$this->hasColumn('level', 'integer', null, array('default' => NULL));
		$this->hasColumn('path', 'string', 500);
		$this->hasColumn('link', 'string', 500);
		$this->hasColumn('uneditable', 'integer', null, array('default' => NULL)); 
	}
	/**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		// set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"name.notblank" => $this->translate->_("global_name_error"),	
       									"name.length" => $this->translate->_("global_name_length_error")						
       	       						));
	}
	/*
	 * Relationships for the model
	 */
	public function setUp() {
		parent::setUp(); 
		$this->hasOne('Category as parent', 
								array(
									'local' => 'parentid',
									'foreign' => 'id'
								)
						);
		$this->hasOne('Sector as sector', 
								array(
									'local' => 'sectorid',
									'foreign' => 'id'
								)
						);
	}
	/*
	 * Pre process model data
	 */
	function processPost($formvalues) {
		$session = SessionWrapper::getInstance(); 
		// trim spaces from the name field
		if(isArrayKeyAnEmptyString('parentid', $formvalues)){
			unset($formvalues['parentid']); 
		}
		if(isArrayKeyAnEmptyString('sectorid', $formvalues)){
			unset($formvalues['sectorid']);
			if(!isEmptyString($formvalues['parentid'])){ 
				$category = new Category();
				$category->populate($formvalues['parentid']);
				$formvalues['sectorid'] = $category->getSectorID();
			} 
		}
		if(isArrayKeyAnEmptyString('level', $formvalues)){
			unset($formvalues['level']); 
		}
		if(isArrayKeyAnEmptyString('type', $formvalues)){
			unset($formvalues['type']); 
		}
		if(isArrayKeyAnEmptyString('status', $formvalues)){
			unset($formvalues['status']); 
		}
		if(isArrayKeyAnEmptyString('uneditable', $formvalues)){
			unset($formvalues['uneditable']); 
		}
		if(isArrayKeyAnEmptyString('sortorder', $formvalues)){
			if(!isEmptyString($formvalues['parentid']) && !isEmptyString($formvalues['sectorid'])){
				$formvalues['sortorder'] = $this->getNextSortOrder($formvalues['sectorid'], $formvalues['parentid']);
			}
		}
		// debugMessage($formvalues); exit();
		parent::processPost($formvalues);
	}
	/**
     * Overide  to save persons relationships
     *	@return true if saved, false otherwise
     */
    function afterSave(){
    	$session = SessionWrapper::getInstance();
    	$conn = Doctrine_Manager::connection();
    	$update = false;
    	
    	# save changes 
    	if($update){
    		$this->save();
    	}
    	
    	// find any duplicates and delete them
    	$duplicates = $this->getDuplicates();
		if($duplicates->count() > 0){
			$duplicates->delete();
		}
		
    	// exit();
    	return true;
    }
	# find duplicates after save
	function getDuplicates(){
		$q = Doctrine_Query::create()->from('Category c')->where("c.name = '".$this->getName()."' AND c.alias = '".$this->getAlias()."' AND c.type = '".$this->getType()."' AND c.parentid = '".$this->getParentID()."' AND c.sectorid = '".$this->getSectorID()."' AND c.id <> '".$this->getID()."' ");
		
		$result = $q->execute();
		return $result;
	}
	# sort order counter
	function getNextSortOrder($sector = 1, $type = 1){
		$conn = Doctrine_Manager::connection();
		$where_query = " WHERE id <> '' AND sectorid = '".$sector."' AND parentid = '".$type."' ";
		$query = "SELECT max(sortorder) FROM category ".$where_query;
		// debugMessage($query);
		$result = $conn->fetchOne($query);
		return $result+1;
	}
	# find category from position
	function findByPosition($pos, $type = 1, $sector = 1, $parentid = 1){
		$q = Doctrine_Query::create()->from('Category c')
		->where('c.sortorder = ?', $pos)
		->andWhere('c.sectorid = ?', $sector)
		->andWhere('c.parentid = ?', $parentid)
		->andWhere('c.type = ?', $type);
		$result = $q->fetchOne(); 
		return $result;
	}
	# determine the articles in a category and enterprise
	function getArticles($sectorid, $entid, $typeid = '', $isleading = false){
		$custom_query = '';
		if(!isEmptyString($typeid)){
			$custom_query .= " AND c.typeid = '".$typeid."' ";
		}
		if($isleading){
			$custom_query .= " AND c.isfeatured = 1 ";
		}
		$q = Doctrine_Query::create()->from('Content c')->where("c.sectorid = '".$sectorid."' AND c.enterpriseid = '".$entid."' ".$custom_query)->orderBy("c.sortorder asc, c.id asc");
		
		$result = $q->execute();
		return $result;
	}
	# fetch random categories
	function fetchRandom($limit = 1, $parentid = 1){
		$q = Doctrine_Query::create()
		->from('Category c')
		->where("c.parentid = 1 AND (c.alias <> 'coffee' AND c.alias <> 'soyabean') ")
		->limit(1)->orderBy('rand()');
		return $q->execute();
	}
	# determine the sub categories for a category
	function getTopLevelCategories() {
		$q = Doctrine_Query::create()
		->from('Category b')
		->where("b.parentid = ?", 110)
		// ->andWhere("b.level = 1")
		->andWhere("b.status = 1")
		->orderby("b.sortorder ASC");
		return $q->execute();
	}
	# determine the sub categories for a category
	function getLevelTwoCategories() {
		$q = Doctrine_Query::create()
		->from('Category b')
		->where("b.parentid = ?", $this->getID())
		// ->andWhere("b.level = 2")
		->andWhere("b.status = 1")
		->orderby("b.sortorder ASC");
		return $q->execute();
	}
	# determine the contacts for a category
	function getContacts() {
		$q = Doctrine_Query::create()
		->from('Contact c')
		->where("c.categoryid = ?", $this->getID())
		->andWhere("c.status = 3 ")
		//->andWhere("c.sampledata <> 1")
		->orderby("c.orgname ASC");
		return $q->execute();
	}
	# find by name
	function findIDFromName($category){
		$q = Doctrine_Query::create()
		->from('Category c')
		->where("c.name = '".$category."' AND c.parentid = 110 ");
		$result = $q->execute();
		if(!$result){
			return new Category();
		}
		return $result->get(0);
	}
}
?>