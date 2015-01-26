update member set createdby = 1 where (createdby is null OR createdby = '');
update member set datecreated = '2014-10-01 12:00:00' where (datecreated is null OR datecreated = '');

update organisation set createdby = 1 where (createdby is null OR createdby = '');
update organisation set datecreated = '2014-10-01 12:00:00' where (datecreated is null OR datecreated = '');
