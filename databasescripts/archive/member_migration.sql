update memt SET Contact = replace(Contact,' ','') where `Contact` <> '';
update memt SET Contact = replace(Contact,'-','') where `Contact` <> '';
update memt SET Contact = replace(Contact,'/','') where `Contact` <> '';

update memt SET P_Contact2 = replace(P_Contact2,' ','') where `P_Contact2` <> '';
update memt SET P_Contact2 = replace(P_Contact2,'-','') where `P_Contact2` <> '';
update memt SET P_Contact2 = replace(P_Contact2,'/','') where `P_Contact2` <> '';

DELETE from memt where `Contact` = ''; 
DELETE from memt where LENGTH(Contact) BETWEEN 1 AND 8; 
DELETE from memt where `SECOND NAME` = '' OR `F_Name` = '';
DELETE from memt where id IN (688,1372,1826,547,1425);

update memt set Contact = concat('256',SUBSTRING(Contact, -9)) where `Contact` <> '';
update memt set P_Contact2 = concat('256',SUBSTRING(P_Contact2, -9)) where `P_Contact2` <> '';

DELETE from memt where id IN (1642,);


SELECT m.id, m.F_Name, m.`SECOND NAME`, m.Contact FROM memt m
INNER JOIN (SELECT Contact FROM memt
GROUP BY Contact HAVING count(id) > 1) t ON m.Contact = t.Contact order by m.Contact asc, m.id asc 

