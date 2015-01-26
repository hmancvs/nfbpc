<?php

class DashboardController extends SecureController  {
	
	public function init()    {
		parent::init();
		$current_timestamp = strtotime('now'); $now_iso = date('Y-m-d H:i:s', $current_timestamp); $this->view->now_iso = $now_iso; //debugMessage('now '.$now_iso.'-'.$current_timestamp); 
		$onehourago_timestamp = strtotime('-1 hour'); $onehourago_iso = date('Y-m-d H:i:s', $onehourago_timestamp ); 
		$this->view->onehourago_iso = $onehourago_iso; $this->view->onehourago_timestamp = $onehourago_timestamp;// debugMessage('now '.$onehourago_iso.'-'.$onehourago_timestamp); 
		$sixhourago_timestamp = strtotime('-6 hour'); $sixhourago_iso = date('Y-m-d H:i:s', $sixhourago_timestamp); 
		$this->view->sixhourago_iso = $sixhourago_iso; $this->view->sixhourago_timestamp = $sixhourago_timestamp;
		$twelvehourago_timestamp = strtotime('-12 hour'); $twelvehourago_iso = date('Y-m-d H:i:s', $twelvehourago_timestamp); 
		$this->view->twelvehourago_timestamp = $twelvehourago_timestamp; $this->view->twelvehourago_iso = $twelvehourago_iso; 
		
		// debugMessage($logged_today_sql);
		$today_iso = date('Y-m-d'); $today = changeMySQLDateToPageFormat($today_iso);  $this->view->today_iso = $today_iso; //debugMessage('today '.$today_iso); 
		$yestday_iso = date('Y-m-d', strtotime('1 day ago')); $yestday = changeMySQLDateToPageFormat($yestday_iso); $this->view->yestday_iso = $yestday_iso; //debugMessage('yesterday '.$yestday_iso); 
		$weekday = date("N");
		
		// monday of week
		$mondaythisweek_iso = date('Y-m-d', strtotime('monday this week')); $mondaythisweek = changeMySQLDateToPageFormat($mondaythisweek_iso); 
		if($weekday == 1){
			$mondaythisweek_iso = $today_iso;
			$mondaythisweek = $today;
		}
		if($weekday == 7){
			$mondaythisweek_iso = date('Y-m-d', strtotime('monday last week'));
			$mondaythisweek = changeMySQLDateToPageFormat($mondaythisweek_iso);
		}
		$this->view->mondaythisweek_iso = $mondaythisweek_iso; //debugMessage('monday this week '.$mondaythisweek_iso);
		
		// sunday of week
		$sundaythisweek_iso = date('Y-m-d', strtotime('sunday this week')); $sundaythisweek = changeMySQLDateToPageFormat($sundaythisweek_iso);
		if($weekday == 1){
			$sundaythisweek_iso = date('Y-m-d', strtotime('today + 7 days')); $sundaythisweek = changeMySQLDateToPageFormat($sundaythisweek_iso);
		}
		if($weekday == 7){
			$sundaythisweek_iso = $today_iso; $sundaythisweek = $today;
		}
		$this->view->sundaythisweek_iso = $sundaythisweek_iso; // debugMessage('sunday this week '.$sundaythisweek_iso);
		
		// monday last week
		$mondaylastweek_iso = date('Y-m-d', strtotime('-7 days', strtotime($mondaythisweek_iso))); //debugMessage('monday last week '.$mondaylastweek_iso);
		$this->view->mondaylastweek_iso = $mondaylastweek_iso;
		// sunday last week
		$sundaylastweek_iso = date('Y-m-d', strtotime('-7 days', strtotime($sundaythisweek_iso))); // debugMessage('sunday last week '.$sundaylastweek_iso);
		$this->view->sundaylastweek_iso = $sundaylastweek_iso;
		// firstday this month
		$firstdayofthismonth_iso = getFirstDayOfCurrentMonth(); //debugMessage('1st day this month '.$firstdayofthismonth_iso);
		$this->view->firstdayofthismonth_iso = $firstdayofthismonth_iso;
		// lastday this month 
		$lastdayofthismonth_iso = getLastDayOfCurrentMonth(); //debugMessage('last day this month '.$lastdayofthismonth_iso);
		$this->view->lastdayofthismonth_iso = $lastdayofthismonth_iso;
		// firstday last month
		$firstdayoflastmonth_iso = getFirstDayOfMonth(date('m')-1, date('Y')); //debugMessage('1st day last month '.$firstdayoflastmonth_iso);
		$this->view->firstdayoflastmonth_iso = $firstdayoflastmonth_iso;
		// lastday last month 
		$lastdayoflastmonth_iso = getLastDayOfMonth(date('m')-1, date('Y')); //debugMessage('last day last month '.$lastdayoflastmonth_iso);
		$this->view->lastdayoflastmonth_iso = $lastdayoflastmonth_iso;
		// firstday this year 
		$firstdayofyear_iso = getFirstDayOfMonth(1, date('Y')); //debugMessage('1st day this year '.$firstdayofyear_iso);
		$this->view->firstdayofyear_iso = $firstdayofyear_iso;
		// lastday this year
		$lastdayofyear_iso = getLastDayOfMonth(12, date('Y')); //debugMessage('last day this year '.$lastdayofyear_iso);
		$this->view->lastdayofyear_iso = $lastdayofyear_iso;
		// first day of month one year ago
		$startofmonth_oneyearago = getFirstDayOfMonth(date('m', strtotime('1 year ago')), date('Y', strtotime('1 year ago')));
		$this->view->startofmonth_oneyearago = $startofmonth_oneyearago;
		
		$firstsystemday_iso = '2013-01-01';
		$this->view->firstsystemday_iso = $firstsystemday_iso;
	}
	/**
	 * @see SecureController::getActionforACL()
	 * 
	 * The dashboard can only be viewed, however the default is create for the index.phtml file. 
	 *
	 * @return String
	 */
	function getActionforACL() {
		return ACTION_VIEW; 
	}
	function membersstatsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$conn = Doctrine_Manager::connection();
		$session = SessionWrapper::getInstance();
	
		$query = "SELECT
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->onehourago_timestamp.", 1, 0)) as onehourago,
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->sixhourago_timestamp.", 1, 0)) as sixhourago,
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->twelvehourago_timestamp.", 1, 0)) as twelvehourago,
		SUM(IF(TO_DAYS(c.datecreated) = TO_DAYS('".$this->view->today_iso."'), 1, 0)) as today,
		SUM(IF(TO_DAYS(c.datecreated) = TO_DAYS('".$this->view->yestday_iso."'), 1, 0)) as yesterday,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->mondaythisweek_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->sundaythisweek_iso."'), 1, 0)) as thisweek,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->mondaylastweek_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->sundaylastweek_iso."'), 1, 0)) as lastweek ,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayofthismonth_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayofthismonth_iso."'), 1, 0)) as thismonth,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayoflastmonth_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayoflastmonth_iso."'), 1, 0)) as lastmonth,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayofyear_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayofyear_iso."'), 1, 0)) as thisyear,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstsystemday_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->today_iso."'), 1, 0)) as allentries
		FROM member AS c
		where c.id <> '' ";
		// debugMessage($query);
		$result = $conn->fetchRow($query);
		// debugMessage($result);
		echo json_encode($result);
	}
	function usersstatsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$conn = Doctrine_Manager::connection();
		$session = SessionWrapper::getInstance();
	
		$query = "SELECT
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->onehourago_timestamp.", 1, 0)) as onehourago,
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->sixhourago_timestamp.", 1, 0)) as sixhourago,
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->twelvehourago_timestamp.", 1, 0)) as twelvehourago,
		SUM(IF(TO_DAYS(c.datecreated) = TO_DAYS('".$this->view->today_iso."'), 1, 0)) as today,
		SUM(IF(TO_DAYS(c.datecreated) = TO_DAYS('".$this->view->yestday_iso."'), 1, 0)) as yesterday,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->mondaythisweek_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->sundaythisweek_iso."'), 1, 0)) as thisweek,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->mondaylastweek_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->sundaylastweek_iso."'), 1, 0)) as lastweek ,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayofthismonth_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayofthismonth_iso."'), 1, 0)) as thismonth,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayoflastmonth_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayoflastmonth_iso."'), 1, 0)) as lastmonth,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayofyear_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayofyear_iso."'), 1, 0)) as thisyear,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstsystemday_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->today_iso."'), 1, 0)) as allentries
		FROM member AS c
		where c.id <> '' AND c.status = 1 ";
		// debugMessage($query);
		$result = $conn->fetchRow($query);
		// debugMessage($result);
		echo json_encode($result);
	}
	function churchesstatsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$conn = Doctrine_Manager::connection();
		$session = SessionWrapper::getInstance();
	
		$query = "SELECT
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->onehourago_timestamp.", 1, 0)) as onehourago,
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->sixhourago_timestamp.", 1, 0)) as sixhourago,
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->twelvehourago_timestamp.", 1, 0)) as twelvehourago,
		SUM(IF(TO_DAYS(c.datecreated) = TO_DAYS('".$this->view->today_iso."'), 1, 0)) as today,
		SUM(IF(TO_DAYS(c.datecreated) = TO_DAYS('".$this->view->yestday_iso."'), 1, 0)) as yesterday,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->mondaythisweek_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->sundaythisweek_iso."'), 1, 0)) as thisweek,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->mondaylastweek_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->sundaylastweek_iso."'), 1, 0)) as lastweek ,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayofthismonth_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayofthismonth_iso."'), 1, 0)) as thismonth,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayoflastmonth_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayoflastmonth_iso."'), 1, 0)) as lastmonth,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayofyear_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayofyear_iso."'), 1, 0)) as thisyear,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstsystemday_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->today_iso."'), 1, 0)) as allentries
		FROM organisation AS c
		where c.id <> '' ";
		// debugMessage($query);
		$result = $conn->fetchRow($query);
		// debugMessage($result);
		echo json_encode($result);
	}
	function appointsstatsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$conn = Doctrine_Manager::connection();
		$session = SessionWrapper::getInstance();
	
		$query = "SELECT
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->onehourago_timestamp.", 1, 0)) as onehourago,
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->sixhourago_timestamp.", 1, 0)) as sixhourago,
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->twelvehourago_timestamp.", 1, 0)) as twelvehourago,
		SUM(IF(TO_DAYS(c.datecreated) = TO_DAYS('".$this->view->today_iso."'), 1, 0)) as today,
		SUM(IF(TO_DAYS(c.datecreated) = TO_DAYS('".$this->view->yestday_iso."'), 1, 0)) as yesterday,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->mondaythisweek_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->sundaythisweek_iso."'), 1, 0)) as thisweek,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->mondaylastweek_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->sundaylastweek_iso."'), 1, 0)) as lastweek ,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayofthismonth_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayofthismonth_iso."'), 1, 0)) as thismonth,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayoflastmonth_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayoflastmonth_iso."'), 1, 0)) as lastmonth,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayofyear_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayofyear_iso."'), 1, 0)) as thisyear,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstsystemday_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->today_iso."'), 1, 0)) as allentries
		FROM appointment AS c
		where c.id <> '' AND c.status = 1 ";
		// debugMessage($query);
		$result = $conn->fetchRow($query);
		// debugMessage($result);
		echo json_encode($result);
	}
	function smsstatsAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$conn = Doctrine_Manager::connection();
		$session = SessionWrapper::getInstance();
	
		$query = "SELECT
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->onehourago_timestamp.", c.msgcount, 0)) as onehourago,
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->sixhourago_timestamp.", c.msgcount, 0)) as sixhourago,
		SUM(IF(UNIX_TIMESTAMP(c.datecreated) >= ".$this->view->twelvehourago_timestamp.", c.msgcount, 0)) as twelvehourago,
		SUM(IF(TO_DAYS(c.datecreated) = TO_DAYS('".$this->view->today_iso."'), c.msgcount, 0)) as today,
		SUM(IF(TO_DAYS(c.datecreated) = TO_DAYS('".$this->view->yestday_iso."'), c.msgcount, 0)) as yesterday,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->mondaythisweek_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->sundaythisweek_iso."'), c.msgcount, 0)) as thisweek,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->mondaylastweek_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->sundaylastweek_iso."'), c.msgcount, 0)) as lastweek ,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayofthismonth_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayofthismonth_iso."'), c.msgcount, 0)) as thismonth,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayoflastmonth_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayoflastmonth_iso."'), c.msgcount, 0)) as lastmonth,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstdayofyear_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->lastdayofyear_iso."'), c.msgcount, 0)) as thisyear,
		SUM(IF(TO_DAYS(c.datecreated) >= TO_DAYS('".$this->view->firstsystemday_iso."') AND TO_DAYS(c.datecreated) <= TO_DAYS('".$this->view->today_iso."'), c.msgcount, 0)) as allentries
		FROM outbox AS c
		where c.phone <> '' ";
		// debugMessage($query);
		$result = $conn->fetchRow($query);
		// debugMessage($result);
		echo json_encode($result);
	}
}