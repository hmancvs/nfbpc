-- Reset all email addresses to development addresses and the password to the word password
UPDATE useraccount set email =  INSERT(email,LOCATE('@', email)+1, 15,'devmail.infomacorp.com') where email <> '';
UPDATE useraccount set password = sha1('password') where password <> '';
UPDATE useraccount set email =  concat('test',id,'@devmail.infomacorp.com') WHERE email like '%veritracker%';
UPDATE useraccount set email = 'admin@devmail.infomacorp.com', username = 'admin' WHERE id = '1';

UPDATE appconfig set optionvalue = 'notifications@devmail.infomacorp.com' where optionname = 'emailmessagesender';
UPDATE appconfig set optionvalue = 'support@devmail.infomacorp.com' where optionname = 'supportemailaddress';
UPDATE appconfig set optionvalue = 'admin@devmail.infomacorp.com' where optionname = 'errorlogemail';
UPDATE appconfig set optionvalue = 'admin@devmail.infomacorp.com' where optionname = 'defaultadminemail';
UPDATE appconfig set optionvalue = 'admin' where optionname = 'amiaadminusername'; 
UPDATE appconfig set optionvalue = '127.0.0.1' where optionname = 'smtphost'; 
UPDATE appconfig set optionvalue = 'admin@devmail.infomacorp.com' where optionname = 'smtpuser'; 
UPDATE appconfig set optionvalue = 'password' where optionname = 'smtppassword';