
select l.id, l.name, l.districtid, l.gpslat, l.gpslng, d.gpslat, d.gpslng from location l INNER JOIN location d on l.districtid = d.id  where l.locationtype = 2 and l.districtid <> '' ;

update location l INNER JOIN location d on l.districtid = d.id set l.gpslat = d.gpslat, l.gpslng = d.gpslng where l.locationtype = 2 and l.districtid <> '' ;

update location l INNER JOIN location d on l.districtid = d.id set l.regionid = d.regionid where l.locationtype = 2 and l.districtid <> '' ;

select l.id, l.name, l.districtid, l.regionid, l.nfbpcregionid, l.provinceid, d.nfbpcregionid, d.provinceid 
from location l INNER JOIN location d on l.districtid = d.id where l.locationtype = 3;

update location l INNER JOIN location d on l.districtid = d.id set l.nfbpcregionid = d.nfbpcregionid, l.provinceid = d.provinceid where l.locationtype = 3;
update location l INNER JOIN location d on l.districtid = d.id set l.nfbpcregionid = d.nfbpcregionid, l.provinceid = d.provinceid where l.locationtype = 4;
update location l INNER JOIN location d on l.districtid = d.id set l.nfbpcregionid = d.nfbpcregionid, l.provinceid = d.provinceid where l.locationtype = 5;
update location l INNER JOIN location d on l.districtid = d.id set l.nfbpcregionid = d.nfbpcregionid, l.provinceid = d.provinceid where l.locationtype = 6;