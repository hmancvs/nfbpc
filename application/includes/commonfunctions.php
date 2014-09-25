<?php

# functions to create and manage drop down lists
require_once 'dropdownlists.php';
require_once 'BrowserDetection.php';

define("ACTION_CREATE", "create");
define("ACTION_INDEX", "index"); // maps to the default controller for Zend Framework, same as the create action in the ACL 
define("ACTION_EDIT", "edit");
define("ACTION_APPROVE", "approve");
define("ACTION_DELETE", "delete");
define("ACTION_EXPORT", "export");
define("ACTION_VIEW", "view");
define("ACTION_LIST", "list");

# redirect, success and error urls during the processing of an action 
define("URL_REDIRECT", "redirecturl"); // url forwarded to when a user has to login 
define("URL_SUCCESS", "successurl"); // override the url when an action suceeds
define("URL_FAILURE", "failureurl"); // override the url when an action fails

# the separator between a table name and a column name for filtering since the . cannot be used as
# a separator for HTML field names
define("HTML_TABLE_COLUMN_SEPARATOR", "__");

# the session variable holding the values from the REQUEST when an error occurs
define("FORM_VALUES", "formvalues");
# the session variable holding the error message when processing a form 
define("ERROR_MESSAGE", "errors"); 
# the session variable holding the success message when processing a form 
define("SUCCESS_MESSAGE", "successmessage"); 
# the session variable holding the error message for the error page which is not cleared from page to page 
define("APPLICATION_ERROR_PAGE_MESSAGE", "error_page_erros"); 
# the session variable for the access control lists 
define("SESSION_ACL", "acl"); 

# calendar view options
define("CALENDAR_VIEW_MONTH", "month_view"); 
define("CALENDAR_VIEW_WEEK", "week_view"); 

# constant for showing views in a popup
define("PAGE_CONTENTS_ONLY", "pgc"); 
define("EXPORT_TO_EXCEL", "excel"); 

# constant for the select chain value
define("SELECT_CHAIN_TYPE", "select_chain_type"); 

# excel generation constants
# a comma delimited list of column indexes with numbers
define("EXPORT_NUMBER_COLUMN_LIST", "numbercolumnlist");
# the number of columns to ignore at the beginning of the query 
define("EXPORT_IGNORE_COLUMN_COUNT", "columncheck");  
# the query string with all the results
define("ALL_RESULTS_QUERY", "arq");
# the query string with the searches and filters applied
define("CURRENT_RESULTS_QUERY", "crq");
# the page title for current list
define("PAGE_TITLE", "ttl");

define('DEFAULT_USER_GROUP', '2');
define('DEFAULT_ID', '1');
define('VALIDATE_PHONE_ACTIVATED', false);
define('DEFAULT_DATETIME', date("Y-m-d H:i:s"));
define('DEFAULT_PROGRAM_LINES', 20);

function getAppName(){
	$config = Zend_Registry::get("config"); 
	return $config->system->appname;
}
function getAppFullName(){
	$config = Zend_Registry::get("config");
	return $config->system->appname;
}
function getCompanyName(){
	$config = Zend_Registry::get("config"); 
	return $config->system->companyname;
}
function getCompanySignoff(){
	$config = Zend_Registry::get("config"); 
	return $config->system->companysignoff;
}
function getCopyrightInfo(){
	$config = Zend_Registry::get("config"); 
	return $config->system->copyrightinfo;
}
function getCountryCode(){
	$config = Zend_Registry::get("config"); 
	return $config->country->countrycode;
}
function getCountryCurrency(){
	$config = Zend_Registry::get("config"); 
	return $config->country->currencysymbol;
}
function getCountryCurrencyCode(){
	$config = Zend_Registry::get("config"); 
	return $config->country->currencycode;
}
function getDefaultAdminEmail(){
	$config = Zend_Registry::get("config"); 
	return $config->notification->defaultadminemail;
}
function getDefaultAdminName(){
	$config = Zend_Registry::get("config"); 
	return $config->notification->defaultadminname;
}
function getEmailMessageSender(){
	$config = Zend_Registry::get("config"); 
	return $config->notification->emailmessagesender;
}
function getNotificationSenderName(){
	$config = Zend_Registry::get("config"); 
	return $config->notification->notificationsendername;
}
/**
 * Change a date from MySQL database Format (yyyy-mm-dd) to the format displayed on pages(mm/dd/yyyy)
 * 
 * If the date from the database is NULL, it is transformed to an empty string for display on the pages 
 *
 * @param String $mysqldate The date in MySQL format 
 * @return String the date in short date format, or an empty string if no date is provided 
 */
function changeMySQLDateToPageFormat($mysqldate) {
	$aconfig = Zend_Registry::get("config"); 
	if (isEmptyString($mysqldate)) {
		return $mysqldate;
	} else {
		return date($aconfig->dateandtime->mediumformat, strtotime($mysqldate));
	}
}
/**
 * Transform a date from the format displayed on pages(mm/dd/yyyy) to the MySQL database date format (yyyy-mm-dd). 
 * If the date from the database is an empty string or the string NULL, it is transformed to a NULL value.
 *
 * @param String $pagedate The string representing the date
 * @return String The MYSQL datetime format or NULL if the provided date is an empty string or the string NULL 
 */
function changeDateFromPageToMySQLFormat($pagedate, $ignoretime = true) {
	if ($pagedate == "NULL") {
		return NULL;
	}
	if (isEmptyString($pagedate)) {
		return NULL;
	} else {
		if($ignoretime){
			return date("Y-m-d", strtotime($pagedate));
		} else {
			return date("Y-m-d H:i:s", strtotime($pagedate));
		}
	}
}

function formatDateAndTime($mysqldate, $ignoretime = true){
	if(isEmptyString($mysqldate)){
		return '--';
	}
	$timestr = '';
	if($ignoretime === true){
		$timestr = '  H:i';
	}
	$oDate = new DateTime($mysqldate);
	$sDate = $oDate->format("m/d/Y".$timestr);
	return $sDate;
}
function formatTime($timestring){
	if(isEmptyString($timestring)){
		return '--';
	}
	$otime = new DateTime($timestring);
	$stime = $otime->format("H:i a");
	return $stime;
}
function getCurrentMysqlTimestamp(){
	return date('Y-m-d H:i:s');
}
function decimalToTime($decimal){
	return gmdate('H:i', floor($decimal * 3600));
}
/**
 * Check whether or not the string is empty. The string is emptied
 *
 * @param String $str The string to be checked
 * 
 * @return Boolean Whether or not the string is empty
 */
function isEmptyString($str) {
	if ($str == "") {
		return true; 
	}
	if (trim($str) == "") {
		return true;
	} else {
		return false;
	}
}

/**
 * Check whether or not the value of the key in the specified array is empty
 *
 * @param String $key The key whose value is to be checked
 * @param Array $arr The array to check  
 * 
 * @return Boolean Whether or not the array key is empty
 */
function isArrayKeyAnEmptyString($key, $arr) {
	if (!array_key_exists($key, $arr)) {
		return true; 
	}
	if (is_string($arr[$key])) {
		return isEmptyString($arr[$key]);
	}
	return false; 
}
/**
 * Check whether or not the string is empty. The string is emptied
 *
 * @param String $str The string to be checked
 * 
 * @return boolean Whether or not the string is empty
 */
function isNotAnEmptyString($str) {
	return ! isEmptyString($str);
}

/**
 * Return a debug message with a break tag before and two break tags after
 *
 * @param Object $obj The object to be printed
 */
function debugMessage($obj) {
	echo "<br />";
	print_r($obj);
	echo "<br /><br />";
}

/**
 * Return the value of the checked HTML attribute for a checkbox or radio button
 *
 * @param Boolean $bool whether or not the HTML control is checked
 * @return String the checked attribute
 */
function getCheckedAttribute($bool) {
	if ($bool) {
		return ' checked="checked"';
	}
	return "";
}
/**
 *  Merge the arrays passed to the function and keep the keys intact.
 *  If two keys overlap then it is the last added key that takes precedence.
 * 
 * @return Array the merged array
 */
function array_merge_maintain_keys() {
	$args = func_get_args();
	$result = array();
	foreach ( $args as &$array ) {
		foreach ( $array as $key => &$value ) {
			$result[$key] = $value;
		}
	}
	return $result;
}

# function that trims every value of an array
function trim_value(&$value) {
	$value = trim($value);
}

/**
 * Recursively Remove empty values from an array. If any of the keys contains an 
 * array, the values are also removed.
 *
 * @param Array $input The array
 * @return Array with the specified values removed or the filtered values
 */
function array_remove_empty($arr) {
	$narr = array();
	while ( list ($key, $val) = each($arr) ) {
		if (is_array($val)) {
			$val = array_remove_empty($val);
			// does the result array contain anything?
			if (count($val) != 0) {
				// yes :-)
				$narr[$key] = $val;
			}
		} else {
			if (! isEmptyString($val)) {
				$narr[$key] = $val;
			}
		}
	}
	unset($arr);
	return $narr;

}

/**
 * Send test email
 *
 * @param String $subject The subject of the email 
 * @param String $message The contents of the email 
 */
function sendTestMessage($subject = "", $message = "") {
	$mailer = getMailInstance(); 
	
	# get an instance of the PHP Mailer
	$from_email = $mailer->getDefaultFrom(); 
	// $mailer->AddTo($from_email['email']);
	$mailer->AddTo("hmanmstw@gmail.com");
	
	$mailer->setSubject($subject);
	$mailer->setBodyHTML($message);
	try {
		$result = $mailer->send();
		// debugMessage("The email sending result is ".$result);
		if (!$result) {
			# Log the error
			echo "an error occured while sending the message " . $mailer->ErrorInfo;
		} else {
			debugMessage("message sent to ".APPLICATION_ENV);
		}
	} catch ( Exception $e ) {
		debugMessage("Error sending email ".$e);
	}
}

/**
 * Wrapper function for the encoding of the urls using base64_encode 
 *
 * @param String $str The string to be encoded
 * @return String The encoded string 
 */
function encode($str) {
	return base64_encode($str); 
}
/**
 * Wrapper function for the decoding of the urls using base64_decode 
 *
 * @param String $str The string to be decoded
 * @return String The encoded string 
 */
function decode($str) {
	return base64_decode($str); 
}

/**
 * Function to generate a JSON string from an array of data, using the keys and values
 *
 * @param $data The data to be converted into a string
 * @param $default_option_value Value of the default option
 * @param $default_option_text Test for the default 
 * 
 * @return the JSON string containing the select options
 */
function generateJSONStringForSelectChain($data, $default_option_value = "", $default_option_text = "<Select One>") {
	$values = array(); 
	//debugMessage($data);
	if (!isEmptyString($default_option_value)) {
		# use the text and option from the data
		if(!isArrayKeyAnEmptyString($default_option_value, $data)){
			$values[] = '{"id":"' . $default_option_value . '", "label":"' . $data[$default_option_value] . '"}';
			// remove the default option from the available options
			unset($data[$default_option_value]);
		}
	}
	# add a default option
	$values[] = '{"id":"", "label":"' . $default_option_text . '"}';
	foreach ( $data as $key => $value ) {
		$values[] = '{"id":"'.$key.'", "label":"' . $value . '"}';
		//debugMessage($strstring);
	}
	# remove the first comma at the end
	return '[' . implode("," , $values). "]";
}
/**
 * Format a number to two decimal places and a comma separator between thousands. Empty Strings are considered to be numeric
 *
 * @param Number $number The number to be formatted
 * @return Number The formatted version of the number
 */
function formatNumber($number) {
	if (isEmptyString($number) || !is_numeric($number)) {
		return $number;
	}
	$aconfig = Zend_Registry::get("config"); 
	return number_format($number, $aconfig->country->currencydecimalplaces);
}
function formatMoney($amount) {
	$aconfig = Zend_Registry::get("config");
	if (isEmptyString($amount)) {
		return '--';
	}
	return formatNumber($amount)."&nbsp;<span class='pagedescription'>(".$aconfig->country->currencydecimalplaces.")</span>";
}
function formatMoneyOnly($amount) {
	$aconfig = Zend_Registry::get("config");
	if (isEmptyString($amount)) {
		return '0';
	}
	return formatNumber($amount);
}
/**
 * Generate an HTML list from an array of values
 *
 * @param Array $array
 * @return String 
 */
function createHTMLListFromArray($array, $classname="") {
	$str = ""; 
	// return empty string if no array is passed
	if (!is_array($array)) {
		return $str; 
	}
	// return an empty string if the array is empty
	if (!$array) {
		return $str; 
	}
	$class = "";
	if(!isEmptyString($classname)){
		$class = " class='".$classname."'";
	}
	// opening list tag and the first li element
	$str  = "<ul ".$class."><li>";
	// explode the array and generate the inner list items
	$str .= implode($array, "</li><li>");
	// close the last list item, and the ul
	$str .= "</li></ul>"; 
	
	return $str; 
}
function createHTMLCommaListFromArray($array, $separator = "', '") {
	$str = ""; 
	// return empty string if no array is passed
	if (!is_array($array)) {
		return $str; 
	}
	// return an empty string if the array is empty
	if (!$array) {
		return $str; 
	}
	
	// explode the array and generate the inner list items
	$str .= implode($array, $separator);
	
	return $str; 
}
/**
  * Load the application configuration from the database
  * 
  */
function loadConfig() {
	$cache = Zend_Registry::get('cache');
	// load the configuration from the cache
	$config = $cache->load('config'); 
	if (!$config) {
		// do nothing 
	} else {
		// add the config object to the registry 
		Zend_Registry::set('config', $config);
		return; 
	}
	
	// load the active application configuration from the database
	$sql = "SELECT section, optionname, optionvalue FROM appconfig WHERE active = 'Y'";

	$conn = Doctrine_Manager::connection(); 
	$result = $conn->fetchAll($sql); 
	
	// generate a config array from the data
	if (!$result) {
		// do nothing no data returned
	} else {
		$config_array = array(); 
		foreach ($result as $line) {
			if (isEmptyString($line['section'])) {
				// no section name provided so add the option to the root
				$config_array[$line['optionname']] = $line['optionvalue']; 
			} else {
				// add the option to the section 
				$config_array[$line['section']][$line['optionname']]= $line['optionvalue'];
			}  
		}
		# Add the config object to the registry
		$config = new Zend_Config($config_array); 
		Zend_Registry::set('config', $config);
		# cache the config object
		$cache->save($config, 'config');
	}
}
/**
 * Create a Zend_Mail instance from the registry, clear all recipients and the existing subject
 * 
 * @return Zend_Mail 
 */
function getMailInstance() {
	// create mail object
	$mail = Zend_Registry::get("mail");
	//TODO: Temporary workaround for subject set twice error message
	// clear the subject to prevent an error when sending multiple emails in the same session
	$mail->clearSubject(); 
	// clear the addresses too
	$mail->clearRecipients();
	
	return $mail; 
}
/**
 * Return an instance of the access control list 
 *
 * @return ACL 
 */
function getACLInstance() {
	$cache = Zend_Registry::get('cache'); 
	$session = SessionWrapper::getInstance(); 
	// check if the acl is cached
	$aclkey = "acl".$session->getVar('userid'); 
	$acl = $cache->load($aclkey); 
	if (!$acl) {
		$acl = new ACL($session->getVar('userid')); 
	}
	
	return $acl; 
}
/**
 * Return the file extension from a file name
 * 
 * @param string $filename
 * @return The file extension 
 */
function findExtension($filename){  
	return substr(strrchr($filename,'.'),1);
}
/**
 * Decode all html entities of an array  
 * @param Array $elem the array to be decoded
 */
function decodeHtmlEntitiesInArray(&$elem){ 
	if (!is_array($elem)) { 
    	$elem=html_entity_decode($elem); 
	}  else  { 
		foreach ($elem as $key=>$value){
			$elem[$key]=decodeHtmlEntitiesInArray($value);
		} 
  	} 
	return $elem; 
}
 /**
 * Trims a given string with a length more than a specified length with a more link to view the details 
 *
 * @param string $text
 * @param int $length
 * @param string $tail
 * @return string the substring with a more link as the tail
 */
function snippet($text, $length, $tail) {
	$text = trim($text);
	$txtl = strlen($text);
	if ($txtl > $length) {
		for($i = 1; $text[$length - $i] != " "; $i ++) {
			if ($i == $length) {
				return substr($text, 0, $length) . $tail;
			}
		}
		for(; $text[$length - $i] == "," || $text[$length - $i] == "." || $text[$length - $i] == " "; $i ++) {
			;
		}
		$text = substr($text, 0, $length - $i + 1) . $tail;
	}
	return $text;
} 
function formatBytes($size, $precision = 2) { 
    $base = log($size) / log(1000);
    $suffixes = array('', 'KB', 'MB', 'GB', 'TB');   

    return round(pow(1000, $base - floor($base)), $precision) . $suffixes[floor($base)];
}
 /*
 * Generate a thumbnail from a source and a new width
 */
function resizeImage($in_filename, $out_filename, $width){
	$src_img = ImageCreateFromJpeg($in_filename);

    $old_x = ImageSX($src_img);
    $old_y = ImageSY($src_img);
    
    /* find the "desired height" of this thumbnail, relative to the desired width  */
  	$desired_height = floor($old_y *($width/$old_x));
  
    $dst_img = ImageCreateTrueColor($width, $desired_height);
    ImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $width, $desired_height, $old_x, $old_y);

    ImageJpeg($dst_img, $out_filename, 100);

    ImageDestroy($dst_img);
    ImageDestroy($src_img);
}
# determine key for an array in a multimensional array
function array_search_key($all, $checkarray) {
   foreach ($all as $key => $value) {
		// debugMessage($subkey);
		if($checkarray['id'] == $value['id'] && count(array_diff($value, $checkarray)) == 0){
			return $key;
		}
   }
}
# determine key for id in multidimensional array
function array_search_key_by_id($themultiarray, $theid) {
   foreach ($themultiarray as $key => $value) {
		// debugMessage($subkey);
		if($theid == $value['id']){
			return $key;
		}
   }
}
# synchronise to multimensional arrays
function multidimensional_array_merge($oldarray, $newarray){
    $result = array();
    foreach($oldarray as $key => $value){
    	$result[$key] = $value;
        foreach ($newarray as $n_key => $n_value) {
        	if(strval($n_key) == strval($key)){
        		// debugMessage($value); debugMessage($newarray[$key]);
        		$merged = array_merge($oldarray[$key], $newarray[$n_key]);
        		unset($result[$key]);
        		$result[$key] = $merged;
        	} else {
        		$result[$n_key] = $n_value;
        	}
        }
        // debugMessage($oldarray[$n_key]);
    }
    return $result;
}
# convert text to url
function textToUrl($txtstring){
	if(isEmptyString($txtstring)){
		return '---';
	}
	return "<a href='".$txtstring."' target='_blank' title='Visit address'>".$txtstring."</a>";
}
# determine if person has profile image
function hasProfileImage($id, $photoname){
	$real_path = APPLICATION_PATH."/../public/uploads/user_";
 	if (APPLICATION_ENV == "production") {
 		$real_path = str_replace("public/", "", $real_path); 
 	}
	$real_path = $real_path.$id.DIRECTORY_SEPARATOR."avatar".DIRECTORY_SEPARATOR."base_".$photoname;
	if(file_exists($real_path) && !isEmptyString($photoname)){
		return true;
	}
	return false;
}
# determine path to small profile picture
function getSmallPicturePath($id, $gender, $photoname) {
	$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
	$path= "";
	if(isMale($gender)){
		$path = $baseUrl.'/uploads/user_0/avatar/default_small_male.jpg';
	}  
	if(isFemale($gender)){
		$path = $baseUrl.'/uploads/user_0/avatar/default_small_female.jpg'; 
	}
	if(hasProfileImage($id, $photoname)){
		$path = $baseUrl.'/uploads/user_'.$id.'/avatar/small_'.$photoname;
	}
	return $path;
}
# determine path to thumbnail profile picture
function getThumbnailPicturePath($id, $gender, $photoname) {
	$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
	$path= "";
	if(isMale($gender)){
		$path = $baseUrl.'/uploads/user_0/avatar/default_thumbnail_male.jpg';
	}  
	if(isFemale($gender)){
		$path = $baseUrl.'/uploads/user_0/avatar/default_thumbnail_female.jpg'; 
	}
	if(hasProfileImage($id, $photoname)){
		$path = $baseUrl.'/uploads/user_'.$id.'/avatar/thumbnail_'.$photoname;
	}
	return $path;
}
# determine path to medium profile picture
function getMediumPicturePath($id, $gender, $photoname) {
	$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
	$path= "";
	if(isMale($gender)){
		$path = $baseUrl.'/uploads/user_0/avatar/default_medium_male.jpg';
	}  
	if(isFemale($gender)){
		$path = $baseUrl.'/uploads/user_0/avatar/default_medium_female.jpg'; 
	}
	if(hasProfileImage($id, $photoname)){
		$path = $baseUrl.'/uploads/user_'.$id.'/avatar/medium_'.$photoname;
	}
	return $path;
}
# determine path to large profile picture
function getLargePicturePath($id, $gender, $photoname) {
	$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
	$path= "";
	if(isMale($gender)){
		$path = $baseUrl.'/uploads/user_0/avatar/default_large_male.jpg';
	}  
	if(isFemale($gender)){
		$path = $baseUrl.'/uploads/user_0/avatar/default_large_female.jpg'; 
	}
	if(hasProfileImage($id, $photoname)){
		$path = $baseUrl.'/uploads/user_'.$id.'/avatar/large_'.$photoname;
	}
	// debugMessage($path);
	return $path;
}
# determine if male
function isMale($gender){
	return $gender == '1' ? true : false; 
}
# determine if female
function isFemale($gender){
	return $gender == '2' ? true : false; 
}
function getGenderText($gender){
	$str = 'Male';
	if(isFemale($gender)){
		$str = 'Male';
	}
	return $str;
}
function getImagePath($id, $filename, $gender){
	$hasprofileimage = false;
	$real_path = BASE_PATH.DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."members".DIRECTORY_SEPARATOR."member_".$id.DIRECTORY_SEPARATOR."avatar".DIRECTORY_SEPARATOR."medium_".$filename;
	if(file_exists($real_path) && !isEmptyString($filename)){
		$hasprofileimage = true;
	}
	$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
	$photo_path = $baseUrl.'/uploads/default/default_medium_male.jpg';
	if(isFemale($gender)){
		$photo_path = $baseUrl.'/uploads/default/default_thumbnail_female.jpg';
	}
	if($hasprofileimage){
		$photo_path = $baseUrl.'/uploads/members/member_'.$$id.'/avatar/medium_'.$filename;
	}
	
	return $photo_path;
}
# determine if loggedin user is admin
function isAdmin() {
	$session = SessionWrapper::getInstance(); 
	$return = false;
	if($session->getVar('type') == '1'){
		$return = true;
	}
	return $return;
}
# determine if loggedin user is subscriber
function isEmployee() {
	$session = SessionWrapper::getInstance(); 
	return $session->getVar('type') == '2' ? true : false;
}
# determine if loggedin user is data clerk
function isDataClerk() {
	$session = SessionWrapper::getInstance(); 
	return $session->getVar('type') == '3' ? true : false;
}
function isManager() {
	$session = SessionWrapper::getInstance(); 
	return $session->getVar('type') == '4' ? true : false;
}
# determine if user is logged in
function isLoggedIn(){
	$session = SessionWrapper::getInstance();
	return isEmptyString($session->getVar('userid')) ? false : true;
}
# determine current status label
function getStatusText($status) {
	$text = '--';
	if(!isEmptyString($status)){
		$values = getStatusValues();
		$text = $values[$status];
    }
	return $text;
}
function png2jpg($originalFile, $outputFile, $quality) {
    $image = imagecreatefrompng($originalFile);
    imagejpeg($image, $outputFile, $quality);
    imagedestroy($image);
}
function ak_img_convert_to_jpg($target, $newcopy, $ext) {
    list($w_orig, $h_orig) = getimagesize($target);
    $ext = strtolower($ext);
    $img = "";
    if ($ext == "gif"){ 
        $img = imagecreatefromgif($target);
    } else if($ext =="png"){ 
        $img = imagecreatefrompng($target);
    }
    $tci = imagecreatetruecolor($w_orig, $h_orig);
    imagecopyresampled($tci, $img, 0, 0, 0, 0, $w_orig, $h_orig, $w_orig, $h_orig);
    imagejpeg($tci, $newcopy, 84);
}
/**
 * Determine the Unix timestamp of a given MySql date
 * @param string the MySql date whose timestamp is being determined
 * @return string the timestamp of the MySql date specified
 */
function convertMySqlDateToUnixTimestamp($mysqldate) {
	if ($mysqldate) {
		$parts = explode(' ', $mysqldate);
	}
	$datebits = explode('-', $parts[0]);
	if (3 != count($datebits)) {
		return -1;
	}
	if (isset($parts[1])) {
		$timebits = explode(':', $parts[1]);
		if (3 != count($timebits)) return -1;
		return mktime($timebits[0], $timebits[1], $timebits[2], $datebits[1], $datebits[2], $datebits[0]);
	}
	return mktime (0, 0, 0, $datebits[1], $datebits[2], $datebits[0]);
}
function sort_multi_array ($array, $key, $order="DESC"){
  $keys = array();
  for ($i=1;$i<func_num_args();$i++) {
    $keys[$i-1] = func_get_arg($i);
  }

  // create a custom search function to pass to usort
  $func = function ($a, $b) use ($keys) {
    for ($i=0;$i<count($keys);$i++) {
      if ($a[$keys[$i]] != $b[$keys[$i]]) {
        return ($a[$keys[$i]] < $b[$keys[$i]]) ? -1 : 1;
      }
    }
    return 0;
  };
  usort($array, $func);
  if($order != "DESC"){
  	$result = $array;
  } else {
  	$result = array_reverse($array, true);
  }
  return $result;
}
function num_to_letter($num, $uppercase = FALSE){
	$num -= 1;
	
	$letter = 	chr(($num % 26) + 97);
	$letter .= 	(floor($num/26) > 0) ? str_repeat($letter, floor($num/26)) : '';
	return 		($uppercase ? strtoupper($letter) : $letter); 
}
# return the formatted phone number
function getShortPhone($phone){
	if(isEmptyString($phone)){
		return '';
	}
	return str_pad(ltrim($phone, getCountryCode()), 10, '0', STR_PAD_LEFT); 
} 
function getFullPhone($phone){
	if(isEmptyString($phone)){
		return '';
	}
	return str_pad(ltrim($phone, '0'), 12, getCountryCode(), STR_PAD_LEFT);
}
function _is_curl_installed() {
    if  (in_array  ('curl', get_loaded_extensions())) {
        return true;
    }
    else{
        return false;
    }
}
function clean_num($num){
	if(isEmptyString($num)){
		return '';		
	}
	return rtrim(rtrim(number_format($num, 2, ".", ""), '0'), '.');
}
// function to covert simple xml to an array
// Convert an xml object in to an array
/**
 * convert xml string to php array - useful to get a serializable value
 *
 * @param string $xmlstr 
 * @return array
 * @author Adrien aka Gaarf
 */
function xmlstr_to_array($xmlstr) {
  $doc = new DOMDocument();
  $doc->loadXML($xmlstr);
  return domnode_to_array($doc->documentElement);
}
function domnode_to_array($node) {
  $output = array();
  switch ($node->nodeType) {
   case XML_CDATA_SECTION_NODE:
   case XML_TEXT_NODE:
    $output = trim($node->textContent);
   break;
   case XML_ELEMENT_NODE:
    for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) { 
     $child = $node->childNodes->item($i);
     $v = domnode_to_array($child);
     if(isset($child->tagName)) {
       $t = $child->tagName;
       if(!isset($output[$t])){
       	// debugMessage($output[$t]);
        $output[$t] = array();
       }
       $output[$t][] = $v;
     }
     elseif($v) {
      $output = (string) $v;
     }
    }
    if(is_array($output)) {
     if($node->attributes->length) {
      $a = array();
      foreach($node->attributes as $attrName => $attrNode) {
       $a[$attrName] = (string) $attrNode->value;
      }
      $output['@attributes'] = $a;
     }
     foreach ($output as $t => $v) {
      if(is_array($v) && count($v)==1 && $t!='@attributes') {
       $output[$t] = $v[0];
      }
     }
    }
   break;
  }
  return $output;
}
# left pad number with zeros
function number_pad($number,$n) {
	return str_pad((int) $number,$n,"0",STR_PAD_LEFT);
}
# determine the number of days between two dates
function dateDiff($start, $end) {
	$start_ts = strtotime($start);
	$end_ts = strtotime($end);
	$diff = $end_ts - $start_ts;
	return round($diff / 86400);

}

function format($str){
	return isEmptyString($str) ? '--' : $str;
}
function getStyleIncludes(){
	$files = array(
		# major css to run application
		// 'stylesheets/bootstrap-3.1.1.css', ## due to breaking of minificaition, this file is appended directly in the layout script
		'stylesheets/bootstrap-theme.css',
		'stylesheets/jquery.ui.1.8.14.css',
			
		# append all plugins and extensions styles to end of plugins.css so as to keep includes to minimal
		'stylesheets/plugins.css',
		
		# application specific
		'stylesheets/custom.css', // templated styles
		'stylesheets/app.css'
	);
	return $files;
}
function getJsIncludes(){
	$files = array(
		# major javascript to run application
		'javascript/jquery-1.11.1.min.js',
		'javascript/bootstrap-3.1.1.min.js',
		'javascript/plugins/jquery-ui-1.8.14.custom.min.js',
		'javascript/jquery-migrate-1.0.0.js',
		
		# include here all plugins and extensions
		'javascript/plugins/jquery.validate-1.11.1.min.js',
		'javascript/plugins/jquery.validate.min.js',
		'javascript/plugins/select-chain.js',
		'javascript/plugins/chosen.jquery-1.0.0.min.js',
		'javascript/plugins/jquery.elastic.source.1.6.11.js',		
		'javascript/plugins/jquery.placeholder.min.js',				
		'javascript/plugins/bootbox-4.2.0.min.js',
		'javascript/plugins/jquery.blockUI.js',
			
		//'javascript/jquery.form.min.js',
		//'javascript/pdfobject.js',
		//'javascript/jquery.qtip.min.js',
		//'javascript/jquery.tipsy.js',
		//'javascript/jquery.imgareaselect.min.js',
		//'javascript/jquery.slimscroll.min.js',
		//'javascript/jquery.slimscroll.horizontal.min.js',
		//'javascript/jquery.doubleScroll.js',
		//'javascript/table2CSV.js',
		//'javascript/tagmanager.js',
		//'javascript/highcharts.js',
		//'javascript/exporting.js',
		
		# application specific js
		'javascript/app.js'
	);
	return $files;
}
function greatUser($name){
	$b = time(); 
	
	$hour = date("g",$b);
	$m = date ("A", $b);
	
	$txt = '';
	if ($m == "AM"){
		if ($hour == 12){
			$txt = "Good Evening";
		} else if ($hour < 4){
	 		$txt = "Good Evening";
	 	} elseif ($hour > 3){
			$txt = "Good Morning";
	 	}
	 } elseif ($m == "PM") {
	 	if ($hour == 12){
	 		$txt = "Good Afternoon";
	 	} elseif ($hour < 7){
	 		$txt = "Good Afternoon";
	 	} elseif ($hour > 6) {
	 		$txt = "Good Evening";
	 	}
	 }
	 return $txt.', '.$name; 
}

function getAgent(){
	$tablet_browser = 0;
	$mobile_browser = 0;
	 
	if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
	    $tablet_browser++;
	}
	 
	if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
	    $mobile_browser++;
	}
	 
	if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
	    $mobile_browser++;
	}
	 
	$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
	$mobile_agents = array(
	    'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
	    'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
	    'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
	    'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
	    'newt','noki','palm','pana','pant','phil','play','port','prox',
	    'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
	    'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
	    'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
	    'wapr','webc','winw','winw','xda ','xda-');
	 
	if (in_array($mobile_ua,$mobile_agents)) {
	    $mobile_browser++;
	}
	 
	if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'opera mini') > 0) {
	    $mobile_browser++;
	    //Check for tablets on opera mini alternative headers
	    $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
	    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
	      $tablet_browser++;
	    }
	}
	 
	if ($tablet_browser > 0) {
	   // do something for tablet devices
	   return 'tablet';
	}
	else if ($mobile_browser > 0) {
	   // do something for mobile devices
	   return 'mobile';
	}
	else {
	   // do something for everything else
	   return 'desktop';
	}
}
function isMobile() {
	if(getAgent() == 'mobile'){
		return true;
	}
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
function retrieve_remote_file_size($url){
     $ch = curl_init($url);

     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     curl_setopt($ch, CURLOPT_HEADER, TRUE);
     curl_setopt($ch, CURLOPT_NOBODY, TRUE);

     $data = curl_exec($ch);
     $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

     curl_close($ch);
     return $size;
}
/** 
* Counts the number occurrences of a certain day of the week between a start and end date
* The $start and $end variables must be in UTC format or you will get the wrong number 
* of days  when crossing daylight savings time
* @param - $day - the day of the week such as "Monday", "Tuesday"...
* @param - $start - a UTC timestamp representing the start date
* @param - $end - a UTC timestamp representing the end date
* @return Number of occurences of $day between $start and $end
*/
function countDays($day, $start, $end)
{        
    //get the day of the week for start and end dates (0-6)
    $w = array(date('w', $start), date('w', $end));

    //get partial week day count
    if ($w[0] < $w[1])
    {            
        $partialWeekCount = ($day >= $w[0] && $day <= $w[1]);
    }else if ($w[0] == $w[1])
    {
        $partialWeekCount = $w[0] == $day;
    }else
    {
        $partialWeekCount = ($day >= $w[0] || $day <= $w[1]);
    }

    //first count the number of complete weeks, then add 1 if $day falls in a partial week.
    return floor( ( $end-$start )/60/60/24/7) + $partialWeekCount;
}
function stringContains($substr, $str){
	if(strpos($str, $substr) !== false) {
		return true;
	}
	return false;
}
function curlContents($url, $method = 'GET', $data = false, $headers = false, $returnInfo = false) {    
    $ch = curl_init();
    
    if($method == 'POST') {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        if($data !== false) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    } else {
        if($data !== false) {
            if(is_array($data)) {
                $dataTokens = array();
                foreach($data as $key => $value) {
                    array_push($dataTokens, urlencode($key).'='.urlencode($value));
                }
                $data = implode('&', $dataTokens);
            }
            curl_setopt($ch, CURLOPT_URL, $url.'?'.$data);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if($headers !== false) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $contents = curl_exec($ch);
    
    if($returnInfo) {
        $info = curl_getinfo($ch);
    }
	// debugMessage(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
    curl_close($ch);

    if($returnInfo) {
        return array('contents' => $contents, 'info' => $info);
    } else {
    	// debugMessage($contents);
        return $contents;
    }
}
function getClientUrl (Zend_Http_Client $client)
{
    try
    {
        $c = clone $client;
        /*
         * Assume there is nothing on 80 port.
         */
        $c->setUri ('http://127.0.0.1');

        $c->getAdapter ()
            ->setConfig (array (
            'timeout' => 0
        ));

        $c->request ();
    }
    catch (Exception $e)
    {
        $string = $c->getLastRequest ();
        $string = substr ($string, 4, strpos ($string, "HTTP/1.1\r\n") - 5);
    }
    return $client->getUri (true) . $string;
}
function fileUploaded() {
	if(empty($_FILES)) {
		return false;
	}
	return true;
}
?>