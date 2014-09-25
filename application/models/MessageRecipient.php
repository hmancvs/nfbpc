<?php

class MessageRecipient extends BaseRecord  {
	public function setTableDefinition() {
		parent::setTableDefinition();
		$this->setTableName('messagerecipient');
		$this->hasColumn('id', 'integer', 11, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('messageid', 'integer', 11, array("notblank" => true, "notnull" => true));
		$this->hasColumn('recipientid', 'integer', 11, array("notblank" => true, "notnull" => true));
		$this->hasColumn('isread', 'integer', 11, array("default" =>'0'));
	}
	
	public function setUp() {
		parent::setUp(); 
		$this->hasOne('Message as message',
							array('local' => 'messageid',
									'foreign' => 'id'
							)
						);	
		$this->hasOne('UserAccount as recipient',
							array('local' => 'recipientid',
									'foreign' => 'id'
							)
						);	
	}
	
   /**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		
		// set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"messageid.notblank" => $this->translate->_("message_messageid_error"),
       									"recipientid.notblank" => $this->translate->_("message_recipientid_error")
       	       						));
	}
	/*
	 * Custom model validation
	 */
	function validate() {
		// execute the column validation 
		parent::validate();
		
		// custom validatio for unique messageid and recipientid combination

	}
	/*
	 * Pre process model data
	 */
	function processPost($formvalues){
		# force setting of default none string column values. enum, int and date 	
		if(isArrayKeyAnEmptyString('isread', $formvalues)){
			$formvalues['isread'] = '0'; 
		}
		parent::processPost($formvalues);
	}
	/**
	 * 
	 * Determine if message has been read by the recipient
	 * @return Bool True if read and false otherwise
	 */
	function hasReadMessage() {
		if($this->getIsRead()== '0'){
			return false;
		} else {
			return true;
		}
	}
	/**
	 * Mark selected messages has read
	 * @param $msgdetailids an array of message recipient ids to be marked as read 
	 * @return Boolean True if successfull, false otherwise
	 */
	function markAsRead($msgdetailids, $flag) {
		$q = Doctrine_Query::create()->update('MessageRecipient')->set('isread', $flag)->whereIn('id', $msgdetailids);
			
		// $result = $q->execute(); 
		return $q->execute();
	}
	/**
	 * Calculate the number of unread messages currently availble
	 * @param $userid Integer the user's id
	 * @return Integer The number of unread messages  
	 */
	function countUnReadMessages($userid) {
		# query active user details using email
		$q = Doctrine_Query::create()
				->select('SUM(IF(mr.isread = 0, 1 , 0)) as total')
				->from('MessageRecipient mr')
				->where('mr.isread = ?', '0')
				->andWhere('mr.recipientid = ?', $userid)
				->groupBy('mr.recipientid');
				
		$result = $q->fetchOne();
		
		return isEmptyString($result['total']) ? '0' : $result['total'];
	}
}