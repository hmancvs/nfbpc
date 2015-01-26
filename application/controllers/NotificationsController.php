<?php

class NotificationsController extends IndexController  {
	
	function getResourceForACL() {
		// return "Message";
		return "Notifications"; 
	}
	/**
	 * Get the name of the resource being accessed 
	 *
	 * @return String 
	 */
	function getActionforACL() {
		$action = strtolower($this->getRequest()->getActionName()); 
		
		if ($action == "markasread" || $action == "processmassmail" || $action == "processnotification" || 
			$action == "processreply" || $action == "subscriber" || $action == "invite"
		) {
			return ACTION_VIEW; 
		}
		if ($action == "sent" || $action == "sentsearch" ||  $action == "massmail" || $action == "subscriber" || $action == "invite") {
			return ACTION_LIST; 
		}
		return parent::getActionforACL(); 
	}
	
    public function markasreadAction(){
		// disable rendering of the view and layout so that we can just echo the AJAX output 
	    $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$session = SessionWrapper::getInstance(); 
		// debugMessage($this->_getAllParams());
		// user is deleting more than one message
		$idsarray = array();
		
		$idsarray = $this->_getParam("messagesformarking");
		// remove all empty keys in the array of ids to be deleted
		foreach ($idsarray as $key => $value){
			if(isEmptyString($value)){
				unset($idsarray[$key]);
			}
		}
		// debugMessage($idsarray);
		$messagerecipient = new MessageRecipient();
		// mark selected message details using selected mark action
   		$messagerecipient->markAsRead($idsarray, $this->_getParam("markaction"));
		// debugMessage("Message(s) were successfully marked");
		// fetch number of remaining unread messages for the user 
		$remaining = $messagerecipient->countUnReadMessages($session->getVar('userid'));		
		$session->setVar('unread', $remaining);
		// if no unread messages hide unread label else show unread in brackets
		if($remaining == '0'){
			$newmsghtml = '<a title="Messages" href="'.$this->_helper->url('list', 'message').'"><img src="'.$this->_helper->url('email.png', 'images').'">Messages</a>';		
		} else {
			$newmsghtml = '<a title="Messages" href="'.$this->_helper->url('list', 'message').'"><img src="'.$this->_helper->url('email.png', 'images').'">Messages (<label class="unread">'.$session->getVar('unread').' Unread</label>)</a>';	
		}
		
		$session->setVar('newmsghtml', $newmsghtml);
		echo $session->getVar('newmsghtml');
		
		return false;
	}
	
	function deleteAction(){		
		// disable rendering of the view and layout so that we can just echo the AJAX output 
	    $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		if($this->_getParam("deletemultiple") == '1'){
			// debugMessage($this->_getAllParams());
			// user is deleting more than one message
			$idsarray = $this->_getParam("messagesfordelete");
			// remove all empty keys in the array of ids to be deleted
			foreach ($idsarray as $key => $value){
				if(isEmptyString($value)){
					unset($idsarray[$key]);
				}
			}
			// debugMessage($idsarray);
			$message = new MessageRecipient();
			if($message->deleteMultiple($idsarray)){
				debugMessage("Message(s) were successfully deleted");
			} else {
				debugMessage("An error occured in deleting your message(s)");
			}
			
		} else {
			// user is deleting only one message from reply page
			// the messageid being deleted
			$msgid = $this->_getParam("idfordelete");
			// debugMessage($this->_getAllParams());
			
			// populate message to be deleted
			$message = new Message();
			$message->populate($msgid);
			
			if($message->delete()){
				debugMessage("Message was successfully deleted");
			} else {
				debugMessage("An error occured in deleting your message");
			}
		}
		return false;
	}
	
	function processnotificationAction(){
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	
		$session = SessionWrapper::getInstance(); 	
	    $config = Zend_Registry::get("config");
	    $message_collection = new Doctrine_Collection(Doctrine_Core::getTable("Message"));
	    
	    $formvalues = $this->_getAllParams(); // debugMessage($formvalues); exit;
	    $recipients_array = array(); 
	    $messagedata = array(); 
	    $users = array();
	    $execresult = array('result'=>'', 'msg'=>'');
	    
	    $type = $formvalues['type'];
	    if($type == 1){
	    	$ismail = true; $issms = false;
	    }
	    if($type == 2){
	    	$issms = true; $ismail = false;
	    }
	    if(!isArrayKeyAnEmptyString('memberid', $formvalues)){
	    	$formvalues['selecttype'] = 1;
	    }
	    $custom_query = "";
	    
	    if($formvalues['selecttype'] == 1 || $formvalues['selecttype'] == 2){
	    	if(!isArrayKeyAnEmptyString('memberids', $formvalues)){
	    		$users = $formvalues['memberids'];
	    	}
	    	if(!isArrayKeyAnEmptyString('userids', $formvalues)){
	    		$users = $formvalues['userids'];
	    	}
	    	# check for other filters for members/users
	    	if(isArrayKeyAnEmptyString('memberids', $formvalues) && isArrayKeyAnEmptyString('userids', $formvalues)){
	    		if(!isArrayKeyAnEmptyString('committeeid', $formvalues)){
	    			$custom_query .= " AND a.committeeid = '".$formvalues['committeeid']."' ";
	    		}
	    		if(!isArrayKeyAnEmptyString('positionid', $formvalues)){
	    			$custom_query .= " AND a.positionid = '".$formvalues['positionid']."' ";
	    		}
	    		if(!isArrayKeyAnEmptyString('regionid', $formvalues)){
	    			$custom_query .= " AND m.regionid = '".$formvalues['regionid']."' ";
	    		}
	    		if(!isArrayKeyAnEmptyString('provinceid', $formvalues)){
	    			$custom_query .= " AND m.provinceid = '".$formvalues['provinceid']."' ";
	    		}
	    		if(!isArrayKeyAnEmptyString('districtid', $formvalues)){
	    			$custom_query .= " AND m.districtid = '".$formvalues['districtid']."' ";
	    		}
	    		if(!isArrayKeyAnEmptyString('organisationid', $formvalues)){
	    			$custom_query .= " AND m.organisationid = '".$formvalues['organisationid']."' ";
	    		}
	    		if(!isEmptyString(trim($custom_query))){
	    			# debugMessage('query is '.$custom_query);
	    			$emailopt = false; $phoneopt  = false;
	    			if($ismail){
	    				$emailopt = true;
	    			}
	    			if($issms){
	    				$phoneopt = true;
	    			}
	    			$users = getUsers('', '', '', '', $custom_query, $emailopt, $phoneopt);
	    		}
	    	}
	    	if(!isArrayKeyAnEmptyString('memberid', $formvalues)){
	    		$users = array($formvalues['memberid']);
	    	}
	    }
	    if($formvalues['selecttype'] == 3){
	    	if($ismail){
	    		$users = getMembersWithEmail();
	    	}
	    	if($issms){
	    		$users = getMembersWithPhone();
	    	}
	    }
	    if($formvalues['selecttype'] == 4){
	    	if($ismail){
	    		$users = getMembersWithEmail(true);
	    	}
	    	if($issms){
	    		$users = getMembersWithPhone(true);
	    	}
	    }
	   	//debugMessage($users); exit;
	    # if no receipients specified
	    if(count($users) == 0){
	    	$session->setVar(ERROR_MESSAGE, "Error: No Receipients specified!");
	    	// $this->_helper->redirector->gotoUrl(decode($formvalues[URL_SUCCESS]));
	    	$execresult = array('result'=>'fail', 'msg'=>"Error: No Receipients specified!");
	    }
		
	    $messages = array(); $sent = array(); $phones = array();
	    $messages['contents'] = $formvalues['contents'];
	    $messages['type'] = $formvalues['type'];
	    if(!isArrayKeyAnEmptyString('subject', $formvalues)){
	    	$messages['subject'] = $formvalues['subject'];
	    } else {
	    	$messages['subject'] = '';
	    }
		$messages['senderid'] = 0;
		if(!isArrayKeyAnEmptyString('senderid', $formvalues)){
			$messages['senderid'] = $formvalues['senderid'];
		}
		if(!isArrayKeyAnEmptyString('senderemail', $formvalues) && isEmptyString($session->getVar('userid'))){
			$messages['senderemail'] = $formvalues['senderemail'];
		}
		if(!isArrayKeyAnEmptyString('sendername', $formvalues) && isEmptyString($session->getVar('userid'))){
			$messages['sendername'] = $formvalues['sendername'];
		}
		# process receipients depending on select type
		foreach ($users as $key => $userid){
			$memb = new Member();
			$id = '';
			if($formvalues['selecttype'] == 1 || $formvalues['selecttype'] == 2){
				$id = $userid;
			}
			if($formvalues['selecttype'] == 3 || $formvalues['selecttype'] == 4 || !isEmptyString(trim($custom_query))){
				$id = $key;
			}
			$memb->populate($id); // debugMessage($memb->toArray());
			if($memb->isUser()){
				$recipients_array[$id]['recipientid'] = $memb->getID();
			}
			$messagedata[$id]['id'] = $memb->getID();
			$messagedata[$id]['name'] = $memb->getName();
			$messagedata[$id]['email'] = $memb->getEmail();
			$messagedata[$id]['phone'] = $memb->getPhone();
			if($ismail){
				$sent[] = $memb->getName().' ('.$memb->getEmail().')';
			}
			if($issms){
				$sent[] = $memb->getName().' ('.$memb->getPhone().')';
				$phones[] = $memb->getPhone();
			}
		}
		$messages['recipients'] = $recipients_array;
		$messages['membertotal'] = count($messagedata);
		$messages['usertotal'] = count($recipients_array);
		// debugMessage($sent); 
		// debugMessage($messagedata); 
			
		$msg = new Message();
		$msg->processPost($messages);
		// debugMessage($msg->toArray());
		// debugMessage('error is '.$msg->getErrorStackAsString()); exit();
		// save the messages to system inbox
		if($msg->hasError()){
			$session->setVar(ERROR_MESSAGE, "Error: ".$msg->getErrorStackAsString());
			$session->setVar(FORM_VALUES, $this->_getAllParams());
			// $this->_helper->redirector->gotoUrl(decode($formvalues[URL_SUCCESS]));
			$execresult = array('result'=>'fail', 'msg'=>"Error: ".$msg->getErrorStackAsString());
		} else {
			try {
				$msg->save();
				// send message to emails
				if(count($messagedata) > 0){
					foreach($messagedata as $key => $receipient){
						$msgdetail = new MessageRecipient();
						if(!isArrayKeyAnEmptyString('email', $receipient)){
							// debugMessage($formvalues['senderemail'].'-'.$formvalues['sendername'].'-'.$messages['subject'].'-'. $receipient['email'].'-'.$receipient['name'].'-'.$messages['contents']);
							$msgdetail->sendInboxEmailNotification($formvalues['senderemail'], $formvalues['sendername'], $messages['subject'], $receipient['email'], $receipient['name'], $messages['contents']);
						}
					}
				}
				
				// send message to phones
				if(count($phones) > 0){
					$messagechuncks = array_chunk($messagedata, 100, true);
					if(count($messagedata) <= 100){
						$phonelist = implode(',',$phones);
						$result = sendSMSMessage($phonelist, $messages['contents'], '', $msg->getID());
						// debugMessage($result); exit;
					} else {
						foreach ($messagechuncks as $key => $messagegrp){
							$phones_temp_array = array();
							foreach ($messagegrp as $keynest => $messageline) {
								$phones_temp_array[] = $messageline['phone'];
							}
							$phonelist = implode(',',$phones_temp_array);
							$result = sendSMSMessage($phonelist, $messages['contents'], '', $msg->getID());
							// debugMessage($result);
						}
					}
				}
				
				if(count($messagedata) == 1){
					$key = current(array_keys($messagedata));
					if($ismail){
						$rcpt = $messagedata[$key]['name'].' ('.$messagedata[$key]['email'].')';
						$sentmessage = "Message sent to ".$rcpt;
						$session->setVar(SUCCESS_MESSAGE, $sentmessage);
					}
					if($issms){
						$rcpt = $messagedata[$key]['name'].' ('.$messagedata[$key]['phone'].')';
						$sentmessage = "Message sent to ".$rcpt;
						$session->setVar(SUCCESS_MESSAGE, $sentmessage);
					}
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
	   	// $this->_helper->redirector->gotoUrl(decode($formvalues[URL_SUCCESS]));
	   	echo json_encode($execresult);
    }
    
	function processreplyAction(){
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	
		$session = SessionWrapper::getInstance(); 	
	    $config = Zend_Registry::get("config");
		
	    $formvalues = $this->_getAllParams(); debugMessage($formvalues);
	    $messages = array();
		$messages['senderid'] = $formvalues['senderid'];
		$messages['parentid'] = $formvalues['parentid'];
		$messages['subject'] = $formvalues['subject'];
		$messages['contents'] = $formvalues['contents'];
		$recipients_array = array(); $users = array();
		$users = $formvalues['recipientids'];
		foreach ($users as $userid){
			$recipients_array[$userid]['recipientid'] = $userid;
		}
		
		$messages['recipients'] = $recipients_array;
		// debugMessage($messages); 
		
		$msg = new Message();
		$msg->processPost($messages);
		/*debugMessage($msg->toArray());
		debugMessage('error is '.$msg->getErrorStackAsString()); exit();*/
		// save the messages to system inbox
		if($msg->hasError()){
			$session->setVar(ERROR_MESSAGE, "An error occured in sending the message. ".$msg->getErrorStackAsString());
		} else {
			try {
				$msg->save();
				// copy message to recepient's email of specified  / required for admin contact
				$messagereceipients = $msg->getRecipients();
				if($this->_getParam('copytoemail') == 1){
					foreach ($messagereceipients as $messageuser){
						if(!isEmptyString($messageuser->getRecipient()->getEmail())){
							$messageuser->sendInboxEmailNotification();
						}
					}
				}
				if($this->_getParam('copytophone') == 1){
					foreach ($messagereceipients as $messageuser){
						if(!isEmptyString($messageuser->getRecipient()->getPhone())){
							# check if user has phone number on profile
							$messageuser->sendSmsNotification();
						}
					}
				}
				// copy message to user's phone if specified
				$session->setVar(SUCCESS_MESSAGE, "Message successfully replied. ");
			} catch (Exception $e) {
				$session->setVar(ERROR_MESSAGE, "An error occured in sending the message. ".$e->getMessage());
			}
		}
	    
	   	$this->_helper->redirector->gotoUrl(decode($formvalues[URL_SUCCESS]));
	    // exit();
    }
}