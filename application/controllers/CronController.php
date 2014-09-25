<?php
/**
 * Controller that processes cron jobs 
 *
 */
class CronController extends IndexController   {
	
	/**
	 * Backs up the database with an option of sending the backup via email 
	 *
	 */
	function backupAction(){
		// disable rendering of the view and layout so that we can just echo the AJAX output
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		
		// the config object instance
		$config = Zend_Registry::get('config'); 
		
		 // get the database connection parameters 
		$db_params = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getPluginResource('db')->getParams();
		
		#  configure your database variables below:
		$host_array = explode(":", $db_params['host']); 
		$dbhost = $host_array[0]; #  Server address of your MySQL Server
		$dbuser = $db_params['username']; #  Username to access MySQL database
		$dbpass = $db_params['password']; #  Password to access MySQL database
		$dbname = $db_params['dbname']; #  Database Name
		$dbport = isArrayKeyAnEmptyString(1, $host_array) ? "3306" : "3356"; 
		
		# Optional Options You May Optionally Configure
		$use_gzip = $config->backup->usegzip;  #  Set to No if you don't want the files sent in .gz format
		$remove_sql_file = $config->backup->removesqlfile; #  Set this to yes if you want to remove the .sql file after gzipping. Yes is recommended.
		$remove_gzip_file = $config->backup->removegzipfile; #  Set this to yes if you want to delete the gzip file also. I recommend leaving it to "no"
		
		# Configure the path that this script resides on your server.
		$savepath = APPLICATION_PATH.$config->backup->scriptfolder; #  Full path to this directory. Do not use trailing slash!
		$send_email = $config->backup->sendemail;  #  Do you want this database backup sent to your email? Fill out the next 2 lines
		
		# attachment mime type - default for a text attachment 
		$attachment_mime_type = "text/plain"; 
		
		# set the maximum execution time to ensure that the backup is completed 
		ini_set("max_execution_time", 600);
		
		$date = date("dMy-Hi");
		# sql backup filename
		$sqlattachmentname = $dbname."-".$date.".sql";
		# zipped backup filename
		$gzipattachmentname = $dbname."-".$date.".tar.gz";
		# sql backup path
		$sqlscriptpath = $savepath.DIRECTORY_SEPARATOR.$sqlattachmentname;
		# zipped backup path
		$zipfilepath = $savepath.DIRECTORY_SEPARATOR.$gzipattachmentname;
		
		$backupcommand = "mysqldump -R --add-drop-table --complete-insert --add-locks --quote-names --lock-tables -h ".$dbhost." -P ".$dbport." -u ".$dbuser." -p".$dbpass." ".$dbname.' -q > "'.$sqlscriptpath.'"';
		passthru($backupcommand);
		debugMessage("backup completed to ".$sqlattachmentname);	
		
		# create tar archive
		if($use_gzip=="yes"){		
			$zipline = "tar czf ".$zipfilepath." ".$sqlscriptpath;
			shell_exec($zipline);
			debugMessage("Gzip of backup completed");
		}
		# set email attachment name and path depending on weather to form zip or not
		if($use_gzip=="yes"){
			$attachmentpath = $zipfilepath;
			$attachmentname = $gzipattachmentname;
			$attachment_mime_type = "application/gzip"; 
		} else {
			$attachmentpath = $sqlscriptpath;
			$attachmentname = $sqlattachmentname;
		}
		
		# send an email with a copy of the backup	
		if($send_email == "yes" ){
			
			$mail = Zend_Registry::get('mail');
			# build the mailer class 
			$mail->addTo($config->get(APPLICATION_ENV)->get("databasebackupemail"));
			
			$mail->setSubject(sprintf($this->_translate->_("database_backup_subject"), date("j F Y h:i:s A"))); #  Subject in the email to be sent.
			$mail->setBodyHtml($this->_translate->_("database_backup_body")); #  Brief Message.
			
			# attachmentpath is the full path to the file and attachmentname is the name of the file
			$at = new Zend_Mime_Part(file_get_contents($attachmentpath));
			$at->filename = $attachmentname; 
			$at->disposition = Zend_Mime::DISPOSITION_INLINE;
			$at->encoding = Zend_Mime::ENCODING_BASE64;
			$at->type = $attachment_mime_type; 
			$mail->addAttachment($at);
			$mail->send(); 
			debugMessage("backup sent via email");
		}
		
		# remove sql file if condition is set
		if($remove_sql_file=="yes"){
			exec("rm -rf ".$sqlscriptpath);
		}
		# remove tar file if condition is set
		if($remove_gzip_file=="yes"){
			exec("rm -rf ".$attachmentpath);
		}
	}

    
}

