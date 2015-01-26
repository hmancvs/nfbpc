-- Reset all email addresses to development addresses and the password to the word password
-- UPDATE useraccount set email =  INSERT(email,LOCATE('@', email)+1, 22,'portal.nfbpc.org'), password = sha1('password');
UPDATE member set email =  concat(username,'@portal.nfbpc.org'), password = sha1('password') WHERE email like '%devmail%';
UPDATE member set email =  'admin@portal.nfbpc.org', username = 'admin' where id = 1;

UPDATE appconfig set optionvalue = 'support@portal.nfbpc.org' where optionname = 'emailmessagesender';
UPDATE appconfig set optionvalue = 'support@portal.nfbpc.org' where optionname = 'supportemailaddress';
UPDATE appconfig set optionvalue = 'support@portal.nfbpc.org' where optionname = 'defaultadminemail';
UPDATE appconfig set optionvalue = 'support@portal.nfbpc.org' where optionname = 'errorlogemail';
UPDATE appconfig set optionvalue = 'support@portal.nfbpc.org' where optionname = 'smtpuser';

update location l set l.name = REPLACE(l.name,' Ii',' II') WHERE `name` LIKE '% Ii';
update location l set l.name = REPLACE(l.name,' Iii',' III') WHERE `name` LIKE '% Iii';
update location l set l.name = REPLACE(l.name,' Iiii',' IIII') WHERE `name` LIKE '% Iiii';

delete from lookuptypevalue where id = 31;
UPDATE lookuptype set displayname = 'Member Professions' where name = 'PROFESSIONS';
UPDATE lookuptype set displayname = 'Next of Kin Relationships' where name = 'CONTACT_RELATIONSHIPS';
delete from department where id = 20 OR id = 21;
delete from position where id = 20;
update organisation set type = 1 where type is null;

ALTER table member add column memberdistrictid int(11) default null;
ALTER table organisation add column orgdistrictid int(11) default null;

UPDATE nfb_menu set link = 'http://portal.nfbpc.org/index/committee' where id = '129';
UPDATE nfb_menu set link = 'http://portal.nfbpc.org/index/ministries' where id = '132';
UPDATE nfb_menu set link = 'http://portal.nfbpc.org/index/committee' where id = '145';
UPDATE nfb_menu set link = 'http://portal.nfbpc.org/index/search' where id = '155';

UPDATE nfb_modules set content = '<p><br /><a href="http://portal.nfbpc.org/index/search"><img src="images/church near.png" alt="" width="250" height="65" /></a> <a href="index.php?option=com_k2&amp;view=item&amp;layout=item&amp;id=9&amp;Itemid=147"><img src="images/send-prayer-request.png" alt="" width="252" height="65" /></a></p>' where id = '102';
