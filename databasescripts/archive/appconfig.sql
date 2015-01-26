/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50524
Source Host           : localhost:3306
Source Database       : nfbpc

Target Server Type    : MYSQL
Target Server Version : 50524
File Encoding         : 65001

Date: 2014-11-01 09:06:54
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `appconfig`
-- ----------------------------
DROP TABLE IF EXISTS `appconfig`;
CREATE TABLE `appconfig` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `section` varchar(50) DEFAULT '',
  `sectiondisplay` varchar(50) DEFAULT NULL,
  `optionname` varchar(50) DEFAULT NULL,
  `displayname` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `optionvalue` varchar(255) DEFAULT '',
  `optiontype` varchar(15) DEFAULT '',
  `active` enum('N','Y') DEFAULT 'Y',
  `editable` tinyint(4) unsigned DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL,
  `createdby` int(11) unsigned DEFAULT NULL,
  `lastupdatedate` datetime DEFAULT NULL,
  `lastupdatedby` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_appconfig_createdby` (`createdby`),
  KEY `index_appconfig_lastupdatedby` (`lastupdatedby`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of appconfig
-- ----------------------------
INSERT INTO `appconfig` VALUES ('1', 'backup', 'Backup Settings', 'retentionperiod', 'Backup Rentention Perid', 'Duration for which backups are kept', '7', 'integer', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-21 19:01:16', null);
INSERT INTO `appconfig` VALUES ('2', 'backup', 'Backup Settings', 'scriptfolder', 'Folder for Backup Scripts', 'The path relative to the APPLICATION_PATH variable, use a starting / since the variable is a folder name', 'backup', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-21 19:01:16', null);
INSERT INTO `appconfig` VALUES ('3', 'backup', 'Backup Settings', 'usegzip', 'Gzip Backups', 'Whether to use gzip compression or not, options are yes and no', 'yes', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-21 19:01:16', null);
INSERT INTO `appconfig` VALUES ('4', 'backup', 'Backup Settings', 'removesqlfile', 'Sql Backups', 'Remove SQL file after processing, options are yes and no', 'yes', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-21 19:01:16', null);
INSERT INTO `appconfig` VALUES ('5', 'backup', 'Backup Settings', 'sendemail', 'Send Backups to Admin Email', 'Send backup via email, options are yes and no', 'no', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-21 19:01:16', null);
INSERT INTO `appconfig` VALUES ('6', 'country', 'Country Settings', 'currencysymbol', 'Currency Symbol', '', 'Shs', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('7', 'country', 'Country Settings', 'currencycode', 'Currency Code', '', 'UGX', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('8', 'country', 'Country Settings', 'currencydecimalplaces', 'Currreny decimal places', '', '0', 'integer', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('9', 'country', 'Country Settings', 'mincurrencyvalue', 'Minimum currency amount', '', '50', 'decimal', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('10', 'country', 'Country Settings', 'maxcurrencyvalue', 'Maximum currency amount', '', '50000', 'decimal', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('12', 'dateandtime', 'Date and Time Settings', 'shortformat', 'Short date display format', '', 'm/d/Y', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('13', 'dateandtime', 'Date and Time Settings', 'mediumformat', 'Long date display format', '', 'j M, Y', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('14', 'dateandtime', 'Date and Time Settings', 'longformat', 'Long date with week day', '', 'l, j F Y', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('15', 'dateandtime', 'Date and Time Settings', 'mysqlformat', 'Short date database format', '', '%m/%d/%Y', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('16', 'dateandtime', 'Date and Time Settings', 'mysqlmediumformat', 'Long date database format', '', '%d %b, %Y', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('17', 'dateandtime', 'Date and Time Settings', 'mysqldateandtimeformat', 'Long date with timestamp', '', '%m/%d/%Y - %H:%i:%s', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('19', 'profile', 'User Profile Settings', 'passwordminlength', 'Minimum password length', 'The minimum length of a password', '6', 'integer', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-03-17 00:05:02', null);
INSERT INTO `appconfig` VALUES ('20', 'dateandtime', 'Date and Time Settings', 'mindate', 'Date picker number of days ahead of current date', 'The minimum date for the date picker', '2', 'integer', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('21', 'dateandtime', 'Date and Time Settings', 'maxdate', 'Date picker number of days before current date', 'The maximum date for the date picker', '2', 'integer', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('22', 'dateandtime', 'Date and Time Settings', 'datepickerformat', 'Javascript long date', 'The format for Javascript dates', 'M dd, yy', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('23', 'dateandtime', 'Date and Time Settings', 'javascriptshortformat', 'Javascript short date', 'Short date for Javascript dates', 'm/dd/yy', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('24', 'uploads', 'File upload Options', 'docallowedformats', 'Allowed formats for document upload', 'Allowed document file formats', 'doc, docx, pdf, txt, jpg, jpeg, png, bmp', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:09:02', null);
INSERT INTO `appconfig` VALUES ('25', 'uploads', 'File upload Options', 'docmaximumfilesize', 'Maximum allowed size (bytes) for document uploads', 'Maximum size of a document in bytes', '8000000', 'integer', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:09:02', null);
INSERT INTO `appconfig` VALUES ('26', 'profile', 'User Profile Settings', 'passwordmaxlength', 'Maximum password length', 'The maximum length of a password', '20', 'integer', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-03-17 00:05:02', null);
INSERT INTO `appconfig` VALUES ('30', 'notification', 'Notification and Email Options', 'emailmessagesender', 'Sender of email notifications', 'The email address the application uses to send out notifications', 'notifications@devmail.infomacorp.com', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('31', 'uploads', 'File upload Settings', 'photoallowedformats', 'Profile photo allowed formats', 'Allowed photo file formats', 'jpg, jpeg, png', 'text', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:09:02', null);
INSERT INTO `appconfig` VALUES ('32', 'uploads', 'File upload Settings', 'photomaximumfilesize', 'Maximum allowed size (bytes) for profile photo', 'Maximum size of a profile photo in bytes', '5000000', 'integer', 'Y', '1', '2012-03-01 12:00:00', '1', '2014-02-12 14:09:02', null);
INSERT INTO `appconfig` VALUES ('35', 'notification', 'Notification and Email Options', 'supportemailaddress', 'Contact us, feedback and support email address ', 'The address to which support emails are sent', 'support@devmail.infomacorp.com', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('36', 'dateandtime', 'Date and Time Settings', 'mindateofbirth', 'Date of birth number of years before today', 'The number of years before today for allowable date for the hire date', '100', 'integer', 'Y', '1', '2011-05-18 09:55:32', '1', '2014-02-12 14:08:46', null);
INSERT INTO `appconfig` VALUES ('37', 'profile', 'User Profile Settings', 'usernamemaxlength', 'Minimum username length', '', '20', 'integer', 'Y', '1', '2011-05-18 09:55:32', '1', '2014-03-17 00:05:02', null);
INSERT INTO `appconfig` VALUES ('38', 'profile', 'User Profile Settings', 'usernameminlength', 'Maximum username length', '', '4', 'integer', 'Y', '1', '2011-05-18 09:55:32', '1', '2014-03-17 00:05:02', null);
INSERT INTO `appconfig` VALUES ('39', 'country', 'Country Settings', 'countryisocode', 'Country standard iso code', '', 'UG', 'text', 'Y', '1', '2011-05-18 09:55:32', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('40', 'country', 'Country Settings', 'phonemaxlength', 'Maximum digits allowed for phone number', '', '12', 'integer', 'Y', '1', '2011-05-18 09:55:32', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('41', 'country', 'Country Settings', 'phoneminlength', 'Minimum digits allowed for phone number', '', '12', 'integer', 'Y', '1', '2011-05-18 09:55:32', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('42', 'country', 'Country Settings', 'nationalidminlength', 'Minimum digits allowed for National ID', '', '6', 'integer', 'Y', '1', '2011-05-18 09:55:32', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('43', 'country', 'Country Settings', 'nationalidmaxlength', 'Maximum digits allowed for National ID', '', '10', 'integer', 'Y', '1', '2011-05-18 09:55:32', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('44', 'profile', 'User Profile Settings', 'activationkeylength', 'The length of random account activation key', '', '6', 'integer', 'Y', '1', '2011-05-18 09:55:32', '1', '2014-03-17 00:05:02', null);
INSERT INTO `appconfig` VALUES ('45', 'notification', 'Notification and Email Options', 'notificationsendername', 'Name of sender for email notifications', '', 'NFBPC Support', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('46', 'country', 'Country Settings', 'countrycode', 'Phone number code prefix', '', '256', 'integer', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('47', 'sms', 'SMS Settings', 'serverurl', 'The server url', '', 'http://sms.shreeweb.com/sendsms/sendsms.php', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-28 12:40:33', null);
INSERT INTO `appconfig` VALUES ('48', 'sms', 'SMS Settings', 'serverusername', 'The server username', '', 'nfbpc', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-28 12:40:33', null);
INSERT INTO `appconfig` VALUES ('49', 'sms', 'SMS Settings', 'serverpassword', 'The server password', '', '9UhDHU9V', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-28 12:40:33', null);
INSERT INTO `appconfig` VALUES ('50', 'sms', 'SMS Settings', 'serverport', 'The sms server port', '', null, 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-28 12:40:33', null);
INSERT INTO `appconfig` VALUES ('51', 'sms', 'SMS Settings', 'sendername', 'The default sender of sms notifications', '', 'NFBPC', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-28 12:40:33', null);
INSERT INTO `appconfig` VALUES ('52', 'sms', 'SMS Settings', 'testnumber', 'The test number for sms notifications', '', '256701595279', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-28 12:40:33', null);
INSERT INTO `appconfig` VALUES ('53', 'country', 'Country Settings', 'timezone', 'Country timezone', '', 'UTC+03:00', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-02-21 19:00:26', null);
INSERT INTO `appconfig` VALUES ('54', 'notification', 'Notification and Email Options', 'errorlogemail', 'Email used to report errors and downtime', '', 'admin@devmail.infomacorp.com', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('55', 'notification', 'Notification and Email Options', 'smtpuser', 'SMPT User email', '', 'admin@devmail.infomacorp.com', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('56', 'notification', 'Notification and Email Options', 'smtphost', 'SMTP host ipaddress/domain', '', '127.0.0.1', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('57', 'notification', 'Notification and Email Options', 'smtppassword', 'SMTP Password', '', 'password', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('58', 'notification', 'Notification and Email Options', 'smtpport', 'SMTP Port', '', '25', 'integer', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('59', 'notification', 'Notification and Email Options', 'defaultadminemail', 'Default Admin email', '', 'admin@devmail.infomacorp.com', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('60', 'notification', 'Notification and Email Options', 'defaultadminname', 'Default Admin name', '', 'NFBPC Support', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('61', 'system', 'System and UI Settings', 'appname', 'Application name', '', 'NFBPC Web Portal', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-12 11:10:47', null);
INSERT INTO `appconfig` VALUES ('62', 'system', 'System and UI Settings', 'companyname', 'Company name', '', 'NFBPC', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-12 11:10:47', null);
INSERT INTO `appconfig` VALUES ('63', 'system', 'System and UI Settings', 'companysignoff', 'Company signoff', '', 'NFBPC Support', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-12 11:10:48', null);
INSERT INTO `appconfig` VALUES ('64', 'system', 'System and UI Settings', 'logotype', 'Logo Type', '', '1', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-12 11:10:48', null);
INSERT INTO `appconfig` VALUES ('65', 'system', 'System and UI Settings', 'copyrightinfo', 'Company Copyright', '', ' Copyright Ã¢â€Å“ÃƒÂ©Ã¢â€Â¬Ã‚Â® 2014  |  NFBPC  |  All Rights Reserved', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-02-13 16:54:06', null);
INSERT INTO `appconfig` VALUES ('71', 'api', 'APIs and Services', 'google_mapsapikey', 'API Key for google maps', '', 'AIzaSyAjkTHnLzEkyF1ntVoUkZthaZWV4VN5DJE', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-12 10:59:09', null);
INSERT INTO `appconfig` VALUES ('75', 'api', 'APIs and Services', 'google_disablemaps', 'Turn on/off maps for debugging', '', 'on', 'Boolean', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-12 10:59:09', null);
INSERT INTO `appconfig` VALUES ('76', 'sms', 'SMS Settings', 'smsdelivery', 'Turn on/off sms sending feature', '', 'on', 'Boolean', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-28 12:40:33', null);
INSERT INTO `appconfig` VALUES ('77', 'system', 'System and UI Settings', 'db_host', 'Database Host', '', 'localhost', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-12 11:10:48', null);
INSERT INTO `appconfig` VALUES ('78', 'system', 'System and UI Settings', 'db_username', 'Database Username', '', 'dev', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-12 11:10:48', null);
INSERT INTO `appconfig` VALUES ('79', 'system', 'System and UI Settings', 'db_name', 'Database Name', '', 'nfbpc', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-12 11:10:48', null);
INSERT INTO `appconfig` VALUES ('80', 'system', 'System and UI Settings', 'db_password', 'Database Password', '', 'dev', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-03-12 11:10:48', null);
INSERT INTO `appconfig` VALUES ('96', 'notification', 'Notification and Email Options', 'amiaadminusername', 'AMIA Admin User', '', 'admin', 'text', 'Y', '1', '2014-03-12 08:00:44', '1', '2014-04-02 12:07:37', null);
INSERT INTO `appconfig` VALUES ('97', 'notification', 'Notification and Email Options', 'defaultadminusername', 'Default Admin User', '', 'admin', 'text', 'Y', '1', '2012-02-28 15:59:27', '1', '2014-04-02 12:07:37', null);
