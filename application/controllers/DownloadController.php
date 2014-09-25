<?php

class DownloadController extends IndexController {
        /**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// automatic file mime type handling
		$filename = decode($this->_getParam('filename')); 
		$full_path = decode($this->_getParam('path')); 
		
		// file headers to force a download
	    header('Content-Description: File Transfer');
	    header('Content-Type: application/octet-stream');
	    // to handle spaces in the file names 
	    header("Content-Disposition: attachment; filename=\"$filename\"");
	    header('Content-Transfer-Encoding: binary');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    header('Pragma: public');
	    readfile($full_path);
 
	    // disable layout and view
	    $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	}
	
	function excelAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$session = SessionWrapper::getInstance(); 
		$formvalues = $this->_getAllParams(); // debugMessage($formvalues);
		$title = $this->_getParam('reporttitle');
		
		// debugMessage($formvalues);
		$cvsdata = decode($formvalues['csv_text']); 
		if(!isEmptyString($title)){
			$cvsdata = str_replace('"--"', '""', $cvsdata); 
			$title = str_replace(', ',' ',$title);
			$cvsdata = $title."\r\n".$cvsdata;
		}
		// debugMessage($cvsdata); exit(); 
		$currenttime = mktime();
		$filename = $currenttime.'.csv';
		
		/*$full_path = APPLICATION_PATH.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.$filename;
		file_put_contents($full_path, $cvsdata);*/
		$data=stripcslashes($cvsdata); // exit();
		
	    //OUPUT HEADERS
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$filename\";" );
		header("Content-Transfer-Encoding: binary");
		 
		//OUTPUT CSV CONTENT
		echo $data;
		exit();
	}	
}
