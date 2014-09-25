<?php

class Message extends BaseRecord  {
	public function setTableDefinition() {
		parent::setTableDefinition();
		$this->setTableName('message');
		$this->hasColumn('id', 'integer', null, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('parentid', 'integer', null);
		$this->hasColumn('senderid', 'integer', null, array("notblank" => true, "notnull" => true));
		$this->hasColumn('sendername', 'string', 255);
		$this->hasColumn('senderemail', 'string', 255);
		$this->hasColumn('contents', 'string', 65535, array("notblank" => true, "notnull" => true));
		$this->hasColumn('subject', 'string', 255);
		$this->hasColumn('html', 'string', 1000);
		$this->hasColumn('datecreated', 'timestamp');
		$this->hasColumn('isoutbox', 'integer', null, array('default'=> '0'));
	}
	
	public function setUp() {
		parent::setUp(); 
		// automatically set timestamp on datecreated
		$this->actAs('Timestampable', 
						array('created' => array(
												'name' => 'datecreated',    
											),
							 'updated' => array(
												'name'     =>  'datecreated',    
												'onInsert' => false,
												'options'  =>  array('notnull' => false)
											)
						)
					);
					
		$this->hasOne('Message as parent',
							array('local' => 'parentid',
									'foreign' => 'id'
							)
						);	
		$this->hasOne('UserAccount as member',
							array('local' => 'senderid',
									'foreign' => 'id'
							)
						);	
		$this->hasMany('MessageRecipient as recipients',
								array('local' => 'id',
									  'foreign' => 'messageid'
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
       									"senderid.notblank" => $this->translate->_("message_sender_error"),
       									"contents.notblank" => $this->translate->_("message_contents_error")       									
       	       						));
	}
	/**
	 * Pre process model data
	 */
	function processPost($formvalues){
		# force setting of default none string column values. enum, int and date 	
		if(isArrayKeyAnEmptyString('parentid', $formvalues)){
			unset($formvalues['parentid']); 
		}
		if(isArrayKeyAnEmptyString('isoutbox', $formvalues)){
			unset($formvalues['isoutbox']); 
		}
		parent::processPost($formvalues);
	}
	/**
	 * Return all message recipients as a comma separated list 
	 */
	function getAllMessageRecipients() {
		$names_array = array(); 
		$recipients = $this->getRecipients();
		foreach($recipients as $recipient){
			$names_array[] = $recipient->getRecipient()->getName();
		} 
		
		return implode(', ', $names_array);
	}
	/**
	 * Return the system generated id of the message to which this message is a reply or the id of the current message if there is no parent
	 * @return Array
	 */
	function getCurrentParentID() {
		if(isEmptyString($this->getParentID())){
			return $this->getID();
		} else {
			return $this->getParentID();
		}
	}
	/**
	 * Generate an array containing the system generated ids of the recipients of the message
	 * @return Array
	 */
	function getRecipientIds() {
		$ids = array();
		$recipients = $this->getRecipients();
		foreach ($recipients as $recipient){
			$ids[$recipient->getRecipientID()] = $recipient->getRecipientID();
		}
		return $ids;
	}
	/**
	 * Generate a messages collection containing all messages exchanged between the message sender and the recipient
	 * @return 
	 */		
	function getMessageHistory() {
		// Use doctrine raw sql 
		$q = new Doctrine_RawSql();
        $q->select('{m.*}, {mr.*}');
        $q->from('message m INNER JOIN messagerecipient mr ON (m.id = mr.messageid AND ISNULL(m.commentid))');
		$q->where("(m.senderid = '".$this->getSenderID()."' AND mr.recipientid = '".$this->getRecipient()->getID()."') OR 
					(m.senderid = '".$this->getRecipient()->getID()."' AND mr.recipientid = '".$this->getSenderID()."') ORDER BY m.datecreated ASC ");
		$q->addComponent('m', 'Message m');
		$q->addComponent('mr', 'm.recipients mr');
		
		$result = $q->execute();
		// debugMessage($result->toArray()); 
		return $result;
	}
	/**
	 * 
	 * Return a single object for the messagerecipient details 
	 */
	function getMessageDetails() {
		# query active user details using email
		$q = Doctrine_Query::create()->from('MessageRecipient r')->where('r.messageid = ?', $this->getID());
		// debugMessage($q->fetchOne()->toArray());
		return $q->fetchOne();
	}
	/**
	 * 
	 * Return a single object for the recipient user
	 */
	function getRecipient() {
		return $this->getMessageDetails()->getRecipient();
	}
	/**
	 * Send new message notifications 
	 * @return true 
	 */
	function afterSave() {
		// send message email notification if recipient has allowed under account settings
		if($this->getRecipient()->getEmailMeOnMessage() == '1'){
			$this->sendInboxEmailNotification();
		}
		
		return true;
	}
	/**
	 * Send a notification to a user that a private message has been sent to them
	 * 
	 * @return Bool whether the email notification has been sent
	 *
	 */
	function sendInboxEmailNotification($fromemail, $fromname, $subject = '') {
		$template = new EmailTemplate(); 
		# create mail object
		$mail = getMailInstance(); 
		
		$sendername = $this->getSender()->getName();
		if(!isEmptyString($fromname)){
			$sendername = $fromname;
		}
		$senderemail = $this->config->notification->emailmessagesender;
		if(!isEmptyString($fromemail)){
			$senderemail = $fromemail;
		}
		
		// debugMessage($this->getRecipients()->toArray());
		// the message reciever's first name
		$template->assign('firstname', $this->getRecipient()->getFirstName());
		// the message sender's name
		$template->assign('emailsender', $sendername);	
		
		$subject = $this->getSubject();
		if(isEmptyString($this->getSubject())){
			$subject = sprintf($this->translate->_('message_private_email_subject'), $sendername);
		}
		// message subject
		$mail->setSubject($subject);
		// message introduction
		$template->assign('emailintro', sprintf($this->translate->_('message_private_email_subject'), $sendername));
		// message contents 
		$template->assign('emailcontent', nl2br($this->getContents()));			
		// the actual url will be built in the view 
		$template->assign('emaillink', array("controller"=> "message", "action"=> "reply", "id" => encode($this->getID())));
		// message html file
		$mail->setBodyHtml($template->render('messagenotification.phtml'));
		// debugMessage($template->render('messagenotification.phtml'));
		
		// add the recipient emails TODO if sent to many users, add all their emails
		$mail->addTo($this->getRecipient()->getEmail());
		// $mail->addCc('hman@devmail.infomacorp.com');
		// set the send of the email address
		$mail->setFrom($senderemail, $this->translate->_('appname'));
		// send the message
		
		$mail->send();
		$mail->clearRecipients();
		$mail->clearSubject();
		$mail->setBodyHtml('');
		$mail->clearFrom();
		
		return true;
	}
	/**
	 * Delete multiple message
	 * @return Bool whether message is deleted false otherwise
	 */
	function deleteMultiple($idsarray) {		
		// doctrine query to select all messages to be deleted
		$q = Doctrine_Query::create()->from('Message m')->whereIn('m.id', $idsarray);
		// execute query
		$result = $q->execute();
		
		//debugMessage($result->toArray());
		return $result->delete();
	}
}