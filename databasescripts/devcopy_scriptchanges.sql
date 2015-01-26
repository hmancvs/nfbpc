-- Reset all email addresses to development addresses and the password to the word password
UPDATE member set email =  INSERT(email,LOCATE('@', email)+1, 15,'devmail.infomacorp.com') where email <> '';
UPDATE member set password = sha1('password') where password <> '';
UPDATE member set email =  concat('test',id,'@devmail.infomacorp.com') WHERE email like '%veritracker%';
UPDATE member set email = 'admin@devmail.infomacorp.com', username = 'admin' WHERE id = '1';

UPDATE appconfig set optionvalue = 'notifications@devmail.infomacorp.com' where optionname = 'emailmessagesender';
UPDATE appconfig set optionvalue = 'support@devmail.infomacorp.com' where optionname = 'supportemailaddress';
UPDATE appconfig set optionvalue = 'admin@devmail.infomacorp.com' where optionname = 'errorlogemail';
UPDATE appconfig set optionvalue = 'admin@devmail.infomacorp.com' where optionname = 'defaultadminemail';
UPDATE appconfig set optionvalue = 'admin' where optionname = 'amiaadminusername'; 
UPDATE appconfig set optionvalue = '127.0.0.1' where optionname = 'smtphost'; 
UPDATE appconfig set optionvalue = 'admin@devmail.infomacorp.com' where optionname = 'smtpuser'; 
UPDATE appconfig set optionvalue = 'password' where optionname = 'smtppassword';

UPDATE nfbpcjoomla.nfb_menu set link = 'http://127.0.0.1/nfbpc/index/committee' where id = '129';
UPDATE nfbpcjoomla.nfb_menu set link = 'http://127.0.0.1/nfbpc/index/ministries' where id = '132';
UPDATE nfbpcjoomla.nfb_menu set link = 'http://127.0.0.1/nfbpc/index/committee' where id = '145';
UPDATE nfbpcjoomla.nfb_menu set link = 'http://127.0.0.1/nfbpc/index/search' where id = '155';

UPDATE nfbpcjoomla.nfb_modules set content = '<p><br /><a href="http://127.0.0.1/nfbpc/index/search"><img src="images/church near.png" alt="" width="250" height="65" /></a> <a href="index.php?option=com_k2&amp;view=item&amp;layout=item&amp;id=9&amp;Itemid=147"><img src="images/send-prayer-request.png" alt="" width="252" height="65" /></a></p>' where id = '102';